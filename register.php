<?php
session_start();
include("db.php");

$error_msg = '';
$success_msg = '';

/* Register */
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    /* Validation */
    if ($name == '' || $email == '' || $password == '' || $address == '') {

        $error_msg = 'Please fill in all required fields.';

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error_msg = 'Please enter a valid email address.';

    } elseif ($password != $confirm_password) {

        $error_msg = 'Passwords do not match.';

    } elseif (strlen($password) < 6) {

        $error_msg = 'Password must be at least 6 characters long.';

    } else {

        /* Check email */
        $check = mysqli_query($conn,
            "SELECT user_id FROM users WHERE email='$email'"
        );

        if (mysqli_num_rows($check) > 0) {

            $error_msg = "An account with email '$email' is already registered. Please sign in.";

        } else {

            /* Insert user */
            $query = mysqli_query($conn,
                "INSERT INTO users
                (name, email, password, phone, address, role, created_at)
                VALUES
                ('$name', '$email', '$password', '$phone', '$address', 'Citizen', NOW())"
            );

            if ($query) {

                /* Get user ID */
                $user_id = mysqli_insert_id($conn);

                /* Session Variables */
                $_SESSION['user_id'] = $user_id;
                $_SESSION['user_name'] = $name;
                $_SESSION['name'] = $name;
                $_SESSION['user_email'] = $email;
                $_SESSION['email'] = $email;
                $_SESSION['user_phone'] = $phone;
                $_SESSION['user_address'] = $address;
                $_SESSION['user_role'] = 'Citizen';
                $_SESSION['role'] = 'citizen';

                $success_msg = "Account created successfully! Redirecting to your Citizen Dashboard...";

            } else {

                $error_msg = "Registration failed. Database error: " . mysqli_error($conn);
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
  <title>Citizen Registration — CCMS Anti-Corruption Portal</title>
  <meta name="description" content="Create a citizen account to lodge anti-corruption complaints and track investigation dossiers in real time.">

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
      top: -15%; right: -10%;
    }
    .orb-2 {
      width: 550px; height: 550px;
      background: radial-gradient(circle, rgba(2, 132, 199, 0.25), transparent 70%);
      bottom: -15%; left: -10%;
    }
    .orb-3 {
      width: 450px; height: 450px;
      background: radial-gradient(circle, rgba(16, 185, 129, 0.2), transparent 70%);
      top: 35%; left: 30%;
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
    [data-theme="light"] .grid-overlay {
      background-image:
        linear-gradient(rgba(0, 0, 0, 0.03) 1px, transparent 1px),
        linear-gradient(90deg, rgba(0, 0, 0, 0.03) 1px, transparent 1px);
    }

    /* Outer Wrapper: Split Layout */
    .portal-container {
      position: relative;
      z-index: 2;
      width: 100%;
      max-width: 1060px;
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
      background: linear-gradient(90deg, #10b981, #06b6d4, #6366f1, #10b981);
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
      background: linear-gradient(135deg, var(--emerald), var(--cyan));
      display: flex;
      align-items: center;
      justify-content: center;
      color: #fff;
      font-size: 1.35rem;
      box-shadow: 0 0 25px rgba(16, 185, 129, 0.35);
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

    /* Live Interactive ID Badge Card Preview */
    .live-badge-card {
      background: linear-gradient(135deg, rgba(15, 23, 42, 0.85), rgba(30, 41, 59, 0.95));
      border: 1px solid rgba(16, 185, 129, 0.35);
      border-radius: 20px;
      padding: 1.5rem;
      margin: 2rem 0;
      position: relative;
      overflow: hidden;
      box-shadow: 0 15px 35px -10px rgba(0, 0, 0, 0.5);
      transition: var(--transition);
    }
    .live-badge-card::before {
      content: '';
      position: absolute;
      top: -50%; left: -50%; width: 200%; height: 200%;
      background: radial-gradient(circle, rgba(16, 185, 129, 0.12) 0%, transparent 60%);
      pointer-events: none;
    }
    .badge-chip-bar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1.25rem;
    }
    .badge-microchip {
      width: 34px; height: 26px;
      border-radius: 6px;
      background: linear-gradient(135deg, #d97706, #fbbf24);
      box-shadow: inset 0 0 0 2px rgba(0,0,0,0.2);
    }
    .badge-status-pill {
      font-size: 0.68rem;
      font-weight: 700;
      font-family: var(--font-mono);
      padding: 0.25rem 0.6rem;
      border-radius: 99px;
      background: rgba(16, 185, 129, 0.15);
      color: var(--emerald);
      border: 1px solid rgba(16, 185, 129, 0.3);
      display: inline-flex;
      align-items: center;
      gap: 0.35rem;
    }
    .badge-name-display {
      font-size: 1.15rem;
      font-weight: 800;
      font-family: var(--font-heading);
      letter-spacing: -0.01em;
      margin-bottom: 0.2rem;
      color: #fff;
    }
    .badge-role-display {
      font-size: 0.78rem;
      color: var(--cyan);
      font-weight: 600;
      margin-bottom: 0.8rem;
    }
    .badge-details-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 0.5rem;
      font-size: 0.72rem;
      border-top: 1px solid rgba(255, 255, 255, 0.08);
      padding-top: 0.75rem;
    }
    .badge-detail-label { color: var(--text-subtle); text-transform: uppercase; font-size: 0.65rem; }
    .badge-detail-val { font-family: var(--font-mono); font-weight: 600; color: var(--text-main); }

    /* Security Specs list */
    .security-specs-list {
      list-style: none;
      padding: 0;
      margin: 0;
      font-size: 0.82rem;
      color: var(--text-muted);
    }
    .security-specs-list li {
      display: flex;
      align-items: center;
      gap: 0.6rem;
      margin-bottom: 0.65rem;
    }
    .security-specs-list li i {
      color: var(--emerald);
      font-size: 0.9rem;
    }

    /* Right Form Side */
    .form-side-panel {
      padding: 3rem 2.5rem;
    }

    .form-header-badge {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      padding: 0.35rem 0.85rem;
      border-radius: 99px;
      background: rgba(16, 185, 129, 0.12);
      border: 1px solid rgba(16, 185, 129, 0.25);
      color: var(--emerald);
      font-size: 0.75rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      margin-bottom: 1rem;
    }

    .field-group {
      margin-bottom: 1.2rem;
    }
    .field-label {
      display: block;
      font-size: 0.82rem;
      font-weight: 600;
      margin-bottom: 0.45rem;
      color: var(--text-main);
    }
    .field-label .req {
      color: var(--rose);
      margin-left: 2px;
    }
    .input-box {
      position: relative;
      display: flex;
      align-items: center;
    }
    .input-ico {
      position: absolute;
      left: 1rem;
      color: var(--text-subtle);
      font-size: 0.95rem;
      pointer-events: none;
      transition: var(--transition);
    }
    .form-input-ccms {
      width: 100%;
      background: var(--bg-surface-2);
      border: 1px solid var(--border);
      color: var(--text-main);
      font-size: 0.9rem;
      font-family: var(--font-body);
      padding: 0.75rem 1rem 0.75rem 2.65rem;
      border-radius: 12px;
      outline: none;
      transition: var(--transition);
    }
    .form-input-ccms:focus {
      border-color: var(--emerald);
      box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.18);
      background: var(--bg-surface);
    }
    .form-input-ccms:focus + .input-ico {
      color: var(--emerald);
    }

    .btn-pwd-eye {
      position: absolute;
      right: 0.85rem;
      background: none;
      border: none;
      color: var(--text-subtle);
      cursor: pointer;
      font-size: 0.9rem;
      padding: 0.25rem;
      transition: var(--transition);
    }
    .btn-pwd-eye:hover {
      color: var(--text-main);
    }

    /* Strength Meter */
    .strength-meter-bar {
      height: 4px;
      background: var(--bg-surface-2);
      border-radius: 99px;
      overflow: hidden;
      margin-top: 0.4rem;
    }
    .strength-fill {
      height: 100%;
      width: 0%;
      transition: width 0.3s ease, background-color 0.3s ease;
      background: var(--text-subtle);
      border-radius: 99px;
    }

    .btn-register-submit {
      width: 100%;
      background: linear-gradient(135deg, #10b981, #059669);
      border: none;
      color: #fff;
      font-weight: 700;
      font-size: 0.95rem;
      padding: 0.85rem;
      border-radius: 14px;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.6rem;
      transition: var(--transition);
      box-shadow: 0 12px 30px rgba(16, 185, 129, 0.35);
    }
    .btn-register-submit:hover {
      transform: translateY(-2px);
      box-shadow: 0 15px 35px rgba(16, 185, 129, 0.45);
    }

    .auth-bottom-switch {
      text-align: center;
      margin-top: 1.5rem;
      font-size: 0.85rem;
      color: var(--text-muted);
    }
    .auth-bottom-switch a {
      color: var(--primary-light);
      text-decoration: none;
      font-weight: 700;
    }
    .auth-bottom-switch a:hover {
      text-decoration: underline;
    }

    .notice-card {
      background: rgba(99, 102, 241, 0.08);
      border: 1px dashed var(--border);
      border-radius: 12px;
      padding: 0.75rem 1rem;
      font-size: 0.78rem;
      color: var(--text-muted);
      margin-top: 1.25rem;
      display: flex;
      align-items: flex-start;
      gap: 0.6rem;
    }

    @media (max-width: 991px) {
      .hero-side-panel {
        border-right: none;
        border-bottom: 1px solid var(--border);
        padding: 2rem 1.5rem;
      }
      .form-side-panel {
        padding: 2rem 1.5rem;
      }
    }
  </style>
</head>
<body>

  <!-- Ambient Glow Orbs -->
  <div class="ambient-orb orb-1"></div>
  <div class="ambient-orb orb-2"></div>
  <div class="ambient-orb orb-3"></div>
  <div class="grid-overlay"></div>

  <div class="portal-container">
    <div class="main-auth-card">
      <div class="row g-0">
        
        <!-- Left Side: Information & Citizen Identity Live Card -->
        <div class="col-lg-5 hero-side-panel">
          <div>
            <div class="d-flex justify-content-between align-items-center mb-4">
              <a href="index.php" class="brand-header-box">
                <div class="brand-logo-icon">
                  <i class="fa-solid fa-shield-halved"></i>
                </div>
                <div>
                  <div class="brand-logo-text">CCMS</div>
                  <div class="brand-logo-sub">Citizen Registration</div>
                </div>
              </a>

              <button class="btn btn-sm btn-outline-secondary border-0" id="themeToggleBtn" title="Toggle Dark/Light">
                <i class="fa-solid fa-moon" id="themeIcon"></i>
              </button>
            </div>

            <h3 style="font-size: 1.35rem; font-weight: 800; line-height: 1.3; margin-bottom: 0.4rem;">
              Empower Citizens, <br><span style="color: var(--emerald);">Expose Corruption</span>
            </h3>
            <p style="font-size: 0.85rem; color: var(--text-muted);">
              Every registered citizen receives an official record in the database for tracking complaints and maintaining secure whistleblower protection.
            </p>

            <!-- Dynamic Live ID Badge Card Preview -->
            <div class="live-badge-card" id="liveBadgeCard">
              <div class="badge-chip-bar">
                <div class="badge-microchip"></div>
                <div class="badge-status-pill" id="badgeStatusDisplay">
                  <i class="fa-solid fa-circle" style="font-size: 0.5rem;"></i> Active Citizen
                </div>
              </div>
              <div class="badge-name-display" id="badgeNameDisplay">Maria Gonzalez</div>
              <div class="badge-role-display" id="badgeRoleDisplay">Verified Citizen / Whistleblower</div>
              
              <div class="badge-details-grid">
                <div>
                  <div class="badge-detail-label">Registry Unit</div>
                  <div class="badge-detail-val">National Oversight</div>
                </div>
                <div>
                  <div class="badge-detail-label">City / Address</div>
                  <div class="badge-detail-val" id="badgeAddressDisplay" style="color: var(--cyan);">Ahmedabad</div>
                </div>
              </div>
            </div>
          </div>

          <div>
            <ul class="security-specs-list">
              <li><i class="fa-solid fa-shield-check"></i> <span>Relational MySQL Table Storage</span></li>
              <li><i class="fa-solid fa-server"></i> <span>Direct PHP Session Management</span></li>
              <li><i class="fa-solid fa-user-shield"></i> <span>Whistleblower Identity Safeguards</span></li>
            </ul>
          </div>
        </div>

        <!-- Right Side: Citizen Registration Form -->
        <div class="col-lg-7 form-side-panel">
          
          <div class="form-header-badge">
            <i class="fa-solid fa-user"></i> Citizen Portal Registration
          </div>

          <div class="mb-4">
            <h2 style="font-size: 1.5rem; font-weight: 800; margin-bottom: 0.25rem;" id="formTitle">
              Create Citizen Account
            </h2>
            <p style="font-size: 0.85rem; color: var(--text-muted);" id="formSubtitle">
              Enroll to submit corruption complaints, follow ongoing investigations, and communicate securely with assigned officers.
            </p>
          </div>

          <!-- Citizen Registration Form with JS validation + PHP POST -->
          <form method="POST" action="" id="citizenRegistrationForm" onsubmit="return validateCitizenForm(event)">
            
            <!-- Full Name -->
            <div class="field-group">
              <label class="field-label" for="inputName">
                <span>Full Name</span> <span class="req">*</span>
              </label>
              <div class="input-box">
                <input type="text" class="form-input-ccms" id="inputName" name="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" placeholder="e.g. Rahul Sharma" oninput="updateLiveBadge()">
                <i class="fa-solid fa-user input-ico"></i>
              </div>
            </div>

            <!-- Email & Phone -->
            <div class="row g-3">
              <div class="col-md-6 field-group">
                <label class="field-label" for="inputEmail">Email Address <span class="req">*</span></label>
                <div class="input-box">
                  <input type="email" class="form-input-ccms" id="inputEmail" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" placeholder="rahul@gmail.com">
                  <i class="fa-solid fa-envelope input-ico"></i>
                </div>
              </div>
              <div class="col-md-6 field-group">
                <label class="field-label" for="inputPhone">Phone Number <span class="req">*</span></label>
                <div class="input-box">
                  <input type="tel" class="form-input-ccms" id="inputPhone" name="phone" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" placeholder="9876543210" maxlength="15">
                  <i class="fa-solid fa-phone input-ico"></i>
                </div>
              </div>
            </div>

            <!-- Address Field (Added as requested) -->
            <div class="field-group">
              <label class="field-label" for="inputAddress">
                <span>Residential Address / City</span> <span class="req">*</span>
              </label>
              <div class="input-box">
                <input type="text" class="form-input-ccms" id="inputAddress" name="address" value="<?= htmlspecialchars($_POST['address'] ?? '') ?>" placeholder="e.g. Ahmedabad, Surat, Rajkot" oninput="updateLiveBadge()">
                <i class="fa-solid fa-location-dot input-ico"></i>
              </div>
            </div>

            <!-- Password & Confirm Password -->
            <div class="row g-3">
              <div class="col-md-6 field-group">
                <label class="field-label" for="inputPassword">Secure Password <span class="req">*</span></label>
                <div class="input-box">
                  <input type="password" class="form-input-ccms" id="inputPassword" name="password" placeholder="Min. 6 characters" oninput="checkStrength(this.value)">
                  <i class="fa-solid fa-lock input-ico"></i>
                  <button type="button" class="btn-pwd-eye" onclick="togglePassView('inputPassword', this)" title="Show/Hide Password">
                    <i class="fa-solid fa-eye"></i>
                  </button>
                </div>
              </div>
              <div class="col-md-6 field-group">
                <label class="field-label" for="inputConfirmPassword">Confirm Password <span class="req">*</span></label>
                <div class="input-box">
                  <input type="password" class="form-input-ccms" id="inputConfirmPassword" name="confirm_password" placeholder="Confirm password">
                  <i class="fa-solid fa-shield-halved input-ico"></i>
                  <button type="button" class="btn-pwd-eye" onclick="togglePassView('inputConfirmPassword', this)" title="Show/Hide Password">
                    <i class="fa-solid fa-eye"></i>
                  </button>
                </div>
              </div>
            </div>

            <!-- Password Strength Bar -->
            <div class="mb-3">
              <div class="strength-meter-bar">
                <div class="strength-fill" id="strengthFill"></div>
              </div>
              <div class="d-flex justify-content-between align-items-center mt-1" style="font-size: 0.72rem;">
                <span class="text-muted">Complexity: <strong id="strengthLabel" style="color: var(--text-subtle);">None</strong></span>
                <span class="font-monospace text-muted">Role: Citizen</span>
              </div>
            </div>

            <div class="form-check mb-4">
              <input class="form-check-input" type="checkbox" id="termsConsent" name="terms" checked>
              <label class="form-check-label" for="termsConsent" style="font-size: 0.8rem; color: var(--text-muted);">
                I acknowledge the official Code of Integrity and statutory anti-corruption rules.
              </label>
            </div>

            <button type="submit" class="btn-register-submit" id="btnSubmitForm">
              <i class="fa-solid fa-user-plus"></i>
              <span id="btnSubmitLabel">Create Citizen Account</span>
            </button>

          </form>

          <div class="notice-card">
            <i class="fa-solid fa-circle-info text-primary mt-1"></i>
            <div>
              <strong>Officer & Admin Accounts:</strong> Investigating officer accounts are provisioned exclusively by Central Administration via the Admin Panel.
            </div>
          </div>

          <div class="auth-bottom-switch">
            <span>Already have an account?</span>
            <a href="login.php?role=citizen" id="linkToSignIn" class="ms-1">
              <i class="fa-solid fa-arrow-right-to-bracket me-1"></i>Sign In to Portal
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
    // Live badge updater
    function updateLiveBadge() {
      const nameInput = document.getElementById('inputName').value.trim();
      const addrInput = document.getElementById('inputAddress').value.trim();
      const badgeName = document.getElementById('badgeNameDisplay');
      const badgeAddr = document.getElementById('badgeAddressDisplay');
      
      badgeName.innerText = nameInput || 'Maria Gonzalez';
      badgeAddr.innerText = addrInput || 'Ahmedabad';
    }

    // Toggle password view
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

    // Password strength check
    function checkStrength(p) {
      const fill = document.getElementById('strengthFill');
      const label = document.getElementById('strengthLabel');
      let score = 0;
      if (!p) {
        fill.style.width = '0%';
        label.innerText = 'None';
        label.style.color = 'var(--text-subtle)';
        return;
      }
      if (p.length >= 6) score += 25;
      if (p.length >= 10) score += 25;
      if (/[A-Z]/.test(p) && /[a-z]/.test(p)) score += 25;
      if (/[0-9]/.test(p) || /[^A-Za-z0-9]/.test(p)) score += 25;

      fill.style.width = score + '%';
      if (score <= 25) {
        fill.style.background = '#f43f5e';
        label.innerText = 'Weak';
        label.style.color = '#f43f5e';
      } else if (score <= 50) {
        fill.style.background = '#f59e0b';
        label.innerText = 'Fair';
        label.style.color = '#f59e0b';
      } else if (score <= 75) {
        fill.style.background = '#06b6d4';
        label.innerText = 'Good';
        label.style.color = '#06b6d4';
      } else {
        fill.style.background = '#10b981';
        label.innerText = 'Strong';
        label.style.color = '#10b981';
      }
    }

    function showInlineError(id, msg) {
      const el = document.getElementById(id);
      if (!el) return;
      el.classList.add('is-invalid');
      el.style.borderColor = '#ef4444';
      
      let parent = el.closest('.field-group') || el.closest('.mb-3') || el.closest('.mb-4') || el.parentElement;
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
      let parent = el.closest('.field-group') || el.closest('.mb-3') || el.closest('.mb-4') || el.parentElement;
      let err = parent.querySelector('.inline-error-text');
      if (err) {
        err.style.display = 'none';
      }
    }

    // Attach clearInlineError listeners to input fields
    document.addEventListener('DOMContentLoaded', () => {
      ['inputName', 'inputEmail', 'inputPhone', 'inputAddress', 'inputPassword', 'inputConfirmPassword', 'termsConsent'].forEach(id => {
        const el = document.getElementById(id);
        if (el) {
          el.addEventListener('input', () => clearInlineError(id));
          el.addEventListener('change', () => clearInlineError(id));
        }
      });
    });

    // Inline Red Text Validation for Citizen Registration
    function validateCitizenForm(e) {
      let isValid = true;
      ['inputName', 'inputEmail', 'inputPhone', 'inputAddress', 'inputPassword', 'inputConfirmPassword', 'termsConsent'].forEach(clearInlineError);

      const name = document.getElementById('inputName').value.trim();
      const email = document.getElementById('inputEmail').value.trim();
      const phone = document.getElementById('inputPhone').value.trim();
      const address = document.getElementById('inputAddress').value.trim();
      const password = document.getElementById('inputPassword').value;
      const confirmPassword = document.getElementById('inputConfirmPassword').value;
      const terms = document.getElementById('termsConsent').checked;

      if (!name) {
        showInlineError('inputName', 'Please enter your full legal name.');
        isValid = false;
      }
      if (!email || !email.includes('@')) {
        showInlineError('inputEmail', 'Please enter a valid email address.');
        isValid = false;
      }
      if (!phone) {
        showInlineError('inputPhone', 'Please enter your contact phone number.');
        isValid = false;
      }
      if (!address) {
        showInlineError('inputAddress', 'Please enter your residential address or city.');
        isValid = false;
      }
      if (!password || password.length < 6) {
        showInlineError('inputPassword', 'Password must be at least 6 characters.');
        isValid = false;
      }
      if (password && password !== confirmPassword) {
        showInlineError('inputConfirmPassword', 'Passwords do not match.');
        isValid = false;
      }
      if (!terms) {
        showInlineError('termsConsent', 'You must acknowledge the integrity rules.');
        isValid = false;
      }

      if (!isValid && e) {
        e.preventDefault();
      }
      return isValid;
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
      updateLiveBadge();
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
        title: 'Enrollment Successful!',
        text: <?= json_encode($success_msg) ?>,
        timer: 1500,
        showConfirmButton: false
      }).then(function() {
        window.location.href = 'citizen/dashboard.php';
      });
    });
  </script>
  <?php endif; ?>

</body>
</html>
