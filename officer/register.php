<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../db.php';

$error_msg = '';
$success_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name          = trim($_POST['name'] ?? '');
    $email         = trim($_POST['email'] ?? '');
    $phone         = trim($_POST['phone'] ?? '');
    $designation   = trim($_POST['designation'] ?? '');
    $department_id = intval($_POST['department_id'] ?? 8);
    $password      = $_POST['password'] ?? '';
    $confirm_pass  = $_POST['confirm_password'] ?? '';
    $badge_no      = trim($_POST['badge_no'] ?? '');

    if (empty($name) || empty($email) || empty($password) || empty($designation)) {
        $error_msg = 'Please fill in all mandatory fields marked with an asterisk (*).';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_msg = 'Please enter a valid official email address.';
    } elseif (strlen($password) < 6) {
        $error_msg = 'Password must be at least 6 characters in length.';
    } elseif ($password !== $confirm_pass) {
        $error_msg = 'Passwords do not match. Please re-enter your password.';
    } else {
        // Check if officer with this email already exists
        $stmt_check = mysqli_prepare($conn, "SELECT officer_id FROM officers WHERE email = ? LIMIT 1");
        if ($stmt_check) {
            mysqli_stmt_bind_param($stmt_check, "s", $email);
            mysqli_stmt_execute($stmt_check);
            mysqli_stmt_store_result($stmt_check);

            if (mysqli_stmt_num_rows($stmt_check) > 0) {
                $error_msg = "An officer with email '{$email}' is already registered in the bureau roster.";
            } else {
                // Insert new officer
                $status = 'Active';
                $insert = mysqli_prepare($conn, "INSERT INTO officers (name, email, phone, password, status, designation, department_id, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
                if ($insert) {
                    mysqli_stmt_bind_param($insert, "ssssssi", $name, $email, $phone, $password, $status, $designation, $department_id);
                    if (mysqli_stmt_execute($insert)) {
                        $new_id = mysqli_insert_id($conn);

                        // Auto-login newly registered officer
                        $_SESSION['officer_id']      = $new_id;
                        $_SESSION['officer_name']    = $name;
                        $_SESSION['name']            = $name;
                        $_SESSION['officer_email']   = $email;
                        $_SESSION['email']           = $email;
                        $_SESSION['officer_phone']   = $phone;
                        $_SESSION['designation']     = $designation;
                        $_SESSION['department_id']   = $department_id;
                        $_SESSION['role']            = 'officer';

                        $success_msg = "Officer registration complete! Initializing Officer Command Dashboard...";
                    } else {
                        $error_msg = 'Database insertion failed: ' . mysqli_error($conn);
                    }
                    mysqli_stmt_close($insert);
                } else {
                    $error_msg = 'Failed to prepare registration statement.';
                }
            }
            mysqli_stmt_close($stmt_check);
        } else {
            $error_msg = 'Database error checking officer credentials.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Officer Registration &amp; Accreditation — CCMS</title>
  <meta name="description" content="Register new investigating officer credentials for Corruption Complaint Management System.">

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&family=Space+Grotesk:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">

  <!-- Bootstrap 5.3 & Font Awesome 6 Pro -->
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
      padding: 2.5rem 1rem;
      background: radial-gradient(circle at 15% 15%, rgba(6,182,212,.09) 0%, transparent 45%),
                  radial-gradient(circle at 85% 85%, rgba(79,70,229,.09) 0%, transparent 45%),
                  var(--bg-main);
      position: relative;
    }
    .auth-card {
      background: var(--bg-surface);
      border: 1px solid var(--border);
      border-radius: 24px;
      max-width: 580px;
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

    .form-control-custom, .form-select-custom {
      background: var(--bg-surface-2);
      border: 1.5px solid var(--border);
      border-radius: 12px;
      padding: 0.75rem 1rem 0.75rem 2.75rem;
      color: var(--text-main);
      font-size: 0.92rem;
      width: 100%;
      outline: none;
      transition: var(--transition);
      font-family: inherit;
    }
    .form-control-custom:focus, .form-select-custom:focus {
      border-color: var(--cyan);
      box-shadow: 0 0 15px rgba(6,182,212,.25);
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
      box-shadow: 0 10px 25px rgba(2,132,199,0.3);
    }
    .btn-auth:hover {
      transform: translateY(-2px);
      box-shadow: 0 12px 30px rgba(2,132,199,0.4);
      background: linear-gradient(135deg, #0369a1, #0284c7);
      color: #fff;
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
      <div class="d-flex align-items-center justify-content-between mb-3">
        <a href="../index.php" class="d-flex align-items-center gap-2 text-decoration-none">
          <div style="width:38px;height:38px;font-size:1.15rem;border-radius:12px;background:linear-gradient(135deg,#0284c7,#0369a1);display:flex;align-items:center;justify-content:center;color:#fff;box-shadow:0 4px 12px rgba(2,132,199,0.3);">
            <i class="fa-solid fa-shield-halved"></i>
          </div>
          <div>
            <div style="font-weight:800;font-family:var(--font-heading);font-size:1.15rem;line-height:1;color:var(--text-main);">CCMS</div>
            <div style="font-size:0.68rem;color:var(--cyan);text-transform:uppercase;font-weight:700;letter-spacing:.06em;">Investigator Command</div>
          </div>
        </a>

        <button class="theme-toggle-btn" id="themeToggle" title="Toggle Dark/Light Mode">
          <i class="fa-solid fa-moon" id="themeIcon"></i>
        </button>
      </div>

      <!-- Title & Subtitle -->
      <div class="mb-3">
        <h2 style="font-size:1.45rem;margin-bottom:0.35rem;font-weight:800;font-family:var(--font-heading);">Officer Accreditation Registration</h2>
        <p class="text-muted" style="font-size:0.85rem;">Enroll authorized investigation officers into the CCMS National Anti-Corruption Roster.</p>
      </div>

      <!-- Quick Fill Helper -->
      <div class="demo-bar">
        <div>
          <i class="fa-solid fa-wand-magic-sparkles text-info me-1"></i>
          <span>Sample: <strong>Insp. Vikram Roy</strong> / <strong>vikram@ccms.com</strong></span>
        </div>
        <button type="button" class="btn btn-sm btn-outline-info py-0 px-2" style="font-size:0.72rem;border-radius:8px;" onclick="fillSampleOfficer()">
          Fill Sample
        </button>
      </div>

      <!-- Registration Form -->
      <form method="POST" action="" id="officerRegForm" onsubmit="return validateOfficerReg(event)">

        <div class="row g-3">

          <!-- Full Name -->
          <div class="col-md-6">
            <label class="form-label" style="font-size:0.82rem;font-weight:700;text-transform:uppercase;letter-spacing:0.04em;">Full Name *</label>
            <div class="input-wrap">
              <input type="text" class="form-control-custom" id="regName" name="name"
                     placeholder="e.g. Insp. Vikram Roy" required>
              <i class="fa-solid fa-user-shield field-icon"></i>
            </div>
          </div>

          <!-- Official Email -->
          <div class="col-md-6">
            <label class="form-label" style="font-size:0.82rem;font-weight:700;text-transform:uppercase;letter-spacing:0.04em;">Official Secure Email *</label>
            <div class="input-wrap">
              <input type="email" class="form-control-custom" id="regEmail" name="email"
                     placeholder="vikram@ccms.com" required>
              <i class="fa-solid fa-envelope field-icon"></i>
            </div>
          </div>

          <!-- Phone Number -->
          <div class="col-md-6">
            <label class="form-label" style="font-size:0.82rem;font-weight:700;text-transform:uppercase;letter-spacing:0.04em;">Contact / Phone *</label>
            <div class="input-wrap">
              <input type="tel" class="form-control-custom" id="regPhone" name="phone"
                     placeholder="+91 98765 43210" required>
              <i class="fa-solid fa-phone field-icon"></i>
            </div>
          </div>

          <!-- Rank / Designation -->
          <div class="col-md-6">
            <label class="form-label" style="font-size:0.82rem;font-weight:700;text-transform:uppercase;letter-spacing:0.04em;">Rank / Designation *</label>
            <div class="input-wrap">
              <select class="form-select-custom" id="regDesignation" name="designation" required>
                <option value="Anti-Corruption Inspector">Anti-Corruption Inspector</option>
                <option value="Field Investigator">Field Investigator</option>
                <option value="Senior Vigilance Officer">Senior Vigilance Officer</option>
                <option value="Chief Inquiry Officer">Chief Inquiry Officer</option>
                <option value="Cyber Forensics Specialist">Cyber Forensics Specialist</option>
                <option value="Special Case Prosecutor">Special Case Prosecutor</option>
              </select>
              <i class="fa-solid fa-medal field-icon"></i>
            </div>
          </div>

          <!-- Department -->
          <div class="col-12">
            <label class="form-label" style="font-size:0.82rem;font-weight:700;text-transform:uppercase;letter-spacing:0.04em;">Assigned Department *</label>
            <div class="input-wrap">
              <select class="form-select-custom" id="regDepartment" name="department_id" required>
                <option value="8">Anti-Corruption Bureau (ACB)</option>
                <option value="6">Law Enforcement &amp; Police Service</option>
                <option value="2">Revenue &amp; Customs Vigilance</option>
                <option value="5">Land Administration &amp; Records Oversight</option>
                <option value="1">Public Works &amp; Infrastructure Oversight</option>
                <option value="3">Health &amp; Family Welfare Surveillance</option>
                <option value="4">Education &amp; Higher Studies Audit</option>
                <option value="7">Energy &amp; Power Resources Monitor</option>
              </select>
              <i class="fa-solid fa-building-columns field-icon"></i>
            </div>
          </div>

          <!-- Password -->
          <div class="col-md-6">
            <label class="form-label" style="font-size:0.82rem;font-weight:700;text-transform:uppercase;letter-spacing:0.04em;">Access Key / Password *</label>
            <div class="input-wrap">
              <input type="password" class="form-control-custom" id="regPassword" name="password"
                     placeholder="Min. 6 chars" required>
              <i class="fa-solid fa-lock field-icon"></i>
              <button type="button" class="btn-pwd-eye" onclick="togglePassView('regPassword', this)">
                <i class="fa-solid fa-eye"></i>
              </button>
            </div>
          </div>

          <!-- Confirm Password -->
          <div class="col-md-6">
            <label class="form-label" style="font-size:0.82rem;font-weight:700;text-transform:uppercase;letter-spacing:0.04em;">Confirm Password *</label>
            <div class="input-wrap">
              <input type="password" class="form-control-custom" id="regConfirmPass" name="confirm_password"
                     placeholder="Re-enter password" required>
              <i class="fa-solid fa-shield-check field-icon"></i>
              <button type="button" class="btn-pwd-eye" onclick="togglePassView('regConfirmPass', this)">
                <i class="fa-solid fa-eye"></i>
              </button>
            </div>
          </div>

        </div>

        <!-- Submit Button -->
        <button type="submit" class="btn-auth mt-4 mb-3">
          <i class="fa-solid fa-user-check"></i>
          Enroll Officer &amp; Launch Command
        </button>

        <!-- Switch to Login -->
        <div class="text-center pt-2 border-top">
          <span class="text-muted" style="font-size:0.85rem;">Already have an officer account? </span>
          <a href="login.php" class="text-primary fw-bold text-decoration-none" style="font-size:0.85rem;">
            <i class="fa-solid fa-arrow-right-to-bracket me-1"></i>Officer Sign In
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

  <script>
    function fillSampleOfficer() {
      document.getElementById('regName').value = 'Insp. Vikram Roy';
      document.getElementById('regEmail').value = 'vikram@ccms.com';
      document.getElementById('regPhone').value = '+91 98234 56789';
      document.getElementById('regDesignation').value = 'Anti-Corruption Inspector';
      document.getElementById('regDepartment').value = '8';
      document.getElementById('regPassword').value = 'vikram123';
      document.getElementById('regConfirmPass').value = 'vikram123';
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
      
      let parent = el.closest('.col-md-6') || el.closest('.col-12') || el.parentElement;
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
      let parent = el.closest('.col-md-6') || el.closest('.col-12') || el.parentElement;
      let err = parent.querySelector('.inline-error-text');
      if (err) {
        err.style.display = 'none';
      }
    }

    document.addEventListener('DOMContentLoaded', () => {
      ['regName', 'regEmail', 'regPhone', 'regPassword', 'regConfirmPass'].forEach(id => {
        const el = document.getElementById(id);
        if (el) {
          el.addEventListener('input', () => clearInlineError(id));
        }
      });
    });

    function validateOfficerReg(e) {
      let isValid = true;
      ['regName', 'regEmail', 'regPhone', 'regPassword', 'regConfirmPass'].forEach(clearInlineError);

      const name = document.getElementById('regName').value.trim();
      const email = document.getElementById('regEmail').value.trim();
      const phone = document.getElementById('regPhone').value.trim();
      const password = document.getElementById('regPassword').value;
      const confirmPass = document.getElementById('regConfirmPass').value;

      if (!name) {
        showInlineError('regName', 'Please provide officer full name.');
        isValid = false;
      }
      if (!email || !email.includes('@')) {
        showInlineError('regEmail', 'Please enter a valid official email address.');
        isValid = false;
      }
      if (!phone) {
        showInlineError('regPhone', 'Please enter a contact telephone number.');
        isValid = false;
      }
      if (!password || password.length < 6) {
        showInlineError('regPassword', 'Password must be at least 6 characters long.');
        isValid = false;
      }
      if (password !== confirmPass) {
        showInlineError('regConfirmPass', 'Passwords do not match.');
        isValid = false;
      }

      if (!isValid && e) {
        e.preventDefault();
      }
      return isValid;
    }

    // Theme Toggle
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
        title: 'Registration Error',
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
        title: 'Accreditation Granted!',
        text: <?= json_encode($success_msg) ?>,
        timer: 1600,
        showConfirmButton: false
      }).then(function() {
        window.location.href = 'index.php';
      });
    });
  </script>
  <?php endif; ?>

</body>
</html>
