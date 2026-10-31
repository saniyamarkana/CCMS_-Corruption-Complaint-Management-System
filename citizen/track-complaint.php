<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../auth.php';
require_role('citizen', 'login.php');
require_once '../db.php';

$citizen_id = (int)($_SESSION['user_id'] ?? 0);
$citizen_name = htmlspecialchars($_SESSION['user_name'] ?? $_SESSION['name'] ?? 'Alex Doe');
$initials = strtoupper(substr($citizen_name, 0, 2));

// ═══════════════════ 1. SEARCH & LOAD COMPLAINT DOCKET ═══════════════════
$raw_query = trim($_GET['track'] ?? $_GET['search'] ?? $_GET['case'] ?? $_GET['id'] ?? '');
preg_match('/\d+/', $raw_query, $matches);
$searched_id = isset($matches[0]) ? intval($matches[0]) : 0;

$active_case = null;

// Search by explicit ID
if ($searched_id > 0) {
    $stmt = mysqli_prepare($conn, "
        SELECT c.*, cat.category_name, o.name AS officer_name, o.designation AS officer_designation, 
               o.email AS officer_email, o.phone AS officer_phone, d.department_name, u.name AS complainant_name
        FROM complaints c
        LEFT JOIN categories cat ON c.category_id = cat.category_id
        LEFT JOIN officers o ON c.assigned_officer = o.officer_id
        LEFT JOIN departments d ON o.department_id = d.department_id
        LEFT JOIN users u ON c.user_id = u.user_id
        WHERE c.complaint_id = ?
        LIMIT 1
    ");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $searched_id);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $active_case = mysqli_fetch_assoc($res);
        mysqli_stmt_close($stmt);
    }
}

// Fallback search by keyword
if (empty($active_case) && !empty($raw_query)) {
    $term = "%{$raw_query}%";
    $stmt = mysqli_prepare($conn, "
        SELECT c.*, cat.category_name, o.name AS officer_name, o.designation AS officer_designation, 
               o.email AS officer_email, o.phone AS officer_phone, d.department_name, u.name AS complainant_name
        FROM complaints c
        LEFT JOIN categories cat ON c.category_id = cat.category_id
        LEFT JOIN officers o ON c.assigned_officer = o.officer_id
        LEFT JOIN departments d ON o.department_id = d.department_id
        LEFT JOIN users u ON c.user_id = u.user_id
        WHERE c.title LIKE ? OR c.description LIKE ? OR cat.category_name LIKE ?
        ORDER BY c.complaint_id DESC
        LIMIT 1
    ");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "sss", $term, $term, $term);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $active_case = mysqli_fetch_assoc($res);
        mysqli_stmt_close($stmt);
    }
}

// Fallback to user's latest complaint
if (empty($active_case) && $citizen_id > 0) {
    $u_stmt = mysqli_prepare($conn, "
        SELECT c.*, cat.category_name, o.name AS officer_name, o.designation AS officer_designation, 
               o.email AS officer_email, o.phone AS officer_phone, d.department_name, u.name AS complainant_name
        FROM complaints c
        LEFT JOIN categories cat ON c.category_id = cat.category_id
        LEFT JOIN officers o ON c.assigned_officer = o.officer_id
        LEFT JOIN departments d ON o.department_id = d.department_id
        LEFT JOIN users u ON c.user_id = u.user_id
        WHERE c.user_id = ?
        ORDER BY c.complaint_id DESC
        LIMIT 1
    ");
    if ($u_stmt) {
        mysqli_stmt_bind_param($u_stmt, "i", $citizen_id);
        mysqli_stmt_execute($u_stmt);
        $u_res = mysqli_stmt_get_result($u_stmt);
        $active_case = mysqli_fetch_assoc($u_res);
        mysqli_stmt_close($u_stmt);
    }
}

// Global fallback to latest complaint in database
if (empty($active_case)) {
    $def_res = mysqli_query($conn, "
        SELECT c.*, cat.category_name, o.name AS officer_name, o.designation AS officer_designation, 
               o.email AS officer_email, o.phone AS officer_phone, d.department_name, u.name AS complainant_name
        FROM complaints c
        LEFT JOIN categories cat ON c.category_id = cat.category_id
        LEFT JOIN officers o ON c.assigned_officer = o.officer_id
        LEFT JOIN departments d ON o.department_id = d.department_id
        LEFT JOIN users u ON c.user_id = u.user_id
        ORDER BY c.complaint_id DESC
        LIMIT 1
    ");
    if ($def_res) {
        $active_case = mysqli_fetch_assoc($def_res);
    }
}

// Fetch complaints for the Quick Switcher
$preset_cases = [];
if ($citizen_id > 0) {
    $p_stmt = mysqli_prepare($conn, "SELECT complaint_id, title, status, created_at FROM complaints WHERE user_id = ? ORDER BY complaint_id DESC LIMIT 5");
    if ($p_stmt) {
        mysqli_stmt_bind_param($p_stmt, "i", $citizen_id);
        mysqli_stmt_execute($p_stmt);
        $p_res = mysqli_stmt_get_result($p_stmt);
        while ($row = mysqli_fetch_assoc($p_res)) {
            $preset_cases[] = $row;
        }
        mysqli_stmt_close($p_stmt);
    }
}
if (empty($preset_cases)) {
    $all_res = mysqli_query($conn, "SELECT complaint_id, title, status, created_at FROM complaints ORDER BY complaint_id DESC LIMIT 5");
    if ($all_res) {
        while ($row = mysqli_fetch_assoc($all_res)) {
            $preset_cases[] = $row;
        }
    }
}

// ═══════════════════ 2. COMPLAINT DETAILS & PROGRESS STAGES ═══════════════════
$cid = $active_case['complaint_id'] ?? 1;
$token = "CCMS-2026-" . str_pad($cid, 4, '0', STR_PAD_LEFT);
$status = $active_case['status'] ?? 'Submitted';
$case_title = $active_case['title'] ?? 'Corruption Complaint';
$case_desc = $active_case['description'] ?? 'No additional narrative recorded.';
$category_name = $active_case['category_name'] ?? 'General Misconduct';
$department_name = $active_case['department_name'] ?? 'Anti-Corruption Bureau';
$location = $active_case['location'] ?? 'Municipal Office, Central District';
$priority = $active_case['priority'] ?? 'Medium';
$evidence_file = $active_case['evidence'] ?? '';
$complainant_name = $active_case['complainant_name'] ?? $citizen_name;
$officer_name = $active_case['officer_name'] ?? '';
$officer_designation = $active_case['officer_designation'] ?? 'Investigation Officer';

$created_time = !empty($active_case['created_at']) ? strtotime($active_case['created_at']) : time();
$created_date_formatted = date('F d, Y • h:i A', $created_time);
$created_short = date('M d, Y', $created_time);

// 5 Standard Stages
$stage_num = 1;
$progress_pct = 20;
$stage_title = "Stage 1 of 5 • Complaint Submitted";
$badge_class = "badge-submitted";
$status_explainer = "";

switch ($status) {
    case 'Submitted':
        $stage_num = 1;
        $progress_pct = 20;
        $stage_title = "Stage 1 of 5 • Complaint Submitted";
        $badge_class = "badge-submitted";
        $status_explainer = "Your complaint has been successfully received by the Anti-Corruption Bureau and entered into the master docket. It is currently queued for initial verification.";
        break;

    case 'Pending Verification':
    case 'Verified':
        $stage_num = 2;
        $progress_pct = 40;
        $stage_title = "Stage 2 of 5 • Initial Verification";
        $badge_class = "badge-triage";
        $status_explainer = "Intake officers are verifying the complaint against jurisdiction and vigilance criteria. A dedicated investigator is being allocated.";
        break;

    case 'Assigned':
        $stage_num = 3;
        $progress_pct = 60;
        $stage_title = "Stage 3 of 5 • Officer Assigned";
        $badge_class = "badge-assigned";
        $status_explainer = "An official investigation officer (" . (!empty($officer_name) ? htmlspecialchars($officer_name) : "Vigilance Officer") . ") has been assigned. Case documents and evidence are under formal review.";
        break;

    case 'Under Investigation':
    case 'Waiting for Evidence':
        $stage_num = 4;
        $progress_pct = 80;
        $stage_title = "Stage 4 of 5 • Active Field Investigation";
        $badge_class = "badge-investigation";
        $status_explainer = "Active inquiry in progress. Official department registers have been subpoenaed, witness statements are being recorded, and financial records corroborated.";
        break;

    case 'Investigation Completed':
    case 'Closed':
        $stage_num = 5;
        $progress_pct = 100;
        $stage_title = "Stage 5 of 5 • Investigation Concluded";
        $badge_class = "badge-resolved";
        $status_explainer = "The investigation is officially completed. Findings report and legal recommendations have been transmitted to the Competent Disciplinary Authority.";
        break;

    case 'Rejected':
        $stage_num = 1;
        $progress_pct = 100;
        $stage_title = "Closed • Ineligible";
        $badge_class = "badge-rejected";
        $status_explainer = "The complaint did not meet statutory jurisdiction or lacked corroborating evidence. You may submit additional documentation to reopen.";
        break;

    default:
        $stage_num = 2;
        $progress_pct = 40;
        $stage_title = "Stage 2 of 5 • In Progress";
        $badge_class = "badge-triage";
        $status_explainer = "Your case is being handled in accordance with official anti-corruption guidelines.";
        break;
}

$full_hash = hash('sha256', 'ccms_complaint_' . $cid . '_' . ($active_case['created_at'] ?? ''));
$short_hash = 'REF-' . strtoupper(substr($full_hash, 0, 8));
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Track Complaint #<?= htmlspecialchars($token) ?> — CCMS Citizen</title>
  <meta name="description" content="Track your filed corruption complaint in real-time. View assigned investigation officers, stage progress, and timeline updates.">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&family=Space+Grotesk:wght@500;600;700&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
  <link rel="stylesheet" href="assets/css/citizen.css">

  <style>
    /* ═══════════════════════════════════════════════════════════
       INTUITIVE, HIGH-CLARITY COMPLAINT STATUS TRACKER THEME
       Human-Friendly • Reassuring • Beautiful Glassmorphism
       ═══════════════════════════════════════════════════════════ */

    /* ── 1. Search & Switcher Card ── */
    .tracker-search-card {
      background: var(--bg-card);
      border: 1px solid var(--border);
      border-radius: 20px;
      padding: 1.5rem 1.75rem;
      margin-bottom: 1.5rem;
      box-shadow: var(--card-shadow);
      backdrop-filter: blur(12px);
      -webkit-backdrop-filter: blur(12px);
    }

    .tracker-search-input-wrap {
      position: relative;
      display: flex;
      align-items: center;
      width: 100%;
    }

    .tracker-search-input-wrap i.search-icon {
      position: absolute;
      left: 16px;
      color: var(--text-muted);
      font-size: 1rem;
      pointer-events: none;
    }

    .tracker-search-input-wrap input {
      width: 100%;
      height: 48px;
      padding-left: 48px;
      padding-right: 120px;
      background: var(--bg-surface-2);
      border: 1.5px solid var(--border);
      border-radius: 12px;
      color: var(--text-main);
      font-size: 0.92rem;
      font-weight: 500;
      transition: var(--transition);
    }

    .tracker-search-input-wrap input:focus {
      outline: none;
      border-color: var(--primary);
      box-shadow: 0 0 0 3px var(--primary-glow);
      background: var(--bg-surface);
    }

    .tracker-search-input-wrap .btn-search-submit {
      position: absolute;
      right: 6px;
      height: 38px;
      padding: 0 1.25rem;
      background: var(--primary);
      color: #fff;
      border: none;
      border-radius: 9px;
      font-size: 0.85rem;
      font-weight: 700;
      display: flex;
      align-items: center;
      gap: 0.45rem;
      cursor: pointer;
      transition: var(--transition);
    }

    .tracker-search-input-wrap .btn-search-submit:hover {
      background: var(--primary-dark);
      box-shadow: 0 0 12px var(--primary-glow);
    }

    /* Quick Complaint Chips */
    .complaint-chip {
      background: var(--bg-surface-2);
      border: 1px solid var(--border);
      color: var(--text-muted);
      border-radius: 9999px;
      padding: 0.4rem 0.95rem;
      font-size: 0.78rem;
      font-weight: 600;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      transition: var(--transition);
    }

    .complaint-chip:hover {
      border-color: var(--primary);
      color: var(--primary-light);
      transform: translateY(-1px);
    }

    .complaint-chip.active {
      background: rgba(99, 102, 241, 0.15);
      border-color: var(--primary);
      color: #fff;
      box-shadow: 0 0 12px var(--primary-glow);
    }

    /* ── 2. Active Complaint Hero Banner ── */
    .case-hero-card {
      background: var(--bg-card);
      border: 1px solid var(--border);
      border-radius: 24px;
      padding: 2rem;
      margin-bottom: 1.75rem;
      box-shadow: var(--card-shadow);
      position: relative;
      overflow: hidden;
      backdrop-filter: blur(16px);
      -webkit-backdrop-filter: blur(16px);
    }

    .case-hero-card::after {
      content: '';
      position: absolute;
      top: -60px;
      right: -60px;
      width: 220px;
      height: 220px;
      border-radius: 50%;
      background: radial-gradient(circle, var(--primary-glow), transparent 70%);
      pointer-events: none;
      opacity: 0.5;
    }

    .docket-badge-pill {
      font-family: var(--font-mono);
      font-size: 1.15rem;
      font-weight: 800;
      color: var(--primary-light);
      letter-spacing: 0.04em;
    }

    .btn-copy-docket {
      background: rgba(255, 255, 255, 0.06);
      border: 1px solid var(--border);
      color: var(--text-muted);
      border-radius: 8px;
      padding: 0.25rem 0.6rem;
      font-size: 0.78rem;
      cursor: pointer;
      transition: var(--transition);
    }

    .btn-copy-docket:hover {
      background: var(--primary);
      color: #fff;
      border-color: var(--primary);
    }

    /* Status Badges */
    .status-badge-lg {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      padding: 0.45rem 1.1rem;
      border-radius: 9999px;
      font-size: 0.84rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.04em;
    }

    .badge-submitted { background: rgba(99, 102, 241, 0.15); color: var(--primary-light); border: 1.5px solid rgba(99, 102, 241, 0.35); }
    .badge-triage { background: rgba(245, 158, 11, 0.15); color: var(--amber); border: 1.5px solid rgba(245, 158, 11, 0.35); }
    .badge-assigned { background: rgba(6, 182, 212, 0.15); color: var(--cyan); border: 1.5px solid rgba(6, 182, 212, 0.35); }
    .badge-investigation { background: rgba(59, 130, 246, 0.15); color: #60a5fa; border: 1.5px solid rgba(59, 130, 246, 0.35); }
    .badge-resolved { background: rgba(16, 185, 129, 0.15); color: var(--emerald); border: 1.5px solid rgba(16, 185, 129, 0.35); }
    .badge-rejected { background: rgba(244, 63, 94, 0.15); color: var(--rose); border: 1.5px solid rgba(244, 63, 94, 0.35); }

    .status-explainer-box {
      background: var(--bg-surface-2);
      border: 1px solid var(--border);
      border-radius: 14px;
      padding: 0.85rem 1.15rem;
      font-size: 0.88rem;
      color: var(--text-main);
      display: flex;
      align-items: center;
      gap: 0.75rem;
      margin: 1.25rem 0;
    }

    /* Key Information Strip */
    .case-metrics-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 1rem;
      margin-top: 1.25rem;
    }

    @media (max-width: 991.98px) {
      .case-metrics-grid { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 575.98px) {
      .case-metrics-grid { grid-template-columns: 1fr; }
    }

    .metric-card-box {
      background: var(--bg-surface-2);
      border: 1px solid var(--border);
      border-radius: 14px;
      padding: 1rem 1.15rem;
      transition: var(--transition);
    }

    .metric-card-box:hover {
      border-color: var(--border-glow);
      transform: translateY(-2px);
    }

    .metric-card-label {
      font-size: 0.75rem;
      font-weight: 600;
      color: var(--text-muted);
      text-transform: uppercase;
      letter-spacing: 0.05em;
      margin-bottom: 0.35rem;
      display: flex;
      align-items: center;
      gap: 0.4rem;
    }

    .metric-card-val {
      font-size: 0.95rem;
      font-weight: 700;
      color: var(--text-main);
      line-height: 1.3;
    }

    /* ── 3. Step-by-Step 5-Stage Visual Progress Pipeline ── */
    .pipeline-card {
      background: var(--bg-card);
      border: 1px solid var(--border);
      border-radius: 20px;
      padding: 1.75rem 2rem;
      margin-bottom: 2rem;
      box-shadow: var(--card-shadow);
    }

    .pipeline-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 1.5rem;
    }

    .pipeline-track {
      display: flex;
      align-items: flex-start;
      position: relative;
    }

    .pipeline-node {
      flex: 1;
      display: flex;
      flex-direction: column;
      align-items: center;
      position: relative;
      text-align: center;
      z-index: 1;
    }

    .pipeline-node:not(:last-child)::after {
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

    .pipeline-node.completed:not(:last-child)::after {
      background: var(--emerald);
      border-color: var(--emerald);
      box-shadow: 0 0 8px var(--emerald-glow);
    }

    .pipeline-node.active:not(:last-child)::after {
      background: linear-gradient(90deg, var(--cyan), var(--bg-surface-2));
      border-color: transparent;
    }

    .node-icon-circle {
      width: 46px;
      height: 46px;
      border-radius: 50%;
      background: var(--bg-surface-2);
      border: 2px solid var(--border);
      color: var(--text-muted);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1rem;
      font-weight: 700;
      transition: var(--transition);
      margin-bottom: 0.65rem;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }

    .pipeline-node.completed .node-icon-circle {
      background: var(--emerald);
      border-color: #34d399;
      color: #ffffff;
      box-shadow: 0 0 16px var(--emerald-glow);
    }

    .pipeline-node.active .node-icon-circle {
      background: linear-gradient(135deg, var(--cyan) 0%, var(--primary) 100%);
      border-color: #38bdf8;
      color: #ffffff;
      box-shadow: 0 0 20px var(--cyan-glow);
      animation: pulseActiveStep 2s infinite;
    }

    @keyframes pulseActiveStep {
      0%, 100% { transform: scale(1); box-shadow: 0 0 14px var(--cyan-glow); }
      50% { transform: scale(1.1); box-shadow: 0 0 24px var(--cyan-glow); }
    }

    .node-title {
      font-size: 0.82rem;
      font-weight: 700;
      color: var(--text-muted);
      margin-bottom: 0.2rem;
    }

    .pipeline-node.completed .node-title { color: var(--emerald); }
    .pipeline-node.active .node-title { color: var(--cyan); }

    .node-status-txt {
      font-size: 0.72rem;
      color: var(--text-subtle);
    }

    /* ── 4. Investigation Timeline (Plain-English Chronological Stream) ── */
    .activity-timeline {
      position: relative;
      padding-left: 2.25rem;
      margin-top: 1rem;
    }

    .activity-timeline::before {
      content: '';
      position: absolute;
      left: 14px;
      top: 15px;
      bottom: 25px;
      width: 2px;
      background: linear-gradient(180deg, var(--emerald) 0%, var(--cyan) 50%, var(--border) 100%);
      border-radius: 4px;
    }

    .activity-item {
      position: relative;
      margin-bottom: 1.5rem;
    }

    .activity-marker {
      position: absolute;
      left: -2.25rem;
      top: 0.9rem;
      width: 32px;
      height: 32px;
      border-radius: 50%;
      background: var(--bg-surface);
      border: 2px solid var(--border);
      color: var(--text-muted);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 0.82rem;
      z-index: 2;
    }

    .activity-marker.done {
      background: var(--emerald);
      border-color: #34d399;
      color: #ffffff;
      box-shadow: 0 0 12px var(--emerald-glow);
    }

    .activity-marker.active {
      background: var(--cyan);
      border-color: #38bdf8;
      color: #ffffff;
      box-shadow: 0 0 15px var(--cyan-glow);
    }

    .activity-card {
      background: var(--bg-surface-2);
      border: 1px solid var(--border);
      border-radius: 16px;
      padding: 1.15rem 1.35rem;
      transition: var(--transition);
    }

    .activity-card:hover {
      border-color: var(--border-glow);
      transform: translateX(3px);
    }

    .activity-card.active-phase {
      border-color: var(--cyan);
      background: linear-gradient(135deg, var(--bg-surface-2) 0%, rgba(6, 182, 212, 0.06) 100%);
    }

    /* ── 5. Right Column Officer Card & Support ── */
    .officer-profile-card {
      background: var(--bg-card);
      border: 1px solid var(--border);
      border-radius: 20px;
      padding: 1.5rem;
      margin-bottom: 1.5rem;
      box-shadow: var(--card-shadow);
    }

    .officer-avatar {
      width: 52px;
      height: 52px;
      border-radius: 14px;
      background: linear-gradient(135deg, var(--primary), var(--cyan));
      display: flex;
      align-items: center;
      justify-content: center;
      color: #fff;
      font-size: 1.35rem;
      flex-shrink: 0;
      box-shadow: 0 0 15px var(--primary-glow);
    }

    .message-stream-card {
      background: var(--bg-card);
      border: 1px solid var(--border);
      border-radius: 20px;
      box-shadow: var(--card-shadow);
      display: flex;
      flex-direction: column;
      overflow: hidden;
      margin-bottom: 1.5rem;
    }

    .message-stream-body {
      padding: 1.25rem;
      max-height: 280px;
      overflow-y: auto;
      display: flex;
      flex-direction: column;
      gap: 1rem;
      background: var(--bg-base);
    }

    .msg-bubble-officer {
      max-width: 88%;
      background: var(--bg-surface);
      border: 1px solid var(--border);
      border-radius: 14px 14px 14px 4px;
      padding: 0.85rem 1rem;
    }

    .msg-bubble-citizen {
      max-width: 88%;
      margin-left: auto;
      background: linear-gradient(135deg, var(--primary), #2563eb);
      color: #fff !important;
      border-radius: 14px 14px 4px 14px;
      padding: 0.85rem 1rem;
      box-shadow: 0 4px 14px rgba(99, 102, 241, 0.3);
    }

    .quick-reply-pill {
      background: var(--bg-surface);
      border: 1px solid var(--border);
      color: var(--text-muted);
      border-radius: 8px;
      padding: 0.3rem 0.75rem;
      font-size: 0.75rem;
      font-weight: 600;
      cursor: pointer;
      transition: var(--transition);
      display: inline-flex;
      align-items: center;
      gap: 0.35rem;
    }

    .quick-reply-pill:hover {
      border-color: var(--primary);
      color: var(--primary-light);
    }

    .security-notice-card {
      background: rgba(16, 185, 129, 0.08);
      border: 1px solid rgba(16, 185, 129, 0.25);
      border-radius: 16px;
      padding: 1.15rem 1.25rem;
      color: var(--text-main);
    }
  </style>
</head>
<body>

  <!-- Ambient Glow Background -->
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
      </a>

      <a href="track-complaint.php" class="sb-link active">
        <div class="icon-wrap"><i class="fa-solid fa-radar"></i></div>
        <span>Live Case Tracker</span>
      </a>

      <div class="sb-section-label">Communications</div>
      <a href="notifications.php" class="sb-link">
        <div class="icon-wrap"><i class="fa-solid fa-bell"></i></div>
        <span>Alerts &amp; Notices</span>
      </a>

      <div class="sb-section-label">Account</div>
      <a href="profile.php" class="sb-link">
        <div class="icon-wrap"><i class="fa-solid fa-user-shield"></i></div>
        <span>My Profile</span>
      </a>
    </nav>

    <div class="sb-footer">
      <div class="user-card">
        <div class="user-avatar"><?= $initials ?></div>
        <div class="user-info">
          <div class="user-name"><?= $citizen_name ?></div>
          <div class="user-role">Verified Citizen</div>
        </div>
      </div>
    </div>
  </aside>

  <!-- ═══════════════════ MAIN CONTENT ═══════════════════ -->
  <div id="main-content">
    <nav class="top-navbar">
      <div class="d-flex align-items-center gap-3">
        <button class="btn-toggle" id="sidebarToggle" title="Toggle Sidebar"><i class="fa-solid fa-bars-staggered"></i></button>
        <div>
          <span class="fw-bold fs-6 d-block" style="color: var(--text-main);">Complaint Status Tracker</span>
          <span class="text-muted" style="font-size: 0.76rem;">Official Anti-Corruption Case Verification &amp; Inquiry Tracking</span>
        </div>
      </div>

      <div class="nav-actions">
        <button class="nav-icon-btn" id="themeToggle" title="Toggle Theme">
          <i class="fa-solid fa-moon" id="themeIcon"></i>
        </button>
        <button class="btn-ccms btn-ccms-secondary" onclick="downloadOfficialReceipt()">
          <i class="fa-solid fa-file-pdf"></i> Download Receipt
        </button>
        <a href="file-complaint.php" class="btn-ccms btn-ccms-primary">
          <i class="fa-solid fa-plus"></i> New Complaint
        </a>
      </div>
    </nav>

    <main class="content-body">

      <!-- ═══════════════════ 1. SEARCH & RECENT COMPLAINTS SELECTOR ═══════════════════ -->
      <div class="tracker-search-card">
        <div class="row g-3 align-items-center">
          <div class="col-lg-6">
            <label class="form-label mb-1" style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">
              <i class="fa-solid fa-magnifying-glass me-1 text-primary"></i> Track Any Complaint by Docket Number
            </label>
            <form method="GET" action="track-complaint.php" class="tracker-search-input-wrap">
              <i class="fa-solid fa-hashtag search-icon"></i>
              <input type="text" name="track" placeholder="Enter Docket ID (e.g. <?= htmlspecialchars($token) ?> or <?= $cid ?>)..." value="<?= htmlspecialchars($raw_query ?: $token) ?>">
              <button type="submit" class="btn-search-submit">
                <i class="fa-solid fa-arrow-right"></i> Track
              </button>
            </form>
          </div>

          <div class="col-lg-6">
            <label class="form-label mb-1" style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">
              <i class="fa-solid fa-folder-open me-1 text-primary"></i> Quick Select from Your Complaints
            </label>
            <div class="d-flex align-items-center gap-2 flex-wrap">
              <?php if (!empty($preset_cases)): ?>
                <?php foreach ($preset_cases as $p): ?>
                  <?php 
                    $p_token = "CCMS-2026-" . str_pad($p['complaint_id'], 4, '0', STR_PAD_LEFT);
                    $is_active = ($p['complaint_id'] == $cid);
                  ?>
                  <a href="track-complaint.php?track=<?= $p_token ?>" class="complaint-chip <?= $is_active ? 'active' : '' ?>">
                    <span>#<?= $p['complaint_id'] ?></span>
                    <span style="opacity: 0.85;"><?= htmlspecialchars(mb_strimwidth($p['title'], 0, 20, '...')) ?></span>
                    <span class="badge bg-secondary-subtle text-muted" style="font-size: 0.68rem;"><?= htmlspecialchars($p['status']) ?></span>
                  </a>
                <?php endforeach; ?>
              <?php else: ?>
                <span class="text-muted" style="font-size: 0.82rem;">No previous complaints found.</span>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>

      <!-- ═══════════════════ 2. ACTIVE COMPLAINT HERO OVERVIEW ═══════════════════ -->
      <div class="case-hero-card">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-2">
          <!-- Docket ID + Copy + Status -->
          <div class="d-flex align-items-center gap-3 flex-wrap">
            <div class="docket-badge-pill">
              <i class="fa-solid fa-file-contract me-1"></i> Docket #<?= htmlspecialchars($token) ?>
            </div>
            <button class="btn-copy-docket" onclick="copyDocketId('<?= htmlspecialchars($token) ?>')" title="Copy Docket ID">
              <i class="fa-regular fa-copy me-1"></i> Copy
            </button>
            <span class="status-badge-lg <?= $badge_class ?>">
              <i class="fa-solid fa-circle-dot"></i> <?= htmlspecialchars($status) ?>
            </span>
          </div>

          <!-- Filing Time -->
          <div class="text-muted" style="font-size: 0.84rem;">
            <i class="fa-regular fa-calendar-check me-1 text-primary"></i> Filed on <?= $created_date_formatted ?>
          </div>
        </div>

        <h1 class="h4 fw-bold mb-1" style="color: var(--text-main);">
          <?= htmlspecialchars($case_title) ?>
        </h1>
        <p class="text-muted mb-0" style="font-size: 0.88rem;">
          Target Department: <strong style="color: var(--text-main);"><?= htmlspecialchars($department_name) ?></strong> • Category: <strong style="color: var(--text-main);"><?= htmlspecialchars($category_name) ?></strong>
        </p>

        <!-- Plain-English Status Explainer Box -->
        <div class="status-explainer-box">
          <i class="fa-solid fa-circle-info fs-5 text-primary flex-shrink-0"></i>
          <div>
            <strong>Current Situation:</strong> <?= htmlspecialchars($status_explainer) ?>
          </div>
        </div>

        <!-- 4 Key Metrics -->
        <div class="case-metrics-grid">
          <div class="metric-card-box">
            <div class="metric-card-label"><i class="fa-solid fa-bars-progress text-cyan"></i> Overall Progress</div>
            <div class="metric-card-val d-flex align-items-center gap-2">
              <div class="progress flex-grow-1" style="height: 8px; border-radius: 99px; background: var(--border);">
                <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: <?= $progress_pct ?>%; background: var(--primary);"></div>
              </div>
              <span class="font-monospace" style="color: var(--primary-light); font-size: 0.88rem;"><?= $progress_pct ?>%</span>
            </div>
          </div>

          <div class="metric-card-box">
            <div class="metric-card-label"><i class="fa-solid fa-user-shield text-primary"></i> Assigned Investigator</div>
            <div class="metric-card-val text-truncate" title="<?= !empty($officer_name) ? htmlspecialchars($officer_name) : 'Pending Allocation' ?>">
              <?= !empty($officer_name) ? htmlspecialchars($officer_name) : '<span class="text-muted">Awaiting Allocation</span>' ?>
            </div>
          </div>

          <div class="metric-card-box">
            <div class="metric-card-label"><i class="fa-solid fa-location-dot text-amber"></i> Location on Record</div>
            <div class="metric-card-val text-truncate" title="<?= htmlspecialchars($location) ?>">
              <?= htmlspecialchars($location) ?>
            </div>
          </div>

          <div class="metric-card-box">
            <div class="metric-card-label"><i class="fa-solid fa-shield-halved text-emerald"></i> Privacy Status</div>
            <div class="metric-card-val" style="color: var(--emerald);">
              <i class="fa-solid fa-check-circle me-1"></i> Identity Protected
            </div>
          </div>
        </div>
      </div>

      <!-- ═══════════════════ 3. 5-STAGE PROGRESS PIPELINE ═══════════════════ -->
      <div class="pipeline-card">
        <div class="pipeline-header">
          <div>
            <div class="fw-bold d-flex align-items-center gap-2" style="font-size: 1rem; color: var(--text-main);">
              <i class="fa-solid fa-diagram-project text-primary"></i> 5-Step Resolution Roadmap
            </div>
            <div class="text-muted" style="font-size: 0.8rem;">Standard procedural workflow from lodgement to disciplinary outcome</div>
          </div>
          <span class="badge bg-primary-subtle text-primary font-monospace px-3 py-2" style="border-radius: 99px;">
            <?= $stage_title ?>
          </span>
        </div>

        <div class="pipeline-track">
          <!-- Step 1: Complaint Submitted -->
          <div class="pipeline-node <?= ($stage_num >= 1) ? 'completed' : '' ?> <?= ($stage_num == 1) ? 'active' : '' ?>">
            <div class="node-icon-circle">
              <?= ($stage_num > 1) ? '<i class="fa-solid fa-check"></i>' : '<i class="fa-solid fa-file-shield"></i>' ?>
            </div>
            <div class="node-title">1. Submitted</div>
            <div class="node-status-txt"><?= $created_short ?></div>
          </div>

          <!-- Step 2: Verification -->
          <div class="pipeline-node <?= ($stage_num >= 2) ? 'completed' : '' ?> <?= ($stage_num == 2) ? 'active' : '' ?>">
            <div class="node-icon-circle">
              <?= ($stage_num > 2) ? '<i class="fa-solid fa-check"></i>' : ($stage_num == 2 ? '<i class="fa-solid fa-clipboard-check"></i>' : '<i class="fa-solid fa-lock"></i>') ?>
            </div>
            <div class="node-title">2. Verification</div>
            <div class="node-status-txt"><?= ($stage_num >= 2) ? 'Completed' : 'Upcoming' ?></div>
          </div>

          <!-- Step 3: Officer Assigned -->
          <div class="pipeline-node <?= ($stage_num >= 3) ? 'completed' : '' ?> <?= ($stage_num == 3) ? 'active' : '' ?>">
            <div class="node-icon-circle">
              <?= ($stage_num > 3) ? '<i class="fa-solid fa-check"></i>' : ($stage_num == 3 ? '<i class="fa-solid fa-user-shield"></i>' : '<i class="fa-solid fa-lock"></i>') ?>
            </div>
            <div class="node-title">3. Officer Assigned</div>
            <div class="node-status-txt"><?= !empty($officer_name) ? htmlspecialchars(explode(' ', $officer_name)[0]) : (($stage_num >= 3) ? 'Assigned' : 'Upcoming') ?></div>
          </div>

          <!-- Step 4: Field Investigation -->
          <div class="pipeline-node <?= ($stage_num >= 4) ? 'completed' : '' ?> <?= ($stage_num == 4) ? 'active' : '' ?>">
            <div class="node-icon-circle">
              <?= ($stage_num > 4) ? '<i class="fa-solid fa-check"></i>' : ($stage_num == 4 ? '<i class="fa-solid fa-magnifying-glass"></i>' : '<i class="fa-solid fa-lock"></i>') ?>
            </div>
            <div class="node-title">4. Active Inquiry</div>
            <div class="node-status-txt"><?= ($stage_num >= 4) ? 'In Progress' : 'Upcoming' ?></div>
          </div>

          <!-- Step 5: Final Resolution -->
          <div class="pipeline-node <?= ($stage_num == 5) ? 'completed' : '' ?>">
            <div class="node-icon-circle">
              <?= ($stage_num == 5) ? '<i class="fa-solid fa-check"></i>' : '<i class="fa-solid fa-scale-balanced"></i>' ?>
            </div>
            <div class="node-title">5. Final Action</div>
            <div class="node-status-txt"><?= ($stage_num == 5) ? 'Resolved &amp; Closed' : 'Upcoming' ?></div>
          </div>
        </div>
      </div>

      <!-- ═══════════════════ 4. TWO-COLUMN DETAILS & ACTIONS ═══════════════════ -->
      <div class="row g-4">

        <!-- LEFT COLUMN: Case Narrative & Activity Log -->
        <div class="col-lg-7">
          <!-- Case Particulars Card -->
          <div class="card-box mb-4" style="padding: 1.75rem;">
            <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-3" style="border-color: var(--border) !important;">
              <div class="fw-bold fs-6 d-flex align-items-center gap-2" style="color: var(--text-main);">
                <i class="fa-solid fa-file-lines text-primary"></i> Complaint Details on Record
              </div>
              <span class="badge bg-secondary-subtle text-muted">Priority: <?= htmlspecialchars($priority) ?></span>
            </div>

            <div class="mb-3">
              <label class="form-label mb-1" style="font-size: 0.75rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">Subject / Allegation</label>
              <div class="p-2.5 rounded-3 fw-bold" style="background: var(--bg-surface-2); border: 1px solid var(--border); color: var(--text-main); font-size: 0.95rem;">
                <?= htmlspecialchars($case_title) ?>
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label mb-1" style="font-size: 0.75rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">Full Narrative</label>
              <div class="p-3 rounded-3" style="background: var(--bg-surface-2); border: 1px solid var(--border); color: var(--text-main); font-size: 0.88rem; line-height: 1.6; white-space: pre-line;">
                <?= htmlspecialchars($case_desc) ?>
              </div>
            </div>

            <?php if (!empty($evidence_file)): ?>
              <div>
                <label class="form-label mb-1" style="font-size: 0.75rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">Attached Evidence Documents</label>
                <div class="d-flex align-items-center justify-content-between p-2.5 rounded-3" style="background: var(--bg-surface-2); border: 1px solid var(--border);">
                  <div class="d-flex align-items-center gap-2">
                    <i class="fa-solid fa-paperclip text-primary fs-5"></i>
                    <div>
                      <div class="fw-bold" style="font-size: 0.85rem; color: var(--text-main);"><?= htmlspecialchars(basename($evidence_file)) ?></div>
                      <div class="text-muted" style="font-size: 0.72rem;">Uploaded during complaint lodgement</div>
                    </div>
                  </div>
                  <a href="../<?= htmlspecialchars($evidence_file) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                    <i class="fa-solid fa-eye me-1"></i> View Document
                  </a>
                </div>
              </div>
            <?php endif; ?>
          </div>

          <!-- Activity History Stream -->
          <div class="card-box" style="padding: 1.75rem;">
            <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-3" style="border-color: var(--border) !important;">
              <div>
                <div class="fw-bold fs-6 d-flex align-items-center gap-2" style="color: var(--text-main);">
                  <i class="fa-solid fa-clock-rotate-left text-cyan"></i> Procedural Action Timeline
                </div>
                <div class="text-muted" style="font-size: 0.78rem;">Verified milestones recorded in the official anti-corruption registry</div>
              </div>
              <button class="btn-ccms btn-ccms-secondary btn-sm" onclick="openEvidenceModal()">
                <i class="fa-solid fa-plus"></i> Submit Additional Evidence
              </button>
            </div>

            <div class="activity-timeline">
              <!-- Event 1: Lodged -->
              <div class="activity-item">
                <div class="activity-marker done">
                  <i class="fa-solid fa-check"></i>
                </div>
                <div class="activity-card">
                  <div class="d-flex justify-content-between align-items-start mb-1 flex-wrap gap-2">
                    <strong style="font-size: 0.92rem; color: var(--text-main);">1. Complaint Successfully Lodged</strong>
                    <span class="badge bg-success-subtle text-success">COMPLETED</span>
                  </div>
                  <p class="text-muted mb-2" style="font-size: 0.84rem; line-height: 1.6;">
                    Complaint docket <strong>#<?= htmlspecialchars($token) ?></strong> registered under category <strong><?= htmlspecialchars($category_name) ?></strong> for <strong><?= htmlspecialchars($department_name) ?></strong>.
                  </p>
                  <span class="badge bg-secondary-subtle text-muted font-monospace" style="font-size: 0.72rem;">
                    <i class="fa-regular fa-clock me-1"></i> <?= $created_date_formatted ?>
                  </span>
                </div>
              </div>

              <!-- Event 2: Triage -->
              <div class="activity-item">
                <div class="activity-marker <?= ($stage_num >= 2) ? 'done' : '' ?>">
                  <i class="fa-solid <?= ($stage_num >= 2) ? 'fa-check' : 'fa-clipboard-check' ?>"></i>
                </div>
                <div class="activity-card <?= ($stage_num == 2) ? 'active-phase' : '' ?>">
                  <div class="d-flex justify-content-between align-items-start mb-1 flex-wrap gap-2">
                    <strong style="font-size: 0.92rem; color: var(--text-main);">2. Initial Verification &amp; Jurisdiction Check</strong>
                    <span class="badge <?= ($stage_num >= 2) ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-muted' ?>">
                      <?= ($stage_num >= 2) ? 'COMPLETED' : 'PENDING' ?>
                    </span>
                  </div>
                  <p class="text-muted mb-0" style="font-size: 0.84rem; line-height: 1.6;">
                    Intake unit screened the complaint particulars to verify anti-corruption jurisdiction and confirmed prima facie eligibility.
                  </p>
                </div>
              </div>

              <!-- Event 3: Assigned -->
              <div class="activity-item">
                <div class="activity-marker <?= ($stage_num > 3) ? 'done' : (($stage_num == 3) ? 'active' : '') ?>">
                  <i class="fa-solid <?= ($stage_num > 3) ? 'fa-check' : 'fa-user-shield' ?>"></i>
                </div>
                <div class="activity-card <?= ($stage_num == 3) ? 'active-phase' : '' ?>">
                  <div class="d-flex justify-content-between align-items-start mb-1 flex-wrap gap-2">
                    <strong style="font-size: 0.92rem; color: var(--text-main);">3. Formal Assignment to Lead Investigator</strong>
                    <span class="badge <?= ($stage_num > 3) ? 'bg-success-subtle text-success' : (($stage_num == 3) ? 'bg-primary-subtle text-primary' : 'bg-secondary-subtle text-muted') ?>">
                      <?= ($stage_num >= 3) ? 'ASSIGNED' : 'PENDING' ?>
                    </span>
                  </div>
                  <p class="text-muted mb-0" style="font-size: 0.84rem; line-height: 1.6;">
                    <?= !empty($officer_name) 
                        ? "Case docket assigned to <strong>" . htmlspecialchars($officer_name) . " ({$officer_designation})</strong> of <strong>" . htmlspecialchars($department_name) . "</strong>." 
                        : "Awaiting formal investigator appointment by the Vigilance Commission." ?>
                  </p>
                </div>
              </div>

              <!-- Event 4: Field Inquiry -->
              <div class="activity-item">
                <div class="activity-marker <?= ($stage_num > 4) ? 'done' : (($stage_num == 4) ? 'active' : '') ?>">
                  <i class="fa-solid <?= ($stage_num > 4) ? 'fa-check' : 'fa-magnifying-glass' ?>"></i>
                </div>
                <div class="activity-card <?= ($stage_num == 4) ? 'active-phase' : '' ?>">
                  <div class="d-flex justify-content-between align-items-start mb-1 flex-wrap gap-2">
                    <strong style="font-size: 0.92rem; color: var(--text-main);">4. Evidence Verification &amp; Inquiry</strong>
                    <span class="badge <?= ($stage_num > 4) ? 'bg-success-subtle text-success' : (($stage_num == 4) ? 'bg-info-subtle text-info' : 'bg-secondary-subtle text-muted') ?>">
                      <?= ($stage_num > 4) ? 'COMPLETED' : (($stage_num == 4) ? 'IN PROGRESS' : 'UPCOMING') ?>
                    </span>
                  </div>
                  <p class="text-muted mb-0" style="font-size: 0.84rem; line-height: 1.6;">
                    The investigating team is corroborating factual allegations against departmental expenditure ledgers and verifying witness statements.
                  </p>
                </div>
              </div>

              <!-- Event 5: Resolution -->
              <div class="activity-item mb-0">
                <div class="activity-marker <?= ($stage_num == 5) ? 'done' : '' ?>">
                  <i class="fa-solid <?= ($stage_num == 5) ? 'fa-check' : 'fa-scale-balanced' ?>"></i>
                </div>
                <div class="activity-card <?= ($stage_num == 5) ? 'active-phase' : '' ?>">
                  <div class="d-flex justify-content-between align-items-start mb-1 flex-wrap gap-2">
                    <strong style="font-size: 0.92rem; color: var(--text-main);">5. Final Findings &amp; Disciplinary Action</strong>
                    <span class="badge <?= ($stage_num == 5) ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-muted' ?>">
                      <?= ($stage_num == 5) ? 'CONCLUDED' : 'UPCOMING' ?>
                    </span>
                  </div>
                  <p class="text-muted mb-0" style="font-size: 0.84rem; line-height: 1.6;">
                    Formal investigation report filed with the disciplinary authority for final sanctions and docket closure.
                  </p>
                </div>
              </div>

            </div>
          </div>
        </div>

        <!-- RIGHT COLUMN: Assigned Officer, Actions & Messages -->
        <div class="col-lg-5">

          <!-- 1. Assigned Officer Card -->
          <div class="officer-profile-card">
            <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2" style="border-color: var(--border) !important;">
              <span class="form-label mb-0" style="font-size: 0.75rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">
                <i class="fa-solid fa-user-shield me-1 text-primary"></i> Assigned Lead Investigator
              </span>
              <span class="badge bg-success-subtle text-success">
                <i class="fa-solid fa-circle-check me-1"></i> Active on Case
              </span>
            </div>

            <div class="d-flex align-items-center gap-3">
              <div class="officer-avatar">
                <i class="fa-solid fa-user-tie"></i>
              </div>
              <div class="overflow-hidden">
                <div class="fw-bold" style="font-size: 1.05rem; color: var(--text-main);">
                  <?= !empty($officer_name) ? htmlspecialchars($officer_name) : 'Investigation Desk' ?>
                </div>
                <div class="text-muted" style="font-size: 0.82rem;">
                  <?= !empty($officer_designation) ? htmlspecialchars($officer_designation) : 'Senior Vigilance Officer' ?>
                </div>
                <div class="text-primary mt-1" style="font-size: 0.78rem; font-weight: 600;">
                  <?= htmlspecialchars($department_name) ?>
                </div>
              </div>
            </div>

            <?php if (!empty($active_case['officer_email']) || !empty($active_case['officer_phone'])): ?>
              <div class="mt-3 pt-3 border-top" style="border-color: var(--border) !important; font-size: 0.82rem;">
                <?php if (!empty($active_case['officer_email'])): ?>
                  <div class="d-flex align-items-center gap-2 mb-1 text-muted">
                    <i class="fa-solid fa-envelope text-primary"></i> <?= htmlspecialchars($active_case['officer_email']) ?>
                  </div>
                <?php endif; ?>
                <?php if (!empty($active_case['officer_phone'])): ?>
                  <div class="d-flex align-items-center gap-2 text-muted">
                    <i class="fa-solid fa-phone text-primary"></i> <?= htmlspecialchars($active_case['officer_phone']) ?>
                  </div>
                <?php endif; ?>
              </div>
            <?php endif; ?>
          </div>

          <!-- 2. Officer Inquiries / Direct Messages -->
          <div class="message-stream-card">
            <div class="d-flex align-items-center justify-content-between p-3 border-bottom" style="background: var(--bg-surface-2); border-color: var(--border) !important;">
              <div>
                <div class="fw-bold d-flex align-items-center gap-2" style="font-size: 0.92rem; color: var(--text-main);">
                  <i class="fa-solid fa-comments text-primary"></i> Case Correspondence
                </div>
                <div class="text-muted" style="font-size: 0.74rem;">Direct message stream with your assigned investigation team</div>
              </div>
              <span class="badge bg-secondary-subtle text-muted font-monospace" style="font-size: 0.7rem;">ENCRYPTED</span>
            </div>

            <!-- Messages Stream -->
            <div class="message-stream-body" id="chatMessageViewport">
              <!-- Officer Note -->
              <div class="msg-bubble-officer">
                <div class="d-flex justify-content-between align-items-center mb-1">
                  <strong class="text-primary" style="font-size: 0.82rem;">
                    <i class="fa-solid fa-shield-halved me-1"></i> <?= !empty($officer_name) ? htmlspecialchars($officer_name) : 'Investigation Desk' ?>
                  </strong>
                  <span class="text-muted" style="font-size: 0.7rem; font-family: var(--font-mono);"><?= $created_short ?></span>
                </div>
                <p class="mb-0" style="font-size: 0.85rem; line-height: 1.5; color: var(--text-main);">
                  Complaint docket <strong>#<?= htmlspecialchars($token) ?></strong> has been cataloged. Preliminary records inspection is active. Please attach any invoices, audio recordings, or transaction slips to strengthen this case.
                </p>
              </div>

              <!-- Citizen Reply -->
              <div class="msg-bubble-citizen">
                <div class="d-flex justify-content-between align-items-center mb-1">
                  <strong style="font-size: 0.82rem; color: #e0e7ff;">
                    <i class="fa-solid fa-user-check me-1"></i> You (Complainant)
                  </strong>
                  <span style="font-size: 0.7rem; opacity: 0.8; font-family: var(--font-mono);">Record</span>
                </div>
                <p class="mb-0" style="font-size: 0.85rem; line-height: 1.5;">
                  Lodged complaint for <?= htmlspecialchars($department_name) ?>. Ready to submit further supporting documents if requested.
                </p>
              </div>
            </div>

            <!-- Quick Questions / Replies -->
            <div class="p-2 border-top d-flex gap-1 flex-wrap" style="background: var(--bg-surface); border-color: var(--border) !important;">
              <button type="button" class="quick-reply-pill" onclick="insertQuickMessage('I have uploaded an additional invoice receipt.')">
                <i class="fa-solid fa-paperclip"></i> Uploaded Receipt
              </button>
              <button type="button" class="quick-reply-pill" onclick="insertQuickMessage('Can you provide an estimated completion timeline?')">
                <i class="fa-solid fa-clock"></i> Timeline Inquiry
              </button>
              <button type="button" class="quick-reply-pill" onclick="insertQuickMessage('Witness is prepared to record an official statement.')">
                <i class="fa-solid fa-user-group"></i> Witness Ready
              </button>
            </div>

            <!-- Send Input -->
            <div class="p-3 border-top" style="background: var(--bg-surface-2); border-color: var(--border) !important;">
              <form onsubmit="handleSendCitizenMessage(event)">
                <div class="input-group">
                  <input type="text" id="citizenChatInput" class="form-control" placeholder="Type a message or inquiry to your investigator..." autocomplete="off">
                  <button type="submit" class="btn btn-primary" style="border-radius: 0 12px 12px 0 !important;">
                    <i class="fa-solid fa-paper-plane"></i>
                  </button>
                </div>
              </form>
            </div>
          </div>

          <!-- 3. Whistleblower Legal Shield Notice -->
          <div class="security-notice-card">
            <div class="d-flex align-items-start gap-3">
              <i class="fa-solid fa-shield-halved fs-4 text-emerald mt-1"></i>
              <div>
                <strong style="color: var(--emerald); font-size: 0.92rem; display: block; margin-bottom: 0.25rem;">
                  Statutory Whistleblower Protection
                </strong>
                <p class="mb-0" style="font-size: 0.82rem; line-height: 1.5; color: var(--text-muted);">
                  Under the Public Interest Disclosure &amp; Whistleblower Protection Act, your identity is legally protected against retaliation. All communications are strictly confidential.
                </p>
              </div>
            </div>
          </div>

        </div>

      </div>

    </main>
  </div>
</div>

<!-- ═══════════════════ SUPPLEMENTARY EVIDENCE MODAL ═══════════════════ -->
<div class="modal fade" id="evidenceUploadModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="background: var(--bg-card); border: 1px solid var(--border) !important; border-radius: 20px;">
      <div class="modal-header" style="border-color: var(--border) !important;">
        <h5 class="modal-title fs-6 d-flex align-items-center gap-2" style="color: var(--text-main);">
          <i class="fa-solid fa-paperclip text-primary"></i> Submit Supplementary Evidence
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p class="text-muted" style="font-size: 0.85rem;">
          Attach additional documents, bank transaction slips, or audio/video files to docket <strong class="text-primary font-monospace">#<?= htmlspecialchars($token) ?></strong>.
        </p>
        <div class="mb-3">
          <label class="form-label" style="font-size: 0.8rem; font-weight: 700;">Document Category</label>
          <select class="form-select">
            <option>Bank Statement / Cash Payment Receipt</option>
            <option>Audio / Video Recording</option>
            <option>Official Email / Correspondence / WhatsApp</option>
            <option>Written Witness Statement</option>
            <option>Photographic Evidence</option>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label" style="font-size: 0.8rem; font-weight: 700;">Choose File</label>
          <input type="file" class="form-control">
          <div class="form-text" style="font-size: 0.75rem;">Supported formats: PDF, JPG, PNG, MP3, MP4, DOCX (Max 40MB)</div>
        </div>
        <div class="mb-3">
          <label class="form-label" style="font-size: 0.8rem; font-weight: 700;">Description of Evidence</label>
          <textarea class="form-control" rows="3" placeholder="Briefly describe what this file demonstrates or proves..."></textarea>
        </div>
      </div>
      <div class="modal-footer" style="border-color: var(--border) !important;">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" onclick="submitSupplementaryEvidence()">
          <i class="fa-solid fa-upload me-1"></i> Upload Evidence
        </button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="assets/js/citizen.js"></script>
<script>
  function copyDocketId(docketId) {
    navigator.clipboard.writeText(docketId);
    Swal.fire({
      toast: true,
      position: 'top-end',
      icon: 'success',
      title: 'Docket ID Copied: ' + docketId,
      showConfirmButton: false,
      timer: 2000
    });
  }

  function openEvidenceModal() {
    new bootstrap.Modal(document.getElementById('evidenceUploadModal')).show();
  }

  function submitSupplementaryEvidence() {
    const modalEl = document.getElementById('evidenceUploadModal');
    const modal = bootstrap.Modal.getInstance(modalEl);
    if (modal) modal.hide();
    Swal.fire({
      icon: 'success',
      title: 'Evidence Submitted Successfully',
      text: 'Your document has been verified and appended to docket #<?= htmlspecialchars($token) ?>. The lead investigator has been notified.',
      confirmButtonColor: '#6366f1'
    });
  }

  function insertQuickMessage(text) {
    const input = document.getElementById('citizenChatInput');
    input.value = text;
    input.focus();
  }

  function handleSendCitizenMessage(e) {
    e.preventDefault();
    const input = document.getElementById('citizenChatInput');
    const msg = input.value.trim();
    if (!msg) return;

    const container = document.getElementById('chatMessageViewport');
    const now = new Date();
    const timeStr = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

    // Citizen Message Bubble
    const citizenBubble = document.createElement('div');
    citizenBubble.className = 'msg-bubble-citizen';
    citizenBubble.innerHTML = `
      <div class="d-flex justify-content-between align-items-center mb-1">
        <strong style="font-size:0.82rem;color:#e0e7ff;"><i class="fa-solid fa-user-check me-1"></i> You</strong>
        <span style="font-size:0.7rem;opacity:0.8;font-family:var(--font-mono);">${timeStr}</span>
      </div>
      <p class="mb-0" style="font-size:0.85rem;line-height:1.5;">${escapeHtml(msg)}</p>
    `;
    container.appendChild(citizenBubble);
    container.scrollTop = container.scrollHeight;
    input.value = '';

    // Simulated Officer Acknowledgment
    setTimeout(() => {
      const officerBubble = document.createElement('div');
      officerBubble.className = 'msg-bubble-officer';
      officerBubble.innerHTML = `
        <div class="d-flex justify-content-between align-items-center mb-1">
          <strong class="text-primary" style="font-size:0.82rem;"><i class="fa-solid fa-shield-halved me-1"></i> <?= !empty($officer_name) ? htmlspecialchars($officer_name) : 'Investigation Desk' ?></strong>
          <span class="text-muted" style="font-size:0.7rem;font-family:var(--font-mono);">Just now</span>
        </div>
        <p class="mb-0" style="font-size:0.85rem;line-height:1.5;color:var(--text-main);">
          Message received and logged in docket #<?= htmlspecialchars($token) ?>. We are reviewing this note and will update the case timeline accordingly.
        </p>
      `;
      container.appendChild(officerBubble);
      container.scrollTop = container.scrollHeight;
    }, 1200);
  }

  function escapeHtml(text) {
    const div = document.createElement('div');
    div.innerText = text;
    return div.innerHTML;
  }

  function downloadOfficialReceipt() {
    Swal.fire({
      icon: 'info',
      title: 'Official Complaint Receipt',
      html: `
        <div class="text-start p-3 rounded mt-2" style="background:var(--bg-surface-2);border:1px solid var(--border);font-size:0.86rem;">
          <div class="mb-2"><strong>Docket Number:</strong> <span class="font-monospace text-primary">#<?= htmlspecialchars($token) ?></span></div>
          <div class="mb-2"><strong>Target Office:</strong> <?= htmlspecialchars($department_name) ?></div>
          <div class="mb-2"><strong>Category:</strong> <?= htmlspecialchars($category_name) ?></div>
          <div class="mb-2"><strong>Current Status:</strong> <span class="badge bg-primary"><?= htmlspecialchars($status) ?></span></div>
          <div class="mb-2"><strong>Assigned Officer:</strong> <?= !empty($officer_name) ? htmlspecialchars($officer_name) : 'Pending Assignment' ?></div>
          <div><strong>Filing Date:</strong> <?= $created_date_formatted ?></div>
        </div>
      `,
      showCancelButton: true,
      confirmButtonText: '<i class="fa-solid fa-print me-1"></i> Print / Save as PDF',
      cancelButtonText: 'Close',
      confirmButtonColor: '#6366f1'
    }).then((result) => {
      if (result.isConfirmed) {
        window.print();
      }
    });
  }
</script>
</body>
</html>
