<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Live Case Radar & Telemetry — CCMS Citizen</title>
  <meta name="description" content="Track your corruption complaint in real-time with encrypted case telemetry, investigation timeline, and live officer dispatch console.">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&family=Space+Grotesk:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
  <link rel="stylesheet" href="assets/css/citizen.css">

  <style>
    /* ═══ Ultra-Premium Adaptive Case Tracker Theme ═══ */

    /* ── Tracking HUD Hero Banner ── */
    .tracker-hud-card {
      background: var(--bg-card);
      border: 1px solid var(--border);
      border-radius: 24px;
      padding: 2rem 2.25rem;
      margin-bottom: 2rem;
      position: relative;
      overflow: hidden;
      box-shadow: var(--card-shadow);
      backdrop-filter: blur(20px);
      -webkit-backdrop-filter: blur(20px);
      transition: var(--transition);
    }
    .tracker-hud-card::before {
      content: '';
      position: absolute;
      top: -90px;
      right: -70px;
      width: 280px;
      height: 280px;
      border-radius: 50%;
      background: radial-gradient(circle, var(--cyan-glow), transparent 70%);
      pointer-events: none;
      opacity: 0.6;
    }
    .tracker-hud-card::after {
      content: '';
      position: absolute;
      bottom: -80px;
      left: 10%;
      width: 240px;
      height: 240px;
      border-radius: 50%;
      background: radial-gradient(circle, var(--primary-glow), transparent 70%);
      pointer-events: none;
      opacity: 0.4;
    }

    /* Radar Disk Scanner */
    .radar-scan-wrapper {
      width: 64px;
      height: 64px;
      border-radius: 50%;
      background: var(--bg-surface-2);
      border: 2px solid var(--cyan);
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
      flex-shrink: 0;
      box-shadow: 0 0 25px var(--cyan-glow);
    }
    .radar-scan-wrapper::before {
      content: '';
      position: absolute;
      inset: -6px;
      border-radius: 50%;
      border: 1.5px dashed var(--cyan);
      opacity: 0.4;
      animation: rotateDash 12s linear infinite;
    }
    @keyframes rotateDash {
      from { transform: rotate(0deg); }
      to { transform: rotate(360deg); }
    }
    .radar-sweep-blade {
      position: absolute;
      inset: 0;
      border-radius: 50%;
      background: conic-gradient(from 0deg, transparent 270deg, var(--cyan) 360deg);
      opacity: 0.35;
      animation: sweepAnim 3s linear infinite;
    }
    @keyframes sweepAnim {
      from { transform: rotate(0deg); }
      to { transform: rotate(360deg); }
    }

    /* Token Search Control */
    .token-search-group {
      display: flex;
      align-items: center;
      background: var(--bg-surface-2);
      border: 1.5px solid var(--border);
      border-radius: 16px;
      padding: 0.3rem 0.35rem 0.3rem 1rem;
      transition: var(--transition);
      box-shadow: inset 0 2px 4px rgba(0,0,0,0.05);
    }
    .token-search-group:focus-within {
      border-color: var(--primary);
      box-shadow: 0 0 0 3px var(--primary-glow);
    }
    .token-search-input {
      background: transparent;
      border: none;
      color: var(--text-main);
      font-family: var(--font-mono);
      font-weight: 700;
      font-size: 0.92rem;
      outline: none;
      width: 175px;
    }
    .token-search-input::placeholder {
      color: var(--text-muted);
      font-weight: 400;
      font-family: var(--font-body);
    }
    .token-search-btn {
      background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
      color: #ffffff;
      border: none;
      border-radius: 12px;
      padding: 0.55rem 1.15rem;
      font-size: 0.85rem;
      font-weight: 600;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 0.45rem;
      transition: var(--transition);
    }
    .token-search-btn:hover {
      background: linear-gradient(135deg, var(--primary-light) 0%, var(--primary) 100%);
      transform: translateY(-1px);
      box-shadow: 0 4px 15px var(--primary-glow);
      color: #ffffff;
    }

    /* Preset Quick Chips */
    .token-preset-chip {
      background: var(--bg-surface-2);
      border: 1px solid var(--border);
      color: var(--text-muted);
      border-radius: 9999px;
      padding: 0.3rem 0.85rem;
      font-size: 0.76rem;
      font-family: var(--font-mono);
      font-weight: 700;
      cursor: pointer;
      transition: var(--transition);
    }
    .token-preset-chip:hover {
      border-color: var(--primary-light);
      color: var(--primary-light);
      transform: translateY(-1px);
    }
    .token-preset-chip.active {
      background: linear-gradient(135deg, var(--primary) 0%, var(--cyan) 100%);
      border-color: transparent;
      color: #ffffff;
      box-shadow: 0 4px 12px var(--primary-glow);
    }

    /* Telemetry KPI Strip */
    .tele-kpi-card {
      background: var(--bg-surface-2);
      border: 1px solid var(--border);
      border-radius: 16px;
      padding: 1.1rem 1.25rem;
      height: 100%;
      display: flex;
      flex-direction: column;
      justify-content: center;
      transition: var(--transition);
    }
    .tele-kpi-card:hover {
      border-color: var(--border-glow);
      transform: translateY(-2px);
      box-shadow: var(--card-glow-shadow);
    }
    .tele-kpi-title {
      font-size: 0.72rem;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      color: var(--text-muted);
      font-family: var(--font-mono);
      font-weight: 600;
      margin-bottom: 0.4rem;
    }
    .tele-kpi-val {
      font-size: 0.95rem;
      font-weight: 700;
      color: var(--text-main);
      display: flex;
      align-items: center;
      gap: 0.45rem;
    }

    /* Hash Chip Button */
    .hash-copy-chip {
      display: inline-flex;
      align-items: center;
      gap: 0.4rem;
      background: rgba(16, 185, 129, 0.12);
      color: var(--emerald);
      border: 1px solid rgba(16, 185, 129, 0.25);
      border-radius: 10px;
      padding: 0.28rem 0.75rem;
      font-family: var(--font-mono);
      font-size: 0.8rem;
      font-weight: 700;
      cursor: pointer;
      transition: var(--transition);
    }
    .hash-copy-chip:hover {
      background: rgba(16, 185, 129, 0.22);
      border-color: var(--emerald);
      box-shadow: 0 0 12px var(--emerald-glow);
    }

    /* ── 5-Step Visual Investigation Pipeline ── */
    .pipeline-container {
      background: var(--bg-card);
      border: 1px solid var(--border);
      border-radius: 20px;
      padding: 1.5rem 1.75rem;
      margin-bottom: 2rem;
      box-shadow: var(--card-shadow);
    }
    .pipeline-steps-track {
      display: flex;
      align-items: flex-start;
      position: relative;
      padding: 0.5rem 0;
    }
    .pipeline-step {
      flex: 1;
      display: flex;
      flex-direction: column;
      align-items: center;
      position: relative;
      text-align: center;
      z-index: 1;
    }
    .pipeline-step:not(:last-child)::after {
      content: '';
      position: absolute;
      top: 22px;
      left: 50%;
      width: 100%;
      height: 3px;
      background: var(--bg-surface-2);
      border-top: 1px solid var(--border);
      border-bottom: 1px solid var(--border);
      z-index: -1;
      transition: var(--transition);
    }
    .pipeline-step.step-done:not(:last-child)::after {
      background: var(--emerald);
      border-color: var(--emerald);
      box-shadow: 0 0 8px var(--emerald-glow);
    }
    .pipeline-step.step-active:not(:last-child)::after {
      background: linear-gradient(90deg, var(--cyan), var(--bg-surface-2));
      border-color: transparent;
    }
    .step-indicator {
      width: 44px;
      height: 44px;
      border-radius: 50%;
      background: var(--bg-surface-2);
      border: 2px solid var(--border);
      color: var(--text-muted);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 0.95rem;
      font-weight: 700;
      transition: var(--transition);
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
      margin-bottom: 0.65rem;
    }
    .pipeline-step.step-done .step-indicator {
      background: var(--emerald);
      border-color: #34d399;
      color: #ffffff;
      box-shadow: 0 0 18px var(--emerald-glow);
    }
    .pipeline-step.step-active .step-indicator {
      background: linear-gradient(135deg, var(--cyan) 0%, var(--primary) 100%);
      border-color: #38bdf8;
      color: #ffffff;
      box-shadow: 0 0 22px var(--cyan-glow);
      animation: pulseActiveStep 2s infinite;
    }
    @keyframes pulseActiveStep {
      0%, 100% { transform: scale(1); box-shadow: 0 0 15px var(--cyan-glow); }
      50% { transform: scale(1.12); box-shadow: 0 0 28px var(--cyan-glow); }
    }
    .step-title-text {
      font-size: 0.78rem;
      font-weight: 700;
      color: var(--text-muted);
      line-height: 1.3;
      max-width: 90px;
    }
    .pipeline-step.step-done .step-title-text {
      color: var(--emerald);
    }
    .pipeline-step.step-active .step-title-text {
      color: var(--cyan);
    }

    /* ── Timeline Radar Left Column ── */
    .timeline-stream-box {
      position: relative;
      padding-left: 2.75rem;
      margin-top: 0.5rem;
    }
    .timeline-stream-box::before {
      content: '';
      position: absolute;
      left: 19px;
      top: 20px;
      bottom: 25px;
      width: 2.5px;
      background: linear-gradient(180deg, var(--emerald) 0%, var(--cyan) 50%, var(--border) 100%);
      border-radius: 4px;
    }
    .timeline-card-node {
      position: relative;
      margin-bottom: 1.75rem;
    }
    .timeline-node-dot {
      position: absolute;
      left: -2.75rem;
      top: 1.25rem;
      width: 40px;
      height: 40px;
      border-radius: 50%;
      background: var(--bg-surface);
      border: 2px solid var(--border);
      color: var(--text-muted);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 0.95rem;
      z-index: 2;
      transition: var(--transition);
    }
    .timeline-node-dot.done {
      background: var(--emerald);
      border-color: #34d399;
      color: #ffffff;
      box-shadow: 0 0 16px var(--emerald-glow);
    }
    .timeline-node-dot.active {
      background: linear-gradient(135deg, var(--cyan) 0%, var(--primary) 100%);
      border-color: #38bdf8;
      color: #ffffff;
      box-shadow: 0 0 22px var(--cyan-glow);
    }
    .timeline-inner-card {
      background: var(--bg-surface-2);
      border: 1px solid var(--border);
      border-radius: 18px;
      padding: 1.35rem 1.5rem;
      transition: var(--transition);
    }
    .timeline-inner-card:hover {
      border-color: var(--border-glow);
      transform: translateX(4px);
      box-shadow: var(--card-shadow);
    }
    .timeline-inner-card.active-card {
      border-color: var(--cyan);
      background: linear-gradient(135deg, var(--bg-surface-2) 0%, rgba(6, 182, 212, 0.08) 100%);
      box-shadow: 0 0 25px rgba(6, 182, 212, 0.12);
    }

    /* ── Encrypted Dispatch Console (Right Column) ── */
    .dispatch-box-wrapper {
      background: var(--bg-card);
      border: 1px solid var(--border);
      border-radius: 24px;
      display: flex;
      flex-direction: column;
      height: 100%;
      min-height: 580px;
      box-shadow: var(--card-shadow);
      overflow: hidden;
    }
    .dispatch-box-header {
      padding: 1.25rem 1.5rem;
      background: var(--bg-surface-2);
      border-bottom: 1px solid var(--border);
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    .dispatch-chat-viewport {
      flex: 1;
      overflow-y: auto;
      padding: 1.4rem;
      display: flex;
      flex-direction: column;
      gap: 1.2rem;
      background: var(--bg-base);
      max-height: 380px;
    }

    /* Officer Bubble */
    .chat-bubble-officer {
      max-width: 88%;
      background: var(--bg-surface);
      border: 1.5px solid var(--border);
      border-radius: 18px 18px 18px 4px;
      padding: 1.1rem 1.25rem;
      box-shadow: 0 4px 15px rgba(0,0,0,0.06);
      transition: var(--transition);
    }
    .chat-bubble-officer:hover {
      border-color: var(--primary-light);
    }
    .officer-bubble-sender {
      color: var(--primary-light);
      font-size: 0.82rem;
      font-weight: 700;
      display: flex;
      align-items: center;
      gap: 0.4rem;
    }

    /* Citizen Whistleblower Bubble */
    .chat-bubble-citizen {
      max-width: 88%;
      margin-left: auto;
      background: linear-gradient(135deg, var(--primary) 0%, #0284c7 100%);
      color: #ffffff !important;
      border: 1px solid rgba(255, 255, 255, 0.2);
      border-radius: 18px 18px 4px 18px;
      padding: 1.1rem 1.25rem;
      box-shadow: 0 6px 20px rgba(99, 102, 241, 0.35);
      transition: var(--transition);
    }
    .citizen-bubble-sender {
      color: #bae6fd;
      font-size: 0.82rem;
      font-weight: 700;
      display: flex;
      align-items: center;
      gap: 0.4rem;
    }

    /* Quick Reply Buttons */
    .quick-pill-btn {
      background: var(--bg-surface);
      border: 1px solid var(--border);
      color: var(--text-muted);
      border-radius: 10px;
      padding: 0.35rem 0.85rem;
      font-size: 0.76rem;
      font-weight: 600;
      cursor: pointer;
      transition: var(--transition);
      display: inline-flex;
      align-items: center;
      gap: 0.35rem;
    }
    .quick-pill-btn:hover {
      background: var(--bg-surface-2);
      border-color: var(--primary-light);
      color: var(--primary-light);
      transform: translateY(-1px);
    }

    /* Chat Input Form */
    .dispatch-input-area {
      padding: 1.1rem 1.25rem;
      background: var(--bg-surface-2);
      border-top: 1px solid var(--border);
      margin-top: auto;
    }
    .dispatch-text-input {
      background: var(--bg-surface);
      border: 1.5px solid var(--border);
      color: var(--text-main);
      border-radius: 14px;
      padding: 0.75rem 1.1rem;
      font-size: 0.88rem;
      width: 100%;
      outline: none;
      transition: var(--transition);
    }
    .dispatch-text-input:focus {
      border-color: var(--cyan);
      box-shadow: 0 0 0 3px var(--cyan-glow);
    }
    .dispatch-send-icon-btn {
      background: linear-gradient(135deg, var(--cyan) 0%, var(--primary) 100%);
      color: #ffffff;
      border: none;
      width: 44px;
      height: 44px;
      border-radius: 14px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 0.95rem;
      cursor: pointer;
      transition: var(--transition);
      flex-shrink: 0;
    }
    .dispatch-send-icon-btn:hover {
      transform: scale(1.08) rotate(-8deg);
      box-shadow: 0 6px 20px var(--cyan-glow);
      color: #ffffff;
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
        <div class="sb-sub">Citizen Sentinel</div>
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

      <a href="track-complaint.php" class="sb-link active">
        <div class="icon-wrap"><i class="fa-solid fa-radar"></i></div>
        <span>Live Case Tracker</span>
      </a>

      <div class="sb-section-label">Communications</div>
      <a href="notifications.php" class="sb-link">
        <div class="icon-wrap"><i class="fa-solid fa-bell"></i></div>
        <span>Encrypted Alerts</span>
        <span class="sb-badge amber">2</span>
      </a>

      <div class="sb-section-label">Account & Legal</div>
      <a href="profile.php" class="sb-link">
        <div class="icon-wrap"><i class="fa-solid fa-user-shield"></i></div>
        <span>Security & Keys</span>
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
          <div class="user-role">Verified Whistleblower</div>
        </div>
      </div>
    </div>
  </aside>

  <!-- ═══════════════════ MAIN CONTENT ═══════════════════ -->
  <div id="main-content">
    <nav class="top-navbar">
      <div class="d-flex align-items-center gap-3">
        <button class="btn-toggle" id="sidebarToggle"><i class="fa-solid fa-bars-staggered"></i></button>
        <div>
          <span class="fw-bold fs-6 d-block" style="color: var(--text-main);">Live Telemetry & Radar Console</span>
          <span class="text-muted font-monospace" style="font-size: 0.72rem;">ENCRYPTED NODE: #CENTRAL-SENTINEL-04</span>
        </div>
      </div>

      <div class="nav-actions">
        <button class="nav-icon-btn" id="themeToggle" title="Toggle Theme">
          <i class="fa-solid fa-moon" id="themeIcon"></i>
        </button>
        <button class="btn-cyber-action btn-cyber-primary" onclick="downloadFullDossier()">
          <i class="fa-solid fa-file-pdf"></i> Export Dossier
        </button>
      </div>
    </nav>

    <main class="content-body">

      <!-- ═══════════════════ TRACKER HUD HERO CARD ═══════════════════ -->
      <div class="tracker-hud-card">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-4 mb-4">
          <!-- Left: Radar Disk + Title & Details -->
          <div class="d-flex align-items-center gap-3">
            <div class="radar-scan-wrapper">
              <div class="radar-sweep-blade"></div>
              <i class="fa-solid fa-satellite-dish fs-5" style="color: var(--cyan); position: relative; z-index: 2;"></i>
            </div>
            <div>
              <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                <h1 class="h4 mb-0 fw-bold font-monospace" style="color: var(--primary);" id="docketId">CCMS-2026-9082</h1>
                <span class="badge-status status-investigation" id="docketStatus">Forensic Examination</span>
                <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace px-2 py-1" style="font-size: 0.72rem;">
                  <i class="fa-solid fa-shield-halved me-1"></i>256-BIT SEALED
                </span>
              </div>
              <p class="text-muted mb-0" style="font-size: 0.88rem;">
                Target: <strong style="color: var(--text-main);" id="docketDept">Revenue &amp; Customs — Port Assessment Cell</strong> • Lodged via Anonymous Token
              </p>
            </div>
          </div>

          <!-- Right: Search Input + Presets -->
          <div class="d-flex flex-column align-items-end gap-2">
            <div class="token-search-group">
              <i class="fa-solid fa-hashtag text-muted me-2" style="font-size: 0.85rem;"></i>
              <input type="text" id="trackInput" class="token-search-input" placeholder="CCMS-2026-XXXX" value="CCMS-2026-9082">
              <button class="token-search-btn" onclick="loadCaseTelemetry()">
                <i class="fa-solid fa-magnifying-glass"></i> Track
              </button>
            </div>
            <div class="d-flex align-items-center gap-1 flex-wrap">
              <span class="text-muted" style="font-size: 0.72rem; font-weight: 600;">Presets:</span>
              <span class="token-preset-chip active" onclick="selectPresetToken(this, 'CCMS-2026-9082')">#9082 (Active)</span>
              <span class="token-preset-chip" onclick="selectPresetToken(this, 'CCMS-2026-4412')">#4412 (Triage)</span>
              <span class="token-preset-chip" onclick="selectPresetToken(this, 'CCMS-2026-7890')">#7890 (Resolved)</span>
            </div>
          </div>
        </div>

        <!-- Telemetry 4 KPI Grid Strip -->
        <div class="row g-3 pt-3" style="border-top: 1px solid var(--border);">
          <div class="col-6 col-md-3">
            <div class="tele-kpi-card">
              <div class="tele-kpi-title">Investigation Progress</div>
              <div class="d-flex align-items-center gap-2">
                <div class="progress flex-grow-1" style="height: 8px; background: var(--bg-surface); border-radius: 9999px;">
                  <div class="progress-bar progress-bar-striped progress-bar-animated" id="kpiProgressBar" style="width: 60%; background: linear-gradient(90deg, var(--cyan), var(--primary)); border-radius: 9999px;"></div>
                </div>
                <span class="font-monospace fw-bold" style="color: var(--cyan); font-size: 0.88rem;" id="kpiProgressText">60%</span>
              </div>
            </div>
          </div>

          <div class="col-6 col-md-3">
            <div class="tele-kpi-card">
              <div class="tele-kpi-title">Statutory SLA Clock</div>
              <div class="tele-kpi-val" style="color: var(--amber);">
                <i class="fa-solid fa-hourglass-half"></i>
                <span id="kpiSlaText">35h 42m Remaining</span>
              </div>
            </div>
          </div>

          <div class="col-6 col-md-3">
            <div class="tele-kpi-card">
              <div class="tele-kpi-title">Assigned Lead Investigator</div>
              <div class="tele-kpi-val">
                <i class="fa-solid fa-user-shield text-primary"></i>
                <span id="kpiOfficerText">Insp. K. Vance (#AC-819)</span>
              </div>
            </div>
          </div>

          <div class="col-6 col-md-3">
            <div class="tele-kpi-card">
              <div class="tele-kpi-title">Cryptographic SHA-256 Seal</div>
              <div class="d-flex align-items-center">
                <span class="hash-copy-chip" onclick="copyLedgerHash()">
                  <i class="fa-solid fa-fingerprint"></i>
                  <span>0x9a8f...312e</span>
                  <i class="fa-solid fa-copy ms-1" style="font-size: 0.72rem; opacity: 0.7;"></i>
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ═══════════════════ 5-STEP INVESTIGATION PIPELINE ═══════════════════ -->
      <div class="pipeline-container">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <div class="fw-bold d-flex align-items-center gap-2" style="font-size: 0.95rem; color: var(--text-main);">
            <i class="fa-solid fa-diagram-project text-cyan"></i> Statutory Investigation Pipeline
          </div>
          <span class="badge bg-warning-subtle text-warning font-monospace" id="pipelinePhaseBadge">
            <i class="fa-solid fa-spinner fa-spin me-1"></i> PHASE 3 ACTIVE
          </span>
        </div>

        <div class="pipeline-steps-track" id="pipelineTrack">
          <!-- Step 1: Done -->
          <div class="pipeline-step step-done">
            <div class="step-indicator"><i class="fa-solid fa-check"></i></div>
            <div class="step-title-text">Complaint Lodged</div>
          </div>

          <!-- Step 2: Done -->
          <div class="pipeline-step step-done">
            <div class="step-indicator"><i class="fa-solid fa-check"></i></div>
            <div class="step-title-text">Jurisdiction Triage</div>
          </div>

          <!-- Step 3: Active -->
          <div class="pipeline-step step-active">
            <div class="step-indicator"><i class="fa-solid fa-magnifying-glass"></i></div>
            <div class="step-title-text">Forensic Exam</div>
          </div>

          <!-- Step 4: Upcoming -->
          <div class="pipeline-step">
            <div class="step-indicator"><i class="fa-solid fa-gavel"></i></div>
            <div class="step-title-text">Tribunal Summons</div>
          </div>

          <!-- Step 5: Final -->
          <div class="pipeline-step">
            <div class="step-indicator"><i class="fa-solid fa-scale-balanced"></i></div>
            <div class="step-title-text">Final Sanctions</div>
          </div>
        </div>
      </div>

      <!-- ═══════════════════ 2-COLUMN RADAR & DISPATCH ═══════════════════ -->
      <div class="row g-4">

        <!-- Left: Interactive Timeline -->
        <div class="col-lg-7">
          <div class="card-box h-100">
            <div class="card-header-flex mb-3">
              <div>
                <div class="card-title">
                  <i class="fa-solid fa-route text-cyan"></i> Procedural Telemetry Timeline
                </div>
                <p class="text-muted mb-0" style="font-size: 0.82rem;">
                  Statutory milestones recorded in compliance with Vigilance Directives 2026.
                </p>
              </div>
              <button class="btn-cyber-action btn-cyber-secondary btn-sm" onclick="openAddendumModal()">
                <i class="fa-solid fa-paperclip"></i> Submit Addendum
              </button>
            </div>

            <div class="timeline-stream-box" id="timelineStreamContainer">

              <!-- Node 1: Completed -->
              <div class="timeline-card-node">
                <div class="timeline-node-dot done">
                  <i class="fa-solid fa-check"></i>
                </div>
                <div class="timeline-inner-card">
                  <div class="d-flex justify-content-between align-items-start mb-1 flex-wrap gap-2">
                    <strong style="font-size: 0.95rem; color: var(--text-main);">1. Complaint Registered & Cryptographically Sealed</strong>
                    <span class="badge bg-success-subtle text-success font-monospace" style="font-size: 0.72rem;">COMPLETED</span>
                  </div>
                  <p class="text-muted mb-2" style="font-size: 0.84rem; line-height: 1.6;">
                    Whistleblower report secured with zero-knowledge scrub. Raw evidence files ingested into cold-storage vault.
                  </p>
                  <div class="d-flex align-items-center gap-2 flex-wrap" style="font-size: 0.75rem;">
                    <span class="badge bg-secondary-subtle text-muted font-monospace"><i class="fa-solid fa-clock me-1"></i>Aug 14, 2026 • 10:23 AM</span>
                    <span class="badge bg-primary-subtle text-primary font-monospace"><i class="fa-solid fa-file-audio me-1"></i>1 Audio (.WAV)</span>
                    <span class="badge bg-primary-subtle text-primary font-monospace"><i class="fa-solid fa-file-pdf me-1"></i>2 Invoices (.PDF)</span>
                  </div>
                </div>
              </div>

              <!-- Node 2: Completed -->
              <div class="timeline-card-node">
                <div class="timeline-node-dot done">
                  <i class="fa-solid fa-check"></i>
                </div>
                <div class="timeline-inner-card">
                  <div class="d-flex justify-content-between align-items-start mb-1 flex-wrap gap-2">
                    <strong style="font-size: 0.95rem; color: var(--text-main);">2. Independent Jurisdictional Triage</strong>
                    <span class="badge bg-success-subtle text-success font-monospace" style="font-size: 0.72rem;">COMPLETED</span>
                  </div>
                  <p class="text-muted mb-2" style="font-size: 0.84rem; line-height: 1.6;">
                    Senior Vigilance Commissioner validated anti-corruption statutes (Sec 7/13 PC Act) and allocated docket to Bureau Cell #04.
                  </p>
                  <div class="d-flex align-items-center gap-2 flex-wrap" style="font-size: 0.75rem;">
                    <span class="badge bg-secondary-subtle text-muted font-monospace"><i class="fa-solid fa-clock me-1"></i>Aug 14, 2026 • 02:40 PM</span>
                    <span class="badge bg-info-subtle text-info"><i class="fa-solid fa-user-check me-1"></i>Lead Insp. K. Vance Assigned</span>
                  </div>
                </div>
              </div>

              <!-- Node 3: Active -->
              <div class="timeline-card-node">
                <div class="timeline-node-dot active">
                  <i class="fa-solid fa-magnifying-glass"></i>
                </div>
                <div class="timeline-inner-card active-card">
                  <div class="d-flex justify-content-between align-items-start mb-1 flex-wrap gap-2">
                    <strong class="text-cyan fw-bold" style="font-size: 0.96rem;">3. Forensic & Evidence Examination (Active Phase)</strong>
                    <span class="badge bg-warning text-dark font-monospace fw-bold" style="font-size: 0.72rem;">IN PROGRESS</span>
                  </div>
                  <p class="text-muted mb-2" style="font-size: 0.85rem; line-height: 1.6;">
                    Audio recordings and electronic gate pass registers from Port Terminal #02 are being corroborated against bank demand timestamps.
                  </p>
                  <div class="p-2.5 rounded-3 mb-2" style="background: var(--bg-surface); border: 1px solid var(--border); font-size: 0.8rem;">
                    <i class="fa-solid fa-circle-info text-cyan me-1"></i>
                    <strong>Investigator Note:</strong> Falcon Shipping logistics records requested via statutory subpoena.
                  </div>
                  <div class="d-flex align-items-center gap-2 flex-wrap" style="font-size: 0.75rem;">
                    <span class="badge bg-cyan-subtle text-cyan font-monospace"><i class="fa-solid fa-wave-square me-1"></i>Voiceprint: 94.8% Match</span>
                  </div>
                </div>
              </div>

              <!-- Node 4: Scheduled -->
              <div class="timeline-card-node opacity-75">
                <div class="timeline-node-dot">
                  <i class="fa-solid fa-gavel"></i>
                </div>
                <div class="timeline-inner-card">
                  <div class="d-flex justify-content-between align-items-start mb-1 flex-wrap gap-2">
                    <strong style="font-size: 0.95rem; color: var(--text-muted);">4. Tribunal Summons & Accused Deposition</strong>
                    <span class="badge bg-secondary text-light font-monospace" style="font-size: 0.72rem;">SCHEDULED</span>
                  </div>
                  <p class="text-muted mb-0" style="font-size: 0.84rem; line-height: 1.6;">
                    Formal deposition notice issued to accused Assistant Examiner to present official clearance logs before the vigilance tribunal on Aug 18, 2026.
                  </p>
                </div>
              </div>

              <!-- Node 5: Pending -->
              <div class="timeline-card-node opacity-50 mb-0">
                <div class="timeline-node-dot">
                  <i class="fa-solid fa-scale-balanced"></i>
                </div>
                <div class="timeline-inner-card">
                  <div class="d-flex justify-content-between align-items-start mb-1 flex-wrap gap-2">
                    <strong style="font-size: 0.95rem; color: var(--text-muted);">5. Final Prosecution Sanction & Asset Restitution</strong>
                    <span class="badge bg-secondary text-light font-monospace" style="font-size: 0.72rem;">PENDING</span>
                  </div>
                  <p class="text-muted mb-0" style="font-size: 0.84rem; line-height: 1.6;">
                    Filing of statutory chargesheet with Special Anti-Corruption Court and disciplinary service action recommendation.
                  </p>
                </div>
              </div>

            </div>
          </div>
        </div>

        <!-- Right: Encrypted Dispatch Console -->
        <div class="col-lg-5">
          <div class="dispatch-box-wrapper">
            <!-- Header -->
            <div class="dispatch-box-header">
              <div>
                <div class="fw-bold d-flex align-items-center gap-2" style="font-size: 0.92rem; color: var(--text-main);">
                  <i class="fa-solid fa-lock text-cyan"></i>
                  <span>Encrypted Officer Channel</span>
                </div>
                <div class="text-muted" style="font-size: 0.76rem;">Direct connection to Insp. K. Vance</div>
              </div>
              <span class="badge bg-success-subtle text-success font-monospace" style="font-size: 0.72rem; border: 1px solid rgba(16,185,129,0.3); padding: 0.35rem 0.65rem;">
                <i class="fa-solid fa-circle me-1" style="font-size: 0.5rem;"></i> LIVE SYNC
              </span>
            </div>

            <!-- Messages Stream -->
            <div class="dispatch-chat-viewport" id="chatMessageViewport">
              <!-- Officer Bubble -->
              <div class="chat-bubble-officer">
                <div class="d-flex justify-content-between align-items-center mb-1">
                  <div class="officer-bubble-sender">
                    <i class="fa-solid fa-user-shield"></i>
                    <span>Insp. K. Vance (Lead)</span>
                  </div>
                  <span class="text-muted font-monospace" style="font-size: 0.7rem;">Today • 09:12 AM</span>
                </div>
                <p class="mb-0" style="font-size: 0.86rem; line-height: 1.6; color: var(--text-main);">
                  Whistleblower integrity confirmed. We have retrieved the audio file and verified the port gate logs. Can you confirm if you noticed any other clearing agents present during the bribe demand?
                </p>
              </div>

              <!-- Citizen Bubble -->
              <div class="chat-bubble-citizen">
                <div class="d-flex justify-content-between align-items-center mb-1">
                  <div class="citizen-bubble-sender">
                    <i class="fa-solid fa-user-ninja"></i>
                    <span>Whistleblower (#9082)</span>
                  </div>
                  <span style="font-size: 0.7rem; opacity: 0.8; font-family: var(--font-mono);">Today • 10:05 AM</span>
                </div>
                <p class="mb-0" style="font-size: 0.86rem; line-height: 1.6; color: #ffffff;">
                  Yes, two representatives from Falcon Shipping Logistics were outside Room 402 and witnessed the clerk demanding the cash envelope.
                </p>
              </div>

              <!-- Officer Bubble 2 -->
              <div class="chat-bubble-officer">
                <div class="d-flex justify-content-between align-items-center mb-1">
                  <div class="officer-bubble-sender">
                    <i class="fa-solid fa-user-shield"></i>
                    <span>Insp. K. Vance</span>
                  </div>
                  <span class="text-muted font-monospace" style="font-size: 0.7rem;">Today • 10:14 AM</span>
                </div>
                <p class="mb-0" style="font-size: 0.86rem; line-height: 1.6; color: var(--text-main);">
                  Understood. Summons notices are being served to Falcon Logistics management today. Your identity remains 100% sealed under the Whistleblower Act.
                </p>
              </div>
            </div>

            <!-- Quick canned replies -->
            <div class="d-flex gap-1.5 flex-wrap px-3 py-2" style="background: var(--bg-surface); border-top: 1px solid var(--border);">
              <button type="button" class="quick-pill-btn" onclick="applyQuickMessage('I have uploaded an additional invoice receipt.')">
                <i class="fa-solid fa-plus-circle"></i> Uploaded Invoice
              </button>
              <button type="button" class="quick-pill-btn" onclick="applyQuickMessage('Can you provide an estimated date for tribunal hearing?')">
                <i class="fa-solid fa-calendar"></i> Hearing Date?
              </button>
              <button type="button" class="quick-pill-btn" onclick="applyQuickMessage('Confirming the location was Room 402, Custom House.')">
                <i class="fa-solid fa-location-dot"></i> Room 402
              </button>
            </div>

            <!-- Dispatch Input Form -->
            <div class="dispatch-input-area">
              <form onsubmit="handleSendCitizenMessage(event)">
                <div class="d-flex gap-2 align-items-center">
                  <input type="text" id="citizenChatInput" class="dispatch-text-input" placeholder="Type confidential message to investigator..." autocomplete="off">
                  <button type="submit" class="dispatch-send-icon-btn" title="Dispatch Message">
                    <i class="fa-solid fa-paper-plane"></i>
                  </button>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-2" style="font-size: 0.72rem; color: var(--text-muted);">
                  <span><i class="fa-solid fa-shield-halved text-emerald me-1"></i>End-to-End Cryptographically Isolated</span>
                  <span>Press Enter ↵ to send</span>
                </div>
              </form>
            </div>
          </div>
        </div>

      </div>

    </main>
  </div>
</div>

<!-- ═══════════════════ MODALS ═══════════════════ -->

<!-- Addendum Modal -->
<div class="modal fade" id="addendumModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0" style="background: var(--bg-card); border: 1px solid var(--border) !important; border-radius: 20px; box-shadow: var(--card-shadow);">
      <div class="modal-header" style="border-color: var(--border);">
        <h5 class="modal-title fs-6 d-flex align-items-center gap-2" style="color: var(--text-main);">
          <i class="fa-solid fa-paperclip text-cyan"></i> Submit Supplementary Evidence
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p class="text-muted" style="font-size: 0.84rem;">
          Attach additional documents, audio recordings, or transaction slips to docket <strong class="text-primary font-monospace">CCMS-2026-9082</strong>.
        </p>
        <div class="mb-3">
          <label class="form-label text-muted" style="font-size: 0.8rem; font-weight: 600;">Evidence Type</label>
          <select class="form-select" style="background: var(--bg-surface-2); color: var(--text-main); border-color: var(--border);">
            <option>Bank Transaction / UPI Slip</option>
            <option>Audio / Video Recording</option>
            <option>Official Email / WhatsApp Export</option>
            <option>Written Witness Statement</option>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label text-muted" style="font-size: 0.8rem; font-weight: 600;">Attach File (Sanitized automatically)</label>
          <input type="file" class="form-control" style="background: var(--bg-surface-2); color: var(--text-main); border-color: var(--border);">
        </div>
        <div class="mb-3">
          <label class="form-label text-muted" style="font-size: 0.8rem; font-weight: 600;">Forensic Note</label>
          <textarea class="form-control" rows="3" placeholder="Briefly explain what this evidence proves..." style="background: var(--bg-surface-2); color: var(--text-main); border-color: var(--border);"></textarea>
        </div>
      </div>
      <div class="modal-footer" style="border-color: var(--border);">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn-cyber-action btn-cyber-primary" onclick="submitAddendumForm()">
          <i class="fa-solid fa-upload me-1"></i> Upload &amp; Hash
        </button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="assets/js/citizen.js"></script>
<script>
  /* ── Presets & Telemetry Data ── */
  const telemetryData = {
    'CCMS-2026-9082': {
      token: 'CCMS-2026-9082',
      dept: 'Revenue & Customs — Port Assessment Cell',
      status: 'Forensic Examination',
      statusClass: 'status-investigation',
      phaseBadge: '<i class="fa-solid fa-spinner fa-spin me-1"></i> PHASE 3 ACTIVE',
      progress: 60,
      sla: '35h 42m Remaining',
      officer: 'Insp. K. Vance (#AC-819)',
      stepActive: 3
    },
    'CCMS-2026-4412': {
      token: 'CCMS-2026-4412',
      dept: 'Municipal Corporation — Urban Planning Dept',
      status: 'Triage & Jurisdiction',
      statusClass: 'status-triage',
      phaseBadge: '<i class="fa-solid fa-spinner fa-spin me-1"></i> PHASE 2 ACTIVE',
      progress: 25,
      sla: '72h 00m Remaining',
      officer: 'Insp. M. Sharma (#AC-205)',
      stepActive: 2
    },
    'CCMS-2026-7890': {
      token: 'CCMS-2026-7890',
      dept: 'Public Works Dept — Highway Division',
      status: 'Charges Filed & Sanctioned',
      statusClass: 'status-resolved',
      phaseBadge: '<i class="fa-solid fa-check-double me-1"></i> COMPLETED',
      progress: 100,
      sla: 'Concluded & Transmitted',
      officer: 'Insp. P. Kumar (#AC-341)',
      stepActive: 5
    }
  };

  function selectPresetToken(chipEl, token) {
    document.querySelectorAll('.token-preset-chip').forEach(c => c.classList.remove('active'));
    chipEl.classList.add('active');
    document.getElementById('trackInput').value = token;
    loadCaseTelemetry();
  }

  function loadCaseTelemetry() {
    const token = document.getElementById('trackInput').value.trim().toUpperCase();
    if (!token) return;

    let matched = telemetryData[token] || telemetryData['CCMS-2026-9082'];
    if (!telemetryData[token]) {
      if (token.includes('4412')) matched = telemetryData['CCMS-2026-4412'];
      else if (token.includes('7890')) matched = telemetryData['CCMS-2026-7890'];
    }

    document.getElementById('docketId').innerText = token;
    document.getElementById('docketDept').innerText = matched.dept;

    const sEl = document.getElementById('docketStatus');
    sEl.className = 'badge-status ' + matched.statusClass;
    sEl.innerText = matched.status;

    document.getElementById('kpiProgressBar').style.width = matched.progress + '%';
    document.getElementById('kpiProgressText').innerText = matched.progress + '%';
    document.getElementById('kpiSlaText').innerText = matched.sla;
    document.getElementById('kpiOfficerText').innerText = matched.officer;
    document.getElementById('pipelinePhaseBadge').innerHTML = matched.phaseBadge;

    // Update pipeline indicators
    const steps = document.querySelectorAll('#pipelineTrack .pipeline-step');
    steps.forEach((step, idx) => {
      const stepNum = idx + 1;
      step.classList.remove('step-done', 'step-active');
      if (stepNum < matched.stepActive) {
        step.classList.add('step-done');
        step.querySelector('.step-indicator').innerHTML = '<i class="fa-solid fa-check"></i>';
      } else if (stepNum === matched.stepActive) {
        step.classList.add('step-active');
      }
    });

    showCitizenToast(`Telemetry updated for ${token}`, 'info');
  }

  /* ── Interactive Encrypted Chat ── */
  function handleSendCitizenMessage(e) {
    e.preventDefault();
    const input = document.getElementById('citizenChatInput');
    const msg = input.value.trim();
    if (!msg) return;

    const container = document.getElementById('chatMessageViewport');
    const now = new Date();
    const timeStr = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

    const citizenBubble = document.createElement('div');
    citizenBubble.className = 'chat-bubble-citizen';
    citizenBubble.innerHTML = `
      <div class="d-flex justify-content-between align-items-center mb-1">
        <div class="citizen-bubble-sender">
          <i class="fa-solid fa-user-ninja"></i>
          <span>Whistleblower (#9082)</span>
        </div>
        <span style="font-size:0.7rem;opacity:0.8;font-family:var(--font-mono);">${timeStr}</span>
      </div>
      <p class="mb-0" style="font-size:0.86rem;line-height:1.6;color:#ffffff;">${msg}</p>
    `;
    container.appendChild(citizenBubble);
    container.scrollTop = container.scrollHeight;
    input.value = '';
    showCitizenToast('Encrypted dispatch sent to Investigator Vance!', 'success');

    // Simulated Officer Reply
    setTimeout(() => {
      const officerBubble = document.createElement('div');
      officerBubble.className = 'chat-bubble-officer';
      officerBubble.innerHTML = `
        <div class="d-flex justify-content-between align-items-center mb-1">
          <div class="officer-bubble-sender">
            <i class="fa-solid fa-user-shield"></i>
            <span>Insp. K. Vance</span>
          </div>
          <span class="text-muted font-monospace" style="font-size:0.7rem;">Just now</span>
        </div>
        <p class="mb-0" style="font-size:0.86rem;line-height:1.6;color:var(--text-main);">
          Received and appended to official case docket. Corroboration is proceeding under priority protocol.
        </p>
      `;
      container.appendChild(officerBubble);
      container.scrollTop = container.scrollHeight;
    }, 1800);
  }

  function applyQuickMessage(text) {
    const input = document.getElementById('citizenChatInput');
    input.value = text;
    input.focus();
  }

  /* ── Copy Hash ── */
  function copyLedgerHash() {
    navigator.clipboard.writeText('0x9a8fe10b48c90382d56ef19a823b12e');
    showCitizenToast('SHA-256 Checksum copied to clipboard!', 'info');
  }

  /* ── Addendum Modal ── */
  function openAddendumModal() {
    new bootstrap.Modal(document.getElementById('addendumModal')).show();
  }
  function submitAddendumForm() {
    const modalEl = document.getElementById('addendumModal');
    const modal = bootstrap.Modal.getInstance(modalEl);
    if (modal) modal.hide();
    Swal.fire({
      icon: 'success',
      title: 'Supplementary Evidence Ingested',
      html: `
        <p style="font-size:0.88rem;color:var(--text-muted);">EXIF metadata scrubbed. File assigned SHA-256 seal <code>0x77c2...f81a</code> and linked to docket <strong>CCMS-2026-9082</strong>.</p>
      `,
      confirmButtonColor: '#6366f1'
    });
  }

  /* ── Download Dossier ── */
  function downloadFullDossier() {
    Swal.fire({
      icon: 'success',
      title: 'Certified Judicial Dossier Compiled',
      html: `
        <p style="font-size:0.88rem;color:var(--text-muted);">The official encrypted investigation docket for <strong>CCMS-2026-9082</strong> has been generated with cryptographic integrity stamps.</p>
        <div class="font-monospace text-info p-2 rounded mt-2 text-start" style="font-size:0.75rem; background: var(--bg-surface-2); border: 1px solid var(--border);">
          <div>• DOCKET: CCMS-2026-9082</div>
          <div>• LEDGER HASH: 0x9a8fe10b48c90382d56ef19a823b12e</div>
          <div>• CERTIFYING AUTHORITY: CCMS Sentinel Investigation Bureau</div>
        </div>
      `,
      confirmButtonText: '<i class="fa-solid fa-file-pdf me-1"></i> Download PDF Dossier',
      confirmButtonColor: '#6366f1'
    });
  }
</script>
</body>
</html>
