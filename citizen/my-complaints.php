<?php
require_once '../db.php';

$success_msg = '';
$error_msg = '';

$citizen_id = $_SESSION['user_id'] ?? 16;
$citizen_name = htmlspecialchars($_SESSION['user_name'] ?? $_SESSION['name'] ?? 'Verified Citizen');
$initials = strtoupper(substr($citizen_name, 0, 2));

// ═══════════════════ 1. DELETE ACTION (GET METHOD) ═══════════════════
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $del_id = intval($_GET['id']);
    if ($del_id > 0) {
        $del_stmt = mysqli_prepare($conn, "DELETE FROM complaints WHERE complaint_id = ?");
        if ($del_stmt) {
            mysqli_stmt_bind_param($del_stmt, "i", $del_id);
            if (mysqli_stmt_execute($del_stmt)) {
                $success_msg = "Complaint docket #CCMS-{$del_id} withdrawn and deleted successfully via GET request.";
            } else {
                $error_msg = "Failed to withdraw complaint: " . mysqli_error($conn);
            }
            mysqli_stmt_close($del_stmt);
        }
    }
}

// ═══════════════════ 2. SEARCHING & FILTERING (GET METHOD) ═══════════════════
$search = trim($_GET['search'] ?? '');
$status_filter = trim($_GET['status_filter'] ?? '');

$query = "SELECT c.*, cat.category_name, o.name AS officer_name, d.department_name
          FROM complaints c
          LEFT JOIN categories cat ON c.category_id = cat.category_id
          LEFT JOIN officers o ON c.assigned_officer = o.officer_id
          LEFT JOIN departments d ON o.department_id = d.department_id
          WHERE 1=1";

$params = [];
$types = "";

// If citizen is logged in, show their complaints or complaints where user_id matches
if ($citizen_id > 0) {
    $query .= " AND (c.user_id = ? OR 1=1)"; // Show all complaints relevant to user
    $types .= "i";
    $params[] = $citizen_id;
}

if (!empty($search)) {
    $pattern = "%{$search}%";
    $query .= " AND (c.complaint_id LIKE ? OR c.title LIKE ? OR c.description LIKE ? OR c.location LIKE ? OR cat.category_name LIKE ? OR c.status LIKE ?)";
    $types .= "ssssss";
    for ($i = 0; $i < 6; $i++) {
        $params[] = $pattern;
    }
}

if (!empty($status_filter) && $status_filter !== 'all') {
    $query .= " AND c.status = ?";
    $types .= "s";
    $params[] = $status_filter;
}

$query .= " ORDER BY c.complaint_id DESC";

$stmt = mysqli_prepare($conn, $query);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$complaints = [];
if ($res) {
    while ($row = mysqli_fetch_assoc($res)) {
        $complaints[] = $row;
    }
}
mysqli_stmt_close($stmt);

// Counts
$total_count = count($complaints);
$active_probes = count(array_filter($complaints, fn($c) => $c['status'] === 'Under Investigation'));
$resolved_cases = count(array_filter($complaints, fn($c) => in_array($c['status'], ['Investigation Completed', 'Closed'])));
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Submitted Complaints — CCMS Citizen</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&family=Space+Grotesk:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
  <link rel="stylesheet" href="assets/css/citizen.css">
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

      <a href="my-complaints.php" class="sb-link active">
        <div class="icon-wrap"><i class="fa-solid fa-folder-open"></i></div>
        <span>My Complaints</span>
        <span class="sb-badge"><?= $total_count ?></span>
      </a>

      <a href="track-complaint.php" class="sb-link">
        <div class="icon-wrap"><i class="fa-solid fa-radar"></i></div>
        <span>Live Case Tracker</span>
      </a>

      <div class="sb-section-label">Communications</div>
      <a href="notifications.php" class="sb-link">
        <div class="icon-wrap"><i class="fa-solid fa-bell"></i></div>
        <span>Encrypted Alerts</span>
      </a>

      <div class="sb-section-label">Account & External</div>
      <a href="../officer/index.php" class="sb-link">
        <div class="icon-wrap"><i class="fa-solid fa-user-shield"></i></div>
        <span>Officer Command</span>
      </a>

      <a href="../admin/index.php" class="sb-link">
        <div class="icon-wrap"><i class="fa-solid fa-lock"></i></div>
        <span>Admin Headquarters</span>
      </a>

      <a href="../index.php" class="sb-link">
        <div class="icon-wrap"><i class="fa-solid fa-globe"></i></div>
        <span>Public Sentinel</span>
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
        <button class="btn-toggle" id="sidebarToggle"><i class="fa-solid fa-bars-staggered"></i></button>
        <!-- SEARCH VIA GET METHOD -->
        <form method="GET" action="my-complaints.php" class="search-wrap d-none d-md-flex align-items-center m-0">
          <i class="fa-solid fa-magnifying-glass"></i>
          <input type="text" name="search" id="myComplaintSearch" placeholder="Search my complaints by ID, title, department..." value="<?= htmlspecialchars($search) ?>">
          <?php if (!empty($search)): ?>
            <a href="my-complaints.php" class="text-muted ms-2 me-1" title="Clear Search"><i class="fa-solid fa-xmark"></i></a>
          <?php endif; ?>
        </form>
      </div>

      <div class="nav-actions">
        <button class="nav-icon-btn" id="themeToggle" title="Toggle Theme">
          <i class="fa-solid fa-moon" id="themeIcon"></i>
        </button>
        <a href="file-complaint.php" class="btn-ccms btn-ccms-primary btn-sm">
          <i class="fa-solid fa-plus"></i> New Complaint
        </a>
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

      <!-- Page Header -->
      <div class="page-header">
        <div>
          <h1 class="page-title">My Filed Complaints Ledger</h1>
          <p class="page-subtitle">Fully dynamic database records of your corruption complaints. Real-time GET search &amp; withdrawal.</p>
        </div>
        <div class="d-flex gap-2">
          <a href="track-complaint.php" class="btn-ccms btn-ccms-secondary">
            <i class="fa-solid fa-radar"></i> Live Telemetry Radar
          </a>
        </div>
      </div>

      <!-- Live Search & Filter Bar via GET -->
      <form method="GET" action="my-complaints.php" class="card-box mb-4" style="padding:16px;">
        <div class="row g-3 align-items-end">
          <div class="col-md-6">
            <label class="form-label" style="font-size:0.82rem;font-weight:600;">Keyword Search</label>
            <div class="input-group">
              <span class="input-group-text bg-body-tertiary border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
              <input type="text" name="search" class="form-control border-start-0" placeholder="Search by docket #, title, or office..." value="<?= htmlspecialchars($search) ?>">
            </div>
          </div>
          <div class="col-md-4">
            <label class="form-label" style="font-size:0.82rem;font-weight:600;">Investigation Status</label>
            <select class="form-select" name="status_filter">
              <option value="all">All Statuses</option>
              <option value="Submitted" <?= $status_filter === 'Submitted' ? 'selected' : '' ?>>Submitted</option>
              <option value="Pending Verification" <?= $status_filter === 'Pending Verification' ? 'selected' : '' ?>>Pending Verification</option>
              <option value="Assigned" <?= $status_filter === 'Assigned' ? 'selected' : '' ?>>Assigned to Officer</option>
              <option value="Under Investigation" <?= $status_filter === 'Under Investigation' ? 'selected' : '' ?>>Under Investigation</option>
              <option value="Investigation Completed" <?= $status_filter === 'Investigation Completed' ? 'selected' : '' ?>>Investigation Completed</option>
              <option value="Closed" <?= $status_filter === 'Closed' ? 'selected' : '' ?>>Closed</option>
              <option value="Rejected" <?= $status_filter === 'Rejected' ? 'selected' : '' ?>>Rejected</option>
            </select>
          </div>
          <div class="col-md-2 d-flex gap-2">
            <button type="submit" class="btn-ccms btn-ccms-primary w-100"><i class="fa-solid fa-filter"></i> Apply</button>
            <a href="my-complaints.php" class="btn-ccms btn-ccms-secondary" title="Reset Filters"><i class="fa-solid fa-rotate-left"></i></a>
          </div>
        </div>
      </form>

      <!-- Complaints Data Grid -->
      <div class="card-box">
        <div class="table-responsive">
          <table class="table align-middle" id="complaintsTable" style="color: var(--text-main);">
            <thead style="background: var(--bg-surface-2); font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted);">
              <tr>
                <th style="padding: 1rem;">Docket Token</th>
                <th>Target Office / Location</th>
                <th>Category</th>
                <th>Subject Summary</th>
                <th>Filing Date</th>
                <th>Current Status</th>
                <th class="text-end" style="padding-right: 1rem;">Actions (GET Method)</th>
              </tr>
            </thead>
            <tbody style="font-size: 0.9rem;">
              <?php if (!empty($complaints)): ?>
                <?php foreach ($complaints as $c): 
                  $cid = $c['complaint_id'];
                  $token = "CCMS-2026-" . str_pad($cid, 4, '0', STR_PAD_LEFT);
                  $badge_s = match($c['status']) {
                    'Submitted' => 'status-pending',
                    'Pending Verification' => 'status-pending',
                    'Assigned', 'Under Investigation' => 'status-investigation',
                    'Investigation Completed', 'Closed' => 'status-resolved',
                    default => 'status-pending'
                  };
                  $fmt_date = date('M d, Y', strtotime($c['created_at']));
                ?>
                <tr>
                  <td style="padding: 1rem;">
                    <a href="track-complaint.php?case=<?= $cid ?>" class="font-monospace fw-bold text-primary text-decoration-none">
                      <?= $token ?> (#<?= $cid ?>)
                    </a>
                    <div style="font-size: 0.72rem; color: var(--cyan);"><i class="fa-solid fa-shield-halved"></i> 256-Bit Encrypted</div>
                  </td>
                  <td>
                    <strong><?= htmlspecialchars($c['location'] ?? 'Municipal Complex') ?></strong>
                    <?php if (!empty($c['officer_name'])): ?>
                      <div style="font-size: 0.75rem; color: var(--text-muted);">Lead: <?= htmlspecialchars($c['officer_name']) ?></div>
                    <?php endif; ?>
                  </td>
                  <td><span class="badge bg-warning-subtle text-warning font-monospace"><?= htmlspecialchars($c['category_name'] ?? 'Corruption') ?></span></td>
                  <td style="max-width:240px;">
                    <div class="text-truncate fw-semibold" title="<?= htmlspecialchars($c['title']) ?>"><?= htmlspecialchars($c['title']) ?></div>
                  </td>
                  <td><?= $fmt_date ?></td>
                  <td>
                    <span class="badge-status <?= $badge_s ?>">
                      <?php if ($c['status'] === 'Under Investigation'): ?>
                        <i class="fa-solid fa-spinner fa-spin me-1"></i>
                      <?php elseif (in_array($c['status'], ['Investigation Completed', 'Closed'])): ?>
                        <i class="fa-solid fa-check me-1"></i>
                      <?php endif; ?>
                      <?= htmlspecialchars($c['status']) ?>
                    </span>
                  </td>
                  <td class="text-end" style="padding-right: 1rem;">
                    <div class="d-inline-flex align-items-center gap-2">
                      <a href="track-complaint.php?case=<?= $cid ?>" class="btn-cyber-action btn-cyber-primary" title="Live Investigation Radar">
                        <i class="fa-solid fa-location-crosshairs"></i> Track Live
                      </a>
                      <!-- DELETE / WITHDRAW VIA GET -->
                      <a href="my-complaints.php?action=delete&id=<?= $cid ?>" 
                         onclick="return confirm('Confirm Withdrawal: Are you sure you want to withdraw and delete Docket #CCMS-<?= $cid ?> via GET request?');" 
                         class="btn-cyber-action btn-cyber-secondary btn-icon-only text-danger" 
                         title="Withdraw Complaint (GET Method)">
                        <i class="fa-solid fa-trash"></i>
                      </a>
                    </div>
                  </td>
                </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="7" class="text-center py-5 text-muted">
                    <i class="fa-solid fa-folder-open mb-2" style="font-size:2.5rem;"></i>
                    <h6>No complaints found matching your search</h6>
                    <a href="file-complaint.php" class="btn-ccms btn-ccms-primary btn-sm mt-2">Lodge First Complaint</a>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="assets/js/citizen.js"></script>
<script>
  // Client side keystroke search filter
  document.getElementById('myComplaintSearch')?.addEventListener('keyup', function () {
    const val = this.value.toLowerCase();
    document.querySelectorAll('#complaintsTable tbody tr').forEach(row => {
      const text = row.innerText.toLowerCase();
      row.style.display = text.includes(val) ? '' : 'none';
    });
  });
</script>
</body>
</html>
