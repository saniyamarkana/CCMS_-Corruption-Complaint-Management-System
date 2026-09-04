<?php
require_once '../db.php';

// Check if Admin is logged in (optional check, fallback allowed)
$error_msg = '';
$success_msg = '';

// Handle Add Officer Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_officer'])) {
    $name = trim($_POST['name'] ?? '');
    $badge = trim($_POST['badge'] ?? '');
    $designation = trim($_POST['designation'] ?? '');
    $department_id = intval($_POST['department_id'] ?? 1);
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $status = $_POST['status'] ?? 'Active';

    if (empty($name) || empty($email) || empty($password) || empty($designation)) {
        $error_msg = 'Please fill in all mandatory officer credentials.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_msg = 'Please enter a valid official email address.';
    } else {
        // Check duplicate email
        $check = mysqli_prepare($conn, "SELECT officer_id FROM officers WHERE email = ? LIMIT 1");
        if ($check) {
            mysqli_stmt_bind_param($check, "s", $email);
            mysqli_stmt_execute($check);
            mysqli_stmt_store_result($check);

            if (mysqli_stmt_num_rows($check) > 0) {
                $error_msg = "An officer with email '{$email}' already exists in the roster.";
            } else {
                $insert = mysqli_prepare($conn, "INSERT INTO officers (name, email, phone, password, status, designation, department_id, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
                if ($insert) {
                    mysqli_stmt_bind_param($insert, "ssssssi", $name, $email, $phone, $password, $status, $designation, $department_id);
                    if (mysqli_stmt_execute($insert)) {
                        $success_msg = "Officer '{$name}' registered and credentialed successfully!";
                    } else {
                        $error_msg = "Failed to add officer: " . mysqli_error($conn);
                    }
                    mysqli_stmt_close($insert);
                }
            }
            mysqli_stmt_close($check);
        }
    }
}

// Fetch dynamic officers list from MySQL
$officers_query = "SELECT * FROM officers ORDER BY officer_id DESC";
$officers_res = mysqli_query($conn, $officers_query);
$db_officers = [];
if ($officers_res) {
    while ($row = mysqli_fetch_assoc($officers_res)) {
        $db_officers[] = $row;
    }
}

// Department name helper
$dept_names = [
    1 => 'Public Works & Transport',
    2 => 'Revenue & Customs Board',
    3 => 'Health & Family Welfare',
    4 => 'Education & Higher Studies',
    5 => 'Land Administration & Records',
    6 => 'Law Enforcement & Police',
    7 => 'Energy & Power Resources',
    8 => 'Anti-Corruption Bureau'
];
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Officers — CCMS Admin</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&family=Space+Grotesk:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    .officer-card { background:var(--surface); border:1px solid var(--border); border-radius:var(--r-lg); overflow:hidden; transition:var(--tx); box-shadow:var(--sh-card); }
    .officer-card:hover { transform:translateY(-5px); box-shadow:var(--sh-md); border-color:var(--indigo-400); }
    .officer-banner { height:80px; position:relative; display:flex; align-items:flex-end; padding:0 18px 0; }
    .officer-avatar { width:72px; height:72px; border-radius:16px; object-fit:cover; border:3px solid var(--surface); box-shadow:var(--sh-sm); position:relative; bottom:-36px; flex-shrink:0; }
    .officer-body { padding:44px 18px 16px; }
    .officer-footer { padding:12px 18px; border-top:1px solid var(--border); background:var(--surface-2); display:flex; align-items:center; justify-content:space-between; }
    .workload-bar { height:6px; border-radius:99px; background:var(--border); overflow:hidden; margin-top:6px; }
    .workload-fill { height:100%; border-radius:99px; transition:width 1s ease; }
    .badge-rank { display:inline-flex; align-items:center; gap:6px; padding:.28em .8em; border-radius:8px; font-size:.7rem; font-weight:800; text-transform:uppercase; letter-spacing:.05em; }
  </style>
</head>
<body>
<div id="wrapper">
  <!-- ══════════════════════ SIDEBAR ══════════════════════ -->
  <aside id="sidebar">
    <div class="sb-brand">
      <div class="sb-logo"><i class="fa-solid fa-shield-halved"></i></div>
      <div><div class="sb-title">CCMS</div><div class="sb-sub">Anti-Corruption</div></div>
    </div>
    <nav class="sb-nav">
      <div class="sb-section-label">Public Portal</div>
      <a href="../index.php" class="sb-link">
        <div class="icon-wrap"><i class="fa-solid fa-globe"></i></div>
        <span>Citizen Portal</span>
      </a>

      <div class="sb-section-label">Main Overview</div>
      <a href="index.php" class="sb-link"><div class="icon-wrap"><i class="fa-solid fa-gauge-high"></i></div><span>Dashboard</span></a>
      <a href="manage-complaints.php" class="sb-link"><div class="icon-wrap"><i class="fa-solid fa-folder-open"></i></div><span>Manage Complaints</span><span class="sb-badge">12</span></a>
      <a href="assign-complaints.php" class="sb-link"><div class="icon-wrap"><i class="fa-solid fa-user-tag"></i></div><span>Assign Complaints</span><span class="sb-badge amber">5</span></a>
      
      <div class="sb-section-label">Personnel</div>
      <a href="manage-users.php" class="sb-link"><div class="icon-wrap"><i class="fa-solid fa-users"></i></div><span>Manage Users</span></a>
      <a href="manage-officers.php" class="sb-link active"><div class="icon-wrap"><i class="fa-solid fa-user-shield"></i></div><span>Manage Officers</span></a>
      
      <div class="sb-section-label">System Masters</div>
      <a href="manage-departments.php" class="sb-link"><div class="icon-wrap"><i class="fa-solid fa-building-columns"></i></div><span>Departments</span></a>
      <a href="manage-categories.php" class="sb-link"><div class="icon-wrap"><i class="fa-solid fa-tags"></i></div><span>Categories</span></a>
      
      <div class="sb-section-label">Intelligence</div>
      <a href="reports.php" class="sb-link"><div class="icon-wrap"><i class="fa-solid fa-file-chart-column"></i></div><span>Reports</span></a>
      <a href="analytics.php" class="sb-link"><div class="icon-wrap"><i class="fa-solid fa-chart-line"></i></div><span>Analytics</span></a>
      <a href="activity-logs.php" class="sb-link"><div class="icon-wrap"><i class="fa-solid fa-clock-rotate-left"></i></div><span>Activity Logs</span></a>
    </nav>
    <div class="sb-footer"><div class="sb-admin-card"><img src="https://images.unsplash.com/photo-1560250097-0b93528c311a?auto=format&fit=crop&w=80&q=80" alt="Admin" class="sb-avatar"><div class="sb-admin-info"><div class="sb-admin-name">Master Admin</div><div class="sb-admin-role">Super Administrator</div></div></div></div>
  </aside>

  <!-- ══════════════════════ MAIN CONTENT ══════════════════════ -->
  <div id="main-content">
    <nav class="top-navbar">
      <div class="d-flex align-items-center gap-3">
        <button class="btn-toggle" id="sidebarToggle"><i class="fa-solid fa-bars-staggered"></i></button>
        <div class="search-wrap d-none d-md-block"><i class="fa-solid fa-magnifying-glass"></i><input type="text" id="officerSearch" placeholder="Search officer name, email, rank…"></div>
      </div>
      <div class="nav-actions">
        <div class="online-chip d-none d-xl-flex"><span class="online-dot"></span>System Online</div>
        <button class="nav-icon-btn" id="themeToggle"><i class="fa-solid fa-moon" id="themeIcon"></i></button>
        <div class="dropdown"><div class="nav-profile-btn dropdown-toggle" data-bs-toggle="dropdown"><img src="https://images.unsplash.com/photo-1560250097-0b93528c311a?auto=format&fit=crop&w=80&q=80" class="nav-avatar" alt="Admin"><div class="d-none d-md-block"><div class="nav-profile-name"><?= htmlspecialchars($_SESSION['admin_name'] ?? 'Super Admin') ?></div><div class="nav-profile-role">Master Authority</div></div></div>
          <ul class="dropdown-menu dropdown-menu-end"><li><a class="dropdown-item" href="../index.php"><i class="fa-solid fa-globe"></i> View Citizen Portal</a></li><li><a class="dropdown-item" href="activity-logs.php"><i class="fa-solid fa-clock-rotate-left"></i> My Logs</a></li><li><div class="dropdown-divider"></div></li><li><a class="dropdown-item text-danger" href="login.php"><i class="fa-solid fa-power-off"></i> Logout</a></li></ul>
        </div>
      </div>
    </nav>

    <main class="content-body">
      <div class="page-header">
        <div>
          <div class="page-breadcrumb"><a href="index.php"><i class="fa-solid fa-house"></i></a><i class="fa-solid fa-chevron-right"></i><span>Manage Officers</span></div>
          <h1 class="page-title">Anti-Corruption Investigators Roster</h1>
          <p class="page-subtitle">Roster of all investigating officers, ranks, caseloads, and statutory authority metrics.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
          <button class="btn btn-ghost btn-export-pdf" data-doc-name="Officers_Roster_PDF"><i class="fa-solid fa-file-pdf" style="color:var(--rose-500);"></i> Export PDF</button>
          <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addOfficerModal"><i class="fa-solid fa-user-plus me-1"></i> Add New Officer</button>
        </div>
      </div>

      <!-- KPI Row -->
      <div class="kpi-grid">
        <div class="kpi-card indigo">
          <div class="kpi-icon"><i class="fa-solid fa-id-badge"></i></div>
          <div class="kpi-value counter-value"><?= count($db_officers) ?></div>
          <div class="kpi-label">Registered Officers</div>
          <div class="kpi-trend up"><i class="fa-solid fa-check"></i> In Database</div>
        </div>
        <div class="kpi-card emerald">
          <div class="kpi-icon"><i class="fa-solid fa-user-shield"></i></div>
          <div class="kpi-value counter-value"><?= count(array_filter($db_officers, fn($o) => ($o['status'] ?? '') === 'Active')) ?></div>
          <div class="kpi-label">On Active Duty</div>
          <div class="kpi-trend up"><i class="fa-solid fa-bolt"></i> Active Status</div>
        </div>
        <div class="kpi-card amber">
          <div class="kpi-icon"><i class="fa-solid fa-plane-departure"></i></div>
          <div class="kpi-value counter-value"><?= count(array_filter($db_officers, fn($o) => ($o['status'] ?? '') !== 'Active')) ?></div>
          <div class="kpi-label">On Leave / Inactive</div>
          <div class="kpi-trend down"><i class="fa-solid fa-umbrella-beach"></i> Rotational</div>
        </div>
        <div class="kpi-card rose">
          <div class="kpi-icon"><i class="fa-solid fa-folder-tree"></i></div>
          <div class="kpi-value counter-value">8</div>
          <div class="kpi-label">Divisions Active</div>
          <div class="kpi-trend up"><i class="fa-solid fa-scale-balanced"></i> National Reach</div>
        </div>
      </div>

      <!-- Filter Bar -->
      <div class="filter-bar">
        <div class="row g-3 align-items-end">
          <div class="col-md-3">
            <label class="form-label">Rank / Designation</label>
            <div class="input-icon-group">
              <i class="fa-solid fa-medal input-icon"></i>
              <select class="form-select" id="rankFilter">
                <option value="">All Ranks</option>
                <option>Inspector</option>
                <option>Investigation Officer</option>
                <option>Senior Officer</option>
                <option>Deputy Commissioner</option>
              </select>
            </div>
          </div>
          <div class="col-md-3">
            <label class="form-label">Assigned Department</label>
            <div class="input-icon-group">
              <i class="fa-solid fa-building-columns input-icon"></i>
              <select class="form-select" id="officerDeptFilter">
                <option value="">All Departments</option>
                <option>Public Works</option>
                <option>Revenue & Customs</option>
                <option>Health</option>
                <option>Education</option>
                <option>Land Administration</option>
                <option>Law Enforcement</option>
              </select>
            </div>
          </div>
          <div class="col-md-3">
            <label class="form-label">Duty Status</label>
            <div class="input-icon-group">
              <i class="fa-solid fa-shield-halved input-icon"></i>
              <select class="form-select" id="dutyFilter">
                <option value="">All Statuses</option>
                <option value="Active">Active</option>
                <option value="Leave">On Leave</option>
                <option value="Inactive">Inactive</option>
              </select>
            </div>
          </div>
          <div class="col-md-3 d-flex gap-2">
            <button class="btn btn-primary w-100" onclick="filterOfficerCards()"><i class="fa-solid fa-filter"></i> Filter</button>
            <button class="btn btn-ghost" onclick="resetOfficerFilter()" title="Reset"><i class="fa-solid fa-rotate-left"></i></button>
          </div>
        </div>
      </div>

      <!-- Dynamic Officer Cards Grid From MySQL Database -->
      <div class="row g-3" id="officerGrid">

        <?php if (!empty($db_officers)): ?>
          <?php foreach ($db_officers as $idx => $off): 
            $dept_title = $dept_names[$off['department_id'] ?? 1] ?? 'Anti-Corruption Bureau';
            $avatar_seed = urlencode($off['name']);
            $avatar_url = "https://ui-avatars.com/api/?name={$avatar_seed}&background=6366f1&color=fff&size=120";
            $banner_bg = ($idx % 3 === 0) ? 'linear-gradient(135deg,#4f46e5,#7c3aed)' : (($idx % 3 === 1) ? 'linear-gradient(135deg,#0284c7,#0369a1)' : 'linear-gradient(135deg,#10b981,#059669)');
            $is_active = ($off['status'] ?? 'Active') === 'Active';
          ?>
          <div class="col-lg-4 col-md-6 officer-item" data-dept="<?= htmlspecialchars($dept_title) ?>" data-rank="<?= htmlspecialchars($off['designation'] ?? 'Officer') ?>" data-status="<?= htmlspecialchars($off['status'] ?? 'Active') ?>">
            <div class="officer-card">
              <div class="officer-banner" style="background:<?= $banner_bg ?>;">
                <img src="<?= $avatar_url ?>" class="officer-avatar" alt="<?= htmlspecialchars($off['name']) ?>">
                <div class="ms-3 pb-1" style="color:rgba(255,255,255,.8);font-size:.7rem;font-weight:700;">ID: #OFF-<?= $off['officer_id'] ?></div>
              </div>
              <div class="officer-body">
                <div class="d-flex align-items-start justify-content-between mb-1">
                  <div>
                    <div class="fw-bold" style="font-size:1rem;"><?= htmlspecialchars($off['name']) ?></div>
                    <div class="extra-small text-muted"><?= htmlspecialchars($off['email']) ?></div>
                  </div>
                  <span class="pill <?= $is_active ? 'pill-active pill-live' : 'pill-blocked' ?>">
                    <?= htmlspecialchars($off['status'] ?? 'Active') ?>
                  </span>
                </div>
                <div class="mb-3">
                  <span class="badge-rank" style="background:rgba(99,102,241,.12);color:var(--primary);"><i class="fa-solid fa-medal"></i> <?= htmlspecialchars($off['designation'] ?? 'Field Officer') ?></span>
                </div>
                <div class="row g-2 text-center mb-3">
                  <div class="col-6"><div style="background:var(--bg);border:1px solid var(--border);border-radius:10px;padding:8px;"><div class="extra-small text-muted">PHONE</div><div class="fw-bold extra-small text-truncate"><?= htmlspecialchars($off['phone'] ?? 'N/A') ?></div></div></div>
                  <div class="col-6"><div style="background:var(--bg);border:1px solid var(--border);border-radius:10px;padding:8px;"><div class="extra-small text-muted">DEPT CODE</div><div class="fw-bold extra-small">Dept #<?= $off['department_id'] ?></div></div></div>
                </div>
                <div class="extra-small fw-bold text-muted mb-1">DUTY STATUS</div>
                <div class="workload-bar"><div class="workload-fill" style="width:<?= $is_active ? '85%' : '15%' ?>;background:<?= $is_active ? 'var(--g-emerald)' : 'var(--g-amber)' ?>;"></div></div>
                <div class="d-flex justify-content-between extra-small mt-1 text-muted"><span>Assigned to investigations</span><span class="fw-bold"><?= $is_active ? 'Operational' : 'On Leave' ?></span></div>
              </div>
              <div class="officer-footer">
                <div class="extra-small text-muted text-truncate" style="max-width: 200px;"><i class="fa-solid fa-building me-1"></i><?= htmlspecialchars($dept_title) ?></div>
                <div class="d-flex gap-1">
                  <button class="btn btn-ghost btn-icon btn-sm" onclick="showOfficerProfile('<?= htmlspecialchars(addslashes($off['name'])) ?>', '<?= htmlspecialchars(addslashes($off['name'])) ?>', 'OFF-<?= $off['officer_id'] ?>', '<?= htmlspecialchars(addslashes($off['designation'])) ?>', '<?= htmlspecialchars(addslashes($dept_title)) ?>', '<?= htmlspecialchars(addslashes($off['email'])) ?>', '5', '12', '95%', '<?= $avatar_url ?>')" title="View Dossier"><i class="fa-solid fa-eye text-primary"></i></button>
                  <button class="btn btn-ghost btn-icon btn-sm btn-send-email" data-email="<?= htmlspecialchars($off['email']) ?>" title="Email"><i class="fa-solid fa-envelope text-info"></i></button>
                </div>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="col-12 text-center py-5">
            <i class="fa-solid fa-user-shield text-muted" style="font-size: 3rem;"></i>
            <h5 class="mt-3 text-muted">No officers found in database</h5>
            <p class="text-muted">Click "Add New Officer" above to add your first investigating officer.</p>
          </div>
        <?php endif; ?>

      </div><!-- /officerGrid -->
    </main>
  </div>
</div>

<!-- ══════════════════════ MODAL: ADD OFFICER ══════════════════════ -->
<div class="modal fade" id="addOfficerModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa-solid fa-user-shield text-primary me-2"></i> Register Designated Field Officer</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" action="" enctype="multipart/form-data" id="addOfficerForm" onsubmit="return validateAddOfficer(event)">
        <div class="modal-body" style="padding:24px;">
          
          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label">Officer Full Name *</label>
              <div class="input-icon-group">
                <i class="fa-solid fa-user input-icon"></i>
                <input type="text" class="form-control" name="name" id="newOffName" placeholder="e.g. Rajesh Kumar">
              </div>
            </div>
            <div class="col-md-6">
              <label class="form-label">Badge Identifier / Officer Code</label>
              <div class="input-icon-group">
                <i class="fa-solid fa-id-badge input-icon"></i>
                <input type="text" class="form-control" name="badge" id="newOffBadge" placeholder="e.g. BADGE-8842 or ACO-408">
              </div>
            </div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label">Rank / Designation *</label>
              <div class="input-icon-group">
                <i class="fa-solid fa-medal input-icon"></i>
                <select class="form-select" name="designation" id="newOffDesig">
                  <option value="">Select Rank...</option>
                  <option value="Inspector" selected>Inspector</option>
                  <option value="Investigation Officer">Investigation Officer</option>
                  <option value="Senior Officer">Senior Officer</option>
                  <option value="Deputy Commissioner">Deputy Commissioner</option>
                  <option value="Chief Forensic Auditor">Chief Forensic Auditor</option>
                  <option value="Anti-Corruption Inspector">Anti-Corruption Inspector</option>
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <label class="form-label">Jurisdiction Department *</label>
              <div class="input-icon-group">
                <i class="fa-solid fa-building-columns input-icon"></i>
                <select class="form-select" name="department_id" id="newOffDept">
                  <option value="1">Dept 1: Public Works & Transport</option>
                  <option value="2">Dept 2: Revenue & Customs Board</option>
                  <option value="3">Dept 3: Health & Family Welfare</option>
                  <option value="4">Dept 4: Education & Higher Studies</option>
                  <option value="5">Dept 5: Land Administration & Records</option>
                  <option value="6">Dept 6: Law Enforcement & Police</option>
                  <option value="7">Dept 7: Energy & Power Resources</option>
                  <option value="8">Dept 8: Anti-Corruption Bureau</option>
                </select>
              </div>
            </div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label">Official Secure Email *</label>
              <div class="input-icon-group">
                <i class="fa-solid fa-envelope input-icon"></i>
                <input type="email" class="form-control" name="email" id="newOffEmail" placeholder="rajesh@ccms.com">
              </div>
            </div>
            <div class="col-md-6">
              <label class="form-label">Mobile Contact Number *</label>
              <div class="input-icon-group">
                <i class="fa-solid fa-phone input-icon"></i>
                <input type="tel" class="form-control" name="phone" id="newOffPhone" placeholder="9000000001">
              </div>
            </div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label">Initial Access Password *</label>
              <div class="input-icon-group">
                <i class="fa-solid fa-lock input-icon"></i>
                <input type="password" class="form-control" name="password" id="newOffPass" placeholder="e.g. raj123">
              </div>
              <div class="form-text" style="font-size: 0.75rem;">Officer will use this password to log into the Officer Portal.</div>
            </div>
            <div class="col-md-6">
              <label class="form-label">Initial Duty Status</label>
              <div class="input-icon-group">
                <i class="fa-solid fa-toggle-on input-icon"></i>
                <select class="form-select" name="status">
                  <option value="Active" selected>Active Duty</option>
                  <option value="On Approved Leave">On Approved Leave</option>
                  <option value="Inactive">Inactive / Suspended</option>
                </select>
              </div>
            </div>
          </div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" name="add_officer" class="btn btn-primary"><i class="fa-solid fa-user-plus me-1"></i> Register & Provision Officer</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ══════════════════════ MODAL: VIEW OFFICER PROFILE ══════════════════════ -->
<div class="modal fade" id="viewOfficerModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa-solid fa-id-badge text-primary me-2"></i> Officer Dossier</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" style="padding:24px;">
        <div class="d-flex align-items-center gap-3 mb-4">
          <img src="" id="profAvatar" style="width:72px;height:72px;border-radius:16px;object-fit:cover;border:2px solid var(--border);" alt="">
          <div>
            <div class="fw-bold" style="font-size:1.1rem;" id="profName">Officer Name</div>
            <div class="extra-small text-muted" id="profRank">ACO-401 · Inspector</div>
            <div class="mt-1"><span class="pill pill-active pill-live">Active Duty</span></div>
          </div>
        </div>
        <div class="row g-2 mb-3">
          <div class="col-6"><div style="background:var(--bg);border:1px solid var(--border);border-radius:10px;padding:10px;"><div class="extra-small text-muted">DEPT.</div><div class="fw-semibold extra-small" id="profDept">Public Works</div></div></div>
          <div class="col-6"><div style="background:var(--bg);border:1px solid var(--border);border-radius:10px;padding:10px;"><div class="extra-small text-muted">EMAIL</div><div class="fw-semibold extra-small" id="profEmail">officer@acc.gov</div></div></div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="assets/js/main.js"></script>
<script>
  function showOfficerProfile(name, full, badge, rank, dept, email, active, resolved, rate, avatar) {
    $('#profName').text(full || name);
    $('#profRank').text(`${badge} · ${rank}`);
    $('#profDept').text(dept);
    $('#profEmail').text(email);
    $('#profAvatar').attr('src', avatar);
    const modal = new bootstrap.Modal(document.getElementById('viewOfficerModal'));
    modal.show();
  }

  function filterOfficerCards() {
    const rank = $('#rankFilter').val()?.toLowerCase() || '';
    const dept = $('#officerDeptFilter').val()?.toLowerCase() || '';
    const status = $('#dutyFilter').val()?.toLowerCase() || '';

    $('.officer-item').each(function () {
      const cardRank = $(this).data('rank')?.toLowerCase() || '';
      const cardDept = $(this).data('dept')?.toLowerCase() || '';
      const cardStatus = $(this).data('status')?.toLowerCase() || '';

      const matchRank = !rank || cardRank.includes(rank);
      const matchDept = !dept || cardDept.includes(dept);
      const matchStatus = !status || cardStatus.includes(status);

      $(this).toggle(matchRank && matchDept && matchStatus);
    });
  }

  function resetOfficerFilter() {
    $('#rankFilter, #officerDeptFilter, #dutyFilter, #officerSearch').val('');
    $('.officer-item').show();
  }

  $('#officerSearch').on('keyup', function () {
    const val = $(this).val().toLowerCase();
    $('.officer-item').each(function () {
      $(this).toggle($(this).text().toLowerCase().includes(val));
    });
  });

  function showInlineError(id, msg) {
    const el = document.getElementById(id);
    if (!el) return;
    el.classList.add('is-invalid');
    el.style.borderColor = '#ef4444';
    
    let parent = el.closest('.input-icon-group') || el.closest('.col-md-6') || el.parentElement;
    let err = parent.parentElement.querySelector('.inline-error-text');
    if (!err) {
      err = document.createElement('div');
      err.className = 'inline-error-text';
      err.style.color = '#ef4444';
      err.style.fontSize = '0.78rem';
      err.style.marginTop = '4px';
      err.style.fontWeight = '600';
      err.style.display = 'flex';
      err.style.alignItems = 'center';
      err.style.gap = '4px';
      parent.parentElement.appendChild(err);
    }
    err.innerHTML = `<i class="fa-solid fa-circle-exclamation"></i> ${msg}`;
    err.style.display = 'flex';
  }

  function clearInlineError(id) {
    const el = document.getElementById(id);
    if (!el) return;
    el.classList.remove('is-invalid');
    el.style.borderColor = '';
    let parent = el.closest('.input-icon-group') || el.closest('.col-md-6') || el.parentElement;
    let err = parent.parentElement.querySelector('.inline-error-text');
    if (err) {
      err.style.display = 'none';
    }
  }

  document.addEventListener('DOMContentLoaded', () => {
    ['newOffName', 'newOffDesig', 'newOffDept', 'newOffEmail', 'newOffPhone', 'newOffPass'].forEach(id => {
      const el = document.getElementById(id);
      if (el) {
        el.addEventListener('input', () => clearInlineError(id));
        el.addEventListener('change', () => clearInlineError(id));
      }
    });
  });

  // Inline Red Text Validation for Add Officer
  function validateAddOfficer(e) {
    let isValid = true;
    ['newOffName', 'newOffDesig', 'newOffDept', 'newOffEmail', 'newOffPhone', 'newOffPass'].forEach(clearInlineError);

    const name = document.getElementById('newOffName').value.trim();
    const email = document.getElementById('newOffEmail').value.trim();
    const phone = document.getElementById('newOffPhone').value.trim();
    const pass = document.getElementById('newOffPass').value;
    const desig = document.getElementById('newOffDesig').value;

    if (!name) {
      showInlineError('newOffName', 'Please enter officer full name.');
      isValid = false;
    }
    if (!desig) {
      showInlineError('newOffDesig', 'Please select officer rank / designation.');
      isValid = false;
    }
    if (!email || !email.includes('@')) {
      showInlineError('newOffEmail', 'Please enter a valid official email address.');
      isValid = false;
    }
    if (!phone) {
      showInlineError('newOffPhone', 'Please enter officer contact phone number.');
      isValid = false;
    }
    if (!pass || pass.length < 4) {
      showInlineError('newOffPass', 'Please set an initial access password (min 4 characters).');
      isValid = false;
    }

    if (!isValid && e) e.preventDefault();
    return isValid;
  }
</script>

<!-- Server Feedback Alerts -->
<?php if (!empty($error_msg)): ?>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    Swal.fire({
      icon: 'error',
      title: 'Registration Error',
      text: <?= json_encode($error_msg) ?>,
      confirmButtonColor: '#e11d48'
    });
  });
</script>
<?php endif; ?>

<?php if (!empty($success_msg)): ?>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    Swal.fire({
      icon: 'success',
      title: 'Officer Registered!',
      text: <?= json_encode($success_msg) ?>,
      confirmButtonColor: '#6366f1'
    });
  });
</script>
<?php endif; ?>

</body>
</html>
