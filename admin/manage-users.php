<?php
require_once '../db.php';

$success_msg = '';
$error_msg = '';

// ═══════════════════ 1. DELETE ACTION (GET METHOD) ═══════════════════
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $del_id = intval($_GET['id']);
    if ($del_id > 0) {
        $del_stmt = mysqli_prepare($conn, "DELETE FROM users WHERE user_id = ?");
        if ($del_stmt) {
            mysqli_stmt_bind_param($del_stmt, "i", $del_id);
            if (mysqli_stmt_execute($del_stmt)) {
                $success_msg = "User account #USR-{$del_id} has been permanently deleted.";
            } else {
                $error_msg = "Failed to delete user: " . mysqli_error($conn);
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
    $address = trim($_GET['address'] ?? '');
    $role = trim($_GET['role'] ?? 'Citizen');

    if ($up_id > 0 && !empty($name) && !empty($email)) {
        $up_stmt = mysqli_prepare($conn, "UPDATE users SET name = ?, email = ?, phone = ?, address = ?, role = ? WHERE user_id = ?");
        if ($up_stmt) {
            mysqli_stmt_bind_param($up_stmt, "sssssi", $name, $email, $phone, $address, $role, $up_id);
            if (mysqli_stmt_execute($up_stmt)) {
                $success_msg = "Account details for {$name} (#USR-{$up_id}) updated successfully via GET action!";
            } else {
                $error_msg = "Update failed: " . mysqli_error($conn);
            }
            mysqli_stmt_close($up_stmt);
        }
    } else {
        $error_msg = "Name and Email are required fields.";
    }
}

// ═══════════════════ 3. TOGGLE ROLE ACTION (GET METHOD) ═══════════════════
if (isset($_GET['action']) && $_GET['action'] === 'toggle_role' && isset($_GET['id'])) {
    $toggle_id = intval($_GET['id']);
    $target_role = $_GET['role'] === 'Admin' ? 'Citizen' : 'Admin';
    $tog_stmt = mysqli_prepare($conn, "UPDATE users SET role = ? WHERE user_id = ?");
    if ($tog_stmt) {
        mysqli_stmt_bind_param($tog_stmt, "si", $target_role, $toggle_id);
        if (mysqli_stmt_execute($tog_stmt)) {
            $success_msg = "Role for User #USR-{$toggle_id} toggled to '{$target_role}'.";
        }
        mysqli_stmt_close($tog_stmt);
    }
}

// ═══════════════════ 4. ADD NEW USER (POST OR GET) ═══════════════════
if (($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) || (isset($_GET['action']) && $_GET['action'] === 'add')) {
    $src = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : $_GET;
    $name = trim($src['name'] ?? '');
    $email = trim($src['email'] ?? '');
    $phone = trim($src['phone'] ?? '');
    $address = trim($src['address'] ?? '');
    $password = trim($src['password'] ?? 'user123');
    $role = trim($src['role'] ?? 'Citizen');

    if (!empty($name) && !empty($email) && !empty($password)) {
        // Check duplicate email
        $chk = mysqli_prepare($conn, "SELECT user_id FROM users WHERE email = ? LIMIT 1");
        mysqli_stmt_bind_param($chk, "s", $email);
        mysqli_stmt_execute($chk);
        mysqli_stmt_store_result($chk);
        if (mysqli_stmt_num_rows($chk) > 0) {
            $error_msg = "Email address '{$email}' already exists in user database.";
        } else {
            $ins = mysqli_prepare($conn, "INSERT INTO users (name, email, password, phone, address, role, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
            mysqli_stmt_bind_param($ins, "ssssss", $name, $email, $password, $phone, $address, $role);
            if (mysqli_stmt_execute($ins)) {
                $new_uid = mysqli_insert_id($conn);
                $success_msg = "New user '{$name}' created with ID #USR-{$new_uid}!";
            } else {
                $error_msg = "Failed to create user: " . mysqli_error($conn);
            }
            mysqli_stmt_close($ins);
        }
        mysqli_stmt_close($chk);
    } else {
        $error_msg = "Full Name, Email, and Password are required.";
    }
}

// ═══════════════════ 5. SEARCHING & FILTERING (GET METHOD) ═══════════════════
$search = trim($_GET['search'] ?? '');
$role_filter = trim($_GET['role_filter'] ?? '');

$query = "SELECT u.*, COUNT(c.complaint_id) AS complaints_count 
          FROM users u 
          LEFT JOIN complaints c ON u.user_id = c.user_id 
          WHERE 1=1";

$params = [];
$types = "";

if (!empty($search)) {
    $search_pattern = "%{$search}%";
    $query .= " AND (u.name LIKE ? OR u.email LIKE ? OR u.phone LIKE ? OR u.address LIKE ? OR u.user_id LIKE ?)";
    $types .= "sssss";
    $params[] = $search_pattern;
    $params[] = $search_pattern;
    $params[] = $search_pattern;
    $params[] = $search_pattern;
    $params[] = $search_pattern;
}

if (!empty($role_filter) && $role_filter !== 'all') {
    $query .= " AND u.role = ?";
    $types .= "s";
    $params[] = $role_filter;
}

$query .= " GROUP BY u.user_id ORDER BY u.user_id DESC";

$stmt = mysqli_prepare($conn, $query);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$users_res = mysqli_stmt_get_result($stmt);
$users_list = [];
if ($users_res) {
    while ($row = mysqli_fetch_assoc($users_res)) {
        $users_list[] = $row;
    }
}
mysqli_stmt_close($stmt);

// ═══════════════════ 6. DYNAMIC KPI COUNTS ═══════════════════
$total_users = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM users"))[0] ?? 0;
$total_citizens = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM users WHERE role = 'Citizen'"))[0] ?? 0;
$total_admins = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM users WHERE role = 'Admin'"))[0] ?? 0;
$total_complaints = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM complaints"))[0] ?? 0;
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Citizen & User Accounts — CCMS Admin</title>
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
      <a href="manage-users.php" class="sb-link active"><div class="icon-wrap"><i class="fa-solid fa-users"></i></div><span>Manage Users</span><span class="sb-badge"><?= $total_users ?></span></a>
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
        <!-- SEARCH VIA GET METHOD -->
        <form method="GET" action="manage-users.php" class="search-wrap d-none d-md-flex align-items-center m-0">
          <i class="fa-solid fa-magnifying-glass"></i>
          <input type="text" name="search" id="tableSearch" placeholder="Search user ID, name, email, phone..." value="<?= htmlspecialchars($search) ?>">
          <?php if (!empty($search)): ?>
            <a href="manage-users.php" class="text-muted ms-2 me-1" title="Clear Search"><i class="fa-solid fa-xmark"></i></a>
          <?php endif; ?>
        </form>
      </div>
      <div class="nav-actions">
        <div class="online-chip d-none d-xl-flex"><span class="online-dot"></span>Live Database</div>
        <button class="nav-icon-btn" id="themeToggle"><i class="fa-solid fa-moon" id="themeIcon"></i></button>
        <div class="dropdown">
          <div class="nav-profile-btn dropdown-toggle" data-bs-toggle="dropdown">
            <img src="https://images.unsplash.com/photo-1560250097-0b93528c311a?auto=format&fit=crop&w=80&q=80" class="nav-avatar" alt="Admin">
            <div class="d-none d-md-block"><div class="nav-profile-name">Inspector Admin</div><div class="nav-profile-role">Super Administrator</div></div>
          </div>
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
          <div class="page-breadcrumb">
            <a href="index.php"><i class="fa-solid fa-house"></i></a>
            <i class="fa-solid fa-chevron-right"></i><span>User Accounts</span>
          </div>
          <h1 class="page-title">Citizen &amp; Admin Accounts Directory</h1>
          <p class="page-subtitle">Fully dynamic oversight of registered users. GET-based update, role toggling, search, and delete.</p>
        </div>
        <div class="d-flex gap-2">
          <button class="btn btn-ghost btn-export-pdf" data-doc-name="User_Directory_Report"><i class="fa-solid fa-file-pdf text-danger"></i> Export PDF</button>
          <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal"><i class="fa-solid fa-user-plus"></i> Add Account</button>
        </div>
      </div>

      <!-- DYNAMIC KPIS -->
      <div class="kpi-grid">
        <div class="kpi-card indigo">
          <div class="kpi-icon"><i class="fa-solid fa-users"></i></div>
          <div class="kpi-value counter-value"><?= $total_users ?></div>
          <div class="kpi-label">Total Accounts (DB)</div>
          <div class="kpi-trend up"><i class="fa-solid fa-check"></i> Registered</div>
        </div>
        <div class="kpi-card cyan">
          <div class="kpi-icon"><i class="fa-solid fa-user-tag"></i></div>
          <div class="kpi-value counter-value"><?= $total_citizens ?></div>
          <div class="kpi-label">Active Citizens</div>
          <div class="kpi-trend up"><i class="fa-solid fa-user"></i> Public Role</div>
        </div>
        <div class="kpi-card rose">
          <div class="kpi-icon"><i class="fa-solid fa-user-shield"></i></div>
          <div class="kpi-value counter-value"><?= $total_admins ?></div>
          <div class="kpi-label">Administrators</div>
          <div class="kpi-trend down"><i class="fa-solid fa-key"></i> Executive Tier</div>
        </div>
        <div class="kpi-card emerald">
          <div class="kpi-icon"><i class="fa-solid fa-file-shield"></i></div>
          <div class="kpi-value counter-value"><?= $total_complaints ?></div>
          <div class="kpi-label">Dockets Logged</div>
          <div class="kpi-trend up"><i class="fa-solid fa-shield-halved"></i> Active Probes</div>
        </div>
      </div>

      <!-- FILTER & SEARCH BAR VIA GET -->
      <form method="GET" action="manage-users.php" class="filter-bar">
        <div class="row g-3 align-items-end">
          <div class="col-md-5">
            <label class="form-label">Keyword Search</label>
            <div class="input-icon-group">
              <i class="fa-solid fa-magnifying-glass input-icon"></i>
              <input type="text" name="search" class="form-control" placeholder="Search by name, email, phone, location..." value="<?= htmlspecialchars($search) ?>">
            </div>
          </div>
          <div class="col-md-4">
            <label class="form-label">Role Classification</label>
            <div class="input-icon-group">
              <i class="fa-solid fa-shield-heart input-icon"></i>
              <select class="form-select" name="role_filter">
                <option value="all">All Roles</option>
                <option value="Citizen" <?= $role_filter === 'Citizen' ? 'selected' : '' ?>>Citizen</option>
                <option value="Admin" <?= $role_filter === 'Admin' ? 'selected' : '' ?>>Admin</option>
              </select>
            </div>
          </div>
          <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-primary flex-grow-1"><i class="fa-solid fa-filter"></i> Apply</button>
            <a href="manage-users.php" class="btn btn-ghost" title="Reset Filters"><i class="fa-solid fa-rotate-left"></i></a>
          </div>
        </div>
      </form>

      <!-- USERS TABLE -->
      <div class="card-box">
        <div class="card-box-head">
          <div class="card-box-title"><div class="title-icon"><i class="fa-solid fa-users"></i></div>User Account Registry (Showing <?= count($users_list) ?> Users)</div>
          <button class="btn btn-ghost btn-sm btn-export-pdf" data-doc-name="User_Registry_Export"><i class="fa-solid fa-file-pdf" style="color:var(--rose-500);"></i> PDF Export</button>
        </div>
        <div class="tbl-wrap">
          <table class="tbl" id="usersTable">
            <thead>
              <tr>
                <th>User ID</th>
                <th>Full Name &amp; Address</th>
                <th>Email Address</th>
                <th>Phone Contact</th>
                <th>Role Classification</th>
                <th>Dockets Filed</th>
                <th>Join Date</th>
                <th class="text-end no-sort">Actions (GET Method)</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($users_list)): ?>
                <?php foreach ($users_list as $u): 
                  $uid = $u['user_id'];
                  $avatar_seed = urlencode($u['name']);
                  $avatar_url = "https://ui-avatars.com/api/?name={$avatar_seed}&background=6366f1&color=fff&size=80";
                  $join_date = date('M d, Y', strtotime($u['created_at']));
                  $role_pill = $u['role'] === 'Admin' ? 'pill-investigating' : 'pill-active';
                ?>
                <tr>
                  <td class="fw-bold" style="color:var(--indigo-600); font-family:var(--font-mono);">USR-<?= str_pad($uid, 5, '0', STR_PAD_LEFT) ?></td>
                  <td>
                    <div class="d-flex align-items-center gap-2">
                      <img src="<?= $avatar_url ?>" style="width:34px;height:34px;border-radius:8px;object-fit:cover;" alt="<?= htmlspecialchars($u['name']) ?>">
                      <div>
                        <div class="fw-semibold" style="font-size:.85rem;"><?= htmlspecialchars($u['name']) ?></div>
                        <div class="extra-small text-muted text-truncate" style="max-width: 180px;"><i class="fa-solid fa-location-dot me-1"></i><?= htmlspecialchars($u['address'] ?? 'No address set') ?></div>
                      </div>
                    </div>
                  </td>
                  <td><div style="font-size:.82rem;font-family:var(--font-mono);"><?= htmlspecialchars($u['email']) ?></div></td>
                  <td><div style="font-size:.82rem;"><?= htmlspecialchars($u['phone'] ?? 'N/A') ?></div></td>
                  <td>
                    <a href="manage-users.php?action=toggle_role&id=<?= $uid ?>&role=<?= $u['role'] ?>" title="Click to toggle role via GET" class="text-decoration-none">
                      <span class="pill <?= $role_pill ?>"><i class="fa-solid fa-shield-halved me-1"></i><?= htmlspecialchars($u['role']) ?></span>
                    </a>
                  </td>
                  <td class="fw-bold text-center"><?= $u['complaints_count'] ?></td>
                  <td class="extra-small"><?= $join_date ?></td>
                  <td class="text-end">
                    <div class="d-inline-flex gap-1 align-items-center">
                      <!-- VIEW PROFILE -->
                      <button class="btn btn-ghost btn-icon btn-sm" onclick="showUserProfile('USR-<?= $uid ?>', '<?= addslashes($u['name']) ?>', '<?= addslashes($u['email']) ?>', '<?= $u['role'] ?>', '<?= addslashes($u['phone'] ?? 'N/A') ?>', '<?= $u['complaints_count'] ?> Cases', '<?= $join_date ?>', '<?= addslashes($u['address'] ?? 'N/A') ?>')" title="View Account Profile">
                        <i class="fa-solid fa-eye text-primary"></i>
                      </button>

                      <!-- EDIT USER MODAL (GET METHOD) -->
                      <button class="btn btn-ghost btn-icon btn-sm" onclick="openEditUserModal('<?= $uid ?>', '<?= addslashes($u['name']) ?>', '<?= addslashes($u['email']) ?>', '<?= addslashes($u['phone'] ?? '') ?>', '<?= addslashes($u['address'] ?? '') ?>', '<?= $u['role'] ?>')" title="Edit User (GET Method)">
                        <i class="fa-solid fa-pen-to-square text-info"></i>
                      </button>

                      <!-- DELETE USER (GET METHOD) -->
                      <a href="manage-users.php?action=delete&id=<?= $uid ?>" 
                         onclick="return confirm('Confirm Deletion: Are you sure you want to permanently delete user <?= addslashes($u['name']) ?> (#USR-<?= $uid ?>) via GET request?');" 
                         class="btn btn-ghost btn-icon btn-sm" 
                         title="Delete User (GET Method)">
                        <i class="fa-solid fa-trash text-danger"></i>
                      </a>
                    </div>
                  </td>
                </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="8" class="text-center py-5">
                    <i class="fa-solid fa-users-slash text-muted mb-2" style="font-size: 2.5rem;"></i>
                    <h6 class="text-muted">No users found matching current criteria</h6>
                    <a href="manage-users.php" class="btn btn-sm btn-outline-primary mt-2">Clear Filters</a>
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

<!-- ══════════════════════ MODAL: ADD USER ══════════════════════ -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa-solid fa-user-plus text-primary me-2"></i> Register Account</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" action="manage-users.php">
        <input type="hidden" name="add_user" value="1">
        <div class="modal-body" style="padding:24px;">
          <div class="mb-3">
            <label class="form-label">Full Name *</label>
            <div class="input-icon-group">
              <i class="fa-solid fa-user input-icon"></i>
              <input type="text" class="form-control" name="name" placeholder="Legal full name" required>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Email Address *</label>
            <div class="input-icon-group">
              <i class="fa-solid fa-envelope input-icon"></i>
              <input type="email" class="form-control" name="email" placeholder="user@domain.com" required>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Phone Contact Number</label>
            <div class="input-icon-group">
              <i class="fa-solid fa-phone input-icon"></i>
              <input type="tel" class="form-control" name="phone" placeholder="e.g. 9898012345">
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Residential / Office Address</label>
            <div class="input-icon-group">
              <i class="fa-solid fa-location-dot input-icon"></i>
              <input type="text" class="form-control" name="address" placeholder="e.g. City, District">
            </div>
          </div>
          <div class="row g-2 mb-3">
            <div class="col-md-6">
              <label class="form-label">Account Role *</label>
              <select class="form-select" name="role" required>
                <option value="Citizen" selected>Citizen</option>
                <option value="Admin">Admin</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Initial Password *</label>
              <input type="password" class="form-control" name="password" placeholder="Min 6 chars" required>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check me-1"></i> Create User</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ══════════════════════ MODAL: EDIT USER (GET METHOD) ══════════════════════ -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa-solid fa-pen-to-square text-info me-2"></i> Edit Account (GET Method)</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <!-- SUBMISSION VIA GET METHOD -->
      <form method="GET" action="manage-users.php">
        <input type="hidden" name="action" value="update">
        <input type="hidden" name="id" id="editUserId">
        <div class="modal-body" style="padding:24px;">
          <div class="mb-3">
            <label class="form-label">Full Legal Name *</label>
            <input type="text" class="form-control" name="name" id="editUserName" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Official Email Address *</label>
            <input type="email" class="form-control" name="email" id="editUserEmail" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Phone Contact</label>
            <input type="text" class="form-control" name="phone" id="editUserPhone">
          </div>
          <div class="mb-3">
            <label class="form-label">Address</label>
            <input type="text" class="form-control" name="address" id="editUserAddress">
          </div>
          <div class="mb-3">
            <label class="form-label">Account Role *</label>
            <select class="form-select" name="role" id="editUserRole" required>
              <option value="Citizen">Citizen</option>
              <option value="Admin">Admin</option>
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

<!-- ══════════════════════ MODAL: VIEW USER PROFILE ══════════════════════ -->
<div class="modal fade" id="viewUserModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa-solid fa-address-card text-primary me-2"></i> User Account Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" style="padding:24px;">
        <div class="d-flex align-items-center gap-3 mb-3">
          <div class="card-icon-bubble" style="width:48px;height:48px;margin-bottom:0;"><i class="fa-solid fa-user"></i></div>
          <div>
            <h5 class="mb-0" id="profUserName">User</h5>
            <span class="extra-small text-muted" id="profUserId">USR-00000</span>
          </div>
        </div>
        <div class="row g-2 mb-3">
          <div class="col-6"><div style="background:var(--bg);border:1px solid var(--border);border-radius:10px;padding:10px;"><div class="extra-small text-muted">Role</div><strong class="extra-small" id="profUserRole">Citizen</strong></div></div>
          <div class="col-6"><div style="background:var(--bg);border:1px solid var(--border);border-radius:10px;padding:10px;"><div class="extra-small text-muted">Phone</div><strong class="extra-small" id="profUserPhone">N/A</strong></div></div>
          <div class="col-6"><div style="background:var(--bg);border:1px solid var(--border);border-radius:10px;padding:10px;"><div class="extra-small text-muted">Email</div><strong class="extra-small" id="profUserEmail">user@ccms.com</strong></div></div>
          <div class="col-6"><div style="background:var(--bg);border:1px solid var(--border);border-radius:10px;padding:10px;"><div class="extra-small text-muted">Dockets Filed</div><strong class="extra-small text-primary" id="profUserCases">0 Cases</strong></div></div>
          <div class="col-6"><div style="background:var(--bg);border:1px solid var(--border);border-radius:10px;padding:10px;"><div class="extra-small text-muted">Member Since</div><strong class="extra-small" id="profUserJoined">Aug 2026</strong></div></div>
          <div class="col-6"><div style="background:var(--bg);border:1px solid var(--border);border-radius:10px;padding:10px;"><div class="extra-small text-muted">Address</div><strong class="extra-small text-truncate d-block" id="profUserAddress">Surat</strong></div></div>
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
  function showUserProfile(id, name, email, role, phone, cases, joined, address) {
    $('#profUserId').text(id);
    $('#profUserName').text(name);
    $('#profUserEmail').text(email);
    $('#profUserRole').text(role);
    $('#profUserPhone').text(phone);
    $('#profUserCases').text(cases);
    $('#profUserJoined').text(joined);
    $('#profUserAddress').text(address);
    const modal = new bootstrap.Modal(document.getElementById('viewUserModal'));
    modal.show();
  }

  function openEditUserModal(id, name, email, phone, address, role) {
    $('#editUserId').val(id);
    $('#editUserName').val(name);
    $('#editUserEmail').val(email);
    $('#editUserPhone').val(phone);
    $('#editUserAddress').val(address);
    $('#editUserRole').val(role);
    const modal = new bootstrap.Modal(document.getElementById('editUserModal'));
    modal.show();
  }

  // Client side keystroke search filter
  document.getElementById('tableSearch')?.addEventListener('keyup', function() {
    const val = this.value.toLowerCase();
    document.querySelectorAll('#usersTable tbody tr').forEach(row => {
      const text = row.innerText.toLowerCase();
      row.style.display = text.includes(val) ? '' : 'none';
    });
  });
</script>
</body>
</html>
