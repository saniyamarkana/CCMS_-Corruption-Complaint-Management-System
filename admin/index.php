<?php
require_once '../db.php';
$admin_name = htmlspecialchars($_SESSION['admin_name'] ?? $_SESSION['name'] ?? 'Super Administrator');

$success_msg = '';
$error_msg = '';

// ═══════════════════ 1. DELETE ACTION (GET METHOD) ═══════════════════
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $del_id = intval($_GET['id']);
    if ($del_id > 0) {
        $del_stmt = mysqli_prepare($conn, "DELETE FROM complaints WHERE complaint_id = ?");
        if ($del_stmt) {
            mysqli_stmt_bind_param($del_stmt, "i", $del_id);
            if (mysqli_stmt_execute($del_stmt)) {
                $success_msg = "Complaint #CCMS-{$del_id} deleted successfully via GET request.";
            } else {
                $error_msg = "Failed to delete complaint: " . mysqli_error($conn);
            }
            mysqli_stmt_close($del_stmt);
        }
    }
}

// ═══════════════════ 2. DYNAMIC KPIS & METRICS FROM DB ═══════════════════
$count_all = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM complaints"))[0] ?? 0;
$count_pending = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM complaints WHERE status IN ('Submitted', 'Pending Verification')"))[0] ?? 0;
$count_active = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM complaints WHERE status = 'Under Investigation'"))[0] ?? 0;
$count_resolved = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM complaints WHERE status IN ('Investigation Completed', 'Closed')"))[0] ?? 0;

$count_officers = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM officers"))[0] ?? 0;
$count_officers_active = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM officers WHERE status = 'Active'"))[0] ?? 0;
$count_depts = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM departments"))[0] ?? 0;
$count_citizens = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM users WHERE role = 'Citizen'"))[0] ?? 0;

// Query priority breakdown for donut chart
$crit_count = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM complaints WHERE priority = 'Critical'"))[0] ?? 0;
$high_count = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM complaints WHERE priority = 'High'"))[0] ?? 0;
$med_count = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM complaints WHERE priority = 'Medium'"))[0] ?? 0;
$low_count = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM complaints WHERE priority = 'Low'"))[0] ?? 0;

// Query recent complaints
$recent_res = mysqli_query($conn, "
    SELECT c.*, u.name AS complainant_name, cat.category_name, o.name AS officer_name, d.department_name
    FROM complaints c
    LEFT JOIN users u ON c.user_id = u.user_id
    LEFT JOIN categories cat ON c.category_id = cat.category_id
    LEFT JOIN officers o ON c.assigned_officer = o.officer_id
    LEFT JOIN departments d ON o.department_id = d.department_id
    ORDER BY c.complaint_id DESC LIMIT 8
");
$recent_complaints = [];
if ($recent_res) {
    while ($r = mysqli_fetch_assoc($recent_res)) {
        $recent_complaints[] = $r;
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Executive Command Dashboard — CCMS Admin Panel</title>
  <meta name="description" content="Corruption Complaint Management System — Executive Admin Command Center">

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&family=Space+Grotesk:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">

  <!-- Bootstrap 5.3 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome 6 -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <!-- SweetAlert2 -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

  <!-- CCMS Custom CSS -->
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

  <!-- Ambient Visual Glow Blobs -->
  <div class="ambient-glow-1"></div>
  <div class="ambient-glow-2"></div>
  <div class="ambient-glow-3"></div>
  <div class="grid-overlay"></div>

<div id="wrapper">

  <!-- ═══════════════════ SIDEBAR ═══════════════════ -->
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
      <a href="index.php" class="sb-link active">
        <div class="icon-wrap"><i class="fa-solid fa-gauge-high"></i></div>
        <span>Dashboard</span>
      </a>

      <a href="manage-complaints.php" class="sb-link">
        <div class="icon-wrap"><i class="fa-solid fa-folder-open"></i></div>
        <span>Manage Complaints</span>
        <span class="sb-badge"><?= $count_all ?></span>
      </a>

      <a href="assign-complaints.php" class="sb-link">
        <div class="icon-wrap"><i class="fa-solid fa-user-tag"></i></div>
        <span>Assign Complaints</span>
        <span class="sb-badge amber"><?= $count_pending ?></span>
      </a>

      <div class="sb-section-label">Personnel</div>
      <a href="manage-users.php" class="sb-link">
        <div class="icon-wrap"><i class="fa-solid fa-users"></i></div>
        <span>Manage Users</span>
      </a>

      <a href="manage-officers.php" class="sb-link">
        <div class="icon-wrap"><i class="fa-solid fa-user-shield"></i></div>
        <span>Manage Officers</span>
      </a>

      <div class="sb-section-label">System Masters</div>
      <a href="manage-departments.php" class="sb-link">
        <div class="icon-wrap"><i class="fa-solid fa-building-columns"></i></div>
        <span>Departments</span>
      </a>

      <a href="manage-categories.php" class="sb-link">
        <div class="icon-wrap"><i class="fa-solid fa-tags"></i></div>
        <span>Categories</span>
      </a>

      <div class="sb-section-label">Intelligence</div>
      <a href="reports.php" class="sb-link">
        <div class="icon-wrap"><i class="fa-solid fa-file-chart-column"></i></div>
        <span>Reports</span>
      </a>
      <a href="analytics.php" class="sb-link">
        <div class="icon-wrap"><i class="fa-solid fa-chart-line"></i></div>
        <span>Analytics</span>
      </a>
      <a href="activity-logs.php" class="sb-link">
        <div class="icon-wrap"><i class="fa-solid fa-clock-rotate-left"></i></div>
        <span>Activity Logs</span>
      </a>
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

  <!-- ═══════════════════ MAIN CONTENT ═══════════════════ -->
  <div id="main-content">

    <!-- TOP NAVBAR -->
    <nav class="top-navbar">
      <div class="d-flex align-items-center gap-3">
        <button class="btn-toggle" id="sidebarToggle">
          <i class="fa-solid fa-bars-staggered"></i>
        </button>

        <!-- SEARCH VIA GET METHOD -->
        <form method="GET" action="manage-complaints.php" class="search-wrap d-none d-md-flex align-items-center m-0">
          <i class="fa-solid fa-magnifying-glass"></i>
          <input type="text" name="search" id="tableSearch" placeholder="Search case ID, target dept, or category…">
        </form>
      </div>

      <div class="nav-actions">
        <!-- System Status -->
        <div class="online-chip d-none d-xl-flex">
          <span class="online-dot"></span>
          System Live · Sentinel Active
        </div>

        <!-- Theme Toggle -->
        <button class="nav-icon-btn" id="themeToggle" title="Toggle Theme">
          <i class="fa-solid fa-moon" id="themeIcon"></i>
        </button>

        <!-- Profile -->
        <div class="dropdown">
          <div class="nav-profile-btn dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
            <img src="https://images.unsplash.com/photo-1560250097-0b93528c311a?auto=format&fit=crop&w=80&q=80" class="nav-avatar" alt="Admin">
            <div class="d-none d-md-block">
              <div class="nav-profile-name"><?= $admin_name ?></div>
              <div class="nav-profile-role">Super Administrator</div>
            </div>
          </div>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="activity-logs.php"><i class="fa-solid fa-clock-rotate-left"></i> My Logs</a></li>
            <li><div class="dropdown-divider"></div></li>
            <li><a class="dropdown-item text-danger" href="./login.php"><i class="fa-solid fa-power-off"></i> Logout</a></li>
          </ul>
        </div>
      </div>
    </nav>

    <!-- ═══════════════════ CONTENT BODY ═══════════════════ -->
    <main class="content-body">
      <?php if (!empty($success_msg)): ?>
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 mb-3" role="alert">
          <i class="fa-solid fa-circle-check fs-5"></i>
          <div><?= htmlspecialchars($success_msg) ?></div>
          <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>

      <?php if (!empty($error_msg)): ?>
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 mb-3" role="alert">
          <i class="fa-solid fa-triangle-exclamation fs-5"></i>
          <div><?= htmlspecialchars($error_msg) ?></div>
          <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>

      <!-- Page Header -->
      <div class="page-header">
        <div>
          <div class="page-breadcrumb">
            <a href="index.php"><i class="fa-solid fa-house"></i></a>
            <i class="fa-solid fa-chevron-right"></i>
            <span>Executive Command Center</span>
          </div>
          <h1 class="page-title">Anti-Corruption Command Center</h1>
          <p class="page-subtitle">Fully dynamic oversight, real-time case triage, GET-based operations, and live database metrics.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
          <button class="btn btn-ghost btn-export-pdf" data-doc-name="Executive_Dashboard_Summary">
            <i class="fa-solid fa-file-pdf text-danger"></i> Export PDF Summary
          </button>
          <a href="manage-complaints.php" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> Lodge New Complaint
          </a>
        </div>
      </div>

      <!-- DYNAMIC KPI CARDS FROM DATABASE -->
      <div class="kpi-grid">
        <div class="kpi-card indigo">
          <div class="kpi-icon"><i class="fa-solid fa-folder-open"></i></div>
          <div class="kpi-value counter-value"><?= $count_all ?></div>
          <div class="kpi-label">Total Dockets Ingested</div>
          <div class="kpi-trend up"><i class="fa-solid fa-database"></i> Live Database</div>
        </div>

        <div class="kpi-card amber">
          <div class="kpi-icon"><i class="fa-solid fa-hourglass-half"></i></div>
          <div class="kpi-value counter-value"><?= $count_pending ?></div>
          <div class="kpi-label">Pending / Triage Queue</div>
          <div class="kpi-trend down"><i class="fa-solid fa-clock"></i> Action Needed</div>
        </div>

        <div class="kpi-card rose">
          <div class="kpi-icon"><i class="fa-solid fa-magnifying-glass-chart"></i></div>
          <div class="kpi-value counter-value"><?= $count_active ?></div>
          <div class="kpi-label">Active Field Probes</div>
          <div class="kpi-trend up"><i class="fa-solid fa-bolt"></i> High Priority</div>
        </div>

        <div class="kpi-card emerald">
          <div class="kpi-icon"><i class="fa-solid fa-circle-check"></i></div>
          <div class="kpi-value counter-value"><?= $count_resolved ?></div>
          <div class="kpi-label">Successfully Resolved</div>
          <div class="kpi-trend up"><i class="fa-solid fa-shield-halved"></i> Closed Cases</div>
        </div>
      </div>

      <!-- Secondary Quick Stats Row -->
      <div class="row g-3 mb-4">
        <div class="col-md-4">
          <div class="kpi-card cyan" style="padding:16px 20px;">
            <div class="d-flex align-items-center gap-3">
              <div class="kpi-icon mb-0" style="background:rgba(6,182,212,.15);color:#06b6d4;">
                <i class="fa-solid fa-user-shield"></i>
              </div>
              <div>
                <div class="kpi-value" style="font-size:1.5rem;"><?= $count_officers ?> Officers</div>
                <div class="kpi-label"><?= $count_officers_active ?> Active On Duty</div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="kpi-card violet" style="padding:16px 20px;">
            <div class="d-flex align-items-center gap-3">
              <div class="kpi-icon mb-0" style="background:rgba(139,92,246,.15);color:#8b5cf6;">
                <i class="fa-solid fa-building-flag"></i>
              </div>
              <div>
                <div class="kpi-value" style="font-size:1.5rem;"><?= $count_depts ?> Departments</div>
                <div class="kpi-label">Monitored Sectors</div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="kpi-card indigo" style="padding:16px 20px;">
            <div class="d-flex align-items-center gap-3">
              <div class="kpi-icon mb-0">
                <i class="fa-solid fa-users"></i>
              </div>
              <div>
                <div class="kpi-value counter-value" style="font-size:1.5rem;"><?= $count_citizens ?></div>
                <div class="kpi-label">Registered Citizens</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Charts Row -->
      <div class="row g-3 mb-4">
        <div class="col-lg-8">
          <div class="card-box h-100">
            <div class="card-box-head">
              <div class="card-box-title">
                <div class="title-icon"><i class="fa-solid fa-chart-line"></i></div>
                Complaint Volume &amp; Resolution Trends (2026)
              </div>
              <div class="d-flex align-items-center gap-2">
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace" style="font-size:0.75rem;">
                  <i class="fa-solid fa-circle text-success me-1" style="font-size:0.5rem;"></i> Live DB
                </span>
              </div>
            </div>
            <div class="card-box-body" style="padding: 16px 20px 20px;">
              <div class="chart-container-rel">
                <canvas id="trendChart"></canvas>
              </div>
            </div>
          </div>
        </div>

        <div class="col-lg-4">
          <div class="card-box h-100">
            <div class="card-box-head">
              <div class="card-box-title">
                <div class="title-icon"><i class="fa-solid fa-chart-pie"></i></div>
                Severity Classification (DB)
              </div>
              <span class="extra-small text-muted font-monospace fw-bold"><?= $count_all ?> Total</span>
            </div>
            <div class="card-box-body d-flex flex-column align-items-center justify-content-center" style="padding: 16px 20px 20px;">
              <div class="chart-container-donut">
                <canvas id="severityChart"></canvas>
                <div class="donut-center-info">
                  <div class="donut-center-num"><?= $count_all ?></div>
                  <div class="donut-center-txt">Dockets</div>
                </div>
              </div>
              <div class="d-flex flex-wrap gap-2 mt-3 justify-content-center">
                <div class="d-flex align-items-center gap-1 extra-small fw-bold"><span style="width:10px;height:10px;border-radius:3px;background:#f43f5e;display:inline-block;"></span> Critical (<?= $crit_count ?>)</div>
                <div class="d-flex align-items-center gap-1 extra-small fw-bold"><span style="width:10px;height:10px;border-radius:3px;background:#f59e0b;display:inline-block;"></span> High (<?= $high_count ?>)</div>
                <div class="d-flex align-items-center gap-1 extra-small fw-bold"><span style="width:10px;height:10px;border-radius:3px;background:#0ea5e9;display:inline-block;"></span> Medium (<?= $med_count ?>)</div>
                <div class="d-flex align-items-center gap-1 extra-small fw-bold"><span style="width:10px;height:10px;border-radius:3px;background:#10b981;display:inline-block;"></span> Low (<?= $low_count ?>)</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- DYNAMIC RECENT COMPLAINTS TABLE (FROM DB) -->
      <div class="card-box">
        <div class="card-box-head">
          <div class="card-box-title">
            <div class="title-icon"><i class="fa-solid fa-list-check"></i></div>
            Live Complaints Triage Queue (Recent Cases)
          </div>
          <div class="d-flex gap-2">
            <a href="manage-complaints.php" class="btn btn-primary btn-sm">View Full Ledger</a>
          </div>
        </div>

        <div class="tbl-wrap">
          <table class="tbl" id="complaintsTable">
            <thead>
              <tr>
                <th>Tracking ID</th>
                <th>Complainant</th>
                <th>Title &amp; Location</th>
                <th>Category</th>
                <th>Priority</th>
                <th>Assigned Officer</th>
                <th>Status</th>
                <th class="text-end no-sort">Action (GET Method)</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($recent_complaints)): ?>
                <?php foreach ($recent_complaints as $c): 
                  $cid = $c['complaint_id'];
                  $badge_p = match($c['priority']) {
                    'Critical' => 'chip-critical',
                    'High' => 'chip-high',
                    'Medium' => 'chip-medium',
                    default => 'chip-low'
                  };
                  $badge_s = match($c['status']) {
                    'Submitted' => 'pill-new',
                    'Pending Verification' => 'pill-pending',
                    'Assigned' => 'pill-assigned',
                    'Under Investigation' => 'pill-investigating',
                    'Investigation Completed', 'Closed' => 'pill-active',
                    default => 'pill-new'
                  };
                ?>
                <tr>
                  <td>
                    <a href="manage-complaints.php?search=<?= $cid ?>" class="fw-bold" style="color:var(--indigo-600); font-family:var(--font-mono);">#CCMS-<?= $cid ?></a>
                    <div class="extra-small text-muted"><?= date('M d, Y', strtotime($c['created_at'])) ?></div>
                  </td>
                  <td>
                    <div class="fw-semibold" style="font-size:.82rem;"><?= htmlspecialchars($c['complainant_name'] ?? 'Anonymous') ?></div>
                    <div class="extra-small text-muted"><?= htmlspecialchars($c['location'] ?? 'N/A') ?></div>
                  </td>
                  <td style="max-width: 220px;">
                    <div class="fw-semibold text-truncate" style="font-size:.82rem;" title="<?= htmlspecialchars($c['title']) ?>">
                      <?= htmlspecialchars($c['title']) ?>
                    </div>
                  </td>
                  <td style="font-size:.82rem;"><span class="badge bg-light text-dark border"><?= htmlspecialchars($c['category_name'] ?? 'General') ?></span></td>
                  <td><span class="<?= $badge_p ?>"><?= htmlspecialchars($c['priority']) ?></span></td>
                  <td>
                    <div class="fw-semibold" style="font-size:.82rem;"><?= htmlspecialchars($c['officer_name'] ?? 'Unallocated') ?></div>
                  </td>
                  <td><span class="pill <?= $badge_s ?> pill-live"><?= htmlspecialchars($c['status']) ?></span></td>
                  <td class="text-end">
                    <div class="d-inline-flex gap-1">
                      <button class="btn btn-ghost btn-icon btn-sm" onclick="showDashboardCaseModal('#CCMS-<?= $cid ?>', '<?= addslashes($c['location'] ?? 'N/A') ?>', '<?= addslashes($c['category_name'] ?? '') ?>', '<?= $c['priority'] ?>', '<?= addslashes($c['complainant_name'] ?? 'Anonymous') ?>', '<?= addslashes($c['description']) ?>')" title="View Case"><i class="fa-solid fa-eye text-primary"></i></button>
                      <a href="assign-complaints.php?search=<?= $cid ?>" class="btn btn-primary btn-icon btn-sm" title="Assign Officer"><i class="fa-solid fa-user-plus"></i></a>
                      <!-- DELETE VIA GET METHOD -->
                      <a href="index.php?action=delete&id=<?= $cid ?>" onclick="return confirm('Delete complaint #CCMS-<?= $cid ?> via GET?');" class="btn btn-ghost btn-icon btn-sm text-danger" title="Delete via GET"><i class="fa-solid fa-trash"></i></a>
                    </div>
                  </td>
                </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="8" class="text-center py-4 text-muted">No complaints currently in database</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </main>
  </div>
</div>

<!-- ═══════════════════ MODAL: VIEW COMPLAINT ═══════════════════ -->
<div class="modal fade" id="viewComplaintModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <div>
          <div class="d-flex align-items-center gap-2 mb-1">
            <span class="chip-critical" id="modalCasePriority">Critical</span>
          </div>
          <h5 class="modal-title" id="modalCaseTitle">#CCMS-0000 — Case File</h5>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" style="padding:24px;">
        <div class="row g-3 mb-3">
          <div class="col-md-6">
            <div style="background:var(--bg);border:1px solid var(--border);border-radius:12px;padding:14px;">
              <div class="extra-small fw-bold text-muted mb-1">Complainant</div>
              <div class="fw-bold" id="modalCaseComplainant">Citizen</div>
            </div>
          </div>
          <div class="col-md-6">
            <div style="background:var(--bg);border:1px solid var(--border);border-radius:12px;padding:14px;">
              <div class="extra-small fw-bold text-muted mb-1">Jurisdiction / Location</div>
              <div class="fw-bold" id="modalCaseDept">Location</div>
            </div>
          </div>
        </div>

        <div class="mb-3">
          <div class="extra-small fw-bold text-muted mb-1">Allegation Statement</div>
          <p class="extra-small text-muted" id="modalCaseDesc" style="line-height:1.6;"></p>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Close</button>
        <a href="assign-complaints.php" class="btn btn-primary"><i class="fa-solid fa-user-plus me-1"></i> Assign Officer</a>
      </div>
    </div>
  </div>
</div>

<!-- ═══════════════════ SCRIPTS ═══════════════════ -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="assets/js/main.js"></script>

<script>
function showDashboardCaseModal(id, dept, cat, priority, complainant, desc) {
  $('#modalCaseTitle').text(`${id} — ${cat}`);
  $('#modalCasePriority').text(priority);
  $('#modalCaseComplainant').text(complainant);
  $('#modalCaseDept').text(dept);
  $('#modalCaseDesc').text(desc);
  const modal = new bootstrap.Modal(document.getElementById('viewComplaintModal'));
  modal.show();
}

document.addEventListener('DOMContentLoaded', function() {
  const baseFont = { family: 'Plus Jakarta Sans', size: 12, weight: '600' };
  const trendCanvas = document.getElementById('trendChart');
  const severityCanvas = document.getElementById('severityChart');

  if (trendCanvas) {
    const ctx = trendCanvas.getContext('2d');
    const gradPrimary = ctx.createLinearGradient(0, 0, 0, 280);
    gradPrimary.addColorStop(0, 'rgba(99, 102, 241, 0.35)');
    gradPrimary.addColorStop(1, 'rgba(99, 102, 241, 0.00)');

    new Chart(ctx, {
      type: 'line',
      data: {
        labels: ['May', 'Jun', 'Jul', 'Aug', 'Sep (Live)'],
        datasets: [{
          label: 'Complaints Ingested',
          data: [2, 3, 5, 8, <?= $count_all ?>],
          borderColor: '#6366f1',
          backgroundColor: gradPrimary,
          borderWidth: 3,
          fill: true,
          tension: 0.38,
          pointBackgroundColor: '#6366f1'
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
          x: { grid: { display: false } },
          y: { beginAtZero: true, grid: { color: 'rgba(148, 163, 184, 0.1)' } }
        }
      }
    });
  }

  if (severityCanvas) {
    new Chart(severityCanvas.getContext('2d'), {
      type: 'doughnut',
      data: {
        labels: ['Critical', 'High', 'Medium', 'Low'],
        datasets: [{
          data: [<?= max(1, $crit_count) ?>, <?= max(1, $high_count) ?>, <?= max(1, $med_count) ?>, <?= max(1, $low_count) ?>],
          backgroundColor: ['#f43f5e', '#f59e0b', '#0ea5e9', '#10b981'],
          borderWidth: 0
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '74%',
        plugins: { legend: { display: false } }
      }
    });
  }
});
</script>
</body>
</html>
