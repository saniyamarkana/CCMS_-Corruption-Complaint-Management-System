<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Encrypted Alerts & Notices — CCMS Citizen</title>
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
      <a href="notifications.php" class="sb-link active">
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
        <span class="fw-bold" style="font-size: 0.95rem;">Alerts & Communication Dispatch</span>
      </div>

      <div class="nav-actions">
        <button class="nav-icon-btn" id="themeToggle" title="Toggle Theme">
          <i class="fa-solid fa-moon" id="themeIcon"></i>
        </button>
        <button class="btn-ccms btn-ccms-secondary btn-sm" onclick="showCitizenToast('All notifications marked as read', 'success')">
          <i class="fa-solid fa-check-double"></i> Mark All Read
        </button>
      </div>
    </nav>

    <main class="content-body">
      <div class="page-header">
        <div>
          <h1 class="page-title">Encrypted Citizen Alerts & Official Notices</h1>
          <p class="page-subtitle">Zero-knowledge dispatch notifications from investigating officers and anti-corruption tribunals.</p>
        </div>
      </div>

      <div class="card-box">
        <div class="d-flex flex-column gap-3">
          
          <!-- Alert 1 (Unread) -->
          <div class="p-3 rounded-4 border d-flex align-items-start justify-content-between gap-3" style="background: rgba(99, 102, 241, 0.06); border-color: var(--primary-glow) !important;">
            <div class="d-flex gap-3">
              <div style="width: 42px; height: 42px; border-radius: 12px; background: var(--primary); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 1.15rem; flex-shrink: 0;">
                <i class="fa-solid fa-message"></i>
              </div>
              <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                  <span class="font-monospace fw-bold text-primary" style="font-size: 0.85rem;">CASE #CCMS-2026-9082</span>
                  <span class="badge bg-danger rounded-pill" style="font-size: 0.65rem;">NEW MESSAGE</span>
                  <span class="text-muted" style="font-size: 0.75rem;">• 10 mins ago</span>
                </div>
                <h4 style="font-size: 0.95rem; margin-bottom: 0.25rem;">Investigator Request for Clarification</h4>
                <p class="text-muted mb-0" style="font-size: 0.85rem;">
                  Lead Investigator Vance has submitted a question regarding potential witnesses present at the Customs port gate.
                </p>
              </div>
            </div>
            <a href="track-complaint.php?case=CCMS-2026-9082" class="btn-cyber-action btn-cyber-primary">
              <i class="fa-solid fa-reply"></i> Reply Encrypted
            </a>
          </div>

          <!-- Alert 2 (Unread) -->
          <div class="p-3 rounded-4 border d-flex align-items-start justify-content-between gap-3" style="background: rgba(16, 185, 129, 0.06); border-color: var(--emerald-glow) !important;">
            <div class="d-flex gap-3">
              <div style="width: 42px; height: 42px; border-radius: 12px; background: var(--emerald); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 1.15rem; flex-shrink: 0;">
                <i class="fa-solid fa-file-circle-check"></i>
              </div>
              <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                  <span class="font-monospace fw-bold text-emerald" style="font-size: 0.85rem;">CASE #CCMS-2026-4150</span>
                  <span class="badge bg-success rounded-pill" style="font-size: 0.65rem;">DISPOSAL ORDER</span>
                  <span class="text-muted" style="font-size: 0.75rem;">• 2 days ago</span>
                </div>
                <h4 style="font-size: 0.95rem; margin-bottom: 0.25rem;">Final Disciplinary Sanction Published</h4>
                <p class="text-muted mb-0" style="font-size: 0.85rem;">
                  The Public Works tender corruption probe concluded with recovery orders and disciplinary suspension of the executive engineer.
                </p>
              </div>
            </div>
            <a href="track-complaint.php?case=CCMS-2026-4150" class="btn-cyber-action btn-cyber-secondary">
              <i class="fa-solid fa-file-lines"></i> View Docket
            </a>
          </div>

          <!-- Alert 3 (Read) -->
          <div class="p-3 rounded-4 border d-flex align-items-start justify-content-between gap-3 bg-body-tertiary">
            <div class="d-flex gap-3">
              <div style="width: 42px; height: 42px; border-radius: 12px; background: var(--bg-surface-2); border: 1px solid var(--border); color: var(--text-muted); display: flex; align-items: center; justify-content: center; font-size: 1.15rem; flex-shrink: 0;">
                <i class="fa-solid fa-lock"></i>
              </div>
              <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                  <span class="font-monospace fw-bold text-muted" style="font-size: 0.85rem;">SECURITY UPDATE</span>
                  <span class="text-muted" style="font-size: 0.75rem;">• Aug 10, 2026</span>
                </div>
                <h4 style="font-size: 0.95rem; margin-bottom: 0.25rem;">Zero-Knowledge Cryptographic Protocol v2.4 Applied</h4>
                <p class="text-muted mb-0" style="font-size: 0.85rem;">
                  National database cold nodes were upgraded with quantum-resistant key rotation. Your existing tokens remain valid.
                </p>
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
<script src="assets/js/citizen.js"></script>
</body>
</html>
