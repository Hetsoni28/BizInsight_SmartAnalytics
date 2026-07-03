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

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 3. Verify CSRF Token to prevent cross-site forgery
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error = "Security validation failed. Please refresh the page and try again.";
    }

    if (empty($error)) {
        require_once 'includes/db.php';
        
        // 4. Sanitize Inputs thoroughly
        $name          = htmlspecialchars(trim($_POST['name'] ?? ''), ENT_QUOTES, 'UTF-8');
        $email         = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $password      = trim($_POST['password'] ?? '');
        $business_name = htmlspecialchars(trim($_POST['business_name'] ?? ''), ENT_QUOTES, 'UTF-8');
        $business_type = htmlspecialchars(trim($_POST['business_type'] ?? ''), ENT_QUOTES, 'UTF-8');

        // Basic Validation
        if ($name && filter_var($email, FILTER_VALIDATE_EMAIL) && $password && $business_name) {
            
            if (strlen($password) < 8) {
                $error = "Password must be at least 8 characters long.";
            } else {
                // Check if email already exists using Prepared Statements
                $check = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
                $check->bind_param("s", $email);
                $check->execute();
                
                if ($check->get_result()->num_rows > 0) {
                    $error = 'An account with this email address already exists.';
                } else {
                    // Secure Password Hashing
                    $hashed = password_hash($password, PASSWORD_DEFAULT);
                    
                    // Insert new user
                    $stmt = $conn->prepare("INSERT INTO users (name, email, password, business_name, business_type) VALUES (?, ?, ?, ?, ?)");
                    $stmt->bind_param("sssss", $name, $email, $hashed, $business_name, $business_type);
                    
                    if ($stmt->execute()) {
                        // Prevent form resubmission
                        session_regenerate_id(true);
                        $success = 'Account created successfully! Preparing your dashboard...';
                        header("refresh:2;url=login.php");
                    } else {
                        $error = 'Registration failed due to a server error. Please try again.';
                    }
                    $stmt->close();
                }
                $check->close();
            }
        } else {
            $error = 'Please fill in all required fields correctly.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account — BizInsight Analytics</title>
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <meta name="description" content="Register your business on BizInsight to start analyzing your sales data.">
    
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
            --green-success: #22c55e;
            --red-error: #ef4444;
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

        /* 50/50 CSS Grid Container */
        .auth-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            width: 100%;
            max-width: 1200px;
            min-height: 750px;
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

        .auth-left::after {
            content: '';
            position: absolute;
            inset: 0;
            background-image: url('https://images.unsplash.com/photo-1552664730-d307ca884978?auto=format&fit=crop&w=1000&q=80');
            background-size: cover;
            background-position: center;
            opacity: 0.12;
            mix-blend-mode: overlay;
            z-index: 1;
        }

        .left-content-top { position: relative; z-index: 2; }
        .logo { font-size: 24px; font-weight: 800; display: flex; align-items: center; gap: 10px; text-decoration: none; color: #fff; margin-bottom: 50px; }
        .auth-left h2 { font-size: 36px; font-weight: 800; line-height: 1.2; margin-bottom: 20px; letter-spacing: -0.5px; }
        .auth-left p { font-size: 16px; color: rgba(255,255,255,0.8); line-height: 1.6; max-width: 85%; margin-bottom: 40px; }

        .glass-features {
            position: relative; z-index: 2;
            background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.2); border-radius: 20px; padding: 32px;
        }
        .feature-item { display: flex; align-items: center; gap: 16px; margin-bottom: 16px; font-weight: 500; font-size: 15px; }
        .feature-item:last-child { margin-bottom: 0; }
        .feature-icon-box { background: rgba(255,255,255,0.2); width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border-radius: 8px; font-size: 14px; }

        /* ==================== RIGHT SIDE ==================== */
        .auth-right {
            padding: 50px 80px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            overflow-y: auto;
        }

        .back-btn {
            position: absolute; top: 40px; right: 40px;
            color: var(--text-muted); text-decoration: none; font-size: 14px; font-weight: 600;
            display: flex; align-items: center; gap: 6px; transition: color 0.2s;
        }
        .back-btn:hover { color: var(--blue-primary); }

        .form-header { margin-bottom: 30px; }
        .form-header h3 { font-size: 30px; font-weight: 800; color: var(--text-main); margin-bottom: 8px; }
        .form-header p { color: var(--text-muted); font-size: 15px; }

        /* Alerts */
        .alert { padding: 16px; border-radius: 12px; font-size: 14px; font-weight: 500; margin-bottom: 24px; display: flex; align-items: center; gap: 10px; animation: shake 0.4s ease-in-out; }
        .alert-error { background: #fef2f2; border: 1px solid #fca5a5; color: var(--red-error); }
        .alert-success { background: #f0fdf4; border: 1px solid #86efac; color: #166534; animation: fadeUpIn 0.4s ease-out; }

        /* Form Layout & Inputs */
        .form-row-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        
        .form-group { margin-bottom: 20px; position: relative; }
        .form-group label { display: block; font-size: 12px; font-weight: 700; color: var(--text-main); margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px; }
        .form-group label .req { color: var(--red-error); }
        
        .input-wrapper { position: relative; display: flex; align-items: center; }
        .input-field { 
            width: 100%; padding: 14px 16px;
            border: 1.5px solid var(--border-color); border-radius: 12px;
            font-size: 14px; color: var(--text-main); transition: all 0.2s; background: #fff;
            appearance: none;
        }
        select.input-field { background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2364748b'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 16px center; background-size: 16px; cursor: pointer; }
        
        .input-field:focus { border-color: var(--blue-primary); box-shadow: 0 0 0 4px rgba(26,127,212,0.15); outline: none; }
        .toggle-pwd { position: absolute; right: 16px; cursor: pointer; color: #94a3b8; font-size: 16px; user-select: none; transition: color 0.2s; }
        .toggle-pwd:hover { color: var(--blue-primary); }

        /* Live Password Strength Meter */
        .pwd-strength { margin-top: 8px; display: flex; flex-direction: column; gap: 6px; }
        .pwd-bars { display: flex; gap: 4px; height: 4px; }
        .pwd-bar { flex: 1; background: var(--border-color); border-radius: 2px; transition: all 0.3s; }
        .pwd-text { font-size: 11px; font-weight: 600; color: var(--text-muted); text-align: right; }

        /* Checkbox & Links */
        .checkbox-wrapper { display: flex; align-items: flex-start; gap: 10px; font-size: 13px; color: var(--text-muted); line-height: 1.5; margin-bottom: 28px; }
        .checkbox-wrapper input[type="checkbox"] { width: 16px; height: 16px; margin-top: 2px; accent-color: var(--blue-primary); cursor: pointer; }
        .checkbox-wrapper a { color: var(--blue-primary); font-weight: 600; text-decoration: none; }
        .checkbox-wrapper a:hover { text-decoration: underline; }

        /* Submit Button */
        .btn-submit {
            width: 100%; padding: 16px; background: var(--blue-primary); color: #fff;
            border: none; border-radius: 12px; font-size: 15px; font-weight: 700;
            cursor: pointer; transition: all 0.2s; position: relative; overflow: hidden;
            display: flex; justify-content: center; align-items: center; gap: 10px;
        }
        .btn-submit:hover { background: var(--blue-dark); transform: translateY(-2px); box-shadow: 0 10px 25px rgba(26,127,212,0.3); }
        .btn-submit.loading { background: var(--text-muted); pointer-events: none; transform: none; box-shadow: none; }
        
        .spinner { display: none; width: 20px; height: 20px; border: 3px solid rgba(255,255,255,0.3); border-radius: 50%; border-top-color: #fff; animation: spin 1s ease-in-out infinite; }
        .btn-submit.loading .btn-text { display: none; }
        .btn-submit.loading .spinner { display: block; }

        .auth-footer { text-align: center; margin-top: 32px; font-size: 14px; color: var(--text-muted); font-weight: 500; }
        .auth-footer a { color: var(--blue-primary); font-weight: 700; text-decoration: none; }
        
        /* Animations */
        .stagger-1 { animation: fadeUpIn 0.5s 0.1s both; }
        .stagger-2 { animation: fadeUpIn 0.5s 0.2s both; }
        .stagger-3 { animation: fadeUpIn 0.5s 0.3s both; }
        
        @keyframes fadeUpIn { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes shake { 0%, 100% { transform: translateX(0); } 25% { transform: translateX(-6px); } 75% { transform: translateX(6px); } }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* Responsive */
        @media (max-width: 1024px) { .auth-right { padding: 40px; } }
        @media (max-width: 768px) {
            .auth-container { grid-template-columns: 1fr; min-height: auto; }
            .auth-left { display: none; }
            .auth-right { padding: 40px 24px; }
            .form-row-grid { grid-template-columns: 1fr; gap: 0; }
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
            <h2>Your business analytics journey starts here.</h2>
            <p>Join hundreds of businesses converting raw sales data into actionable, dynamic dashboards instantly.</p>
        </div>

        <div class="glass-features">
            <div class="feature-item"><div class="feature-icon-box"><i class='bx bx-credit-card'></i></div><span>Free to start — No credit card</span></div>
            <div class="feature-item"><div class="feature-icon-box"><i class='bx bx-line-chart'></i></div><span>Auto-generate dynamic charts</span></div>
            <div class="feature-item"><div class="feature-icon-box"><i class='bx bxs-report'></i></div><span>Export professional PDF reports</span></div>
        </div>
    </div>

    <div class="auth-right">
        <a href="index.php" class="back-btn">Exit <i class='bx bx-x'></i></a>
        
        <div class="form-header stagger-1">
            <h3>Create Account</h3>
            <p>Set up your workspace in less than a minute.</p>
        </div>

        <?php if(!empty($error)): ?>
            <div class="alert alert-error stagger-1"><i class='bx bx-error-circle'></i> <span><?= htmlspecialchars($error) ?></span></div>
        <?php endif; ?>
        <?php if(!empty($success)): ?>
            <div class="alert alert-success stagger-1"><i class='bx bx-check-circle'></i> <span><?= htmlspecialchars($success) ?></span></div>
        <?php endif; ?>

        <form method="POST" action="register.php" onsubmit="handleFormSubmit(event)">
            
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

            <div class="form-row-grid stagger-2">
                <div class="form-group">
                    <label for="name">Full Name <span class="req">*</span></label>
                    <input type="text" id="name" name="name" class="input-field" placeholder="John Doe" required>
                </div>
                <div class="form-group">
                    <label for="business_name">Business Name <span class="req">*</span></label>
                    <input type="text" id="business_name" name="business_name" class="input-field" placeholder="e.g., Crazy Chat Corner" required>
                </div>
            </div>

            <div class="form-group stagger-2">
                <label for="email">Work Email <span class="req">*</span></label>
                <input type="email" id="email" name="email" class="input-field" placeholder="name@yourbusiness.com" required>
            </div>

            <div class="form-group stagger-3">
                <label for="password">Create Password <span class="req">*</span></label>
                <div class="input-wrapper">
                    <input type="password" id="password" name="password" class="input-field" placeholder="Minimum 8 characters" required minlength="8">
                    <span class="toggle-pwd" onclick="togglePassword()" title="Show Password"><i class='bx bx-show'></i></span>
                </div>
                <div class="pwd-strength">
                    <div class="pwd-bars">
                        <div class="pwd-bar" id="bar-1"></div>
                        <div class="pwd-bar" id="bar-2"></div>
                        <div class="pwd-bar" id="bar-3"></div>
                        <div class="pwd-bar" id="bar-4"></div>
                    </div>
                    <div class="pwd-text" id="pwd-text">Password strength</div>
                </div>
            </div>

            <div class="form-group stagger-3">
                <label for="business_type">Industry / Business Type</label>
                <select id="business_type" name="business_type" class="input-field">
                    <option value="" disabled selected>Select your industry</option>
                    <option value="Retail Store">Retail Store</option>
                    <option value="E-commerce">E-commerce</option>
                    <option value="Service Business">Service / Rentals</option>
                    <option value="Manufacturing">Manufacturing</option>
                    <option value="Food & Beverage">Food & Beverage</option>
                    <option value="Startup">Tech / Startup</option>
                </select>
            </div>

            <label class="checkbox-wrapper stagger-3">
                <input type="checkbox" required> 
                <span>I agree to the <a href="#">Terms of Service</a>, <a href="#">Privacy Policy</a>, and consent to electronic data processing.</span>
            </label>

            <button type="submit" class="btn-submit stagger-3" id="submitBtn">
                <span class="btn-text">Create Free Account →</span>
                <div class="spinner"></div>
            </button>
        </form>
        
        <div class="auth-footer stagger-3">
            Already have an account? <a href="login.php">Secure Sign In</a>
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

    // 2. Live Password Strength Meter
    const passwordInput = document.getElementById('password');
    const bars = [
        document.getElementById('bar-1'),
        document.getElementById('bar-2'),
        document.getElementById('bar-3'),
        document.getElementById('bar-4')
    ];
    const textLabel = document.getElementById('pwd-text');

    passwordInput.addEventListener('input', function() {
        const val = this.value;
        let strength = 0;

        if (val.length >= 8) strength += 1;
        if (val.match(/[A-Z]/) && val.match(/[a-z]/)) strength += 1;
        if (val.match(/[0-9]/)) strength += 1;
        if (val.match(/[^a-zA-Z0-9]/)) strength += 1;

        // Reset all bars
        bars.forEach(bar => { bar.style.backgroundColor = 'var(--border-color)'; });

        // Apply colors based on strength
        if (val.length === 0) {
            textLabel.textContent = 'Password strength';
            textLabel.style.color = 'var(--text-muted)';
        } else if (strength === 1 || val.length < 8) {
            bars[0].style.backgroundColor = 'var(--red-error)';
            textLabel.textContent = 'Weak';
            textLabel.style.color = 'var(--red-error)';
        } else if (strength === 2) {
            bars[0].style.backgroundColor = '#f59e0b';
            bars[1].style.backgroundColor = '#f59e0b';
            textLabel.textContent = 'Fair';
            textLabel.style.color = '#f59e0b';
        } else if (strength === 3) {
            bars[0].style.backgroundColor = '#3b82f6';
            bars[1].style.backgroundColor = '#3b82f6';
            bars[2].style.backgroundColor = '#3b82f6';
            textLabel.textContent = 'Good';
            textLabel.style.color = '#3b82f6';
        } else if (strength === 4) {
            bars.forEach(bar => bar.style.backgroundColor = 'var(--green-success)');
            textLabel.textContent = 'Strong';
            textLabel.style.color = 'var(--green-success)';
        }
    });

    // 3. Loading State & Double Submit Prevention
    function handleFormSubmit(event) {
        const btn = document.getElementById('submitBtn');
        if(btn.classList.contains('loading')) {
            event.preventDefault(); 
            return;
        }
        btn.classList.add('loading');
        // Let natural PHP POST handle the rest
    }
</script>

</body>
</html>
