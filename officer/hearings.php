<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Hearings & Summons Tribunal — CCMS Officer</title>
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
      <a href="index.php" class="sb-link">
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
      <a href="hearings.php" class="sb-link active">
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
        <div class="officer-avatar">KV</div>
        <div class="officer-info">
          <div class="officer-name">Insp. K. Vance</div>
          <div class="officer-badge-no">BADGE #AC-819 (Cell-04)</div>
        </div>
      </div>
    </div>
  </aside>

  <!-- ═══════════════════ MAIN CONTENT ═══════════════════ -->
  <div id="main-content">
    <nav class="top-navbar">
      <div class="d-flex align-items-center gap-3">
        <button class="btn-toggle" id="sidebarToggle"><i class="fa-solid fa-bars-staggered"></i></button>
        <span class="fw-bold" style="font-size: 0.95rem;">Tribunal Summons & Hearing Calendar</span>
      </div>

      <div class="nav-actions">
        <button class="nav-icon-btn" id="themeToggle" title="Toggle Theme">
          <i class="fa-solid fa-moon" id="themeIcon"></i>
        </button>
        <button class="btn-officer btn-officer-primary btn-sm" onclick="showScheduleHearingModal()">
          <i class="fa-solid fa-plus"></i> Schedule New Summons
        </button>
      </div>
    </nav>

    <main class="content-body">
      <div class="page-header">
        <div>
          <h1 class="page-title">Summons & Deposition Hearings Calendar</h1>
          <p class="page-subtitle">Schedule, track, and record statutory witness appearances and accused depositions before vigilance tribunal benches.</p>
        </div>
      </div>

      <div class="card-box">
        <div class="table-responsive">
          <table class="table align-middle" style="color: var(--text-main);">
            <thead style="background: var(--bg-surface-2); font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted);">
              <tr>
                <th style="padding: 1rem;">Summons Token</th>
                <th>Case Docket</th>
                <th>Target Party</th>
                <th>Hearing Venue</th>
                <th>Date & Time</th>
                <th>Status</th>
                <th class="text-end" style="padding-right: 1rem;">Action</th>
              </tr>
            </thead>
            <tbody style="font-size: 0.9rem;">
              <tr>
                <td style="padding: 1rem;">
                  <span class="font-monospace fw-bold text-primary">SUM-8941</span>
                </td>
                <td><span class="font-monospace fw-bold">CCMS-2026-9082</span></td>
                <td>
                  <strong>Assistant Port Examiner K. Vance</strong>
                  <div style="font-size: 0.75rem; color: var(--text-muted);">Accused Party Deposition</div>
                </td>
                <td>Tribunal Bench Room #3B</td>
                <td><strong class="text-danger font-monospace">TODAY • 02:30 PM</strong></td>
                <td><span class="badge bg-warning-subtle text-warning font-monospace">Summons Served</span></td>
                <td class="text-end" style="padding-right: 1rem;">
                  <a href="case-investigation.php?case=CCMS-2026-9082" class="btn-cyber-action btn-cyber-primary">
                    <i class="fa-solid fa-folder-open"></i> Open Case Room <i class="fa-solid fa-chevron-right ms-1" style="font-size: 0.7rem; opacity: 0.8;"></i>
                  </a>
                </td>
              </tr>

              <tr>
                <td style="padding: 1rem;">
                  <span class="font-monospace fw-bold text-primary">SUM-8942</span>
                </td>
                <td><span class="font-monospace fw-bold">CCMS-2026-9082</span></td>
                <td>
                  <strong>M. Farooq (Falcon Shipping Logistics)</strong>
                  <div style="font-size: 0.75rem; color: var(--text-muted);">Material Eyewitness</div>
                </td>
                <td>Hearing Chamber #1</td>
                <td><strong class="text-warning font-monospace">TOMORROW • 11:00 AM</strong></td>
                <td><span class="badge bg-info-subtle text-info font-monospace">Confirmed Appearance</span></td>
                <td class="text-end" style="padding-right: 1rem;">
                  <a href="case-investigation.php?case=CCMS-2026-9082" class="btn-cyber-action btn-cyber-primary">
                    <i class="fa-solid fa-folder-open"></i> Open Case Room <i class="fa-solid fa-chevron-right ms-1" style="font-size: 0.7rem; opacity: 0.8;"></i>
                  </a>
                </td>
              </tr>

              <tr>
                <td style="padding: 1rem;">
                  <span class="font-monospace fw-bold text-primary">SUM-8810</span>
                </td>
                <td><span class="font-monospace fw-bold">CCMS-2026-9114</span></td>
                <td>
                  <strong>Sub-Registrar Zone 4 Clerk</strong>
                  <div style="font-size: 0.75rem; color: var(--text-muted);">Record Custodian</div>
                </td>
                <td>Main Vigilance Chamber</td>
                <td><span class="font-monospace text-muted">Aug 22, 2026 • 03:00 PM</span></td>
                <td><span class="badge bg-secondary-subtle text-secondary font-monospace">Notice Dispatched</span></td>
                <td class="text-end" style="padding-right: 1rem;">
                  <a href="case-investigation.php?case=CCMS-2026-9114" class="btn-cyber-action btn-cyber-primary">
                    <i class="fa-solid fa-folder-open"></i> Open Case Room <i class="fa-solid fa-chevron-right ms-1" style="font-size: 0.7rem; opacity: 0.8;"></i>
                  </a>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </main>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="assets/js/officer.js"></script>
<script>
  function showScheduleHearingModal() {
    Swal.fire({
      title: 'Issue Statutory Summons',
      html: `
        <input id="sumTarget" class="swal2-input" placeholder="Summons Target Official/Witness">
        <input id="sumDate" type="date" class="swal2-input" value="2026-08-20">
        <input id="sumVenue" class="swal2-input" placeholder="Hearing Tribunal Courtroom" value="Tribunal Bench Room #3B">
      `,
      confirmButtonText: 'Issue Summons',
      confirmButtonColor: '#0284c7',
      showCancelButton: true
    }).then((res) => {
      if (res.isConfirmed) {
        showOfficerToast('Statutory Summons dispatched via encrypted registry!', 'success');
      }
    });
  }
</script>
</body>
</html>
