<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../db.php';
$citizen_id = $_SESSION['user_id'] ?? 0;
$citizen_name = htmlspecialchars($_SESSION['user_name'] ?? $_SESSION['name'] ?? 'Verified Citizen');
$initials = strtoupper(substr($citizen_name, 0, 2));

// KPI counters
$total_q = mysqli_query($conn, "SELECT COUNT(*) AS total FROM complaints");
$total_count = $total_q ? intval(mysqli_fetch_assoc($total_q)['total']) : 0;

$probe_q = mysqli_query($conn, "SELECT COUNT(*) AS total FROM complaints WHERE status = 'Under Investigation' OR status = 'Assigned' OR status = 'Pending'");
$probe_count = $probe_q ? intval(mysqli_fetch_assoc($probe_q)['total']) : 0;

$resolved_q = mysqli_query($conn, "SELECT COUNT(*) AS total FROM complaints WHERE status = 'Resolved' OR status = 'Closed'");
$resolved_count = $resolved_q ? intval(mysqli_fetch_assoc($resolved_q)['total']) : 0;

// Latest complaint for alert banner
$latest_alert_q = mysqli_query($conn, "
    SELECT c.*, d.department_name 
    FROM complaints c
    LEFT JOIN officers o ON c.assigned_officer = o.officer_id
    LEFT JOIN departments d ON o.department_id = d.department_id
    ORDER BY c.complaint_id DESC
    LIMIT 1
");
$latest_alert = $latest_alert_q ? mysqli_fetch_assoc($latest_alert_q) : null;

// Recent complaints
$recent_complaints_res = mysqli_query($conn, "
    SELECT c.*, cat.category_name, o.name AS officer_name, d.department_name 
    FROM complaints c
    LEFT JOIN categories cat ON c.category_id = cat.category_id
    LEFT JOIN officers o ON c.assigned_officer = o.officer_id
    LEFT JOIN departments d ON o.department_id = d.department_id
    ORDER BY c.complaint_id DESC
    LIMIT 5
");
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Citizen Dashboard — CCMS</title>
  <link rel="icon" type="image/x-icon" href="assets/icon.png">
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
      <a href="dashboard.php" class="sb-link active">
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
        <span class="sb-badge"><?= $total_count ?></span>
      </a>

      <a href="track-complaint.php" class="sb-link">
        <div class="icon-wrap"><i class="fa-solid fa-satellite-dish"></i></div>
        <span>Live Case Tracker</span>
      </a>

      <div class="sb-section-label">Communications</div>
      <a href="notifications.php" class="sb-link">
        <div class="icon-wrap"><i class="fa-solid fa-bell"></i></div>
        <span>Encrypted Alerts</span>
        <span class="sb-badge amber">2</span>
      </a>

      <!-- <div class="sb-section-label">Account & External</div>
      <a href="profile.php" class="sb-link">
        <div class="icon-wrap"><i class="fa-solid fa-user-shield"></i></div>
        <span>Security & Keys</span>
      </a>

      <a href="../officer/index.php" class="sb-link">
        <div class="icon-wrap"><i class="fa-solid fa-user-shield"></i></div>
        <span>Officer Command</span>
      </a>

      <a href="../admin/index.php" class="sb-link">
        <div class="icon-wrap"><i class="fa-solid fa-lock"></i></div>
        <span>Admin Headquarters</span>
      </a> -->

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
        <button class="btn-toggle" id="sidebarToggle" title="Toggle Sidebar">
          <i class="fa-solid fa-bars-staggered"></i>
        </button>
        <div class="d-none d-md-block">
          <span style="font-size: 0.85rem; color: var(--text-muted);">
            Whistleblower Protection Status: <strong class="text-success"><i class="fa-solid fa-circle-check"></i> Active & Encrypted</strong>
          </span>
        </div>
      </div>

      <div class="nav-actions">
        <button class="nav-icon-btn" id="themeToggle" title="Toggle Theme">
          <i class="fa-solid fa-moon" id="themeIcon"></i>
        </button>

        <a href="notifications.php" class="nav-icon-btn" title="Notifications">
          <i class="fa-solid fa-bell"></i>
          <span class="badge-dot"></span>
        </a>

        <div class="dropdown">
          <button class="btn-ccms btn-ccms-secondary dropdown-toggle" data-bs-toggle="dropdown">
            <i class="fa-solid fa-circle-user text-primary"></i> <?= $citizen_name ?>
          </button>
          <ul class="dropdown-menu dropdown-menu-end shadow border-0" style="background: var(--bg-surface); border: 1px solid var(--border);">
            <li><a class="dropdown-item" href="profile.php"><i class="fa-solid fa-key me-2"></i> Security Profile</a></li>
            <li><a class="dropdown-item" href="my-complaints.php"><i class="fa-solid fa-folder me-2"></i> My Records</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger" href="login.php"><i class="fa-solid fa-power-off me-2"></i> Sign Out</a></li>
          </ul>
        </div>
      </div>
    </nav>

    <main class="content-body">
      <!-- Page Header -->
      <div class="page-header">
        <div>
          <h1 class="page-title">Citizen Oversight Dashboard</h1>
          <p class="page-subtitle">Track your submitted corruption complaints, view forensic milestones, and communicate securely with assigned investigators.</p>
        </div>

        <a href="file-complaint.php" class="btn-ccms btn-ccms-primary">
          <i class="fa-solid fa-bullhorn"></i> Lodge New Complaint
        </a>
      </div>

      <!-- Quick Alert Notification -->
      <?php if ($latest_alert): 
        $alert_cid = $latest_alert['complaint_id'];
        $alert_token = "CCMS-2026-" . str_pad($alert_cid, 4, '0', STR_PAD_LEFT);
        $alert_dept = htmlspecialchars($latest_alert['department_name'] ?? 'Anti-Corruption Bureau');
        $alert_status = htmlspecialchars($latest_alert['status'] ?? 'Under Review');
      ?>
      <div class="alert alert-primary d-flex align-items-center justify-content-between p-3 rounded-4 mb-4" style="background: rgba(99, 102, 241, 0.1); border: 1px solid var(--border-glow);">
        <div class="d-flex align-items-center gap-3">
          <div style="font-size: 1.5rem; color: var(--primary);"><i class="fa-solid fa-shield-halved"></i></div>
          <div>
            <strong style="color: var(--text-main); font-size: 0.95rem;">Case #<?= $alert_token ?> &bull; <?= $alert_dept ?></strong>
            <p class="mb-0 text-muted" style="font-size: 0.82rem;">Current docket status: <strong><?= $alert_status ?></strong>. Official records encrypted under secure judicial protocol.</p>
          </div>
        </div>
        <a href="track-complaint.php?track=<?= $alert_token ?>" class="btn-ccms btn-ccms-primary btn-sm" style="font-size: 0.8rem; padding: 0.4rem 1rem;">
          View Update
        </a>
      </div>
      <?php endif; ?>

      <!-- KPI Summary Cards -->
      <div class="kpi-grid">
        <div class="kpi-card indigo">
          <div class="kpi-icon"><i class="fa-solid fa-file-shield"></i></div>
          <div class="kpi-value counter-val" data-target="<?= $total_count ?>"><?= $total_count ?></div>
          <div class="kpi-label">Total Filed Complaints</div>
        </div>

        <div class="kpi-card cyan">
          <div class="kpi-icon"><i class="fa-solid fa-magnifying-glass"></i></div>
          <div class="kpi-value counter-val" data-target="<?= $probe_count ?>"><?= $probe_count ?></div>
          <div class="kpi-label">Under Active Probe</div>
        </div>

        <div class="kpi-card emerald">
          <div class="kpi-icon"><i class="fa-solid fa-circle-check"></i></div>
          <div class="kpi-value counter-val" data-target="<?= $resolved_count ?>"><?= $resolved_count ?></div>
          <div class="kpi-label">Resolved / Action Taken</div>
        </div>

        <div class="kpi-card amber">
          <div class="kpi-icon"><i class="fa-solid fa-lock"></i></div>
          <div class="kpi-value counter-val" data-target="100">100%</div>
          <div class="kpi-label">Identity Encryption Score</div>
        </div>
      </div>

      <!-- Active Cases Grid -->
      <div class="card-box">
        <div class="card-header-flex">
          <div class="card-title">
            <i class="fa-solid fa-clock-rotate-left text-primary"></i> Active & Recent Complaints
          </div>
          <a href="my-complaints.php" class="btn-ccms btn-ccms-secondary btn-sm" style="font-size: 0.8rem;">
            View All (<?= $total_count ?>)
          </a>
        </div>

        <div class="table-responsive">
          <table class="table align-middle" style="color: var(--text-main);">
            <thead style="background: var(--bg-surface-2); font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted);">
              <tr>
                <th style="padding: 1rem;">Docket Token</th>
                <th>Target Department</th>
                <th>Subject</th>
                <th>Filing Date</th>
                <th>Status</th>
                <th>SLA Clock</th>
                <th class="text-end" style="padding-right: 1rem;">Action</th>
              </tr>
            </thead>
            <tbody style="font-size: 0.9rem;">
              <?php if ($recent_complaints_res && mysqli_num_rows($recent_complaints_res) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($recent_complaints_res)): 
                  $r_cid = $row['complaint_id'];
                  $r_token = "CCMS-2026-" . str_pad($r_cid, 4, '0', STR_PAD_LEFT);
                  $r_dept = htmlspecialchars($row['department_name'] ?? 'Anti-Corruption Bureau');
                  $r_title = htmlspecialchars($row['title']);
                  $r_date = !empty($row['created_at']) ? date('M d, Y', strtotime($row['created_at'])) : 'Recent';
                  $r_status = $row['status'] ?? 'Pending';
                  $r_anon = !empty($row['is_anonymous']);

                  $badge_class = 'status-investigation';
                  $badge_icon = 'fa-spinner fa-spin';
                  if ($r_status === 'Resolved' || $r_status === 'Closed') {
                      $badge_class = 'status-resolved';
                      $badge_icon = 'fa-check';
                  } elseif ($r_status === 'Rejected') {
                      $badge_class = 'status-rejected';
                      $badge_icon = 'fa-xmark';
                  }
                ?>
                <tr>
                  <td style="padding: 1rem;">
                    <span class="font-monospace fw-bold text-primary"><?= $r_token ?></span>
                    <?php if ($r_anon): ?>
                      <span class="badge bg-secondary-subtle text-secondary ms-1" style="font-size: 0.7rem;">Anonymous</span>
                    <?php else: ?>
                      <span class="badge bg-primary-subtle text-primary ms-1" style="font-size: 0.7rem;">Verified ID</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <strong><?= $r_dept ?></strong>
                    <div style="font-size: 0.75rem; color: var(--text-muted);"><?= htmlspecialchars($row['category_name'] ?? 'General') ?></div>
                  </td>
                  <td><?= $r_title ?></td>
                  <td><?= $r_date ?></td>
                  <td><span class="badge-status <?= $badge_class ?>"><i class="fa-solid <?= $badge_icon ?>"></i> <?= htmlspecialchars($r_status) ?></span></td>
                  <td>
                    <?php if ($r_status === 'Resolved' || $r_status === 'Closed'): ?>
                      <span class="text-success fw-bold"><i class="fa-solid fa-check-double me-1"></i> Completed</span>
                    <?php else: ?>
                      <span class="text-warning fw-bold font-monospace"><i class="fa-solid fa-hourglass-half me-1"></i> Active</span>
                    <?php endif; ?>
                  </td>
                  <td class="text-end" style="padding-right: 1rem;">
                    <a href="track-complaint.php?track=<?= $r_token ?>" class="btn-cyber-action btn-cyber-primary">
                      <i class="fa-solid fa-location-crosshairs"></i> Track Live
                    </a>
                  </td>
                </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr>
                  <td colspan="7" class="text-center py-4 text-muted">
                    <i class="fa-solid fa-inbox fs-3 mb-2 d-block"></i> No complaints recorded yet.
                  </td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Quick Guidance & Whistleblower Shield Tips -->
      <div class="row g-4">
        <div class="col-lg-6">
          <div class="card-box h-100 mb-0">
            <div class="card-title mb-3">
              <i class="fa-solid fa-user-shield text-cyan"></i> Whistleblower Rights & Protections
            </div>
            <p class="text-muted" style="font-size: 0.88rem; line-height: 1.7;">
              Under national anti-corruption statutory provisions, all complainants are legally shielded from retaliation, termination, or harassment.
            </p>
            <ul class="list-unstyled d-flex flex-column gap-2" style="font-size: 0.85rem;">
              <li><i class="fa-solid fa-circle-check text-success me-2"></i> Client-side EXIF sanitization prevents photo location leaks.</li>
              <li><i class="fa-solid fa-circle-check text-success me-2"></i> Encrypted 2-way messaging without revealing email or telephone.</li>
              <li><i class="fa-solid fa-circle-check text-success me-2"></i> Downloadable tamper-proof cryptographic filing receipt.</li>
            </ul>
          </div>
        </div>

        <div class="col-lg-6">
          <div class="card-box h-100 mb-0">
            <div class="card-title mb-3">
              <i class="fa-solid fa-headset text-primary"></i> 24/7 Anti-Corruption Helpline
            </div>
            <p class="text-muted" style="font-size: 0.88rem;">
              Need immediate assistance or witnessing extortion in real-time? Connect with the Anti-Corruption Vigilance Bureau.
            </p>
            <div class="d-flex align-items-center gap-3 p-3 rounded-3" style="background: var(--bg-surface-2);">
              <div style="width: 44px; height: 44px; border-radius: 50%; background: var(--primary); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                <i class="fa-solid fa-phone-volume"></i>
              </div>
              <div>
                <div style="font-weight: 800; font-family: var(--font-mono); font-size: 1.15rem; color: var(--text-main);">1800-ANTI-CORRUPT (Toll Free)</div>
                <div style="font-size: 0.78rem; color: var(--cyan);">Toll-Free Emergency Response Cell • 24/7 Active</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="assets/js/citizen.js"></script>
</body>
</html>
