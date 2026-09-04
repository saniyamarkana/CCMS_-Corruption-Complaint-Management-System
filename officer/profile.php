<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Officer Clearance Profile & Credentials — CCMS</title>
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
      <a href="profile.php" class="sb-link active">
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
        <span class="fw-bold" style="font-size: 0.95rem;">Investigator Clearance Profile</span>
      </div>

      <div class="nav-actions">
        <button class="nav-icon-btn" id="themeToggle" title="Toggle Theme">
          <i class="fa-solid fa-moon" id="themeIcon"></i>
        </button>
      </div>
    </nav>

    <main class="content-body">
      <div class="page-header">
        <div>
          <h1 class="page-title">Officer Credentials & Clearance Registry</h1>
          <p class="page-subtitle">Anti-Corruption Special Investigation Commission • Active Deployment Credentials</p>
        </div>
      </div>

      <div class="row g-4">
        <!-- Officer Badge Credentials -->
        <div class="col-lg-6">
          <div class="card-box h-100 mb-0">
            <div class="card-title mb-3">
              <i class="fa-solid fa-id-badge text-primary"></i> Vigilance Officer Record
            </div>

            <div class="p-3 rounded-4 bg-body-tertiary border mb-3 d-flex align-items-center gap-3">
              <div class="officer-avatar" style="width: 58px; height: 58px; font-size: 1.3rem;">KV</div>
              <div>
                <h4 style="font-size: 1.1rem; margin-bottom: 2px;">Inspector Kevin Vance</h4>
                <div class="font-monospace text-primary" style="font-size: 0.82rem; font-weight: 700;">BADGE NO: #AC-819</div>
                <div class="text-muted" style="font-size: 0.78rem;">Special Anti-Corruption Bureau Cell #04</div>
              </div>
            </div>

            <div class="row g-3 mb-4" style="font-size: 0.85rem;">
              <div class="col-6">
                <label class="form-label text-muted mb-1" style="font-size: 0.75rem;">Security Classification</label>
                <div class="p-2 rounded bg-body border font-monospace text-primary fw-bold">LEVEL-3 TOP SECRET</div>
              </div>
              <div class="col-6">
                <label class="form-label text-muted mb-1" style="font-size: 0.75rem;">Active Caseload Status</label>
                <div class="p-2 rounded bg-body border font-monospace text-success fw-bold">8 / 12 ASSIGNED</div>
              </div>
              <div class="col-6">
                <label class="form-label text-muted mb-1" style="font-size: 0.75rem;">Government Service ID</label>
                <div class="p-2 rounded bg-body border font-monospace">GOV-AC-2018-0912</div>
              </div>
              <div class="col-6">
                <label class="form-label text-muted mb-1" style="font-size: 0.75rem;">Court Jurisdiction</label>
                <div class="p-2 rounded bg-body border">Special Vigilance Tribunal</div>
              </div>
            </div>

            <button class="btn-cyber-action btn-cyber-primary w-100 justify-content-center py-2" onclick="showOfficerToast('Digital Warrant & Credentials Certificate Generated!', 'success')">
              <i class="fa-solid fa-certificate"></i> Generate Active Digital Warrant Badge
            </button>
          </div>
        </div>

        <!-- Bureau Cell Jurisdiction -->
        <div class="col-lg-6">
          <div class="card-box h-100 mb-0">
            <div class="card-title mb-3">
              <i class="fa-solid fa-building-shield text-cyan"></i> Unit Cell #04 Jurisdiction
            </div>

            <p class="text-muted mb-3" style="font-size: 0.85rem; line-height: 1.7;">
              Unit Cell #04 maintains primary statutory authority for investigating bribery, customs fraud, import/export cargo extortion, and corporate tax evasion.
            </p>

            <div class="d-flex flex-column gap-2 mb-4" style="font-size: 0.85rem;">
              <div class="d-flex align-items-center justify-content-between p-2 rounded-3 border">
                <span>Revenue & Tax Administration</span>
                <span class="badge bg-success-subtle text-success">Lead Oversight</span>
              </div>
              <div class="d-flex align-items-center justify-content-between p-2 rounded-3 border">
                <span>Customs & Seaport Terminals</span>
                <span class="badge bg-success-subtle text-success">Full Jurisdiction</span>
              </div>
              <div class="d-flex align-items-center justify-content-between p-2 rounded-3 border">
                <span>Airport Cargo Vigilance</span>
                <span class="badge bg-success-subtle text-success">Joint Taskforce</span>
              </div>
            </div>

            <div class="p-3 rounded-4 bg-body-tertiary border font-monospace text-muted" style="font-size: 0.78rem;">
              Bureau Cold-Vault Node: <strong class="text-primary">#US-EAST-09-VIGILANCE</strong><br>
              Cryptographic Token: <span class="text-emerald">Active & Synced (Ed25519)</span>
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
