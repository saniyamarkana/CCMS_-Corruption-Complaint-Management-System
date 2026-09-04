<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Citizen Security & Privacy Profile — CCMS</title>
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
      <a href="notifications.php" class="sb-link">
        <div class="icon-wrap"><i class="fa-solid fa-bell"></i></div>
        <span>Encrypted Alerts</span>
        <span class="sb-badge amber">2</span>
      </a>

      <div class="sb-section-label">Account & External</div>
      <a href="profile.php" class="sb-link active">
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
        <span class="fw-bold" style="font-size: 0.95rem;">Identity & Cryptographic Settings</span>
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
          <h1 class="page-title">Citizen Security Profile</h1>
          <p class="page-subtitle">Manage your verified credentials, active encryption keys, and whistleblower protection preferences.</p>
        </div>
      </div>

      <div class="row g-4">
        <!-- Personal Credentials Card -->
        <div class="col-lg-6">
          <div class="card-box h-100 mb-0">
            <div class="card-title mb-3">
              <i class="fa-solid fa-user-gear text-primary"></i> Citizen Profile Details
            </div>

            <form onsubmit="handleProfileSave(event)">
              <div class="mb-3">
                <label class="form-label" style="font-size: 0.85rem; font-weight: 600;">Full Legal Name</label>
                <input type="text" class="form-control" id="profileName" value="Alex Doe">
              </div>

              <div class="mb-3">
                <label class="form-label" style="font-size: 0.85rem; font-weight: 600;">Contact Email</label>
                <input type="email" class="form-control" id="profileEmail" value="citizen@anti-corruption.gov">
              </div>

              <div class="mb-3">
                <label class="form-label" style="font-size: 0.85rem; font-weight: 600;">Registered Mobile Phone</label>
                <input type="tel" class="form-control" id="profilePhone" value="+1 (555) 234-8921">
              </div>

              <div class="mb-4">
                <label class="form-label" style="font-size: 0.85rem; font-weight: 600;">Citizen Identity Token</label>
                <input type="text" class="form-control font-monospace" value="NID-2026-8942-US" readonly>
              </div>

              <button type="submit" class="btn-cyber-action btn-cyber-primary">
                <i class="fa-solid fa-floppy-disk"></i> Update Profile Info
              </button>
            </form>
          </div>
        </div>

        <!-- Privacy & Encryption Keys -->
        <div class="col-lg-6">
          <div class="card-box h-100 mb-0">
            <div class="card-title mb-3">
              <i class="fa-solid fa-key text-cyan"></i> Cryptographic Vault & Keys
            </div>

            <div class="p-3 rounded-4 bg-body-tertiary border mb-3">
              <span class="text-muted d-block" style="font-size: 0.75rem;">Active Public Key Fingerprint (Ed25519)</span>
              <div class="font-monospace text-primary fw-bold" style="font-size: 0.82rem; word-break: break-all;">
                SHA256:7f8e3c1d9b0a42f6e5d8c7b6a5f4e3d2c1b0a9f8e7d6c5b4a3
              </div>
            </div>

            <div class="d-flex flex-column gap-3 mb-4">
              <div class="d-flex align-items-center justify-content-between p-2 rounded-3 border">
                <div>
                  <strong style="font-size: 0.88rem; display: block;">Two-Factor Authentication (2FA)</strong>
                  <span class="text-muted" style="font-size: 0.75rem;">Hardware token / Authenticator app</span>
                </div>
                <span class="badge bg-success-subtle text-success">Enabled</span>
              </div>

              <div class="d-flex align-items-center justify-content-between p-2 rounded-3 border">
                <div>
                  <strong style="font-size: 0.88rem; display: block;">Automated EXIF Sanitizer</strong>
                  <span class="text-muted" style="font-size: 0.75rem;">Auto-scrub camera metadata on file upload</span>
                </div>
                <span class="badge bg-success-subtle text-success">Always On</span>
              </div>

              <div class="d-flex align-items-center justify-content-between p-2 rounded-3 border">
                <div>
                  <strong style="font-size: 0.88rem; display: block;">Zero-Knowledge Token Storage</strong>
                  <span class="text-muted" style="font-size: 0.75rem;">Store access tokens securely</span>
                </div>
                <span class="badge bg-primary-subtle text-primary">Active</span>
              </div>
            </div>

            <button type="button" class="btn-cyber-action btn-cyber-secondary" onclick="showCitizenToast('New cryptographic keypair generated and synced with Sentinel!', 'success')">
              <i class="fa-solid fa-rotate"></i> Rotate Encryption Keypair
            </button>
          </div>
        </div>
      </div>
    </main>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="assets/js/citizen.js"></script>
<script>
  function handleProfileSave(e) {
    e.preventDefault();
    showCitizenToast('Profile updated securely!', 'success');
  }
</script>
</body>
</html>
