<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../db.php';
$officer_name = htmlspecialchars($_SESSION['officer_name'] ?? $_SESSION['name'] ?? 'Investigating Officer');
$officer_desig = htmlspecialchars($_SESSION['designation'] ?? 'Anti-Corruption Inspector');
$initials = strtoupper(substr($officer_name, 0, 2));
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Officer Command Center — CCMS Investigation Bureau</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&family=Space+Grotesk:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
  <link rel="stylesheet" href="assets/css/officer.css">
</head>
<body>

  <!-- Ambient Visual Glow Blobs -->
  <div class="ambient-glow-1"></div>
  <div class="ambient-glow-2"></div>
  <div class="ambient-glow-3"></div>
  <div class="grid-overlay"></div>

<div id="wrapper">
  <!-- ═══════════════════ OFFICER SIDEBAR ═══════════════════ -->
  <aside id="sidebar">
    <div class="sb-brand">
      <div class="sb-logo"><i class="fa-solid fa-user-shield"></i></div>
      <div>
        <div class="sb-title">CCMS</div>
        <div class="sb-sub">Officer Command</div>
      </div>
    </div>

    <nav class="sb-nav">
      <div class="sb-section-label">Investigation Ops</div>
      <a href="index.php" class="sb-link active">
        <div class="icon-wrap"><i class="fa-solid fa-gauge-high"></i></div>
        <span>Command Radar</span>
      </a>

      <a href="my-cases.php" class="sb-link">
        <div class="icon-wrap"><i class="fa-solid fa-folder-tree"></i></div>
        <span>Assigned Docket</span>
        <span class="sb-badge red">4</span>
      </a>

      <a href="case-investigation.php?case=CCMS-2026-9082" class="sb-link">
        <div class="icon-wrap"><i class="fa-solid fa-microscope"></i></div>
        <span>Active Case Console</span>
      </a>

      <div class="sb-section-label">Judicial & Forensics</div>
      <a href="hearings.php" class="sb-link">
        <div class="icon-wrap"><i class="fa-solid fa-gavel"></i></div>
        <span>Hearings & Summons</span>
        <span class="sb-badge amber">2</span>
      </a>

      <a href="evidence-vault.php" class="sb-link">
        <div class="icon-wrap"><i class="fa-solid fa-vault"></i></div>
        <span>Evidence Vault</span>
      </a>

      <div class="sb-section-label">Portals & Oversight</div>
      <a href="profile.php" class="sb-link">
        <div class="icon-wrap"><i class="fa-solid fa-id-badge"></i></div>
        <span>Officer Clearance</span>
      </a>

      <a href="register.php" class="sb-link">
        <div class="icon-wrap"><i class="fa-solid fa-user-plus"></i></div>
        <span>Register New Officer</span>
      </a>

      <a href="../citizen/dashboard.php" class="sb-link">
        <div class="icon-wrap"><i class="fa-solid fa-users"></i></div>
        <span>Citizen Portal</span>
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
      <div class="officer-card">
        <div class="officer-avatar"><?= $initials ?></div>
        <div class="officer-info">
          <div class="officer-name"><?= $officer_name ?></div>
          <div class="officer-badge-no"><?= $officer_desig ?></div>
        </div>
      </div>
    </div>
  </aside>

  <!-- ═══════════════════ MAIN CONTENT ═══════════════════ -->
  <div id="main-content">
    <nav class="top-navbar">
      <div class="d-flex align-items-center gap-3">
        <button class="btn-toggle" id="sidebarToggle"><i class="fa-solid fa-bars-staggered"></i></button>
        <div class="d-none d-md-flex align-items-center gap-2">
          <span class="badge bg-danger-subtle text-danger font-monospace px-3 py-1">
            <i class="fa-solid fa-satellite-dish me-1"></i> ANTI-CORRUPTION VIGILANCE DESK • ACTIVE
          </span>
        </div>
      </div>

      <div class="nav-actions">
        <button class="nav-icon-btn" id="themeToggle" title="Toggle Theme">
          <i class="fa-solid fa-moon" id="themeIcon"></i>
        </button>

        <div class="dropdown">
          <button class="btn-officer btn-officer-secondary dropdown-toggle" data-bs-toggle="dropdown">
            <i class="fa-solid fa-user-shield text-primary"></i> <?= $officer_name ?>
          </button>
          <ul class="dropdown-menu dropdown-menu-end shadow border-0" style="background: var(--bg-surface); border: 1px solid var(--border);">
            <li><a class="dropdown-item" href="profile.php"><i class="fa-solid fa-id-badge me-2"></i> Badge Credentials</a></li>
            <li><a class="dropdown-item" href="register.php"><i class="fa-solid fa-user-plus me-2"></i> Register New Officer</a></li>
            <li><a class="dropdown-item" href="my-cases.php"><i class="fa-solid fa-folder-tree me-2"></i> Active Queue</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger" href="login.php"><i class="fa-solid fa-power-off me-2"></i> Secure Logout</a></li>
          </ul>
        </div>
      </div>
    </nav>

    <main class="content-body">
      <div class="page-header">
        <div>
          <h1 class="page-title">Investigation Command Radar</h1>
          <p class="page-subtitle">Anti-Corruption Bureau • Unit Cell #04 (Customs, Ports & Revenue Vigilance Division)</p>
        </div>

        <div class="d-flex gap-2 flex-wrap">
          <a href="register.php" class="btn-officer btn-officer-secondary">
            <i class="fa-solid fa-user-plus"></i> Register Officer
          </a>
          <a href="hearings.php" class="btn-officer btn-officer-secondary">
            <i class="fa-solid fa-calendar-plus"></i> Schedule Hearing
          </a>
          <a href="case-investigation.php?case=CCMS-2026-9082" class="btn-officer btn-officer-primary">
            <i class="fa-solid fa-magnifying-glass"></i> Open Lead Probe
          </a>
        </div>
      </div>

      <!-- KPI Summary -->
      <div class="kpi-grid">
        <div class="kpi-card blue">
          <div class="kpi-icon"><i class="fa-solid fa-folder-open"></i></div>
          <div class="kpi-value counter-val" data-target="8">8</div>
          <div class="kpi-label">Active Assigned Probes</div>
        </div>

        <div class="kpi-card red">
          <div class="kpi-icon"><i class="fa-solid fa-stopwatch"></i></div>
          <div class="kpi-value counter-val" data-target="2">2</div>
          <div class="kpi-label">SLA Breach Warnings (&lt;48h)</div>
        </div>

        <div class="kpi-card amber">
          <div class="kpi-icon"><i class="fa-solid fa-gavel"></i></div>
          <div class="kpi-value counter-val" data-target="3">3</div>
          <div class="kpi-label">Summons Scheduled</div>
        </div>

        <div class="kpi-card emerald">
          <div class="kpi-icon"><i class="fa-solid fa-file-circle-check"></i></div>
          <div class="kpi-value counter-val" data-target="34">34</div>
          <div class="kpi-label">Charge Sheets Filed</div>
        </div>
      </div>

      <!-- Urgent Active Cases Table -->
      <div class="card-box">
        <div class="card-header-flex">
          <div class="card-title">
            <i class="fa-solid fa-triangle-exclamation text-danger"></i> Priority Investigation Docket
          </div>
          <a href="my-cases.php" class="btn-officer btn-officer-secondary btn-sm" style="font-size: 0.8rem;">
            View All Cases (8)
          </a>
        </div>

        <div class="table-responsive">
          <table class="table align-middle" style="color: var(--text-main);">
            <thead style="background: var(--bg-surface-2); font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted);">
              <tr>
                <th style="padding: 1rem;">Docket Token</th>
                <th>Priority</th>
                <th>Target Entity & Location</th>
                <th>Offense Type</th>
                <th>Evidence Items</th>
                <th>SLA Statutory Clock</th>
                <th class="text-end" style="padding-right: 1rem;">Investigation Action</th>
              </tr>
            </thead>
            <tbody style="font-size: 0.9rem;">
              <tr>
                <td style="padding: 1rem;">
                  <span class="font-monospace fw-bold text-primary">CCMS-2026-9082</span>
                  <div style="font-size: 0.72rem; color: var(--text-muted);">Whistleblower Shield Active</div>
                </td>
                <td><span class="priority-pill priority-critical">CRITICAL</span></td>
                <td>
                  <strong>Revenue & Customs Port Gate</strong>
                  <div style="font-size: 0.75rem; color: var(--text-muted);">Assistant Port Examiner Office</div>
                </td>
                <td><span class="badge bg-warning-subtle text-warning font-monospace">$5,000 Bribery Extortion</span></td>
                <td><span class="badge bg-body-tertiary border font-monospace"><i class="fa-solid fa-paperclip"></i> 2 Files (Audio+PDF)</span></td>
                <td><span class="text-danger fw-bold font-monospace"><i class="fa-solid fa-hourglass-half me-1"></i> 35h Left</span></td>
                <td class="text-end" style="padding-right: 1rem;">
                  <a href="case-investigation.php?case=CCMS-2026-9082" class="btn-cyber-action btn-cyber-primary">
                    <i class="fa-solid fa-microscope"></i> Conduct Probe
                  </a>
                </td>
              </tr>

              <tr>
                <td style="padding: 1rem;">
                  <span class="font-monospace fw-bold text-primary">CCMS-2026-9114</span>
                  <div style="font-size: 0.72rem; color: var(--text-muted);">Verified Citizen Report</div>
                </td>
                <td><span class="priority-pill priority-high">HIGH</span></td>
                <td>
                  <strong>Urban Land Registry</strong>
                  <div style="font-size: 0.75rem; color: var(--text-muted);">Sub-Registrar Zone 4</div>
                </td>
                <td><span class="badge bg-danger-subtle text-danger font-monospace">Land Record Forgery</span></td>
                <td><span class="badge bg-body-tertiary border font-monospace"><i class="fa-solid fa-paperclip"></i> 4 Deeds (PDF)</span></td>
                <td><span class="text-warning fw-bold font-monospace"><i class="fa-solid fa-hourglass-half me-1"></i> 48h Left</span></td>
                <td class="text-end" style="padding-right: 1rem;">
                  <a href="case-investigation.php?case=CCMS-2026-9114" class="btn-cyber-action btn-cyber-primary">
                    <i class="fa-solid fa-microscope"></i> Conduct Probe
                  </a>
                </td>
              </tr>

              <tr>
                <td style="padding: 1rem;">
                  <span class="font-monospace fw-bold text-primary">CCMS-2026-8802</span>
                  <div style="font-size: 0.72rem; color: var(--text-muted);">Anonymous Whistleblower</div>
                </td>
                <td><span class="priority-pill priority-medium">MEDIUM</span></td>
                <td>
                  <strong>Municipal Building Approvals</strong>
                  <div style="font-size: 0.75rem; color: var(--text-muted);">Commercial Permit Division</div>
                </td>
                <td><span class="badge bg-info-subtle text-info font-monospace">Expediting Fee Demands</span></td>
                <td><span class="badge bg-body-tertiary border font-monospace"><i class="fa-solid fa-paperclip"></i> 1 Photo (PNG)</span></td>
                <td><span class="text-success fw-bold font-monospace"><i class="fa-solid fa-clock me-1"></i> 5 Days Left</span></td>
                <td class="text-end" style="padding-right: 1rem;">
                  <a href="case-investigation.php?case=CCMS-2026-8802" class="btn-cyber-action btn-cyber-primary">
                    <i class="fa-solid fa-microscope"></i> Conduct Probe
                  </a>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Hearings Calendar & Quick Dispatches -->
      <div class="row g-4">
        <div class="col-lg-6">
          <div class="card-box h-100 mb-0">
            <div class="card-title mb-3">
              <i class="fa-solid fa-gavel text-primary"></i> Upcoming Hearing Summons & Depositions
            </div>
            
            <div class="d-flex flex-column gap-3">
              <div class="p-3 rounded-4 bg-body-tertiary border d-flex align-items-center justify-content-between">
                <div>
                  <span class="badge bg-primary-subtle text-primary font-monospace mb-1" style="font-size: 0.72rem;">TODAY • 02:30 PM</span>
                  <h5 style="font-size: 0.92rem; margin-bottom: 0.2rem;">Deposition: Accused Port Examiner Vance</h5>
                  <p class="text-muted mb-0" style="font-size: 0.78rem;">Courtroom #3B • Docket #CCMS-2026-9082</p>
                </div>
                <a href="hearings.php" class="btn-cyber-action btn-cyber-primary">
                  <i class="fa-solid fa-folder-open"></i> Summons Dossier
                </a>
              </div>

              <div class="p-3 rounded-4 bg-body-tertiary border d-flex align-items-center justify-content-between">
                <div>
                  <span class="badge bg-warning-subtle text-warning font-monospace mb-1" style="font-size: 0.72rem;">TOMORROW • 11:00 AM</span>
                  <h5 style="font-size: 0.92rem; margin-bottom: 0.2rem;">Witness Hearing: Falcon Shipping Representative</h5>
                  <p class="text-muted mb-0" style="font-size: 0.78rem;">Hearing Chamber #1 • Docket #CCMS-2026-9082</p>
                </div>
                <a href="hearings.php" class="btn-cyber-action btn-cyber-secondary">
                  <i class="fa-solid fa-folder-open"></i> Summons Dossier
                </a>
              </div>
            </div>
          </div>
        </div>

        <div class="col-lg-6">
          <div class="card-box h-100 mb-0">
            <div class="card-title mb-3">
              <i class="fa-solid fa-shield-halved text-cyan"></i> Officer Clearance & Jurisdictions
            </div>
            
            <div class="row g-3" style="font-size: 0.85rem;">
              <div class="col-6">
                <div class="p-3 rounded-3 bg-body-tertiary border">
                  <span class="text-muted d-block" style="font-size: 0.72rem;">Security Clearance</span>
                  <strong class="text-primary font-monospace">LEVEL-3 FORENSIC</strong>
                </div>
              </div>
              <div class="col-6">
                <div class="p-3 rounded-3 bg-body-tertiary border">
                  <span class="text-muted d-block" style="font-size: 0.72rem;">Active Caseload Cap</span>
                  <strong class="text-success font-monospace">8 / 12 MAXIMUM</strong>
                </div>
              </div>
              <div class="col-6">
                <div class="p-3 rounded-3 bg-body-tertiary border">
                  <span class="text-muted d-block" style="font-size: 0.72rem;">Avg Resolution Time</span>
                  <strong class="font-monospace">6.4 Business Days</strong>
                </div>
              </div>
              <div class="col-6">
                <div class="p-3 rounded-3 bg-body-tertiary border">
                  <span class="text-muted d-block" style="font-size: 0.72rem;">Conviction / Disciplinary Ratio</span>
                  <strong class="text-emerald font-monospace">94.2% Sustained</strong>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="assets/js/officer.js"></script>
</body>
</html>
