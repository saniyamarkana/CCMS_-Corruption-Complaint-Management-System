<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Active Case Console — CCMS Officer</title>
  <meta name="description" content="Officer Active Case Investigation Console — Forensic evidence vault, depositions, timeline journal, and charge sheet generator.">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&family=Space+Grotesk:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
  <link rel="stylesheet" href="assets/css/officer.css">

  <style>
    /* ═══ Ultra-Clean Adaptive Case Investigation Console (No Side Scroller) ═══ */

    /* ── Hero Dossier Banner ── */
    .case-hero-banner {
      background: var(--bg-card);
      border: 1px solid var(--border);
      border-radius: 24px;
      padding: 2rem 2.25rem;
      margin-bottom: 1.75rem;
      position: relative;
      overflow: hidden;
      box-shadow: var(--card-shadow);
      backdrop-filter: blur(20px);
      -webkit-backdrop-filter: blur(20px);
      transition: var(--transition);
    }
    .case-hero-banner::before {
      content: '';
      position: absolute;
      top: -80px;
      right: -60px;
      width: 280px;
      height: 280px;
      border-radius: 50%;
      background: radial-gradient(circle, var(--primary-glow), transparent 70%);
      pointer-events: none;
      opacity: 0.5;
    }
    .case-hero-banner::after {
      content: '';
      position: absolute;
      bottom: -60px;
      left: 30%;
      width: 220px;
      height: 220px;
      border-radius: 50%;
      background: radial-gradient(circle, var(--cyan-glow), transparent 70%);
      pointer-events: none;
      opacity: 0.35;
    }

    /* Metric Chips inside Hero Banner */
    .dossier-stat-chip {
      background: var(--bg-surface-2);
      border: 1px solid var(--border);
      border-radius: 16px;
      padding: 1rem 1.25rem;
      height: 100%;
      display: flex;
      flex-direction: column;
      justify-content: center;
      transition: var(--transition);
    }
    .dossier-stat-chip:hover {
      border-color: var(--border-glow);
      transform: translateY(-2px);
      box-shadow: var(--card-glow-shadow);
    }
    .stat-chip-label {
      font-size: 0.72rem;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      color: var(--text-muted);
      font-family: var(--font-mono);
      font-weight: 600;
      margin-bottom: 0.35rem;
    }
    .stat-chip-val {
      font-size: 1.15rem;
      font-weight: 700;
      color: var(--text-main);
      display: flex;
      align-items: center;
      gap: 0.45rem;
    }

    /* ── Seamless Responsive Tab Grid with Hover Marquee Effect ── */
    .inv-tabs-grid-wrapper {
      background: var(--bg-card);
      border: 1px solid var(--border);
      border-radius: 20px;
      padding: 0.65rem;
      margin-bottom: 1.75rem;
      box-shadow: var(--card-shadow);
      backdrop-filter: blur(16px);
      -webkit-backdrop-filter: blur(16px);
    }
    .inv-tabs-grid {
      display: grid;
      grid-template-columns: repeat(5, minmax(0, 1fr));
      gap: 0.6rem;
      width: 100%;
    }
    @media (max-width: 1200px) {
      .inv-tabs-grid {
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
      }
    }
    .inv-tab-btn {
      background: var(--bg-surface-2);
      border: 1.5px solid var(--border);
      padding: 0.85rem 0.95rem;
      font-size: 0.86rem;
      font-weight: 700;
      color: var(--text-muted);
      border-radius: 14px;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 0.5rem;
      transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
      outline: none;
      text-align: left;
      min-width: 0;
      position: relative;
    }
    .inv-tab-btn:hover {
      background: var(--bg-surface);
      border-color: var(--primary-light);
      color: var(--primary-light);
      transform: translateY(-2px);
      box-shadow: 0 4px 15px rgba(99, 102, 241, 0.15);
    }
    .inv-tab-btn.active {
      background: linear-gradient(135deg, var(--primary) 0%, #0284c7 100%);
      border-color: transparent;
      color: #ffffff !important;
      box-shadow: 0 6px 20px var(--primary-glow);
    }
    .tab-btn-content {
      display: flex;
      align-items: center;
      gap: 0.55rem;
      overflow: hidden;
      flex: 1;
      min-width: 0;
    }
    .tab-btn-icon {
      font-size: 0.95rem;
      opacity: 0.85;
      flex-shrink: 0;
    }
    .inv-tab-btn.active .tab-btn-icon {
      opacity: 1;
      color: #ffffff;
    }

    /* Marquee Text Container on Hover */
    .tab-marquee-container {
      overflow: hidden;
      white-space: nowrap;
      position: relative;
      flex: 1;
      min-width: 0;
      display: block;
      mask-image: linear-gradient(90deg, #000 85%, transparent 100%);
      -webkit-mask-image: linear-gradient(90deg, #000 85%, transparent 100%);
    }
    .inv-tab-btn:hover .tab-marquee-container {
      mask-image: none;
      -webkit-mask-image: none;
    }
    .tab-marquee-label {
      display: inline-block;
      white-space: nowrap;
      transition: transform 0.3s ease;
      will-change: transform;
    }
    .inv-tab-btn:hover .tab-marquee-label {
      animation: tabMarqueeScroll 3.2s linear infinite alternate;
    }
    @keyframes tabMarqueeScroll {
      0%, 20% {
        transform: translateX(0%);
      }
      80%, 100% {
        transform: translateX(var(--marquee-overflow, -35%));
      }
    }

    .tab-pill-badge {
      background: rgba(99, 102, 241, 0.15);
      color: var(--primary-light);
      border-radius: 9999px;
      padding: 0.15rem 0.55rem;
      font-size: 0.72rem;
      font-weight: 800;
      font-family: var(--font-mono);
      flex-shrink: 0;
    }
    .inv-tab-btn.active .tab-pill-badge {
      background: rgba(255, 255, 255, 0.25);
      color: #ffffff;
    }

    /* ── Tab Panes ── */
    .tab-content-pane {
      display: none;
    }
    .tab-content-pane.active {
      display: block;
      animation: tabFadeIn 0.3s ease;
    }
    @keyframes tabFadeIn {
      from { opacity: 0; transform: translateY(6px); }
      to { opacity: 1; transform: translateY(0); }
    }

    /* ── Evidence Cards ── */
    .evidence-item-card {
      background: var(--bg-surface-2);
      border: 1px solid var(--border);
      border-radius: 20px;
      padding: 1.5rem;
      transition: var(--transition);
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      height: 100%;
    }
    .evidence-item-card:hover {
      border-color: var(--border-glow);
      box-shadow: var(--card-glow-shadow);
      transform: translateY(-3px);
    }

    /* Audio Player Box */
    .audio-player-box {
      background: var(--bg-surface);
      border: 1px solid var(--border);
      border-radius: 16px;
      padding: 1rem 1.15rem;
    }
    .audio-visualizer-bars {
      display: flex;
      align-items: center;
      gap: 3px;
      height: 28px;
    }
    .wave-bar {
      width: 3px;
      border-radius: 9999px;
      background: linear-gradient(180deg, var(--cyan), var(--primary));
      opacity: 0.4;
      transition: height 0.15s ease;
    }
    .wave-bar:nth-child(1) { height: 8px; } .wave-bar:nth-child(2) { height: 18px; }
    .wave-bar:nth-child(3) { height: 12px; } .wave-bar:nth-child(4) { height: 24px; }
    .wave-bar:nth-child(5) { height: 18px; } .wave-bar:nth-child(6) { height: 14px; }
    .wave-bar:nth-child(7) { height: 26px; } .wave-bar:nth-child(8) { height: 10px; }
    .wave-bar:nth-child(9) { height: 20px; } .wave-bar:nth-child(10) { height: 16px; }
    .wave-bar:nth-child(11) { height: 8px; } .wave-bar:nth-child(12) { height: 18px; }
    .wave-bar.playing {
      animation: wavePulse 0.6s ease-in-out infinite alternate;
      opacity: 1;
    }
    .wave-bar.playing:nth-child(odd) { animation-delay: 0.1s; }
    .wave-bar.playing:nth-child(3n) { animation-delay: 0.25s; }
    @keyframes wavePulse {
      from { height: 6px; }
      to { height: 26px; }
    }

    /* Spectrogram Box */
    .spectrogram-preview {
      height: 75px;
      background: linear-gradient(90deg, #1e1b4b 0%, #312e81 20%, #4338ca 40%, #06b6d4 70%, #10b981 100%);
      border-radius: 12px;
      position: relative;
      overflow: hidden;
    }
    .spectrogram-grid {
      position: absolute;
      inset: 0;
      background-size: 15px 15px;
      background-image: linear-gradient(to right, rgba(255,255,255,0.1) 1px, transparent 1px),
                        linear-gradient(to bottom, rgba(255,255,255,0.1) 1px, transparent 1px);
    }

    /* ── Deposition Cards ── */
    .deponent-avatar {
      width: 48px;
      height: 48px;
      border-radius: 14px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 800;
      font-size: 1.1rem;
      flex-shrink: 0;
    }
    .deposition-item-card {
      background: var(--bg-surface-2);
      border: 1px solid var(--border);
      border-radius: 18px;
      padding: 1.35rem 1.5rem;
      transition: var(--transition);
    }
    .deposition-item-card:hover {
      border-color: var(--border-glow);
      box-shadow: var(--card-glow-shadow);
      transform: translateY(-2px);
    }

    /* ── Forensic Journal Timeline ── */
    .forensic-timeline-stream {
      position: relative;
      padding-left: 2.75rem;
    }
    .forensic-timeline-stream::before {
      content: '';
      position: absolute;
      left: 19px;
      top: 15px;
      bottom: 25px;
      width: 2.5px;
      background: linear-gradient(180deg, var(--primary) 0%, var(--cyan) 60%, var(--border) 100%);
      border-radius: 4px;
    }
    .journal-step-node {
      position: relative;
      margin-bottom: 1.75rem;
    }
    .journal-node-pin {
      position: absolute;
      left: -2.75rem;
      top: 1rem;
      width: 40px;
      height: 40px;
      border-radius: 50%;
      background: var(--bg-surface);
      border: 2px solid var(--border);
      color: var(--primary-light);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 0.9rem;
      z-index: 2;
      box-shadow: 0 0 15px var(--primary-glow);
    }
    .journal-card-content {
      background: var(--bg-surface-2);
      border: 1px solid var(--border);
      border-radius: 18px;
      padding: 1.25rem 1.4rem;
      transition: var(--transition);
    }
    .journal-card-content:hover {
      border-color: var(--border-glow);
      transform: translateX(4px);
    }

    /* ── Charge Sheet Paper ── */
    .charge-sheet-paper {
      background: var(--bg-surface-2);
      border: 2px solid var(--border);
      border-radius: 22px;
      padding: 2.5rem;
      position: relative;
      box-shadow: inset 0 0 40px rgba(0,0,0,0.05);
    }
    .charge-sheet-watermark {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%) rotate(-25deg);
      font-size: 4.5rem;
      font-weight: 900;
      color: rgba(99, 102, 241, 0.04);
      pointer-events: none;
      white-space: nowrap;
      text-transform: uppercase;
      font-family: var(--font-heading);
      letter-spacing: 0.15em;
    }

    /* SLA Capsule Badge */
    .sla-capsule-badge {
      background: rgba(244, 63, 94, 0.12);
      border: 1.5px solid rgba(244, 63, 94, 0.35);
      border-radius: 14px;
      padding: 0.65rem 1.25rem;
      display: inline-flex;
      align-items: center;
      gap: 0.65rem;
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

      <a href="case-investigation.php?case=CCMS-2026-9082" class="sb-link active">
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
        <span class="fw-bold font-monospace" style="font-size: 0.95rem; color: var(--text-main);">
          Dossier Workspace: <span class="text-primary" id="headerCaseToken">#CCMS-2026-9082</span>
        </span>
      </div>

      <div class="nav-actions">
        <button class="nav-icon-btn" id="themeToggle" title="Toggle Theme">
          <i class="fa-solid fa-moon" id="themeIcon"></i>
        </button>
        <button class="btn-cyber-action btn-cyber-primary" onclick="showGenerateChargeSheetModal()">
          <i class="fa-solid fa-file-signature"></i> Finalize Charge Sheet
        </button>
      </div>
    </nav>

    <main class="content-body">

      <!-- ═══════════════════ CASE DOSSIER HERO BANNER ═══════════════════ -->
      <div class="case-hero-banner">
        <!-- Top Row: Tokens, Title, Badges, SLA -->
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 pb-3 mb-3" style="border-bottom: 1px solid var(--border);">
          <div>
            <div class="d-flex align-items-center gap-2 flex-wrap mb-2">
              <span class="font-monospace fw-bold fs-4 text-primary" id="caseTokenDisplay">CCMS-2026-9082</span>
              <span class="priority-pill priority-critical" id="casePriorityDisplay">CRITICAL PRIORITY</span>
              <span class="badge bg-secondary-subtle text-secondary font-monospace" id="caseModeDisplay">
                <i class="fa-solid fa-user-ninja me-1"></i> 100% Anonymous Whistleblower
              </span>
              <span class="badge bg-warning-subtle text-warning font-monospace" id="caseStatusDisplay">
                <i class="fa-solid fa-spinner fa-spin me-1"></i> Investigation Active
              </span>
            </div>

            <h2 style="font-size: 1.35rem; font-weight: 800; color: var(--text-main);" id="caseTitleDisplay">
              Bribery demand for shipping container clearance certificate
            </h2>
            <p class="text-muted mb-0" style="font-size: 0.88rem;" id="caseSubtitleDisplay">
              Target Entity: <strong style="color: var(--text-main);" id="caseEntityDisplay">Revenue &amp; Customs Port Division</strong>
              • Accused: <strong class="text-danger" id="caseAccusedDisplay">Assistant Examiner K. Vance</strong>
            </p>
          </div>

          <!-- Right: SLA Clock + Quick Action Buttons -->
          <div class="d-flex flex-column align-items-end gap-2">
            <div class="sla-capsule-badge">
              <i class="fa-solid fa-stopwatch text-danger fs-5"></i>
              <div>
                <div class="text-muted font-monospace" style="font-size: 0.68rem; text-transform: uppercase; letter-spacing: 0.06em;">Statutory SLA Clock</div>
                <div class="font-monospace fw-bold text-danger" style="font-size: 0.92rem;" id="slaClockText">35h 20m Remaining</div>
              </div>
            </div>

            <div class="d-flex gap-2 mt-1">
              <button class="btn-cyber-action btn-cyber-secondary btn-sm" onclick="exportForensicBundle()" title="Export Evidence ZIP">
                <i class="fa-solid fa-file-zipper"></i> Forensic ZIP
              </button>
              <button class="btn-cyber-action btn-cyber-secondary btn-sm" onclick="printChargeSheetQuick()" title="Print Case File">
                <i class="fa-solid fa-print"></i> Print Docket
              </button>
            </div>
          </div>
        </div>

        <!-- 4 Key Dossier Metric Chips -->
        <div class="row g-3">
          <div class="col-6 col-md-3">
            <div class="dossier-stat-chip">
              <div class="stat-chip-label">Alleged Bribery Demand</div>
              <div class="stat-chip-val font-monospace text-danger" id="caseAmountDisplay">$5,000.00 USD</div>
            </div>
          </div>
          <div class="col-6 col-md-3">
            <div class="dossier-stat-chip">
              <div class="stat-chip-label">Incident Date &amp; Venue</div>
              <div class="stat-chip-val" style="font-size: 0.95rem;" id="caseVenueDisplay">Aug 14, 2026 • Port Gate #4</div>
            </div>
          </div>
          <div class="col-6 col-md-3">
            <div class="dossier-stat-chip">
              <div class="stat-chip-label">Evidence Item Count</div>
              <div class="stat-chip-val text-primary font-monospace" style="font-size: 0.95rem;" id="caseEvidenceCountDisplay">2 Verified Files</div>
            </div>
          </div>
          <div class="col-6 col-md-3">
            <div class="dossier-stat-chip">
              <div class="stat-chip-label">Investigating Bureau Cell</div>
              <div class="stat-chip-val" style="color: var(--emerald); font-size: 0.95rem;" id="caseCellDisplay">Anti-Corruption Cell #04</div>
            </div>
          </div>
        </div>
      </div>

      <!-- ═══════════════════ SEAMLESS RESPONSIVE INVESTIGATION TABS (NO SIDE SCROLLER) ═══════════════════ -->
      <div class="inv-tabs-grid-wrapper">
        <div class="inv-tabs-grid">
          <button class="inv-tab-btn active" onclick="switchInvTab('evidence', this)" id="tabBtn-evidence" title="1. Forensic Evidence Vault">
            <div class="tab-btn-content">
              <i class="fa-solid fa-vault tab-btn-icon"></i>
              <div class="tab-marquee-container">
                <span class="tab-marquee-label">1. Forensic Evidence Vault</span>
              </div>
            </div>
            <span class="tab-pill-badge">2</span>
          </button>

          <button class="inv-tab-btn" onclick="switchInvTab('depositions', this)" id="tabBtn-depositions" title="2. Witness & Suspect Depositions">
            <div class="tab-btn-content">
              <i class="fa-solid fa-microphone-lines tab-btn-icon"></i>
              <div class="tab-marquee-container">
                <span class="tab-marquee-label">2. Witness Depositions</span>
              </div>
            </div>
            <span class="tab-pill-badge">2</span>
          </button>

          <button class="inv-tab-btn" onclick="switchInvTab('notes', this)" id="tabBtn-notes" title="3. Case Journal & Timeline">
            <div class="tab-btn-content">
              <i class="fa-solid fa-book-bookmark tab-btn-icon"></i>
              <div class="tab-marquee-container">
                <span class="tab-marquee-label">3. Journal &amp; Timeline</span>
              </div>
            </div>
            <span class="tab-pill-badge">4</span>
          </button>

          <button class="inv-tab-btn" onclick="switchInvTab('summons', this)" id="tabBtn-summons" title="4. Hearings & Summons Notice">
            <div class="tab-btn-content">
              <i class="fa-solid fa-gavel tab-btn-icon"></i>
              <div class="tab-marquee-container">
                <span class="tab-marquee-label">4. Summons Notice</span>
              </div>
            </div>
            <span class="tab-pill-badge">1</span>
          </button>

          <button class="inv-tab-btn" onclick="switchInvTab('chargesheet', this)" id="tabBtn-chargesheet" title="5. Prosecution Charge Sheet Generator">
            <div class="tab-btn-content">
              <i class="fa-solid fa-scale-balanced tab-btn-icon"></i>
              <div class="tab-marquee-container">
                <span class="tab-marquee-label">5. Prosecution Charge Sheet</span>
              </div>
            </div>
            <span class="tab-pill-badge"><i class="fa-solid fa-stamp"></i></span>
          </button>
        </div>
      </div>

      <!-- ═══════════════════ TAB 1: EVIDENCE VAULT ═══════════════════ -->
      <div class="tab-content-pane active" id="tabEvidence">
        <div class="card-box">
          <div class="card-header-flex">
            <div>
              <div class="card-title">
                <i class="fa-solid fa-file-shield text-primary"></i> Attached Evidence Bundle (Cryptographically Sealed)
              </div>
              <p class="text-muted mb-0" style="font-size: 0.82rem;">
                Forensic immutable hashes recorded on distributed integrity ledger.
              </p>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
              <span class="badge bg-success-subtle text-success font-monospace" style="font-size: 0.78rem; border: 1px solid rgba(16,185,129,0.3); padding: 0.5rem 0.8rem;">
                <i class="fa-solid fa-shield-halved me-1"></i> SHA-256 Checksums Intact
              </span>
              <button class="btn-cyber-action btn-cyber-primary" onclick="showAddEvidenceModal()">
                <i class="fa-solid fa-cloud-arrow-up"></i> Upload Forensic File
              </button>
            </div>
          </div>

          <div class="row g-4">
            <!-- Evidence 1: Audio Recording -->
            <div class="col-lg-6">
              <div class="evidence-item-card">
                <div>
                  <div class="d-flex align-items-start justify-content-between mb-3">
                    <div class="d-flex align-items-center gap-3">
                      <div class="p-2 rounded-3 bg-warning-subtle text-warning fs-3">
                        <i class="fa-solid fa-file-audio"></i>
                      </div>
                      <div>
                        <strong style="font-size: 0.95rem; display: block; color: var(--text-main);">audio_recording_demands_Aug14.mp3</strong>
                        <span class="font-monospace text-muted" style="font-size: 0.72rem;">SHA-256: 0x8a92f147c0b89e41b</span>
                      </div>
                    </div>
                    <span class="badge bg-warning-subtle text-warning font-monospace flex-shrink-0">MP3 • 4.2 MB</span>
                  </div>

                  <p class="text-muted" style="font-size: 0.85rem; line-height: 1.6;">
                    Verbatim voice recording capturing verbal demand of $5,000 cash for releasing withheld container clearance paperwork at Port Counter #4.
                  </p>

                  <!-- Audio Player Box -->
                  <div class="audio-player-box mb-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                      <div class="d-flex align-items-center gap-3">
                        <button class="btn-cyber-action btn-cyber-primary p-0 rounded-circle" id="playAudioBtn" style="width: 38px; height: 38px;" onclick="toggleAudioPlayback()">
                          <i class="fa-solid fa-play" id="playAudioIcon" style="font-size: 0.8rem; margin-left: 2px;"></i>
                        </button>
                        <div>
                          <div class="font-monospace fw-bold" id="audioTimer" style="font-size: 0.82rem; color: var(--text-main);">00:00 / 02:45</div>
                          <div class="text-muted" style="font-size: 0.7rem;">Sample: 44.1 kHz 24-bit</div>
                        </div>
                      </div>

                      <!-- Waveform Bars -->
                      <div class="audio-visualizer-bars" id="audioWaveBars">
                        <div class="wave-bar"></div><div class="wave-bar"></div>
                        <div class="wave-bar"></div><div class="wave-bar"></div>
                        <div class="wave-bar"></div><div class="wave-bar"></div>
                        <div class="wave-bar"></div><div class="wave-bar"></div>
                        <div class="wave-bar"></div><div class="wave-bar"></div>
                        <div class="wave-bar"></div><div class="wave-bar"></div>
                      </div>
                    </div>

                    <div class="progress" style="height: 4px; background: rgba(99, 102, 241, 0.15); border-radius: 9999px;">
                      <div class="progress-bar bg-primary" id="audioProgressBar" style="width: 0%; border-radius: 9999px;"></div>
                    </div>
                  </div>
                </div>

                <div class="d-flex gap-2 pt-2">
                  <button class="btn-cyber-action btn-cyber-secondary w-100" onclick="showSpectrogramModal()">
                    <i class="fa-solid fa-wave-square"></i> Spectrogram Analysis
                  </button>
                  <button class="btn-cyber-action btn-cyber-primary w-100" onclick="verifyHash('EVD-9082-A')">
                    <i class="fa-solid fa-check-to-slot"></i> Verify Hash
                  </button>
                </div>
              </div>
            </div>

            <!-- Evidence 2: PDF Document -->
            <div class="col-lg-6">
              <div class="evidence-item-card">
                <div>
                  <div class="d-flex align-items-start justify-content-between mb-3">
                    <div class="d-flex align-items-center gap-3">
                      <div class="p-2 rounded-3 bg-danger-subtle text-danger fs-3">
                        <i class="fa-solid fa-file-pdf"></i>
                      </div>
                      <div>
                        <strong style="font-size: 0.95rem; display: block; color: var(--text-main);">shipping_manifest_withheld_notice.pdf</strong>
                        <span class="font-monospace text-muted" style="font-size: 0.72rem;">SHA-256: 0x3f1c88a910e4299d2</span>
                      </div>
                    </div>
                    <span class="badge bg-danger-subtle text-danger font-monospace flex-shrink-0">PDF • 1.8 MB</span>
                  </div>

                  <p class="text-muted" style="font-size: 0.85rem; line-height: 1.6;">
                    Official bill of lading with arbitrary refusal stamp signed by the accused examiner on Aug 14, 2026. Includes forensic scan layers.
                  </p>

                  <div class="audio-player-box mb-3 font-monospace" style="font-size: 0.78rem;">
                    <div class="d-flex justify-content-between mb-1">
                      <span class="text-muted">Digital Timestamp:</span>
                      <strong style="color: var(--text-main);">2026-08-14 10:14:02 UTC</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                      <span class="text-muted">EXIF Metadata Scrub:</span>
                      <span class="text-success fw-bold"><i class="fa-solid fa-shield-halved me-1"></i> Sanitized &amp; Intact</span>
                    </div>
                    <div class="d-flex justify-content-between">
                      <span class="text-muted">Physical Seal Status:</span>
                      <span class="text-primary fw-bold">Digitized &amp; Vectorized</span>
                    </div>
                  </div>
                </div>

                <div class="d-flex gap-2 pt-2">
                  <button class="btn-cyber-action btn-cyber-secondary w-100" onclick="showPdfExtractModal()">
                    <i class="fa-solid fa-magnifying-glass"></i> Preview PDF Extract
                  </button>
                  <button class="btn-cyber-action btn-cyber-primary w-100" onclick="verifyHash('EVD-9082-B')">
                    <i class="fa-solid fa-check-to-slot"></i> Verify Hash
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ═══════════════════ TAB 2: DEPOSITIONS ═══════════════════ -->
      <div class="tab-content-pane" id="tabDepositions">
        <div class="card-box">
          <div class="card-header-flex">
            <div>
              <div class="card-title">
                <i class="fa-solid fa-microphone-lines text-primary"></i> Recorded Depositions &amp; Sworn Testimonies
              </div>
              <p class="text-muted mb-0" style="font-size: 0.82rem;">
                Legally binding testimonies recorded under Vigilance Tribunal Section 14.
              </p>
            </div>
            <button class="btn-cyber-action btn-cyber-primary" onclick="showAddDepositionModal()">
              <i class="fa-solid fa-plus"></i> Record New Deposition
            </button>
          </div>

          <div class="d-flex flex-column gap-3" id="depositionList">
            <!-- Deposition 1 -->
            <div class="deposition-item-card">
              <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                <div class="d-flex align-items-center gap-3">
                  <div class="deponent-avatar bg-primary-subtle text-primary">MF</div>
                  <div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                      <span class="badge bg-primary-subtle text-primary font-monospace">MATERIAL WITNESS #01</span>
                      <strong style="font-size: 0.95rem; color: var(--text-main);">M. Farooq (Falcon Shipping Logistics Agent)</strong>
                    </div>
                    <span class="text-muted font-monospace" style="font-size: 0.75rem;">Recorded: Aug 15, 2026 • 11:30 AM • In-Person</span>
                  </div>
                </div>
                <button class="btn-cyber-action btn-cyber-secondary btn-sm" onclick="showTranscriptModal('M. Farooq', 'Witness')">
                  <i class="fa-solid fa-scroll"></i> Full Sworn Transcript
                </button>
              </div>
              <div class="p-3 rounded-3 font-monospace text-muted mt-2" style="font-size: 0.85rem; line-height: 1.7; background: var(--bg-surface); border: 1px solid var(--border);">
                "I was standing right outside counter #4 when the Assistant Examiner demanded $5,000 cash to affix the customs release stamp. He refused to log the entry in the electronic portal and stated cargo would stay stuck until paid."
              </div>
            </div>

            <!-- Deposition 2 -->
            <div class="deposition-item-card">
              <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                <div class="d-flex align-items-center gap-3">
                  <div class="deponent-avatar bg-danger-subtle text-danger">KV</div>
                  <div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                      <span class="badge bg-danger-subtle text-danger font-monospace">ACCUSED STATEMENT</span>
                      <strong style="font-size: 0.95rem; color: var(--text-main);">Assistant Examiner K. Vance</strong>
                    </div>
                    <span class="text-muted font-monospace" style="font-size: 0.75rem;">Recorded: Aug 15, 2026 • 02:00 PM • Chamber #3B</span>
                  </div>
                </div>
                <button class="btn-cyber-action btn-cyber-secondary btn-sm" onclick="showTranscriptModal('K. Vance', 'Accused')">
                  <i class="fa-solid fa-scroll"></i> Full Sworn Transcript
                </button>
              </div>
              <div class="p-3 rounded-3 font-monospace text-muted mt-2" style="font-size: 0.85rem; line-height: 1.7; background: var(--bg-surface); border: 1px solid var(--border);">
                "Denies extortion. Claims documentation was incomplete regarding container gross weight variance between Bill of Lading CT-8942 and Port Weighbridge #2."
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ═══════════════════ TAB 3: INVESTIGATOR JOURNAL & TIMELINE ═══════════════════ -->
      <div class="tab-content-pane" id="tabNotes">
        <div class="card-box">
          <div class="card-header-flex">
            <div>
              <div class="card-title">
                <i class="fa-solid fa-book-bookmark text-primary"></i> Investigator Forensic Journal &amp; Event Timeline
              </div>
              <p class="text-muted mb-0" style="font-size: 0.82rem;">
                Chronological case verification log with digital investigator signature.
              </p>
            </div>
            <button class="btn-cyber-action btn-cyber-primary" onclick="showAddNoteModal()">
              <i class="fa-solid fa-pen-nib"></i> Add Forensic Observation
            </button>
          </div>

          <div class="forensic-timeline-stream mt-4" id="journalTimelineList">
            <!-- Timeline Item 1 -->
            <div class="journal-step-node">
              <div class="journal-node-pin"><i class="fa-solid fa-video"></i></div>
              <div class="journal-card-content">
                <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                  <strong style="color: var(--primary-light); font-size: 0.95rem;">Gate Security DVR Footage Corroborated</strong>
                  <span class="badge bg-secondary-subtle text-muted font-monospace">Aug 15, 2026 • 04:15 PM</span>
                </div>
                <p class="text-muted mb-0" style="font-size: 0.85rem; line-height: 1.6;">
                  Reviewed Port CCTV Camera #09. Accused examiner seen escorting clearing agent into private booth at 10:11 AM, confirming witness timeline and presence.
                </p>
              </div>
            </div>

            <!-- Timeline Item 2 -->
            <div class="journal-step-node">
              <div class="journal-node-pin"><i class="fa-solid fa-microphone"></i></div>
              <div class="journal-card-content">
                <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                  <strong style="color: var(--primary-light); font-size: 0.95rem;">Whistleblower Audio Voiceprint Matched</strong>
                  <span class="badge bg-secondary-subtle text-muted font-monospace">Aug 15, 2026 • 01:20 PM</span>
                </div>
                <p class="text-muted mb-0" style="font-size: 0.85rem; line-height: 1.6;">
                  Audio waveform spectrum compared against public speeches of the accused examiner. Pitch and fundamental frequency match at 96.4% confidence.
                </p>
              </div>
            </div>

            <!-- Timeline Item 3 -->
            <div class="journal-step-node">
              <div class="journal-node-pin"><i class="fa-solid fa-flag"></i></div>
              <div class="journal-card-content">
                <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                  <strong style="color: var(--primary-light); font-size: 0.95rem;">Initial Whistleblower Dossier Assigned to Cell #04</strong>
                  <span class="badge bg-secondary-subtle text-muted font-monospace">Aug 14, 2026 • 09:30 AM</span>
                </div>
                <p class="text-muted mb-0" style="font-size: 0.85rem; line-height: 1.6;">
                  Anonymous encrypted submission received via Tor Sentinel relay. Immediate statutory triage initiated by Lead Investigating Officer.
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ═══════════════════ TAB 4: HEARINGS & SUMMONS ═══════════════════ -->
      <div class="tab-content-pane" id="tabSummons">
        <div class="card-box">
          <div class="card-header-flex">
            <div>
              <div class="card-title">
                <i class="fa-solid fa-gavel text-primary"></i> Vigilance Tribunal Hearing &amp; Summons Notices
              </div>
              <p class="text-muted mb-0" style="font-size: 0.82rem;">
                Official judicial summons dispatches and court appearance schedules.
              </p>
            </div>
            <button class="btn-cyber-action btn-cyber-primary" onclick="showScheduleHearingModal()">
              <i class="fa-solid fa-calendar-plus"></i> Issue Formal Summons
            </button>
          </div>

          <div class="p-4 rounded-4 mb-4" style="background: var(--bg-surface-2); border: 1px solid var(--border);">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
              <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="badge bg-warning-subtle text-warning font-monospace fs-6 px-3 py-1.5" style="border: 1px solid rgba(245,158,11,0.3);">
                  <i class="fa-solid fa-certificate me-1"></i> SUMMONS NOTICE #SUM-8941
                </span>
                <span class="badge bg-danger-subtle text-danger font-monospace">MANDATORY APPEARANCE</span>
              </div>
              <span class="font-monospace text-warning fw-bold fs-6">
                <i class="fa-solid fa-clock me-1"></i> Scheduled: TODAY • 02:30 PM
              </span>
            </div>

            <h4 style="font-size: 1.15rem; margin-bottom: 0.4rem; color: var(--text-main);">
              Summons Target: Assistant Port Examiner K. Vance
            </h4>
            <p class="text-muted mb-3" style="font-size: 0.88rem; line-height: 1.6;">
              Summons to produce container entry registry CT-8942, terminal inspection physical sign-off records, and personal duty logbooks before Tribunal Bench #3B.
            </p>

            <div class="row g-3 p-3 rounded-3 font-monospace mb-3" style="font-size: 0.8rem; background: var(--bg-surface); border: 1px solid var(--border);">
              <div class="col-md-4">
                <span class="text-muted d-block">Venue Tribunal:</span>
                <strong style="color: var(--text-main);">Bench Chamber Room #3B</strong>
              </div>
              <div class="col-md-4">
                <span class="text-muted d-block">Presiding Officer:</span>
                <strong style="color: var(--text-main);">Hon. Magistrate R. Sengupta</strong>
              </div>
              <div class="col-md-4">
                <span class="text-muted d-block">Delivery Status:</span>
                <span class="text-success fw-bold"><i class="fa-solid fa-check-double me-1"></i> Served &amp; Acknowledged</span>
              </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
              <button class="btn-cyber-action btn-cyber-secondary" onclick="showSubpoenaModal()">
                <i class="fa-solid fa-file-contract"></i> View Official Subpoena
              </button>
              <a href="hearings.php" class="btn-cyber-action btn-cyber-primary">
                <i class="fa-solid fa-gavel"></i> Tribunal Hearings Room
              </a>
            </div>
          </div>
        </div>
      </div>

      <!-- ═══════════════════ TAB 5: JUDICIAL CHARGE SHEET GENERATOR ═══════════════════ -->
      <div class="tab-content-pane" id="tabChargesheet">
        <div class="card-box">
          <div class="card-header-flex">
            <div>
              <div class="card-title">
                <i class="fa-solid fa-scale-balanced text-primary"></i> Judicial Prosecution Dossier &amp; Charge Sheet
              </div>
              <p class="text-muted mb-0" style="font-size: 0.82rem;">
                Compiled statutory brief for transmission to the Special Anti-Corruption Court.
              </p>
            </div>
            <div class="d-flex gap-2">
              <button class="btn-cyber-action btn-cyber-secondary" onclick="printChargeSheetQuick()">
                <i class="fa-solid fa-print"></i> Print Charge Sheet
              </button>
              <button class="btn-cyber-action btn-cyber-primary" onclick="showOfficerToast('Official Judicial Charge Sheet Compiled & Signed with Private Key (AC-819)!', 'success')">
                <i class="fa-solid fa-file-signature"></i> Transmit to Court
              </button>
            </div>
          </div>

          <!-- Official Document Paper View -->
          <div class="charge-sheet-paper font-monospace" style="font-size: 0.86rem; line-height: 1.8;">
            <div class="charge-sheet-watermark">CONFIDENTIAL</div>

            <div class="text-center pb-3 mb-4" style="border-bottom: 1px solid var(--border);">
              <div class="d-flex justify-content-center mb-2">
                <div class="p-3 rounded-circle bg-primary-subtle text-primary fs-3">
                  <i class="fa-solid fa-scale-balanced"></i>
                </div>
              </div>
              <h4 style="font-family: var(--font-heading); margin-bottom: 2px; font-weight: 800; color: var(--text-main);">
                NATIONAL ANTI-CORRUPTION VIGILANCE COMMISSION
              </h4>
              <div class="text-muted" style="font-size: 0.8rem;">
                STATUTORY PROSECUTION CHARGE SHEET — SECTION 7 &amp; 13(1)(d) PCA
              </div>
              <div class="text-primary fw-bold fs-5 mt-1" id="csDocketDisplay">DOCKET NO: CCMS-2026-9082</div>
            </div>

            <div class="mb-3">
              <strong class="text-primary d-block mb-1">I. PARTICULARS OF THE ACCUSED:</strong>
              <div class="p-2.5 rounded" style="background: var(--bg-surface); border: 1px solid var(--border); color: var(--text-main);">
                Name: <strong>Assistant Examiner K. Vance</strong> • Designation: Port Inspection Gate #4 Officer • Bureau: Revenue &amp; Customs Port Authority
              </div>
            </div>

            <div class="mb-3">
              <strong class="text-primary d-block mb-1">II. STATUTORY PENAL SECTIONS CHARGED:</strong>
              <div class="p-2.5 rounded" style="background: var(--bg-surface); border: 1px solid var(--border); color: var(--text-main);">
                1. <strong>Section 7, PCA (1988/2018):</strong> Public servant obtaining undue advantage for performance of public duty.<br>
                2. <strong>Section 13(1)(b) / 13(2):</strong> Criminal misconduct by intentionally withholding official customs clearance stamps.
              </div>
            </div>

            <div class="mb-3">
              <strong class="text-primary d-block mb-1">III. CORROBORATED FORENSIC EVIDENCE MATRIX:</strong>
              <div class="table-responsive">
                <table class="table table-sm table-bordered mt-1 mb-0" style="font-size: 0.8rem; color: var(--text-main);">
                  <thead>
                    <tr class="table-active">
                      <th>Evidence Item</th>
                      <th>SHA-256 Checksum</th>
                      <th>Forensic Weight</th>
                      <th>Chain of Custody</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td>EVD-9082-A (Audio Recording)</td>
                      <td>0x8a92f147c...e41b</td>
                      <td>Direct Verbal Corroboration (96.4% match)</td>
                      <td><span class="text-success fw-bold">Verified</span></td>
                    </tr>
                    <tr>
                      <td>EVD-9082-B (Withheld Manifest PDF)</td>
                      <td>0x3f1c88a91...99d2</td>
                      <td>Documentary Extortion Timestamp Match</td>
                      <td><span class="text-success fw-bold">Verified</span></td>
                    </tr>
                    <tr>
                      <td>CCTV Port Camera #09 DVR</td>
                      <td>0x41e009a2b...77f1</td>
                      <td>Visual Presence in Private Booth</td>
                      <td><span class="text-success fw-bold">Verified</span></td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <div class="mb-4">
              <strong class="text-primary d-block mb-1">IV. INVESTIGATOR DISCIPLINARY RECOMMENDATION:</strong>
              <div class="p-3 rounded" style="background: var(--bg-surface); border: 1px solid var(--border); color: var(--text-main);">
                Immediate statutory suspension from active port service, confiscation of inspection credentials, and formal commencement of summary trial proceedings before the Vigilance Special Court.
              </div>
            </div>

            <div class="d-flex justify-content-between align-items-center pt-3 flex-wrap gap-2" style="border-top: 1px solid var(--border);">
              <div>
                <span class="text-muted d-block" style="font-size: 0.75rem;">Digital Cryptographic Seal:</span>
                <span class="text-primary fw-bold">RSA-4096 / SHA-256 SIGNED (Insp. K. Vance AC-819)</span>
              </div>
              <div class="text-end">
                <span class="text-muted d-block" style="font-size: 0.75rem;">Transmission Timestamp:</span>
                <strong style="color: var(--text-main);">2026-08-20 15:45:00 UTC</strong>
              </div>
            </div>
          </div>
        </div>
      </div>

    </main>
  </div>
</div>

<!-- ═══════════════════ MODALS ═══════════════════ -->

<!-- Spectrogram Modal -->
<div class="modal fade" id="spectrogramModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0" style="background: var(--bg-card); border: 1px solid var(--border) !important; border-radius: 20px; box-shadow: var(--card-shadow);">
      <div class="modal-header" style="border-color: var(--border);">
        <h5 class="modal-title d-flex align-items-center gap-2" style="color: var(--text-main);">
          <i class="fa-solid fa-wave-square text-primary"></i> Forensic Speech Waveform &amp; Spectrogram
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <div class="spectrogram-preview mb-3">
          <div class="spectrogram-grid"></div>
        </div>
        <div class="row g-3 font-monospace" style="font-size: 0.82rem;">
          <div class="col-md-4">
            <span class="text-muted d-block">Fundamental F0:</span>
            <strong style="color: var(--text-main);">124.8 Hz (Male Adult)</strong>
          </div>
          <div class="col-md-4">
            <span class="text-muted d-block">Harmonic SNR:</span>
            <strong class="text-success">28.4 dB (High Clarity)</strong>
          </div>
          <div class="col-md-4">
            <span class="text-muted d-block">Tamper Probability:</span>
            <strong class="text-success"><i class="fa-solid fa-check me-1"></i> 0.00% (Clean Audio)</strong>
          </div>
        </div>
      </div>
      <div class="modal-footer" style="border-color: var(--border);">
        <button type="button" class="btn-cyber-action btn-cyber-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn-cyber-action btn-cyber-primary" onclick="showOfficerToast('Spectrogram Analysis Certificate Exported!', 'success')">
          <i class="fa-solid fa-download"></i> Export Analysis Report
        </button>
      </div>
    </div>
  </div>
</div>

<!-- PDF Extract Modal -->
<div class="modal fade" id="pdfExtractModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0" style="background: var(--bg-card); border: 1px solid var(--border) !important; border-radius: 20px; box-shadow: var(--card-shadow);">
      <div class="modal-header" style="border-color: var(--border);">
        <h5 class="modal-title d-flex align-items-center gap-2" style="color: var(--text-main);">
          <i class="fa-solid fa-file-pdf text-danger"></i> OCR Text Extract &amp; Forensics Preview
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <div class="p-3 rounded-3 font-monospace mb-3" style="font-size: 0.85rem; line-height: 1.7; max-height: 250px; overflow-y: auto; background: var(--bg-surface); border: 1px solid var(--border); color: var(--text-main);">
          <strong>[PORT AUTHORITY OF REVENUE &amp; CUSTOMS — BILL OF ENTRY CT-8942]</strong><br>
          DATE: 2026-08-14 | TERMINAL: GATE #4 CONTAINER YARD<br>
          IMPORTER: FALCON LOGISTICS TRADING CORP<br>
          CONTAINER ID: MSKU-908214-7 (COMMERCIAL ELECTRONICS)<br>
          <br>
          <span class="text-danger fw-bold">--- PHYSICAL REFUSAL ENDORSEMENT ---</span><br>
          "Held for manual weighbridge audit. Clearance stamp refused pending physical examiner inspection."<br>
          SIGNED: [K. Vance, Asst Examiner #819]<br>
          TIMESTAMP STAMP: 2026-08-14 10:14:02 UTC
        </div>
        <div class="d-flex justify-content-between align-items-center text-muted font-monospace" style="font-size: 0.8rem;">
          <span>OCR Accuracy: <strong style="color: var(--text-main);">99.8% (Tesseract OCR Engine)</strong></span>
          <span>Signature Vector Match: <strong class="text-success">98.2%</strong></span>
        </div>
      </div>
      <div class="modal-footer" style="border-color: var(--border);">
        <button type="button" class="btn-cyber-action btn-cyber-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn-cyber-action btn-cyber-primary" onclick="showOfficerToast('Document OCR extract copied to clipboard!', 'success')">
          <i class="fa-solid fa-copy"></i> Copy OCR Text
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Subpoena Modal -->
<div class="modal fade" id="subpoenaModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0" style="background: var(--bg-card); border: 1px solid var(--border) !important; border-radius: 20px; box-shadow: var(--card-shadow);">
      <div class="modal-header" style="border-color: var(--border);">
        <h5 class="modal-title d-flex align-items-center gap-2" style="color: var(--text-main);">
          <i class="fa-solid fa-file-contract text-warning"></i> Statutory Summons Subpoena
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4 text-center">
        <div class="font-monospace p-3 rounded-3 mb-3 text-start" style="font-size: 0.82rem; line-height: 1.7; background: var(--bg-surface); border: 1px solid var(--border); color: var(--text-main);">
          <div class="text-center fw-bold text-primary mb-2">VIGILANCE TRIBUNAL BENCH #3B</div>
          <div><strong>SUMMONS TOKEN:</strong> SUM-8941</div>
          <div><strong>TARGET OFFICIAL:</strong> Assistant Port Examiner K. Vance</div>
          <div><strong>MANDATORY VENUE:</strong> Main Courtroom Bench 3B</div>
          <div><strong>DATE &amp; TIME:</strong> Aug 20, 2026 • 02:30 PM</div>
          <div class="text-danger mt-2">NOTICE: Failure to appear constitutes statutory contempt of Vigilance Tribunal under Section 19.</div>
        </div>
      </div>
      <div class="modal-footer" style="border-color: var(--border);">
        <button type="button" class="btn-cyber-action btn-cyber-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn-cyber-action btn-cyber-primary" onclick="showOfficerToast('Subpoena Certificate Dispatched to Registry!', 'success')">
          <i class="fa-solid fa-paper-plane"></i> Re-dispatch Notice
        </button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="assets/js/officer.js"></script>

<script>
  // Case Database for dynamic loading
  const caseDatabase = {
    'CCMS-2026-9082': {
      token: 'CCMS-2026-9082',
      title: 'Bribery demand for shipping container clearance certificate',
      entity: 'Revenue & Customs Port Division',
      accused: 'Assistant Examiner K. Vance',
      priority: 'CRITICAL',
      mode: '100% Anonymous Whistleblower',
      modeIcon: 'fa-user-ninja',
      status: 'Investigation Active',
      amount: '$5,000.00 USD',
      venue: 'Aug 14, 2026 • Port Gate #4',
      evidenceCount: '2 Verified Files (Audio+Doc)',
      cell: 'Anti-Corruption Cell #04',
      sla: '35h 20m Remaining'
    },
    'CCMS-2026-9114': {
      token: 'CCMS-2026-9114',
      title: 'Urban Land Registry Title Deed Extortion & Forgery',
      entity: 'Urban Land Registry Dept',
      accused: 'Sub-Registrar Zone 4 Clerk',
      priority: 'HIGH',
      mode: 'Verified Citizen Report',
      modeIcon: 'fa-id-card',
      status: 'Witness Summons',
      amount: '$22,000.00 USD',
      venue: 'Aug 13, 2026 • Commercial Registry',
      evidenceCount: '4 Verified Deeds (PDF)',
      cell: 'Anti-Corruption Cell #04',
      sla: '48h 00m Remaining'
    },
    'CCMS-2026-8802': {
      token: 'CCMS-2026-8802',
      title: 'Municipal Building Approvals Expediting Kickbacks',
      entity: 'Municipal Building Approvals',
      accused: 'Permit Director S. Roy',
      priority: 'MEDIUM',
      mode: 'Anonymous Whistleblower',
      modeIcon: 'fa-user-ninja',
      status: 'Evidence Review',
      amount: '$3,500.00 USD',
      venue: 'Aug 10, 2026 • Civic Center',
      evidenceCount: '1 Photo File (PNG)',
      cell: 'Anti-Corruption Cell #02',
      sla: '5 Days Remaining'
    },
    'CCMS-2026-7910': {
      token: 'CCMS-2026-7910',
      title: 'Healthcare & Medical Supply Hospital Equipment Tender Kickbacks',
      entity: 'Healthcare Procurement Division',
      accused: 'Director of Supplies M. Jenkins',
      priority: 'HIGH',
      mode: 'Verified Citizen Report',
      modeIcon: 'fa-id-card',
      status: 'Charge Sheet Filed',
      amount: '$85,000.00 USD',
      venue: 'Aug 02, 2026 • Central Medical Depot',
      evidenceCount: '6 Forensic Documents (PDF)',
      cell: 'Anti-Corruption Cell #01',
      sla: 'Concluded'
    }
  };

  document.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    const caseToken = urlParams.get('case') || 'CCMS-2026-9082';
    loadCaseData(caseToken);
    initTabHoverMarquee();
  });

  function initTabHoverMarquee() {
    document.querySelectorAll('.inv-tab-btn').forEach(btn => {
      btn.addEventListener('mouseenter', () => {
        const container = btn.querySelector('.tab-marquee-container');
        const label = btn.querySelector('.tab-marquee-label');
        if (container && label) {
          const diff = label.scrollWidth - container.clientWidth;
          if (diff > 2) {
            label.style.setProperty('--marquee-overflow', `-${diff + 12}px`);
          } else {
            label.style.setProperty('--marquee-overflow', '0px');
          }
        }
      });
      btn.addEventListener('mouseleave', () => {
        const label = btn.querySelector('.tab-marquee-label');
        if (label) {
          label.style.removeProperty('--marquee-overflow');
        }
      });
    });
  }

  function loadCaseData(token) {
    const data = caseDatabase[token] || caseDatabase['CCMS-2026-9082'];
    
    document.getElementById('headerCaseToken').innerText = '#' + data.token;
    document.getElementById('caseTokenDisplay').innerText = data.token;
    document.getElementById('caseTitleDisplay').innerText = data.title;
    document.getElementById('caseEntityDisplay').innerText = data.entity;
    document.getElementById('caseAccusedDisplay').innerText = data.accused;
    document.getElementById('caseAmountDisplay').innerText = data.amount;
    document.getElementById('caseVenueDisplay').innerText = data.venue;
    document.getElementById('caseEvidenceCountDisplay').innerText = data.evidenceCount;
    document.getElementById('caseCellDisplay').innerText = data.cell;
    document.getElementById('slaClockText').innerText = data.sla;
    document.getElementById('csDocketDisplay').innerText = 'DOCKET NO: ' + data.token;

    // Priority pill styling
    const prioEl = document.getElementById('casePriorityDisplay');
    prioEl.innerText = data.priority + ' PRIORITY';
    prioEl.className = 'priority-pill ' + (data.priority === 'CRITICAL' ? 'priority-critical' : data.priority === 'HIGH' ? 'priority-high' : 'priority-medium');

    // Complainant mode
    document.getElementById('caseModeDisplay').innerHTML = `<i class="fa-solid ${data.modeIcon} me-1"></i> ${data.mode}`;
  }

  /* ── Clean Tab Switching (No Side Scroller) ── */
  function switchInvTab(tabName, btn) {
    document.querySelectorAll('.inv-tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.tab-content-pane').forEach(p => p.classList.remove('active'));

    btn.classList.add('active');

    const targetMap = {
      'evidence': 'tabEvidence',
      'depositions': 'tabDepositions',
      'notes': 'tabNotes',
      'summons': 'tabSummons',
      'chargesheet': 'tabChargesheet'
    };
    const targetPane = document.getElementById(targetMap[tabName]);
    if (targetPane) {
      targetPane.classList.add('active');
    }
  }

  /* ── Interactive Audio Simulation ── */
  let isAudioPlaying = false;
  let audioTimerInterval = null;
  let audioSeconds = 0;

  function toggleAudioPlayback() {
    const icon = document.getElementById('playAudioIcon');
    const waveBars = document.getElementById('audioWaveBars');
    const timerText = document.getElementById('audioTimer');
    const pBar = document.getElementById('audioProgressBar');

    isAudioPlaying = !isAudioPlaying;

    if (isAudioPlaying) {
      icon.className = 'fa-solid fa-pause';
      waveBars.querySelectorAll('.wave-bar').forEach(b => b.classList.add('playing'));
      showOfficerToast('Playing forensic audio recording clip...', 'info');

      audioTimerInterval = setInterval(() => {
        audioSeconds++;
        const mins = Math.floor(audioSeconds / 60);
        const secs = audioSeconds % 60;
        const fmtSecs = secs < 10 ? '0' + secs : secs;
        timerText.innerText = `0${mins}:${fmtSecs} / 02:45`;
        pBar.style.width = ((audioSeconds / 165) * 100) + '%';

        if (audioSeconds >= 165) {
          toggleAudioPlayback();
        }
      }, 1000);
    } else {
      icon.className = 'fa-solid fa-play';
      waveBars.querySelectorAll('.wave-bar').forEach(b => b.classList.remove('playing'));
      if (audioTimerInterval) clearInterval(audioTimerInterval);
    }
  }

  function showSpectrogramModal() {
    new bootstrap.Modal(document.getElementById('spectrogramModal')).show();
  }

  function showPdfExtractModal() {
    new bootstrap.Modal(document.getElementById('pdfExtractModal')).show();
  }

  function showSubpoenaModal() {
    new bootstrap.Modal(document.getElementById('subpoenaModal')).show();
  }

  function verifyHash(evidenceToken) {
    Swal.fire({
      title: 'Cryptographic Checksum Verified',
      html: `
        <div class="text-center font-monospace" style="font-size:0.85rem;">
          <i class="fa-solid fa-circle-check text-success fs-1 mb-2"></i>
          <p class="mb-1" style="color:var(--text-main);">Evidence Token: <strong>${evidenceToken}</strong></p>
          <p class="text-muted mb-2">SHA-256 matches blockchain immutable cold storage ledger.</p>
          <span class="badge bg-success-subtle text-success">Zero Tampering Detected</span>
        </div>
      `,
      confirmButtonText: 'Affix Seal & Close',
      confirmButtonColor: '#4f46e5'
    });
  }

  function showAddEvidenceModal() {
    Swal.fire({
      title: 'Upload Forensic Evidence File',
      html: `
        <input type="text" id="evdTitle" class="swal2-input" placeholder="Evidence File Description">
        <select id="evdType" class="swal2-select" style="width:80%;">
          <option value="Audio">Audio Recording (MP3 / WAV)</option>
          <option value="PDF">Documentary Evidence (PDF / Scan)</option>
          <option value="Video">CCTV Surveillance Footage (MP4)</option>
          <option value="Financial">Bank Ledger / Wire Registry</option>
        </select>
        <input type="file" class="swal2-file" style="width:80%;">
      `,
      confirmButtonText: 'Encrypt & Seal into Vault',
      confirmButtonColor: '#4f46e5',
      showCancelButton: true
    }).then((res) => {
      if (res.isConfirmed) {
        showOfficerToast('New forensic evidence cryptographically indexed!', 'success');
      }
    });
  }

  function showAddDepositionModal() {
    Swal.fire({
      title: 'Record Sworn Deposition',
      html: `
        <input id="depName" class="swal2-input" placeholder="Deponent Legal Name & Title">
        <select id="depRole" class="swal2-select" style="width:80%;">
          <option value="Witness">Material Eyewitness</option>
          <option value="Accused">Accused Public Servant</option>
          <option value="Complainant">Complainant / Whistleblower</option>
        </select>
        <textarea id="depText" class="swal2-textarea" placeholder="Enter verbatim sworn statement under oath..."></textarea>
      `,
      confirmButtonText: 'Sign & Seal Deposition',
      confirmButtonColor: '#4f46e5',
      showCancelButton: true
    }).then((res) => {
      if (res.isConfirmed) {
        showOfficerToast('Deposition recorded and sealed into evidence log!', 'success');
      }
    });
  }

  function showTranscriptModal(name, role) {
    Swal.fire({
      title: `Sworn Transcript: ${name}`,
      html: `
        <div class="text-start font-monospace p-3 rounded-3" style="font-size:0.82rem; line-height:1.7; background:var(--bg-surface); border:1px solid var(--border);">
          <div class="text-primary fw-bold mb-2">RECORDED UNDER VIGILANCE TRIBUNAL SECTION 14</div>
          <p style="color:var(--text-main);"><strong>Deponent:</strong> ${name} (${role})</p>
          <p style="color:var(--text-main);"><strong>Sworn Statement:</strong></p>
          <p class="text-muted">"I affirm under penalty of perjury that the facts stated during the investigation session are true, verbatim, and corroborated by container records CT-8942."</p>
          <div class="text-success fw-bold"><i class="fa-solid fa-stamp me-1"></i> Digital Signature Seal: VERIFIED</div>
        </div>
      `,
      confirmButtonText: 'Export Sworn Certificate',
      confirmButtonColor: '#4f46e5'
    });
  }

  function showAddNoteModal() {
    Swal.fire({
      title: 'Add Forensic Investigation Note',
      input: 'textarea',
      inputPlaceholder: 'Enter forensic corroboration details, surveillance observations...',
      confirmButtonText: 'Save Note to Journal',
      confirmButtonColor: '#4f46e5',
      showCancelButton: true
    }).then((res) => {
      if (res.isConfirmed && res.value) {
        showOfficerToast('Forensic observation logged with timestamp!', 'success');
      }
    });
  }

  function showScheduleHearingModal() {
    Swal.fire({
      title: 'Issue Official Summons Notice',
      html: `
        <input id="sumTarget" class="swal2-input" placeholder="Summons Target Official/Witness">
        <input id="sumDate" type="date" class="swal2-input" value="2026-08-20">
        <input id="sumVenue" class="swal2-input" placeholder="Hearing Tribunal Courtroom" value="Tribunal Bench Room #3B">
      `,
      confirmButtonText: 'Issue Statutory Summons',
      confirmButtonColor: '#4f46e5',
      showCancelButton: true
    }).then((res) => {
      if (res.isConfirmed) {
        showOfficerToast('Statutory Summons dispatched via encrypted registry!', 'success');
      }
    });
  }

  function showGenerateChargeSheetModal() {
    Swal.fire({
      icon: 'question',
      title: 'Finalize Prosecution Charge Sheet?',
      text: 'This will compile all forensic evidence checksums and transmit the docket to the Anti-Corruption Special Court.',
      showCancelButton: true,
      confirmButtonText: 'Yes, Submit to Special Court',
      confirmButtonColor: '#4f46e5'
    }).then((res) => {
      if (res.isConfirmed) {
        showOfficerToast('Dossier successfully transmitted to Judicial Court!', 'success');
      }
    });
  }

  function exportForensicBundle() {
    showOfficerToast('Generating encrypted ZIP of verified forensic evidence...', 'info');
    setTimeout(() => {
      showOfficerToast('Forensic evidence package downloaded (SHA verified)!', 'success');
    }, 1500);
  }

  function printChargeSheetQuick() {
    window.print();
  }
</script>
</body>
</html>
