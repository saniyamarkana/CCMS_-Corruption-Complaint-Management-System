<?php
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

                    $success_msg = "Welcome back, {$user['name']}! Loading Citizen Dashboard...";
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
<html lang="en" data-bs-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Citizen Access & Registration — CCMS</title>
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
      padding: 2rem 1rem;
      background: radial-gradient(circle at 10% 20%, rgba(79, 70, 229, 0.08) 0%, transparent 40%),
                  radial-gradient(circle at 90% 80%, rgba(6, 182, 212, 0.08) 0%, transparent 40%),
                  var(--bg-main);
      position: relative;
    }

    .auth-card {
      background: var(--bg-surface);
      border: 1px solid var(--border);
      border-radius: 24px;
      max-width: 520px;
      width: 100%;
      padding: 2.5rem;
      box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.1);
      position: relative;
      overflow: hidden;
      backdrop-filter: blur(16px);
    }
    [data-bs-theme="dark"] .auth-card {
      box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.7);
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
      padding: 4px;
      border-radius: 14px;
      margin-bottom: 1.75rem;
      border: 1px solid var(--border);
    }
    .auth-tab-btn {
      flex: 1;
      border: none;
      background: transparent;
      padding: 0.65rem 1rem;
      font-size: 0.88rem;
      font-weight: 600;
      color: var(--text-muted);
      border-radius: 10px;
      transition: var(--transition);
      cursor: pointer;
    }
    .auth-tab-btn.active {
      background: var(--bg-surface);
      color: var(--text-main);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }

    .form-control-custom {
      background: var(--bg-surface-2);
      border: 1px solid var(--border);
      border-radius: 12px;
      padding: 0.8rem 1rem;
      color: var(--text-main);
      font-size: 0.92rem;
      width: 100%;
      outline: none;
      transition: var(--transition);
    }
    .form-control-custom:focus {
      border-color: var(--primary-light);
      box-shadow: 0 0 15px var(--primary-glow);
    }
  </style>
</head>
<body>

  <div class="auth-page-wrapper">
    <div class="auth-card">
      <div class="d-flex align-items-center justify-content-between mb-4">
        <a href="../index.php" class="d-flex align-items-center gap-2 text-decoration-none">
          <div class="sb-logo" style="width: 36px; height: 36px; font-size: 1.1rem;">
            <i class="fa-solid fa-shield-halved"></i>
          </div>
          <div>
            <div style="font-weight: 800; font-family: var(--font-heading); font-size: 1.1rem; line-height: 1;">CCMS</div>
            <div style="font-size: 0.68rem; color: var(--cyan); text-transform: uppercase; font-weight: 600;">Citizen Portal</div>
          </div>
        </a>

        <button class="nav-icon-btn" id="themeToggle" title="Toggle Dark/Light Mode">
          <i class="fa-solid fa-moon" id="themeIcon"></i>
        </button>
      </div>

      <!-- Navigation Tabs -->
      <div class="auth-nav-tabs">
        <button class="auth-tab-btn active" id="tabBtnLogin" onclick="switchAuthTab('login')">
          <i class="fa-solid fa-user me-1"></i> Sign In
        </button>
        <button class="auth-tab-btn" id="tabBtnRegister" onclick="switchAuthTab('register')">
          <i class="fa-solid fa-user-plus me-1"></i> Register
        </button>
      </div>

      <!-- Tab 1: Citizen Login Form -->
      <div id="authLoginTab">
        <div class="mb-4">
          <h2 style="font-size: 1.45rem; margin-bottom: 0.35rem;">Welcome Back</h2>
          <p class="text-muted" style="font-size: 0.88rem;">Sign in to access your registered complaints and case tracker.</p>
        </div>

        <form method="POST" action="" id="citizenLoginForm" onsubmit="return validateCitizenLogin(event)">
          <input type="hidden" name="action" value="login">

          <div class="mb-3">
            <label class="form-label" style="font-size: 0.85rem; font-weight: 600;">Email Address *</label>
            <input type="email" name="email" id="loginCitizenEmail" class="form-control-custom" placeholder="rahul@gmail.com" value="<?= htmlspecialchars($_POST['email'] ?? 'rahul@gmail.com') ?>">
          </div>

          <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center mb-1">
              <label class="form-label mb-0" style="font-size: 0.85rem; font-weight: 600;">Password *</label>
              <a href="#" class="text-decoration-none" style="font-size: 0.78rem; color: var(--primary-light);">Forgot password?</a>
            </div>
            <input type="password" name="password" id="loginCitizenPassword" class="form-control-custom" placeholder="••••••••" value="rahul123">
          </div>

          <button type="submit" class="btn-ccms btn-ccms-primary w-100 justify-content-center py-2 mb-3">
            <i class="fa-solid fa-arrow-right-to-bracket"></i> Sign In to Citizen Dashboard
          </button>

          <div class="text-center">
            <span class="text-muted" style="font-size: 0.85rem;">Don't have an account?</span>
            <a href="javascript:void(0)" onclick="switchAuthTab('register')" class="ms-1 fw-bold" style="color: var(--cyan); font-size: 0.85rem;">Register Now</a>
          </div>
        </form>
      </div>

      <!-- Tab 2: Citizen Registration Form -->
      <div id="authRegisterTab" style="display: none;">
        <div class="mb-4">
          <h2 style="font-size: 1.45rem; margin-bottom: 0.35rem;">Create Citizen Account</h2>
          <p class="text-muted" style="font-size: 0.88rem;">Register for verified tracking and anti-corruption protection.</p>
        </div>

        <form method="POST" action="" id="citizenRegForm" onsubmit="return validateCitizenRegistration(event)">
          <input type="hidden" name="action" value="register">

          <div class="mb-3">
            <label class="form-label" style="font-size: 0.85rem; font-weight: 600;">Full Legal Name *</label>
            <input type="text" name="name" id="regCitizenName" class="form-control-custom" placeholder="e.g. Rahul Sharma">
          </div>

          <div class="row g-2 mb-3">
            <div class="col-md-6">
              <label class="form-label" style="font-size: 0.85rem; font-weight: 600;">Email Address *</label>
              <input type="email" name="email" id="regCitizenEmail" class="form-control-custom" placeholder="rahul@gmail.com">
            </div>
            <div class="col-md-6">
              <label class="form-label" style="font-size: 0.85rem; font-weight: 600;">Mobile Number *</label>
              <input type="tel" name="phone" id="regCitizenPhone" class="form-control-custom" placeholder="9876543210">
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label" style="font-size: 0.85rem; font-weight: 600;">Address / City *</label>
            <input type="text" name="address" id="regCitizenAddress" class="form-control-custom" placeholder="e.g. Ahmedabad, Gujarat">
          </div>

          <div class="mb-3">
            <label class="form-label" style="font-size: 0.85rem; font-weight: 600;">Create Secure Password *</label>
            <input type="password" name="password" id="regCitizenPassword" class="form-control-custom" placeholder="Minimum 6 characters">
          </div>

          <button type="submit" class="btn-ccms btn-ccms-primary w-100 justify-content-center py-2 mb-3">
            <i class="fa-solid fa-user-check"></i> Register Citizen Account
          </button>
        </form>
      </div>

      <!-- Footer Quick Switch -->
      <div class="pt-3 border-top mt-4 d-flex justify-content-between align-items-center flex-wrap gap-2" style="font-size: 0.8rem;">
        <a href="../login.php" class="text-primary text-decoration-none fw-bold">
          <i class="fa-solid fa-layer-group me-1"></i> All-Panel Login Hub
        </a>
        <a href="../officer/login.php" class="text-muted text-decoration-none">
          <i class="fa-solid fa-user-shield me-1"></i> Officer Access
        </a>
        <a href="../admin/login.php" class="text-muted text-decoration-none">
          <i class="fa-solid fa-lock me-1"></i> Administrator
        </a>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="assets/js/citizen.js"></script>
  <script>
    function switchAuthTab(tab) {
      document.getElementById('tabBtnLogin').classList.toggle('active', tab === 'login');
      document.getElementById('tabBtnRegister').classList.toggle('active', tab === 'register');

      document.getElementById('authLoginTab').style.display = tab === 'login' ? 'block' : 'none';
      document.getElementById('authRegisterTab').style.display = tab === 'register' ? 'block' : 'none';
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
        title: 'Error',
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
