<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Submitted Complaints — CCMS Citizen</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&family=Space+Grotesk:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
  <link rel="stylesheet" href="assets/css/citizen.css">
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

      <a href="file-complaint.php" class="sb-link">
        <div class="icon-wrap"><i class="fa-solid fa-file-circle-plus"></i></div>
        <span>Lodge Complaint</span>
        <span class="sb-badge emerald">New</span>
      </a>

      <a href="my-complaints.php" class="sb-link active">
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
        <span class="fw-bold" style="font-size: 0.95rem;">My Submitted Records</span>
      </div>

      <div class="nav-actions">
        <button class="nav-icon-btn" id="themeToggle" title="Toggle Theme">
          <i class="fa-solid fa-moon" id="themeIcon"></i>
        </button>
        <a href="file-complaint.php" class="btn-ccms btn-ccms-primary btn-sm">
          <i class="fa-solid fa-plus"></i> New Complaint
        </a>
      </div>
    </nav>

    <main class="content-body">
      <div class="page-header">
        <div>
          <h1 class="page-title">My Filed Complaints Docket</h1>
          <p class="page-subtitle">Historical archive of all your registered reports, investigation status notes, and official disposal orders.</p>
        </div>

        <div class="d-flex gap-2">
          <input type="text" id="citizenSearch" class="form-control form-control-sm" placeholder="Filter by Token or Dept..." style="width: 240px;" onkeyup="filterCitizenTable()">
          <select class="form-select form-select-sm" id="statusFilter" style="width: 170px;" onchange="filterCitizenTable()">
            <option value="">All Statuses</option>
            <option value="Investigation">Under Investigation</option>
            <option value="Action Taken">Action Taken / Resolved</option>
          </select>
        </div>
      </div>

      <!-- Complaints Data Grid -->
      <div class="card-box">
        <div class="table-responsive">
          <table class="table align-middle" id="complaintsTable" style="color: var(--text-main);">
            <thead style="background: var(--bg-surface-2); font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted);">
              <tr>
                <th style="padding: 1rem;">Docket Token</th>
                <th>Target Department</th>
                <th>Category</th>
                <th>Subject Summary</th>
                <th>Filing Date</th>
                <th>Current Status</th>
                <th class="text-end" style="padding-right: 1rem;">Actions</th>
              </tr>
            </thead>
            <tbody style="font-size: 0.9rem;">
              <tr>
                <td style="padding: 1rem;">
                  <span class="font-monospace fw-bold text-primary">CCMS-2026-9082</span>
                  <div style="font-size: 0.72rem; color: var(--cyan);"><i class="fa-solid fa-user-ninja"></i> Anonymous Mode</div>
                </td>
                <td>
                  <strong>Revenue & Customs</strong>
                  <div style="font-size: 0.75rem; color: var(--text-muted);">Port Assessment Cell</div>
                </td>
                <td><span class="badge bg-warning-subtle text-warning font-monospace">Bribery</span></td>
                <td>Bribery demand for clearance certificate</td>
                <td>Aug 14, 2026</td>
                <td><span class="badge-status status-investigation"><i class="fa-solid fa-spinner fa-spin"></i> Investigation Active</span></td>
                <td class="text-end" style="padding-right: 1rem;">
                  <div class="d-inline-flex align-items-center gap-2">
                    <a href="track-complaint.php?case=CCMS-2026-9082" class="btn-cyber-action btn-cyber-primary" title="Live Investigation Radar">
                      <i class="fa-solid fa-location-crosshairs"></i> Track Live
                    </a>
                    <button class="btn-cyber-action btn-cyber-secondary btn-icon-only" title="Download Official Receipt" onclick="downloadReceipt('CCMS-2026-9082')">
                      <i class="fa-solid fa-file-arrow-down"></i>
                    </button>
                  </div>
                </td>
              </tr>

              <tr>
                <td style="padding: 1rem;">
                  <span class="font-monospace fw-bold text-primary">CCMS-2026-4150</span>
                  <div style="font-size: 0.72rem; color: var(--text-muted);"><i class="fa-solid fa-id-card"></i> Verified Citizen</div>
                </td>
                <td>
                  <strong>Public Works Dept (PWD)</strong>
                  <div style="font-size: 0.75rem; color: var(--text-muted);">Highway Tender Division</div>
                </td>
                <td><span class="badge bg-danger-subtle text-danger font-monospace">Tender Fraud</span></td>
                <td>Kickbacks in asphalt supply contract</td>
                <td>Jul 28, 2026</td>
                <td><span class="badge-status status-resolved"><i class="fa-solid fa-check"></i> Action Taken</span></td>
                <td class="text-end" style="padding-right: 1rem;">
                  <div class="d-inline-flex align-items-center gap-2">
                    <a href="track-complaint.php?case=CCMS-2026-4150" class="btn-cyber-action btn-cyber-primary" title="Live Investigation Radar">
                      <i class="fa-solid fa-location-crosshairs"></i> Track Live
                    </a>
                    <button class="btn-cyber-action btn-cyber-secondary btn-icon-only" title="Download Official Receipt" onclick="downloadReceipt('CCMS-2026-4150')">
                      <i class="fa-solid fa-file-arrow-down"></i>
                    </button>
                  </div>
                </td>
              </tr>

              <tr>
                <td style="padding: 1rem;">
                  <span class="font-monospace fw-bold text-primary">CCMS-2026-1189</span>
                  <div style="font-size: 0.72rem; color: var(--cyan);"><i class="fa-solid fa-user-ninja"></i> Anonymous Mode</div>
                </td>
                <td>
                  <strong>Municipal Corporation</strong>
                  <div style="font-size: 0.75rem; color: var(--text-muted);">Zoning & Permissions</div>
                </td>
                <td><span class="badge bg-info-subtle text-info font-monospace">Extortion</span></td>
                <td>Illegal sanction fee extortion</td>
                <td>Jun 12, 2026</td>
                <td><span class="badge-status status-resolved"><i class="fa-solid fa-check"></i> Officer Suspended</span></td>
                <td class="text-end" style="padding-right: 1rem;">
                  <div class="d-inline-flex align-items-center gap-2">
                    <a href="track-complaint.php?case=CCMS-2026-1189" class="btn-cyber-action btn-cyber-primary" title="Live Investigation Radar">
                      <i class="fa-solid fa-location-crosshairs"></i> Track Live
                    </a>
                    <button class="btn-cyber-action btn-cyber-secondary btn-icon-only" title="Download Official Receipt" onclick="downloadReceipt('CCMS-2026-1189')">
                      <i class="fa-solid fa-file-arrow-down"></i>
                    </button>
                  </div>
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
<script src="assets/js/citizen.js"></script>
<script>
  function filterCitizenTable() {
    const search = document.getElementById('citizenSearch').value.toLowerCase();
    const status = document.getElementById('statusFilter').value.toLowerCase();
    const rows = document.querySelectorAll('#complaintsTable tbody tr');

    rows.forEach(row => {
      const text = row.innerText.toLowerCase();
      const matchSearch = !search || text.includes(search);
      const matchStatus = !status || text.includes(status);
      row.style.display = matchSearch && matchStatus ? '' : 'none';
    });
  }

  function downloadReceipt(token) {
    Swal.fire({
      icon: 'info',
      title: `Generate Official Docket Receipt`,
      text: `Compiling cryptographic filing receipt for Docket #${token}...`,
      timer: 1500,
      showConfirmButton: false
    }).then(() => {
      showCitizenToast(`Receipt for ${token} downloaded!`, 'success');
    });
  }
</script>
</body>
</html>
