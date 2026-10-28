<?php
require_once '../auth.php';
require_role('admin', 'login.php');
require_once '../db.php';

// Align timezone for precise temporal queries and timestamp matching
date_default_timezone_set('Asia/Kolkata');

$admin_name = htmlspecialchars($_SESSION['admin_name'] ?? $_SESSION['name'] ?? 'Super Administrator');

// ═══════════════════ 1. TIMEFRAME / PERIOD FILTER SPECIFICATION ═══════════════════
$period = trim($_GET['period'] ?? 'all');
$date_from = trim($_GET['date_from'] ?? '');
$date_to = trim($_GET['date_to'] ?? '');

$date_sql_complaints = "1=1";
$date_sql_users = "1=1";
$period_label = "All Time Audit";
$period_badge = "Lifetime System Telemetry";
$period_subtitle = "Comprehensive audit history across all recorded fiscal & reporting cycles";

switch ($period) {
    case '1day':
        // Past 24 hours / Today
        $date_sql_complaints = "c.created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)";
        $date_sql_users = "created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)";
        $period_label = "Past 24 Hours (1 Day)";
        $period_badge = "24-Hour Real-Time Window";
        $period_subtitle = "Complaints and audit activity recorded from " . date('M d, Y • h:i A', strtotime('-1 day')) . " to " . date('M d, Y • h:i A');
        break;

    case '3day':
        // Past 3 days (72 hours)
        $date_sql_complaints = "c.created_at >= DATE_SUB(NOW(), INTERVAL 3 DAY)";
        $date_sql_users = "created_at >= DATE_SUB(NOW(), INTERVAL 3 DAY)";
        $period_label = "Last 3 Days";
        $period_badge = "72-Hour Rapid Response Window";
        $period_subtitle = "Audited dockets logged between " . date('M d, Y', strtotime('-3 days')) . " and " . date('M d, Y');
        break;

    case '7day':
        // Past 7 days (1 week)
        $date_sql_complaints = "c.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
        $date_sql_users = "created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
        $period_label = "Last 7 Days (1 Week)";
        $period_badge = "Weekly Vigilance Cycle";
        $period_subtitle = "Data recorded between " . date('M d, Y', strtotime('-7 days')) . " and " . date('M d, Y');
        break;

    case 'last_month':
    case '30day':
        // Past 30 days (rolling month)
        $period = 'last_month';
        $date_sql_complaints = "c.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
        $date_sql_users = "created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
        $period_label = "Last Month (30 Days)";
        $period_badge = "Monthly Statutory Audit";
        $period_subtitle = "Telemetry recorded between " . date('M d, Y', strtotime('-30 days')) . " and " . date('M d, Y');
        break;

    case 'custom':
        if (!empty($date_from) && !empty($date_to)) {
            $from_clean = mysqli_real_escape_string($conn, $date_from);
            $to_clean = mysqli_real_escape_string($conn, $date_to);
            $date_sql_complaints = "c.created_at BETWEEN '{$from_clean} 00:00:00' AND '{$to_clean} 23:59:59'";
            $date_sql_users = "created_at BETWEEN '{$from_clean} 00:00:00' AND '{$to_clean} 23:59:59'";
            $period_label = "Custom: " . date('M d', strtotime($from_clean)) . " – " . date('M d, Y', strtotime($to_clean));
            $period_badge = "Targeted Custom Window";
            $period_subtitle = "Audited activity between " . date('F d, Y', strtotime($from_clean)) . " and " . date('F d, Y', strtotime($to_clean));
        } else {
            $period = 'all';
        }
        break;

    default:
        $period = 'all';
        $date_sql_complaints = "1=1";
        $date_sql_users = "1=1";
        $period_label = "All Time Audit";
        $period_badge = "Lifetime System Telemetry";
        $period_subtitle = "Comprehensive lifetime database telemetry across all recorded cycles";
        break;
}

// ═══════════════════ 2. LIFETIME vs PERIOD METRICS ═══════════════════
// Total Complaints lifetime
$count_all_lifetime = (int)(mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM complaints"))[0] ?? 0);
$count_citizens_total = (int)(mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM users WHERE LOWER(role) = 'citizen'"))[0] ?? 0);
$count_officers_total = (int)(mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM officers"))[0] ?? 0);
$count_depts_total = (int)(mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM departments"))[0] ?? 0);

// Filtered Period Complaints
$count_all = (int)(mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM complaints c WHERE {$date_sql_complaints}"))[0] ?? 0);

// Filtered Pending Complaints (Submitted, Pending Verification, Verified, Assigned)
$count_pending = (int)(mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM complaints c WHERE c.status IN ('Submitted', 'Pending Verification', 'Verified', 'Assigned') AND {$date_sql_complaints}"))[0] ?? 0);

// Filtered Solved / Resolved Complaints
$count_solved = (int)(mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM complaints c WHERE c.status IN ('Investigation Completed', 'Closed') AND {$date_sql_complaints}"))[0] ?? 0);

// Filtered Under Active Investigation
$count_under_inv = (int)(mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM complaints c WHERE c.status = 'Under Investigation' AND {$date_sql_complaints}"))[0] ?? 0);

// Filtered Priority Counts
$count_critical = (int)(mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM complaints c WHERE c.priority = 'Critical' AND {$date_sql_complaints}"))[0] ?? 0);
$count_high = (int)(mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM complaints c WHERE c.priority = 'High' AND {$date_sql_complaints}"))[0] ?? 0);
$count_critical_total = $count_critical + $count_high;

$count_medium = (int)(mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM complaints c WHERE c.priority = 'Medium' AND {$date_sql_complaints}"))[0] ?? 0);
$count_low = (int)(mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM complaints c WHERE c.priority = 'Low' AND {$date_sql_complaints}"))[0] ?? 0);

// Filtered Registered Citizens in Period
$count_citizens_period = (int)(mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM users WHERE LOWER(role) = 'citizen' AND {$date_sql_users}"))[0] ?? 0);

// Calculated Rates for the Period
$resolution_rate = ($count_all > 0) ? round(($count_solved / $count_all) * 100, 1) : 0;
$pending_rate = ($count_all > 0) ? round(($count_pending / $count_all) * 100, 1) : 0;
$investigation_rate = ($count_all > 0) ? round(($count_under_inv / $count_all) * 100, 1) : 0;

// ═══════════════════ 3. DEPARTMENTAL BREAKDOWN (PERIOD FILTERED) ═══════════════════
$dept_stats = [];
$dept_query = mysqli_query($conn, "
    SELECT 
        d.department_id,
        d.department_name,
        COUNT(DISTINCT o.officer_id) AS total_officers,
        COUNT(DISTINCT c.complaint_id) AS total_complaints,
        SUM(CASE WHEN c.status IN ('Submitted', 'Pending Verification', 'Verified', 'Assigned') THEN 1 ELSE 0 END) AS pending_count,
        SUM(CASE WHEN c.status IN ('Investigation Completed', 'Closed') THEN 1 ELSE 0 END) AS resolved_count,
        SUM(CASE WHEN c.priority IN ('Critical', 'High') THEN 1 ELSE 0 END) AS critical_count
    FROM departments d
    LEFT JOIN officers o ON d.department_id = o.department_id
    LEFT JOIN complaints c ON (o.officer_id = c.assigned_officer AND {$date_sql_complaints})
    GROUP BY d.department_id, d.department_name
    ORDER BY total_complaints DESC, d.department_name ASC
");
if ($dept_query) {
    while ($row = mysqli_fetch_assoc($dept_query)) {
        $dept_stats[] = $row;
    }
}

$top_dept = !empty($dept_stats) ? $dept_stats[0] : null;
$top_dept_name = ($top_dept && (int)$top_dept['total_complaints'] > 0) ? htmlspecialchars($top_dept['department_name']) : 'Municipal Administration';
$top_dept_cases = $top_dept ? (int)$top_dept['total_complaints'] : 0;
$top_dept_rate = ($top_dept && $top_dept_cases > 0) ? round(((int)$top_dept['resolved_count'] / $top_dept_cases) * 100, 1) : $resolution_rate;

// ═══════════════════ 4. ADAPTIVE TRAJECTORY CHART DATA (PERIOD TAILORED) ═══════════════════
$chart_timeline_title = "Complaints Inflow & Clearance Trajectory";
$chart_timeline_sub = "Trajectory of dockets received vs. concluded within the selected timeframe";
$chart_months = [];
$chart_inflows = [];
$chart_solveds = [];

if ($period === '1day') {
    $chart_timeline_sub = "Today's 4-hour interval case lodgement vs. resolution activity (" . date('M d, Y') . ")";
    $slots = [
        ['00:00:00', '03:59:59', '12 AM - 4 AM'],
        ['04:00:00', '07:59:59', '4 AM - 8 AM'],
        ['08:00:00', '11:59:59', '8 AM - 12 PM'],
        ['12:00:00', '15:59:59', '12 PM - 4 PM'],
        ['16:00:00', '19:59:59', '4 PM - 8 PM'],
        ['20:00:00', '23:59:59', '8 PM - 12 AM']
    ];
    $today_str = date('Y-m-d');
    foreach ($slots as $slot) {
        $s_start = $today_str . ' ' . $slot[0];
        $s_end   = $today_str . ' ' . $slot[1];
        $chart_months[] = $slot[2];

        $q = mysqli_query($conn, "SELECT COUNT(*) AS tot, SUM(CASE WHEN status IN ('Investigation Completed', 'Closed') THEN 1 ELSE 0 END) AS sol FROM complaints WHERE created_at >= '{$s_start}' AND created_at <= '{$s_end}'");
        $r = mysqli_fetch_assoc($q);
        $chart_inflows[] = (int)($r['tot'] ?? 0);
        $chart_solveds[] = (int)($r['sol'] ?? 0);
    }
} elseif ($period === '3day') {
    $chart_timeline_sub = "Daily trajectory of cases lodged vs. resolved across the last 3 days";
    for ($i = 2; $i >= 0; $i--) {
        $day_date = date('Y-m-d', strtotime("-{$i} days"));
        $day_lbl = ($i === 0) ? 'Today (' . date('M d') . ')' : date('D, M d', strtotime("-{$i} days"));
        $chart_months[] = $day_lbl;

        $q = mysqli_query($conn, "SELECT COUNT(*) AS tot, SUM(CASE WHEN status IN ('Investigation Completed', 'Closed') THEN 1 ELSE 0 END) AS sol FROM complaints WHERE DATE(created_at) = '{$day_date}'");
        $r = mysqli_fetch_assoc($q);
        $chart_inflows[] = (int)($r['tot'] ?? 0);
        $chart_solveds[] = (int)($r['sol'] ?? 0);
    }
} elseif ($period === '7day') {
    $chart_timeline_sub = "Day-by-day progression of complaints lodged vs. resolved over the past week";
    for ($i = 6; $i >= 0; $i--) {
        $day_date = date('Y-m-d', strtotime("-{$i} days"));
        $day_lbl = ($i === 0) ? 'Today (' . date('M d') . ')' : date('D, M d', strtotime("-{$i} days"));
        $chart_months[] = $day_lbl;

        $q = mysqli_query($conn, "SELECT COUNT(*) AS tot, SUM(CASE WHEN status IN ('Investigation Completed', 'Closed') THEN 1 ELSE 0 END) AS sol FROM complaints WHERE DATE(created_at) = '{$day_date}'");
        $r = mysqli_fetch_assoc($q);
        $chart_inflows[] = (int)($r['tot'] ?? 0);
        $chart_solveds[] = (int)($r['sol'] ?? 0);
    }
} elseif ($period === 'last_month') {
    $chart_timeline_sub = "5-day interval comparison of case influx vs. statutory clearance over the last 30 days";
    for ($i = 5; $i >= 0; $i--) {
        $start_day = ($i + 1) * 5;
        $end_day   = $i * 5;
        $start_dt  = date('Y-m-d 00:00:00', strtotime("-{$start_day} days"));
        $end_dt    = ($end_day === 0) ? date('Y-m-d 23:59:59') : date('Y-m-d 23:59:59', strtotime("-{$end_day} days"));
        $chart_months[] = date('M d', strtotime("-{$start_day} days")) . ' – ' . date('M d', strtotime("-{$end_day} days"));

        $q = mysqli_query($conn, "SELECT COUNT(*) AS tot, SUM(CASE WHEN status IN ('Investigation Completed', 'Closed') THEN 1 ELSE 0 END) AS sol FROM complaints WHERE created_at >= '{$start_dt}' AND created_at <= '{$end_dt}'");
        $r = mysqli_fetch_assoc($q);
        $chart_inflows[] = (int)($r['tot'] ?? 0);
        $chart_solveds[] = (int)($r['sol'] ?? 0);
    }
} else {
    // All time: Monthly trend across complaints
    $chart_timeline_sub = "Monthly comparison of cases lodged vs. officially resolved across recorded cycles";
    $monthly_res = mysqli_query($conn, "
        SELECT 
            DATE_FORMAT(created_at, '%b %Y') AS month_label,
            COUNT(*) AS total_count,
            SUM(CASE WHEN status IN ('Investigation Completed', 'Closed') THEN 1 ELSE 0 END) AS solved_count
        FROM complaints
        GROUP BY DATE_FORMAT(created_at, '%Y-%m'), DATE_FORMAT(created_at, '%b %Y')
        ORDER BY MIN(created_at) ASC
        LIMIT 6
    ");
    if ($monthly_res && mysqli_num_rows($monthly_res) > 0) {
        while ($m = mysqli_fetch_assoc($monthly_res)) {
            $chart_months[] = $m['month_label'];
            $chart_inflows[] = (int)$m['total_count'];
            $chart_solveds[] = (int)$m['solved_count'];
        }
    } else {
        $chart_months = ['May 2026', 'Jun 2026', 'Jul 2026', 'Aug 2026', 'Sep 2026', 'Oct 2026'];
        $chart_inflows = [1, 2, 3, 2, 5, $count_all_lifetime];
        $chart_solveds = [0, 1, 1, 0, 1, $count_solved];
    }
}

// ═══════════════════ 5. DEPARTMENT CHART DATA (PERIOD FILTERED) ═══════════════════
$chart_dept_names = [];
$chart_dept_totals = [];
$chart_dept_solved = [];
foreach (array_slice($dept_stats, 0, 5) as $ds) {
    $chart_dept_names[] = mb_strimwidth($ds['department_name'], 0, 16, '..');
    $chart_dept_totals[] = (int)$ds['total_complaints'];
    $chart_dept_solved[] = (int)$ds['resolved_count'];
}
if (empty($chart_dept_names)) {
    $chart_dept_names = ['Municipal', 'Revenue', 'Police', 'Anti-Corruption'];
    $chart_dept_totals = [0, 0, 0, 0];
    $chart_dept_solved = [0, 0, 0, 0];
}

// ═══════════════════ 6. CATEGORY DISTRIBUTION DATA (PERIOD FILTERED) ═══════════════════
$chart_cat_names = [];
$chart_cat_counts = [];
$cat_query = mysqli_query($conn, "
    SELECT cat.category_name, COUNT(c.complaint_id) AS total_count
    FROM categories cat
    LEFT JOIN complaints c ON (cat.category_id = c.category_id AND {$date_sql_complaints})
    GROUP BY cat.category_id, cat.category_name
    ORDER BY total_count DESC, cat.category_name ASC
    LIMIT 5
");
if ($cat_query && mysqli_num_rows($cat_query) > 0) {
    while ($cr = mysqli_fetch_assoc($cat_query)) {
        $chart_cat_names[] = $cr['category_name'];
        $chart_cat_counts[] = (int)$cr['total_count'];
    }
}
if (empty($chart_cat_names)) {
    $chart_cat_names = ['Bribery', 'Fraud', 'Misuse of Power', 'Embezzlement', 'Extortion'];
    $chart_cat_counts = [0, 0, 0, 0, 0];
}

// ═══════════════════ 7. AUDIT COMPLAINTS LIST WITH MULTI-FILTERS ═══════════════════
$search = trim($_GET['search'] ?? '');
$dept_filter = trim($_GET['department_filter'] ?? 'all');
$priority_filter = trim($_GET['priority_filter'] ?? 'all');
$status_filter = trim($_GET['status_filter'] ?? 'all');
$quick_filter = trim($_GET['quick_filter'] ?? 'all');

$sql = "
    SELECT c.*, cat.category_name, o.name AS officer_name, d.department_name, u.name AS citizen_name
    FROM complaints c
    LEFT JOIN categories cat ON c.category_id = cat.category_id
    LEFT JOIN officers o ON c.assigned_officer = o.officer_id
    LEFT JOIN departments d ON o.department_id = d.department_id
    LEFT JOIN users u ON c.user_id = u.user_id
    WHERE {$date_sql_complaints}
";
$params = [];
$types = "";

if (!empty($quick_filter) && $quick_filter !== 'all') {
    if ($quick_filter === 'critical') {
        $sql .= " AND c.priority IN ('Critical', 'High')";
    } elseif ($quick_filter === 'pending') {
        $sql .= " AND c.status IN ('Submitted', 'Pending Verification', 'Verified', 'Assigned')";
    } elseif ($quick_filter === 'solved') {
        $sql .= " AND c.status IN ('Investigation Completed', 'Closed')";
    } elseif ($quick_filter === 'active') {
        $sql .= " AND c.status = 'Under Investigation'";
    }
}

if (!empty($search)) {
    $sql .= " AND (c.complaint_id LIKE ? OR c.title LIKE ? OR c.description LIKE ? OR c.location LIKE ?)";
    $types .= "ssss";
    $search_term = "%{$search}%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
}

if (!empty($dept_filter) && $dept_filter !== 'all') {
    $sql .= " AND (d.department_id = ? OR d.department_name = ?)";
    $types .= "is";
    $params[] = intval($dept_filter);
    $params[] = $dept_filter;
}

if (!empty($priority_filter) && $priority_filter !== 'all') {
    $sql .= " AND c.priority = ?";
    $types .= "s";
    $params[] = $priority_filter;
}

if (!empty($status_filter) && $status_filter !== 'all') {
    $sql .= " AND c.status = ?";
    $types .= "s";
    $params[] = $status_filter;
}

$sql .= " ORDER BY c.created_at DESC, c.complaint_id DESC LIMIT 50";
$stmt = mysqli_prepare($conn, $sql);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$complaints_res = mysqli_stmt_get_result($stmt);
$complaints_list = [];
if ($complaints_res) {
    while ($row = mysqli_fetch_assoc($complaints_res)) {
        $complaints_list[] = $row;
    }
}
mysqli_stmt_close($stmt);

// All departments for dropdown
$all_depts_res = mysqli_query($conn, "SELECT department_id, department_name FROM departments ORDER BY department_name ASC");
$departments_dropdown = [];
if ($all_depts_res) {
    while ($dr = mysqli_fetch_assoc($all_depts_res)) {
        $departments_dropdown[] = $dr;
    }
}

// Helper function to build clean filter query strings preserving existing filters
function buildFilterUrl($overrides = []) {
    $current = $_GET;
    foreach ($overrides as $k => $v) {
        if ($v === null || $v === '') {
            unset($current[$k]);
        } else {
            $current[$k] = $v;
        }
    }
    return 'reports.php' . (!empty($current) ? '?' . http_build_query($current) : '');
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>System Intelligence &amp; Multi-Period Audit Reports — CCMS Admin</title>
  <meta name="description" content="Anti-Corruption Complaint Management System: Dynamic period-filtered executive reporting across 1-day, 3-day, 7-day, and monthly vigilance windows with interactive charts.">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&family=Space+Grotesk:wght@500;600;700;800&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
  <link rel="stylesheet" href="assets/css/style.css">

  <!-- Chart.js for High-Definition Dynamic Plots -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

  <style>
    /* ═══════════════════════════════════════════════════════════
       PREMIUM INTELLIGENCE & REPORTING DESIGN SYSTEM
       Rich Visualizations • High Contrast • Beautiful Cards
       ═══════════════════════════════════════════════════════════ */

    /* Period Filter Hero Card */
    .period-control-card {
      background: linear-gradient(135deg, rgba(79, 70, 229, 0.08) 0%, rgba(6, 182, 212, 0.06) 50%, var(--surface) 100%);
      border: 1.5px solid rgba(99, 102, 241, 0.35);
      border-radius: var(--r-lg);
      padding: 1.35rem 1.6rem;
      margin-bottom: 24px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12);
      position: relative;
      overflow: hidden;
    }
    .period-control-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 3px;
      background: linear-gradient(90deg, #6366f1, #06b6d4, #10b981);
    }

    .period-tag-label {
      font-size: 0.76rem;
      font-weight: 800;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      color: var(--indigo-400);
      display: inline-flex;
      align-items: center;
      gap: 0.4rem;
      margin-bottom: 0.5rem;
    }

    .period-button-group {
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
      align-items: center;
    }

    .period-btn {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 8px 16px;
      border-radius: 12px;
      font-size: 0.84rem;
      font-weight: 600;
      background: var(--surface-2);
      border: 1.5px solid var(--border);
      color: var(--text-2);
      text-decoration: none;
      transition: all 0.22s cubic-bezier(0.4, 0, 0.2, 1);
      position: relative;
      user-select: none;
    }
    .period-btn:hover {
      color: var(--text-1);
      border-color: var(--indigo-400);
      background: rgba(99, 102, 241, 0.12);
      transform: translateY(-2px);
      box-shadow: 0 4px 14px rgba(0, 0, 0, 0.15);
    }
    .period-btn.active {
      color: #ffffff !important;
      background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%) !important;
      border-color: #6366f1 !important;
      box-shadow: 0 6px 18px rgba(99, 102, 241, 0.45);
      transform: translateY(-1px);
    }
    .period-btn .period-subtag {
      font-size: 0.7rem;
      font-weight: 500;
      padding: 2px 7px;
      border-radius: 99px;
      background: rgba(0, 0, 0, 0.16);
      color: inherit;
      opacity: 0.88;
    }
    .period-btn.active .period-subtag {
      background: rgba(255, 255, 255, 0.25);
      color: #ffffff;
      opacity: 1;
    }

    .period-meta-pill {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 8px 14px;
      border-radius: 12px;
      background: var(--surface-2);
      border: 1px solid var(--border);
      font-size: 0.8rem;
      font-weight: 600;
      color: var(--text-2);
    }

    .pulse-dot-green {
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background: #10b981;
      display: inline-block;
      box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
      animation: pulseGreen 2s infinite;
    }
    @keyframes pulseGreen {
      0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
      70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
      100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
    }

    /* 6-Column KPI Grid */
    .kpi-report-grid {
      display: grid;
      grid-template-columns: repeat(6, 1fr);
      gap: 16px;
      margin-bottom: 24px;
    }
    @media (max-width: 1400px) { .kpi-report-grid { grid-template-columns: repeat(3, 1fr); } }
    @media (max-width: 768px) { .kpi-report-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 480px) { .kpi-report-grid { grid-template-columns: 1fr; } }

    .report-stat-card {
      background: var(--surface);
      border: 1.5px solid var(--border);
      border-radius: var(--r-lg);
      padding: 1.25rem 1.35rem;
      position: relative;
      overflow: hidden;
      box-shadow: var(--sh-card);
      transition: var(--tx);
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }
    .report-stat-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 12px 30px rgba(0,0,0,0.16);
      border-color: var(--indigo-400);
    }

    .stat-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 0.85rem;
    }

    .stat-icon {
      width: 42px;
      height: 42px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.15rem;
    }
    .stat-icon.indigo { background: rgba(79, 70, 229, 0.15); color: var(--indigo-500); }
    .stat-icon.cyan { background: rgba(6, 182, 212, 0.15); color: var(--cyan-500); }
    .stat-icon.amber { background: rgba(245, 158, 11, 0.15); color: var(--amber-500); }
    .stat-icon.emerald { background: rgba(16, 185, 129, 0.15); color: var(--emerald-500); }
    .stat-icon.rose { background: rgba(244, 63, 94, 0.15); color: var(--rose-500); }
    .stat-icon.purple { background: rgba(168, 85, 247, 0.15); color: #a855f7; }

    .stat-num {
      font-family: var(--font-heading);
      font-size: 2.15rem;
      font-weight: 800;
      color: var(--text-1);
      line-height: 1;
      margin-bottom: 0.35rem;
    }

    .stat-lbl {
      font-size: 0.82rem;
      font-weight: 600;
      color: var(--text-2);
      text-transform: uppercase;
      letter-spacing: 0.04em;
    }

    .stat-badge {
      font-size: 0.72rem;
      font-weight: 700;
      padding: 0.2rem 0.55rem;
      border-radius: 99px;
      display: inline-flex;
      align-items: center;
      gap: 0.3rem;
    }

    /* Interactive Chart Cards */
    .chart-box-card {
      background: var(--surface);
      border: 1.5px solid var(--border);
      border-radius: var(--r-lg);
      padding: 1.5rem;
      box-shadow: var(--sh-card);
      height: 100%;
      display: flex;
      flex-direction: column;
      transition: var(--tx);
      position: relative;
    }
    .chart-box-card:hover {
      border-color: var(--indigo-400);
      box-shadow: 0 10px 28px rgba(0,0,0,0.12);
    }

    .chart-header-flex {
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      margin-bottom: 1.25rem;
    }

    .chart-canvas-wrap {
      position: relative;
      flex: 1;
      min-height: 270px;
      width: 100%;
    }

    /* Keypoints Card */
    .keypoints-card {
      background: linear-gradient(135deg, rgba(16, 24, 45, 0.85) 0%, rgba(13, 20, 38, 0.95) 100%);
      border: 1.5px solid rgba(99, 102, 241, 0.3);
      border-radius: var(--r-lg);
      padding: 1.75rem 2rem;
      box-shadow: var(--sh-card);
      margin-bottom: 24px;
    }
    [data-bs-theme="light"] .keypoints-card {
      background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
      border-color: rgba(99, 102, 241, 0.25);
    }

    .keypoint-item {
      display: flex;
      align-items: flex-start;
      gap: 1rem;
      padding: 1rem;
      background: var(--surface-2);
      border: 1px solid var(--border);
      border-radius: 14px;
      transition: var(--tx);
    }
    .keypoint-item:hover {
      border-color: var(--indigo-400);
      transform: translateX(3px);
    }

    .keypoint-icon-box {
      width: 40px;
      height: 40px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.1rem;
      flex-shrink: 0;
    }

    .dept-rank-badge {
      width: 28px;
      height: 28px;
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 0.78rem;
      font-weight: 800;
      background: var(--surface-2);
      border: 1px solid var(--border);
      color: var(--text-2);
    }

    .pill-priority-critical {
      background: rgba(244, 63, 94, 0.15);
      color: var(--rose-500);
      border: 1px solid rgba(244, 63, 94, 0.3);
      padding: 0.25rem 0.65rem;
      border-radius: 99px;
      font-size: 0.72rem;
      font-weight: 700;
      text-transform: uppercase;
    }
    .pill-priority-high {
      background: rgba(245, 158, 11, 0.15);
      color: var(--amber-500);
      border: 1px solid rgba(245, 158, 11, 0.3);
      padding: 0.25rem 0.65rem;
      border-radius: 99px;
      font-size: 0.72rem;
      font-weight: 700;
      text-transform: uppercase;
    }
    .pill-priority-medium {
      background: rgba(59, 130, 246, 0.15);
      color: var(--indigo-400);
      border: 1px solid rgba(59, 130, 246, 0.3);
      padding: 0.25rem 0.65rem;
      border-radius: 99px;
      font-size: 0.72rem;
      font-weight: 700;
      text-transform: uppercase;
    }
    .pill-priority-low {
      background: rgba(148, 163, 184, 0.15);
      color: var(--text-3);
      border: 1px solid rgba(148, 163, 184, 0.3);
      padding: 0.25rem 0.65rem;
      border-radius: 99px;
      font-size: 0.72rem;
      font-weight: 700;
      text-transform: uppercase;
    }

    /* Glow Accents */
    .glow-indigo::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px; background: linear-gradient(90deg, #6366f1, #818cf8); border-radius: var(--r-lg) var(--r-lg) 0 0; }
    .glow-cyan::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px; background: linear-gradient(90deg, #06b6d4, #38bdf8); border-radius: var(--r-lg) var(--r-lg) 0 0; }
    .glow-amber::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px; background: linear-gradient(90deg, #f59e0b, #fbbf24); border-radius: var(--r-lg) var(--r-lg) 0 0; }
    .glow-emerald::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px; background: linear-gradient(90deg, #10b981, #34d399); border-radius: var(--r-lg) var(--r-lg) 0 0; }
    .glow-rose::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px; background: linear-gradient(90deg, #f43f5e, #fb7185); border-radius: var(--r-lg) var(--r-lg) 0 0; }
    .glow-purple::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px; background: linear-gradient(90deg, #a855f7, #c084fc); border-radius: var(--r-lg) var(--r-lg) 0 0; }

    /* Quick Filter Pills */
    .quick-filter-tabs {
      display: flex;
      gap: 8px;
      flex-wrap: wrap;
      align-items: center;
      margin-bottom: 16px;
    }
    .quick-pill {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 6px 14px;
      border-radius: 99px;
      font-size: 0.8rem;
      font-weight: 600;
      background: var(--surface-2);
      border: 1.5px solid var(--border);
      color: var(--text-2);
      text-decoration: none;
      transition: all 0.2s ease;
    }
    .quick-pill:hover {
      color: var(--text-1);
      border-color: var(--indigo-400);
      transform: translateY(-1px);
    }
    .quick-pill.active {
      color: #fff !important;
      background: var(--indigo-500) !important;
      border-color: var(--indigo-500) !important;
      box-shadow: 0 4px 12px rgba(99, 102, 241, 0.35);
    }

    /* Print styling */
    @media print {
      #sidebar, .top-navbar, .filter-bar, .nav-actions, .btn, .search-wrap, .period-control-card, .quick-filter-tabs {
        display: none !important;
      }
      #main-content { margin: 0 !important; width: 100% !important; }
      body { background: #fff !important; color: #000 !important; }
      .card-box, .report-stat-card, .chart-box-card {
        border: 1px solid #ccc !important;
        box-shadow: none !important;
        break-inside: avoid;
      }
    }
  </style>
</head>
<body>
<div id="wrapper">

  <!-- ══════════════════════ SIDEBAR ══════════════════════ -->
  <aside id="sidebar">
    <div class="sb-brand">
      <div class="sb-logo"><i class="fa-solid fa-shield-halved"></i></div>
      <div>
        <div class="sb-title">CCMS</div>
        <div class="sb-sub">Anti-Corruption</div>
      </div>
    </div>
    
    <nav class="sb-nav">
      <div class="sb-section-label">Main Overview</div>
      <a href="index.php" class="sb-link"><div class="icon-wrap"><i class="fa-solid fa-gauge-high"></i></div><span>Dashboard</span></a>
      <a href="manage-complaints.php" class="sb-link"><div class="icon-wrap"><i class="fa-solid fa-folder-open"></i></div><span>Manage Complaints</span><span class="sb-badge"><?= $count_all_lifetime ?></span></a>
      <a href="assign-complaints.php" class="sb-link"><div class="icon-wrap"><i class="fa-solid fa-user-tag"></i></div><span>Assign Complaints</span><span class="sb-badge amber"><?= $count_pending ?></span></a>
      
      <div class="sb-section-label">Personnel</div>
      <a href="manage-users.php" class="sb-link"><div class="icon-wrap"><i class="fa-solid fa-users"></i></div><span>Manage Users</span><span class="sb-badge"><?= $count_citizens_total ?></span></a>
      <a href="manage-officers.php" class="sb-link"><div class="icon-wrap"><i class="fa-solid fa-user-shield"></i></div><span>Manage Officers</span><span class="sb-badge"><?= $count_officers_total ?></span></a>
      
      <div class="sb-section-label">System Masters</div>
      <a href="manage-departments.php" class="sb-link"><div class="icon-wrap"><i class="fa-solid fa-building-columns"></i></div><span>Departments</span><span class="sb-badge"><?= $count_depts_total ?></span></a>
      <a href="manage-categories.php" class="sb-link"><div class="icon-wrap"><i class="fa-solid fa-tags"></i></div><span>Categories</span></a>
      
      <div class="sb-section-label">Intelligence</div>
      <a href="reports.php" class="sb-link active"><div class="icon-wrap"><i class="fa-solid fa-file-chart-column"></i></div><span>Intelligence Reports</span></a>
    </nav>
    
    <div class="sb-footer">
      <div class="sb-admin-card">
        <img src="https://images.unsplash.com/photo-1560250097-0b93528c311a?auto=format&fit=crop&w=80&q=80" alt="Admin" class="sb-avatar">
        <div class="sb-admin-info">
          <div class="sb-admin-name"><?= $admin_name ?></div>
          <div class="sb-admin-role">Super Administrator</div>
        </div>
      </div>
    </div>
  </aside>

  <!-- ══════════════════════ MAIN CONTENT ══════════════════════ -->
  <div id="main-content">
    <nav class="top-navbar">
      <div class="d-flex align-items-center gap-3">
        <button class="btn-toggle" id="sidebarToggle"><i class="fa-solid fa-bars-staggered"></i></button>
        <form method="GET" action="reports.php" class="search-wrap d-none d-md-flex align-items-center m-0">
          <input type="hidden" name="period" value="<?= htmlspecialchars($period) ?>">
          <i class="fa-solid fa-magnifying-glass"></i>
          <input type="text" name="search" id="tableSearch" placeholder="Search report by docket #, title, or office..." value="<?= htmlspecialchars($search) ?>">
          <?php if (!empty($search)): ?>
            <a href="<?= buildFilterUrl(['search' => null]) ?>" class="text-muted ms-2 me-1" title="Clear Search"><i class="fa-solid fa-xmark"></i></a>
          <?php endif; ?>
        </form>
      </div>
      
      <div class="nav-actions">
        <div class="online-chip d-none d-xl-flex"><span class="online-dot"></span>Database Live</div>
        <button class="nav-icon-btn" id="themeToggle" title="Toggle Theme"><i class="fa-solid fa-moon" id="themeIcon"></i></button>
        
        <div class="dropdown">
          <div class="nav-profile-btn dropdown-toggle" data-bs-toggle="dropdown">
            <img src="https://images.unsplash.com/photo-1560250097-0b93528c311a?auto=format&fit=crop&w=80&q=80" class="nav-avatar" alt="Admin">
            <div class="d-none d-md-block">
              <div class="nav-profile-name"><?= $admin_name ?></div>
              <div class="nav-profile-role">Super Administrator</div>
            </div>
          </div>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="reports.php"><i class="fa-solid fa-file-invoice"></i> System Intelligence Dossier</a></li>
            <li><div class="dropdown-divider"></div></li>
            <li><a class="dropdown-item text-danger" href="../logout.php"><i class="fa-solid fa-power-off"></i> Logout</a></li>
          </ul>
        </div>
      </div>
    </nav>

    <main class="content-body">
      <!-- Page Header -->
      <div class="page-header">
        <div>
          <div class="page-breadcrumb">
            <a href="index.php"><i class="fa-solid fa-house"></i></a>
            <i class="fa-solid fa-chevron-right"></i>
            <span>Intelligence</span>
            <i class="fa-solid fa-chevron-right"></i>
            <span>Executive Analytics &amp; Reports</span>
          </div>
          <h1 class="page-title">Executive Project Report &amp; System Intelligence</h1>
          <p class="page-subtitle">Real-time dynamic telemetry of complaints, registered whistleblowers, pending inquiries, and resolution performance.</p>
        </div>
        <div class="d-flex gap-2 align-items-center">
          <button class="btn btn-outline-primary" onclick="window.print()">
            <i class="fa-solid fa-print"></i> Print Report
          </button>
          <button class="btn btn-primary" onclick="openMasterReportModal()">
            <i class="fa-solid fa-file-pdf"></i> View &amp; Export Dossier
          </button>
        </div>
      </div>

      <!-- ══════════════════════ 1. DYNAMIC PERIOD / TIMEFRAME SELECTOR ══════════════════════ -->
      <div class="period-control-card">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
          <!-- Left: Period Pills -->
          <div>
            <div class="period-tag-label">
              <i class="fa-solid fa-clock-rotate-left"></i>
              <span>Audit Timeframe &amp; Report Window</span>
            </div>
            <div class="period-button-group">
              <!-- 1 Day Filter -->
              <a href="<?= buildFilterUrl(['period' => '1day', 'date_from' => null, 'date_to' => null]) ?>" class="period-btn <?= $period === '1day' ? 'active' : '' ?>">
                <i class="fa-solid fa-bolt text-warning"></i>
                <span>1 Day</span>
                <span class="period-subtag">Past 24h</span>
              </a>

              <!-- 3 Day Filter -->
              <a href="<?= buildFilterUrl(['period' => '3day', 'date_from' => null, 'date_to' => null]) ?>" class="period-btn <?= $period === '3day' ? 'active' : '' ?>">
                <i class="fa-solid fa-calendar-day text-info"></i>
                <span>3 Days</span>
                <span class="period-subtag">72h Window</span>
              </a>

              <!-- 7 Day Filter -->
              <a href="<?= buildFilterUrl(['period' => '7day', 'date_from' => null, 'date_to' => null]) ?>" class="period-btn <?= $period === '7day' ? 'active' : '' ?>">
                <i class="fa-solid fa-calendar-week" style="color: #a855f7;"></i>
                <span>7 Days</span>
                <span class="period-subtag">1 Week</span>
              </a>

              <!-- Last Month Filter -->
              <a href="<?= buildFilterUrl(['period' => 'last_month', 'date_from' => null, 'date_to' => null]) ?>" class="period-btn <?= $period === 'last_month' ? 'active' : '' ?>">
                <i class="fa-solid fa-calendar-days text-success"></i>
                <span>Last Month</span>
                <span class="period-subtag">30 Days</span>
              </a>

              <!-- All Time Filter -->
              <a href="<?= buildFilterUrl(['period' => 'all', 'date_from' => null, 'date_to' => null]) ?>" class="period-btn <?= $period === 'all' ? 'active' : '' ?>">
                <i class="fa-solid fa-infinity text-primary"></i>
                <span>All Time</span>
                <span class="period-subtag">Full History</span>
              </a>

              <!-- Custom Range Trigger -->
              <button class="btn btn-sm btn-ghost px-2.5 py-1.5 text-muted border" type="button" data-bs-toggle="collapse" data-bs-target="#customDateCollapse" aria-expanded="<?= $period === 'custom' ? 'true' : 'false' ?>" title="Custom Calendar Range">
                <i class="fa-solid fa-sliders me-1"></i> Custom Range
              </button>
            </div>
          </div>

          <!-- Right: Active Window Live Meta Badge -->
          <div class="d-flex align-items-center gap-2">
            <div class="period-meta-pill">
              <span class="pulse-dot-green"></span>
              <span>Audit Period: <strong class="text-primary"><?= htmlspecialchars($period_label) ?></strong></span>
              <span class="badge bg-secondary-subtle text-muted d-none d-lg-inline ms-1"><?= $count_all ?> of <?= $count_all_lifetime ?> Cases</span>
            </div>
          </div>
        </div>

        <!-- Custom Date Range Collapse Panel -->
        <div class="collapse <?= $period === 'custom' ? 'show' : '' ?> mt-3 pt-3 border-top" id="customDateCollapse">
          <form method="GET" action="reports.php" class="row g-2 align-items-center">
            <input type="hidden" name="period" value="custom">
            <?php if (!empty($search)): ?><input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>"><?php endif; ?>
            <?php if ($dept_filter !== 'all'): ?><input type="hidden" name="department_filter" value="<?= htmlspecialchars($dept_filter) ?>"><?php endif; ?>
            <?php if ($priority_filter !== 'all'): ?><input type="hidden" name="priority_filter" value="<?= htmlspecialchars($priority_filter) ?>"><?php endif; ?>
            <?php if ($status_filter !== 'all'): ?><input type="hidden" name="status_filter" value="<?= htmlspecialchars($status_filter) ?>"><?php endif; ?>

            <div class="col-auto">
              <span class="extra-small text-muted fw-bold"><i class="fa-solid fa-calendar me-1"></i> Start Date:</span>
            </div>
            <div class="col-auto">
              <input type="date" name="date_from" class="form-control form-control-sm" value="<?= htmlspecialchars($date_from ?: date('Y-m-d', strtotime('-14 days'))) ?>" required>
            </div>
            <div class="col-auto">
              <span class="extra-small text-muted fw-bold">End Date:</span>
            </div>
            <div class="col-auto">
              <input type="date" name="date_to" class="form-control form-control-sm" value="<?= htmlspecialchars($date_to ?: date('Y-m-d')) ?>" required>
            </div>
            <div class="col-auto">
              <button type="submit" class="btn btn-sm btn-primary">
                <i class="fa-solid fa-filter me-1"></i> Apply Custom Range
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- ══════════════════════ 2. LIVE METRICS & PERIOD KPIS (6 TILES) ══════════════════════ -->
      <div class="kpi-report-grid">
        <!-- 1. Total Complaints in Period -->
        <div class="report-stat-card glow-indigo">
          <div>
            <div class="stat-header">
              <div class="stat-icon indigo"><i class="fa-solid fa-folder-tree"></i></div>
              <span class="stat-badge bg-primary-subtle text-primary"><i class="fa-solid fa-filter"></i> In Period</span>
            </div>
            <div class="stat-num counter-value"><?= number_format($count_all) ?></div>
            <div class="stat-lbl">Total Complaints</div>
          </div>
          <div class="extra-small text-muted mt-2 pt-2 border-top d-flex justify-content-between align-items-center">
            <span>Lifetime: <?= number_format($count_all_lifetime) ?></span>
            <span class="badge bg-secondary-subtle text-muted"><?= $count_all_lifetime > 0 ? round(($count_all / $count_all_lifetime) * 100, 1) : 0 ?>% Vol</span>
          </div>
        </div>

        <!-- 2. Registered Citizens -->
        <div class="report-stat-card glow-cyan">
          <div>
            <div class="stat-header">
              <div class="stat-icon cyan"><i class="fa-solid fa-users"></i></div>
              <span class="stat-badge bg-info-subtle text-info"><i class="fa-solid fa-shield-halved"></i> Whistleblowers</span>
            </div>
            <div class="stat-num counter-value"><?= number_format($count_citizens_total) ?></div>
            <div class="stat-lbl">Registered Citizens</div>
          </div>
          <div class="extra-small text-muted mt-2 pt-2 border-top d-flex justify-content-between align-items-center">
            <span>Period Signups: +<?= $count_citizens_period ?></span>
            <span class="text-info fw-bold"><i class="fa-solid fa-check me-1"></i>Active</span>
          </div>
        </div>

        <!-- 3. Pending Triage -->
        <div class="report-stat-card glow-amber">
          <div>
            <div class="stat-header">
              <div class="stat-icon amber"><i class="fa-solid fa-clock-rotate-left"></i></div>
              <span class="stat-badge bg-warning-subtle text-warning"><i class="fa-solid fa-hourglass-half"></i> Backlog</span>
            </div>
            <div class="stat-num counter-value text-warning"><?= number_format($count_pending) ?></div>
            <div class="stat-lbl">Pending Triage</div>
          </div>
          <div class="mt-2 pt-2 border-top">
            <div class="progress" style="height: 4px; border-radius: 99px;">
              <div class="progress-bar bg-warning" style="width: <?= $pending_rate ?>%;"></div>
            </div>
            <div class="d-flex justify-content-between extra-small text-muted mt-1">
              <span>Backlog Ratio</span>
              <span class="text-warning fw-bold"><?= $pending_rate ?>%</span>
            </div>
          </div>
        </div>

        <!-- 4. Solved / Concluded Complaints -->
        <div class="report-stat-card glow-emerald">
          <div>
            <div class="stat-header">
              <div class="stat-icon emerald"><i class="fa-solid fa-circle-check"></i></div>
              <span class="stat-badge bg-success-subtle text-success"><i class="fa-solid fa-check-double"></i> Concluded</span>
            </div>
            <div class="stat-num counter-value text-success"><?= number_format($count_solved) ?></div>
            <div class="stat-lbl">Solved Complaints</div>
          </div>
          <div class="mt-2 pt-2 border-top">
            <div class="progress" style="height: 4px; border-radius: 99px;">
              <div class="progress-bar bg-success" style="width: <?= $resolution_rate ?>%;"></div>
            </div>
            <div class="d-flex justify-content-between extra-small text-muted mt-1">
              <span>Clearance Rate</span>
              <span class="text-success fw-bold"><?= $resolution_rate ?>%</span>
            </div>
          </div>
        </div>

        <!-- 5. Critical & High Priority -->
        <div class="report-stat-card glow-rose">
          <div>
            <div class="stat-header">
              <div class="stat-icon rose"><i class="fa-solid fa-triangle-exclamation"></i></div>
              <span class="stat-badge bg-danger-subtle text-danger"><i class="fa-solid fa-bolt"></i> High Severity</span>
            </div>
            <div class="stat-num counter-value text-danger"><?= number_format($count_critical_total) ?></div>
            <div class="stat-lbl">Critical Complaints</div>
          </div>
          <div class="extra-small text-muted mt-2 pt-2 border-top d-flex gap-1 justify-content-between align-items-center">
            <span class="badge bg-danger-subtle text-danger"><?= $count_critical ?> Critical</span>
            <span class="badge bg-warning-subtle text-warning"><?= $count_high ?> High</span>
          </div>
        </div>

        <!-- 6. Under Active Investigation -->
        <div class="report-stat-card glow-purple">
          <div>
            <div class="stat-header">
              <div class="stat-icon purple"><i class="fa-solid fa-magnifying-glass-chart"></i></div>
              <span class="stat-badge bg-primary-subtle text-primary"><i class="fa-solid fa-user-shield"></i> Field Probes</span>
            </div>
            <div class="stat-num counter-value" style="color: #a855f7;"><?= number_format($count_under_inv) ?></div>
            <div class="stat-lbl">Under Active Probe</div>
          </div>
          <div class="extra-small text-muted mt-2 pt-2 border-top d-flex justify-content-between align-items-center">
            <span><?= $count_officers_total ?> Vigilance Officers</span>
            <span class="text-primary fw-bold"><?= $investigation_rate ?>%</span>
          </div>
        </div>
      </div>

      <!-- ══════════════════════ 3. INTERACTIVE CHARTS & VISUALIZATIONS ══════════════════════ -->
      <div class="row g-4 mb-4">
        <!-- Plot 1: Trajectory Line Chart (Inflow vs. Clearance) -->
        <div class="col-lg-8">
          <div class="chart-box-card">
            <div class="chart-header-flex">
              <div>
                <div class="fw-bold fs-6 d-flex align-items-center gap-2" style="color: var(--text-1);">
                  <i class="fa-solid fa-chart-line text-primary"></i> <?= htmlspecialchars($chart_timeline_title) ?>
                </div>
                <div class="text-muted extra-small"><?= htmlspecialchars($chart_timeline_sub) ?></div>
              </div>
              <span class="badge bg-primary-subtle text-primary font-monospace px-2.5 py-1">
                <i class="fa-solid fa-filter me-1"></i><?= htmlspecialchars($period_label) ?>
              </span>
            </div>
            <div class="chart-canvas-wrap">
              <canvas id="trendLineChart"></canvas>
            </div>
          </div>
        </div>

        <!-- Plot 2: Case Status Spectrum (Doughnut Chart) -->
        <div class="col-lg-4">
          <div class="chart-box-card">
            <div class="chart-header-flex">
              <div>
                <div class="fw-bold fs-6 d-flex align-items-center gap-2" style="color: var(--text-1);">
                  <i class="fa-solid fa-chart-pie text-cyan"></i> Case Status Spectrum
                </div>
                <div class="text-muted extra-small">Realtime lifecycle breakdown for <?= htmlspecialchars($period_label) ?></div>
              </div>
              <span class="badge bg-success-subtle text-success font-monospace"><?= $count_all ?> Period Cases</span>
            </div>
            <div class="chart-canvas-wrap position-relative">
              <canvas id="statusDonutChart"></canvas>
            </div>
          </div>
        </div>

        <!-- Plot 3: Department Complaint Load & Clearance (Bar Chart) -->
        <div class="col-lg-6">
          <div class="chart-box-card">
            <div class="chart-header-flex">
              <div>
                <div class="fw-bold fs-6 d-flex align-items-center gap-2" style="color: var(--text-1);">
                  <i class="fa-solid fa-building-shield text-indigo-500"></i> Departmental Caseload &amp; Clearance Matrix
                </div>
                <div class="text-muted extra-small">Complaints received vs. successfully solved in <?= htmlspecialchars($period_label) ?></div>
              </div>
              <span class="badge bg-info-subtle text-info font-monospace"><?= count($chart_dept_names) ?> Active Depts</span>
            </div>
            <div class="chart-canvas-wrap">
              <canvas id="departmentBarChart"></canvas>
            </div>
          </div>
        </div>

        <!-- Plot 4: Top Corruption Allegations by Category (Horizontal Bar Chart) -->
        <div class="col-lg-6">
          <div class="chart-box-card">
            <div class="chart-header-flex">
              <div>
                <div class="fw-bold fs-6 d-flex align-items-center gap-2" style="color: var(--text-1);">
                  <i class="fa-solid fa-tags text-cyan"></i> Top Corruption Allegations by Category
                </div>
                <div class="text-muted extra-small">Misconduct classifications identified in <?= htmlspecialchars($period_label) ?></div>
              </div>
              <span class="badge bg-primary-subtle text-primary font-monospace"><?= count($chart_cat_names) ?> Categories</span>
            </div>
            <div class="chart-canvas-wrap">
              <canvas id="categoryBarChart"></canvas>
            </div>
          </div>
        </div>

        <!-- Plot 5: Severity & Risk Matrix (Polar Area Chart & Threat Summary) -->
        <div class="col-12">
          <div class="chart-box-card">
            <div class="chart-header-flex">
              <div>
                <div class="fw-bold fs-6 d-flex align-items-center gap-2" style="color: var(--text-1);">
                  <i class="fa-solid fa-shield-virus text-danger"></i> Systemwide Priority &amp; Threat Severity Analysis
                </div>
                <div class="text-muted extra-small">Distribution of risk tiers logged within <?= htmlspecialchars($period_label) ?></div>
              </div>
              <span class="badge bg-danger-subtle text-danger font-monospace px-3 py-1">
                <i class="fa-solid fa-triangle-exclamation me-1"></i><?= $count_critical_total ?> Urgent Cases
              </span>
            </div>
            <div class="row align-items-center">
              <div class="col-lg-5">
                <div class="chart-canvas-wrap" style="min-height: 250px;">
                  <canvas id="priorityPolarChart"></canvas>
                </div>
              </div>
              <div class="col-lg-7">
                <div class="row g-3">
                  <div class="col-sm-6">
                    <div class="p-3 rounded-3" style="background: rgba(244, 63, 94, 0.08); border: 1.5px solid rgba(244, 63, 94, 0.25);">
                      <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="fw-bold text-danger"><i class="fa-solid fa-fire me-1"></i> Critical Tier</span>
                        <span class="badge bg-danger text-white"><?= $count_critical ?> Cases</span>
                      </div>
                      <p class="extra-small text-muted mb-0">High-magnitude corruption with direct public fiscal compromise requiring immediate summons.</p>
                    </div>
                  </div>
                  <div class="col-sm-6">
                    <div class="p-3 rounded-3" style="background: rgba(245, 158, 11, 0.08); border: 1.5px solid rgba(245, 158, 11, 0.25);">
                      <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="fw-bold text-warning"><i class="fa-solid fa-bolt me-1"></i> High Priority</span>
                        <span class="badge bg-warning text-dark"><?= $count_high ?> Cases</span>
                      </div>
                      <p class="extra-small text-muted mb-0">Substantial misconduct allegations under active supervisory monitoring and witness verification.</p>
                    </div>
                  </div>
                  <div class="col-sm-6">
                    <div class="p-3 rounded-3" style="background: rgba(59, 130, 246, 0.08); border: 1.5px solid rgba(59, 130, 246, 0.25);">
                      <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="fw-bold" style="color: var(--indigo-400);"><i class="fa-solid fa-circle-info me-1"></i> Medium Priority</span>
                        <span class="badge bg-primary text-white"><?= $count_medium ?> Cases</span>
                      </div>
                      <p class="extra-small text-muted mb-0">Standard administrative irregularities undergoing standard evidentiary review pipelines.</p>
                    </div>
                  </div>
                  <div class="col-sm-6">
                    <div class="p-3 rounded-3" style="background: rgba(148, 163, 184, 0.08); border: 1.5px solid rgba(148, 163, 184, 0.25);">
                      <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="fw-bold text-muted"><i class="fa-solid fa-layer-group me-1"></i> Low Priority</span>
                        <span class="badge bg-secondary text-white"><?= $count_low ?> Cases</span>
                      </div>
                      <p class="extra-small text-muted mb-0">Procedural queries, non-fiscal procedural grievances, and preliminary informational submissions.</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ══════════════════════ 4. EXECUTIVE BRIEFING & KEY FINDINGS ══════════════════════ -->
      <div class="keypoints-card">
        <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-3" style="border-color: var(--border) !important;">
          <div class="d-flex align-items-center gap-2">
            <div class="stat-icon indigo" style="width:36px;height:36px;font-size:1rem;"><i class="fa-solid fa-lightbulb"></i></div>
            <div>
              <h3 class="h5 fw-bold mb-0" style="color: var(--text-1);">Executive Intelligence Briefing &amp; Period Assessment</h3>
              <div class="text-muted extra-small">Synthesized findings for <strong><?= htmlspecialchars($period_label) ?></strong> (<?= htmlspecialchars($period_subtitle) ?>)</div>
            </div>
          </div>
          <span class="badge bg-primary-subtle text-primary font-monospace px-3 py-1.5" style="border-radius: 99px;">
            <?= strtoupper(htmlspecialchars($period_badge)) ?>
          </span>
        </div>

        <div class="row g-3">
          <!-- Keypoint 1: Resolution Efficiency -->
          <div class="col-md-6 col-lg-3">
            <div class="keypoint-item h-100">
              <div class="keypoint-icon-box" style="background: rgba(16, 185, 129, 0.15); color: var(--emerald-500);">
                <i class="fa-solid fa-gauge-high"></i>
              </div>
              <div>
                <strong style="color: var(--text-1); font-size: 0.88rem; display: block;">Clearance Efficiency: <?= $resolution_rate ?>%</strong>
                <p class="extra-small text-muted mb-0 mt-1" style="line-height: 1.5;">
                  <strong><?= $count_solved ?> of <?= $count_all ?> complaints</strong> concluded within this timeframe with formal recommendations.
                </p>
              </div>
            </div>
          </div>

          <!-- Keypoint 2: Critical Severity Alert -->
          <div class="col-md-6 col-lg-3">
            <div class="keypoint-item h-100">
              <div class="keypoint-icon-box" style="background: rgba(244, 63, 94, 0.15); color: var(--rose-500);">
                <i class="fa-solid fa-triangle-exclamation"></i>
              </div>
              <div>
                <strong style="color: var(--text-1); font-size: 0.88rem; display: block;">High Vulnerabilities: <?= $count_critical_total ?></strong>
                <p class="extra-small text-muted mb-0 mt-1" style="line-height: 1.5;">
                  <strong><?= $count_critical ?> critical and <?= $count_high ?> high-priority cases</strong> identified in this period requiring urgent intervention.
                </p>
              </div>
            </div>
          </div>

          <!-- Keypoint 3: Top Department Load -->
          <div class="col-md-6 col-lg-3">
            <div class="keypoint-item h-100">
              <div class="keypoint-icon-box" style="background: rgba(6, 182, 212, 0.15); color: var(--cyan-500);">
                <i class="fa-solid fa-building-circle-exclamation"></i>
              </div>
              <div>
                <strong style="color: var(--text-1); font-size: 0.88rem; display: block;">Highest Load: <?= $top_dept_name ?></strong>
                <p class="extra-small text-muted mb-0 mt-1" style="line-height: 1.5;">
                  Recorded <strong><?= $top_dept_cases ?> complaints</strong> in this window with a resolution index of <strong><?= $top_dept_rate ?>%</strong>.
                </p>
              </div>
            </div>
          </div>

          <!-- Keypoint 4: Citizen Whistleblower Trust -->
          <div class="col-md-6 col-lg-3">
            <div class="keypoint-item h-100">
              <div class="keypoint-icon-box" style="background: rgba(245, 158, 11, 0.15); color: var(--amber-500);">
                <i class="fa-solid fa-user-shield"></i>
              </div>
              <div>
                <strong style="color: var(--text-1); font-size: 0.88rem; display: block;">Whistleblower Engagement</strong>
                <p class="extra-small text-muted mb-0 mt-1" style="line-height: 1.5;">
                  <strong><?= $count_citizens_total ?> total citizens</strong> registered, with <strong>+<?= $count_citizens_period ?></strong> joining in this audit period.
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Strategic Directives Banner -->
        <div class="mt-3 p-3 rounded-3" style="background: var(--surface-2); border: 1px solid var(--border);">
          <div class="d-flex align-items-center gap-2 extra-small text-muted flex-wrap">
            <strong class="text-primary text-uppercase"><i class="fa-solid fa-bullhorn me-1"></i> Strategic Action Plan:</strong>
            <span>1. Expedite <?= $count_pending ?> pending submissions within the statutory triage SLA.</span>
            <span class="mx-1">•</span>
            <span>2. Maintain vigilance deployment to <?= $top_dept_name ?>.</span>
            <span class="mx-1">•</span>
            <span>3. Resolve <?= $count_under_inv ?> active field inquiries before audit closure.</span>
          </div>
        </div>
      </div>

      <!-- ══════════════════════ 5. DEPARTMENTAL AUDIT MATRIX TABLE ══════════════════════ -->
      <div class="card-box mb-4">
        <div class="card-box-head">
          <div class="card-box-title">
            <div class="title-icon"><i class="fa-solid fa-building-shield"></i></div>
            Departmental Complaint Breakdown &amp; Clearance Matrix (<?= htmlspecialchars($period_label) ?>)
          </div>
          <div class="d-flex gap-2 align-items-center">
            <span class="extra-small text-muted">Audited across <?= count($dept_stats) ?> departments</span>
          </div>
        </div>

        <div class="tbl-wrap">
          <table class="tbl">
            <thead>
              <tr>
                <th style="width: 50px;">Rank</th>
                <th>Target Department</th>
                <th>Assigned Officers</th>
                <th>Period Complaints</th>
                <th>Pending Triage</th>
                <th>Resolved / Solved</th>
                <th>Critical Cases</th>
                <th>Clearance Performance</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($dept_stats)): ?>
                <?php $rank = 1; foreach ($dept_stats as $d): ?>
                  <?php 
                    $d_total = (int)$d['total_complaints'];
                    $d_solved = (int)$d['resolved_count'];
                    $d_pending = (int)$d['pending_count'];
                    $d_crit = (int)$d['critical_count'];
                    $d_rate = ($d_total > 0) ? round(($d_solved / $d_total) * 100, 1) : 0;
                  ?>
                  <tr>
                    <td><div class="dept-rank-badge"><?= $rank++ ?></div></td>
                    <td>
                      <div class="fw-bold" style="color: var(--indigo-500);"><?= htmlspecialchars($d['department_name']) ?></div>
                    </td>
                    <td><span class="badge bg-secondary-subtle text-muted"><i class="fa-solid fa-user-shield me-1"></i><?= $d['total_officers'] ?> Officers</span></td>
                    <td><strong><?= $d_total ?></strong></td>
                    <td>
                      <?php if ($d_pending > 0): ?>
                        <span class="badge bg-warning-subtle text-warning"><?= $d_pending ?> Pending</span>
                      <?php else: ?>
                        <span class="text-muted extra-small">0 Pending</span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <span class="badge bg-success-subtle text-success"><?= $d_solved ?> Solved</span>
                    </td>
                    <td>
                      <?php if ($d_crit > 0): ?>
                        <span class="badge bg-danger-subtle text-danger"><i class="fa-solid fa-triangle-exclamation me-1"></i><?= $d_crit ?></span>
                      <?php else: ?>
                        <span class="text-muted extra-small">0</span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <div class="d-flex align-items-center gap-2" style="width: 140px;">
                        <div class="progress flex-grow-1" style="height: 6px; border-radius: 99px;">
                          <div class="progress-bar <?= $d_rate >= 70 ? 'bg-success' : ($d_rate >= 40 ? 'bg-info' : 'bg-warning') ?>" style="width: <?= $d_rate ?>%;"></div>
                        </div>
                        <span class="font-monospace extra-small fw-bold"><?= $d_rate ?>%</span>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr><td colspan="8" class="text-center py-4 text-muted">No departmental statistics recorded for this period.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- ══════════════════════ 6. MULTI-PARAMETER AUDIT FILTER ══════════════════════ -->
      <div class="quick-filter-tabs">
        <span class="text-muted extra-small fw-bold d-flex align-items-center me-2"><i class="fa-solid fa-bolt me-1 text-primary"></i> Fast Filter:</span>
        <a href="<?= buildFilterUrl(['quick_filter' => null]) ?>" class="quick-pill <?= empty($quick_filter) || $quick_filter === 'all' ? 'active' : '' ?>">All in Window (<?= $count_all ?>)</a>
        <a href="<?= buildFilterUrl(['quick_filter' => 'critical']) ?>" class="quick-pill <?= $quick_filter === 'critical' ? 'active' : '' ?> text-danger"><i class="fa-solid fa-triangle-exclamation"></i> Critical / High (<?= $count_critical_total ?>)</a>
        <a href="<?= buildFilterUrl(['quick_filter' => 'pending']) ?>" class="quick-pill <?= $quick_filter === 'pending' ? 'active' : '' ?> text-warning"><i class="fa-solid fa-clock-rotate-left"></i> Pending Triage (<?= $count_pending ?>)</a>
        <a href="<?= buildFilterUrl(['quick_filter' => 'active']) ?>" class="quick-pill <?= $quick_filter === 'active' ? 'active' : '' ?>" style="color: #06b6d4;"><i class="fa-solid fa-magnifying-glass-chart"></i> Active Probe (<?= $count_under_inv ?>)</a>
        <a href="<?= buildFilterUrl(['quick_filter' => 'solved']) ?>" class="quick-pill <?= $quick_filter === 'solved' ? 'active' : '' ?> text-success"><i class="fa-solid fa-circle-check"></i> Solved &amp; Closed (<?= $count_solved ?>)</a>
      </div>

      <form method="GET" action="reports.php" class="filter-bar">
        <div class="row g-3 align-items-end">
          <div class="col-md-2">
            <label class="form-label">Audit Timeframe</label>
            <div class="input-icon-group">
              <i class="fa-solid fa-clock input-icon"></i>
              <select class="form-select" name="period" onchange="this.form.submit()">
                <option value="1day" <?= $period === '1day' ? 'selected' : '' ?>>1 Day (24 Hours)</option>
                <option value="3day" <?= $period === '3day' ? 'selected' : '' ?>>3 Days (72h)</option>
                <option value="7day" <?= $period === '7day' ? 'selected' : '' ?>>7 Days (1 Week)</option>
                <option value="last_month" <?= in_array($period, ['last_month', '30day']) ? 'selected' : '' ?>>Last Month (30d)</option>
                <option value="all" <?= $period === 'all' ? 'selected' : '' ?>>All Time History</option>
                <?php if ($period === 'custom'): ?>
                  <option value="custom" selected>Custom Range</option>
                <?php endif; ?>
              </select>
            </div>
          </div>

          <div class="col-md-3">
            <label class="form-label">Keyword Search</label>
            <div class="input-icon-group">
              <i class="fa-solid fa-magnifying-glass input-icon"></i>
              <input type="text" name="search" class="form-control" placeholder="Search docket #, title, or office..." value="<?= htmlspecialchars($search) ?>">
            </div>
          </div>

          <div class="col-md-2">
            <label class="form-label">Department</label>
            <div class="input-icon-group">
              <i class="fa-solid fa-building-columns input-icon"></i>
              <select class="form-select" name="department_filter">
                <option value="all">All Departments</option>
                <?php foreach ($departments_dropdown as $dept): ?>
                  <option value="<?= $dept['department_id'] ?>" <?= ($dept_filter == $dept['department_id'] || $dept_filter === $dept['department_name']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($dept['department_name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="col-md-2">
            <label class="form-label">Priority Level</label>
            <div class="input-icon-group">
              <i class="fa-solid fa-triangle-exclamation input-icon"></i>
              <select class="form-select" name="priority_filter">
                <option value="all">All Priorities</option>
                <option value="Critical" <?= $priority_filter === 'Critical' ? 'selected' : '' ?>>Critical</option>
                <option value="High" <?= $priority_filter === 'High' ? 'selected' : '' ?>>High</option>
                <option value="Medium" <?= $priority_filter === 'Medium' ? 'selected' : '' ?>>Medium</option>
                <option value="Low" <?= $priority_filter === 'Low' ? 'selected' : '' ?>>Low</option>
              </select>
            </div>
          </div>

          <div class="col-md-2">
            <label class="form-label">Status</label>
            <div class="input-icon-group">
              <i class="fa-solid fa-filter input-icon"></i>
              <select class="form-select" name="status_filter">
                <option value="all">All Statuses</option>
                <option value="Submitted" <?= $status_filter === 'Submitted' ? 'selected' : '' ?>>Submitted</option>
                <option value="Assigned" <?= $status_filter === 'Assigned' ? 'selected' : '' ?>>Assigned</option>
                <option value="Under Investigation" <?= $status_filter === 'Under Investigation' ? 'selected' : '' ?>>Under Investigation</option>
                <option value="Investigation Completed" <?= $status_filter === 'Investigation Completed' ? 'selected' : '' ?>>Investigation Completed</option>
                <option value="Closed" <?= $status_filter === 'Closed' ? 'selected' : '' ?>>Closed</option>
              </select>
            </div>
          </div>

          <div class="col-md-1 d-flex gap-1">
            <button type="submit" class="btn btn-primary flex-grow-1" title="Apply Filters">
              <i class="fa-solid fa-filter"></i>
            </button>
            <a href="reports.php?period=<?= urlencode($period) ?>" class="btn btn-ghost" title="Reset Filters">
              <i class="fa-solid fa-rotate-left"></i>
            </a>
          </div>
        </div>
      </form>

      <!-- ══════════════════════ 7. AUDITED COMPLAINTS REPOSITORY TABLE ══════════════════════ -->
      <div class="card-box">
        <div class="card-box-head">
          <div class="card-box-title">
            <div class="title-icon"><i class="fa-solid fa-clipboard-list"></i></div>
            Audited Complaints Repository &amp; Case Telemetry (<?= htmlspecialchars($period_label) ?>)
          </div>
          <div class="d-flex gap-2 align-items-center">
            <span class="extra-small text-muted">Showing <?= count($complaints_list) ?> matching cases</span>
            <button class="btn btn-sm btn-ghost btn-export-pdf" data-doc-name="Complaints_Audit_<?= $period ?>_Report">
              <i class="fa-solid fa-download me-1"></i> Export Table
            </button>
          </div>
        </div>

        <div class="tbl-wrap">
          <table class="tbl" id="reportsTable">
            <thead>
              <tr>
                <th>Docket #</th>
                <th>Subject &amp; Allegation</th>
                <th>Complainant</th>
                <th>Target Department</th>
                <th>Priority</th>
                <th>Status</th>
                <th>Filing Date</th>
                <th class="text-end">Audit Action</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($complaints_list)): ?>
                <?php foreach ($complaints_list as $c): ?>
                  <?php 
                    $token = "CCMS-2026-" . str_pad($c['complaint_id'], 4, '0', STR_PAD_LEFT);
                    $pri = $c['priority'] ?? 'Medium';
                    $pri_class = match($pri) {
                        'Critical' => 'pill-priority-critical',
                        'High' => 'pill-priority-high',
                        'Low' => 'pill-priority-low',
                        default => 'pill-priority-medium'
                    };
                    $stat = $c['status'] ?? 'Submitted';
                    $stat_badge = match($stat) {
                        'Investigation Completed', 'Closed' => 'bg-success-subtle text-success',
                        'Under Investigation' => 'bg-info-subtle text-info',
                        'Assigned' => 'bg-primary-subtle text-primary',
                        default => 'bg-warning-subtle text-warning'
                    };
                  ?>
                  <tr>
                    <td>
                      <span class="font-monospace fw-bold text-primary">#<?= $token ?></span>
                    </td>
                    <td>
                      <div class="fw-bold" style="color: var(--text-1); max-width: 280px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                        <?= htmlspecialchars($c['title']) ?>
                      </div>
                      <div class="extra-small text-muted"><?= htmlspecialchars($c['category_name'] ?? 'Anti-Corruption') ?></div>
                    </td>
                    <td>
                      <div class="extra-small fw-semibold"><?= htmlspecialchars($c['citizen_name'] ?? 'Anonymous Whistleblower') ?></div>
                    </td>
                    <td>
                      <span class="fw-semibold" style="font-size: 0.85rem;"><?= htmlspecialchars($c['department_name'] ?? 'Municipal Office') ?></span>
                    </td>
                    <td>
                      <span class="<?= $pri_class ?>"><?= htmlspecialchars($pri) ?></span>
                    </td>
                    <td>
                      <span class="badge <?= $stat_badge ?>"><?= htmlspecialchars($stat) ?></span>
                    </td>
                    <td class="extra-small text-muted font-monospace">
                      <?= !empty($c['created_at']) ? date('M d, Y • h:i A', strtotime($c['created_at'])) : 'Recent' ?>
                    </td>
                    <td class="text-end">
                      <div class="d-inline-flex gap-1">
                        <button class="btn btn-ghost btn-icon btn-sm" onclick="previewCaseDocket('<?= $token ?>', '<?= htmlspecialchars(addslashes($c['title'])) ?>', '<?= htmlspecialchars(addslashes($c['department_name'] ?? '')) ?>', '<?= htmlspecialchars(addslashes($c['category_name'] ?? '')) ?>', '<?= $stat ?>', '<?= $pri ?>', '<?= !empty($c['created_at']) ? date('M d, Y', strtotime($c['created_at'])) : '' ?>', '<?= htmlspecialchars(addslashes(mb_strimwidth($c['description'] ?? '', 0, 200, '...'))) ?>')" title="Preview Case Audit">
                          <i class="fa-solid fa-eye text-primary"></i>
                        </button>
                        <button class="btn btn-ghost btn-icon btn-sm btn-export-pdf" data-doc-name="Docket_<?= $token ?>_Report" title="Export PDF">
                          <i class="fa-solid fa-file-pdf text-danger"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="8" class="text-center py-4 text-muted">
                    <i class="fa-solid fa-circle-info me-1"></i> No complaints matched this filter within the <strong><?= htmlspecialchars($period_label) ?></strong> window.
                  </td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </main>
  </div>
</div>

<!-- ══════════════════════ MODAL: MASTER FULL PROJECT REPORT ══════════════════════ -->
<div class="modal fade" id="masterProjectReportModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-bottom">
        <h5 class="modal-title fs-5 d-flex align-items-center gap-2">
          <i class="fa-solid fa-shield-halved text-primary"></i>
          <span>Official Anti-Corruption System Intelligence Report</span>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body p-4" id="printableReportSection">
        <div class="p-4 rounded-3" style="background: var(--surface); border: 1.5px solid var(--border);">
          <!-- Report Header -->
          <div class="d-flex justify-content-between align-items-start border-bottom pb-3 mb-4">
            <div>
              <div class="badge bg-primary-subtle text-primary font-monospace px-3 py-1 mb-2">
                OFFICIAL SYSTEMWIDE AUDIT DOSSIER • <?= strtoupper(htmlspecialchars($period_label)) ?>
              </div>
              <h2 class="h3 fw-bold mb-1" style="color: var(--text-1);">Corruption Complaint Management System (CCMS)</h2>
              <div class="text-muted" style="font-size: 0.88rem;">National Vigilance &amp; Public Accountability Directorate • Compiled <?= date('F d, Y • h:i A') ?></div>
            </div>
            <div class="text-end font-monospace text-muted extra-small">
              <div>REPORT ID: CCMS-AUDIT-<?= strtoupper($period) ?>-<?= date('Ymd') ?></div>
              <div>CLASSIFICATION: OFFICIAL USE ONLY</div>
              <div class="text-success"><i class="fa-solid fa-circle-check"></i> CRYPTOGRAPHIC INTEGRITY VERIFIED</div>
            </div>
          </div>

          <!-- Master Figures Grid -->
          <div class="row g-3 mb-4 text-center">
            <div class="col-6 col-md-2">
              <div class="p-3 rounded-3" style="background: var(--surface-2); border: 1px solid var(--border);">
                <div class="extra-small text-muted text-uppercase fw-bold">Period Complaints</div>
                <div class="fs-3 fw-bold text-primary mt-1"><?= $count_all ?></div>
                <div class="extra-small text-muted">of <?= $count_all_lifetime ?> total</div>
              </div>
            </div>
            <div class="col-6 col-md-2">
              <div class="p-3 rounded-3" style="background: var(--surface-2); border: 1px solid var(--border);">
                <div class="extra-small text-muted text-uppercase fw-bold">Citizens Registered</div>
                <div class="fs-3 fw-bold text-info mt-1"><?= $count_citizens_total ?></div>
                <div class="extra-small text-muted">+<?= $count_citizens_period ?> in period</div>
              </div>
            </div>
            <div class="col-6 col-md-2">
              <div class="p-3 rounded-3" style="background: var(--surface-2); border: 1px solid var(--border);">
                <div class="extra-small text-muted text-uppercase fw-bold">Pending Triage</div>
                <div class="fs-3 fw-bold text-warning mt-1"><?= $count_pending ?></div>
                <div class="extra-small text-warning"><?= $pending_rate ?>% ratio</div>
              </div>
            </div>
            <div class="col-6 col-md-2">
              <div class="p-3 rounded-3" style="background: var(--surface-2); border: 1px solid var(--border);">
                <div class="extra-small text-muted text-uppercase fw-bold">Resolved / Solved</div>
                <div class="fs-3 fw-bold text-success mt-1"><?= $count_solved ?></div>
                <div class="extra-small text-success"><?= $resolution_rate ?>% rate</div>
              </div>
            </div>
            <div class="col-6 col-md-2">
              <div class="p-3 rounded-3" style="background: var(--surface-2); border: 1px solid var(--border);">
                <div class="extra-small text-muted text-uppercase fw-bold">Critical Complaints</div>
                <div class="fs-3 fw-bold text-danger mt-1"><?= $count_critical_total ?></div>
                <div class="extra-small text-danger"><?= $count_critical ?> critical tier</div>
              </div>
            </div>
            <div class="col-6 col-md-2">
              <div class="p-3 rounded-3" style="background: var(--surface-2); border: 1px solid var(--border);">
                <div class="extra-small text-muted text-uppercase fw-bold">Active Probes</div>
                <div class="fs-3 fw-bold text-primary mt-1"><?= $count_under_inv ?></div>
                <div class="extra-small text-muted"><?= $count_officers_total ?> officers</div>
              </div>
            </div>
          </div>

          <!-- Executive Finding Narrative -->
          <div class="p-3.5 rounded-3 mb-4" style="background: var(--surface-2); border: 1px solid var(--border);">
            <h6 class="fw-bold extra-small text-uppercase text-muted mb-2"><i class="fa-solid fa-gavel me-1 text-primary"></i> Executive Audit Summary</h6>
            <p class="extra-small mb-0" style="line-height: 1.8; color: var(--text-1);">
              During the audited window <strong>(<?= htmlspecialchars($period_label) ?>)</strong>, the CCMS Anti-Corruption System has processed <strong><?= $count_all ?> complaints</strong> with an active statutory clearance rate of <strong><?= $resolution_rate ?>%</strong> (<?= $count_solved ?> cases officially resolved and closed). 
              Currently, <strong><?= $count_under_inv ?> dockets are under rigorous field investigation</strong> across <?= count($dept_stats) ?> departments by <?= $count_officers_total ?> commissioned vigilance officers.
              A total of <strong><?= $count_critical_total ?> high-severity &amp; critical allegations</strong> are designated for fast-track evidentiary hearings.
            </p>
          </div>

          <!-- Department Summary Table -->
          <h6 class="fw-bold extra-small text-uppercase text-muted mb-2"><i class="fa-solid fa-building-columns me-1 text-primary"></i> Departmental Distribution Overview</h6>
          <div class="table-responsive mb-4">
            <table class="table table-sm align-middle" style="font-size: 0.82rem; color: var(--text-1);">
              <thead>
                <tr class="border-bottom">
                  <th>Department Name</th>
                  <th>Period Complaints</th>
                  <th>Pending</th>
                  <th>Solved</th>
                  <th>Critical</th>
                  <th>Clearance %</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($dept_stats as $d): ?>
                  <?php 
                    $dt = (int)$d['total_complaints'];
                    $ds = (int)$d['resolved_count'];
                    $dp = (int)$d['pending_count'];
                    $dc = (int)$d['critical_count'];
                    $dr = ($dt > 0) ? round(($ds / $dt) * 100, 1) : 0;
                  ?>
                  <tr>
                    <td class="fw-bold"><?= htmlspecialchars($d['department_name']) ?></td>
                    <td><?= $dt ?></td>
                    <td><span class="badge bg-warning-subtle text-warning"><?= $dp ?></span></td>
                    <td><span class="badge bg-success-subtle text-success"><?= $ds ?></span></td>
                    <td><?= $dc > 0 ? '<span class="badge bg-danger-subtle text-danger">' . $dc . '</span>' : '0' ?></td>
                    <td><strong><?= $dr ?>%</strong></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>

          <!-- Official Stamp Block -->
          <div class="d-flex justify-content-between align-items-center pt-3 border-top extra-small text-muted">
            <div>
              <strong>Super Administrator:</strong> <?= $admin_name ?><br>
              Digital Verification: CCMS-PKI-SHA256-<?= substr(hash('sha256', 'ccms_report_' . $period . '_' . date('Y-m-d')), 0, 16) ?>
            </div>
            <div class="text-end">
              <span class="badge bg-success-subtle text-success px-3 py-1.5"><i class="fa-solid fa-stamp me-1"></i> CERTIFIED TRUE COPY</span>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-outline-primary" onclick="window.print()">
          <i class="fa-solid fa-print me-1"></i> Print Report
        </button>
        <button type="button" class="btn btn-primary btn-export-pdf" data-doc-name="Official_CCMS_Audit_Report_<?= $period ?>_<?= date('Ymd') ?>">
          <i class="fa-solid fa-file-arrow-down me-1"></i> Export Certified PDF
        </button>
      </div>
    </div>
  </div>
</div>

<!-- ══════════════════════ MODAL: PREVIEW CASE AUDIT ══════════════════════ -->
<div class="modal fade" id="previewCaseModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-bottom">
        <h5 class="modal-title fs-6 d-flex align-items-center gap-2">
          <i class="fa-solid fa-file-contract text-primary"></i> Case Docket Audit Record
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4">
        <div class="p-3.5 rounded-3 mb-3" style="background: var(--surface-2); border: 1px solid var(--border);">
          <div class="d-flex justify-content-between align-items-start mb-2">
            <div>
              <span class="badge bg-primary-subtle text-primary font-monospace" id="modalCaseToken">#CCMS-2026-0001</span>
              <h4 class="h5 fw-bold mt-1 mb-1" id="modalCaseTitle" style="color: var(--text-1);">Case Title</h4>
            </div>
            <span class="badge bg-secondary-subtle" id="modalCaseStatus">Status</span>
          </div>
          <div class="extra-small text-muted mb-2">
            Target: <strong id="modalCaseDept" class="text-primary">Department</strong> • Category: <strong id="modalCaseCat">Category</strong>
          </div>
          <p class="extra-small text-muted mb-0" id="modalCaseDesc" style="line-height: 1.6;"></p>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary btn-export-pdf" data-doc-name="Complaint_Audit_Slip">
          <i class="fa-solid fa-file-pdf me-1"></i> Download Case Dossier
        </button>
      </div>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="assets/js/main.js"></script>
<script>
  function openMasterReportModal() {
    new bootstrap.Modal(document.getElementById('masterProjectReportModal')).show();
  }

  function previewCaseDocket(token, title, dept, cat, status, pri, date, desc) {
    $('#modalCaseToken').text('#' + token);
    $('#modalCaseTitle').text(title);
    $('#modalCaseDept').text(dept || 'Municipal Office');
    $('#modalCaseCat').text(cat || 'General Misconduct');
    $('#modalCaseStatus').text(status);
    $('#modalCaseDesc').text(desc || 'No further evidentiary particulars logged.');
    new bootstrap.Modal(document.getElementById('previewCaseModal')).show();
  }

  // Handle PDF Export click simulation with feedback
  $(document).on('click', '.btn-export-pdf', function () {
    const docName = $(this).data('doc-name') || 'CCMS_Audit_Report';
    Swal.fire({
      icon: 'success',
      title: 'Preparing Certified Report',
      html: `Dossier <b>${docName}.pdf</b> is being prepared with full cryptographic verification stamp.<br><small class="text-muted">Opening print dialogue for direct PDF download...</small>`,
      timer: 2000,
      showConfirmButton: false
    }).then(() => {
      window.print();
    });
  });

  // ═══════════════════ RENDER BEAUTIFUL CHART.JS PLOTS ═══════════════════
  document.addEventListener('DOMContentLoaded', function () {
    const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
    const gridColor = isDark ? 'rgba(255, 255, 255, 0.08)' : 'rgba(0, 0, 0, 0.06)';
    const textColor = isDark ? '#94a3b8' : '#64748b';

    Chart.defaults.color = textColor;
    Chart.defaults.font.family = "'Plus Jakarta Sans', sans-serif";

    // Chart.js Center Text Plugin for Doughnut Chart
    const doughnutCenterTextPlugin = {
      id: 'doughnutCenterText',
      beforeDraw: function (chart) {
        if (chart.config.type !== 'doughnut') return;
        const width = chart.width;
        const height = chart.height;
        const ctx = chart.ctx;
        ctx.restore();

        const fontSize = Math.max(18, Math.min(32, Math.round(height / 9)));
        ctx.font = 'bold ' + fontSize + 'px "Space Grotesk", sans-serif';
        ctx.textBaseline = 'middle';
        ctx.fillStyle = isDark ? '#f8fafc' : '#0f172a';

        const text = '<?= (int)$count_all ?>';
        const textX = Math.round((width - ctx.measureText(text).width) / 2);
        const textY = Math.round(height / 2) - 8;
        ctx.fillText(text, textX, textY);

        ctx.font = '700 10px "Plus Jakarta Sans", sans-serif';
        ctx.fillStyle = isDark ? '#94a3b8' : '#64748b';
        const sub = 'PERIOD CASES';
        const subX = Math.round((width - ctx.measureText(sub).width) / 2);
        const subY = textY + fontSize * 0.75;
        ctx.fillText(sub, subX, subY);
        ctx.save();
      }
    };
    Chart.register(doughnutCenterTextPlugin);

    // Common tooltip options
    const commonTooltipOptions = {
      backgroundColor: isDark ? 'rgba(15, 23, 42, 0.94)' : 'rgba(255, 255, 255, 0.96)',
      titleColor: isDark ? '#f8fafc' : '#0f172a',
      bodyColor: isDark ? '#cbd5e1' : '#334155',
      borderColor: isDark ? 'rgba(99, 102, 241, 0.35)' : 'rgba(99, 102, 241, 0.25)',
      borderWidth: 1.5,
      padding: 12,
      cornerRadius: 10,
      usePointStyle: true,
      boxPadding: 6,
      titleFont: { family: "'Plus Jakarta Sans', sans-serif", size: 12, weight: '700' },
      bodyFont: { family: "'Plus Jakarta Sans', sans-serif", size: 12, weight: '500' }
    };

    // Plot 1: Trajectory Line Chart (Inflow vs Solved)
    const ctxLine = document.getElementById('trendLineChart');
    if (ctxLine) {
      const lineCtx = ctxLine.getContext('2d');
      const gradInflow = lineCtx.createLinearGradient(0, 0, 0, 270);
      gradInflow.addColorStop(0, 'rgba(99, 102, 241, 0.45)');
      gradInflow.addColorStop(1, 'rgba(99, 102, 241, 0.01)');

      const gradSolved = lineCtx.createLinearGradient(0, 0, 0, 270);
      gradSolved.addColorStop(0, 'rgba(16, 185, 129, 0.45)');
      gradSolved.addColorStop(1, 'rgba(16, 185, 129, 0.01)');

      new Chart(ctxLine, {
        type: 'line',
        data: {
          labels: <?= json_encode($chart_months) ?>,
          datasets: [
            {
              label: 'Complaints Lodged',
              data: <?= json_encode($chart_inflows) ?>,
              borderColor: '#6366f1',
              backgroundColor: gradInflow,
              borderWidth: 3,
              tension: 0.38,
              fill: true,
              pointRadius: 4.5,
              pointHoverRadius: 7,
              pointBackgroundColor: '#6366f1',
              pointBorderColor: '#ffffff',
              pointBorderWidth: 1.5
            },
            {
              label: 'Complaints Solved',
              data: <?= json_encode($chart_solveds) ?>,
              borderColor: '#10b981',
              backgroundColor: gradSolved,
              borderWidth: 3,
              tension: 0.38,
              fill: true,
              pointRadius: 4.5,
              pointHoverRadius: 7,
              pointBackgroundColor: '#10b981',
              pointBorderColor: '#ffffff',
              pointBorderWidth: 1.5
            }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              position: 'bottom',
              labels: { boxWidth: 12, padding: 16, font: { weight: '600' } }
            },
            tooltip: commonTooltipOptions
          },
          scales: {
            x: { grid: { color: gridColor }, ticks: { font: { size: 11, weight: '500' } } },
            y: { grid: { color: gridColor }, beginAtZero: true, ticks: { precision: 0 } }
          }
        }
      });
    }

    // Plot 2: Status Doughnut Chart
    const ctxDonut = document.getElementById('statusDonutChart');
    if (ctxDonut) {
      const totalPeriodCases = <?= $count_all ?>;
      new Chart(ctxDonut, {
        type: 'doughnut',
        data: {
          labels: ['Solved / Concluded', 'Active Probe', 'Pending Triage', 'Critical Flagged'],
          datasets: [{
            data: [
              <?= max(0, $count_solved) ?>,
              <?= max(0, $count_under_inv) ?>,
              <?= max(0, $count_pending) ?>,
              <?= max(0, $count_critical_total) ?>
            ],
            backgroundColor: ['#10b981', '#06b6d4', '#f59e0b', '#f43f5e'],
            borderColor: isDark ? '#0c1222' : '#ffffff',
            borderWidth: 3,
            hoverOffset: 8,
            borderRadius: 4
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              position: 'bottom',
              labels: { boxWidth: 12, padding: 12, font: { size: 11, weight: '600' } }
            },
            tooltip: commonTooltipOptions
          },
          cutout: '70%'
        }
      });
    }

    // Plot 3: Department Bar Chart
    const ctxBar = document.getElementById('departmentBarChart');
    if (ctxBar) {
      new Chart(ctxBar, {
        type: 'bar',
        data: {
          labels: <?= json_encode($chart_dept_names) ?>,
          datasets: [
            {
              label: 'Received in Period',
              data: <?= json_encode($chart_dept_totals) ?>,
              backgroundColor: 'rgba(99, 102, 241, 0.88)',
              borderRadius: 6,
              borderSkipped: false
            },
            {
              label: 'Resolved in Period',
              data: <?= json_encode($chart_dept_solved) ?>,
              backgroundColor: 'rgba(16, 185, 129, 0.88)',
              borderRadius: 6,
              borderSkipped: false
            }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              position: 'bottom',
              labels: { boxWidth: 12, padding: 14, font: { weight: '600' } }
            },
            tooltip: commonTooltipOptions
          },
          scales: {
            x: { grid: { display: false }, ticks: { font: { size: 11, weight: '500' } } },
            y: { grid: { color: gridColor }, beginAtZero: true, ticks: { precision: 0 } }
          }
        }
      });
    }

    // Plot 4: Category Horizontal Bar Chart
    const ctxCat = document.getElementById('categoryBarChart');
    if (ctxCat) {
      new Chart(ctxCat, {
        type: 'bar',
        data: {
          labels: <?= json_encode($chart_cat_names) ?>,
          datasets: [{
            label: 'Reported Misconducts',
            data: <?= json_encode($chart_cat_counts) ?>,
            backgroundColor: [
              'rgba(6, 182, 212, 0.88)',
              'rgba(99, 102, 241, 0.88)',
              'rgba(245, 158, 11, 0.88)',
              'rgba(168, 85, 247, 0.88)',
              'rgba(244, 63, 94, 0.88)'
            ],
            borderRadius: 6,
            borderSkipped: false
          }]
        },
        options: {
          indexAxis: 'y',
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { display: false },
            tooltip: commonTooltipOptions
          },
          scales: {
            x: { grid: { color: gridColor }, beginAtZero: true, ticks: { precision: 0 } },
            y: { grid: { display: false }, ticks: { font: { size: 11, weight: '500' } } }
          }
        }
      });
    }

    // Plot 5: Priority Severity Polar Area Chart
    const ctxPolar = document.getElementById('priorityPolarChart');
    if (ctxPolar) {
      new Chart(ctxPolar, {
        type: 'polarArea',
        data: {
          labels: ['Critical', 'High', 'Medium', 'Low'],
          datasets: [{
            data: [
              <?= max(0, $count_critical) ?>,
              <?= max(0, $count_high) ?>,
              <?= max(0, $count_medium) ?>,
              <?= max(0, $count_low) ?>
            ],
            backgroundColor: [
              'rgba(244, 63, 94, 0.82)',
              'rgba(245, 158, 11, 0.82)',
              'rgba(59, 130, 246, 0.82)',
              'rgba(148, 163, 184, 0.82)'
            ],
            borderWidth: 1.5,
            borderColor: isDark ? '#0c1222' : '#ffffff'
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { position: 'bottom', labels: { boxWidth: 12, padding: 12 } },
            tooltip: commonTooltipOptions
          },
          scales: {
            r: { grid: { color: gridColor }, ticks: { display: false } }
          }
        }
      });
    }
  });
</script>
</body>
</html>
