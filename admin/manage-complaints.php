<?php
require_once '../db.php';

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
                $success_msg = "Complaint docket #CCMS-{$del_id} has been permanently deleted.";
            } else {
                $error_msg = "Failed to delete complaint: " . mysqli_error($conn);
            }
            mysqli_stmt_close($del_stmt);
        }
    }
}

// ═══════════════════ 2. UPDATE STATUS ACTION (GET METHOD) ═══════════════════
if (isset($_GET['action']) && $_GET['action'] === 'update_status' && isset($_GET['id'])) {
    $up_id = intval($_GET['id']);
    $new_status = trim($_GET['status'] ?? '');
    $remarks = trim($_GET['remarks'] ?? '');

    $allowed_statuses = [
        'Submitted', 'Pending Verification', 'Verified', 'Assigned',
        'Under Investigation', 'Waiting for Evidence', 'Investigation Completed',
        'Closed', 'Rejected'
    ];

    if ($up_id > 0 && in_array($new_status, $allowed_statuses)) {
        $up_stmt = mysqli_prepare($conn, "UPDATE complaints SET status = ? WHERE complaint_id = ?");
        if ($up_stmt) {
            mysqli_stmt_bind_param($up_stmt, "si", $new_status, $up_id);
            if (mysqli_stmt_execute($up_stmt)) {
                $success_msg = "Status for Case #CCMS-{$up_id} updated to '{$new_status}'.";
                // Optionally record remark in investigation table
                if (!empty($remarks)) {
                    $off_res = mysqli_query($conn, "SELECT assigned_officer FROM complaints WHERE complaint_id = {$up_id}");
                    $off_row = mysqli_fetch_assoc($off_res);
                    $officer_id = $off_row['assigned_officer'] ? intval($off_row['assigned_officer']) : 5; // default officer if none
                    $inv_stmt = mysqli_prepare($conn, "INSERT INTO investigation (complaint_id, officer_id, remarks, progress, updated_at) VALUES (?, ?, ?, 50, NOW())");
                    if ($inv_stmt) {
                        mysqli_stmt_bind_param($inv_stmt, "iis", $up_id, $officer_id, $remarks);
                        mysqli_stmt_execute($inv_stmt);
                        mysqli_stmt_close($inv_stmt);
                    }
                }
            } else {
                $error_msg = "Failed to update status: " . mysqli_error($conn);
            }
            mysqli_stmt_close($up_stmt);
        }
    } elseif (!empty($new_status)) {
        $error_msg = "Invalid status selected.";
    }
}

// ═══════════════════ 3. FULL UPDATE ACTION (GET METHOD) ═══════════════════
if (isset($_GET['action']) && $_GET['action'] === 'update' && isset($_GET['id'])) {
    $edit_id = intval($_GET['id']);
    $title = trim($_GET['title'] ?? '');
    $category_id = intval($_GET['category_id'] ?? 1);
    $priority = trim($_GET['priority'] ?? 'Medium');
    $status = trim($_GET['status'] ?? 'Submitted');
    $location = trim($_GET['location'] ?? '');
    $assigned_officer = !empty($_GET['assigned_officer']) ? intval($_GET['assigned_officer']) : NULL;
    $description = trim($_GET['description'] ?? '');

    if ($edit_id > 0 && !empty($title)) {
        if ($assigned_officer !== NULL) {
            $stmt = mysqli_prepare($conn, "UPDATE complaints SET title = ?, category_id = ?, priority = ?, status = ?, location = ?, assigned_officer = ?, description = ? WHERE complaint_id = ?");
            mysqli_stmt_bind_param($stmt, "sisssisi", $title, $category_id, $priority, $status, $location, $assigned_officer, $description, $edit_id);
        } else {
            $stmt = mysqli_prepare($conn, "UPDATE complaints SET title = ?, category_id = ?, priority = ?, status = ?, location = ?, assigned_officer = NULL, description = ? WHERE complaint_id = ?");
            mysqli_stmt_bind_param($stmt, "sissssi", $title, $category_id, $priority, $status, $location, $description, $edit_id);
        }
        if ($stmt && mysqli_stmt_execute($stmt)) {
            $success_msg = "Complaint #CCMS-{$edit_id} updated successfully via GET action!";
        } else {
            $error_msg = "Failed to update complaint: " . mysqli_error($conn);
        }
        if ($stmt) mysqli_stmt_close($stmt);
    }
}

// ═══════════════════ 4. ADD NEW COMPLAINT (POST OR GET) ═══════════════════
if (($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['lodge_complaint'])) || (isset($_GET['action']) && $_GET['action'] === 'lodge')) {
    $req = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : $_GET;
    $user_id = intval($req['user_id'] ?? 11);
    $cat_id = intval($req['category_id'] ?? 1);
    $title = trim($req['title'] ?? '');
    $description = trim($req['description'] ?? '');
    $location = trim($req['location'] ?? 'Headquarters Jurisdiction');
    $priority = trim($req['priority'] ?? 'Medium');
    $assigned_officer = !empty($req['assigned_officer']) ? intval($req['assigned_officer']) : NULL;

    if (!empty($title) && !empty($description)) {
        $stmt = mysqli_prepare($conn, "INSERT INTO complaints (user_id, category_id, title, description, location, priority, status, assigned_officer, created_at) VALUES (?, ?, ?, ?, ?, ?, 'Submitted', ?, NOW())");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "iissssi", $user_id, $cat_id, $title, $description, $location, $priority, $assigned_officer);
            if (mysqli_stmt_execute($stmt)) {
                $new_id = mysqli_insert_id($conn);
                $success_msg = "New complaint docket #CCMS-{$new_id} registered successfully!";
            } else {
                $error_msg = "Failed to register complaint: " . mysqli_error($conn);
            }
            mysqli_stmt_close($stmt);
        }
    } else {
        $error_msg = "Complaint title and particulars are mandatory.";
    }
}

// ═══════════════════ 5. SEARCHING & FILTERING (GET METHOD) ═══════════════════
$search = trim($_GET['search'] ?? '');
$filter_status = trim($_GET['status_filter'] ?? '');
$filter_priority = trim($_GET['priority_filter'] ?? '');
$filter_category = intval($_GET['category_filter'] ?? 0);

$query = "SELECT c.*, u.name AS complainant_name, u.email AS complainant_email, cat.category_name, o.name AS officer_name, o.designation AS officer_designation, d.department_name
          FROM complaints c
          LEFT JOIN users u ON c.user_id = u.user_id
          LEFT JOIN categories cat ON c.category_id = cat.category_id
          LEFT JOIN officers o ON c.assigned_officer = o.officer_id
          LEFT JOIN departments d ON o.department_id = d.department_id
          WHERE 1=1";

$params = [];
$types = "";

if (!empty($search)) {
    $search_pattern = "%{$search}%";
    $query .= " AND (c.complaint_id LIKE ? OR c.title LIKE ? OR c.description LIKE ? OR c.location LIKE ? OR u.name LIKE ? OR cat.category_name LIKE ? OR o.name LIKE ?)";
    $types .= "sssssss";
    $params[] = $search_pattern;
    $params[] = $search_pattern;
    $params[] = $search_pattern;
    $params[] = $search_pattern;
    $params[] = $search_pattern;
    $params[] = $search_pattern;
    $params[] = $search_pattern;
}

if (!empty($filter_status) && $filter_status !== 'all') {
    $query .= " AND c.status = ?";
    $types .= "s";
    $params[] = $filter_status;
}

if (!empty($filter_priority) && $filter_priority !== 'all') {
    $query .= " AND c.priority = ?";
    $types .= "s";
    $params[] = $filter_priority;
}

if ($filter_category > 0) {
    $query .= " AND c.category_id = ?";
    $types .= "i";
    $params[] = $filter_category;
}

$query .= " ORDER BY c.complaint_id DESC";

$stmt = mysqli_prepare($conn, $query);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$complaints_res = mysqli_stmt_get_result($stmt);
$complaints = [];
if ($complaints_res) {
    while ($row = mysqli_fetch_assoc($complaints_res)) {
        $complaints[] = $row;
    }
}
mysqli_stmt_close($stmt);

// ═══════════════════ 6. DYNAMIC KPI COUNTS FROM DATABASE ═══════════════════
$count_all = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM complaints"))[0] ?? 0;
$count_under_inv = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM complaints WHERE status = 'Under Investigation'"))[0] ?? 0;
$count_pending = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM complaints WHERE status IN ('Submitted', 'Pending Verification')"))[0] ?? 0;
$count_resolved = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM complaints WHERE status IN ('Investigation Completed', 'Closed')"))[0] ?? 0;

// Fetch categories for dropdowns
$categories_res = mysqli_query($conn, "SELECT * FROM categories ORDER BY category_name ASC");
$all_categories = [];
while ($cat_row = mysqli_fetch_assoc($categories_res)) {
    $all_categories[] = $cat_row;
}

// Fetch officers for dropdowns
$officers_res = mysqli_query($conn, "SELECT officer_id, name, designation FROM officers WHERE status = 'Active' ORDER BY name ASC");
$all_officers = [];
while ($off_row = mysqli_fetch_assoc($officers_res)) {
    $all_officers[] = $off_row;
}

// Fetch citizens for dropdowns
$users_res = mysqli_query($conn, "SELECT user_id, name, email FROM users ORDER BY name ASC");
$all_users = [];
while ($u_row = mysqli_fetch_assoc($users_res)) {
    $all_users[] = $u_row;
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Complaints — CCMS Admin</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&family=Space+Grotesk:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div id="wrapper">

  <!-- ══════════════════════ SIDEBAR ══════════════════════ -->
  <aside id="sidebar">
    <div class="sb-brand">
      <div class="sb-logo"><i class="fa-solid fa-shield-halved"></i></div>
      <div><div class="sb-title">CCMS</div><div class="sb-sub">Anti-Corruption</div></div>
    </div>
    <nav class="sb-nav">

      <div class="sb-section-label">Main Overview</div>
      <a href="index.php" class="sb-link"><div class="icon-wrap"><i class="fa-solid fa-gauge-high"></i></div><span>Dashboard</span></a>
      <a href="manage-complaints.php" class="sb-link active"><div class="icon-wrap"><i class="fa-solid fa-folder-open"></i></div><span>Manage Complaints</span><span class="sb-badge"><?= $count_all ?></span></a>
      <a href="assign-complaints.php" class="sb-link"><div class="icon-wrap"><i class="fa-solid fa-user-tag"></i></div><span>Assign Complaints</span><span class="sb-badge amber"><?= $count_pending ?></span></a>
      
      <div class="sb-section-label">Personnel</div>
      <a href="manage-users.php" class="sb-link"><div class="icon-wrap"><i class="fa-solid fa-users"></i></div><span>Manage Users</span></a>
      <a href="manage-officers.php" class="sb-link"><div class="icon-wrap"><i class="fa-solid fa-user-shield"></i></div><span>Manage Officers</span></a>
      
      <div class="sb-section-label">System Masters</div>
      <a href="manage-departments.php" class="sb-link"><div class="icon-wrap"><i class="fa-solid fa-building-columns"></i></div><span>Departments</span></a>
      <a href="manage-categories.php" class="sb-link"><div class="icon-wrap"><i class="fa-solid fa-tags"></i></div><span>Categories</span></a>
      
      <div class="sb-section-label">Intelligence</div>
      <a href="reports.php" class="sb-link"><div class="icon-wrap"><i class="fa-solid fa-file-chart-column"></i></div><span>Reports</span></a>
      <a href="analytics.php" class="sb-link"><div class="icon-wrap"><i class="fa-solid fa-chart-line"></i></div><span>Analytics</span></a>
      <a href="activity-logs.php" class="sb-link"><div class="icon-wrap"><i class="fa-solid fa-clock-rotate-left"></i></div><span>Activity Logs</span></a>
    </nav>
    <div class="sb-footer">
      <div class="sb-admin-card">
        <img src="https://images.unsplash.com/photo-1560250097-0b93528c311a?auto=format&fit=crop&w=80&q=80" alt="Admin" class="sb-avatar">
        <div class="sb-admin-info"><div class="sb-admin-name">Super Admin</div><div class="sb-admin-role">System Overseer</div></div>
      </div>
    </div>
  </aside>

  <!-- ══════════════════════ MAIN CONTENT ══════════════════════ -->
  <div id="main-content">
    <nav class="top-navbar">
      <div class="d-flex align-items-center gap-3">
        <button class="btn-toggle" id="sidebarToggle"><i class="fa-solid fa-bars-staggered"></i></button>
        <!-- SEARCH BOX WITH GET METHOD -->
        <form method="GET" action="manage-complaints.php" class="search-wrap d-none d-md-flex align-items-center m-0">
          <i class="fa-solid fa-magnifying-glass"></i>
          <input type="text" name="search" id="tableSearch" placeholder="Search complaint ID, title, officer, status..." value="<?= htmlspecialchars($search) ?>">
          <?php if (!empty($search)): ?>
            <a href="manage-complaints.php" class="text-muted ms-2 me-1" title="Clear Search"><i class="fa-solid fa-xmark"></i></a>
          <?php endif; ?>
        </form>
      </div>
      <div class="nav-actions">
        <div class="online-chip d-none d-xl-flex"><span class="online-dot"></span>Database Live</div>
        <button class="nav-icon-btn" id="themeToggle" title="Toggle Theme"><i class="fa-solid fa-moon" id="themeIcon"></i></button>
        <div class="dropdown">
          <div class="nav-profile-btn dropdown-toggle" data-bs-toggle="dropdown">
            <img src="https://images.unsplash.com/photo-1560250097-0b93528c311a?auto=format&fit=crop&w=80&q=80" class="nav-avatar" alt="Admin">
            <div class="d-none d-md-block"><div class="nav-profile-name">Inspector Admin</div><div class="nav-profile-role">Super Administrator</div></div>
          </div>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="activity-logs.php"><i class="fa-solid fa-clock-rotate-left"></i> My Audit Trail</a></li>
            <li><div class="dropdown-divider"></div></li>
            <li><a class="dropdown-item text-danger" href="login.php"><i class="fa-solid fa-power-off"></i> Logout</a></li>
          </ul>
        </div>
      </div>
    </nav>

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

      <div class="page-header">
        <div>
          <div class="page-breadcrumb">
            <a href="index.php"><i class="fa-solid fa-house"></i></a>
            <i class="fa-solid fa-chevron-right"></i><span>Complaints Master Docket</span>
          </div>
          <h1 class="page-title">Master Complaint Repository</h1>
          <p class="page-subtitle">Fully dynamic oversight of all filed complaints. Real-time GET-based update, status revision, search &amp; removal.</p>
        </div>
        <div class="d-flex gap-2">
          <button class="btn btn-outline-primary btn-export-pdf" data-doc-name="Complaint_Repository_Full">
            <i class="fa-solid fa-file-pdf"></i> Export Dossier
          </button>
          <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newLodgeModal">
            <i class="fa-solid fa-plus"></i> Lodge New Complaint
          </button>
        </div>
      </div>

      <!-- DYNAMIC KPI ROW -->
      <div class="kpi-grid">
        <div class="kpi-card indigo">
          <div class="kpi-icon"><i class="fa-solid fa-folder-tree"></i></div>
          <div class="kpi-value counter-value"><?= $count_all ?></div>
          <div class="kpi-label">Total Filed (DB)</div>
          <div class="kpi-trend up"><i class="fa-solid fa-database"></i> Live Database</div>
        </div>
        <div class="kpi-card amber">
          <div class="kpi-icon"><i class="fa-solid fa-clock-rotate-left"></i></div>
          <div class="kpi-value counter-value"><?= $count_pending ?></div>
          <div class="kpi-label">Pending Triage</div>
          <div class="kpi-trend down"><i class="fa-solid fa-hourglass"></i> Action Required</div>
        </div>
        <div class="kpi-card rose">
          <div class="kpi-icon"><i class="fa-solid fa-bolt"></i></div>
          <div class="kpi-value counter-value"><?= $count_under_inv ?></div>
          <div class="kpi-label">Under Active Probe</div>
          <div class="kpi-trend up"><i class="fa-solid fa-microscope"></i> Field Probes</div>
        </div>
        <div class="kpi-card emerald">
          <div class="kpi-icon"><i class="fa-solid fa-circle-check"></i></div>
          <div class="kpi-value counter-value"><?= $count_resolved ?></div>
          <div class="kpi-label">Resolved &amp; Closed</div>
          <div class="kpi-trend up"><i class="fa-solid fa-check-double"></i> Complete</div>
        </div>
      </div>

      <!-- DYNAMIC FILTER & SEARCH BAR WITH GET METHOD -->
      <form method="GET" action="manage-complaints.php" class="filter-bar">
        <div class="row g-3 align-items-end">
          <div class="col-md-3">
            <label class="form-label">Keyword Search</label>
            <div class="input-icon-group">
              <i class="fa-solid fa-magnifying-glass input-icon"></i>
              <input type="text" name="search" class="form-control" placeholder="Search keywords..." value="<?= htmlspecialchars($search) ?>">
            </div>
          </div>
          <div class="col-md-3">
            <label class="form-label">Filter by Status</label>
            <div class="input-icon-group">
              <i class="fa-solid fa-filter input-icon"></i>
              <select class="form-select" name="status_filter" id="statusFilterSelect">
                <option value="all">All Statuses</option>
                <option value="Submitted" <?= $filter_status === 'Submitted' ? 'selected' : '' ?>>Submitted</option>
                <option value="Pending Verification" <?= $filter_status === 'Pending Verification' ? 'selected' : '' ?>>Pending Verification</option>
                <option value="Assigned" <?= $filter_status === 'Assigned' ? 'selected' : '' ?>>Assigned</option>
                <option value="Under Investigation" <?= $filter_status === 'Under Investigation' ? 'selected' : '' ?>>Under Investigation</option>
                <option value="Investigation Completed" <?= $filter_status === 'Investigation Completed' ? 'selected' : '' ?>>Investigation Completed</option>
                <option value="Closed" <?= $filter_status === 'Closed' ? 'selected' : '' ?>>Closed</option>
                <option value="Rejected" <?= $filter_status === 'Rejected' ? 'selected' : '' ?>>Rejected</option>
              </select>
            </div>
          </div>
          <div class="col-md-2">
            <label class="form-label">Filter by Priority</label>
            <div class="input-icon-group">
              <i class="fa-solid fa-shield-halved input-icon"></i>
              <select class="form-select" name="priority_filter" id="priorityFilterSelect">
                <option value="all">All Priorities</option>
                <option value="Critical" <?= $filter_priority === 'Critical' ? 'selected' : '' ?>>Critical</option>
                <option value="High" <?= $filter_priority === 'High' ? 'selected' : '' ?>>High</option>
                <option value="Medium" <?= $filter_priority === 'Medium' ? 'selected' : '' ?>>Medium</option>
                <option value="Low" <?= $filter_priority === 'Low' ? 'selected' : '' ?>>Low</option>
              </select>
            </div>
          </div>
          <div class="col-md-2">
            <label class="form-label">Corruption Category</label>
            <div class="input-icon-group">
              <i class="fa-solid fa-tag input-icon"></i>
              <select class="form-select" name="category_filter">
                <option value="0">All Categories</option>
                <?php foreach ($all_categories as $cat): ?>
                  <option value="<?= $cat['category_id'] ?>" <?= $filter_category == $cat['category_id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat['category_name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div class="col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-primary flex-grow-1"><i class="fa-solid fa-filter"></i> Apply</button>
            <a href="manage-complaints.php" class="btn btn-ghost" title="Reset Filters"><i class="fa-solid fa-rotate-left"></i></a>
          </div>
        </div>
      </form>

      <!-- COMPLAINTS MASTER TABLE -->
      <div class="card-box">
        <div class="card-box-head">
          <div class="card-box-title">
            <div class="title-icon"><i class="fa-solid fa-folder-open"></i></div>
            Live Complaints Register (Showing <?= count($complaints) ?> Records)
          </div>
          <div class="d-flex gap-2 align-items-center">
            <button class="btn btn-ghost btn-sm btn-export-pdf" data-doc-name="Complaints_Master_Export"><i class="fa-solid fa-file-pdf" style="color:var(--rose-500);"></i> PDF</button>
          </div>
        </div>
        <div class="tbl-wrap">
          <table class="tbl" id="complaintsTable">
            <thead>
              <tr>
                <th>Docket ID</th>
                <th>Complainant / Source</th>
                <th>Title &amp; Scope</th>
                <th>Category</th>
                <th>Priority</th>
                <th>Assigned Officer</th>
                <th>Case Status</th>
                <th class="text-end no-sort">Actions (GET Based)</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($complaints)): ?>
                <?php foreach ($complaints as $c): 
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
                    'Rejected' => 'pill-blocked',
                    default => 'pill-new'
                  };
                  $complainant_display = !empty($c['complainant_name']) ? htmlspecialchars($c['complainant_name']) : 'Anonymous Whistleblower';
                  $off_display = !empty($c['officer_name']) ? htmlspecialchars($c['officer_name']) : '<span class="text-danger extra-small">Unassigned</span>';
                  $cat_display = !empty($c['category_name']) ? htmlspecialchars($c['category_name']) : 'General';
                  $fmt_date = date('M d, Y', strtotime($c['created_at']));
                ?>
                <tr>
                  <td>
                    <a href="javascript:void(0)" class="fw-bold" style="color:var(--indigo-600); font-family:var(--font-mono);" 
                       onclick="openViewComplaint('#CCMS-<?= $cid ?>', '<?= addslashes($complainant_display) ?>', '<?= addslashes($c['location'] ?? 'N/A') ?>', '<?= addslashes($cat_display) ?>', '<?= $c['priority'] ?>', '<?= addslashes(strip_tags($off_display)) ?>', '<?= $c['status'] ?>', '<?= addslashes($c['description']) ?>')">
                      #CCMS-<?= $cid ?>
                    </a>
                    <div class="extra-small text-muted"><?= $fmt_date ?></div>
                  </td>
                  <td>
                    <div class="d-flex align-items-center gap-2">
                      <span style="background:var(--primary);width:32px;height:32px;border-radius:8px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:.8rem;flex-shrink:0;">
                        <i class="fa-solid fa-user"></i>
                      </span>
                      <div>
                        <div class="fw-semibold" style="font-size:.82rem;"><?= $complainant_display ?></div>
                        <div class="extra-small text-muted" style="font-family:var(--font-mono);"><?= htmlspecialchars($c['complainant_email'] ?? 'Encrypted User') ?></div>
                      </div>
                    </div>
                  </td>
                  <td style="max-width: 240px;">
                    <div class="fw-semibold text-truncate" style="font-size:.85rem;" title="<?= htmlspecialchars($c['title']) ?>">
                      <?= htmlspecialchars($c['title']) ?>
                    </div>
                    <div class="extra-small text-muted text-truncate"><i class="fa-solid fa-location-dot me-1"></i><?= htmlspecialchars($c['location'] ?? 'N/A') ?></div>
                  </td>
                  <td style="font-size:.82rem;"><span class="badge bg-light text-dark border"><?= $cat_display ?></span></td>
                  <td><span class="<?= $badge_p ?>"><?= htmlspecialchars($c['priority']) ?></span></td>
                  <td>
                    <div class="fw-semibold" style="font-size:.82rem;"><?= $off_display ?></div>
                    <?php if (!empty($c['officer_designation'])): ?>
                      <div class="extra-small text-muted"><?= htmlspecialchars($c['officer_designation']) ?></div>
                    <?php endif; ?>
                  </td>
                  <td><span class="pill <?= $badge_s ?> pill-live"><?= htmlspecialchars($c['status']) ?></span></td>
                  <td class="text-end">
                    <div class="d-inline-flex gap-1 align-items-center">
                      <!-- VIEW DETAILS -->
                      <button class="btn btn-ghost btn-icon btn-sm" 
                              onclick="openViewComplaint('#CCMS-<?= $cid ?>', '<?= addslashes($complainant_display) ?>', '<?= addslashes($c['location'] ?? 'N/A') ?>', '<?= addslashes($cat_display) ?>', '<?= $c['priority'] ?>', '<?= addslashes(strip_tags($off_display)) ?>', '<?= $c['status'] ?>', '<?= addslashes($c['description']) ?>')" 
                              title="View Dossier">
                        <i class="fa-solid fa-eye text-primary"></i>
                      </button>

                      <!-- UPDATE STATUS VIA GET MODAL -->
                      <button class="btn btn-ghost btn-icon btn-sm" onclick="openStatusModal('<?= $cid ?>', '<?= htmlspecialchars($c['status']) ?>')" title="Update Status (GET Method)">
                        <i class="fa-solid fa-rotate text-warning"></i>
                      </button>

                      <!-- EDIT COMPLAINT VIA GET MODAL -->
                      <button class="btn btn-ghost btn-icon btn-sm" 
                              onclick="openEditModal('<?= $cid ?>', '<?= addslashes($c['title']) ?>', '<?= $c['category_id'] ?>', '<?= $c['priority'] ?>', '<?= $c['status'] ?>', '<?= addslashes($c['location'] ?? '') ?>', '<?= $c['assigned_officer'] ?? '' ?>', '<?= addslashes($c['description']) ?>')" 
                              title="Edit Record (GET Method)">
                        <i class="fa-solid fa-pen-to-square text-info"></i>
                      </button>

                      <!-- DELETE BASED ON GET METHOD -->
                      <a href="manage-complaints.php?action=delete&id=<?= $cid ?>" 
                         onclick="return confirm('Confirm Deletion: Are you sure you want to delete Complaint #CCMS-<?= $cid ?> via GET request?');" 
                         class="btn btn-ghost btn-icon btn-sm" 
                         title="Delete Complaint (GET Method)">
                        <i class="fa-solid fa-trash text-danger"></i>
                      </a>
                    </div>
                  </td>
                </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="8" class="text-center py-5">
                    <i class="fa-solid fa-folder-open text-muted mb-2" style="font-size:2.5rem;"></i>
                    <h6 class="text-muted">No complaints match your active filter / search query</h6>
                    <a href="manage-complaints.php" class="btn btn-sm btn-outline-primary mt-2">Clear All Filters</a>
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

<!-- ══════════════════════ MODAL: LODGE COMPLAINT ══════════════════════ -->
<div class="modal fade" id="newLodgeModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa-solid fa-plus text-primary me-2"></i> Register New Complaint</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" action="manage-complaints.php">
        <input type="hidden" name="lodge_complaint" value="1">
        <div class="modal-body" style="padding:24px;">
          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label">Complainant / Citizen User *</label>
              <select class="form-select" name="user_id" required>
                <?php foreach ($all_users as $u): ?>
                  <option value="<?= $u['user_id'] ?>"><?= htmlspecialchars($u['name']) ?> (<?= htmlspecialchars($u['email']) ?>)</option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Corruption Category *</label>
              <select class="form-select" name="category_id" required>
                <?php foreach ($all_categories as $cat): ?>
                  <option value="<?= $cat['category_id'] ?>"><?= htmlspecialchars($cat['category_name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Complaint Title / Incident Summary *</label>
            <input type="text" class="form-control" name="title" placeholder="e.g. Demanding bribe for clearance certificate" required>
          </div>
          <div class="row g-3 mb-3">
            <div class="col-md-4">
              <label class="form-label">Incident Location / Office *</label>
              <input type="text" class="form-control" name="location" placeholder="e.g. Revenue Sub-Registrar Office, Surat" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">Priority Level *</label>
              <select class="form-select" name="priority">
                <option value="Low">Low</option>
                <option value="Medium" selected>Medium</option>
                <option value="High">High</option>
                <option value="Critical">Critical</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">Assign Field Officer</label>
              <select class="form-select" name="assigned_officer">
                <option value="">Leave Unassigned</option>
                <?php foreach ($all_officers as $off): ?>
                  <option value="<?= $off['officer_id'] ?>"><?= htmlspecialchars($off['name']) ?> (<?= htmlspecialchars($off['designation']) ?>)</option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Detailed Incident Allegation *</label>
            <textarea class="form-control" name="description" rows="4" placeholder="Detail the corrupt transaction, involved officials, demanded sums, timestamps..." required></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check me-1"></i> Register Complaint</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ══════════════════════ MODAL: UPDATE STATUS (GET METHOD) ══════════════════════ -->
<div class="modal fade" id="updateStatusModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa-solid fa-rotate text-warning me-2"></i> Update Status (GET Method)</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <!-- FORM USING GET METHOD -->
      <form method="GET" action="manage-complaints.php">
        <input type="hidden" name="action" value="update_status">
        <input type="hidden" name="id" id="statusTargetId">
        <div class="modal-body" style="padding:24px;">
          <div class="mb-3">
            <label class="form-label">Target Complaint Docket</label>
            <input type="text" class="form-control" id="statusTargetCaseDisplay" readonly style="font-family:var(--font-mono);font-weight:700;">
          </div>
          <div class="mb-3">
            <label class="form-label">New Investigation State *</label>
            <select class="form-select" name="status" id="newStatusSelect" required>
              <option value="Submitted">Submitted</option>
              <option value="Pending Verification">Pending Verification</option>
              <option value="Verified">Verified</option>
              <option value="Assigned">Assigned</option>
              <option value="Under Investigation">Under Investigation</option>
              <option value="Waiting for Evidence">Waiting for Evidence</option>
              <option value="Investigation Completed">Investigation Completed</option>
              <option value="Closed">Closed</option>
              <option value="Rejected">Rejected</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Official Investigation Remark / Audit Note</label>
            <textarea class="form-control" name="remarks" rows="3" placeholder="Enter findings, hearing dates, or closing rationale..."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-warning"><i class="fa-solid fa-check me-1"></i> Update Status via GET</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ══════════════════════ MODAL: EDIT COMPLAINT (GET METHOD) ══════════════════════ -->
<div class="modal fade" id="editComplaintModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa-solid fa-pen-to-square text-info me-2"></i> Edit Complaint Docket (GET Method)</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <!-- FORM SUBMISSION VIA GET METHOD -->
      <form method="GET" action="manage-complaints.php">
        <input type="hidden" name="action" value="update">
        <input type="hidden" name="id" id="editId">
        <div class="modal-body" style="padding:24px;">
          <div class="mb-3">
            <label class="form-label">Docket Title / Incident Summary *</label>
            <input type="text" class="form-control" name="title" id="editTitle" required>
          </div>
          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label">Category *</label>
              <select class="form-select" name="category_id" id="editCategory" required>
                <?php foreach ($all_categories as $cat): ?>
                  <option value="<?= $cat['category_id'] ?>"><?= htmlspecialchars($cat['category_name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Priority Level *</label>
              <select class="form-select" name="priority" id="editPriority">
                <option value="Low">Low</option>
                <option value="Medium">Medium</option>
                <option value="High">High</option>
                <option value="Critical">Critical</option>
              </select>
            </div>
          </div>
          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label">Current Status *</label>
              <select class="form-select" name="status" id="editStatus">
                <option value="Submitted">Submitted</option>
                <option value="Pending Verification">Pending Verification</option>
                <option value="Verified">Verified</option>
                <option value="Assigned">Assigned</option>
                <option value="Under Investigation">Under Investigation</option>
                <option value="Waiting for Evidence">Waiting for Evidence</option>
                <option value="Investigation Completed">Investigation Completed</option>
                <option value="Closed">Closed</option>
                <option value="Rejected">Rejected</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Assigned Investigating Officer</label>
              <select class="form-select" name="assigned_officer" id="editOfficer">
                <option value="">Unassigned</option>
                <?php foreach ($all_officers as $off): ?>
                  <option value="<?= $off['officer_id'] ?>"><?= htmlspecialchars($off['name']) ?> (<?= htmlspecialchars($off['designation']) ?>)</option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Location / Jurisdiction Office</label>
            <input type="text" class="form-control" name="location" id="editLocation">
          </div>
          <div class="mb-3">
            <label class="form-label">Full Narrative Statement</label>
            <textarea class="form-control" name="description" id="editDescription" rows="4"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-info text-white"><i class="fa-solid fa-save me-1"></i> Save Changes via GET</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ══════════════════════ MODAL: VIEW COMPLAINT DOSSIER ══════════════════════ -->
<div class="modal fade" id="viewDetailsModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <div>
          <div class="d-flex gap-2 mb-1">
            <span class="pill pill-new" id="modalViewStatusBadge">Submitted</span>
            <span class="chip-critical" id="modalViewPriority">Critical</span>
          </div>
          <h5 class="modal-title" id="modalViewId">#CCMS-0000 — Complaint Dossier</h5>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" style="padding:24px;">
        <div class="p-3 border rounded-3 bg-body-tertiary mb-3">
          <div class="row g-2">
            <div class="col-md-6">
              <div class="extra-small text-muted">Complainant:</div>
              <strong id="modalViewComplainant">Citizen</strong>
            </div>
            <div class="col-md-6">
              <div class="extra-small text-muted">Location / Department Scope:</div>
              <strong id="modalViewDept">Location</strong>
            </div>
            <div class="col-md-6 mt-2">
              <div class="extra-small text-muted">Assigned Officer:</div>
              <strong id="modalViewOfficer">Unassigned</strong>
            </div>
            <div class="col-md-6 mt-2">
              <div class="extra-small text-muted">Category:</div>
              <strong id="modalViewCat">Category</strong>
            </div>
          </div>
        </div>

        <h6 class="fw-bold extra-small text-uppercase text-muted mb-2">Allegation Statement</h6>
        <p style="font-size:.875rem;line-height:1.7;" id="modalViewDesc"></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Close</button>
        <a href="assign-complaints.php" class="btn btn-outline-primary"><i class="fa-solid fa-user-plus"></i> Reassign Officer</a>
      </div>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="assets/js/main.js"></script>
<script>
  function openViewComplaint(id, complainant, dept, cat, priority, officer, status, desc) {
    $('#modalViewId').text(`${id} — Case File`);
    $('#modalViewComplainant').text(complainant);
    $('#modalViewDept').text(dept);
    $('#modalViewCat').text(cat);
    $('#modalViewPriority').text(priority);
    $('#modalViewOfficer').text(officer);
    $('#modalViewStatusBadge').text(status);
    $('#modalViewDesc').text(desc);
    const modal = new bootstrap.Modal(document.getElementById('viewDetailsModal'));
    modal.show();
  }

  function openStatusModal(caseId, currentStatus) {
    $('#statusTargetId').val(caseId);
    $('#statusTargetCaseDisplay').val('#CCMS-' + caseId);
    if (currentStatus) {
      $('#newStatusSelect').val(currentStatus);
    }
    const modal = new bootstrap.Modal(document.getElementById('updateStatusModal'));
    modal.show();
  }

  function openEditModal(id, title, catId, priority, status, location, officerId, desc) {
    $('#editId').val(id);
    $('#editTitle').val(title);
    $('#editCategory').val(catId);
    $('#editPriority').val(priority);
    $('#editStatus').val(status);
    $('#editLocation').val(location);
    $('#editOfficer').val(officerId || '');
    $('#editDescription').val(desc);
    const modal = new bootstrap.Modal(document.getElementById('editComplaintModal'));
    modal.show();
  }

  // Client-side instant filter on table rows for real-time responsiveness
  document.getElementById('tableSearch')?.addEventListener('keyup', function() {
    const val = this.value.toLowerCase();
    document.querySelectorAll('#complaintsTable tbody tr').forEach(row => {
      const text = row.innerText.toLowerCase();
      row.style.display = text.includes(val) ? '' : 'none';
    });
  });
</script>
</body>
</html>
