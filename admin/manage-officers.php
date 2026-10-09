<?php
require_once '../db.php';

$error_msg = '';
$success_msg = '';

// ═══════════════════ 1. DELETE ACTION (GET METHOD) ═══════════════════
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $del_id = intval($_GET['id']);
    if ($del_id > 0) {
        $del_stmt = mysqli_prepare($conn, "DELETE FROM officers WHERE officer_id = ?");
        if ($del_stmt) {
            mysqli_stmt_bind_param($del_stmt, "i", $del_id);
            if (mysqli_stmt_execute($del_stmt)) {
                $success_msg = "Officer record #OFF-{$del_id} removed from roster.";
            } else {
                $error_msg = "Failed to remove officer: " . mysqli_error($conn);
            }
            mysqli_stmt_close($del_stmt);
        }
    }
}

// ═══════════════════ 2. UPDATE ACTION (GET METHOD) ═══════════════════
if (isset($_GET['action']) && $_GET['action'] === 'update' && isset($_GET['id'])) {
    $up_id = intval($_GET['id']);
    $name = trim($_GET['name'] ?? '');
    $email = trim($_GET['email'] ?? '');
    $phone = trim($_GET['phone'] ?? '');
    $designation = trim($_GET['designation'] ?? '');
    $department_id = intval($_GET['department_id'] ?? 1);
    $status = trim($_GET['status'] ?? 'Active');

    if ($up_id > 0 && !empty($name) && !empty($email) && !empty($designation)) {
        $up_stmt = mysqli_prepare($conn, "UPDATE officers SET name = ?, email = ?, phone = ?, designation = ?, department_id = ?, status = ? WHERE officer_id = ?");
        if ($up_stmt) {
            mysqli_stmt_bind_param($up_stmt, "ssssisi", $name, $email, $phone, $designation, $department_id, $status, $up_id);
            if (mysqli_stmt_execute($up_stmt)) {
                $success_msg = "Officer '{$name}' updated successfully via GET action!";
            } else {
                $error_msg = "Failed to update officer: " . mysqli_error($conn);
            }
            mysqli_stmt_close($up_stmt);
        }
    } else {
        $error_msg = "Name, Email, and Rank/Designation are mandatory.";
    }
}

// ═══════════════════ 3. STATUS TOGGLE (GET METHOD) ═══════════════════
if (isset($_GET['action']) && $_GET['action'] === 'toggle_status' && isset($_GET['id'])) {
    $tog_id = intval($_GET['id']);
    $new_status = ($_GET['status'] ?? '') === 'Active' ? 'Inactive' : 'Active';
    $tog_stmt = mysqli_prepare($conn, "UPDATE officers SET status = ? WHERE officer_id = ?");
    if ($tog_stmt) {
        mysqli_stmt_bind_param($tog_stmt, "si", $new_status, $tog_id);
        if (mysqli_stmt_execute($tog_stmt)) {
            $success_msg = "Officer #OFF-{$tog_id} duty status set to '{$new_status}'.";
        }
        mysqli_stmt_close($tog_stmt);
    }
}

// ═══════════════════ 4. ADD OFFICER (POST OR GET) ═══════════════════
if (($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_officer'])) || (isset($_GET['action']) && $_GET['action'] === 'add')) {
    $src = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : $_GET;
    $name = trim($src['name'] ?? '');
    $designation = trim($src['designation'] ?? '');
    $department_id = intval($src['department_id'] ?? 1);
    $email = trim($src['email'] ?? '');
    $phone = trim($src['phone'] ?? '');
    $password = $src['password'] ?? 'officer123';
    $status = $src['status'] ?? 'Active';

    if (empty($name) || empty($email) || empty($password) || empty($designation)) {
        $error_msg = 'Please fill in all mandatory officer credentials.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_msg = 'Please enter a valid official email address.';
    } else {
        $check = mysqli_prepare($conn, "SELECT officer_id FROM officers WHERE email = ? LIMIT 1");
        mysqli_stmt_bind_param($check, "s", $email);
        mysqli_stmt_execute($check);
        mysqli_stmt_store_result($check);

        if (mysqli_stmt_num_rows($check) > 0) {
            $error_msg = "An officer with email '{$email}' already exists.";
        } else {
            $insert = mysqli_prepare($conn, "INSERT INTO officers (name, email, phone, password, status, designation, department_id, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
            mysqli_stmt_bind_param($insert, "ssssssi", $name, $email, $phone, $password, $status, $designation, $department_id);
            if (mysqli_stmt_execute($insert)) {
                $new_id = mysqli_insert_id($conn);
                $success_msg = "Officer '{$name}' (#OFF-{$new_id}) registered successfully!";
            } else {
                $error_msg = "Failed to add officer: " . mysqli_error($conn);
            }
            mysqli_stmt_close($insert);
        }
        mysqli_stmt_close($check);
    }
}

// ═══════════════════ 5. SEARCHING & FILTERING (GET METHOD) ═══════════════════
$search = trim($_GET['search'] ?? '');
$filter_rank = trim($_GET['rank'] ?? '');
$filter_dept = intval($_GET['dept_id'] ?? 0);
$filter_status = trim($_GET['duty_status'] ?? '');

$query = "SELECT o.*, d.department_name, COUNT(c.complaint_id) AS active_cases
          FROM officers o
          LEFT JOIN departments d ON o.department_id = d.department_id
          LEFT JOIN complaints c ON o.officer_id = c.assigned_officer
          WHERE 1=1";

$params = [];
$types = "";

if (!empty($search)) {
    $search_pattern = "%{$search}%";
    $query .= " AND (o.name LIKE ? OR o.email LIKE ? OR o.phone LIKE ? OR o.designation LIKE ? OR d.department_name LIKE ? OR o.officer_id LIKE ?)";
    $types .= "ssssss";
    $params[] = $search_pattern;
    $params[] = $search_pattern;
    $params[] = $search_pattern;
    $params[] = $search_pattern;
    $params[] = $search_pattern;
    $params[] = $search_pattern;
}

if (!empty($filter_rank)) {
    $query .= " AND o.designation = ?";
    $types .= "s";
    $params[] = $filter_rank;
}

if ($filter_dept > 0) {
    $query .= " AND o.department_id = ?";
    $types .= "i";
    $params[] = $filter_dept;
}

if (!empty($filter_status)) {
    $query .= " AND o.status = ?";
    $types .= "s";
    $params[] = $filter_status;
}

$query .= " GROUP BY o.officer_id ORDER BY o.officer_id DESC";

$stmt = mysqli_prepare($conn, $query);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$officers_res = mysqli_stmt_get_result($stmt);
$db_officers = [];
if ($officers_res) {
    while ($row = mysqli_fetch_assoc($officers_res)) {
        $db_officers[] = $row;
    }
}
mysqli_stmt_close($stmt);

// Fetch all dynamic departments
$dept_res = mysqli_query($conn, "SELECT department_id, department_name FROM departments ORDER BY department_name ASC");
$all_departments = [];
while ($drow = mysqli_fetch_assoc($dept_res)) {
    $all_departments[] = $drow;
}

// Dynamic KPIs
$kpi_total = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM officers"))[0] ?? 0;
$kpi_active = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM officers WHERE status = 'Active'"))[0] ?? 0;
$kpi_inactive = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM officers WHERE status != 'Active'"))[0] ?? 0;
$kpi_dept_count = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(DISTINCT department_id) FROM officers"))[0] ?? 0;
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Officers — CCMS Admin</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&family=Space+Grotesk:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    .officer-card { background:var(--surface); border:1px solid var(--border); border-radius:var(--r-lg); overflow:hidden; transition:var(--tx); box-shadow:var(--sh-card); }
    .officer-card:hover { transform:translateY(-5px); box-shadow:var(--sh-md); border-color:var(--indigo-400); }
    .officer-banner { height:80px; position:relative; display:flex; align-items:flex-end; padding:0 18px 0; }
    .officer-avatar { width:72px; height:72px; border-radius:16px; object-fit:cover; border:3px solid var(--surface); box-shadow:var(--sh-sm); position:relative; bottom:-36px; flex-shrink:0; }
    .officer-body { padding:44px 18px 16px; }
    .officer-footer { padding:12px 18px; border-top:1px solid var(--border); background:var(--surface-2); display:flex; align-items:center; justify-content:space-between; }
    .workload-bar { height:6px; border-radius:99px; background:var(--border); overflow:hidden; margin-top:6px; }
    .workload-fill { height:100%; border-radius:99px; transition:width 1s ease; }
    .badge-rank { display:inline-flex; align-items:center; gap:6px; padding:.28em .8em; border-radius:8px; font-size:.7rem; font-weight:800; text-transform:uppercase; letter-spacing:.05em; }
  </style>
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
      <div class="sb-section-label">External Portals</div>
      <a href="../citizen/dashboard.php" class="sb-link">
        <div class="icon-wrap"><i class="fa-solid fa-users"></i></div>
        <span>Citizen Portal</span>
      </a>
      <a href="../officer/index.php" class="sb-link">
        <div class="icon-wrap"><i class="fa-solid fa-user-shield"></i></div>
        <span>Officer Command</span>
      </a>
      <a href="../index.php" class="sb-link">
        <div class="icon-wrap"><i class="fa-solid fa-globe"></i></div>
        <span>Public Sentinel</span>
      </a>

      <div class="sb-section-label">Main Overview</div>
      <a href="index.php" class="sb-link"><div class="icon-wrap"><i class="fa-solid fa-gauge-high"></i></div><span>Dashboard</span></a>
      <a href="manage-complaints.php" class="sb-link"><div class="icon-wrap"><i class="fa-solid fa-folder-open"></i></div><span>Manage Complaints</span></a>
      <a href="assign-complaints.php" class="sb-link"><div class="icon-wrap"><i class="fa-solid fa-user-tag"></i></div><span>Assign Complaints</span></a>
      
      <div class="sb-section-label">Personnel</div>
      <a href="manage-users.php" class="sb-link"><div class="icon-wrap"><i class="fa-solid fa-users"></i></div><span>Manage Users</span></a>
      <a href="manage-officers.php" class="sb-link active"><div class="icon-wrap"><i class="fa-solid fa-user-shield"></i></div><span>Manage Officers</span><span class="sb-badge"><?= $kpi_total ?></span></a>
      
      <div class="sb-section-label">System Masters</div>
      <a href="manage-departments.php" class="sb-link"><div class="icon-wrap"><i class="fa-solid fa-building-columns"></i></div><span>Departments</span></a>
      <a href="manage-categories.php" class="sb-link"><div class="icon-wrap"><i class="fa-solid fa-tags"></i></div><span>Categories</span></a>
      
      <div class="sb-section-label">Intelligence</div>
      <a href="reports.php" class="sb-link"><div class="icon-wrap"><i class="fa-solid fa-file-chart-column"></i></div><span>Reports</span></a>
      <a href="analytics.php" class="sb-link"><div class="icon-wrap"><i class="fa-solid fa-chart-line"></i></div><span>Analytics</span></a>
      <a href="activity-logs.php" class="sb-link"><div class="icon-wrap"><i class="fa-solid fa-clock-rotate-left"></i></div><span>Activity Logs</span></a>
    </nav>
    <div class="sb-footer"><div class="sb-admin-card"><img src="https://images.unsplash.com/photo-1560250097-0b93528c311a?auto=format&fit=crop&w=80&q=80" alt="Admin" class="sb-avatar"><div class="sb-admin-info"><div class="sb-admin-name">Super Admin</div><div class="sb-admin-role">System Overseer</div></div></div></div>
  </aside>

  <!-- ══════════════════════ MAIN CONTENT ══════════════════════ -->
  <div id="main-content">
    <nav class="top-navbar">
      <div class="d-flex align-items-center gap-3">
        <button class="btn-toggle" id="sidebarToggle"><i class="fa-solid fa-bars-staggered"></i></button>
        <!-- SEARCH VIA GET METHOD -->
        <form method="GET" action="manage-officers.php" class="search-wrap d-none d-md-flex align-items-center m-0">
          <i class="fa-solid fa-magnifying-glass"></i>
          <input type="text" name="search" id="officerSearchInput" placeholder="Search officer name, designation, email..." value="<?= htmlspecialchars($search) ?>">
          <?php if (!empty($search)): ?>
            <a href="manage-officers.php" class="text-muted ms-2 me-1" title="Clear Search"><i class="fa-solid fa-xmark"></i></a>
          <?php endif; ?>
        </form>
      </div>
      <div class="nav-actions">
        <div class="online-chip d-none d-xl-flex"><span class="online-dot"></span>Officers Live</div>
        <button class="nav-icon-btn" id="themeToggle"><i class="fa-solid fa-moon" id="themeIcon"></i></button>
        <div class="dropdown">
          <div class="nav-profile-btn dropdown-toggle" data-bs-toggle="dropdown">
            <img src="https://images.unsplash.com/photo-1560250097-0b93528c311a?auto=format&fit=crop&w=80&q=80" class="nav-avatar" alt="Admin">
            <div class="d-none d-md-block"><div class="nav-profile-name">Inspector Admin</div><div class="nav-profile-role">Super Administrator</div></div>
          </div>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="../index.php"><i class="fa-solid fa-globe"></i> View Public Sentinel</a></li>
            <li><a class="dropdown-item" href="activity-logs.php"><i class="fa-solid fa-clock-rotate-left"></i> My Logs</a></li>
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
          <div class="page-breadcrumb"><a href="index.php"><i class="fa-solid fa-house"></i></a><i class="fa-solid fa-chevron-right"></i><span>Field Officers</span></div>
          <h1 class="page-title">Investigating Officers Registry</h1>
          <p class="page-subtitle">Fully dynamic management of all officers. GET-based update, status toggle, search &amp; delete.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
          <button class="btn btn-ghost btn-export-pdf" data-doc-name="Officer_Roster_PDF"><i class="fa-solid fa-file-pdf" style="color:var(--rose-500);"></i> Export Roster</button>
          <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addOfficerModal"><i class="fa-solid fa-plus"></i> Add New Officer</button>
        </div>
      </div>

      <!-- DYNAMIC KPIS -->
      <div class="kpi-grid">
        <div class="kpi-card indigo">
          <div class="kpi-icon"><i class="fa-solid fa-user-shield"></i></div>
          <div class="kpi-value counter-value"><?= $kpi_total ?></div>
          <div class="kpi-label">Commissioned Officers</div>
          <div class="kpi-trend up"><i class="fa-solid fa-shield-halved"></i> Active Staff</div>
        </div>
        <div class="kpi-card emerald">
          <div class="kpi-icon"><i class="fa-solid fa-circle-check"></i></div>
          <div class="kpi-value counter-value"><?= $kpi_active ?></div>
          <div class="kpi-label">Active on Duty</div>
          <div class="kpi-trend up"><i class="fa-solid fa-bolt"></i> Operational</div>
        </div>
        <div class="kpi-card amber">
          <div class="kpi-icon"><i class="fa-solid fa-bed"></i></div>
          <div class="kpi-value counter-value"><?= $kpi_inactive ?></div>
          <div class="kpi-label">On Leave / Inactive</div>
          <div class="kpi-trend down"><i class="fa-solid fa-clock"></i> Standby</div>
        </div>
        <div class="kpi-card rose">
          <div class="kpi-icon"><i class="fa-solid fa-building-columns"></i></div>
          <div class="kpi-value counter-value"><?= $kpi_dept_count ?></div>
          <div class="kpi-label">Departments Covered</div>
          <div class="kpi-trend up"><i class="fa-solid fa-building"></i> Jurisdiction</div>
        </div>
      </div>

      <!-- FILTER & SEARCH BAR VIA GET -->
      <form method="GET" action="manage-officers.php" class="filter-bar">
        <div class="row g-3 align-items-end">
          <div class="col-md-3">
            <label class="form-label">Search Query</label>
            <div class="input-icon-group">
              <i class="fa-solid fa-magnifying-glass input-icon"></i>
              <input type="text" name="search" class="form-control" placeholder="Name, designation, email..." value="<?= htmlspecialchars($search) ?>">
            </div>
          </div>
          <div class="col-md-3">
            <label class="form-label">Rank / Designation</label>
            <div class="input-icon-group">
              <i class="fa-solid fa-medal input-icon"></i>
              <select class="form-select" name="rank">
                <option value="">All Ranks</option>
                <option value="Inspector" <?= $filter_rank === 'Inspector' ? 'selected' : '' ?>>Inspector</option>
                <option value="Investigation Officer" <?= $filter_rank === 'Investigation Officer' ? 'selected' : '' ?>>Investigation Officer</option>
                <option value="Senior Officer" <?= $filter_rank === 'Senior Officer' ? 'selected' : '' ?>>Senior Officer</option>
                <option value="Deputy Commissioner" <?= $filter_rank === 'Deputy Commissioner' ? 'selected' : '' ?>>Deputy Commissioner</option>
              </select>
            </div>
          </div>
          <div class="col-md-3">
            <label class="form-label">Assigned Department</label>
            <div class="input-icon-group">
              <i class="fa-solid fa-building-columns input-icon"></i>
              <select class="form-select" name="dept_id">
                <option value="0">All Departments</option>
                <?php foreach ($all_departments as $d): ?>
                  <option value="<?= $d['department_id'] ?>" <?= $filter_dept == $d['department_id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($d['department_name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div class="col-md-2">
            <label class="form-label">Duty Status</label>
            <div class="input-icon-group">
              <i class="fa-solid fa-shield-halved input-icon"></i>
              <select class="form-select" name="duty_status">
                <option value="">All Statuses</option>
                <option value="Active" <?= $filter_status === 'Active' ? 'selected' : '' ?>>Active</option>
                <option value="Inactive" <?= $filter_status === 'Inactive' ? 'selected' : '' ?>>Inactive</option>
              </select>
            </div>
          </div>
          <div class="col-md-1 d-flex gap-1">
            <button type="submit" class="btn btn-primary w-100" title="Search"><i class="fa-solid fa-filter"></i></button>
            <a href="manage-officers.php" class="btn btn-ghost" title="Reset"><i class="fa-solid fa-rotate-left"></i></a>
          </div>
        </div>
      </form>

      <!-- DYNAMIC OFFICER CARDS GRID -->
      <div class="row g-3" id="officerGrid">
        <?php if (!empty($db_officers)): ?>
          <?php foreach ($db_officers as $idx => $off): 
            $oid = $off['officer_id'];
            $dept_title = $off['department_name'] ?? 'Anti-Corruption Bureau';
            $avatar_seed = urlencode($off['name']);
            $avatar_url = "https://ui-avatars.com/api/?name={$avatar_seed}&background=6366f1&color=fff&size=120";
            $banner_bg = ($idx % 3 === 0) ? 'linear-gradient(135deg,#4f46e5,#7c3aed)' : (($idx % 3 === 1) ? 'linear-gradient(135deg,#0284c7,#0369a1)' : 'linear-gradient(135deg,#10b981,#059669)');
            $is_active = ($off['status'] ?? 'Active') === 'Active';
          ?>
          <div class="col-lg-4 col-md-6 officer-item">
            <div class="officer-card">
              <div class="officer-banner" style="background:<?= $banner_bg ?>;">
                <img src="<?= $avatar_url ?>" class="officer-avatar" alt="<?= htmlspecialchars($off['name']) ?>">
                <div class="ms-3 pb-1" style="color:rgba(255,255,255,.9);font-size:.75rem;font-weight:700;font-family:var(--font-mono);">ID: #OFF-<?= $oid ?></div>
              </div>
              <div class="officer-body">
                <div class="d-flex align-items-start justify-content-between mb-1">
                  <div>
                    <div class="fw-bold" style="font-size:1rem;"><?= htmlspecialchars($off['name']) ?></div>
                    <div class="extra-small text-muted" style="font-family:var(--font-mono);"><?= htmlspecialchars($off['email']) ?></div>
                  </div>
                  <!-- TOGGLE STATUS VIA GET -->
                  <a href="manage-officers.php?action=toggle_status&id=<?= $oid ?>&status=<?= $off['status'] ?>" 
                     title="Click to toggle status via GET" 
                     class="text-decoration-none">
                    <span class="pill <?= $is_active ? 'pill-active pill-live' : 'pill-blocked' ?>">
                      <?= htmlspecialchars($off['status']) ?>
                    </span>
                  </a>
                </div>
                <div class="mb-3">
                  <span class="badge-rank" style="background:rgba(99,102,241,.12);color:var(--primary);"><i class="fa-solid fa-medal"></i> <?= htmlspecialchars($off['designation']) ?></span>
                </div>
                <div class="row g-2 text-center mb-3">
                  <div class="col-6"><div style="background:var(--bg);border:1px solid var(--border);border-radius:10px;padding:8px;"><div class="extra-small text-muted">PHONE</div><div class="fw-bold extra-small text-truncate"><?= htmlspecialchars($off['phone'] ?? 'N/A') ?></div></div></div>
                  <div class="col-6"><div style="background:var(--bg);border:1px solid var(--border);border-radius:10px;padding:8px;"><div class="extra-small text-muted">ACTIVE CASES</div><div class="fw-bold extra-small text-primary"><?= $off['active_cases'] ?> Cases</div></div></div>
                </div>
                <div class="extra-small fw-bold text-muted mb-1">DUTY READINESS</div>
                <div class="workload-bar"><div class="workload-fill" style="width:<?= $is_active ? '85%' : '15%' ?>;background:<?= $is_active ? 'var(--g-emerald)' : 'var(--g-amber)' ?>;"></div></div>
                <div class="d-flex justify-content-between extra-small mt-1 text-muted"><span>Assigned Department:</span><span class="fw-bold text-truncate" style="max-width: 140px;"><?= htmlspecialchars($dept_title) ?></span></div>
              </div>
              <div class="officer-footer">
                <div class="extra-small text-muted text-truncate" style="max-width: 150px;"><i class="fa-solid fa-building me-1"></i><?= htmlspecialchars($dept_title) ?></div>
                <div class="d-flex gap-1 align-items-center">
                  <!-- VIEW PROFILE -->
                  <button class="btn btn-ghost btn-icon btn-sm" onclick="showOfficerProfile('<?= addslashes($off['name']) ?>', 'OFF-<?= $oid ?>', '<?= addslashes($off['designation']) ?>', '<?= addslashes($dept_title) ?>', '<?= addslashes($off['email']) ?>', '<?= addslashes($off['phone'] ?? '') ?>', '<?= $off['active_cases'] ?>', '<?= $avatar_url ?>', '<?= $off['status'] ?>')" title="View Dossier"><i class="fa-solid fa-eye text-primary"></i></button>

                  <!-- EDIT OFFICER (GET METHOD) -->
                  <button class="btn btn-ghost btn-icon btn-sm" onclick="openEditOfficerModal('<?= $oid ?>', '<?= addslashes($off['name']) ?>', '<?= addslashes($off['email']) ?>', '<?= addslashes($off['phone'] ?? '') ?>', '<?= addslashes($off['designation']) ?>', '<?= $off['department_id'] ?>', '<?= $off['status'] ?>')" title="Edit Officer (GET Method)"><i class="fa-solid fa-pen-to-square text-info"></i></button>

                  <!-- DELETE OFFICER (GET METHOD) -->
                  <a href="manage-officers.php?action=delete&id=<?= $oid ?>" 
                     onclick="return confirm('Confirm Deletion: Remove Officer <?= addslashes($off['name']) ?> (#OFF-<?= $oid ?>) via GET request?');" 
                     class="btn btn-ghost btn-icon btn-sm text-danger" 
                     title="Delete Officer (GET Method)">
                    <i class="fa-solid fa-trash"></i>
                  </a>
                </div>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="col-12 text-center py-5">
            <i class="fa-solid fa-user-shield text-muted" style="font-size: 3rem;"></i>
            <h5 class="mt-3 text-muted">No officers found matching search criteria</h5>
            <a href="manage-officers.php" class="btn btn-sm btn-outline-primary mt-2">Clear Filters</a>
          </div>
        <?php endif; ?>
      </div>
    </main>
  </div>
</div>

<!-- ══════════════════════ MODAL: ADD OFFICER ══════════════════════ -->
<div class="modal fade" id="addOfficerModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa-solid fa-user-shield text-primary me-2"></i> Register Designated Officer</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" action="manage-officers.php">
        <input type="hidden" name="add_officer" value="1">
        <div class="modal-body" style="padding:24px;">
          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label">Officer Full Name *</label>
              <input type="text" class="form-control" name="name" placeholder="e.g. Rajesh Kumar" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Rank / Designation *</label>
              <select class="form-select" name="designation" required>
                <option value="Inspector">Inspector</option>
                <option value="Investigation Officer" selected>Investigation Officer</option>
                <option value="Senior Officer">Senior Officer</option>
                <option value="Deputy Commissioner">Deputy Commissioner</option>
              </select>
            </div>
          </div>
          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label">Department *</label>
              <select class="form-select" name="department_id" required>
                <?php foreach ($all_departments as $d): ?>
                  <option value="<?= $d['department_id'] ?>"><?= htmlspecialchars($d['department_name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Official Email *</label>
              <input type="email" class="form-control" name="email" placeholder="officer@ccms.com" required>
            </div>
          </div>
          <div class="row g-3 mb-3">
            <div class="col-md-4">
              <label class="form-label">Mobile Contact Number</label>
              <input type="tel" class="form-control" name="phone" placeholder="9000000001">
            </div>
            <div class="col-md-4">
              <label class="form-label">Initial Password *</label>
              <input type="password" class="form-control" name="password" placeholder="e.g. off123" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">Duty Status</label>
              <select class="form-select" name="status">
                <option value="Active" selected>Active Duty</option>
                <option value="Inactive">Inactive / On Leave</option>
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary"><i class="fa-solid fa-user-plus me-1"></i> Register Officer</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ══════════════════════ MODAL: EDIT OFFICER (GET METHOD) ══════════════════════ -->
<div class="modal fade" id="editOfficerModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa-solid fa-pen-to-square text-info me-2"></i> Edit Officer Profile (GET Method)</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <!-- SUBMIT VIA GET METHOD -->
      <form method="GET" action="manage-officers.php">
        <input type="hidden" name="action" value="update">
        <input type="hidden" name="id" id="editOffId">
        <div class="modal-body" style="padding:24px;">
          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label">Officer Full Name *</label>
              <input type="text" class="form-control" name="name" id="editOffName" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Rank / Designation *</label>
              <select class="form-select" name="designation" id="editOffDesig" required>
                <option value="Inspector">Inspector</option>
                <option value="Investigation Officer">Investigation Officer</option>
                <option value="Senior Officer">Senior Officer</option>
                <option value="Deputy Commissioner">Deputy Commissioner</option>
              </select>
            </div>
          </div>
          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label">Department *</label>
              <select class="form-select" name="department_id" id="editOffDept" required>
                <?php foreach ($all_departments as $d): ?>
                  <option value="<?= $d['department_id'] ?>"><?= htmlspecialchars($d['department_name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Official Email *</label>
              <input type="email" class="form-control" name="email" id="editOffEmail" required>
            </div>
          </div>
          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label">Mobile Contact</label>
              <input type="tel" class="form-control" name="phone" id="editOffPhone">
            </div>
            <div class="col-md-6">
              <label class="form-label">Duty Status *</label>
              <select class="form-select" name="status" id="editOffStatus" required>
                <option value="Active">Active</option>
                <option value="Inactive">Inactive</option>
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-info text-white"><i class="fa-solid fa-check me-1"></i> Update via GET</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ══════════════════════ MODAL: VIEW OFFICER PROFILE ══════════════════════ -->
<div class="modal fade" id="viewOfficerModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa-solid fa-id-badge text-primary me-2"></i> Officer Dossier</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" style="padding:24px;">
        <div class="d-flex align-items-center gap-3 mb-4">
          <img src="" id="profAvatar" style="width:72px;height:72px;border-radius:16px;object-fit:cover;border:2px solid var(--border);" alt="">
          <div>
            <div class="fw-bold" style="font-size:1.1rem;" id="profName">Officer Name</div>
            <div class="extra-small text-muted" id="profBadge">ACO-401</div>
            <div class="mt-1"><span class="pill pill-active pill-live" id="profStatus">Active Duty</span></div>
          </div>
        </div>
        <div class="row g-2 mb-3">
          <div class="col-6"><div style="background:var(--bg);border:1px solid var(--border);border-radius:10px;padding:10px;"><div class="extra-small text-muted">RANK</div><div class="fw-semibold extra-small" id="profRank">Inspector</div></div></div>
          <div class="col-6"><div style="background:var(--bg);border:1px solid var(--border);border-radius:10px;padding:10px;"><div class="extra-small text-muted">DEPT.</div><div class="fw-semibold extra-small text-truncate" id="profDept">Public Works</div></div></div>
          <div class="col-6"><div style="background:var(--bg);border:1px solid var(--border);border-radius:10px;padding:10px;"><div class="extra-small text-muted">EMAIL</div><div class="fw-semibold extra-small font-monospace" id="profEmail">officer@acc.gov</div></div></div>
          <div class="col-6"><div style="background:var(--bg);border:1px solid var(--border);border-radius:10px;padding:10px;"><div class="extra-small text-muted">ACTIVE CASES</div><div class="fw-semibold extra-small text-primary" id="profCases">0 Cases</div></div></div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="assets/js/main.js"></script>
<script>
  function showOfficerProfile(name, badge, rank, dept, email, phone, cases, avatar, status) {
    $('#profName').text(name);
    $('#profBadge').text(badge);
    $('#profRank').text(rank);
    $('#profDept').text(dept);
    $('#profEmail').text(email);
    $('#profCases').text(cases + ' Cases Assigned');
    $('#profAvatar').attr('src', avatar);
    $('#profStatus').text(status);
    const modal = new bootstrap.Modal(document.getElementById('viewOfficerModal'));
    modal.show();
  }

  function openEditOfficerModal(id, name, email, phone, desig, deptId, status) {
    $('#editOffId').val(id);
    $('#editOffName').val(name);
    $('#editOffEmail').val(email);
    $('#editOffPhone').val(phone);
    $('#editOffDesig').val(desig);
    $('#editOffDept').val(deptId);
    $('#editOffStatus').val(status);
    const modal = new bootstrap.Modal(document.getElementById('editOfficerModal'));
    modal.show();
  }

  // Client side keystroke filter
  document.getElementById('officerSearchInput')?.addEventListener('keyup', function() {
    const val = this.value.toLowerCase();
    document.querySelectorAll('.officer-item').forEach(card => {
      const text = card.innerText.toLowerCase();
      card.style.display = text.includes(val) ? '' : 'none';
    });
  });
</script>
</body>
</html>
