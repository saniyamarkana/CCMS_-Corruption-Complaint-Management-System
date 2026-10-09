<?php
require_once '../db.php';

$success_msg = '';
$error_msg = '';

// ═══════════════════ 1. ASSIGN OFFICER ACTION (GET METHOD) ═══════════════════
if (isset($_GET['action']) && $_GET['action'] === 'assign' && isset($_GET['complaint_id']) && isset($_GET['officer_id'])) {
    $cid = intval($_GET['complaint_id']);
    $oid = intval($_GET['officer_id']);

    if ($cid > 0 && $oid > 0) {
        $stmt = mysqli_prepare($conn, "UPDATE complaints SET assigned_officer = ?, status = 'Assigned' WHERE complaint_id = ?");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ii", $oid, $cid);
            if (mysqli_stmt_execute($stmt)) {
                $off_name = mysqli_fetch_row(mysqli_query($conn, "SELECT name FROM officers WHERE officer_id = {$oid}"))[0] ?? 'Officer';
                $success_msg = "Complaint #CCMS-{$cid} assigned to {$off_name} successfully via GET action!";
            } else {
                $error_msg = "Assignment failed: " . mysqli_error($conn);
            }
            mysqli_stmt_close($stmt);
        }
    }
}

// ═══════════════════ 2. UNASSIGN ACTION (GET METHOD) ═══════════════════
if (isset($_GET['action']) && $_GET['action'] === 'unassign' && isset($_GET['id'])) {
    $un_id = intval($_GET['id']);
    if ($un_id > 0) {
        $stmt = mysqli_prepare($conn, "UPDATE complaints SET assigned_officer = NULL, status = 'Submitted' WHERE complaint_id = ?");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $un_id);
            if (mysqli_stmt_execute($stmt)) {
                $success_msg = "Complaint #CCMS-{$un_id} unassigned and returned to pending queue.";
            }
            mysqli_stmt_close($stmt);
        }
    }
}

// ═══════════════════ 3. DELETE ACTION (GET METHOD) ═══════════════════
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $del_id = intval($_GET['id']);
    if ($del_id > 0) {
        $del_stmt = mysqli_prepare($conn, "DELETE FROM complaints WHERE complaint_id = ?");
        if ($del_stmt) {
            mysqli_stmt_bind_param($del_stmt, "i", $del_id);
            if (mysqli_stmt_execute($del_stmt)) {
                $success_msg = "Complaint #CCMS-{$del_id} removed via GET request.";
            }
            mysqli_stmt_close($del_stmt);
        }
    }
}

// ═══════════════════ 4. SEARCHING & FILTERING VIA GET ═══════════════════
$search = trim($_GET['search'] ?? '');
$filter_scope = trim($_GET['scope'] ?? 'all'); // 'unassigned', 'assigned', 'all'

$query = "SELECT c.*, u.name AS complainant_name, cat.category_name, o.name AS officer_name, o.designation AS officer_designation, d.department_name
          FROM complaints c
          LEFT JOIN users u ON c.user_id = u.user_id
          LEFT JOIN categories cat ON c.category_id = cat.category_id
          LEFT JOIN officers o ON c.assigned_officer = o.officer_id
          LEFT JOIN departments d ON o.department_id = d.department_id
          WHERE 1=1";

$params = [];
$types = "";

if (!empty($search)) {
    $pattern = "%{$search}%";
    $query .= " AND (c.complaint_id LIKE ? OR c.title LIKE ? OR c.description LIKE ? OR c.location LIKE ? OR u.name LIKE ? OR cat.category_name LIKE ? OR o.name LIKE ?)";
    $types .= "sssssss";
    for ($i = 0; $i < 7; $i++) {
        $params[] = $pattern;
    }
}

if ($filter_scope === 'unassigned') {
    $query .= " AND c.assigned_officer IS NULL";
} elseif ($filter_scope === 'assigned') {
    $query .= " AND c.assigned_officer IS NOT NULL";
}

$query .= " ORDER BY (c.assigned_officer IS NULL) DESC, c.complaint_id DESC";

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

// Fetch all active officers
$officers_res = mysqli_query($conn, "SELECT officer_id, name, designation, department_id FROM officers WHERE status = 'Active' ORDER BY name ASC");
$active_officers = [];
while ($or = mysqli_fetch_assoc($officers_res)) {
    $active_officers[] = $or;
}

// Dynamic KPIs
$kpi_unassigned = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM complaints WHERE assigned_officer IS NULL"))[0] ?? 0;
$kpi_assigned = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM complaints WHERE assigned_officer IS NOT NULL"))[0] ?? 0;
$kpi_active_officers = count($active_officers);
$kpi_total_cases = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM complaints"))[0] ?? 0;
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Assign Complaints & Triage Dispatch — CCMS Admin</title>
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
      <a href="manage-complaints.php" class="sb-link"><div class="icon-wrap"><i class="fa-solid fa-folder-open"></i></div><span>Manage Complaints</span></a>
      <a href="assign-complaints.php" class="sb-link active"><div class="icon-wrap"><i class="fa-solid fa-user-tag"></i></div><span>Assign Complaints</span><span class="sb-badge amber"><?= $kpi_unassigned ?></span></a>
      
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
    <div class="sb-footer"><div class="sb-admin-card"><img src="https://images.unsplash.com/photo-1560250097-0b93528c311a?auto=format&fit=crop&w=80&q=80" alt="Admin" class="sb-avatar"><div class="sb-admin-info"><div class="sb-admin-name">Super Admin</div><div class="sb-admin-role">System Overseer</div></div></div></div>
  </aside>

  <!-- ══════════════════════ MAIN CONTENT ══════════════════════ -->
  <div id="main-content">
    <nav class="top-navbar">
      <div class="d-flex align-items-center gap-3">
        <button class="btn-toggle" id="sidebarToggle"><i class="fa-solid fa-bars-staggered"></i></button>
        <!-- SEARCH VIA GET METHOD -->
        <form method="GET" action="assign-complaints.php" class="search-wrap d-none d-md-flex align-items-center m-0">
          <i class="fa-solid fa-magnifying-glass"></i>
          <input type="text" name="search" id="assignSearch" placeholder="Search case ID, title, department, or officer..." value="<?= htmlspecialchars($search) ?>">
          <?php if (!empty($search)): ?>
            <a href="assign-complaints.php" class="text-muted ms-2 me-1" title="Clear Search"><i class="fa-solid fa-xmark"></i></a>
          <?php endif; ?>
        </form>
      </div>
      <div class="nav-actions">
        <div class="online-chip d-none d-xl-flex"><span class="online-dot"></span>Triage Engine Active</div>
        <button class="nav-icon-btn" id="themeToggle"><i class="fa-solid fa-moon" id="themeIcon"></i></button>
        <div class="dropdown"><div class="nav-profile-btn dropdown-toggle" data-bs-toggle="dropdown"><img src="https://images.unsplash.com/photo-1560250097-0b93528c311a?auto=format&fit=crop&w=80&q=80" class="nav-avatar" alt="Admin"><div class="d-none d-md-block"><div class="nav-profile-name">Inspector Admin</div><div class="nav-profile-role">Super Administrator</div></div></div>
          <ul class="dropdown-menu dropdown-menu-end">
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
          <div class="page-breadcrumb"><a href="index.php"><i class="fa-solid fa-house"></i></a><i class="fa-solid fa-chevron-right"></i><span>Triage Dispatch</span></div>
          <h1 class="page-title">Investigations Assignment Center</h1>
          <p class="page-subtitle">Fully dynamic officer allocation console. Real-time GET-based assignment, unassignment, search &amp; removal.</p>
        </div>
        <div class="d-flex gap-2">
          <a href="manage-complaints.php" class="btn btn-outline-primary"><i class="fa-solid fa-folder-open"></i> Full Complaints Docket</a>
        </div>
      </div>

      <!-- DYNAMIC KPIS -->
      <div class="kpi-grid">
        <div class="kpi-card amber">
          <div class="kpi-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
          <div class="kpi-value counter-value"><?= $kpi_unassigned ?></div>
          <div class="kpi-label">Awaiting Officer Allocation</div>
          <div class="kpi-trend down"><i class="fa-solid fa-bolt"></i> Needs Assignment</div>
        </div>
        <div class="kpi-card emerald">
          <div class="kpi-icon"><i class="fa-solid fa-user-check"></i></div>
          <div class="kpi-value counter-value"><?= $kpi_assigned ?></div>
          <div class="kpi-label">Active Cases Assigned</div>
          <div class="kpi-trend up"><i class="fa-solid fa-check"></i> In Progress</div>
        </div>
        <div class="kpi-card indigo">
          <div class="kpi-icon"><i class="fa-solid fa-user-shield"></i></div>
          <div class="kpi-value counter-value"><?= $kpi_active_officers ?></div>
          <div class="kpi-label">Available Officers</div>
          <div class="kpi-trend up"><i class="fa-solid fa-shield"></i> Active Duty</div>
        </div>
        <div class="kpi-card cyan">
          <div class="kpi-icon"><i class="fa-solid fa-folder-tree"></i></div>
          <div class="kpi-value counter-value"><?= $kpi_total_cases ?></div>
          <div class="kpi-label">Total Dockets Logged</div>
          <div class="kpi-trend up"><i class="fa-solid fa-database"></i> Database Total</div>
        </div>
      </div>

      <!-- FILTER BAR VIA GET -->
      <form method="GET" action="assign-complaints.php" class="filter-bar">
        <div class="row g-3 align-items-end">
          <div class="col-md-6">
            <label class="form-label">Search Query</label>
            <div class="input-icon-group">
              <i class="fa-solid fa-magnifying-glass input-icon"></i>
              <input type="text" name="search" class="form-control" placeholder="Search case title, citizen name, ID..." value="<?= htmlspecialchars($search) ?>">
            </div>
          </div>
          <div class="col-md-4">
            <label class="form-label">Assignment Scope</label>
            <div class="input-icon-group">
              <i class="fa-solid fa-filter input-icon"></i>
              <select class="form-select" name="scope">
                <option value="all" <?= $filter_scope === 'all' ? 'selected' : '' ?>>All Complaints (<?= $kpi_total_cases ?>)</option>
                <option value="unassigned" <?= $filter_scope === 'unassigned' ? 'selected' : '' ?>>Unassigned Only (<?= $kpi_unassigned ?>)</option>
                <option value="assigned" <?= $filter_scope === 'assigned' ? 'selected' : '' ?>>Assigned Cases (<?= $kpi_assigned ?>)</option>
              </select>
            </div>
          </div>
          <div class="col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-primary flex-grow-1"><i class="fa-solid fa-filter"></i> Apply</button>
            <a href="assign-complaints.php" class="btn btn-ghost" title="Reset Filters"><i class="fa-solid fa-rotate-left"></i></a>
          </div>
        </div>
      </form>

      <!-- ASSIGNMENT TABLE -->
      <div class="card-box">
        <div class="card-box-head">
          <div class="card-box-title"><div class="title-icon"><i class="fa-solid fa-user-tag"></i></div>Complaint Assignment Roster (Showing <?= count($complaints) ?> Cases)</div>
        </div>
        <div class="tbl-wrap">
          <table class="tbl" id="assignTable">
            <thead>
              <tr>
                <th>Case Docket</th>
                <th>Complainant</th>
                <th>Allegation Title &amp; Office</th>
                <th>Category</th>
                <th>Priority</th>
                <th>Assigned Officer &amp; Fast Reassign (GET)</th>
                <th class="text-end no-sort">Actions (GET Method)</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($complaints)): ?>
                <?php foreach ($complaints as $c): 
                  $cid = $c['complaint_id'];
                  $is_assigned = !empty($c['assigned_officer']);
                  $badge_p = match($c['priority']) {
                    'Critical' => 'chip-critical',
                    'High' => 'chip-high',
                    'Medium' => 'chip-medium',
                    default => 'chip-low'
                  };
                ?>
                <tr>
                  <td>
                    <span class="fw-bold" style="color:var(--indigo-600); font-family:var(--font-mono);">#CCMS-<?= $cid ?></span>
                    <div class="extra-small text-muted"><?= date('M d, Y', strtotime($c['created_at'])) ?></div>
                  </td>
                  <td>
                    <div class="fw-semibold" style="font-size:.85rem;"><?= htmlspecialchars($c['complainant_name'] ?? 'Anonymous Whistleblower') ?></div>
                    <div class="extra-small text-muted"><?= htmlspecialchars($c['location'] ?? 'N/A') ?></div>
                  </td>
                  <td style="max-width: 250px;">
                    <div class="fw-semibold text-truncate" style="font-size:.85rem;" title="<?= htmlspecialchars($c['title']) ?>">
                      <?= htmlspecialchars($c['title']) ?>
                    </div>
                    <div class="extra-small text-muted text-truncate"><?= htmlspecialchars($c['description']) ?></div>
                  </td>
                  <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($c['category_name'] ?? 'General') ?></span></td>
                  <td><span class="<?= $badge_p ?>"><?= htmlspecialchars($c['priority']) ?></span></td>
                  <td style="min-width: 220px;">
                    <!-- FAST ASSIGNMENT DROPDOWN VIA GET METHOD -->
                    <form method="GET" action="assign-complaints.php" class="d-flex align-items-center gap-1 m-0">
                      <input type="hidden" name="action" value="assign">
                      <input type="hidden" name="complaint_id" value="<?= $cid ?>">
                      <select class="form-select form-select-sm" name="officer_id" onchange="this.form.submit()" title="Select to immediately assign via GET">
                        <option value="">-- Choose Officer --</option>
                        <?php foreach ($active_officers as $off): ?>
                          <option value="<?= $off['officer_id'] ?>" <?= ($c['assigned_officer'] == $off['officer_id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($off['name']) ?> (<?= htmlspecialchars($off['designation']) ?>)
                          </option>
                        <?php endforeach; ?>
                      </select>
                    </form>
                    <?php if ($is_assigned): ?>
                      <div class="extra-small text-success mt-1">
                        <i class="fa-solid fa-circle-check me-1"></i>Assigned to <?= htmlspecialchars($c['officer_name'] ?? 'Officer') ?>
                      </div>
                    <?php else: ?>
                      <div class="extra-small text-danger mt-1">
                        <i class="fa-solid fa-circle-exclamation me-1"></i>Unallocated — Select above to assign
                      </div>
                    <?php endif; ?>
                  </td>
                  <td class="text-end">
                    <div class="d-inline-flex gap-1 align-items-center">
                      <?php if ($is_assigned): ?>
                        <!-- UNASSIGN VIA GET -->
                        <a href="assign-complaints.php?action=unassign&id=<?= $cid ?>" 
                           onclick="return confirm('Remove officer assignment for #CCMS-<?= $cid ?>?');"
                           class="btn btn-ghost btn-icon btn-sm text-warning" title="Unassign Officer (GET)">
                          <i class="fa-solid fa-user-minus"></i>
                        </a>
                      <?php endif; ?>

                      <!-- DELETE VIA GET -->
                      <a href="assign-complaints.php?action=delete&id=<?= $cid ?>" 
                         onclick="return confirm('Permanently delete complaint #CCMS-<?= $cid ?> via GET?');"
                         class="btn btn-ghost btn-icon btn-sm text-danger" title="Delete Complaint (GET)">
                        <i class="fa-solid fa-trash"></i>
                      </a>
                    </div>
                  </td>
                </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="7" class="text-center py-5">
                    <i class="fa-solid fa-clipboard-check text-muted mb-2" style="font-size:2.5rem;"></i>
                    <h6 class="text-muted">No complaints match your active triage filter</h6>
                    <a href="assign-complaints.php" class="btn btn-sm btn-outline-primary mt-2">Clear Filters</a>
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

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="assets/js/main.js"></script>
<script>
  // Client side quick search
  document.getElementById('assignSearch')?.addEventListener('keyup', function () {
    const val = this.value.toLowerCase();
    document.querySelectorAll('#assignTable tbody tr').forEach(row => {
      const text = row.innerText.toLowerCase();
      row.style.display = text.includes(val) ? '' : 'none';
    });
  });
</script>
</body>
</html>
