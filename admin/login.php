<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../db.php';

$error_msg = '';
$success_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error_msg = 'Please enter both administrator email and master password.';
    } elseif ($email === 'admin@ccms.com' && $password === 'admin123') {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_id'] = 1;
        $_SESSION['admin_name'] = 'Super Administrator';
        $_SESSION['admin_email'] = 'admin@ccms.com';
        $_SESSION['role'] = 'admin';

        $success_msg = 'Master clearance authorized. Welcome Administrator!';
    } else {
        $error_msg = 'Invalid administrator credentials or unauthorized access.';
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Central Oversight Command — Super Admin Access — CCMS</title>
  <meta name="description" content="Master executive administration login for Corruption Complaint Management System.">

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&family=Space+Grotesk:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">

  <!-- Bootstrap 5.3 & Font Awesome 6 Pro -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <!-- SweetAlert2 -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

  <style>
    :root {
      --font-body: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
      --font-heading: 'Space Grotesk', sans-serif;
      --font-mono: 'JetBrains Mono', monospace;

      --bg-main: #f8fafc;
      --bg-surface: #ffffff;
      --bg-surface-2: #f1f5f9;
      --border: rgba(99, 102, 241, 0.15);
      --border-glow: rgba(99, 102, 241, 0.35);

      --primary: #4f46e5;
      --cyan: #0284c7;
      --cyan-light: #06b6d4;

      --text-main: #0f172a;
      --text-muted: #64748b;
      --card-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.12);
      --transition: all 0.28s cubic-bezier(0.16, 1, 0.3, 1);
    }

    [data-bs-theme="dark"] {
      --bg-main: #060913;
      --bg-surface: #0c1222;
      --bg-surface-2: #10192e;
      --border: rgba(99, 102, 241, 0.2);
      --border-glow: rgba(6, 182, 212, 0.35);

      --text-main: #f8fafc;
      --text-muted: #94a3b8;
      --card-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.6);
    }

    * { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: var(--font-body);
      background-color: var(--bg-main);
      color: var(--text-main);
      min-height: 100vh;
      overflow-x: hidden;
      position: relative;
      transition: background-color 0.3s ease, color 0.3s ease;
    }

    .auth-wrap {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 2rem 1rem;
      background: radial-gradient(circle at 15% 15%, rgba(6, 182, 212, 0.09) 0%, transparent 45%),
                  radial-gradient(circle at 85% 85%, rgba(79, 70, 229, 0.09) 0%, transparent 45%),
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
      box-shadow: var(--card-shadow);
      position: relative;
      overflow: hidden;
      backdrop-filter: blur(16px);
      -webkit-backdrop-filter: blur(16px);
    }

    .auth-card::before {
      content: '';
      position: absolute;
      top: 0; left: 0; right: 0;
      height: 4px;
      background: linear-gradient(90deg, #0284c7, #06b6d4, #6366f1);
    }

    .form-control-custom {
      background: var(--bg-surface-2);
      border: 1.5px solid var(--border);
      border-radius: 12px;
      padding: 0.8rem 1rem 0.8rem 2.85rem;
      color: var(--text-main);
      font-size: 0.92rem;
      width: 100%;
      outline: none;
      transition: var(--transition);
      font-family: inherit;
    }

    .form-control-custom:focus {
      border-color: var(--cyan);
      box-shadow: 0 0 15px rgba(6, 182, 212, 0.25);
      background: var(--bg-surface);
    }

    .input-wrap {
      position: relative;
      display: flex;
      align-items: center;
    }

    .input-wrap .field-icon {
      position: absolute;
      left: 1rem;
      color: var(--text-muted);
      font-size: 0.95rem;
      pointer-events: none;
    }

    .btn-pwd-eye {
      position: absolute;
      right: 0.85rem;
      background: none;
      border: none;
      color: var(--text-muted);
      cursor: pointer;
      font-size: 0.9rem;
      padding: 0.25rem;
      transition: var(--transition);
    }
    .btn-pwd-eye:hover {
      color: var(--text-main);
    }

    .btn-auth {
      width: 100%;
      background: linear-gradient(135deg, #0284c7, #0369a1);
      border: none;
      color: #fff;
      font-weight: 700;
      padding: 0.85rem;
      border-radius: 12px;
      font-size: 0.95rem;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
      transition: var(--transition);
      box-shadow: 0 10px 25px rgba(2, 132, 199, 0.3);
    }

    .btn-auth:hover {
      transform: translateY(-2px);
      box-shadow: 0 12px 30px rgba(2, 132, 199, 0.4);
      background: linear-gradient(135deg, #0369a1, #0284c7);
      color: #fff;
    }

    .demo-bar {
      background: rgba(2, 132, 199, 0.08);
      border: 1px dashed rgba(2, 132, 199, 0.3);
      border-radius: 12px;
      padding: 0.6rem 0.9rem;
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 1.25rem;
      font-size: 0.78rem;
    }

    .demo-bar strong {
      color: var(--text-main);
    }

    .theme-toggle-btn {
      width: 36px;
      height: 36px;
      border-radius: 50%;
      border: 1px solid var(--border);
      background: transparent;
      color: var(--text-muted);
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: var(--transition);
    }
    .theme-toggle-btn:hover {
      border-color: var(--cyan);
      color: var(--text-main);
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

    @media (max-width: 576px) {
      .auth-card {
        padding: 1.75rem 1.25rem;
        border-radius: 20px;
      }
    }
  </style>
</head>
<body>

  <div class="auth-wrap">
    <div class="auth-card">

      <!-- Brand Header -->
      <div class="d-flex align-items-center justify-content-between mb-4">
        <a href="login.php" class="d-flex align-items-center gap-2 text-decoration-none">
          <div style="width:38px;height:38px;font-size:1.15rem;border-radius:12px;background:linear-gradient(135deg,#0284c7,#0369a1);display:flex;align-items:center;justify-content:center;color:#fff;box-shadow:0 4px 12px rgba(2,132,199,0.3);">
            <i class="fa-solid fa-shield-halved"></i>
          </div>
          <div>
            <div style="font-weight:800;font-family:var(--font-heading);font-size:1.15rem;line-height:1;color:var(--text-main);">CCMS</div>
            <div style="font-size:0.68rem;color:#0284c7;text-transform:uppercase;font-weight:700;letter-spacing:.06em;">Central Oversight</div>
          </div>
        </a>

        <button class="theme-toggle-btn" id="themeToggle" title="Toggle Dark/Light Mode">
          <i class="fa-solid fa-moon" id="themeIcon"></i>
        </button>
      </div>

      <!-- Title & Description -->
      <div class="mb-3">
        <h2 style="font-size:1.45rem;margin-bottom:0.35rem;font-weight:800;font-family:var(--font-heading);">Super Admin Accreditation</h2>
        <p class="text-muted" style="font-size:0.85rem;">Authenticate with your master-issued credentials to access the CCMS Executive Command Center.</p>
      </div>

      <!-- Quick Demo Credentials Box -->
      <div class="demo-bar">
        <div>
          <i class="fa-solid fa-bolt text-info me-1"></i>
          <span>Demo: <strong>admin@ccms.com</strong> / <strong>admin123</strong></span>
        </div>
        <button type="button" class="btn btn-sm btn-outline-info py-0 px-2" style="font-size:0.72rem;border-radius:8px;" onclick="fillAdminDemo()">
          Autofill
        </button>
      </div>

      <!-- Admin Login Form with JS Validation + PHP POST -->
      <form method="POST" action="" id="adminLoginForm" onsubmit="return validateAdminLogin(event)">

        <!-- Email Address -->
        <div class="mb-3">
          <label class="form-label" style="font-size:0.85rem;font-weight:700;text-transform:uppercase;letter-spacing:0.04em;">Official Secure Email *</label>
          <div class="input-wrap">
            <input type="email" class="form-control-custom" id="adminEmail" name="email"
                   placeholder="admin@ccms.com" value="<?= htmlspecialchars($_POST['email'] ?? 'admin@ccms.com') ?>">
            <i class="fa-solid fa-envelope field-icon"></i>
          </div>
        </div>

        <!-- Password -->
        <div class="mb-4">
          <div class="d-flex justify-content-between align-items-center mb-1">
            <label class="form-label mb-0" style="font-size:0.85rem;font-weight:700;text-transform:uppercase;letter-spacing:0.04em;">Master Access Key / Password *</label>
            <span style="font-size:0.78rem;color:#0284c7;font-weight:600;"><i class="fa-solid fa-lock me-1"></i> Root Level</span>
          </div>
          <div class="input-wrap">
            <input type="password" class="form-control-custom" id="adminPassword" name="password"
                   placeholder="••••••••" value="admin123">
            <i class="fa-solid fa-lock field-icon"></i>
            <button type="button" class="btn-pwd-eye" onclick="togglePassView('adminPassword', this)" title="Show/Hide Password">
              <i class="fa-solid fa-eye"></i>
            </button>
          </div>
        </div>

        <!-- Submit Button -->
        <button type="submit" class="btn-auth mb-3" id="btnSubmitAdmin">
          <i class="fa-solid fa-fingerprint"></i>
          Authenticate &amp; Open Command Center
        </button>

        <div class="text-center mt-2 mb-1">
          <span class="text-muted" style="font-size:0.82rem;"><i class="fa-solid fa-shield-halved me-1 text-info"></i> Super Admin accounts are provisioned with master executive clearance.</span>
        </div>

      </form>

      <!-- Footer links -->
      <div class="pt-3 border-top mt-4 text-center" style="font-size:.8rem;">
        <a href="../login.php" class="text-primary text-decoration-none fw-bold d-inline-flex align-items-center gap-1">
          <i class="fa-solid fa-layer-group"></i> All-Panel Login Hub
        </a>
      </div>

    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <script>
    function fillAdminDemo() {
      document.getElementById('adminEmail').value = 'admin@ccms.com';
      document.getElementById('adminPassword').value = 'admin123';
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
      
      let parent = el.closest('.mb-3') || el.closest('.mb-4') || el.parentElement;
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
      let parent = el.closest('.mb-3') || el.closest('.mb-4') || el.parentElement;
      let err = parent.querySelector('.inline-error-text');
      if (err) {
        err.style.display = 'none';
      }
    }

    document.addEventListener('DOMContentLoaded', () => {
      ['adminEmail', 'adminPassword'].forEach(id => {
        const el = document.getElementById(id);
        if (el) {
          el.addEventListener('input', () => clearInlineError(id));
        }
      });
    });

    // Inline Red Text Validation for Admin Login
    function validateAdminLogin(e) {
      let isValid = true;
      ['adminEmail', 'adminPassword'].forEach(clearInlineError);

      const email = document.getElementById('adminEmail').value.trim();
      const pass = document.getElementById('adminPassword').value;

      if (!email || !email.includes('@')) {
        showInlineError('adminEmail', 'Please enter a valid administrator email address.');
        isValid = false;
      }
      if (!pass) {
        showInlineError('adminPassword', 'Please enter your master password.');
        isValid = false;
      }

      if (!isValid && e) {
        e.preventDefault();
      }
      return isValid;
    }

    // Theme Toggle Functionality
    const themeBtn = document.getElementById('themeToggle');
    const themeIcon = document.getElementById('themeIcon');

    function applyTheme(theme) {
      document.documentElement.setAttribute('data-bs-theme', theme);
      document.documentElement.setAttribute('data-theme', theme);
      localStorage.setItem('ccms_theme', theme);
      if (themeIcon) {
        themeIcon.className = theme === 'dark' ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
      }
    }

    if (themeBtn) {
      themeBtn.addEventListener('click', () => {
        const current = document.documentElement.getAttribute('data-bs-theme') || 'light';
        applyTheme(current === 'dark' ? 'light' : 'dark');
      });
    }

    document.addEventListener('DOMContentLoaded', () => {
      const savedTheme = localStorage.getItem('ccms_theme') || 'light';
      applyTheme(savedTheme);
    });
  </script>

  <!-- Server Feedback Alerts -->
  <?php if (!empty($error_msg)): ?>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      Swal.fire({
        icon: 'error',
        title: 'Authentication Denied',
        text: <?= json_encode($error_msg) ?>,
        confirmButtonColor: '#0284c7'
      });
    });
  </script>
  <?php endif; ?>

  <?php if (!empty($success_msg)): ?>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      Swal.fire({
        icon: 'success',
        title: 'Clearance Authorized!',
        text: <?= json_encode($success_msg) ?>,
        timer: 1300,
        showConfirmButton: false
      }).then(function() {
        window.location.href = 'index.php';
      });
    });
  </script>
  <?php endif; ?>

</body>
</html>
