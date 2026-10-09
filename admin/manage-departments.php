<?php
require_once '../db.php';

$success_msg = '';
$error_msg = '';

// ═══════════════════ 1. DELETE ACTION (GET METHOD) ═══════════════════
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $del_id = intval($_GET['id']);
    if ($del_id > 0) {
        // Check if officers are currently assigned to this department
        $check_off = mysqli_query($conn, "SELECT COUNT(*) FROM officers WHERE department_id = {$del_id}");
        $off_count = mysqli_fetch_row($check_off)[0];
        if ($off_count > 0) {
            $error_msg = "Cannot delete department: {$off_count} officer(s) are assigned to it. Reassign officers first.";
        } else {
            $del_stmt = mysqli_prepare($conn, "DELETE FROM departments WHERE department_id = ?");
            if ($del_stmt) {
                mysqli_stmt_bind_param($del_stmt, "i", $del_id);
                if (mysqli_stmt_execute($del_stmt)) {
                    $success_msg = "Department #DEPT-{$del_id} removed successfully.";
                } else {
                    $error_msg = "Failed to delete department: " . mysqli_error($conn);
                }
                mysqli_stmt_close($del_stmt);
            }
        }
    }
}

// ═══════════════════ 2. UPDATE ACTION (GET METHOD) ═══════════════════
if (isset($_GET['action']) && $_GET['action'] === 'update' && isset($_GET['id'])) {
    $up_id = intval($_GET['id']);
    $dept_name = trim($_GET['department_name'] ?? '');
    $description = trim($_GET['description'] ?? '');
    $status = trim($_GET['status'] ?? 'Active');

    if ($up_id > 0 && !empty($dept_name)) {
        $up_stmt = mysqli_prepare($conn, "UPDATE departments SET department_name = ?, description = ?, status = ? WHERE department_id = ?");
        if ($up_stmt) {
            mysqli_stmt_bind_param($up_stmt, "sssi", $dept_name, $description, $status, $up_id);
            if (mysqli_stmt_execute($up_stmt)) {
                $success_msg = "Department '{$dept_name}' updated successfully via GET action!";
            } else {
                $error_msg = "Update failed: " . mysqli_error($conn);
            }
            mysqli_stmt_close($up_stmt);
        }
    } else {
        $error_msg = "Department name is required.";
    }
}

// ═══════════════════ 3. TOGGLE STATUS (GET METHOD) ═══════════════════
if (isset($_GET['action']) && $_GET['action'] === 'toggle_status' && isset($_GET['id'])) {
    $tog_id = intval($_GET['id']);
    $new_status = ($_GET['status'] ?? '') === 'Active' ? 'Inactive' : 'Active';
    $tog_stmt = mysqli_prepare($conn, "UPDATE departments SET status = ? WHERE department_id = ?");
    if ($tog_stmt) {
        mysqli_stmt_bind_param($tog_stmt, "si", $new_status, $tog_id);
        if (mysqli_stmt_execute($tog_stmt)) {
            $success_msg = "Department #DEPT-{$tog_id} status set to '{$new_status}'.";
        }
        mysqli_stmt_close($tog_stmt);
    }
}

// ═══════════════════ 4. ADD DEPARTMENT (POST OR GET) ═══════════════════
if (($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_dept'])) || (isset($_GET['action']) && $_GET['action'] === 'add')) {
    $src = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : $_GET;
    $dept_name = trim($src['department_name'] ?? '');
    $description = trim($src['description'] ?? '');
    $status = trim($src['status'] ?? 'Active');

    if (!empty($dept_name)) {
        $check = mysqli_prepare($conn, "SELECT department_id FROM departments WHERE department_name = ? LIMIT 1");
        mysqli_stmt_bind_param($check, "s", $dept_name);
        mysqli_stmt_execute($check);
        mysqli_stmt_store_result($check);
        if (mysqli_stmt_num_rows($check) > 0) {
            $error_msg = "A department with name '{$dept_name}' already exists.";
        } else {
            $ins = mysqli_prepare($conn, "INSERT INTO departments (department_name, description, status, created_at) VALUES (?, ?, ?, NOW())");
            mysqli_stmt_bind_param($ins, "sss", $dept_name, $description, $status);
            if (mysqli_stmt_execute($ins)) {
                $new_id = mysqli_insert_id($conn);
                $success_msg = "Department '{$dept_name}' (#DEPT-{$new_id}) registered successfully!";
            } else {
                $error_msg = "Failed to register department: " . mysqli_error($conn);
            }
            mysqli_stmt_close($ins);
        }
        mysqli_stmt_close($check);
    } else {
        $error_msg = "Department name is mandatory.";
    }
}

// ═══════════════════ 5. SEARCHING VIA GET METHOD ═══════════════════
$search = trim($_GET['search'] ?? '');
$query = "SELECT d.*, 
                 COUNT(DISTINCT o.officer_id) AS officer_count,
                 COUNT(DISTINCT c.complaint_id) AS complaint_count
          FROM departments d
          LEFT JOIN officers o ON d.department_id = o.department_id
          LEFT JOIN complaints c ON o.officer_id = c.assigned_officer
          WHERE 1=1";

$params = [];
$types = "";

if (!empty($search)) {
    $search_pattern = "%{$search}%";
    $query .= " AND (d.department_name LIKE ? OR d.description LIKE ? OR d.department_id LIKE ?)";
    $types .= "sss";
    $params[] = $search_pattern;
    $params[] = $search_pattern;
    $params[] = $search_pattern;
}

$query .= " GROUP BY d.department_id ORDER BY d.department_id ASC";

$stmt = mysqli_prepare($conn, $query);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$departments_res = mysqli_stmt_get_result($stmt);
$departments = [];
if ($departments_res) {
    while ($row = mysqli_fetch_assoc($departments_res)) {
        $departments[] = $row;
    }
}
mysqli_stmt_close($stmt);

// Dynamic KPIs
$kpi_dept_total = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM departments"))[0] ?? 0;
$kpi_dept_active = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM departments WHERE status = 'Active'"))[0] ?? 0;
$kpi_total_officers = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM officers"))[0] ?? 0;
$kpi_total_complaints = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM complaints"))[0] ?? 0;
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Monitored Departments — CCMS Admin</title>
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
      <a href="assign-complaints.php" class="sb-link"><div class="icon-wrap"><i class="fa-solid fa-user-tag"></i></div><span>Assign Complaints</span></a>
      
      <div class="sb-section-label">Personnel</div>
      <a href="manage-users.php" class="sb-link"><div class="icon-wrap"><i class="fa-solid fa-users"></i></div><span>Manage Users</span></a>
      <a href="manage-officers.php" class="sb-link"><div class="icon-wrap"><i class="fa-solid fa-user-shield"></i></div><span>Manage Officers</span></a>
      
      <div class="sb-section-label">System Masters</div>
      <a href="manage-departments.php" class="sb-link active"><div class="icon-wrap"><i class="fa-solid fa-building-columns"></i></div><span>Departments</span><span class="sb-badge"><?= $kpi_dept_total ?></span></a>
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
        <form method="GET" action="manage-departments.php" class="search-wrap d-none d-md-flex align-items-center m-0">
          <i class="fa-solid fa-magnifying-glass"></i>
          <input type="text" name="search" id="deptSearchInput" placeholder="Search department name or description..." value="<?= htmlspecialchars($search) ?>">
          <?php if (!empty($search)): ?>
            <a href="manage-departments.php" class="text-muted ms-2 me-1" title="Clear Search"><i class="fa-solid fa-xmark"></i></a>
          <?php endif; ?>
        </form>
      </div>
      <div class="nav-actions">
        <div class="online-chip d-none d-xl-flex"><span class="online-dot"></span>Oversight Active</div>
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
          <div class="page-breadcrumb"><a href="index.php"><i class="fa-solid fa-house"></i></a><i class="fa-solid fa-chevron-right"></i><span>Departments</span></div>
          <h1 class="page-title">Monitored Government Departments</h1>
          <p class="page-subtitle">Dynamic registry of government agencies. GET-based update, status toggle, search &amp; delete.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
          <button class="btn btn-ghost btn-export-pdf" data-doc-name="Departments_Directory_PDF"><i class="fa-solid fa-file-pdf" style="color:var(--rose-500);"></i> Export PDF</button>
          <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDeptModal"><i class="fa-solid fa-plus"></i> Add Department</button>
        </div>
      </div>

      <!-- DYNAMIC KPIS -->
      <div class="kpi-grid">
        <div class="kpi-card indigo">
          <div class="kpi-icon"><i class="fa-solid fa-building-columns"></i></div>
          <div class="kpi-value counter-value"><?= $kpi_dept_total ?></div>
          <div class="kpi-label">Monitored Departments</div>
          <div class="kpi-trend up"><i class="fa-solid fa-check"></i> Connected</div>
        </div>
        <div class="kpi-card emerald">
          <div class="kpi-icon"><i class="fa-solid fa-shield-check"></i></div>
          <div class="kpi-value counter-value"><?= $kpi_dept_active ?></div>
          <div class="kpi-label">Active Surveillance</div>
          <div class="kpi-trend up"><i class="fa-solid fa-bolt"></i> Live Feed</div>
        </div>
        <div class="kpi-card cyan">
          <div class="kpi-icon"><i class="fa-solid fa-user-shield"></i></div>
          <div class="kpi-value counter-value"><?= $kpi_total_officers ?></div>
          <div class="kpi-label">Total Officers Deployed</div>
          <div class="kpi-trend up"><i class="fa-solid fa-users"></i> Assigned Staff</div>
        </div>
        <div class="kpi-card amber">
          <div class="kpi-icon"><i class="fa-solid fa-folder-tree"></i></div>
          <div class="kpi-value counter-value"><?= $kpi_total_complaints ?></div>
          <div class="kpi-label">Total Complaints Logged</div>
          <div class="kpi-trend up"><i class="fa-solid fa-chart-simple"></i> System Wide</div>
        </div>
      </div>

      <!-- DYNAMIC DEPARTMENT CARDS GRID -->
      <div class="row g-3" id="deptGrid">
        <?php if (!empty($departments)): ?>
          <?php foreach ($departments as $idx => $dept): 
            $did = $dept['department_id'];
            $is_active = $dept['status'] === 'Active';
            $banner_grads = [
              'linear-gradient(135deg,#f43f5e,#be123c)',
              'linear-gradient(135deg,#6366f1,#4338ca)',
              'linear-gradient(135deg,#10b981,#059669)',
              'linear-gradient(135deg,#0284c7,#0369a1)',
              'linear-gradient(135deg,#8b5cf6,#6d28d9)'
            ];
            $banner_bg = $banner_grads[$idx % count($banner_grads)];
          ?>
          <div class="col-lg-4 col-md-6 dept-item">
            <div class="dept-card">
              <div class="dept-card-banner" style="background:<?= $banner_bg ?>;">
                <div class="d-flex align-items-center mb-1">
                  <i class="fa-solid fa-landmark" style="font-size:1.4rem;"></i>
                  <div class="ms-2">
                    <div class="fw-bold" style="font-size:1.05rem;"><?= htmlspecialchars($dept['department_name']) ?></div>
                    <div class="extra-small" style="opacity:.85;font-family:var(--font-mono);">ID: #DEPT-<?= str_pad($did, 3, '0', STR_PAD_LEFT) ?></div>
                  </div>
                </div>
                <!-- STATUS TOGGLE VIA GET -->
                <a href="manage-departments.php?action=toggle_status&id=<?= $did ?>&status=<?= $dept['status'] ?>" 
                   title="Click to toggle status via GET" 
                   class="text-decoration-none">
                  <span class="pill <?= $is_active ? 'pill-active pill-live' : 'pill-blocked' ?>" style="font-size:0.7rem;font-weight:800;margin-top:8px;">
                    <?= htmlspecialchars($dept['status']) ?>
                  </span>
                </a>
              </div>
              <div class="dept-card-body">
                <div class="mb-3">
                  <div class="extra-small text-muted mb-1">DESCRIPTION & SCOPE:</div>
                  <div class="extra-small text-secondary" style="line-height:1.5; min-height: 38px;">
                    <?= htmlspecialchars($dept['description'] ?? 'Monitored under national integrity oversight protocols.') ?>
                  </div>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                  <div class="extra-small text-muted"><i class="fa-solid fa-user-shield me-1"></i>Officers Assigned:</div>
                  <strong class="extra-small text-primary font-monospace"><?= $dept['officer_count'] ?> Officers</strong>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <div class="extra-small text-muted"><i class="fa-solid fa-folder-open me-1"></i>Active Inquiries:</div>
                  <span style="background:rgba(99,102,241,0.12);color:var(--indigo-600);padding:2px 8px;border-radius:6px;font-weight:800;font-size:0.8rem;">
                    <?= $dept['complaint_count'] ?> Cases
                  </span>
                </div>
              </div>
              <div class="dept-card-footer d-flex justify-content-between align-items-center">
                <a href="manage-complaints.php?search=<?= urlencode($dept['department_name']) ?>" class="btn btn-sm btn-ghost">
                  View Cases <i class="fa-solid fa-arrow-right ms-1"></i>
                </a>
                <div class="d-flex gap-1 align-items-center">
                  <!-- EDIT DEPARTMENT VIA GET -->
                  <button class="btn btn-ghost btn-icon btn-sm" onclick="openEditDeptModal('<?= $did ?>', '<?= addslashes($dept['department_name']) ?>', '<?= addslashes($dept['description'] ?? '') ?>', '<?= $dept['status'] ?>')" title="Edit Department (GET Method)">
                    <i class="fa-solid fa-pen-to-square text-info"></i>
                  </button>

                  <!-- DELETE DEPARTMENT VIA GET -->
                  <a href="manage-departments.php?action=delete&id=<?= $did ?>" 
                     onclick="return confirm('Confirm Deletion: Delete Department <?= addslashes($dept['department_name']) ?> (#DEPT-<?= $did ?>) via GET request?');" 
                     class="btn btn-ghost btn-icon btn-sm text-danger" 
                     title="Delete Department (GET Method)">
                    <i class="fa-solid fa-trash"></i>
                  </a>
                </div>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="col-12 text-center py-5">
            <i class="fa-solid fa-building-circle-xmark text-muted mb-2" style="font-size:3rem;"></i>
            <h5 class="text-muted">No departments found matching your search</h5>
            <a href="manage-departments.php" class="btn btn-sm btn-outline-primary mt-2">Clear Search</a>
          </div>
        <?php endif; ?>
      </div>
    </main>
  </div>
</div>

<!-- ══════════════════════ MODAL: ADD DEPARTMENT ══════════════════════ -->
<div class="modal fade" id="addDeptModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa-solid fa-building-columns text-primary me-2"></i> Register Monitored Department</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" action="manage-departments.php">
        <input type="hidden" name="add_dept" value="1">
        <div class="modal-body" style="padding: 24px;">
          <div class="mb-3">
            <label class="form-label">Department Name *</label>
            <div class="input-icon-group">
              <i class="fa-solid fa-landmark input-icon"></i>
              <input type="text" class="form-control" name="department_name" placeholder="e.g. Public Works & Transport" required>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Description &amp; Operational Jurisdiction</label>
            <textarea class="form-control" name="description" rows="3" placeholder="Define the ministry, functions, and key oversight scope..."></textarea>
          </div>
          <div class="mb-0">
            <label class="form-label">Surveillance Status</label>
            <select class="form-select" name="status">
              <option value="Active" selected>Active Surveillance</option>
              <option value="Inactive">Inactive / Suspended</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check me-1"></i> Register Department</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ══════════════════════ MODAL: EDIT DEPARTMENT (GET METHOD) ══════════════════════ -->
<div class="modal fade" id="editDeptModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa-solid fa-pen-to-square text-info me-2"></i> Edit Department (GET Method)</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <!-- SUBMISSION VIA GET METHOD -->
      <form method="GET" action="manage-departments.php">
        <input type="hidden" name="action" value="update">
        <input type="hidden" name="id" id="editDeptId">
        <div class="modal-body" style="padding: 24px;">
          <div class="mb-3">
            <label class="form-label">Department Name *</label>
            <input type="text" class="form-control" name="department_name" id="editDeptName" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Description &amp; Oversight Scope</label>
            <textarea class="form-control" name="description" id="editDeptDesc" rows="3"></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label">Oversight Status *</label>
            <select class="form-select" name="status" id="editDeptStatus" required>
              <option value="Active">Active Surveillance</option>
              <option value="Inactive">Inactive / Suspended</option>
            </select>
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

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="assets/js/main.js"></script>
<script>
  function openEditDeptModal(id, name, desc, status) {
    $('#editDeptId').val(id);
    $('#editDeptName').val(name);
    $('#editDeptDesc').val(desc);
    $('#editDeptStatus').val(status);
    const modal = new bootstrap.Modal(document.getElementById('editDeptModal'));
    modal.show();
  }

  // Client-side quick filter
  document.getElementById('deptSearchInput')?.addEventListener('keyup', function () {
    const val = this.value.toLowerCase();
    document.querySelectorAll('.dept-item').forEach(card => {
      const text = card.innerText.toLowerCase();
      card.style.display = text.includes(val) ? '' : 'none';
    });
  });
</script>
</body>
</html>
