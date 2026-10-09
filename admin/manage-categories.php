<?php
require_once '../db.php';

$success_msg = '';
$error_msg = '';

// ═══════════════════ 1. DELETE ACTION (GET METHOD) ═══════════════════
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $del_id = intval($_GET['id']);
    if ($del_id > 0) {
        // Check if complaints are linked to this category
        $chk_cases = mysqli_query($conn, "SELECT COUNT(*) FROM complaints WHERE category_id = {$del_id}");
        $case_count = mysqli_fetch_row($chk_cases)[0];
        if ($case_count > 0) {
            $error_msg = "Cannot delete category: {$case_count} complaint(s) are linked to it. Reassign or resolve those complaints first.";
        } else {
            $del_stmt = mysqli_prepare($conn, "DELETE FROM categories WHERE category_id = ?");
            if ($del_stmt) {
                mysqli_stmt_bind_param($del_stmt, "i", $del_id);
                if (mysqli_stmt_execute($del_stmt)) {
                    $success_msg = "Category #CAT-{$del_id} deleted successfully via GET request.";
                } else {
                    $error_msg = "Failed to delete category: " . mysqli_error($conn);
                }
                mysqli_stmt_close($del_stmt);
            }
        }
    }
}

// ═══════════════════ 2. UPDATE ACTION (GET METHOD) ═══════════════════
if (isset($_GET['action']) && $_GET['action'] === 'update' && isset($_GET['id'])) {
    $up_id = intval($_GET['id']);
    $cat_name = trim($_GET['category_name'] ?? '');
    $description = trim($_GET['description'] ?? '');
    $status = trim($_GET['status'] ?? 'Active');

    if ($up_id > 0 && !empty($cat_name)) {
        $up_stmt = mysqli_prepare($conn, "UPDATE categories SET category_name = ?, description = ?, status = ? WHERE category_id = ?");
        if ($up_stmt) {
            mysqli_stmt_bind_param($up_stmt, "sssi", $cat_name, $description, $status, $up_id);
            if (mysqli_stmt_execute($up_stmt)) {
                $success_msg = "Category '{$cat_name}' updated successfully via GET action!";
            } else {
                $error_msg = "Update failed: " . mysqli_error($conn);
            }
            mysqli_stmt_close($up_stmt);
        }
    } else {
        $error_msg = "Category name is required.";
    }
}

// ═══════════════════ 3. STATUS TOGGLE (GET METHOD) ═══════════════════
if (isset($_GET['action']) && $_GET['action'] === 'toggle_status' && isset($_GET['id'])) {
    $tog_id = intval($_GET['id']);
    $new_status = ($_GET['status'] ?? '') === 'Active' ? 'Inactive' : 'Active';
    $tog_stmt = mysqli_prepare($conn, "UPDATE categories SET status = ? WHERE category_id = ?");
    if ($tog_stmt) {
        mysqli_stmt_bind_param($tog_stmt, "si", $new_status, $tog_id);
        if (mysqli_stmt_execute($tog_stmt)) {
            $success_msg = "Category #CAT-{$tog_id} status changed to '{$new_status}'.";
        }
        mysqli_stmt_close($tog_stmt);
    }
}

// ═══════════════════ 4. ADD CATEGORY (POST OR GET) ═══════════════════
if (($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) || (isset($_GET['action']) && $_GET['action'] === 'add')) {
    $src = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : $_GET;
    $cat_name = trim($src['category_name'] ?? '');
    $description = trim($src['description'] ?? '');
    $status = trim($src['status'] ?? 'Active');

    if (!empty($cat_name)) {
        $chk = mysqli_prepare($conn, "SELECT category_id FROM categories WHERE category_name = ? LIMIT 1");
        mysqli_stmt_bind_param($chk, "s", $cat_name);
        mysqli_stmt_execute($chk);
        mysqli_stmt_store_result($chk);
        if (mysqli_stmt_num_rows($chk) > 0) {
            $error_msg = "A category named '{$cat_name}' already exists in ledger.";
        } else {
            $ins = mysqli_prepare($conn, "INSERT INTO categories (category_name, description, status, created_at) VALUES (?, ?, ?, NOW())");
            mysqli_stmt_bind_param($ins, "sss", $cat_name, $description, $status);
            if (mysqli_stmt_execute($ins)) {
                $new_id = mysqli_insert_id($conn);
                $success_msg = "Category '{$cat_name}' (#CAT-{$new_id}) registered successfully!";
            } else {
                $error_msg = "Failed to add category: " . mysqli_error($conn);
            }
            mysqli_stmt_close($ins);
        }
        mysqli_stmt_close($chk);
    } else {
        $error_msg = "Category name is mandatory.";
    }
}

// ═══════════════════ 5. SEARCHING VIA GET METHOD ═══════════════════
$search = trim($_GET['search'] ?? '');
$status_filter = trim($_GET['status_filter'] ?? '');

$query = "SELECT cat.*, COUNT(c.complaint_id) AS cases_logged
          FROM categories cat
          LEFT JOIN complaints c ON cat.category_id = c.category_id
          WHERE 1=1";

$params = [];
$types = "";

if (!empty($search)) {
    $search_pattern = "%{$search}%";
    $query .= " AND (cat.category_name LIKE ? OR cat.description LIKE ? OR cat.category_id LIKE ?)";
    $types .= "sss";
    $params[] = $search_pattern;
    $params[] = $search_pattern;
    $params[] = $search_pattern;
}

if (!empty($status_filter) && $status_filter !== 'all') {
    $query .= " AND cat.status = ?";
    $types .= "s";
    $params[] = $status_filter;
}

$query .= " GROUP BY cat.category_id ORDER BY cat.category_id ASC";

$stmt = mysqli_prepare($conn, $query);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$categories_res = mysqli_stmt_get_result($stmt);
$categories = [];
if ($categories_res) {
    while ($row = mysqli_fetch_assoc($categories_res)) {
        $categories[] = $row;
    }
}
mysqli_stmt_close($stmt);

// Dynamic KPIs
$kpi_cat_total = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM categories"))[0] ?? 0;
$kpi_cat_active = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM categories WHERE status = 'Active'"))[0] ?? 0;
$kpi_total_cases = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM complaints"))[0] ?? 0;
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Categories — CCMS Admin</title>
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
      <div class="sb-section-label">Public Portal</div>
      <a href="../index.php" class="sb-link">
        <div class="icon-wrap"><i class="fa-solid fa-globe"></i></div>
        <span>Citizen Portal</span>
      </a>

      <div class="sb-section-label">Main Overview</div>
      <a href="index.php" class="sb-link"><div class="icon-wrap"><i class="fa-solid fa-gauge-high"></i></div><span>Dashboard</span></a>
      <a href="manage-complaints.php" class="sb-link"><div class="icon-wrap"><i class="fa-solid fa-folder-open"></i></div><span>Manage Complaints</span></a>
      <a href="assign-complaints.php" class="sb-link"><div class="icon-wrap"><i class="fa-solid fa-user-tag"></i></div><span>Assign Complaints</span></a>
      
      <div class="sb-section-label">Personnel</div>
      <a href="manage-users.php" class="sb-link"><div class="icon-wrap"><i class="fa-solid fa-users"></i></div><span>Manage Users</span></a>
      <a href="manage-officers.php" class="sb-link"><div class="icon-wrap"><i class="fa-solid fa-user-shield"></i></div><span>Manage Officers</span></a>
      
      <div class="sb-section-label">System Masters</div>
      <a href="manage-departments.php" class="sb-link"><div class="icon-wrap"><i class="fa-solid fa-building-columns"></i></div><span>Departments</span></a>
      <a href="manage-categories.php" class="sb-link active"><div class="icon-wrap"><i class="fa-solid fa-tags"></i></div><span>Categories</span><span class="sb-badge"><?= $kpi_cat_total ?></span></a>
      
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
        <form method="GET" action="manage-categories.php" class="search-wrap d-none d-md-flex align-items-center m-0">
          <i class="fa-solid fa-magnifying-glass"></i>
          <input type="text" name="search" id="catSearchInput" placeholder="Search category name or description..." value="<?= htmlspecialchars($search) ?>">
          <?php if (!empty($search)): ?>
            <a href="manage-categories.php" class="text-muted ms-2 me-1" title="Clear Search"><i class="fa-solid fa-xmark"></i></a>
          <?php endif; ?>
        </form>
      </div>
      <div class="nav-actions">
        <div class="online-chip d-none d-xl-flex"><span class="online-dot"></span>Categories Live</div>
        <button class="nav-icon-btn" id="themeToggle"><i class="fa-solid fa-moon" id="themeIcon"></i></button>
        <div class="dropdown"><div class="nav-profile-btn dropdown-toggle" data-bs-toggle="dropdown"><img src="https://images.unsplash.com/photo-1560250097-0b93528c311a?auto=format&fit=crop&w=80&q=80" class="nav-avatar" alt="Admin"><div class="d-none d-md-block"><div class="nav-profile-name">Inspector Admin</div><div class="nav-profile-role">Super Administrator</div></div></div>
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
          <div class="page-breadcrumb"><a href="index.php"><i class="fa-solid fa-house"></i></a><i class="fa-solid fa-chevron-right"></i><span>Categories</span></div>
          <h1 class="page-title">Corruption Offense Categories</h1>
          <p class="page-subtitle">Fully dynamic offense taxonomy. GET-based update, status toggle, search &amp; delete.</p>
        </div>
        <div class="d-flex gap-2">
          <button class="btn btn-ghost btn-export-pdf" data-doc-name="Category_Ledger_PDF"><i class="fa-solid fa-file-pdf text-danger"></i> Export PDF</button>
          <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal"><i class="fa-solid fa-plus"></i> Add Category</button>
        </div>
      </div>

      <!-- DYNAMIC KPIS -->
      <div class="kpi-grid">
        <div class="kpi-card indigo">
          <div class="kpi-icon"><i class="fa-solid fa-tags"></i></div>
          <div class="kpi-value counter-value"><?= $kpi_cat_total ?></div>
          <div class="kpi-label">Active Classifications</div>
          <div class="kpi-trend up"><i class="fa-solid fa-database"></i> Live Ledger</div>
        </div>
        <div class="kpi-card emerald">
          <div class="kpi-icon"><i class="fa-solid fa-circle-check"></i></div>
          <div class="kpi-value counter-value"><?= $kpi_cat_active ?></div>
          <div class="kpi-label">Active for Lodgement</div>
          <div class="kpi-trend up"><i class="fa-solid fa-check"></i> Operational</div>
        </div>
        <div class="kpi-card amber">
          <div class="kpi-icon"><i class="fa-solid fa-file-shield"></i></div>
          <div class="kpi-value counter-value"><?= $kpi_total_cases ?></div>
          <div class="kpi-label">Complaints Categorized</div>
          <div class="kpi-trend up"><i class="fa-solid fa-folder-tree"></i> Total Dockets</div>
        </div>
        <div class="kpi-card rose">
          <div class="kpi-icon"><i class="fa-solid fa-scale-balanced"></i></div>
          <div class="kpi-value counter-value">100%</div>
          <div class="kpi-label">Statutory Compliance</div>
          <div class="kpi-trend up"><i class="fa-solid fa-shield"></i> Verified</div>
        </div>
      </div>

      <!-- SEARCH & FILTER VIA GET -->
      <form method="GET" action="manage-categories.php" class="filter-bar">
        <div class="row g-3 align-items-end">
          <div class="col-md-6">
            <label class="form-label">Search Category Keywords</label>
            <div class="input-icon-group">
              <i class="fa-solid fa-magnifying-glass input-icon"></i>
              <input type="text" name="search" class="form-control" placeholder="Search by name, description, ID..." value="<?= htmlspecialchars($search) ?>">
            </div>
          </div>
          <div class="col-md-4">
            <label class="form-label">Status Filter</label>
            <div class="input-icon-group">
              <i class="fa-solid fa-shield-halved input-icon"></i>
              <select class="form-select" name="status_filter">
                <option value="all">All Statuses</option>
                <option value="Active" <?= $status_filter === 'Active' ? 'selected' : '' ?>>Active</option>
                <option value="Inactive" <?= $status_filter === 'Inactive' ? 'selected' : '' ?>>Inactive</option>
              </select>
            </div>
          </div>
          <div class="col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-primary flex-grow-1"><i class="fa-solid fa-filter"></i> Apply</button>
            <a href="manage-categories.php" class="btn btn-ghost" title="Reset Filters"><i class="fa-solid fa-rotate-left"></i></a>
          </div>
        </div>
      </form>

      <!-- CATEGORIES TABLE -->
      <div class="card-box">
        <div class="card-box-head">
          <div class="card-box-title"><div class="title-icon"><i class="fa-solid fa-tags"></i></div>Category Master Ledger (Showing <?= count($categories) ?> Records)</div>
          <button class="btn btn-ghost btn-sm btn-export-pdf" data-doc-name="Category_Registry_Export"><i class="fa-solid fa-file-pdf text-danger"></i> PDF Export</button>
        </div>
        <div class="tbl-wrap">
          <table class="tbl" id="categoriesTable">
            <thead>
              <tr>
                <th>Classification Code</th>
                <th>Category Name</th>
                <th>Description &amp; Legal Scope</th>
                <th>Cases Logged</th>
                <th>Status</th>
                <th class="text-end no-sort">Actions (GET Method)</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($categories)): ?>
                <?php foreach ($categories as $cat): 
                  $cat_id = $cat['category_id'];
                  $is_active = $cat['status'] === 'Active';
                ?>
                <tr>
                  <td class="fw-bold" style="color:var(--indigo-600); font-family:var(--font-mono);">CAT-<?= str_pad($cat_id, 3, '0', STR_PAD_LEFT) ?></td>
                  <td>
                    <div class="fw-semibold" style="font-size:.9rem;"><?= htmlspecialchars($cat['category_name']) ?></div>
                    <div class="extra-small text-muted">Created: <?= date('M d, Y', strtotime($cat['created_at'])) ?></div>
                  </td>
                  <td style="max-width: 320px;">
                    <div class="extra-small text-secondary" style="line-height:1.5;">
                      <?= htmlspecialchars($cat['description'] ?? 'Standard statutory corruption classification.') ?>
                    </div>
                  </td>
                  <td class="fw-bold text-center">
                    <a href="manage-complaints.php?category_filter=<?= $cat_id ?>" class="badge bg-primary-subtle text-primary border" title="View Complaints">
                      <?= $cat['cases_logged'] ?> Cases
                    </a>
                  </td>
                  <td>
                    <!-- STATUS TOGGLE VIA GET -->
                    <a href="manage-categories.php?action=toggle_status&id=<?= $cat_id ?>&status=<?= $cat['status'] ?>" 
                       title="Click to toggle status via GET" 
                       class="text-decoration-none">
                      <span class="pill <?= $is_active ? 'pill-active pill-live' : 'pill-blocked' ?>">
                        <?= htmlspecialchars($cat['status']) ?>
                      </span>
                    </a>
                  </td>
                  <td class="text-end">
                    <div class="d-inline-flex gap-1 align-items-center">
                      <!-- EDIT CATEGORY VIA GET -->
                      <button class="btn btn-ghost btn-icon btn-sm" onclick="openEditCategoryModal('<?= $cat_id ?>', '<?= addslashes($cat['category_name']) ?>', '<?= addslashes($cat['description'] ?? '') ?>', '<?= $cat['status'] ?>')" title="Edit Category (GET Method)">
                        <i class="fa-solid fa-pen-to-square text-info"></i>
                      </button>

                      <!-- DELETE CATEGORY VIA GET -->
                      <a href="manage-categories.php?action=delete&id=<?= $cat_id ?>" 
                         onclick="return confirm('Confirm Deletion: Delete Category <?= addslashes($cat['category_name']) ?> (#CAT-<?= $cat_id ?>) via GET request?');" 
                         class="btn btn-ghost btn-icon btn-sm text-danger" 
                         title="Delete Category (GET Method)">
                        <i class="fa-solid fa-trash"></i>
                      </a>
                    </div>
                  </td>
                </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="6" class="text-center py-5">
                    <i class="fa-solid fa-tag text-muted mb-2" style="font-size:2.5rem;"></i>
                    <h6 class="text-muted">No categories match your search criteria</h6>
                    <a href="manage-categories.php" class="btn btn-sm btn-outline-primary mt-2">Clear Filters</a>
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

<!-- ══════════════════════ MODAL: ADD CATEGORY ══════════════════════ -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa-solid fa-tag text-primary me-2"></i> Register Corruption Category</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" action="manage-categories.php">
        <input type="hidden" name="add_category" value="1">
        <div class="modal-body" style="padding: 24px;">
          <div class="mb-3">
            <label class="form-label">Category Name *</label>
            <div class="input-icon-group">
              <i class="fa-solid fa-tag input-icon"></i>
              <input type="text" class="form-control" name="category_name" placeholder="e.g. Kickbacks in Tenders" required>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Offense Description &amp; Evidentiary Scope</label>
            <textarea class="form-control" name="description" rows="3" placeholder="Define legal triggers and particulars..."></textarea>
          </div>
          <div class="mb-0">
            <label class="form-label">Status</label>
            <select class="form-select" name="status">
              <option value="Active" selected>Active</option>
              <option value="Inactive">Inactive</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check me-1"></i> Register Category</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ══════════════════════ MODAL: EDIT CATEGORY (GET METHOD) ══════════════════════ -->
<div class="modal fade" id="editCategoryModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa-solid fa-pen-to-square text-info me-2"></i> Edit Category (GET Method)</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <!-- SUBMIT VIA GET METHOD -->
      <form method="GET" action="manage-categories.php">
        <input type="hidden" name="action" value="update">
        <input type="hidden" name="id" id="editCatId">
        <div class="modal-body" style="padding: 24px;">
          <div class="mb-3">
            <label class="form-label">Category Name *</label>
            <input type="text" class="form-control" name="category_name" id="editCatName" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Description &amp; Scope</label>
            <textarea class="form-control" name="description" id="editCatDesc" rows="3"></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label">Status *</label>
            <select class="form-select" name="status" id="editCatStatus" required>
              <option value="Active">Active</option>
              <option value="Inactive">Inactive</option>
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
  function openEditCategoryModal(id, name, desc, status) {
    $('#editCatId').val(id);
    $('#editCatName').val(name);
    $('#editCatDesc').val(desc);
    $('#editCatStatus').val(status);
    const modal = new bootstrap.Modal(document.getElementById('editCategoryModal'));
    modal.show();
  }

  // Client side instant filter
  document.getElementById('catSearchInput')?.addEventListener('keyup', function () {
    const val = this.value.toLowerCase();
    document.querySelectorAll('#categoriesTable tbody tr').forEach(row => {
      const text = row.innerText.toLowerCase();
      row.style.display = text.includes(val) ? '' : 'none';
    });
  });
</script>
</body>
</html>
