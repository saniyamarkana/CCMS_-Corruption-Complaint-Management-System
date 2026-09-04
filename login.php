<?php
require_once 'db.php';

$error_msg = '';
$success_msg = '';
$redirect_url = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role = trim($_POST['role'] ?? 'citizen');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error_msg = 'Please enter both your email address and password.';
    } else {
        // 1. ADMIN LOGIN (Static Credentials)
        if ($role === 'admin' || $email === 'admin@ccms.com') {
            if ($email === 'admin@ccms.com' && $password === 'admin123') {
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = 1;
                $_SESSION['admin_name'] = 'Super Administrator';
                $_SESSION['name'] = 'Super Administrator';
                $_SESSION['admin_email'] = 'admin@ccms.com';
                $_SESSION['email'] = 'admin@ccms.com';
                $_SESSION['role'] = 'admin';

                $success_msg = 'Admin authorization successful! Opening Command Center...';
                $redirect_url = './admin/index.php';
            } else {
                $error_msg = 'Invalid Admin credentials. Access denied.';
            }
        }
        // 2. OFFICER LOGIN (Database officers table)
        elseif ($role === 'officer') {
            $stmt = mysqli_prepare($conn, "SELECT * FROM officers WHERE email = ? LIMIT 1");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "s", $email);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $officer = mysqli_fetch_assoc($result);

                if ($officer && $officer['password'] === $password) {
                    if (isset($officer['status']) && $officer['status'] !== 'Active') {
                        $error_msg = 'This officer account is currently inactive. Contact Central Administration.';
                    } else {
                        $_SESSION['officer_id'] = $officer['officer_id'];
                        $_SESSION['officer_name'] = $officer['name'];
                        $_SESSION['name'] = $officer['name'];
                        $_SESSION['officer_email'] = $officer['email'];
                        $_SESSION['email'] = $officer['email'];
                        $_SESSION['officer_phone'] = $officer['phone'] ?? '';
                        $_SESSION['designation'] = $officer['designation'] ?? 'Field Officer';
                        $_SESSION['department_id'] = $officer['department_id'] ?? 1;
                        $_SESSION['role'] = 'officer';

                        $success_msg = "Welcome back, {$officer['name']}! Opening Officer Command...";
                        $redirect_url = 'officer/index.php';
                    }
                } else {
                    $error_msg = 'Invalid Officer email or password.';
                }
                mysqli_stmt_close($stmt);
            } else {
                $error_msg = 'Database query failed.';
            }
        }
        // 3. CITIZEN LOGIN (Database users table)
        else {
            $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE email = ? LIMIT 1");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "s", $email);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $user = mysqli_fetch_assoc($result);

                if ($user && $user['password'] === $password) {
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['name'] = $user['name'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['user_phone'] = $user['phone'] ?? '';
                    $_SESSION['user_address'] = $user['address'] ?? '';
                    $_SESSION['user_role'] = 'Citizen';
                    $_SESSION['role'] = 'citizen';

                    $success_msg = "Welcome back, {$user['name']}! Opening Citizen Portal...";
                    $redirect_url = 'citizen/dashboard.php';
                } else {
                    $error_msg = 'Invalid Citizen email or password.';
                }
                mysqli_stmt_close($stmt);
            } else {
                $error_msg = 'Database query failed.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Central Authentication Gateway — CCMS Portal Login</title>
  <meta name="description" content="Secure sign-in portal for Citizen Whistleblowers, Law Enforcement Investigators, and Central Administrators.">

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&family=Space+Grotesk:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">

  <!-- Bootstrap 5.3 & Font Awesome 6 Pro -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <!-- SweetAlert2 -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

  <style>
    :root {
      --bg-base: #050711;
      --bg-surface: #0a0f20;
      --bg-surface-2: #0f1730;
      --bg-glass: rgba(11, 17, 36, 0.78);
      --border: rgba(99, 102, 241, 0.18);
      --border-glow: rgba(99, 102, 241, 0.45);

      --primary: #6366f1;
      --primary-light: #818cf8;
      --primary-dark: #4338ca;
      --primary-glow: rgba(99, 102, 241, 0.35);

      --cyan: #06b6d4;
      --emerald: #10b981;
      --amber: #f59e0b;
      --rose: #f43f5e;
      --sky: #0284c7;

      --text-main: #f8fafc;
      --text-muted: #94a3b8;
      --text-subtle: #64748b;

      --font-body: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
      --font-heading: 'Space Grotesk', sans-serif;
      --font-mono: 'JetBrains Mono', monospace;

      --card-shadow: 0 30px 80px -15px rgba(0, 0, 0, 0.8);
      --transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
    }

    [data-theme="light"] {
      --bg-base: #edf2f9;
      --bg-surface: #ffffff;
      --bg-surface-2: #f4f7fc;
      --bg-glass: rgba(255, 255, 255, 0.92);
      --border: rgba(99, 102, 241, 0.14);
      --border-glow: rgba(99, 102, 241, 0.3);

      --primary: #4f46e5;
      --primary-light: #6366f1;
      --primary-dark: #3730a3;
      --primary-glow: rgba(79, 70, 229, 0.18);

      --text-main: #0f172a;
      --text-muted: #475569;
      --text-subtle: #94a3b8;
      --card-shadow: 0 25px 60px -15px rgba(99, 102, 241, 0.14);
    }

    * { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: var(--font-body);
      background-color: var(--bg-base);
      color: var(--text-main);
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      position: relative;
      overflow-x: hidden;
      padding: 2rem 1rem;
    }

    /* Ambient Background Glows */
    .ambient-orb {
      position: fixed;
      border-radius: 50%;
      filter: blur(150px);
      pointer-events: none;
      z-index: 0;
      opacity: 0.6;
    }
    .orb-1 {
      width: 650px; height: 650px;
      background: radial-gradient(circle, rgba(99, 102, 241, 0.28), transparent 70%);
      top: -15%; left: -10%;
    }
    .orb-2 {
      width: 550px; height: 550px;
      background: radial-gradient(circle, rgba(6, 182, 212, 0.25), transparent 70%);
      bottom: -15%; right: -10%;
    }

    .grid-overlay {
      position: fixed;
      inset: 0;
      background-image:
        linear-gradient(rgba(255, 255, 255, 0.02) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255, 255, 255, 0.02) 1px, transparent 1px);
      background-size: 55px 55px;
      pointer-events: none;
      z-index: 0;
    }

    .portal-container {
      position: relative;
      z-index: 2;
      width: 100%;
      max-width: 1050px;
      margin: auto;
    }

    .main-auth-card {
      background: var(--bg-glass);
      backdrop-filter: blur(24px);
      border: 1px solid var(--border);
      border-radius: 30px;
      box-shadow: var(--card-shadow);
      position: relative;
      overflow: hidden;
    }

    .main-auth-card::before {
      content: '';
      position: absolute;
      top: 0; left: 0; right: 0;
      height: 4px;
      background: linear-gradient(90deg, #6366f1, #06b6d4, #10b981, #f43f5e, #6366f1);
      background-size: 300% 100%;
      animation: gradientShift 6s linear infinite;
    }
    @keyframes gradientShift {
      0% { background-position: 0% 0%; }
      100% { background-position: 300% 0%; }
    }

    /* Left Hero Side */
    .hero-side-panel {
      background: linear-gradient(145deg, rgba(14, 22, 48, 0.65), rgba(8, 12, 28, 0.85));
      border-right: 1px solid var(--border);
      padding: 3rem 2.25rem;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      position: relative;
    }
    [data-theme="light"] .hero-side-panel {
      background: linear-gradient(145deg, #f0f4ff, #e6edfd);
    }

    .brand-header-box {
      display: flex;
      align-items: center;
      gap: 1rem;
      text-decoration: none;
      color: inherit;
    }
    .brand-logo-icon {
      width: 48px; height: 48px;
      border-radius: 16px;
      background: linear-gradient(135deg, var(--primary), var(--cyan));
      display: flex;
      align-items: center;
      justify-content: center;
      color: #fff;
      font-size: 1.35rem;
      box-shadow: 0 0 25px var(--primary-glow);
    }
    .brand-logo-text {
      font-family: var(--font-heading);
      font-size: 1.45rem;
      font-weight: 800;
      line-height: 1;
      letter-spacing: -0.03em;
    }
    .brand-logo-sub {
      font-size: 0.72rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      color: var(--cyan);
    }

    /* Role Showcase Visual Card */
    .role-showcase-box {
      background: linear-gradient(135deg, rgba(15, 23, 42, 0.85), rgba(30, 41, 59, 0.95));
      border: 1px solid var(--border-glow);
      border-radius: 20px;
      padding: 1.5rem;
      margin: 1.5rem 0;
      position: relative;
      overflow: hidden;
      box-shadow: 0 15px 35px -10px rgba(0, 0, 0, 0.5);
    }
    .role-showcase-header {
      display: flex;
      align-items: center;
      gap: 0.85rem;
      margin-bottom: 0.75rem;
    }
    .role-showcase-icon {
      width: 42px; height: 42px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.2rem;
    }
    .role-showcase-title {
      font-size: 1.1rem;
      font-weight: 800;
      font-family: var(--font-heading);
    }
    .role-showcase-desc {
      font-size: 0.8rem;
      color: var(--text-muted);
      line-height: 1.5;
    }

    /* Right Form Side */
    .form-side-panel {
      padding: 3rem 2.5rem;
    }

    /* 3 Role Tabs */
    .role-segmented-nav {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 0.35rem;
      background: var(--bg-surface-2);
      padding: 5px;
      border-radius: 16px;
      border: 1px solid var(--border);
      margin-bottom: 1.25rem;
    }
    .role-seg-btn {
      background: transparent;
      border: none;
      padding: 0.75rem 0.5rem;
      border-radius: 12px;
      color: var(--text-muted);
      font-size: 0.85rem;
      font-weight: 700;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
      transition: var(--transition);
      text-align: center;
    }
    .role-seg-btn i { font-size: 1rem; }
    .role-seg-btn.active {
      background: var(--bg-surface);
      color: var(--primary-light);
      box-shadow: 0 6px 18px rgba(0, 0, 0, 0.2);
      border: 1px solid var(--border-glow);
    }
    [data-theme="light"] .role-seg-btn.active {
      background: #ffffff;
      color: var(--primary);
    }

    /* Demo Quick Autofill Chips */
    .demo-bar-box {
      background: var(--bg-surface-2);
      border: 1px dashed var(--border);
      border-radius: 14px;
      padding: 0.6rem 0.85rem;
      margin-bottom: 1.25rem;
    }
    .demo-bar-title {
      font-size: 0.72rem;
      font-weight: 800;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      color: var(--text-subtle);
      margin-bottom: 0.4rem;
      display: flex;
      justify-content: space-between;
    }
    .demo-chips {
      display: flex;
      flex-wrap: wrap;
      gap: 0.4rem;
    }
    .demo-chip-btn {
      background: var(--bg-surface);
      border: 1px solid var(--border);
      border-radius: 8px;
      padding: 0.28rem 0.6rem;
      font-size: 0.75rem;
      font-weight: 600;
      color: var(--text-muted);
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 0.35rem;
      transition: var(--transition);
    }
    .demo-chip-btn:hover {
      border-color: var(--primary-light);
      color: var(--primary-light);
      background: rgba(99, 102, 241, 0.08);
      transform: translateY(-1px);
    }

    .field-group {
      margin-bottom: 1.25rem;
      position: relative;
    }
    .field-label {
      display: flex;
      justify-content: space-between;
      align-items: center;
      font-size: 0.82rem;
      font-weight: 700;
      color: var(--text-muted);
      margin-bottom: 0.45rem;
    }
    .input-box {
      position: relative;
      display: flex;
      align-items: center;
    }
    .input-box i.input-ico {
      position: absolute;
      left: 1.15rem;
      color: var(--text-subtle);
      font-size: 0.95rem;
      pointer-events: none;
      transition: var(--transition);
    }
    .form-input-ccms {
      width: 100%;
      background: var(--bg-surface-2);
      border: 1.5px solid var(--border);
      border-radius: 14px;
      padding: 0.82rem 1.15rem 0.82rem 2.9rem;
      color: var(--text-main);
      font-size: 0.92rem;
      font-family: inherit;
      outline: none;
      transition: var(--transition);
    }
    .form-input-ccms:focus {
      border-color: var(--primary-light);
      box-shadow: 0 0 0 3.5px var(--primary-glow);
      background: var(--bg-surface);
    }
    .form-input-ccms:focus ~ i.input-ico {
      color: var(--primary-light);
    }

    .btn-pwd-eye {
      position: absolute;
      right: 1.1rem;
      background: none;
      border: none;
      color: var(--text-subtle);
      cursor: pointer;
      font-size: 0.92rem;
    }
    .btn-pwd-eye:hover { color: var(--text-main); }

    /* Submit Button */
    .btn-login-submit {
      width: 100%;
      padding: 0.92rem 1.5rem;
      border-radius: 14px;
      border: none;
      background: linear-gradient(135deg, var(--primary), var(--primary-dark));
      color: #ffffff;
      font-size: 1rem;
      font-weight: 700;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.65rem;
      box-shadow: 0 12px 30px var(--primary-glow);
      transition: var(--transition);
    }
    .btn-login-submit:hover {
      transform: translateY(-2px);
      box-shadow: 0 16px 40px var(--primary-glow);
      background: linear-gradient(135deg, var(--primary-light), var(--primary));
    }

    .auth-bottom-switch {
      text-align: center;
      margin-top: 1.5rem;
      padding-top: 1.15rem;
      border-top: 1px solid var(--border);
      font-size: 0.88rem;
      color: var(--text-muted);
    }
    .auth-bottom-switch a {
      color: var(--primary-light);
      font-weight: 700;
      text-decoration: none;
    }
    .auth-bottom-switch a:hover { text-decoration: underline; color: var(--cyan); }

    @media (max-width: 992px) {
      .hero-side-panel { border-right: none; border-bottom: 1px solid var(--border); padding: 2rem 1.5rem; }
      .form-side-panel { padding: 2rem 1.5rem; }
      .role-segmented-nav { grid-template-columns: repeat(3, 1fr); gap: 0.25rem; }
    }
  </style>
</head>
<body>

  <div class="ambient-orb orb-1"></div>
  <div class="ambient-orb orb-2"></div>
  <div class="grid-overlay"></div>

  <div class="portal-container">
    <div class="main-auth-card">
      <div class="row g-0">

        <!-- Left Side: Interactive Portal Badge & Security Metrics -->
        <div class="col-lg-5 hero-side-panel">
          <div>
            <div class="d-flex justify-content-between align-items-center mb-4">
              <a href="index.php" class="brand-header-box">
                <div class="brand-logo-icon">
                  <i class="fa-solid fa-shield-halved"></i>
                </div>
                <div>
                  <div class="brand-logo-text">CCMS</div>
                  <div class="brand-logo-sub">Central Command</div>
                </div>
              </a>

              <button class="btn btn-sm btn-outline-secondary border-0" id="themeToggleBtn" title="Toggle Dark/Light">
                <i class="fa-solid fa-moon" id="themeIcon"></i>
              </button>
            </div>

            <h3 style="font-size: 1.35rem; font-weight: 800; line-height: 1.3; margin-bottom: 0.4rem;">
              Secure Portal <span style="color: var(--primary-light);" id="portalRoleHeader">Sign In</span>
            </h3>
            <p style="font-size: 0.85rem; color: var(--text-muted);" id="portalRoleSub">
              Access your role-specific dashboard with end-to-end encryption, automated case telemetry, and forensic audit logs.
            </p>

            <!-- Dynamic Role Showcase Box -->
            <div class="role-showcase-box" id="roleShowcaseBox">
              <div class="role-showcase-header">
                <div class="role-showcase-icon" id="roleShowcaseIcon" style="background: rgba(99, 102, 241, 0.2); color: var(--primary-light);">
                  <i class="fa-solid fa-user-shield"></i>
                </div>
                <div>
                  <div class="role-showcase-title" id="roleShowcaseTitle">Citizen Portal</div>
                  <div style="font-size: 0.72rem; color: var(--emerald); font-weight: 700;">
                    <i class="fa-solid fa-circle" style="font-size: 0.5rem;"></i> System Online
                  </div>
                </div>
              </div>
              <div class="role-showcase-desc" id="roleShowcaseDesc">
                Direct access to lodged complaints, real-time status tracker, whistleblower message inbox, and verified legal protection dossier.
              </div>
            </div>
          </div>

          <div>
            <div class="d-flex align-items-center justify-content-between text-muted" style="font-size: 0.75rem; border-top: 1px solid var(--border); padding-top: 1rem;">
              <span><i class="fa-solid fa-lock text-success me-1"></i> 256-Bit SSL</span>
              <span><i class="fa-solid fa-database text-info me-1"></i> MySQL DB</span>
              <span><i class="fa-solid fa-shield text-primary me-1"></i> ISO 27001</span>
            </div>
          </div>
        </div>

        <!-- Right Side: Login Form -->
        <div class="col-lg-7 form-side-panel">
          
          <!-- Segmented Role Tabs (Citizen, Officer, Admin) -->
          <div class="role-segmented-nav">
            <button type="button" class="role-seg-btn active" id="tabBtnCitizen" onclick="setLoginRole('citizen')">
              <i class="fa-solid fa-user"></i>
              <span>Citizen</span>
            </button>
            <button type="button" class="role-seg-btn" id="tabBtnOfficer" onclick="setLoginRole('officer')">
              <i class="fa-solid fa-user-tie"></i>
              <span>Officer</span>
            </button>
            <button type="button" class="role-seg-btn" id="tabBtnAdmin" onclick="setLoginRole('admin')">
              <i class="fa-solid fa-crown"></i>
              <span>Admin</span>
            </button>
          </div>

          <!-- Quick Test Credentials Autofill -->
          <div class="demo-bar-box">
            <div class="demo-bar-title">
              <span><i class="fa-solid fa-bolt text-warning me-1"></i> Quick Test Credentials</span>
              <span style="font-size: 0.68rem; font-weight: 500;">Click to Autofill</span>
            </div>
            <div class="demo-chips">
              <button type="button" class="demo-chip-btn" onclick="autofillDemo('citizen', 'rahul@gmail.com', 'rahul123')">
                <i class="fa-solid fa-user text-primary"></i> Citizen (Rahul)
              </button>
              <button type="button" class="demo-chip-btn" onclick="autofillDemo('officer', 'rajesh@ccms.com', 'raj123')">
                <i class="fa-solid fa-user-tie text-info"></i> Officer (Rajesh)
              </button>
              <button type="button" class="demo-chip-btn" onclick="autofillDemo('admin', 'admin@ccms.com', 'admin123')">
                <i class="fa-solid fa-crown text-danger"></i> Admin (admin@ccms.com)
              </button>
            </div>
          </div>

          <!-- Login Form with JS Validation + PHP POST -->
          <form method="POST" action="" id="universalLoginForm" onsubmit="return validateUniversalLogin(event)">
            
            <input type="hidden" name="role" id="inputLoginRole" value="<?= htmlspecialchars($_POST['role'] ?? 'citizen') ?>">

            <div class="field-group" id="groupEmail">
              <label class="field-label" for="loginEmail" id="labelIdentifier">Email Address</label>
              <div class="input-box">
                <input type="email" class="form-input-ccms" id="loginEmail" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" placeholder="rahul@gmail.com">
                <i class="fa-solid fa-envelope input-ico" id="iconIdentifier"></i>
              </div>
            </div>

            <div class="field-group" id="groupPassword">
              <div class="field-label">
                <label for="loginPassword" class="mb-0">Password</label>
                <a href="javascript:void(0)" onclick="forgotPassAlert()" class="text-decoration-none" style="font-size: 0.76rem; color: var(--primary-light);">Forgot password?</a>
              </div>
              <div class="input-box">
                <input type="password" class="form-input-ccms" id="loginPassword" name="password" placeholder="••••••••">
                <i class="fa-solid fa-lock input-ico"></i>
                <button type="button" class="btn-pwd-eye" onclick="togglePassView('loginPassword', this)">
                  <i class="fa-solid fa-eye"></i>
                </button>
              </div>
            </div>

            <div class="d-flex align-items-center justify-content-between mb-4">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="rememberMeCheck" name="remember_me" checked>
                <label class="form-check-label" for="rememberMeCheck" style="font-size: 0.82rem; color: var(--text-muted);">
                  Remember this device
                </label>
              </div>
              <span style="font-size: 0.78rem;" id="targetPanelBadge">
                Target: <strong class="text-primary-emphasis">Citizen Dashboard</strong>
              </span>
            </div>

            <button type="submit" class="btn-login-submit" id="btnSubmitLogin">
              <i class="fa-solid fa-arrow-right-to-bracket"></i>
              <span id="btnSubmitLabel">Sign In to Citizen Dashboard</span>
            </button>

          </form>

          <div class="auth-bottom-switch" id="bottomRegisterLinkBox">
            <span>Don't have an active account yet?</span>
            <a href="register.php" id="linkToRegister" class="ms-1">
              <i class="fa-solid fa-user-plus me-1"></i>Create Citizen Account
            </a>
          </div>

        </div>

      </div>
    </div>
  </div>

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <script>
    let currentLoginRole = '<?= htmlspecialchars($_POST['role'] ?? 'citizen') ?>';

    function setLoginRole(role) {
      currentLoginRole = role;
      document.getElementById('inputLoginRole').value = role;

      document.getElementById('tabBtnCitizen').classList.toggle('active', role === 'citizen');
      document.getElementById('tabBtnOfficer').classList.toggle('active', role === 'officer');
      document.getElementById('tabBtnAdmin').classList.toggle('active', role === 'admin');

      const labelId = document.getElementById('labelIdentifier');
      const inputId = document.getElementById('loginEmail');
      const iconId = document.getElementById('iconIdentifier');
      const btnSubmit = document.getElementById('btnSubmitLogin');
      const btnLabel = document.getElementById('btnSubmitLabel');
      const targetBadge = document.getElementById('targetPanelBadge');
      const showcaseIcon = document.getElementById('roleShowcaseIcon');
      const showcaseTitle = document.getElementById('roleShowcaseTitle');
      const showcaseDesc = document.getElementById('roleShowcaseDesc');
      const bottomRegisterBox = document.getElementById('bottomRegisterLinkBox');

      if (role === 'citizen') {
        labelId.innerText = 'Citizen Email Address';
        inputId.placeholder = 'rahul@gmail.com';
        iconId.className = 'fa-solid fa-envelope input-ico';
        btnLabel.innerText = 'Sign In to Citizen Dashboard';
        btnSubmit.style.background = 'linear-gradient(135deg, #4f46e5, #3730a3)';
        targetBadge.innerHTML = 'Target: <strong class="text-primary">Citizen Dashboard</strong>';
        showcaseIcon.style.background = 'rgba(99, 102, 241, 0.2)';
        showcaseIcon.style.color = '#818cf8';
        showcaseIcon.innerHTML = '<i class="fa-solid fa-user-shield"></i>';
        showcaseTitle.innerText = 'Citizen Portal';
        showcaseDesc.innerText = 'Direct access to lodged complaints, real-time status tracker, whistleblower message inbox, and verified legal protection dossier.';
        bottomRegisterBox.innerHTML = `<span>Don't have an active account yet?</span> <a href="register.php" class="ms-1"><i class="fa-solid fa-user-plus me-1"></i>Create Citizen Account</a>`;
      } else if (role === 'officer') {
        labelId.innerText = 'Officer Official Email';
        inputId.placeholder = 'rajesh@ccms.com';
        iconId.className = 'fa-solid fa-id-badge input-ico';
        btnLabel.innerText = 'Authenticate & Open Officer Command';
        btnSubmit.style.background = 'linear-gradient(135deg, #0284c7, #0369a1)';
        targetBadge.innerHTML = 'Target: <strong class="text-info">Officer Panel</strong>';
        showcaseIcon.style.background = 'rgba(2, 132, 199, 0.2)';
        showcaseIcon.style.color = '#38bdf8';
        showcaseIcon.innerHTML = '<i class="fa-solid fa-user-tie"></i>';
        showcaseTitle.innerText = 'Investigator Command Center';
        showcaseDesc.innerText = 'Forensic investigation workspace with assigned case dockets, evidence vault, evidentiary hearing logs, and suspect interview records.';
        bottomRegisterBox.innerHTML = `<span>New officer joining the bureau?</span> <a href="officer/register.php" class="ms-1 fw-bold text-info"><i class="fa-solid fa-user-plus me-1"></i>Register Officer Account</a>`;
      } else if (role === 'admin') {
        labelId.innerText = 'Administrator Email';
        inputId.placeholder = 'admin@ccms.com';
        iconId.className = 'fa-solid fa-crown input-ico';
        btnLabel.innerText = 'Grant Access to Central Command';
        btnSubmit.style.background = 'linear-gradient(135deg, #e11d48, #be123c)';
        targetBadge.innerHTML = 'Target: <strong class="text-danger">Super Admin Panel</strong>';
        showcaseIcon.style.background = 'rgba(225, 29, 72, 0.2)';
        showcaseIcon.style.color = '#fb7185';
        showcaseIcon.innerHTML = '<i class="fa-solid fa-crown"></i>';
        showcaseTitle.innerText = 'Central Oversight Authority';
        showcaseDesc.innerText = 'Executive administration hub with national complaint assignment, officer management, department compliance KPIs, and audit reporting.';
        bottomRegisterBox.innerHTML = `<span class="text-muted"><i class="fa-solid fa-lock me-1"></i> Static Master Admin credentials (admin@ccms.com / admin123).</span>`;
      }
    }

    function autofillDemo(role, email, pass) {
      setLoginRole(role);
      document.getElementById('loginEmail').value = email;
      document.getElementById('loginPassword').value = pass;
    }

    function togglePassView(id, btn) {
      const input = document.getElementById(id);
      const icon = btn.querySelector('i');
      if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'fa-solid fa-eye-slash';
      } else {
        input.type = 'password';
        icon.className = 'fa-solid fa-eye';
      }
    }

    function showInlineError(id, msg) {
      const el = document.getElementById(id);
      if (!el) return;
      el.classList.add('is-invalid');
      el.style.borderColor = '#ef4444';
      
      let parent = el.closest('.field-group') || el.closest('.mb-3') || el.parentElement;
      let err = parent.querySelector('.inline-error-text');
      if (!err) {
        err = document.createElement('div');
        err.className = 'inline-error-text';
        err.style.color = '#ef4444';
        err.style.fontSize = '0.78rem';
        err.style.marginTop = '4px';
        err.style.fontWeight = '600';
        err.style.display = 'flex';
        err.style.alignItems = 'center';
        err.style.gap = '4px';
        parent.appendChild(err);
      }
      err.innerHTML = `<i class="fa-solid fa-circle-exclamation"></i> ${msg}`;
      err.style.display = 'flex';
    }

    function clearInlineError(id) {
      const el = document.getElementById(id);
      if (!el) return;
      el.classList.remove('is-invalid');
      el.style.borderColor = '';
      let parent = el.closest('.field-group') || el.closest('.mb-3') || el.parentElement;
      let err = parent.querySelector('.inline-error-text');
      if (err) {
        err.style.display = 'none';
      }
    }

    document.addEventListener('DOMContentLoaded', () => {
      ['loginEmail', 'loginPassword'].forEach(id => {
        const el = document.getElementById(id);
        if (el) {
          el.addEventListener('input', () => clearInlineError(id));
        }
      });
    });

    // Inline Red Text Validation for Universal Login
    function validateUniversalLogin(e) {
      let isValid = true;
      ['loginEmail', 'loginPassword'].forEach(clearInlineError);

      const email = document.getElementById('loginEmail').value.trim();
      const password = document.getElementById('loginPassword').value;

      if (!email || !email.includes('@')) {
        showInlineError('loginEmail', 'Please enter a valid registered email address.');
        isValid = false;
      }
      if (!password) {
        showInlineError('loginPassword', 'Please enter your account password.');
        isValid = false;
      }

      if (!isValid && e) {
        e.preventDefault();
      }
      return isValid;
    }

    function forgotPassAlert() {
      Swal.fire({
        title: 'Password Recovery',
        text: 'Please enter your registered email address to receive password reset instructions.',
        input: 'email',
        inputPlaceholder: 'Enter your email address',
        showCancelButton: true,
        confirmButtonText: 'Send Reset Link',
        confirmButtonColor: '#4f46e5'
      }).then((result) => {
        if (result.isConfirmed && result.value) {
          Swal.fire({
            icon: 'success',
            title: 'Recovery Email Sent',
            text: `Instructions have been dispatched to ${result.value}.`,
            confirmButtonColor: '#4f46e5'
          });
        }
      });
    }

    // Theme Toggle
    const themeBtn = document.getElementById('themeToggleBtn');
    const themeIcon = document.getElementById('themeIcon');

    function applyTheme(theme) {
      document.documentElement.setAttribute('data-theme', theme);
      localStorage.setItem('ccms_theme', theme);
      if (themeIcon) {
        themeIcon.className = theme === 'dark' ? 'fa-solid fa-moon' : 'fa-solid fa-sun';
      }
    }

    if (themeBtn) {
      themeBtn.addEventListener('click', () => {
        const current = document.documentElement.getAttribute('data-theme') || 'dark';
        applyTheme(current === 'dark' ? 'light' : 'dark');
      });
    }

    document.addEventListener('DOMContentLoaded', () => {
      const savedTheme = localStorage.getItem('ccms_theme') || 'dark';
      applyTheme(savedTheme);

      const params = new URLSearchParams(window.location.search);
      const roleParam = params.get('role') || '<?= htmlspecialchars($_POST['role'] ?? 'citizen') ?>';
      if (roleParam && ['officer', 'citizen', 'admin'].includes(roleParam)) {
        setLoginRole(roleParam);
      } else {
        setLoginRole('citizen');
      }
    });
  </script>

  <!-- Server Feedback Alerts -->
  <?php if (!empty($error_msg)): ?>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      Swal.fire({
        icon: 'error',
        title: 'Login Error',
        text: <?= json_encode($error_msg) ?>,
        confirmButtonColor: '#4f46e5'
      });
    });
  </script>
  <?php endif; ?>

  <?php if (!empty($success_msg)): ?>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      Swal.fire({
        icon: 'success',
        title: 'Authenticated!',
        text: <?= json_encode($success_msg) ?>,
        timer: 1300,
        showConfirmButton: false
      }).then(function() {
        window.location.href = <?= json_encode($redirect_url) ?>;
      });
    });
  </script>
  <?php endif; ?>

</body>
</html>
