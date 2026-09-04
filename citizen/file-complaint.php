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
        <span class="sb-badge">3</span>
      </a>

      <a href="track-complaint.php" class="sb-link">
        <div class="icon-wrap"><i class="fa-solid fa-radar"></i></div>
        <span>Live Case Tracker</span>
      </a>

      <div class="sb-section-label">Communications</div>
      <a href="notifications.php" class="sb-link">
        <div class="icon-wrap"><i class="fa-solid fa-bell"></i></div>
        <span>Encrypted Alerts</span>
        <span class="sb-badge amber">2</span>
      </a>

      <div class="sb-section-label">Account & External</div>
      <a href="profile.php" class="sb-link">
        <div class="icon-wrap"><i class="fa-solid fa-user-shield"></i></div>
        <span>Security & Keys</span>
      </a>

      <a href="../officer/index.php" class="sb-link">
        <div class="icon-wrap"><i class="fa-solid fa-user-shield"></i></div>
        <span>Officer Command</span>
      </a>

      <a href="../admin/index.php" class="sb-link">
        <div class="icon-wrap"><i class="fa-solid fa-lock"></i></div>
        <span>Admin Headquarters</span>
      </a>

      <a href="../index.php" class="sb-link">
        <div class="icon-wrap"><i class="fa-solid fa-globe"></i></div>
        <span>Public Sentinel</span>
      </a>
    </nav>

    <div class="sb-footer">
      <div class="user-card">
        <div class="user-avatar">AD</div>
        <div class="user-info">
          <div class="user-name">Alex Doe</div>
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
        
        <!-- Header -->
        <div class="text-center mb-4">
          <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2 mb-2 font-monospace" style="font-size: 0.8rem;">
            <i class="fa-solid fa-shield-halved me-1"></i> ZERO-KNOWLEDGE 256-BIT ENCRYPTION
          </span>
          <h1 class="page-title">File Anti-Corruption Incident Report</h1>
          <p class="page-subtitle">Submit evidence directly to national vigilance authorities. You may choose complete anonymity.</p>
        </div>

        <!-- 5-Step Progress Bar -->
        <div class="wizard-steps-track">
          <div class="wizard-step-node active" id="stepNode1" onclick="goToStep(1)">
            <div class="step-circle">1</div>
            <div class="step-label">Anonymity</div>
          </div>
          <div class="wizard-step-node" id="stepNode2" onclick="goToStep(2)">
            <div class="step-circle">2</div>
            <div class="step-label">Department</div>
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
          <form id="multiStepForm" onsubmit="handleFinalSubmit(event)">
            
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
                        IP address stripped. No name or contact stored. You track exclusively with a Cryptographic Case Token.
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
                        Links complaint to your citizen profile. Enables direct notifications and official witness protection backing.
                      </p>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Citizen Identified Fields (hidden if anonymous) -->
              <div id="identifiedFields" style="display: none;" class="p-3 rounded-4 bg-body-tertiary mb-4 border">
                <h5 style="font-size: 0.95rem; margin-bottom: 1rem;">Citizen Identification Details</h5>
                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="form-label" style="font-size: 0.82rem; font-weight: 600;">Full Legal Name</label>
                    <input type="text" class="form-control" value="Alex Doe" id="complainantName">
                  </div>
                  <div class="col-md-6">
                    <label class="form-label" style="font-size: 0.82rem; font-weight: 600;">Contact Email</label>
                    <input type="email" class="form-control" value="citizen@anti-corruption.gov" id="complainantEmail">
                  </div>
                  <div class="col-md-6">
                    <label class="form-label" style="font-size: 0.82rem; font-weight: 600;">Mobile Phone</label>
                    <input type="tel" class="form-control" placeholder="+1 (555) 000-0000" id="complainantPhone">
                  </div>
                  <div class="col-md-6">
                    <label class="form-label" style="font-size: 0.82rem; font-weight: 600;">Citizen NID / Voter Number</label>
                    <input type="text" class="form-control" placeholder="e.g. NID-8921471">
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
                  <select class="form-select" id="deptSelect">
                    <option value="">Choose department...</option>
                    <option value="Revenue & Tax Administration" selected>Revenue & Tax Administration</option>
                    <option value="Public Works & Procurement">Public Works & Procurement (PWD)</option>
                    <option value="Law Enforcement & Traffic">Law Enforcement & Police</option>
                    <option value="Healthcare & Medical Procurement">Healthcare & Medical Supply</option>
                    <option value="Education & Universities">Education & University Grants</option>
                    <option value="Land Records & Urban Planning">Land Records & Urban Planning</option>
                    <option value="Customs & Port Logistics">Customs & Port Logistics</option>
                  </select>
                </div>

                <div class="col-md-6">
                  <label class="form-label" style="font-size: 0.85rem; font-weight: 600;">Office Branch / Location Jurisdiction *</label>
                  <input type="text" class="form-control" id="locBranch" placeholder="e.g. Central City Customs Port Office, Wing B" value="Central City Customs Port Office, Wing B">
                </div>
              </div>

              <label class="form-label mb-2" style="font-size: 0.85rem; font-weight: 600;">Corruption Offense Category *</label>
              <div class="row g-3 mb-4">
                <div class="col-md-4">
                  <div class="category-radio-card selected" onclick="selectCategoryRadio(this, 'Bribery & Extortion')">
                    <i class="fa-solid fa-hand-holding-dollar text-warning" style="font-size: 1.3rem;"></i>
                    <div>
                      <strong style="font-size: 0.9rem; display: block;">Bribery / Extortion</strong>
                      <span class="text-muted" style="font-size: 0.75rem;">Demand for unlawful cash/gifts</span>
                    </div>
                  </div>
                </div>

                <div class="col-md-4">
                  <div class="category-radio-card" onclick="selectCategoryRadio(this, 'Tender / Procurement Fraud')">
                    <i class="fa-solid fa-file-invoice-dollar text-primary" style="font-size: 1.3rem;"></i>
                    <div>
                      <strong style="font-size: 0.9rem; display: block;">Tender Fraud</strong>
                      <span class="text-muted" style="font-size: 0.75rem;">Rigged bids & kickbacks</span>
                    </div>
                  </div>
                </div>

                <div class="col-md-4">
                  <div class="category-radio-card" onclick="selectCategoryRadio(this, 'Embezzlement & Funds Misuse')">
                    <i class="fa-solid fa-vault text-danger" style="font-size: 1.3rem;"></i>
                    <div>
                      <strong style="font-size: 0.9rem; display: block;">Embezzlement</strong>
                      <span class="text-muted" style="font-size: 0.75rem;">Public fund diversion</span>
                    </div>
                  </div>
                </div>

                <div class="col-md-4">
                  <div class="category-radio-card" onclick="selectCategoryRadio(this, 'Abuse of Power / Nepotism')">
                    <i class="fa-solid fa-user-gear text-info" style="font-size: 1.3rem;"></i>
                    <div>
                      <strong style="font-size: 0.9rem; display: block;">Abuse of Power</strong>
                      <span class="text-muted" style="font-size: 0.75rem;">Illegal orders & favors</span>
                    </div>
                  </div>
                </div>

                <div class="col-md-4">
                  <div class="category-radio-card" onclick="selectCategoryRadio(this, 'Ghost Employees / Payroll Scam')">
                    <i class="fa-solid fa-users-slash text-secondary" style="font-size: 1.3rem;"></i>
                    <div>
                      <strong style="font-size: 0.9rem; display: block;">Ghost Payroll</strong>
                      <span class="text-muted" style="font-size: 0.75rem;">Fictitious employees/claims</span>
                    </div>
                  </div>
                </div>

                <div class="col-md-4">
                  <div class="category-radio-card" onclick="selectCategoryRadio(this, 'Other Corrupt Practices')">
                    <i class="fa-solid fa-circle-exclamation text-emerald" style="font-size: 1.3rem;"></i>
                    <div>
                      <strong style="font-size: 0.9rem; display: block;">Other Violations</strong>
                      <span class="text-muted" style="font-size: 0.75rem;">Statutory code breaches</span>
                    </div>
                  </div>
                </div>
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
              <p class="text-muted mb-4" style="font-size: 0.88rem;">Detail who was involved, dates, demanded sums, and exact circumstance.</p>

              <div class="row g-3 mb-3">
                <div class="col-md-8">
                  <label class="form-label" style="font-size: 0.85rem; font-weight: 600;">Complaint Title / Summary *</label>
                  <input type="text" class="form-control" id="complaintTitle" placeholder="e.g. Unlawful $5,000 bribery demand for shipping cargo clearance stamp" value="Bribery demand for shipping container clearance certificate">
                </div>

                <div class="col-md-4">
                  <label class="form-label" style="font-size: 0.85rem; font-weight: 600;">Incident Date Approx *</label>
                  <input type="date" class="form-control" id="incidentDate" value="2026-08-14">
                </div>
              </div>

              <div class="row g-3 mb-3">
                <div class="col-md-6">
                  <label class="form-label" style="font-size: 0.85rem; font-weight: 600;">Accused Official(s) Name & Title (If Known)</label>
                  <input type="text" class="form-control" id="accusedOfficials" placeholder="e.g. Officer J. Doe, Assistant Port Examiner" value="Deputy Customs Inspector K. Vance">
                </div>

                <div class="col-md-6">
                  <label class="form-label" style="font-size: 0.85rem; font-weight: 600;">Estimated Demanded Amount / Value ($)</label>
                  <div class="input-group">
                    <span class="input-group-text">$</span>
                    <input type="number" class="form-control font-monospace" id="bribeAmount" placeholder="e.g. 5000" value="5000">
                  </div>
                </div>
              </div>

              <div class="mb-4">
                <label class="form-label" style="font-size: 0.85rem; font-weight: 600;">Chronological Narrative & Specific Demands *</label>
                <textarea class="form-control" id="narrativeText" rows="5" placeholder="State clear facts: date, time, location, exact spoken words, bank accounts mentioned, vehicle numbers or witnesses present...">On August 14, 2026, during routine clearance for shipment docket #CT-8942, the inspector withheld the clearance seal and explicitly stated in private office that a cash payment of $5,000 was required before the inspection sign-off would be logged.</textarea>
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
              <h3 class="mb-2" style="font-size: 1.25rem;">Encrypted Evidence Vault</h3>
              <p class="text-muted mb-4" style="font-size: 0.88rem;">Upload audio records, images, invoices, transcripts, or video files. Metadata is stripped client-side.</p>

              <div class="dropzone-box mb-3" onclick="triggerFileInput()">
                <input type="file" id="evidenceFileInput" multiple style="display: none;" onchange="handleEvidenceSelect(event)">
                <div style="font-size: 2.5rem; color: var(--primary); margin-bottom: 0.75rem;">
                  <i class="fa-solid fa-cloud-arrow-up"></i>
                </div>
                <h5 style="font-size: 1.05rem; margin-bottom: 0.25rem;">Click to browse or Drag & Drop evidence files</h5>
                <p class="text-muted mb-0" style="font-size: 0.8rem;">
                  Supported: PDF, JPG, PNG, MP3, MP4, DOCX up to 50MB each.
                </p>
              </div>

              <div class="p-3 rounded-4 bg-body-tertiary border mb-4">
                <div class="d-flex align-items-center justify-content-between mb-2">
                  <span style="font-size: 0.85rem; font-weight: 700;">Uploaded Evidence Bundle (2 files)</span>
                  <span class="badge bg-success-subtle text-success font-monospace" style="font-size: 0.72rem;">
                    <i class="fa-solid fa-shield-virus me-1"></i> EXIF Stripped & SHA-256 Hashed
                  </span>
                </div>

                <div class="d-flex flex-column gap-2" id="evidenceFileList">
                  <div class="d-flex align-items-center justify-content-between p-2 rounded-3 bg-body border">
                    <div class="d-flex align-items-center gap-2">
                      <i class="fa-solid fa-file-audio text-warning"></i>
                      <div>
                        <div style="font-size: 0.85rem; font-weight: 600;">audio_recording_demands_Aug14.mp3</div>
                        <div style="font-size: 0.72rem; color: var(--text-muted);">4.2 MB • Hash: 0x8a92...e41b</div>
                      </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-link text-danger p-0" title="Remove"><i class="fa-solid fa-trash"></i></button>
                  </div>

                  <div class="d-flex align-items-center justify-content-between p-2 rounded-3 bg-body border">
                    <div class="d-flex align-items-center gap-2">
                      <i class="fa-solid fa-file-pdf text-danger"></i>
                      <div>
                        <div style="font-size: 0.85rem; font-weight: 600;">shipping_manifest_withheld_notice.pdf</div>
                        <div style="font-size: 0.72rem; color: var(--text-muted);">1.8 MB • Hash: 0x3f1c...99d2</div>
                      </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-link text-danger p-0" title="Remove"><i class="fa-solid fa-trash"></i></button>
                  </div>
                </div>
              </div>

              <div class="d-flex justify-content-between">
                <button type="button" class="btn-ccms btn-ccms-secondary" onclick="goToStep(3)">
                  <i class="fa-solid fa-arrow-left"></i> Back
                </button>
                <button type="button" class="btn-ccms btn-ccms-primary" onclick="goToStep(5)">
                  Review & Cryptographic Seal <i class="fa-solid fa-arrow-right"></i>
                </button>
              </div>
            </div>

            <!-- STEP 5: Review & Seal -->
            <div class="wizard-pane" id="paneStep5">
              <h3 class="mb-2" style="font-size: 1.25rem;">Final Review & Cryptographic Seal</h3>
              <p class="text-muted mb-4" style="font-size: 0.88rem;">Confirm report accuracy before irreversible transmission to vigilance cold-storage nodes.</p>

              <div class="p-3 rounded-4 bg-body-tertiary border mb-4" style="font-size: 0.88rem;">
                <div class="row g-3 mb-2">
                  <div class="col-md-6">
                    <span class="text-muted d-block" style="font-size: 0.78rem;">Protection Mode:</span>
                    <strong id="reviewMode" class="text-cyan"><i class="fa-solid fa-user-ninja me-1"></i> 100% Anonymous Whistleblower</strong>
                  </div>
                  <div class="col-md-6">
                    <span class="text-muted d-block" style="font-size: 0.78rem;">Target Department:</span>
                    <strong id="reviewDept">Revenue & Tax Administration</strong>
                  </div>
                </div>

                <div class="row g-3 mb-2">
                  <div class="col-md-6">
                    <span class="text-muted d-block" style="font-size: 0.78rem;">Offense Category:</span>
                    <strong id="reviewCat">Bribery / Extortion</strong>
                  </div>
                  <div class="col-md-6">
                    <span class="text-muted d-block" style="font-size: 0.78rem;">Demanded Amount:</span>
                    <strong id="reviewAmount" class="font-monospace text-danger">$5,000 USD</strong>
                  </div>
                </div>

                <div class="mb-2">
                  <span class="text-muted d-block" style="font-size: 0.78rem;">Subject:</span>
                  <strong id="reviewSubject">Bribery demand for shipping container clearance certificate</strong>
                </div>
              </div>

              <div class="form-check mb-4">
                <input class="form-check-input" type="checkbox" id="affirmTruthCheck" checked>
                <label class="form-check-label" for="affirmTruthCheck" style="font-size: 0.82rem; color: var(--text-muted);">
                  I affirm that this report is submitted in good faith and the attached records represent authentic evidence.
                </label>
              </div>

              <div class="d-flex justify-content-between">
                <button type="button" class="btn-ccms btn-ccms-secondary" onclick="goToStep(4)">
                  <i class="fa-solid fa-arrow-left"></i> Back
                </button>
                <button type="submit" class="btn-ccms btn-ccms-primary" style="background: linear-gradient(135deg, var(--emerald) 0%, #059669 100%);">
                  <i class="fa-solid fa-lock"></i> Submit Encrypted Report & Generate Token
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
  let selectedCategory = 'Bribery & Extortion';

  function goToStep(step) {
    if (step < 1 || step > 5) return;
    currentStep = step;

    // Update nodes
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

  function selectCategoryRadio(card, catName) {
    document.querySelectorAll('.category-radio-card').forEach(c => {
      if (c.id !== 'optAnon' && c.id !== 'optIdentified') c.classList.remove('selected');
    });
    card.classList.add('selected');
    selectedCategory = catName;
  }

  function triggerFileInput() {
    document.getElementById('evidenceFileInput').click();
  }

  function handleEvidenceSelect(e) {
    const files = e.target.files;
    if (files.length > 0) {
      showCitizenToast(`${files.length} file(s) sanitized & encrypted!`, 'success');
    }
  }

  function updateReviewSummary() {
    document.getElementById('reviewMode').innerHTML = selectedMode === 'anonymous'
      ? '<i class="fa-solid fa-user-ninja me-1"></i> 100% Anonymous Whistleblower'
      : '<i class="fa-solid fa-id-card me-1"></i> Verified Citizen Profile';
    document.getElementById('reviewDept').textContent = document.getElementById('deptSelect').value;
    document.getElementById('reviewCat').textContent = selectedCategory;
    document.getElementById('reviewAmount').textContent = '$' + (document.getElementById('bribeAmount').value || '0') + ' USD';
    document.getElementById('reviewSubject').textContent = document.getElementById('complaintTitle').value;
  }

  function handleFinalSubmit(e) {
    e.preventDefault();
    const token = 'CCMS-2026-' + Math.floor(1000 + Math.random() * 9000);

    Swal.fire({
      icon: 'success',
      title: 'Complaint Sealed & Dispatched!',
      html: `
        <p style="font-size:0.9rem;color:#64748b;">Your incident dossier has been cryptographically signed and routed to Anti-Corruption Cell #03.</p>
        <div style="background:#f1f5f9;padding:12px;border-radius:12px;margin:15px 0;font-family:monospace;font-size:1.15rem;font-weight:700;color:#4f46e5;border:1px dashed #6366f1;">
          ${token}
        </div>
        <p style="font-size:0.8rem;color:#94a3b8;">Copy and safeguard this Token. You will need it to track investigation milestones anonymously.</p>
      `,
      showCancelButton: true,
      confirmButtonText: '<i class="fa-solid fa-radar me-1"></i> Track Case Live',
      cancelButtonText: '<i class="fa-solid fa-download me-1"></i> Download Receipt',
      confirmButtonColor: '#4f46e5',
      cancelButtonColor: '#0ea5e9'
    }).then((res) => {
      if (res.isConfirmed) {
        window.location.href = `track-complaint.php?case=${token}`;
      } else {
        showCitizenToast('Receipt generated! Redirecting to tracking...', 'info');
        setTimeout(() => {
          window.location.href = `track-complaint.php?case=${token}`;
        }, 1500);
      }
    });
  }
</script>
</body>
</html>
