<?php
session_start();

// ==========================================
// REAL & LIVE LOGIC: Enterprise Security
// ==========================================

// 1. Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

// 2. Generate CSRF Token for form security
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// 3. Rate Limiting (Anti-Brute Force Protection)
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['last_attempt_time'] = time();
}

$error = '';
$max_attempts = 5;
$lockout_time = 60; // 60 seconds lockout
$submitted_email = ''; // Will hold safe email value for form re-population

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Check Rate Limit
    if ($_SESSION['login_attempts'] >= $max_attempts) {
        $time_passed = time() - $_SESSION['last_attempt_time'];
        if ($time_passed < $lockout_time) {
            $remaining = $lockout_time - $time_passed;
            $error = "Account locked due to multiple failed attempts. Try again in $remaining seconds.";
        } else {
            // Reset attempts after timeout
            $_SESSION['login_attempts'] = 0;
        }
    }

    // Verify CSRF Token
    if (empty($error) && (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']))) {
        $error = "Security token validation failed. Please refresh and try again.";
    }

    if (empty($error)) {
        require_once 'includes/db.php';
        
        // Sanitize & Validate Inputs
        $email    = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $password = trim($_POST['password'] ?? '');
        $submitted_email = htmlspecialchars($email ?? '', ENT_QUOTES, 'UTF-8'); // Safe re-population

        if (filter_var($email, FILTER_VALIDATE_EMAIL) && !empty($password)) {
            
            // Prepared statement to prevent SQL Injection
            $stmt = $conn->prepare("SELECT id, name, email, password FROM users WHERE email = ? LIMIT 1");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($row = $result->fetch_assoc()) {
                if (password_verify($password, $row['password'])) {
                    
                    // 4. Prevent Session Fixation Attacks
                    session_regenerate_id(true);
                    
                    // Reset login attempts on success
                    $_SESSION['login_attempts'] = 0;
                    
                    // Set secure session variables
                    $_SESSION['user_id']    = $row['id'];
                    $_SESSION['user_name']  = $row['name'];
                    $_SESSION['user_email'] = $row['email'];
                    $_SESSION['login_time'] = time();
                    
                    header("Location: dashboard.php");
                    exit();
                } else {
                    $_SESSION['login_attempts']++;
                    $_SESSION['last_attempt_time'] = time();
                    $error = 'Invalid email or password.';
                }
            } else {
                // To prevent email enumeration, we still increment login attempts
                $_SESSION['login_attempts']++;
                $_SESSION['last_attempt_time'] = time();
                $error = 'Invalid email or password.';
            }
            $stmt->close();
        } else {
            $error = 'Please enter a valid email and password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Login — BizInsight Analytics</title>
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <meta name="description" content="Login to BizInsight to access your business analytics dashboard.">
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        /* ==========================================
           BEAST MODE UI: Perfectly Aligned Grid
           ========================================== */
        :root {
            --blue-primary: #1a7fd4;
            --blue-dark: #0d5fa3;
            --blue-light: #e8f4ff;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
            --bg-main: #f8fafc;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        
        body { 
            font-family: 'Inter', sans-serif; 
            background-color: var(--bg-main); 
            min-height: 100vh; 
            display: flex; 
            align-items: center; 
            justify-content: center;
            padding: 24px;
        }

        /* The Main Container - CSS Grid ensures perfect 50/50 split */
        .auth-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            width: 100%;
            max-width: 1200px;
            min-height: 700px;
            background: #ffffff;
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
            overflow: hidden;
            opacity: 0;
            transform: translateY(20px);
            animation: fadeUpIn 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        /* ==================== LEFT SIDE ==================== */
        .auth-left {
            position: relative;
            background: linear-gradient(135deg, var(--blue-dark) 0%, var(--blue-primary) 100%);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 60px;
            color: #ffffff;
            overflow: hidden;
        }

        /* Background Image with Overlay */
        .auth-left::after {
            content: '';
            position: absolute;
            inset: 0;
            background-image: url('https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=1000&q=80');
            background-size: cover;
            background-position: center;
            opacity: 0.15;
            mix-blend-mode: overlay;
            z-index: 1;
        }

        .left-content-top { position: relative; z-index: 2; }
        .logo { font-size: 24px; font-weight: 800; display: flex; align-items: center; gap: 10px; text-decoration: none; color: #fff; margin-bottom: 60px; }
        .auth-left h2 { font-size: 36px; font-weight: 800; line-height: 1.2; margin-bottom: 20px; letter-spacing: -0.5px; }
        .auth-left p { font-size: 16px; color: rgba(255,255,255,0.8); line-height: 1.6; max-width: 80%; }

        /* Glassmorphism Feature Box */
        .glass-features {
            position: relative;
            z-index: 2;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            padding: 32px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }
        .feature-item { display: flex; align-items: center; gap: 16px; margin-bottom: 16px; font-weight: 500; font-size: 15px; }
        .feature-item:last-child { margin-bottom: 0; }
        .feature-icon-box { background: rgba(255,255,255,0.2); width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; border-radius: 10px; font-size: 16px; }

        /* ==================== RIGHT SIDE ==================== */
        .auth-right {
            padding: 60px 80px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
        }

        .back-btn {
            position: absolute; top: 40px; right: 40px;
            color: var(--text-muted); text-decoration: none; font-size: 14px; font-weight: 600;
            display: flex; align-items: center; gap: 6px; transition: color 0.2s;
        }
        .back-btn:hover { color: var(--blue-primary); }

        .form-header { margin-bottom: 40px; }
        .form-header h3 { font-size: 30px; font-weight: 800; color: var(--text-main); margin-bottom: 8px; }
        .form-header p { color: var(--text-muted); font-size: 15px; }

        /* Dynamic Alerts */
        .alert { padding: 16px; border-radius: 12px; font-size: 14px; font-weight: 500; margin-bottom: 24px; display: flex; align-items: center; gap: 10px; animation: shake 0.4s ease-in-out; }
        .alert-error { background: #fef2f2; border: 1px solid #fca5a5; color: #b91c1c; }
        .alert-demo { background: var(--blue-light); border: 1px solid #bfdbfe; color: var(--blue-dark); }

        /* Form Inputs */
        .form-group { margin-bottom: 24px; position: relative; }
        .form-group label { display: block; font-size: 13px; font-weight: 700; color: var(--text-main); margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px; }
        
        .input-wrapper { position: relative; display: flex; align-items: center; }
        .input-field { 
            width: 100%; padding: 16px 16px 16px 44px; /* Space for left icon */
            border: 1.5px solid var(--border-color); border-radius: 12px;
            font-size: 15px; color: var(--text-main); transition: all 0.2s; background: #fff;
        }
        .input-field:focus { border-color: var(--blue-primary); box-shadow: 0 0 0 4px rgba(26,127,212,0.15); outline: none; }
        
        .input-icon { position: absolute; left: 16px; color: #94a3b8; font-size: 18px; pointer-events: none; transition: color 0.2s; }
        .input-field:focus + .input-icon { color: var(--blue-primary); } /* Highlight icon on focus */

        .toggle-pwd { position: absolute; right: 16px; cursor: pointer; color: #94a3b8; font-size: 18px; user-select: none; transition: color 0.2s; }
        .toggle-pwd:hover { color: var(--blue-primary); }

        /* Live Logic UI Elements */
        .live-validation-icon { position: absolute; right: 16px; font-size: 16px; display: none; }
        .caps-warning { color: #d97706; font-size: 12px; font-weight: 600; margin-top: 8px; display: none; align-items: center; gap: 4px; }

        /* Form Actions */
        .form-actions { display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px; }
        .checkbox-wrapper { display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 14px; color: var(--text-muted); font-weight: 500; }
        .checkbox-wrapper input[type="checkbox"] { width: 16px; height: 16px; accent-color: var(--blue-primary); cursor: pointer; }
        .forgot-link { font-size: 14px; font-weight: 600; color: var(--blue-primary); text-decoration: none; transition: color 0.2s; }
        .forgot-link:hover { color: var(--blue-dark); text-decoration: underline; }

        /* Button */
        .btn-submit {
            width: 100%; padding: 18px; background: var(--blue-primary); color: #fff;
            border: none; border-radius: 12px; font-size: 16px; font-weight: 700;
            cursor: pointer; transition: all 0.2s; position: relative; overflow: hidden;
            display: flex; justify-content: center; align-items: center; gap: 10px;
        }
        .btn-submit:hover { background: var(--blue-dark); transform: translateY(-2px); box-shadow: 0 10px 25px rgba(26,127,212,0.3); }
        .btn-submit.loading { background: var(--text-muted); pointer-events: none; transform: none; box-shadow: none; }
        
        .spinner { display: none; width: 22px; height: 22px; border: 3px solid rgba(255,255,255,0.3); border-radius: 50%; border-top-color: #fff; animation: spin 1s ease-in-out infinite; }
        .btn-submit.loading .btn-text { display: none; }
        .btn-submit.loading .spinner { display: block; }

        /* Footer */
        .auth-footer { text-align: center; margin-top: 40px; font-size: 14px; color: var(--text-muted); font-weight: 500; }
        .auth-footer a { color: var(--blue-primary); font-weight: 700; text-decoration: none; }
        
        /* Staggered Animations for form elements */
        .stagger-1 { animation: fadeUpIn 0.5s 0.1s both; }
        .stagger-2 { animation: fadeUpIn 0.5s 0.2s both; }
        .stagger-3 { animation: fadeUpIn 0.5s 0.3s both; }
        .stagger-4 { animation: fadeUpIn 0.5s 0.4s both; }

        @keyframes fadeUpIn { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes shake { 0%, 100% { transform: translateX(0); } 25% { transform: translateX(-6px); } 75% { transform: translateX(6px); } }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .auth-right { padding: 40px; }
        }
        @media (max-width: 768px) {
            .auth-container { grid-template-columns: 1fr; min-height: auto; }
            .auth-left { display: none; } /* Hide image side on small screens for cleaner UX */
            .auth-right { padding: 40px 24px; }
            .back-btn { top: 24px; right: 24px; }
        }
    </style>
</head>
<body>

<div class="auth-container">
    
    <div class="auth-left">
        <div class="left-content-top">
            <a href="index.php" class="logo">
                <svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                BizInsight
            </a>
            <h2>Welcome back to <br>your command center.</h2>
            <p>Access your real-time analytics, dynamic reports, and AI-driven business insights.</p>
        </div>

        <div class="glass-features">
            <div class="feature-item">
                <div class="feature-icon-box"><i class='bx bx-bar-chart-alt-2'></i></div>
                <span>Live KPI Tracking Dashboard</span>
            </div>
            <div class="feature-item">
                <div class="feature-icon-box"><i class='bx bx-folder'></i></div>
                <span>Automated CSV / Excel Parsing</span>
            </div>
            <div class="feature-item">
                <div class="feature-icon-box"><i class='bx bx-bot'></i></div>
                <span>AI-Powered Profit Analysis</span>
            </div>
        </div>
    </div>

    <div class="auth-right">
        <a href="index.php" class="back-btn">Exit <i class='bx bx-x'></i></a>
        
        <div class="form-header stagger-1">
            <h3>Sign In</h3>
            <p>Enter your details to access your dashboard.</p>
        </div>

        <?php if(!empty($error)): ?>
            <div class="alert alert-error stagger-1">
                <i class='bx bx-error-circle'></i> <span><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>

        <div class="alert alert-demo stagger-1">
            <i class='bx bx-bulb'></i> <span><strong>Demo:</strong> <code>demo@bizinsight.com</code> / <code>Demo@1234</code></span>
        </div>

        <form method="POST" action="login.php" id="loginForm" onsubmit="handleFormSubmit(event)">
            
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

            <div class="form-group stagger-2">
                <label for="email">Email Address</label>
                <div class="input-wrapper">
                    <input type="email" id="email" name="email" class="input-field" placeholder="name@business.com" value="<?= $submitted_email ?>" required autocomplete="email">
                    <span class="input-icon"><i class='bx bx-envelope'></i></span>
                    <span class="live-validation-icon" id="emailCheck"><i class='bx bx-check-circle'></i></span>
                </div>
            </div>
            
            <div class="form-group stagger-3">
                <label for="password">Password</label>
                <div class="input-wrapper">
                    <input type="password" id="password" name="password" class="input-field" placeholder="••••••••" value="Demo@1234" required autocomplete="current-password">
                    <span class="input-icon"><i class='bx bx-lock-alt'></i></span>
                    <span class="toggle-pwd" onclick="togglePassword()" title="Show Password"><i class='bx bx-show'></i></span>
                </div>
                <div class="caps-warning" id="capsWarning"><i class='bx bx-error-circle'></i> Caps Lock is ON</div>
            </div>
            
            <div class="form-actions stagger-4">
                <label class="checkbox-wrapper">
                    <input type="checkbox" name="remember" checked> 
                    Remember me
                </label>
                <a href="#" class="forgot-link">Forgot password?</a>
            </div>
            
            <button type="submit" class="btn-submit stagger-4" id="submitBtn">
                <span class="btn-text">Secure Sign In →</span>
                <div class="spinner"></div>
            </button>
        </form>
        
        <div class="auth-footer stagger-4">
            Don't have an account? <a href="register.php">Create one for free</a>
        </div>
    </div>
</div>

<script>
    // 1. Password Visibility Toggle
    function togglePassword() {
        const pwdInput = document.getElementById('password');
        const pwdToggle = document.querySelector('.toggle-pwd');
        
        if (pwdInput.type === 'password') {
            pwdInput.type = 'text';
            pwdToggle.textContent = '<i class="bx bx-hide"></i>';
        } else {
            pwdInput.type = 'password';
            pwdToggle.textContent = '<i class="bx bx-show"></i>';
        }
    }

    // 2. LIVE LOGIC: Caps Lock Detector
    const passwordField = document.getElementById('password');
    const capsWarning = document.getElementById('capsWarning');
    
    passwordField.addEventListener('keyup', function(event) {
        if (event.getModifierState('CapsLock')) {
            capsWarning.style.display = 'flex';
        } else {
            capsWarning.style.display = 'none';
        }
    });

    // 3. LIVE LOGIC: Real-time Email Format Validation
    const emailField = document.getElementById('email');
    const emailCheck = document.getElementById('emailCheck');
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    emailField.addEventListener('input', function() {
        if (emailRegex.test(this.value)) {
            emailCheck.style.display = 'block';
            this.style.borderColor = '#22c55e'; // Green border on valid
        } else {
            emailCheck.style.display = 'none';
            this.style.borderColor = 'var(--border-color)'; // Reset
        }
        
        // Auto-hide PHP error alerts when typing
        const errorAlert = document.querySelector('.alert-error');
        if (errorAlert) {
            errorAlert.style.opacity = '0';
            setTimeout(() => errorAlert.remove(), 300);
        }
    });

    // Run email check on page load (for prepopulated demo values)
    if (emailRegex.test(emailField.value)) {
        emailCheck.style.display = 'block';
    }

    // 4. Loading State & Double Submit Prevention
    function handleFormSubmit(event) {
        const btn = document.getElementById('submitBtn');
        if(btn.classList.contains('loading')) {
            event.preventDefault(); // Prevent double click
            return;
        }
        btn.classList.add('loading');
        // Form submits naturally after this
    }
</script>

</body>
</html>
