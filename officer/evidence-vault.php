<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Digital Forensic Evidence Vault — CCMS Officer</title>
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

      <a href="evidence-vault.php" class="sb-link active">
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
        <span class="fw-bold" style="font-size: 0.95rem;">Cryptographic Cold Storage Evidence Room</span>
      </div>

      <div class="nav-actions">
        <button class="nav-icon-btn" id="themeToggle" title="Toggle Theme">
          <i class="fa-solid fa-moon" id="themeIcon"></i>
        </button>
        <button class="btn-officer btn-officer-primary btn-sm" onclick="showOfficerToast('Running SHA-256 Batch Verification across all vault nodes...', 'success')">
          <i class="fa-solid fa-shield-virus"></i> Verify Checksums
        </button>
      </div>
    </nav>

    <main class="content-body">
      <div class="page-header">
        <div>
          <h1 class="page-title">Digital Evidence Vault & Chain of Custody</h1>
          <p class="page-subtitle">Zero-tamper cold repository for audio intercepts, forged invoices, video recordings, and financial ledgers.</p>
        </div>
      </div>

      <div class="card-box">
        <div class="table-responsive">
          <table class="table align-middle" style="color: var(--text-main);">
            <thead style="background: var(--bg-surface-2); font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted);">
              <tr>
                <th style="padding: 1rem;">Evidence Token</th>
                <th>Case Docket</th>
                <th>File Description</th>
                <th>File Format</th>
                <th>Size</th>
                <th>SHA-256 Seal Checksum</th>
                <th>Integrity</th>
                <th class="text-end" style="padding-right: 1rem;">Action</th>
              </tr>
            </thead>
            <tbody style="font-size: 0.9rem;">
              <tr>
                <td style="padding: 1rem;"><span class="font-monospace fw-bold text-primary">EVD-9082-A</span></td>
                <td><span class="font-monospace fw-bold">CCMS-2026-9082</span></td>
                <td><strong>Verbal Bribery Demand Audio</strong></td>
                <td><span class="badge bg-warning-subtle text-warning font-monospace">MP3 Audio</span></td>
                <td class="font-monospace">4.2 MB</td>
                <td><code class="text-primary font-monospace" style="font-size: 0.78rem;">0x8a92f147c0b...e41b</code></td>
                <td><span class="badge bg-success-subtle text-success"><i class="fa-solid fa-check"></i> Unbroken Chain</span></td>
                <td class="text-end" style="padding-right: 1rem;">
                  <button class="btn-cyber-action btn-cyber-primary" onclick="showOfficerToast('Evidence Stream Verified with Cold Node #04 (SHA-256 Valid)', 'success')">
                    <i class="fa-solid fa-magnifying-glass"></i> Inspect File
                  </button>
                </td>
              </tr>

              <tr>
                <td style="padding: 1rem;"><span class="font-monospace fw-bold text-primary">EVD-9082-B</span></td>
                <td><span class="font-monospace fw-bold">CCMS-2026-9082</span></td>
                <td><strong>Refusal Notice & Stamped Bill of Lading</strong></td>
                <td><span class="badge bg-danger-subtle text-danger font-monospace">PDF Document</span></td>
                <td class="font-monospace">1.8 MB</td>
                <td><code class="text-primary font-monospace" style="font-size: 0.78rem;">0x3f1c88a910e...99d2</code></td>
                <td><span class="badge bg-success-subtle text-success"><i class="fa-solid fa-check"></i> Unbroken Chain</span></td>
                <td class="text-end" style="padding-right: 1rem;">
                  <button class="btn-cyber-action btn-cyber-primary" onclick="showOfficerToast('Evidence Stream Verified with Cold Node #04 (SHA-256 Valid)', 'success')">
                    <i class="fa-solid fa-magnifying-glass"></i> Inspect File
                  </button>
                </td>
              </tr>

              <tr>
                <td style="padding: 1rem;"><span class="font-monospace fw-bold text-primary">EVD-9114-A</span></td>
                <td><span class="font-monospace fw-bold">CCMS-2026-9114</span></td>
                <td><strong>Fraudulent Deed Registration Scan</strong></td>
                <td><span class="badge bg-danger-subtle text-danger font-monospace">PDF Document</span></td>
                <td class="font-monospace">8.4 MB</td>
                <td><code class="text-primary font-monospace" style="font-size: 0.78rem;">0x51b8ef0193c...24a1</code></td>
                <td><span class="badge bg-success-subtle text-success"><i class="fa-solid fa-check"></i> Unbroken Chain</span></td>
                <td class="text-end" style="padding-right: 1rem;">
                  <button class="btn-cyber-action btn-cyber-primary" onclick="showOfficerToast('Evidence Stream Verified with Cold Node #04 (SHA-256 Valid)', 'success')">
                    <i class="fa-solid fa-magnifying-glass"></i> Inspect File
                  </button>
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
</body>
</html>
