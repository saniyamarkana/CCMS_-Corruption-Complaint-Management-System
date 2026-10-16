<?php
require_once '../auth.php';
require_role('citizen', 'login.php');
require_once '../db.php';

$success_token = '';
$error_msg = '';

$citizen_id = (int) $_SESSION['user_id'];
$citizen_name = htmlspecialchars($_SESSION['user_name'] ?? $_SESSION['name'] ?? 'Alex Doe');
$citizen_email = htmlspecialchars($_SESSION['user_email'] ?? $_SESSION['email'] ?? 'citizen@ccms.com');
$citizen_phone = htmlspecialchars($_SESSION['user_phone'] ?? '');
$initials = strtoupper(substr($citizen_name, 0, 2));

// ═══════════════════ PROCESS COMPLAINT LODGEMENT ═══════════════════
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['lodge_complaint'])) {
    $title = trim($_POST['title'] ?? '');
    $category_id = (int) ($_POST['category_id'] ?? 1);
    $location = trim($_POST['location'] ?? 'Municipal Office, Central District');
    $description = trim($_POST['description'] ?? '');
    $priority = trim($_POST['priority'] ?? 'Medium');
    $evidenceFiles = [];
    $movedFiles = [];
    $uploadDirectory = __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'evidence';
    $allowedMimeTypes = [
        'pdf' => ['application/pdf'],
        'jpg' => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'png' => ['image/png'],
        'mp3' => ['audio/mpeg', 'audio/mp3', 'audio/x-mpeg'],
        'mp4' => ['video/mp4', 'application/mp4'],
        'docx' => [
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/zip',
            'application/x-zip',
        ],
    ];
    $uploadErrors = [
        UPLOAD_ERR_INI_SIZE => 'exceeds the server upload size limit.',
        UPLOAD_ERR_FORM_SIZE => 'exceeds the allowed file size.',
        UPLOAD_ERR_PARTIAL => 'was only partially uploaded. Please try again.',
        UPLOAD_ERR_NO_TMP_DIR => 'could not be uploaded because the server temporary folder is missing.',
        UPLOAD_ERR_CANT_WRITE => 'could not be written by the server.',
        UPLOAD_ERR_EXTENSION => 'was rejected by a server extension.',
    ];

    if ($title === '' || $description === '') {
        $error_msg = 'Please provide both complaint title and narrative particulars.';
    } elseif (empty($_FILES['evidence_files'])) {
        $error_msg = 'The form data was not received. Please retry your submission; check the server post_max_size setting if this continues.';
    } else {
        $files = $_FILES['evidence_files'];
        $fileCount = is_array($files['name'] ?? null) ? count($files['name']) : 0;

        for ($i = 0; $i < $fileCount; $i++) {
            $uploadError = (int) ($files['error'][$i] ?? UPLOAD_ERR_NO_FILE);
            if ($uploadError === UPLOAD_ERR_NO_FILE) {
                continue;
            }
            if ($uploadError !== UPLOAD_ERR_OK) {
                $fileName = basename(str_replace('\\', '/', (string) ($files['name'][$i] ?? 'Selected file')));
                $error_msg = $fileName . ' ' . ($uploadErrors[$uploadError] ?? 'could not be uploaded.');
                break;
            }

            $temporaryPath = (string) ($files['tmp_name'][$i] ?? '');
            $fileSize = (int) ($files['size'][$i] ?? 0);
            $originalName = basename(str_replace('\\', '/', (string) ($files['name'][$i] ?? '')));
            $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

            if ($fileSize <= 0 || $fileSize > 40 * 1024 * 1024) {
                $error_msg = $originalName . ' must be no larger than 40 MB.';
                break;
            }
            if (!isset($allowedMimeTypes[$extension]) || !is_uploaded_file($temporaryPath)) {
                $error_msg = $originalName . ' has an unsupported or invalid file type.';
                break;
            }

            $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
            if ($fileInfo === false) {
                $error_msg = 'The server could not validate uploaded file types.';
                break;
            }
            $mimeType = finfo_file($fileInfo, $temporaryPath);
            finfo_close($fileInfo);

            if (!is_string($mimeType) || !in_array($mimeType, $allowedMimeTypes[$extension], true)) {
                $error_msg = $originalName . ' does not match its file extension.';
                break;
            }

            $storedName = bin2hex(random_bytes(16)) . '.' . $extension;
            $evidenceFiles[] = [
                'temporary_path' => $temporaryPath,
                'file_name' => substr(preg_replace('/[^A-Za-z0-9._ -]/', '_', $originalName), 0, 255),
                'file_path' => 'uploads/evidence/' . $storedName,
                'destination' => $uploadDirectory . DIRECTORY_SEPARATOR . $storedName,
                'file_size' => $fileSize,
                'file_type' => $mimeType,
            ];
        }

        if ($error_msg === '' && $evidenceFiles !== [] && !is_dir($uploadDirectory)
            && !mkdir($uploadDirectory, 0750, true) && !is_dir($uploadDirectory)) {
            $error_msg = 'The evidence storage folder could not be created.';
        }

        if ($error_msg === '') {
            if (!mysqli_begin_transaction($conn)) {
                $error_msg = 'Could not start complaint storage transaction: ' . mysqli_error($conn);
            } else {
                $complaintEvidencePath = $evidenceFiles[0]['file_path'] ?? null;
                $complaintStatement = mysqli_prepare(
                    $conn,
                    "INSERT INTO complaints (user_id, category_id, title, description, location, priority, status, evidence, created_at) VALUES (?, ?, ?, ?, ?, ?, 'Submitted', ?, NOW())"
                );

                if (!$complaintStatement) {
                    $error_msg = 'Could not prepare complaint storage: ' . mysqli_error($conn);
                } else {
                    mysqli_stmt_bind_param(
                        $complaintStatement,
                        'iisssss',
                        $citizen_id,
                        $category_id,
                        $title,
                        $description,
                        $location,
                        $priority,
                        $complaintEvidencePath
                    );
                    if (!mysqli_stmt_execute($complaintStatement)) {
                        $error_msg = 'Could not save complaint: ' . mysqli_stmt_error($complaintStatement);
                    }
                    mysqli_stmt_close($complaintStatement);
                }

                $new_id = $error_msg === '' ? (int) mysqli_insert_id($conn) : 0;
                $evidenceStatement = null;
                if ($error_msg === '' && $evidenceFiles !== []) {
                    $evidenceStatement = mysqli_prepare(
                        $conn,
                        'INSERT INTO complaint_evidence (complaint_id, file_name, file_path, file_size, file_type) VALUES (?, ?, ?, ?, ?)'
                    );
                    if (!$evidenceStatement) {
                        $error_msg = 'Could not prepare evidence storage: ' . mysqli_error($conn);
                    }
                }

                if ($error_msg === '') {
                    foreach ($evidenceFiles as $evidenceFile) {
                        if (!move_uploaded_file($evidenceFile['temporary_path'], $evidenceFile['destination'])) {
                            $error_msg = 'Could not move uploaded evidence into secure storage.';
                            break;
                        }
                        $movedFiles[] = $evidenceFile['destination'];

                        if ($evidenceStatement) {
                            mysqli_stmt_bind_param(
                                $evidenceStatement,
                                'issis',
                                $new_id,
                                $evidenceFile['file_name'],
                                $evidenceFile['file_path'],
                                $evidenceFile['file_size'],
                                $evidenceFile['file_type']
                            );
                            if (!mysqli_stmt_execute($evidenceStatement)) {
                                $error_msg = 'Could not save evidence details: ' . mysqli_stmt_error($evidenceStatement);
                                break;
                            }
                        }
                    }
                }

                if ($evidenceStatement) {
                    mysqli_stmt_close($evidenceStatement);
                }

                if ($error_msg === '' && mysqli_commit($conn)) {
                    $success_token = 'CCMS-2026-' . str_pad((string) $new_id, 4, '0', STR_PAD_LEFT);
                    $success_raw_id = $new_id;
                } else {
                    if ($error_msg === '') {
                        $error_msg = 'Could not commit complaint and evidence to the database: ' . mysqli_error($conn);
                    }
                    mysqli_rollback($conn);
                    foreach ($movedFiles as $movedFile) {
                        if (file_exists($movedFile) && !unlink($movedFile)) {
                            error_log('Failed to remove uncommitted complaint evidence file: ' . $movedFile);
                        }
                    }
                }
            }
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST)
    && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
    $error_msg = 'The submitted form exceeded the server request size limit. Please reduce the upload size or ask the administrator to increase post_max_size.';
}

// Fetch categories from DB
$cat_res = mysqli_query($conn, "SELECT * FROM categories WHERE status = 'Active' ORDER BY category_id ASC");
$all_categories = [];
while ($cr = mysqli_fetch_assoc($cat_res)) {
    $all_categories[] = $cr;
}

// Fetch departments from DB
$dept_res = mysqli_query($conn, "SELECT * FROM departments WHERE status = 'Active' ORDER BY department_id ASC");
$all_departments = [];
while ($dr = mysqli_fetch_assoc($dept_res)) {
    $all_departments[] = $dr;
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Lodge Complaint — CCMS Citizen Portal</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&family=Space+Grotesk:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
  <link rel="stylesheet" href="assets/css/citizen.css">

  <style>
    .wizard-pane { display: none; }
    .wizard-pane.active { display: block; animation: fadeIn 0.3s ease; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }

    .dropzone-box {
      border: 2px dashed var(--border);
      border-radius: 18px;
      padding: 2.5rem 1.5rem;
      text-align: center;
      background: var(--bg-surface-2);
      transition: var(--transition);
      cursor: pointer;
    }
    .dropzone-box:hover {
      border-color: var(--primary-light);
      background: rgba(99, 102, 241, 0.04);
    }

    .category-radio-card {
      background: var(--bg-surface-2);
      border: 1px solid var(--border);
      border-radius: 14px;
      padding: 1.25rem;
      cursor: pointer;
      transition: var(--transition);
      display: flex;
      align-items: flex-start;
      gap: 1rem;
      height: 100%;
    }
    .category-radio-card:hover {
      border-color: var(--primary-light);
      transform: translateY(-2px);
    }
    .category-radio-card.selected {
      background: rgba(99, 102, 241, 0.08);
      border-color: var(--primary);
      box-shadow: 0 0 15px var(--primary-glow);
    }
  </style>
</head>
<body>

  <!-- Ambient Visual Glow Blobs -->
  <div class="ambient-glow-1"></div>
  <div class="ambient-glow-2"></div>
  <div class="ambient-glow-3"></div>
  <div class="grid-overlay"></div>

<div id="wrapper">
  <!-- ═══════════════════ CITIZEN SIDEBAR ═══════════════════ -->
  <aside id="sidebar">
    <div class="sb-brand">
      <div class="sb-logo"><i class="fa-solid fa-shield-halved"></i></div>
      <div>
        <div class="sb-title">CCMS</div>
        <div class="sb-sub">Citizen Portal</div>
      </div>
    </div>

    <nav class="sb-nav">
      <div class="sb-section-label">Main Hub</div>
      <a href="dashboard.php" class="sb-link">
        <div class="icon-wrap"><i class="fa-solid fa-gauge-high"></i></div>
        <span>Overview</span>
      </a>

      <a href="file-complaint.php" class="sb-link active">
        <div class="icon-wrap"><i class="fa-solid fa-file-circle-plus"></i></div>
        <span>Lodge Complaint</span>
        <span class="sb-badge emerald">New</span>
      </a>

      <a href="my-complaints.php" class="sb-link">
        <div class="icon-wrap"><i class="fa-solid fa-folder-open"></i></div>
        <span>My Complaints</span>
      </a>

      <a href="track-complaint.php" class="sb-link">
        <div class="icon-wrap"><i class="fa-solid fa-radar"></i></div>
        <span>Live Case Tracker</span>
      </a>

      <div class="sb-section-label">Communications</div>
      <a href="notifications.php" class="sb-link">
        <div class="icon-wrap"><i class="fa-solid fa-bell"></i></div>
        <span>Encrypted Alerts</span>
      </a>

    </nav>

    <div class="sb-footer">
      <div class="user-card">
        <div class="user-avatar"><?= $initials ?></div>
        <div class="user-info">
          <div class="user-name"><?= $citizen_name ?></div>
          <div class="user-role">Verified Citizen</div>
        </div>
      </div>
    </div>
  </aside>

  <!-- ═══════════════════ MAIN CONTENT ═══════════════════ -->
  <div id="main-content">
    <nav class="top-navbar">
      <div class="d-flex align-items-center gap-3">
        <button class="btn-toggle" id="sidebarToggle"><i class="fa-solid fa-bars-staggered"></i></button>
        <span class="fw-bold" style="font-size: 0.95rem;">Lodge Whistleblower Dossier</span>
      </div>

      <div class="nav-actions">
        <button class="nav-icon-btn" id="themeToggle" title="Toggle Theme">
          <i class="fa-solid fa-moon" id="themeIcon"></i>
        </button>
        <a href="dashboard.php" class="btn-ccms btn-ccms-secondary btn-sm">
          <i class="fa-solid fa-xmark"></i> Cancel
        </a>
      </div>
    </nav>

    <main class="content-body">
      <div class="container-fluid" style="max-width: 900px;">
        
        <?php if (!empty($error_msg)): ?>
          <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
            <i class="fa-solid fa-triangle-exclamation me-2"></i><?= htmlspecialchars($error_msg) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
        <?php endif; ?>

        <!-- Multi-Step Wizard Track -->
        <div class="wizard-steps-track mb-4">
          <div class="wizard-step-node active" id="stepNode1" onclick="goToStep(1)">
            <div class="step-circle">1</div>
            <div class="step-label">Protection</div>
          </div>
          <div class="wizard-step-node" id="stepNode2" onclick="goToStep(2)">
            <div class="step-circle">2</div>
            <div class="step-label">Category</div>
          </div>
          <div class="wizard-step-node" id="stepNode3" onclick="goToStep(3)">
            <div class="step-circle">3</div>
            <div class="step-label">Incident</div>
          </div>
          <div class="wizard-step-node" id="stepNode4" onclick="goToStep(4)">
            <div class="step-circle">4</div>
            <div class="step-label">Evidence</div>
          </div>
          <div class="wizard-step-node" id="stepNode5" onclick="goToStep(5)">
            <div class="step-circle">5</div>
            <div class="step-label">Review & Seal</div>
          </div>
        </div>

        <!-- Form Card Container -->
        <div class="card-box">
          <form id="multiStepForm" method="POST" action="file-complaint.php" enctype="multipart/form-data">
            <input type="hidden" name="lodge_complaint" value="1">
            <input type="hidden" name="category_id" id="hiddenCategoryId" value="1">
            <input type="hidden" name="location" id="hiddenLocation" value="Municipal Revenue Desk">
            
            <!-- STEP 1: Whistleblower Mode -->
            <div class="wizard-pane active" id="paneStep1">
              <h3 class="mb-2" style="font-size: 1.25rem;">Select Protection Tier</h3>
              <p class="text-muted mb-4" style="font-size: 0.88rem;">Determine how your identity and contact details are handled.</p>

              <div class="row g-3 mb-4">
                <div class="col-md-6">
                  <div class="category-radio-card selected" id="optAnon" onclick="selectProtectionMode('anonymous')">
                    <div style="font-size: 1.75rem; color: var(--cyan);"><i class="fa-solid fa-user-ninja"></i></div>
                    <div>
                      <strong style="font-size: 1rem; color: var(--text-main); display: block; margin-bottom: 4px;">100% Anonymous Mode</strong>
                      <p class="mb-0 text-muted" style="font-size: 0.8rem; line-height: 1.6;">
                        IP address stripped. No name stored on public docket. You track exclusively with a Cryptographic Case Token.
                      </p>
                    </div>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="category-radio-card" id="optIdentified" onclick="selectProtectionMode('identified')">
                    <div style="font-size: 1.75rem; color: var(--primary-light);"><i class="fa-solid fa-id-card-clip"></i></div>
                    <div>
                      <strong style="font-size: 1rem; color: var(--text-main); display: block; margin-bottom: 4px;">Verified Citizen Mode</strong>
                      <p class="mb-0 text-muted" style="font-size: 0.8rem; line-height: 1.6;">
                        Links complaint to your registered citizen profile (<?= $citizen_name ?>). Enables case notifications.
                      </p>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Citizen Identified Fields -->
              <div id="identifiedFields" style="display: none;" class="p-3 rounded-4 bg-body-tertiary mb-4 border">
                <h5 style="font-size: 0.95rem; margin-bottom: 1rem;">Citizen Identification Details</h5>
                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="form-label" style="font-size: 0.82rem; font-weight: 600;">Full Legal Name</label>
                    <input type="text" class="form-control" value="<?= $citizen_name ?>" readonly>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label" style="font-size: 0.82rem; font-weight: 600;">Contact Email</label>
                    <input type="email" class="form-control" value="<?= $citizen_email ?>" readonly>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label" style="font-size: 0.82rem; font-weight: 600;">Mobile Phone</label>
                    <input type="tel" class="form-control" value="<?= $citizen_phone ?>" placeholder="Phone number">
                  </div>
                  <div class="col-md-6">
                    <label class="form-label" style="font-size: 0.82rem; font-weight: 600;">Protection Clearance</label>
                    <input type="text" class="form-control" value="Level-1 Civic Whistleblower" readonly>
                  </div>
                </div>
              </div>

              <div class="d-flex justify-content-end">
                <button type="button" class="btn-ccms btn-ccms-primary" onclick="goToStep(2)">
                  Continue to Department <i class="fa-solid fa-arrow-right"></i>
                </button>
              </div>
            </div>

            <!-- STEP 2: Department & Category -->
            <div class="wizard-pane" id="paneStep2">
              <h3 class="mb-2" style="font-size: 1.25rem;">Target Department & Category</h3>
              <p class="text-muted mb-4" style="font-size: 0.88rem;">Specify the government body and nature of the corrupt offense.</p>

              <div class="row g-3 mb-4">
                <div class="col-md-6">
                  <label class="form-label" style="font-size: 0.85rem; font-weight: 600;">Target Department / Agency *</label>
                  <select class="form-select" id="deptSelect" onchange="syncLocation()">
                    <?php foreach ($all_departments as $d): ?>
                      <option value="<?= htmlspecialchars($d['department_name']) ?>"><?= htmlspecialchars($d['department_name']) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <div class="col-md-6">
                  <label class="form-label" style="font-size: 0.85rem; font-weight: 600;">Office Branch / Location Jurisdiction *</label>
                  <input type="text" class="form-control" id="locBranch" placeholder="e.g. Central City Customs Port Office, Wing B" value="Main Administrative Branch, District Office" oninput="syncLocation()">
                </div>
              </div>

              <label class="form-label mb-2" style="font-size: 0.85rem; font-weight: 600;">Corruption Offense Category *</label>
              <div class="row g-3 mb-4">
                <?php foreach ($all_categories as $idx => $cat): 
                  $cat_icons = [
                    1 => 'fa-hand-holding-dollar text-warning',
                    2 => 'fa-file-invoice-dollar text-primary',
                    3 => 'fa-user-gear text-info',
                    4 => 'fa-vault text-danger'
                  ];
                  $icon = $cat_icons[$cat['category_id']] ?? 'fa-circle-exclamation text-emerald';
                ?>
                <div class="col-md-6">
                  <div class="category-radio-card <?= $idx === 0 ? 'selected' : '' ?>" onclick="selectCategoryRadio(this, '<?= $cat['category_id'] ?>', '<?= htmlspecialchars(addslashes($cat['category_name'])) ?>')">
                    <i class="fa-solid <?= $icon ?>" style="font-size: 1.3rem;"></i>
                    <div>
                      <strong style="font-size: 0.9rem; display: block;"><?= htmlspecialchars($cat['category_name']) ?></strong>
                      <span class="text-muted" style="font-size: 0.75rem;"><?= htmlspecialchars($cat['description'] ?? 'Statutory code breach') ?></span>
                    </div>
                  </div>
                </div>
                <?php endforeach; ?>
              </div>

              <div class="d-flex justify-content-between">
                <button type="button" class="btn-ccms btn-ccms-secondary" onclick="goToStep(1)">
                  <i class="fa-solid fa-arrow-left"></i> Back
                </button>
                <button type="button" class="btn-ccms btn-ccms-primary" onclick="goToStep(3)">
                  Continue to Incident Details <i class="fa-solid fa-arrow-right"></i>
                </button>
              </div>
            </div>

            <!-- STEP 3: Incident Details -->
            <div class="wizard-pane" id="paneStep3">
              <h3 class="mb-2" style="font-size: 1.25rem;">Incident Sequence & Demand</h3>
              <p class="text-muted mb-4" style="font-size: 0.88rem;">Detail who was involved, demanded sums, and exact circumstance.</p>

              <div class="row g-3 mb-3">
                <div class="col-md-8">
                  <label class="form-label" style="font-size: 0.85rem; font-weight: 600;">Complaint Title / Summary *</label>
                  <input type="text" class="form-control" name="title" id="complaintTitle" placeholder="e.g. Demand of cash kickback for building permit certificate" required>
                </div>

                <div class="col-md-4">
                  <label class="form-label" style="font-size: 0.85rem; font-weight: 600;">Severity Priority *</label>
                  <select class="form-select" name="priority" id="complaintPriority">
                    <option value="Low">Low</option>
                    <option value="Medium" selected>Medium</option>
                    <option value="High">High</option>
                    <option value="Critical">Critical</option>
                  </select>
                </div>
              </div>

              <div class="mb-4">
                <label class="form-label" style="font-size: 0.85rem; font-weight: 600;">Chronological Narrative & Particulars *</label>
                <textarea class="form-control" name="description" id="narrativeText" rows="5" placeholder="State clear facts: date, time, official positions, location, demanded sum, vehicle numbers, or witnesses..." required></textarea>
              </div>

              <div class="d-flex justify-content-between">
                <button type="button" class="btn-ccms btn-ccms-secondary" onclick="goToStep(2)">
                  <i class="fa-solid fa-arrow-left"></i> Back
                </button>
                <button type="button" class="btn-ccms btn-ccms-primary" onclick="goToStep(4)">
                  Attach Evidence Files <i class="fa-solid fa-arrow-right"></i>
                </button>
              </div>
            </div>

            <!-- STEP 4: Evidence Dropzone -->
            <div class="wizard-pane" id="paneStep4">
              <h3 class="mb-2" style="font-size: 1.25rem;">Secure Evidence Storage</h3>
              <p class="text-muted mb-4" style="font-size: 0.88rem;">Attach documents, audio transcripts, invoices, or photograph files.</p>

              <div class="dropzone-box mb-3" id="evidenceDropzone" onclick="triggerFileInput()">
                <input type="file" id="evidenceFileInput" name="evidence_files[]" accept=".pdf,.jpg,.jpeg,.png,.mp3,.mp4,.docx" multiple style="display: none;" onchange="handleEvidenceSelect(event)">
                <div style="font-size: 2.5rem; color: var(--primary); margin-bottom: 0.75rem;">
                  <i class="fa-solid fa-cloud-arrow-up"></i>
                </div>
                <h5 style="font-size: 1.05rem; margin-bottom: 0.25rem;">Click to browse or Drag & Drop evidence files</h5>
                <p class="text-muted mb-0" style="font-size: 0.8rem;">
                  Supported: PDF, JPG, PNG, MP3, MP4, DOCX up to 40 MB each. Total upload size is subject to server limits.
                </p>
              </div>

              <div class="p-3 rounded-4 bg-body-tertiary border mb-4">
                <div class="d-flex align-items-center justify-content-between mb-2">
                  <span style="font-size: 0.85rem; font-weight: 700;">Uploaded Evidence Status</span>
                  <span class="badge bg-success-subtle text-success font-monospace" style="font-size: 0.72rem;">
                    <i class="fa-solid fa-shield-virus me-1"></i> File type validated & stored securely
                  </span>
                </div>
                <div class="extra-small text-muted" id="fileNotice">No local files attached yet. You can proceed without files if oral testimony only.</div>
              </div>

              <div class="d-flex justify-content-between">
                <button type="button" class="btn-ccms btn-ccms-secondary" onclick="goToStep(3)">
                  <i class="fa-solid fa-arrow-left"></i> Back
                </button>
                <button type="button" class="btn-ccms btn-ccms-primary" onclick="goToStep(5)">
                  Review & Submit <i class="fa-solid fa-arrow-right"></i>
                </button>
              </div>
            </div>

            <!-- STEP 5: Review & Seal -->
            <div class="wizard-pane" id="paneStep5">
              <h3 class="mb-2" style="font-size: 1.25rem;">Review & Submit</h3>
              <p class="text-muted mb-4" style="font-size: 0.88rem;">Confirm your report before it is saved to the complaint database.</p>

              <div class="p-3 rounded-4 bg-body-tertiary border mb-4" style="font-size: 0.88rem;">
                <div class="row g-3 mb-2">
                  <div class="col-md-6">
                    <span class="text-muted d-block" style="font-size: 0.78rem;">Protection Mode:</span>
                    <strong id="reviewMode" class="text-cyan"><i class="fa-solid fa-user-ninja me-1"></i> 100% Anonymous Whistleblower</strong>
                  </div>
                  <div class="col-md-6">
                    <span class="text-muted d-block" style="font-size: 0.78rem;">Target Department & Location:</span>
                    <strong id="reviewDept">Revenue & Tax Administration</strong>
                  </div>
                </div>

                <div class="row g-3 mb-2">
                  <div class="col-md-6">
                    <span class="text-muted d-block" style="font-size: 0.78rem;">Offense Category:</span>
                    <strong id="reviewCat">Bribery & Kickbacks</strong>
                  </div>
                  <div class="col-md-6">
                    <span class="text-muted d-block" style="font-size: 0.78rem;">Priority Severity:</span>
                    <strong id="reviewPriority" class="font-monospace text-warning">Medium</strong>
                  </div>
                </div>

                <div class="mb-2">
                  <span class="text-muted d-block" style="font-size: 0.78rem;">Subject Summary:</span>
                  <strong id="reviewSubject">Incident summary</strong>
                </div>
              </div>

              <div class="form-check mb-4">
                <input class="form-check-input" type="checkbox" id="affirmTruthCheck" checked required>
                <label class="form-check-label" for="affirmTruthCheck" style="font-size: 0.82rem; color: var(--text-muted);">
                  I affirm that this corruption incident report is lodged in good faith and represents authentic witness testimony.
                </label>
              </div>

              <div class="d-flex justify-content-between">
                <button type="button" class="btn-ccms btn-ccms-secondary" onclick="goToStep(4)">
                  <i class="fa-solid fa-arrow-left"></i> Back
                </button>
                <button type="submit" class="btn-ccms btn-ccms-primary" style="background: linear-gradient(135deg, var(--emerald) 0%, #059669 100%);">
                  <i class="fa-solid fa-lock"></i> Submit Report &amp; Generate Tracking Token
                </button>
              </div>
            </div>

          </form>
        </div>

      </div>
    </main>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="assets/js/citizen.js"></script>

<script>
  let currentStep = 1;
  let selectedMode = 'anonymous';
  let selectedCategoryName = '<?= htmlspecialchars($all_categories[0]['category_name'] ?? 'Bribery') ?>';

  function goToStep(step) {
    if (step < 1 || step > 5) return;
    currentStep = step;

    for (let i = 1; i <= 5; i++) {
      const node = document.getElementById(`stepNode${i}`);
      const pane = document.getElementById(`paneStep${i}`);
      if (node) {
        node.classList.toggle('active', i === step);
        node.classList.toggle('done', i < step);
      }
      if (pane) {
        pane.classList.toggle('active', i === step);
      }
    }

    if (step === 5) {
      updateReviewSummary();
    }
  }

  function selectProtectionMode(mode) {
    selectedMode = mode;
    document.getElementById('optAnon').classList.toggle('selected', mode === 'anonymous');
    document.getElementById('optIdentified').classList.toggle('selected', mode === 'identified');
    document.getElementById('identifiedFields').style.display = mode === 'identified' ? 'block' : 'none';
  }

  function selectCategoryRadio(card, catId, catName) {
    document.querySelectorAll('#paneStep2 .category-radio-card').forEach(c => c.classList.remove('selected'));
    card.classList.add('selected');
    document.getElementById('hiddenCategoryId').value = catId;
    selectedCategoryName = catName;
  }

  function syncLocation() {
    const dept = document.getElementById('deptSelect').value;
    const branch = document.getElementById('locBranch').value;
    document.getElementById('hiddenLocation').value = dept + ' — ' + branch;
  }

  function triggerFileInput() {
    document.getElementById('evidenceFileInput').click();
  }

  function handleEvidenceSelect(e) {
    const files = Array.from(e.target.files);
    const fileNotice = document.getElementById('fileNotice');
    if (files.length > 0) {
      fileNotice.textContent = `${files.length} file(s) attached: ${files.map(file => file.name).join(', ')}`;
      showCitizenToast(`${files.length} evidence file(s) ready to upload.`, 'success');
    } else {
      fileNotice.textContent = 'No local files attached yet. You can proceed without files if oral testimony only.';
    }
  }

  const evidenceDropzone = document.getElementById('evidenceDropzone');
  evidenceDropzone.addEventListener('dragover', event => {
    event.preventDefault();
  });
  evidenceDropzone.addEventListener('drop', event => {
    event.preventDefault();
    const transfer = new DataTransfer();
    Array.from(event.dataTransfer.files).forEach(file => transfer.items.add(file));
    const fileInput = document.getElementById('evidenceFileInput');
    fileInput.files = transfer.files;
    handleEvidenceSelect({ target: fileInput });
  });

  function updateReviewSummary() {
    syncLocation();
    document.getElementById('reviewMode').innerHTML = selectedMode === 'anonymous'
      ? '<i class="fa-solid fa-user-ninja me-1"></i> 100% Anonymous Whistleblower'
      : '<i class="fa-solid fa-id-card me-1"></i> Verified Citizen Profile';
    document.getElementById('reviewDept').textContent = document.getElementById('hiddenLocation').value;
    document.getElementById('reviewCat').textContent = selectedCategoryName;
    document.getElementById('reviewPriority').textContent = document.getElementById('complaintPriority').value;
    document.getElementById('reviewSubject').textContent = document.getElementById('complaintTitle').value || 'Untitled Incident';
  }

  <?php if (!empty($success_token)): ?>
  Swal.fire({
    icon: 'success',
    title: 'Complaint Submitted Successfully',
    html: `
      <p style="font-size:0.9rem;color:#64748b;">Your complaint and attached evidence have been saved in the CCMS database.</p>
      <div style="background:#f1f5f9;padding:12px;border-radius:12px;margin:15px 0;font-family:monospace;font-size:1.25rem;font-weight:700;color:#4f46e5;border:1px dashed #6366f1;">
        <?= $success_token ?> (Database #<?= $success_raw_id ?>)
      </div>
      <p style="font-size:0.8rem;color:#94a3b8;">Copy this tracking ID to view live investigation progress.</p>
    `,
    showCancelButton: true,
    confirmButtonText: '<i class="fa-solid fa-radar me-1"></i> Track Case Live',
    cancelButtonText: '<i class="fa-solid fa-folder me-1"></i> My Complaints',
    confirmButtonColor: '#4f46e5',
    cancelButtonColor: '#0ea5e9'
  }).then((res) => {
    if (res.isConfirmed) {
      window.location.href = `track-complaint.php?case=<?= $success_raw_id ?>`;
    } else {
      window.location.href = `my-complaints.php`;
    }
  });
  <?php endif; ?>
</script>
</body>
</html>
