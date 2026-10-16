<?php
require_once '../auth.php';
redirect_authenticated_user('citizen', 'dashboard.php');
require_once '../db.php';

$error_msg = '';
$success_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'login';

    if ($action === 'login') {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $error_msg = 'Please enter both your email address and password.';
        } else {
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

                    session_regenerate_id(true);
                    header('Location: dashboard.php');
                    exit;
                } else {
                    $error_msg = 'Invalid citizen email or password.';
                }
                mysqli_stmt_close($stmt);
            } else {
                $error_msg = 'Database query error.';
            }
        }
    } elseif ($action === 'register') {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($name) || empty($email) || empty($password) || empty($address)) {
            $error_msg = 'Please fill in all required registration fields.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error_msg = 'Please enter a valid email address.';
        } elseif (strlen($password) < 6) {
            $error_msg = 'Password must be at least 6 characters long.';
        } else {
            $check = mysqli_prepare($conn, "SELECT user_id FROM users WHERE email = ? LIMIT 1");
            mysqli_stmt_bind_param($check, "s", $email);
            mysqli_stmt_execute($check);
            mysqli_stmt_store_result($check);

            if (mysqli_stmt_num_rows($check) > 0) {
                $error_msg = "An account with email '{$email}' already exists. Please sign in.";
            } else {
                $insert = mysqli_prepare($conn, "INSERT INTO users (name, email, password, phone, address, role, created_at) VALUES (?, ?, ?, ?, ?, 'Citizen', NOW())");
                mysqli_stmt_bind_param($insert, "sssss", $name, $email, $password, $phone, $address);
                if (mysqli_stmt_execute($insert)) {
                    $user_id = mysqli_insert_id($conn);
                    $_SESSION['user_id'] = $user_id;
                    $_SESSION['user_name'] = $name;
                    $_SESSION['name'] = $name;
                    $_SESSION['user_email'] = $email;
                    $_SESSION['email'] = $email;
                    $_SESSION['user_phone'] = $phone;
                    $_SESSION['user_address'] = $address;
                    $_SESSION['user_role'] = 'Citizen';
                    $_SESSION['role'] = 'citizen';

                    $success_msg = "Citizen account registered successfully! Initializing workspace...";
                } else {
                    $error_msg = "Registration failed: " . mysqli_error($conn);
                }
                mysqli_stmt_close($insert);
            }
            mysqli_stmt_close($check);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Citizen Access & Registration — CCMS</title>
  <link rel="icon" type="image/x-icon" href="assets/icon.png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&family=Space+Grotesk:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
  <link rel="stylesheet" href="assets/css/citizen.css">

  <style>
    .auth-page-wrapper {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 2.5rem 1rem;
      background: radial-gradient(circle at 10% 20%, rgba(99, 102, 241, 0.1) 0%, transparent 40%),
                  radial-gradient(circle at 90% 80%, rgba(6, 182, 212, 0.1) 0%, transparent 40%),
                  var(--bg-main);
      position: relative;
    }

    .auth-card {
      background: var(--bg-surface);
      border: 1px solid var(--border);
      border-radius: 26px;
      max-width: 540px;
      width: 100%;
      padding: 2.5rem;
      box-shadow: 0 30px 70px -15px rgba(0, 0, 0, 0.65);
      position: relative;
      overflow: hidden;
      backdrop-filter: blur(20px);
    }
    [data-bs-theme="light"] .auth-card {
      box-shadow: 0 25px 60px -15px rgba(99, 102, 241, 0.12);
    }

    .auth-card::before {
      content: '';
      position: absolute;
      top: 0; left: 0; right: 0;
      height: 4px;
      background: linear-gradient(90deg, var(--primary), var(--cyan), var(--emerald));
    }

    .auth-nav-tabs {
      display: flex;
      background: var(--bg-surface-2);
      padding: 5px;
      border-radius: 16px;
      margin-bottom: 1.75rem;
      border: 1px solid var(--border);
    }
    .auth-tab-btn {
      flex: 1;
      border: none;
      background: transparent;
      padding: 0.7rem 1rem;
      font-size: 0.88rem;
      font-weight: 700;
      color: var(--text-muted);
      border-radius: 12px;
      transition: var(--transition);
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.4rem;
    }
    .auth-tab-btn.active {
      background: var(--bg-surface);
      color: var(--primary-light);
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.25);
      border: 1px solid var(--border-glow);
    }

    .input-icon-wrap {
      position: relative;
      display: flex;
      align-items: center;
    }
    .input-icon-wrap i.field-icon {
      position: absolute;
      left: 1rem;
      color: var(--text-muted);
      font-size: 0.95rem;
      pointer-events: none;
      transition: color 0.2s ease;
    }

    .form-control-custom {
      background: var(--bg-surface-2);
      border: 1.5px solid var(--border);
      border-radius: 12px;
      padding: 0.8rem 1rem 0.8rem 2.75rem;
      color: var(--text-main);
      font-size: 0.92rem;
      width: 100%;
      outline: none;
      transition: var(--transition);
    }
    .form-control-custom:focus {
      border-color: var(--primary-light);
      box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
      background: var(--bg-surface);
    }
    .form-control-custom:focus + i.field-icon,
    .input-icon-wrap:focus-within i.field-icon {
      color: var(--primary-light);
    }

    .btn-pwd-eye {
      position: absolute;
      right: 0.85rem;
      background: none;
      border: none;
      color: var(--text-muted);
      cursor: pointer;
      font-size: 0.92rem;
      padding: 0.4rem;
    }
    .btn-pwd-eye:hover { color: var(--text-main); }

    .demo-quickfill-bar {
      background: rgba(99, 102, 241, 0.08);
      border: 1px dashed rgba(99, 102, 241, 0.3);
      border-radius: 12px;
      padding: 0.6rem 0.85rem;
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 1.25rem;
    }

    .inline-error-text {
      color: #ef4444;
      font-size: 0.78rem;
      margin-top: 4px;
      font-weight: 600;
      display: flex;
      align-items: center;
      gap: 4px;
    }
  </style>
</head>
<body>

  <!-- Ambient Visual Glow Blobs -->
  <div class="ambient-glow-1"></div>
  <div class="ambient-glow-2"></div>
  <div class="grid-overlay"></div>

  <div class="auth-page-wrapper">
    <div class="auth-card">
      <div class="d-flex align-items-center justify-content-between mb-4">
        <a href="../index.php" class="d-flex align-items-center gap-2 text-decoration-none">
          <div class="sb-logo" style="width: 38px; height: 38px; font-size: 1.15rem; background: linear-gradient(135deg, var(--primary), var(--cyan));">
            <i class="fa-solid fa-shield-halved"></i>
          </div>
          <div>
            <div style="font-weight: 800; font-family: var(--font-heading); font-size: 1.15rem; line-height: 1; color: var(--text-main);">CCMS</div>
            <div style="font-size: 0.68rem; color: var(--cyan); text-transform: uppercase; font-weight: 700; letter-spacing: 0.06em;">Citizen Portal</div>
          </div>
        </a>

        <button class="nav-icon-btn" id="themeToggle" title="Toggle Dark/Light Mode">
          <i class="fa-solid fa-moon" id="themeIcon"></i>
        </button>
      </div>

      <!-- Navigation Tabs -->
      <div class="auth-nav-tabs">
        <button class="auth-tab-btn active" id="tabBtnLogin" onclick="switchAuthTab('login')">
          <i class="fa-solid fa-user"></i> Sign In
        </button>
        <button class="auth-tab-btn" id="tabBtnRegister" onclick="switchAuthTab('register')">
          <i class="fa-solid fa-user-plus"></i> Register
        </button>
      </div>

      <!-- Quick Test Demo Bar -->
      <div class="demo-quickfill-bar" id="quickfillBox">
        <div class="d-flex align-items-center gap-2" style="font-size: 0.8rem; color: var(--text-main);">
          <i class="fa-solid fa-bolt text-warning"></i>
          <span>Sample Citizen: <strong>Rahul (Verified)</strong></span>
        </div>
        <button type="button" class="btn btn-sm btn-outline-primary border-0 px-2 py-1" style="font-size: 0.78rem; font-weight: 700;" onclick="autofillCitizenLogin()">
          Autofill Login
        </button>
      </div>

      <!-- Tab 1: Citizen Login Form -->
      <div id="authLoginTab">
        <div class="mb-4">
          <h2 style="font-size: 1.45rem; margin-bottom: 0.35rem; color: var(--text-main); font-weight: 800;">Welcome Back</h2>
          <p class="text-muted" style="font-size: 0.88rem; margin-bottom: 0;">Sign in to access your registered complaints and live investigation radar.</p>
        </div>

        <form method="POST" action="" id="citizenLoginForm" onsubmit="return validateCitizenLogin(event)">
          <input type="hidden" name="action" value="login">

          <div class="mb-3">
            <label class="form-label" style="font-size: 0.85rem; font-weight: 700; color: var(--text-main);">Email Address <span class="text-danger">*</span></label>
            <div class="input-icon-wrap">
              <input type="email" name="email" id="loginCitizenEmail" class="form-control-custom" placeholder="rahul@gmail.com" value="<?= htmlspecialchars($_POST['email'] ?? 'rahul@gmail.com') ?>">
              <i class="fa-solid fa-envelope field-icon"></i>
            </div>
          </div>

          <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center mb-1">
              <label class="form-label mb-0" style="font-size: 0.85rem; font-weight: 700; color: var(--text-main);">Password <span class="text-danger">*</span></label>
              <a href="javascript:void(0)" onclick="forgotPassPrompt()" class="text-decoration-none" style="font-size: 0.78rem; color: var(--primary-light);">Forgot password?</a>
            </div>
            <div class="input-icon-wrap">
              <input type="password" name="password" id="loginCitizenPassword" class="form-control-custom" placeholder="••••••••" value="rahul123">
              <i class="fa-solid fa-lock field-icon"></i>
              <button type="button" class="btn-pwd-eye" onclick="togglePass('loginCitizenPassword', this)">
                <i class="fa-solid fa-eye"></i>
              </button>
            </div>
          </div>

          <div class="form-check mb-4">
            <input class="form-check-input" type="checkbox" id="rememberMeCheck" checked>
            <label class="form-check-label" for="rememberMeCheck" style="font-size: 0.82rem; color: var(--text-muted);">
              Stay signed in on this secure terminal
            </label>
          </div>

          <button type="submit" class="btn-ccms btn-ccms-primary w-100 justify-content-center py-2 mb-3" style="font-size: 0.95rem; font-weight: 700;">
            <i class="fa-solid fa-arrow-right-to-bracket me-1"></i> Sign In to Citizen Dashboard
          </button>

          <div class="text-center">
            <span class="text-muted" style="font-size: 0.85rem;">Don't have an account?</span>
            <a href="javascript:void(0)" onclick="switchAuthTab('register')" class="ms-1 fw-bold text-decoration-none" style="color: var(--cyan); font-size: 0.85rem;">Register Now &rarr;</a>
          </div>
        </form>
      </div>

      <!-- Tab 2: Citizen Registration Form -->
      <div id="authRegisterTab" style="display: none;">
        <div class="mb-4">
          <div class="d-flex justify-content-between align-items-center mb-1">
            <h2 style="font-size: 1.45rem; margin-bottom: 0; color: var(--text-main); font-weight: 800;">Create Account</h2>
            <a href="register.php" class="btn btn-sm btn-outline-info border-0" style="font-size: 0.75rem; font-weight: 700;">
              <i class="fa-solid fa-expand me-1"></i> Full Page
            </a>
          </div>
          <p class="text-muted" style="font-size: 0.88rem; margin-bottom: 0;">Register for verified tracking and whistleblower legal shield.</p>
        </div>

        <form method="POST" action="" id="citizenRegForm" onsubmit="return validateCitizenRegistration(event)">
          <input type="hidden" name="action" value="register">

          <div class="mb-3">
            <label class="form-label" style="font-size: 0.85rem; font-weight: 700; color: var(--text-main);">Full Legal Name <span class="text-danger">*</span></label>
            <div class="input-icon-wrap">
              <input type="text" name="name" id="regCitizenName" class="form-control-custom" placeholder="e.g. Rahul Sharma">
              <i class="fa-solid fa-user field-icon"></i>
            </div>
          </div>

          <div class="row g-2 mb-3">
            <div class="col-md-6">
              <label class="form-label" style="font-size: 0.85rem; font-weight: 700; color: var(--text-main);">Email Address <span class="text-danger">*</span></label>
              <div class="input-icon-wrap">
                <input type="email" name="email" id="regCitizenEmail" class="form-control-custom" placeholder="rahul@gmail.com">
                <i class="fa-solid fa-envelope field-icon"></i>
              </div>
            </div>
            <div class="col-md-6">
              <label class="form-label" style="font-size: 0.85rem; font-weight: 700; color: var(--text-main);">Mobile Number <span class="text-danger">*</span></label>
              <div class="input-icon-wrap">
                <input type="tel" name="phone" id="regCitizenPhone" class="form-control-custom" placeholder="9876543210">
                <i class="fa-solid fa-phone field-icon"></i>
              </div>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label" style="font-size: 0.85rem; font-weight: 700; color: var(--text-main);">Address / City <span class="text-danger">*</span></label>
            <div class="input-icon-wrap">
              <input type="text" name="address" id="regCitizenAddress" class="form-control-custom" placeholder="e.g. Ahmedabad, Gujarat">
              <i class="fa-solid fa-location-dot field-icon"></i>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label" style="font-size: 0.85rem; font-weight: 700; color: var(--text-main);">Create Password <span class="text-danger">*</span></label>
            <div class="input-icon-wrap">
              <input type="password" name="password" id="regCitizenPassword" class="form-control-custom" placeholder="Minimum 6 characters">
              <i class="fa-solid fa-lock field-icon"></i>
              <button type="button" class="btn-pwd-eye" onclick="togglePass('regCitizenPassword', this)">
                <i class="fa-solid fa-eye"></i>
              </button>
            </div>
          </div>

          <button type="submit" class="btn-ccms btn-ccms-primary w-100 justify-content-center py-2 mb-3" style="font-size: 0.95rem; font-weight: 700;">
            <i class="fa-solid fa-user-check me-1"></i> Register Citizen Account
          </button>

          <div class="text-center">
            <span class="text-muted" style="font-size: 0.85rem;">Already have an account?</span>
            <a href="javascript:void(0)" onclick="switchAuthTab('login')" class="ms-1 fw-bold text-decoration-none" style="color: var(--cyan); font-size: 0.85rem;">Sign In Here &rarr;</a>
          </div>
        </form>
      </div>

      <!-- Footer Quick Switch -->
      <div class="pt-3 border-top mt-4 d-flex justify-content-between align-items-center flex-wrap gap-2" style="font-size: 0.8rem;">
        <a href="../login.php" class="text-primary text-decoration-none fw-bold">
          <i class="fa-solid fa-layer-group me-1"></i> All-Panel Login Hub
        </a>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="assets/js/citizen.js"></script>
  <script>
    /* Theme Toggle */
    const themeToggle = document.getElementById('themeToggle');
    const themeIcon = document.getElementById('themeIcon');

    function applyTheme(theme) {
      document.documentElement.setAttribute('data-bs-theme', theme);
      localStorage.setItem('ccms_citizen_theme', theme);
      if (themeIcon) {
        themeIcon.className = theme === 'dark' ? 'fa-solid fa-moon' : 'fa-solid fa-sun';
      }
    }

    if (themeToggle) {
      themeToggle.addEventListener('click', () => {
        const cur = document.documentElement.getAttribute('data-bs-theme') || 'dark';
        applyTheme(cur === 'dark' ? 'light' : 'dark');
      });
    }

    document.addEventListener('DOMContentLoaded', () => {
      const saved = localStorage.getItem('ccms_citizen_theme') || 'dark';
      applyTheme(saved);

      const urlParams = new URLSearchParams(window.location.search);
      if (urlParams.get('tab') === 'register') {
        switchAuthTab('register');
      }
    });

    function switchAuthTab(tab) {
      document.getElementById('tabBtnLogin').classList.toggle('active', tab === 'login');
      document.getElementById('tabBtnRegister').classList.toggle('active', tab === 'register');

      document.getElementById('authLoginTab').style.display = tab === 'login' ? 'block' : 'none';
      document.getElementById('authRegisterTab').style.display = tab === 'register' ? 'block' : 'none';
      document.getElementById('quickfillBox').style.display = tab === 'login' ? 'flex' : 'none';
    }

    function togglePass(id, btn) {
      const input = document.getElementById(id);
      if (!input) return;
      const isPwd = input.type === 'password';
      input.type = isPwd ? 'text' : 'password';
      btn.querySelector('i').className = isPwd ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye';
    }

    function autofillCitizenLogin() {
      document.getElementById('loginCitizenEmail').value = 'rahul@gmail.com';
      document.getElementById('loginCitizenPassword').value = 'rahul123';
      clearInlineError('loginCitizenEmail');
      clearInlineError('loginCitizenPassword');
      showCitizenToast('Demo credentials autofilled!', 'info');
    }

    function forgotPassPrompt() {
      Swal.fire({
        icon: 'info',
        title: 'Account Recovery',
        html: `
          <p style="font-size:0.88rem;color:var(--text-muted);">
            Please contact the Central Investigation Registrar with your initial verification docket number to reset your whistleblower key.
          </p>
          <div class="font-monospace text-primary p-2 rounded" style="font-size:0.8rem;background:rgba(99,102,241,0.1);">
            Hotline: 1800-ANTI-CORRUPT &bull; support@ccms.gov
          </div>
        `,
        confirmButtonColor: '#6366f1'
      });
    }

    function showInlineError(id, msg) {
      const el = document.getElementById(id);
      if (!el) return;
      el.classList.add('is-invalid');
      el.style.borderColor = '#ef4444';
      
      let parent = el.closest('.mb-3') || el.closest('.col-md-6') || el.parentElement;
      let err = parent.querySelector('.inline-error-text');
      if (!err) {
        err = document.createElement('div');
        err.className = 'inline-error-text';
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
      let parent = el.closest('.mb-3') || el.closest('.col-md-6') || el.parentElement;
      let err = parent.querySelector('.inline-error-text');
      if (err) {
        err.style.display = 'none';
      }
    }

    document.addEventListener('DOMContentLoaded', () => {
      ['loginCitizenEmail', 'loginCitizenPassword', 'regCitizenName', 'regCitizenEmail', 'regCitizenPhone', 'regCitizenAddress', 'regCitizenPassword'].forEach(id => {
        const el = document.getElementById(id);
        if (el) {
          el.addEventListener('input', () => clearInlineError(id));
        }
      });
    });

    // Inline Red Text Validation for Citizen Login
    function validateCitizenLogin(e) {
      let isValid = true;
      ['loginCitizenEmail', 'loginCitizenPassword'].forEach(clearInlineError);

      const email = document.getElementById('loginCitizenEmail').value.trim();
      const password = document.getElementById('loginCitizenPassword').value;

      if (!email || !email.includes('@')) {
        showInlineError('loginCitizenEmail', 'Please enter a valid email address.');
        isValid = false;
      }
      if (!password) {
        showInlineError('loginCitizenPassword', 'Please enter your password.');
        isValid = false;
      }

      if (!isValid && e) e.preventDefault();
      return isValid;
    }

    // Inline Red Text Validation for Citizen Registration
    function validateCitizenRegistration(e) {
      let isValid = true;
      ['regCitizenName', 'regCitizenEmail', 'regCitizenPhone', 'regCitizenAddress', 'regCitizenPassword'].forEach(clearInlineError);

      const name = document.getElementById('regCitizenName').value.trim();
      const email = document.getElementById('regCitizenEmail').value.trim();
      const phone = document.getElementById('regCitizenPhone').value.trim();
      const address = document.getElementById('regCitizenAddress').value.trim();
      const password = document.getElementById('regCitizenPassword').value;

      if (!name) {
        showInlineError('regCitizenName', 'Please enter your full legal name.');
        isValid = false;
      }
      if (!email || !email.includes('@')) {
        showInlineError('regCitizenEmail', 'Please enter a valid email address.');
        isValid = false;
      }
      if (!phone) {
        showInlineError('regCitizenPhone', 'Please enter a valid mobile number.');
        isValid = false;
      }
      if (!address) {
        showInlineError('regCitizenAddress', 'Please enter your address / city.');
        isValid = false;
      }
      if (!password || password.length < 6) {
        showInlineError('regCitizenPassword', 'Password must be at least 6 characters.');
        isValid = false;
      }

      if (!isValid && e) e.preventDefault();
      return isValid;
    }
  </script>

  <!-- Server Feedback Alerts -->
  <?php if (!empty($error_msg)): ?>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      Swal.fire({
        icon: 'error',
        title: 'Authentication Error',
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
        title: 'Success!',
        text: <?= json_encode($success_msg) ?>,
        timer: 1300,
        showConfirmButton: false
      }).then(function() {
        window.location.href = 'dashboard.php';
      });
    });
  </script>
  <?php endif; ?>

</body>
</html>
