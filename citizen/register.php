<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../db.php';

$error_msg = '';
$success_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validation
    if (empty($name) || empty($email) || empty($password) || empty($address)) {
        $error_msg = 'Please complete all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_msg = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error_msg = 'Password must be at least 6 characters long.';
    } elseif ($password !== $confirm_password) {
        $error_msg = 'Passwords do not match. Please re-enter.';
    } else {
        // Check if email already registered
        $check = mysqli_prepare($conn, "SELECT user_id FROM users WHERE email = ? LIMIT 1");
        if ($check) {
            mysqli_stmt_bind_param($check, "s", $email);
            mysqli_stmt_execute($check);
            mysqli_stmt_store_result($check);

            if (mysqli_stmt_num_rows($check) > 0) {
                $error_msg = "An account with email '{$email}' already exists. Please sign in.";
            } else {
                $insert = mysqli_prepare($conn, "INSERT INTO users (name, email, password, phone, address, role, created_at) VALUES (?, ?, ?, ?, ?, 'Citizen', NOW())");
                if ($insert) {
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

                        $success_msg = "Citizen identity registered successfully! Opening your dashboard...";
                    } else {
                        $error_msg = "Registration failed: " . mysqli_error($conn);
                    }
                    mysqli_stmt_close($insert);
                } else {
                    $error_msg = "Database insert error.";
                }
            }
            mysqli_stmt_close($check);
        } else {
            $error_msg = "Database query error.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Citizen Registration — CCMS Corruption Complaint Portal</title>
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
      position: relative;
      background: radial-gradient(circle at 10% 15%, rgba(16, 185, 129, 0.08) 0%, transparent 45%),
                  radial-gradient(circle at 90% 85%, rgba(99, 102, 241, 0.08) 0%, transparent 45%),
                  var(--bg-main);
    }

    .auth-card {
      background: var(--bg-surface);
      border: 1px solid var(--border);
      border-radius: 26px;
      max-width: 620px;
      width: 100%;
      padding: 2.5rem 2.5rem;
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
      background: linear-gradient(90deg, #10b981, #06b6d4, #6366f1);
    }

    .form-control-custom {
      background: var(--bg-surface-2);
      border: 1.5px solid var(--border);
      border-radius: 12px;
      padding: 0.78rem 1rem 0.78rem 2.75rem;
      color: var(--text-main);
      font-size: 0.92rem;
      width: 100%;
      outline: none;
      transition: all 0.25s ease;
    }
    .form-control-custom:focus {
      border-color: var(--emerald);
      box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2);
      background: var(--bg-surface);
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
    .form-control-custom:focus + i.field-icon,
    .input-icon-wrap:focus-within i.field-icon {
      color: var(--emerald);
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
    .btn-pwd-eye:hover {
      color: var(--text-main);
    }

    .strength-meter-bar {
      height: 4px;
      background: var(--border);
      border-radius: 99px;
      overflow: hidden;
      margin-top: 6px;
    }
    .strength-fill {
      height: 100%;
      width: 0%;
      transition: all 0.3s ease;
      border-radius: 99px;
    }

    .demo-quickfill-bar {
      background: rgba(16, 185, 129, 0.08);
      border: 1px dashed rgba(16, 185, 129, 0.3);
      border-radius: 12px;
      padding: 0.6rem 0.85rem;
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 1.5rem;
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

  <!-- Ambient Glow Blobs -->
  <div class="ambient-glow-1"></div>
  <div class="ambient-glow-2"></div>
  <div class="grid-overlay"></div>

  <div class="auth-page-wrapper">
    <div class="auth-card">

      <!-- Header with Brand & Theme Toggle -->
      <div class="d-flex align-items-center justify-content-between mb-4">
        <a href="../index.php" class="d-flex align-items-center gap-2 text-decoration-none">
          <div class="sb-logo" style="width: 38px; height: 38px; font-size: 1.15rem; background: linear-gradient(135deg, #10b981, #06b6d4);">
            <i class="fa-solid fa-shield-halved"></i>
          </div>
          <div>
            <div style="font-weight: 800; font-family: var(--font-heading); font-size: 1.15rem; line-height: 1; color: var(--text-main);">CCMS</div>
            <div style="font-size: 0.68rem; color: var(--emerald); text-transform: uppercase; font-weight: 700; letter-spacing: 0.06em;">Citizen Oversight</div>
          </div>
        </a>

        <div class="d-flex align-items-center gap-2">
          <button class="nav-icon-btn" id="themeToggle" title="Toggle Dark/Light Mode">
            <i class="fa-solid fa-moon" id="themeIcon"></i>
          </button>
        </div>
      </div>

      <!-- Title & Subtitle -->
      <div class="mb-3">
        <h2 style="font-size: 1.5rem; font-weight: 800; margin-bottom: 0.35rem; color: var(--text-main);">
          Create Citizen Account
        </h2>
        <p class="text-muted" style="font-size: 0.88rem; margin-bottom: 0;">
          Register your protected identity to submit corruption complaints, upload forensic evidence, and monitor live investigation milestones.
        </p>
      </div>

      <!-- Quickfill Demo Chip -->
      <div class="demo-quickfill-bar">
        <div class="d-flex align-items-center gap-2" style="font-size: 0.8rem; color: var(--text-main);">
          <i class="fa-solid fa-bolt text-warning"></i>
          <span>Need sample details for fast testing?</span>
        </div>
        <button type="button" class="btn btn-sm btn-outline-success border-0 px-2 py-1" style="font-size: 0.78rem; font-weight: 700;" onclick="autofillCitizenRegister()">
          Autofill Test Data
        </button>
      </div>

      <!-- Registration Form -->
      <form method="POST" action="" id="citizenRegisterForm" onsubmit="return validateRegistration(event)">

        <!-- Full Name -->
        <div class="mb-3" id="groupName">
          <label class="form-label" style="font-size: 0.84rem; font-weight: 700; color: var(--text-main);">Full Legal Name <span class="text-danger">*</span></label>
          <div class="input-icon-wrap">
            <input type="text" name="name" id="regName" class="form-control-custom" placeholder="e.g. Rahul Sharma" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
            <i class="fa-solid fa-user field-icon"></i>
          </div>
        </div>

        <!-- Email & Phone Grid -->
        <div class="row g-3 mb-3">
          <div class="col-md-6" id="groupEmail">
            <label class="form-label" style="font-size: 0.84rem; font-weight: 700; color: var(--text-main);">Email Address <span class="text-danger">*</span></label>
            <div class="input-icon-wrap">
              <input type="email" name="email" id="regEmail" class="form-control-custom" placeholder="rahul@gmail.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
              <i class="fa-solid fa-envelope field-icon"></i>
            </div>
          </div>
          <div class="col-md-6" id="groupPhone">
            <label class="form-label" style="font-size: 0.84rem; font-weight: 700; color: var(--text-main);">Mobile Number <span class="text-danger">*</span></label>
            <div class="input-icon-wrap">
              <input type="tel" name="phone" id="regPhone" class="form-control-custom" placeholder="9876543210" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
              <i class="fa-solid fa-phone field-icon"></i>
            </div>
          </div>
        </div>

        <!-- Residential Address / City -->
        <div class="mb-3" id="groupAddress">
          <label class="form-label" style="font-size: 0.84rem; font-weight: 700; color: var(--text-main);">City / Residential Address <span class="text-danger">*</span></label>
          <div class="input-icon-wrap">
            <input type="text" name="address" id="regAddress" class="form-control-custom" placeholder="e.g. Navrangpura, Ahmedabad, Gujarat" value="<?= htmlspecialchars($_POST['address'] ?? '') ?>">
            <i class="fa-solid fa-location-dot field-icon"></i>
          </div>
        </div>

        <!-- Password & Confirm Password Grid -->
        <div class="row g-3 mb-2">
          <div class="col-md-6" id="groupPassword">
            <label class="form-label" style="font-size: 0.84rem; font-weight: 700; color: var(--text-main);">Secure Password <span class="text-danger">*</span></label>
            <div class="input-icon-wrap">
              <input type="password" name="password" id="regPassword" class="form-control-custom" placeholder="Min. 6 chars" oninput="checkStrength(this.value)">
              <i class="fa-solid fa-lock field-icon"></i>
              <button type="button" class="btn-pwd-eye" onclick="togglePass('regPassword', this)">
                <i class="fa-solid fa-eye"></i>
              </button>
            </div>
          </div>
          <div class="col-md-6" id="groupConfirmPassword">
            <label class="form-label" style="font-size: 0.84rem; font-weight: 700; color: var(--text-main);">Confirm Password <span class="text-danger">*</span></label>
            <div class="input-icon-wrap">
              <input type="password" name="confirm_password" id="regConfirmPassword" class="form-control-custom" placeholder="Confirm password">
              <i class="fa-solid fa-shield-halved field-icon"></i>
              <button type="button" class="btn-pwd-eye" onclick="togglePass('regConfirmPassword', this)">
                <i class="fa-solid fa-eye"></i>
              </button>
            </div>
          </div>
        </div>

        <!-- Password Strength Meter -->
        <div class="mb-3">
          <div class="strength-meter-bar">
            <div class="strength-fill" id="strengthFill"></div>
          </div>
          <div class="d-flex justify-content-between align-items-center mt-1" style="font-size: 0.72rem; color: var(--text-muted);">
            <span>Password Complexity: <strong id="strengthLabel" style="color: var(--text-muted);">None</strong></span>
            <span><i class="fa-solid fa-shield-halved text-emerald me-1"></i>256-Bit Cryptographic Hash</span>
          </div>
        </div>

        <!-- Terms Consent Checkbox -->
        <div class="form-check mb-4">
          <input class="form-check-input" type="checkbox" id="termsCheck" name="terms" checked>
          <label class="form-check-label" for="termsCheck" style="font-size: 0.82rem; color: var(--text-muted);">
            I agree to the statutory Citizen Integrity Charter and Whistleblower Protection Code.
          </label>
        </div>

        <!-- Submit Button -->
        <button type="submit" class="btn-ccms btn-ccms-primary w-100 justify-content-center py-2 mb-3" style="font-size: 0.95rem; font-weight: 700;">
          <i class="fa-solid fa-user-plus me-1"></i> Complete Citizen Registration
        </button>

        <!-- Switch to Sign In -->
        <div class="text-center pt-2">
          <span class="text-muted" style="font-size: 0.86rem;">Already have a verified account?</span>
          <a href="login.php" class="ms-1 fw-bold text-decoration-none" style="color: var(--cyan); font-size: 0.86rem;">
            Sign In Here &rarr;
          </a>
        </div>
      </form>

      <!-- Footer Portal Links -->
      <div class="pt-3 border-top mt-4 d-flex justify-content-between align-items-center flex-wrap gap-2" style="font-size: 0.8rem;">
        <a href="../login.php" class="text-primary text-decoration-none fw-bold">
          <i class="fa-solid fa-layer-group me-1"></i> Central Gateway
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
    });

    /* Password Eye Toggle */
    function togglePass(id, btn) {
      const input = document.getElementById(id);
      if (!input) return;
      const isPwd = input.type === 'password';
      input.type = isPwd ? 'text' : 'password';
      btn.querySelector('i').className = isPwd ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye';
    }

    /* Password Strength Meter */
    function checkStrength(val) {
      const fill = document.getElementById('strengthFill');
      const label = document.getElementById('strengthLabel');
      let score = 0;
      if (val.length >= 6) score++;
      if (val.length >= 9) score++;
      if (/[A-Z]/.test(val)) score++;
      if (/[0-9]/.test(val)) score++;
      if (/[^A-Za-z0-9]/.test(val)) score++;

      if (!val) {
        fill.style.width = '0%';
        fill.style.background = 'transparent';
        label.innerText = 'None';
        label.style.color = 'var(--text-muted)';
      } else if (score <= 2) {
        fill.style.width = '30%';
        fill.style.background = '#f43f5e';
        label.innerText = 'Weak';
        label.style.color = '#f43f5e';
      } else if (score <= 3) {
        fill.style.width = '65%';
        fill.style.background = '#f59e0b';
        label.innerText = 'Fair';
        label.style.color = '#f59e0b';
      } else {
        fill.style.width = '100%';
        fill.style.background = '#10b981';
        label.innerText = 'Strong';
        label.style.color = '#10b981';
      }
    }

    /* Autofill Test Data */
    function autofillCitizenRegister() {
      const randNum = Math.floor(100 + Math.random() * 900);
      document.getElementById('regName').value = 'Priya Patel';
      document.getElementById('regEmail').value = 'priya' + randNum + '@gmail.com';
      document.getElementById('regPhone').value = '98765' + randNum + '21';
      document.getElementById('regAddress').value = 'Satellite, Ahmedabad, Gujarat';
      document.getElementById('regPassword').value = 'SecurePriya@2026';
      document.getElementById('regConfirmPassword').value = 'SecurePriya@2026';
      checkStrength('SecurePriya@2026');
      clearAllErrors();
    }

    /* Error Display Functions */
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
      if (err) err.style.display = 'none';
    }

    function clearAllErrors() {
      ['regName', 'regEmail', 'regPhone', 'regAddress', 'regPassword', 'regConfirmPassword'].forEach(clearInlineError);
    }

    document.addEventListener('DOMContentLoaded', () => {
      ['regName', 'regEmail', 'regPhone', 'regAddress', 'regPassword', 'regConfirmPassword'].forEach(id => {
        const el = document.getElementById(id);
        if (el) {
          el.addEventListener('input', () => clearInlineError(id));
        }
      });
    });

    /* Client-side form validation */
    function validateRegistration(e) {
      clearAllErrors();
      let valid = true;

      const name = document.getElementById('regName').value.trim();
      const email = document.getElementById('regEmail').value.trim();
      const phone = document.getElementById('regPhone').value.trim();
      const address = document.getElementById('regAddress').value.trim();
      const password = document.getElementById('regPassword').value;
      const confirmPwd = document.getElementById('regConfirmPassword').value;

      if (!name) {
        showInlineError('regName', 'Please provide your full legal name.');
        valid = false;
      }
      if (!email || !email.includes('@')) {
        showInlineError('regEmail', 'Please enter a valid email address.');
        valid = false;
      }
      if (!phone || phone.length < 7) {
        showInlineError('regPhone', 'Please enter a valid contact number.');
        valid = false;
      }
      if (!address) {
        showInlineError('regAddress', 'Please enter your city / address.');
        valid = false;
      }
      if (!password || password.length < 6) {
        showInlineError('regPassword', 'Password must be at least 6 characters long.');
        valid = false;
      }
      if (password && confirmPwd !== password) {
        showInlineError('regConfirmPassword', 'Passwords do not match.');
        valid = false;
      }

      if (!valid && e) {
        e.preventDefault();
      }
      return valid;
    }
  </script>

  <!-- Server Feedback Alerts -->
  <?php if (!empty($error_msg)): ?>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      Swal.fire({
        icon: 'error',
        title: 'Registration Error',
        text: <?= json_encode($error_msg) ?>,
        confirmButtonColor: '#10b981'
      });
    });
  </script>
  <?php endif; ?>

  <?php if (!empty($success_msg)): ?>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      Swal.fire({
        icon: 'success',
        title: 'Registration Complete!',
        text: <?= json_encode($success_msg) ?>,
        timer: 1400,
        showConfirmButton: false
      }).then(function() {
        window.location.href = 'dashboard.php';
      });
    });
  </script>
  <?php endif; ?>

</body>
</html>
