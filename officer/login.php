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
        $error_msg = 'Please enter both your officer email and password.';
    } else {
        $stmt = mysqli_prepare($conn, "SELECT * FROM officers WHERE email = ? LIMIT 1");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $officer = mysqli_fetch_assoc($result);

            if ($officer && $officer['password'] === $password) {
                if (isset($officer['status']) && $officer['status'] !== 'Active') {
                    $error_msg = 'Your officer account is currently inactive or suspended. Contact Admin.';
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

                    $success_msg = "Credentials verified. Welcome Inspector {$officer['name']}!";
                }
            } else {
                $error_msg = 'Invalid officer email or access key.';
            }
            mysqli_stmt_close($stmt);
        } else {
            $error_msg = 'Database query failed.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Investigator &amp; Officer Access Gateway — CCMS</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&family=Space+Grotesk:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
  <link rel="stylesheet" href="assets/css/officer.css">

  <style>
    .auth-wrap {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 2rem 1rem;
      background: radial-gradient(circle at 15% 15%, rgba(6,182,212,.09) 0%, transparent 45%),
                  radial-gradient(circle at 85% 85%, rgba(79,70,229,.09) 0%, transparent 45%),
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
      box-shadow: 0 25px 60px -15px rgba(0,0,0,.15);
      position: relative;
      overflow: hidden;
      backdrop-filter: blur(16px);
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
    }
    .form-control-custom:focus {
      border-color: var(--cyan);
      box-shadow: 0 0 15px rgba(6,182,212,.25);
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
    .btn-pwd-eye:hover { color: var(--text-main); }
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
      box-shadow: 0 10px 25px rgba(2,132,199,0.3);
    }
    .btn-auth:hover {
      transform: translateY(-2px);
      box-shadow: 0 12px 30px rgba(2,132,199,0.4);
    }

    .demo-bar {
      background: rgba(2,132,199,0.08);
      border: 1px dashed rgba(2,132,199,0.3);
      border-radius: 12px;
      padding: 0.6rem 0.9rem;
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 1.25rem;
      font-size: 0.78rem;
    }
  </style>
</head>
<body>

  <div class="auth-wrap">
    <div class="auth-card">

      <!-- Brand Header -->
      <div class="d-flex align-items-center justify-content-between mb-4">
        <a href="../index.php" class="d-flex align-items-center gap-2 text-decoration-none">
          <div class="sb-logo" style="width:38px;height:38px;font-size:1.15rem;border-radius:12px;background:linear-gradient(135deg,#0284c7,#0369a1);display:flex;align-items:center;justify-content:center;color:#fff;">
            <i class="fa-solid fa-shield-halved"></i>
          </div>
          <div>
            <div style="font-weight:800;font-family:var(--font-heading);font-size:1.15rem;line-height:1;color:var(--text-main);">CCMS</div>
            <div style="font-size:0.68rem;color:var(--cyan);text-transform:uppercase;font-weight:700;letter-spacing:.06em;">Investigator Command</div>
          </div>
        </a>

        <button class="nav-icon-btn" id="themeToggle" title="Toggle Dark/Light Mode">
          <i class="fa-solid fa-moon" id="themeIcon"></i>
        </button>
      </div>

      <div class="mb-3">
        <h2 style="font-size:1.45rem;margin-bottom:0.35rem;font-weight:800;">Officer Accreditation</h2>
        <p class="text-muted" style="font-size:0.85rem;">Authenticate with your department-issued credentials to access your assigned case dockets.</p>
      </div>

      <!-- Quick Demo Credentials Box -->
      <div class="demo-bar">
        <div>
          <i class="fa-solid fa-bolt text-info me-1"></i>
          <span>Demo: <strong>rajesh@ccms.com</strong> / <strong>raj123</strong></span>
        </div>
        <button type="button" class="btn btn-sm btn-outline-info py-0 px-2" style="font-size:0.72rem;" onclick="fillOfficerDemo()">
          Autofill
        </button>
      </div>

      <!-- Officer Login Form with JS Validation + PHP POST -->
      <form method="POST" action="" id="officerLoginForm" onsubmit="return validateOfficerLogin(event)">

        <!-- Email Address -->
        <div class="mb-3">
          <label class="form-label" style="font-size:0.85rem;font-weight:700;">Official Secure Email *</label>
          <div class="input-wrap">
            <input type="email" class="form-control-custom" id="officerEmail" name="email"
                   placeholder="e.g. rajesh@ccms.com" value="<?= htmlspecialchars($_POST['email'] ?? 'rajesh@ccms.com') ?>">
            <i class="fa-solid fa-envelope field-icon"></i>
          </div>
        </div>

        <!-- Password -->
        <div class="mb-4">
          <div class="d-flex justify-content-between align-items-center mb-1">
            <label class="form-label mb-0" style="font-size:0.85rem;font-weight:700;">Officer Access Key / Password *</label>
            <a href="#" class="text-decoration-none" style="font-size:0.78rem;color:var(--cyan);">Forgot Key?</a>
          </div>
          <div class="input-wrap">
            <input type="password" class="form-control-custom" id="officerPass" name="password"
                   placeholder="••••••••" value="raj123">
            <i class="fa-solid fa-lock field-icon"></i>
            <button type="button" class="btn-pwd-eye" onclick="togglePassView('officerPass', this)" title="Show/Hide Password">
              <i class="fa-solid fa-eye"></i>
            </button>
          </div>
        </div>

        <!-- Submit Button -->
        <button type="submit" class="btn-auth mb-3">
          <i class="fa-solid fa-fingerprint"></i>
          Authenticate &amp; Open Command Center
        </button>

        <!-- Register Officer Option -->
        <div class="text-center mt-3 pt-3 border-top">
          <span class="text-muted" style="font-size:0.85rem;">Don't have an officer accreditation? </span>
          <a href="register.php" class="text-primary fw-bold text-decoration-none" style="font-size:0.85rem;">
            <i class="fa-solid fa-user-plus me-1"></i>Register New Officer
          </a>
        </div>

      </form>

      <!-- Footer links -->
      <div class="pt-3 border-top mt-4 d-flex justify-content-between align-items-center flex-wrap gap-2" style="font-size:.8rem;">
        <a href="../login.php" class="text-primary text-decoration-none fw-bold d-flex align-items-center gap-1">
          <i class="fa-solid fa-layer-group"></i> All-Panel Login Hub
        </a>
        <a href="../citizen/login.php" class="text-muted text-decoration-none d-flex align-items-center gap-1">
          <i class="fa-solid fa-users"></i> Citizen Portal
        </a>
        <a href="../admin/login.php" class="text-muted text-decoration-none d-flex align-items-center gap-1">
          <i class="fa-solid fa-lock"></i> Super Admin Panel
        </a>
      </div>

    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="assets/js/officer.js"></script>
  <script>
    function fillOfficerDemo() {
      document.getElementById('officerEmail').value = 'rajesh@ccms.com';
      document.getElementById('officerPass').value = 'raj123';
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
      ['officerEmail', 'officerPass'].forEach(id => {
        const el = document.getElementById(id);
        if (el) {
          el.addEventListener('input', () => clearInlineError(id));
        }
      });
    });

    // Inline Red Text Validation for Officer Login
    function validateOfficerLogin(e) {
      let isValid = true;
      ['officerEmail', 'officerPass'].forEach(clearInlineError);

      const email = document.getElementById('officerEmail').value.trim();
      const pass = document.getElementById('officerPass').value;

      if (!email || !email.includes('@')) {
        showInlineError('officerEmail', 'Please enter a valid official email address.');
        isValid = false;
      }
      if (!pass) {
        showInlineError('officerPass', 'Please enter your officer access key / password.');
        isValid = false;
      }

      if (!isValid && e) {
        e.preventDefault();
      }
      return isValid;
    }
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
        title: 'Accreditation Verified!',
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
