<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Assigned Investigation Docket — CCMS Officer</title>
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

      <a href="my-cases.php" class="sb-link active">
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
        <span class="fw-bold" style="font-size: 0.95rem;">Officer Caseload Repository</span>
      </div>

      <div class="nav-actions">
        <button class="nav-icon-btn" id="themeToggle" title="Toggle Theme">
          <i class="fa-solid fa-moon" id="themeIcon"></i>
        </button>
        <button class="btn-officer btn-officer-secondary btn-sm" onclick="showOfficerToast('Dossier summary exported to PDF', 'success')">
          <i class="fa-solid fa-file-pdf text-danger"></i> Export Queue
        </button>
      </div>
    </nav>

    <main class="content-body">
      <div class="page-header">
        <div>
          <h1 class="page-title">Assigned Corruption Cases Docket</h1>
          <p class="page-subtitle">Manage active investigation queues, record evidence corroboration, and submit formal charge sheets.</p>
        </div>

        <div class="d-flex gap-2 flex-wrap">
          <input type="text" id="officerSearch" class="form-control form-control-sm" placeholder="Search Token or Entity..." style="width: 220px;" onkeyup="filterOfficerTable()">
          
          <select class="form-select form-select-sm" id="prioFilter" style="width: 150px;" onchange="filterOfficerTable()">
            <option value="">All Priorities</option>
            <option value="CRITICAL">Critical Priority</option>
            <option value="HIGH">High Priority</option>
            <option value="MEDIUM">Medium Priority</option>
          </select>

          <select class="form-select form-select-sm" id="statusFilter" style="width: 170px;" onchange="filterOfficerTable()">
            <option value="">All Statuses</option>
            <option value="Investigation">Active Investigation</option>
            <option value="Triage">Initial Triage</option>
            <option value="Resolved">Charge Sheet Filed</option>
          </select>
        </div>
      </div>

      <!-- Data Table -->
      <div class="card-box">
        <div class="table-responsive">
          <table class="table align-middle" id="officerCaseTable" style="color: var(--text-main);">
            <thead style="background: var(--bg-surface-2); font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted);">
              <tr>
                <th style="padding: 1rem;">Docket Token</th>
                <th>Priority</th>
                <th>Target Department & Division</th>
                <th>Complainant Mode</th>
                <th>Alleged Amount</th>
                <th>Status</th>
                <th>SLA Statutory Timer</th>
                <th class="text-end" style="padding-right: 1rem;">Actions</th>
              </tr>
            </thead>
            <tbody style="font-size: 0.9rem;">
              <tr>
                <td style="padding: 1rem;">
                  <span class="font-monospace fw-bold text-primary">CCMS-2026-9082</span>
                  <div style="font-size: 0.72rem; color: var(--text-muted);">Assigned: Aug 14, 2026</div>
                </td>
                <td><span class="priority-pill priority-critical">CRITICAL</span></td>
                <td>
                  <strong>Revenue & Customs</strong>
                  <div style="font-size: 0.75rem; color: var(--text-muted);">Port Assessment Cell (Vance)</div>
                </td>
                <td><span class="badge bg-secondary-subtle text-secondary font-monospace"><i class="fa-solid fa-user-ninja"></i> Anonymous</span></td>
                <td class="font-monospace text-danger fw-bold">$5,000</td>
                <td><span class="badge bg-info-subtle text-info font-monospace">Investigation Active</span></td>
                <td><span class="text-danger fw-bold font-monospace"><i class="fa-solid fa-hourglass-half me-1"></i> 35h Left</span></td>
                <td style="padding-right: 1rem;">
                  <div class="d-flex gap-2 align-items-center justify-content-end flex-nowrap">
                    <a href="case-investigation.php?case=CCMS-2026-9082" class="btn-cyber-action btn-cyber-primary" title="Open Forensic Console">
                      <i class="fa-solid fa-microscope"></i> Probe
                    </a>
                    <button class="btn-cyber-action btn-cyber-secondary" onclick="quickStatusModal('CCMS-2026-9082')" title="Change Status">
                      <i class="fa-solid fa-pen-to-square"></i>
                    </button>
                  </div>
                </td>
              </tr>

              <tr>
                <td style="padding: 1rem;">
                  <span class="font-monospace fw-bold text-primary">CCMS-2026-9114</span>
                  <div style="font-size: 0.72rem; color: var(--text-muted);">Assigned: Aug 13, 2026</div>
                </td>
                <td><span class="priority-pill priority-high">HIGH</span></td>
                <td>
                  <strong>Urban Land Registry</strong>
                  <div style="font-size: 0.75rem; color: var(--text-muted);">Zone 4 Commercial Deeds</div>
                </td>
                <td><span class="badge bg-primary-subtle text-primary font-monospace"><i class="fa-solid fa-id-card"></i> Verified Citizen</span></td>
                <td class="font-monospace text-danger fw-bold">$22,000</td>
                <td><span class="badge bg-warning-subtle text-warning font-monospace">Witness Summons</span></td>
                <td><span class="text-warning fw-bold font-monospace"><i class="fa-solid fa-hourglass-half me-1"></i> 48h Left</span></td>
                <td style="padding-right: 1rem;">
                  <div class="d-flex gap-2 align-items-center justify-content-end flex-nowrap">
                    <a href="case-investigation.php?case=CCMS-2026-9114" class="btn-cyber-action btn-cyber-primary" title="Open Forensic Console">
                      <i class="fa-solid fa-microscope"></i> Probe
                    </a>
                    <button class="btn-cyber-action btn-cyber-secondary" onclick="quickStatusModal('CCMS-2026-9114')" title="Change Status">
                      <i class="fa-solid fa-pen-to-square"></i>
                    </button>
                  </div>
                </td>
              </tr>

              <tr>
                <td style="padding: 1rem;">
                  <span class="font-monospace fw-bold text-primary">CCMS-2026-8802</span>
                  <div style="font-size: 0.72rem; color: var(--text-muted);">Assigned: Aug 10, 2026</div>
                </td>
                <td><span class="priority-pill priority-medium">MEDIUM</span></td>
                <td>
                  <strong>Municipal Building Approvals</strong>
                  <div style="font-size: 0.75rem; color: var(--text-muted);">Commercial Permit Division</div>
                </td>
                <td><span class="badge bg-secondary-subtle text-secondary font-monospace"><i class="fa-solid fa-user-ninja"></i> Anonymous</span></td>
                <td class="font-monospace text-danger fw-bold">$3,500</td>
                <td><span class="badge bg-info-subtle text-info font-monospace">Evidence Review</span></td>
                <td><span class="text-success fw-bold font-monospace"><i class="fa-solid fa-clock me-1"></i> 5 Days Left</span></td>
                <td style="padding-right: 1rem;">
                  <div class="d-flex gap-2 align-items-center justify-content-end flex-nowrap">
                    <a href="case-investigation.php?case=CCMS-2026-8802" class="btn-cyber-action btn-cyber-primary" title="Open Forensic Console">
                      <i class="fa-solid fa-microscope"></i> Probe
                    </a>
                    <button class="btn-cyber-action btn-cyber-secondary" onclick="quickStatusModal('CCMS-2026-8802')" title="Change Status">
                      <i class="fa-solid fa-pen-to-square"></i>
                    </button>
                  </div>
                </td>
              </tr>

              <tr>
                <td style="padding: 1rem;">
                  <span class="font-monospace fw-bold text-primary">CCMS-2026-7910</span>
                  <div style="font-size: 0.72rem; color: var(--text-muted);">Assigned: Aug 02, 2026</div>
                </td>
                <td><span class="priority-pill priority-high">HIGH</span></td>
                <td>
                  <strong>Healthcare & Medical Supply</strong>
                  <div style="font-size: 0.75rem; color: var(--text-muted);">Hospital Equipment Tender</div>
                </td>
                <td><span class="badge bg-primary-subtle text-primary font-monospace"><i class="fa-solid fa-id-card"></i> Verified Citizen</span></td>
                <td class="font-monospace text-danger fw-bold">$85,000</td>
                <td><span class="badge bg-success-subtle text-success font-monospace">Charge Sheet Filed</span></td>
                <td><span class="text-success fw-bold font-monospace"><i class="fa-solid fa-check-double me-1"></i> Concluded</span></td>
                <td style="padding-right: 1rem;">
                  <div class="d-flex gap-2 align-items-center justify-content-end flex-nowrap">
                    <a href="case-investigation.php?case=CCMS-2026-7910" class="btn-cyber-action btn-cyber-secondary" title="Open Forensic Console">
                      <i class="fa-solid fa-file-lines"></i> Docket
                    </a>
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
<script src="assets/js/officer.js"></script>
<script>
  function filterOfficerTable() {
    const search = document.getElementById('officerSearch').value.toLowerCase();
    const prio = document.getElementById('prioFilter').value.toLowerCase();
    const status = document.getElementById('statusFilter').value.toLowerCase();
    const rows = document.querySelectorAll('#officerCaseTable tbody tr');

    rows.forEach(row => {
      const text = row.innerText.toLowerCase();
      const matchSearch = !search || text.includes(search);
      const matchPrio = !prio || text.includes(prio);
      const matchStatus = !status || text.includes(status);
      row.style.display = matchSearch && matchPrio && matchStatus ? '' : 'none';
    });
  }

  function quickStatusModal(token) {
    Swal.fire({
      title: `Update Investigation Phase for #${token}`,
      input: 'select',
      inputOptions: {
        'In Triage': 'Initial Triage & Document Verification',
        'Investigation Active': 'Investigation Active / Evidence Corroboration',
        'Deposition Scheduled': 'Witness Deposition & Official Summons',
        'Charge Sheet Prepared': 'Prosecution Charge Sheet / Disciplinary Sanction',
        'Closed': 'Closed (Dismissed or Concluded)'
      },
      inputPlaceholder: 'Select next investigation stage',
      showCancelButton: true,
      confirmButtonText: 'Update Status & Broadcast',
      confirmButtonColor: '#0284c7'
    }).then((res) => {
      if (res.isConfirmed && res.value) {
        showOfficerToast(`Docket ${token} transitioned to: ${res.value}`, 'success');
      }
    });
  }
</script>
</body>
</html>
