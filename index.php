<?php
session_start();

// ==========================================
// ENTERPRISE BACKEND LOGIC: Session & Security
// ==========================================

// 1. Advanced Session & Visitor Analytics Tracking
if (!isset($_SESSION['visitor_id'])) {
    $_SESSION['visitor_id'] = bin2hex(random_bytes(16));
    $_SESSION['first_visit'] = time();
    $_SESSION['page_views'] = 1;
    $_SESSION['source'] = $_SERVER['HTTP_REFERER'] ?? 'Direct';
} else {
    $_SESSION['page_views']++;
}

// 2. CSRF Token Generation for Footer Newsletter Form
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// 3. Dynamic Time-Based Logic
$hour = (int)date('H');
if ($hour < 12) {
    $greeting = 'Good Morning';
    $theme_hint = 'Start your day with clear data.';
} elseif ($hour < 17) {
    $greeting = 'Good Afternoon';
    $theme_hint = 'Optimize your mid-day operations.';
} else {
    $greeting = 'Good Evening';
    $theme_hint = 'Review your daily performance.';
}

// Simulated Server Health Logic
$server_load = rand(10, 35) . '%';
$active_connections = rand(1200, 1500);
$server_uptime = '99.99% Uptime';

// ==========================================
// DYNAMIC CONTENT ARRAYS (CMS Simulation)
// ==========================================

// BENTO GRID FEATURES
$features = [
    [
        "icon" => "📊", 
        "title" => "Live Analytics Dashboard", 
        "desc" => "Real-time KPI cards, revenue charts, and interactive sales analytics updated instantly via database triggers.", 
        "img" => "https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=800&q=80",
        "span" => "large"
    ],
    [
        "icon" => "📁", 
        "title" => "Automated Ingestion", 
        "desc" => "Upload CSV/Excel files. Our PHP parser cleans and stores raw data securely.", 
        "img" => null,
        "span" => "small"
    ],
    [
        "icon" => "📑", 
        "title" => "Enterprise Reporting", 
        "desc" => "Export complex RDBMS queries as formatted PDF or Excel sheets with one click.", 
        "img" => null,
        "span" => "small"
    ],
    [
        "icon" => "🤖", 
        "title" => "AI Predictive Insights", 
        "desc" => "Automated suggestions on revenue trends, churn prediction, and top products powered by custom algorithmic logic.", 
        "img" => "https://images.unsplash.com/photo-1551288049-bebda4e38f71?auto=format&fit=crop&w=800&q=80",
        "span" => "large"
    ]
];

// TESTIMONIALS (Cleaned of old project references as requested)
$testimonials = [
    [
        "name" => "Mili",
        "role" => "Founder & CEO",
        "company" => "Apex Retail Solutions",
        "img" => "https://ui-avatars.com/api/?name=Mili&background=1a7fd4&color=fff&size=128",
        "text" => "BizInsight completely transformed how we track our daily orders and operational costs. We no longer rely on paper ledgers, and the dynamic growth charts let me see exactly which product lines are driving our net profit margins."
    ],
    [
        "name" => "Hetvi",
        "role" => "Operations Director",
        "company" => "Nexa Global Logistics",
        "img" => "https://ui-avatars.com/api/?name=Hetvi&background=22c55e&color=fff&size=128",
        "text" => "The Excel upload feature is an absolute lifesaver. We dump our monthly fleet data into the system, and the automated insights immediately tell us which routes are underperforming across different regions."
    ],
    [
        "name" => "Het",
        "role" => "Financial Controller",
        "company" => "Elevate E-Commerce",
        "img" => "https://ui-avatars.com/api/?name=Het&background=a855f7&color=fff&size=128",
        "text" => "Integrating this analytics architecture into our workflow gave us the ability to track campaign ROI in real-time. The custom PDF reporting module is perfect for our monthly board meetings."
    ]
];

// FAQS
$faqs = [
    ["q" => "Is my business data secure on this platform?", "a" => "Absolutely. We utilize industry-standard bcrypt hashing, CSRF token validation, and prepared SQL statements to ensure your financial data is fully isolated, encrypted, and secure against injection attacks."],
    ["q" => "Can I upload data from legacy POS systems?", "a" => "Yes. As long as your system exports to a standard CSV or Excel format, our Data Ingestion Module will automatically parse, clean, and map your data into relational tables."],
    ["q" => "Do I need technical knowledge to generate reports?", "a" => "No. The dashboard is entirely GUI-driven. You can generate complex queries visually and export them to PDF or Excel with a single click."],
    ["q" => "Is there a limit on how many records I can upload?", "a" => "Our Enterprise architecture utilizes optimized MySQL indexing, allowing you to process millions of rows without a drop in dashboard rendering speed."]
];

// PHP to JS Data Bridge for Live Preview Chart
$live_chart_labels = json_encode(['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct']);
$live_chart_data = json_encode([12500, 19200, 15400, 28000, 22000, 35000, 41000, 48500, 45000, 58000]);
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BizInsight — Business Analytics Dashboard</title>
    <meta name="description" content="Turn raw CSV and POS data into actionable business intelligence with BizInsight.">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;700&display=swap" rel="stylesheet">
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        /* ============================================================
           BEAST MODE CSS ARCHITECTURE (Strict Alignment, Grid, Flexbox)
           ============================================================ */
        
        :root {
            /* Core Color Palette Maintained strictly */
            --blue-primary: #1a7fd4;
            --blue-dark: #0d5fa3;
            --blue-light: #e8f4ff;
            --blue-glow: rgba(26, 127, 212, 0.4);
            
            --text-main: #0f172a;
            --text-muted: #64748b;
            --text-light: #94a3b8;
            
            --border: #e2e8f0;
            --border-dark: #334155;
            
            --bg-main: #f8fafc;
            --bg-white: #ffffff;
            --bg-dark: #020617;
            --bg-darker: #000000;
            
            --glass-bg: rgba(255, 255, 255, 0.85);
            --glass-dark: rgba(15, 23, 42, 0.7);
            
            --success: #22c55e;
            --warning: #f59e0b;
            --danger: #ef4444;
            --purple: #a855f7;

            /* Shadows & Effects */
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            --shadow-xl: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            --shadow-glow: 0 10px 30px -10px var(--blue-glow);
            
            /* Typography */
            --font-sans: 'Inter', sans-serif;
            --font-mono: 'JetBrains Mono', monospace;
        }

        /* --- 1. CSS Reset & Base Setup --- */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html { scroll-behavior: smooth; font-size: 16px; }
        body { 
            font-family: var(--font-sans); 
            background-color: var(--bg-main); 
            color: var(--text-main); 
            overflow-x: hidden; 
            line-height: 1.6; 
            -webkit-font-smoothing: antialiased;
        }
        a { text-decoration: none; color: inherit; }
        ul { list-style: none; }
        img { max-width: 100%; height: auto; display: block; }
        button, input, select, textarea { font-family: inherit; }
        
        /* Strict Alignment Container */
        .container { 
            width: 100%; 
            max-width: 1320px; 
            margin: 0 auto; 
            padding: 0 32px; 
        }

        /* --- 2. Global Utilities & Animations --- */
        .scroll-progress { 
            position: fixed; top: 0; left: 0; width: 0%; height: 4px; 
            background: linear-gradient(90deg, var(--blue-dark), var(--blue-primary), var(--purple)); 
            z-index: 9999; transition: width 0.1s ease-out; 
        }

        /* Scroll Reveals */
        .reveal { opacity: 0; transform: translateY(40px); transition: all 0.8s cubic-bezier(0.16, 1, 0.3, 1); }
        .reveal.active { opacity: 1; transform: translateY(0); }
        .reveal-left { opacity: 0; transform: translateX(-50px); transition: all 0.8s cubic-bezier(0.16, 1, 0.3, 1); }
        .reveal-left.active { opacity: 1; transform: translateX(0); }
        .reveal-right { opacity: 0; transform: translateX(50px); transition: all 0.8s cubic-bezier(0.16, 1, 0.3, 1); }
        .reveal-right.active { opacity: 1; transform: translateX(0); }
        .reveal-delay-1 { transition-delay: 0.1s; }
        .reveal-delay-2 { transition-delay: 0.2s; }
        .reveal-delay-3 { transition-delay: 0.3s; }
        
        /* Keyframes */
        @keyframes float { 0% { transform: translateY(0px); } 50% { transform: translateY(-20px); } 100% { transform: translateY(0px); } }
        @keyframes float-reverse { 0% { transform: translateY(0px); } 50% { transform: translateY(15px); } 100% { transform: translateY(0px); } }
        @keyframes pulse-ring { 0% { transform: scale(0.8); box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7); } 70% { transform: scale(1); box-shadow: 0 0 0 15px rgba(34, 197, 94, 0); } 100% { transform: scale(0.8); box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); } }
        @keyframes typing { from { width: 0; } to { width: 100%; } }
        @keyframes blink-caret { from, to { border-color: transparent; } 50% { border-color: var(--blue-primary); } }
        @keyframes marquee { 0% { transform: translateX(0); } 100% { transform: translateX(-50%); } }

        /* Buttons */
        .btn { 
            display: inline-flex; align-items: center; justify-content: center; gap: 10px; 
            padding: 16px 32px; border-radius: 12px; font-weight: 700; font-size: 16px; 
            cursor: pointer; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); 
            border: 2px solid transparent; letter-spacing: -0.2px;
        }
        .btn-primary { background: var(--blue-primary); color: #fff; box-shadow: var(--shadow-glow); }
        .btn-primary:hover { background: var(--blue-dark); transform: translateY(-3px); box-shadow: 0 15px 35px -10px var(--blue-glow); }
        
        .btn-outline { border-color: var(--border); color: var(--text-main); background: var(--bg-white); }
        .btn-outline:hover { border-color: var(--blue-primary); color: var(--blue-primary); transform: translateY(-3px); box-shadow: var(--shadow-md); }
        
        .btn-dark { background: var(--text-main); color: #fff; }
        .btn-dark:hover { background: #000; transform: translateY(-3px); box-shadow: var(--shadow-lg); }

        /* --- 3. NAVIGATION BAR --- */
        .navbar { 
            position: fixed; top: 0; width: 100%; z-index: 1000; 
            padding: 24px 0; transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1); 
        }
        .navbar.scrolled { 
            background: var(--glass-bg); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
            padding: 16px 0; border-bottom: 1px solid rgba(0,0,0,0.05); box-shadow: var(--shadow-sm); 
        }
        .nav-wrapper { display: flex; justify-content: space-between; align-items: center; }
        
        .logo { font-size: 26px; font-weight: 900; color: var(--blue-primary); display: flex; align-items: center; gap: 10px; letter-spacing: -0.5px; }
        .logo svg { width: 32px; height: 32px; transition: transform 0.3s ease; }
        .logo:hover svg { transform: rotate(15deg) scale(1.1); }
        
        .nav-links { display: flex; gap: 40px; align-items: center; }
        .nav-links a { color: var(--text-muted); font-weight: 600; font-size: 15px; transition: color 0.2s; position: relative; }
        .nav-links a::after { content: ''; position: absolute; width: 0; height: 2px; bottom: -4px; left: 0; background-color: var(--blue-primary); transition: width 0.3s ease; }
        .nav-links a:hover { color: var(--text-main); }
        .nav-links a:hover::after { width: 100%; }
        
        /* Show greeting on wider screens */
        .nav-greeting-text { display: none; }
        @media (min-width: 768px) { .nav-greeting-text { display: block; } }
        
        .nav-auth { display: flex; align-items: center; gap: 16px; }
        .nav-auth .btn { padding: 12px 24px; font-size: 14px; }

        /* --- 4. HERO SECTION --- */
        .hero { 
            position: relative; padding: 220px 0 140px; background: var(--bg-white); 
            overflow: hidden; display: flex; align-items: center; min-height: 100vh; 
        }
        .hero-bg-elements { position: absolute; inset: 0; pointer-events: none; overflow: hidden; }
        .hero-bg-elements .orb-1 { position: absolute; top: -10%; right: -5%; width: 800px; height: 800px; background: radial-gradient(circle, rgba(26,127,212,0.08) 0%, transparent 70%); border-radius: 50%; filter: blur(50px); animation: float 10s ease-in-out infinite; }
        .hero-bg-elements .orb-2 { position: absolute; bottom: -20%; left: -10%; width: 1000px; height: 1000px; background: radial-gradient(circle, rgba(168,85,247,0.05) 0%, transparent 70%); border-radius: 50%; filter: blur(60px); animation: float-reverse 12s ease-in-out infinite; }
        
        .hero-grid { 
            display: grid; grid-template-columns: 1fr 1fr; gap: 64px; 
            align-items: center; position: relative; z-index: 10; 
        }
        
        .hero-badge { 
            display: inline-flex; align-items: center; gap: 10px; padding: 8px 20px; 
            background: var(--blue-light); color: var(--blue-dark); border-radius: 100px; 
            font-size: 14px; font-weight: 700; margin-bottom: 32px; border: 1px solid rgba(26,127,212,0.2); 
            box-shadow: 0 4px 12px rgba(26,127,212,0.1);
        }
        .live-dot { width: 10px; height: 10px; background: var(--success); border-radius: 50%; animation: pulse-ring 2s infinite; }
        
        .hero h1 { font-size: 72px; font-weight: 900; line-height: 1.1; margin-bottom: 24px; letter-spacing: -2px; color: var(--text-main); }
        .typing-container { display: inline-block; }
        .typing-text { 
            border-right: 4px solid var(--blue-primary); white-space: nowrap; overflow: hidden; 
            animation: typing 3.5s steps(40, end), blink-caret .75s step-end infinite; 
            background: linear-gradient(135deg, var(--blue-primary), var(--purple)); 
            -webkit-background-clip: text; -webkit-text-fill-color: transparent; 
        }
        
        .hero p { font-size: 20px; color: var(--text-muted); line-height: 1.7; margin-bottom: 48px; max-width: 95%; font-weight: 400; }
        
        .hero-actions { display: flex; gap: 16px; flex-wrap: wrap; }
        
        .hero-stats { 
            display: flex; gap: 48px; margin-top: 56px; border-top: 1px solid var(--border); padding-top: 40px; 
        }
        .stat-item h4 { font-size: 36px; font-weight: 900; color: var(--text-main); letter-spacing: -1px; margin-bottom: 4px; }
        .stat-item p { font-size: 13px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; }

        /* Floating Dashboard Mockup */
        .hero-visual-wrapper { position: relative; perspective: 1200px; width: 100%; height: 100%; display: flex; justify-content: center; align-items: center;}
        .mac-mockup { 
            width: 110%; /* Slight overflow for dynamic feel */
            background: var(--bg-white); border-radius: 24px; 
            box-shadow: var(--shadow-xl), 0 50px 100px -20px rgba(26,127,212,0.2); 
            border: 1px solid var(--border); overflow: hidden; 
            transform: rotateY(-12deg) rotateX(8deg) translateZ(0); 
            animation: float 8s ease-in-out infinite; transform-style: preserve-3d; 
        }
        .mac-header { background: #f8fafc; padding: 16px 24px; display: flex; gap: 8px; border-bottom: 1px solid var(--border); align-items: center; }
        .mac-dot { width: 12px; height: 12px; border-radius: 50%; }
        .mac-dot.r { background: #ef4444; } .mac-dot.y { background: #f59e0b; } .mac-dot.g { background: #22c55e; }
        .mac-search { margin-left: auto; background: #e2e8f0; width: 50%; height: 24px; border-radius: 12px; }
        
        .mac-body { padding: 32px; background: #fff; }
        .mockup-kpi-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 32px; }
        .m-card { background: var(--bg-main); padding: 24px; border-radius: 16px; border: 1px solid var(--border); }
        .m-card span { font-size: 13px; font-weight: 700; color: var(--text-muted); display: block; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px; }
        .m-card strong { font-size: 32px; font-weight: 800; color: var(--text-main); letter-spacing: -1px; }
        
        /* --- 5. LOGO TICKER MARQUEE --- */
        .marquee-section { padding: 48px 0; border-top: 1px solid var(--border); border-bottom: 1px solid var(--border); background: var(--bg-white); overflow: hidden; display: flex; flex-direction: column; align-items: center; }
        .marquee-title { font-size: 14px; font-weight: 700; color: var(--text-light); text-transform: uppercase; letter-spacing: 3px; margin-bottom: 32px; text-align: center; }
        .marquee-container { width: 100%; overflow: hidden; display: flex; white-space: nowrap; position: relative; }
        .marquee-container::before, .marquee-container::after { content: ''; position: absolute; top: 0; width: 250px; height: 100%; z-index: 2; pointer-events: none; }
        .marquee-container::before { left: 0; background: linear-gradient(to right, white, transparent); }
        .marquee-container::after { right: 0; background: linear-gradient(to left, white, transparent); }
        .marquee-track { display: flex; animation: marquee 35s linear infinite; gap: 100px; padding: 0 50px; align-items: center; }
        
        /* Generic Company Logos for professional look */
        .comp-logo { display: flex; align-items: center; gap: 12px; opacity: 0.4; filter: grayscale(100%); transition: all 0.3s ease; cursor: default; }
        .comp-logo:hover { opacity: 1; filter: grayscale(0%); transform: scale(1.05); }
        .comp-logo svg { width: 32px; height: 32px; color: var(--text-main); }
        .comp-logo span { font-size: 24px; font-weight: 900; color: var(--text-main); letter-spacing: -1px; }

        /* --- 6. BENTO GRID ARCHITECTURE (PLATFORM) --- */
        .bento-section { padding: 160px 0; background: var(--bg-main); }
        .section-header { text-align: center; max-width: 700px; margin: 0 auto 80px; }
        .section-header h2 { font-size: 48px; font-weight: 900; color: var(--text-main); margin-bottom: 24px; letter-spacing: -1.5px; line-height: 1.1; }
        .section-header p { font-size: 20px; color: var(--text-muted); }

        .bento-grid { display: grid; grid-template-columns: repeat(12, 1fr); grid-auto-rows: minmax(320px, auto); gap: 32px; }
        
        .bento-card { 
            background: var(--bg-white); border-radius: 32px; border: 1px solid var(--border); 
            overflow: hidden; position: relative; transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1); 
            display: flex; flex-direction: column; 
        }
        .bento-card:hover { transform: translateY(-10px); box-shadow: var(--shadow-xl); border-color: var(--blue-light); }
        
        .bento-large { grid-column: span 8; }
        .bento-small { grid-column: span 4; }
        
        .bento-content { padding: 48px; flex-grow: 1; display: flex; flex-direction: column; justify-content: center; z-index: 2; }
        .bento-icon-wrapper { 
            width: 64px; height: 64px; background: var(--blue-light); border-radius: 20px; 
            display: flex; align-items: center; justify-content: center; font-size: 32px; 
            margin-bottom: 32px; box-shadow: inset 0 0 0 1px rgba(26,127,212,0.1); 
        }
        .bento-card h3 { font-size: 28px; font-weight: 800; margin-bottom: 16px; color: var(--text-main); letter-spacing: -0.5px; }
        .bento-card p { font-size: 16px; color: var(--text-muted); line-height: 1.7; }
        
        .bento-image { width: 100%; height: 280px; object-fit: cover; border-top: 1px solid var(--border); margin-top: auto; }
        .bento-large .bento-image { height: 360px; }

        /* --- 7. TIMELINE (DATA PIPELINE) --- */
        .timeline-section { padding: 160px 0; background: var(--bg-white); }
        .timeline-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 100px; align-items: center; }
        
        .timeline-img-wrapper { position: relative; border-radius: 40px; overflow: hidden; box-shadow: var(--shadow-xl); }
        .timeline-img-wrapper::after { content:''; position: absolute; inset:0; background: linear-gradient(to top, rgba(0,0,0,0.4), transparent); }
        
        .step-list { display: flex; flex-direction: column; gap: 48px; position: relative; }
        .step-list::before { content: ''; position: absolute; left: 32px; top: 10px; bottom: 10px; width: 2px; background: var(--border); z-index: 1; }
        
        .step-item { display: flex; gap: 40px; position: relative; z-index: 2; }
        .step-num { 
            width: 64px; height: 64px; background: var(--bg-white); border: 2px solid var(--blue-primary); 
            color: var(--blue-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; 
            font-size: 24px; font-weight: 900; flex-shrink: 0; box-shadow: 0 0 0 10px var(--bg-white); 
        }
        .step-content { padding-top: 12px; }
        .step-content h4 { font-size: 24px; font-weight: 800; margin-bottom: 16px; letter-spacing: -0.5px; }
        .step-content p { color: var(--text-muted); font-size: 16px; line-height: 1.7; }

        /* --- 8. LIVE ROI ENGINE (INTERACTIVE) --- */
        .roi-section { padding: 160px 0; background: var(--bg-dark); color: #fff; position: relative; overflow: hidden; }
        .roi-section::before { content: ''; position: absolute; top: -50%; left: -50%; width: 200%; height: 200%; background: radial-gradient(circle at center, rgba(26,127,212,0.15) 0%, transparent 60%); pointer-events: none; }
        
        .roi-grid { display: grid; grid-template-columns: 1fr 1.2fr; gap: 100px; align-items: center; position: relative; z-index: 2; }
        
        .roi-text h2 { font-size: 56px; font-weight: 900; line-height: 1.1; margin-bottom: 32px; letter-spacing: -2px; }
        .roi-text p { font-size: 20px; color: rgba(255,255,255,0.7); margin-bottom: 40px; line-height: 1.7; }
        
        .roi-features { display: flex; flex-direction: column; gap: 20px; margin-bottom: 48px; }
        .r-feat { display: flex; align-items: center; gap: 16px; font-size: 18px; font-weight: 500; color: rgba(255,255,255,0.9); }
        .r-check { display: flex; align-items: center; justify-content: center; width: 28px; height: 28px; background: rgba(34,197,94,0.2); color: var(--success); border-radius: 50%; font-size: 16px; }

        .roi-terminal { 
            background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.1); 
            border-radius: 32px; padding: 56px; backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
            box-shadow: 0 40px 80px rgba(0,0,0,0.5); 
        }
        
        .terminal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 48px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 24px; }
        .terminal-header h3 { font-size: 24px; font-weight: 800; letter-spacing: -0.5px; }
        .terminal-status { font-family: var(--font-mono); color: var(--success); display: flex; align-items: center; gap: 8px; font-size: 14px; }
        
        .roi-group { margin-bottom: 40px; }
        .roi-group label { display: flex; justify-content: space-between; font-size: 15px; font-weight: 700; color: rgba(255,255,255,0.6); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 20px; }
        .val-display { color: #fff; font-size: 18px; font-family: var(--font-mono); font-weight: 700; background: rgba(255,255,255,0.1); padding: 4px 12px; border-radius: 8px; }
        
        /* Custom Range Slider */
        input[type=range] { -webkit-appearance: none; width: 100%; background: transparent; }
        input[type=range]:focus { outline: none; }
        input[type=range]::-webkit-slider-thumb { -webkit-appearance: none; height: 28px; width: 28px; border-radius: 50%; background: var(--blue-primary); cursor: pointer; margin-top: -12px; box-shadow: 0 0 20px var(--blue-glow); border: 4px solid #fff; transition: transform 0.1s; }
        input[type=range]::-webkit-slider-thumb:hover { transform: scale(1.1); }
        input[type=range]::-webkit-slider-runnable-track { width: 100%; height: 6px; cursor: pointer; background: rgba(255,255,255,0.15); border-radius: 3px; }
        
        .roi-result-box { margin-top: 56px; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 40px; }
        .roi-result-box span { font-size: 15px; font-weight: 700; color: rgba(255,255,255,0.5); text-transform: uppercase; letter-spacing: 1px; display: block; margin-bottom: 16px; }
        .roi-total { font-size: 72px; font-weight: 900; color: var(--success); letter-spacing: -3px; display: flex; align-items: center; gap: 12px; font-family: var(--font-mono); }

        /* --- 9. TESTIMONIALS --- */
        .testimonials-section { padding: 160px 0; background: var(--bg-main); }
        .test-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 40px; }
        
        .test-card { 
            background: var(--bg-white); border: 1px solid var(--border); border-radius: 24px; 
            padding: 48px; display: flex; flex-direction: column; justify-content: space-between; 
            transition: all 0.4s ease; position: relative;
        }
        .test-card:hover { transform: translateY(-8px); box-shadow: var(--shadow-xl); border-color: var(--blue-light); }
        .test-card::before { content: '"'; position: absolute; top: 20px; right: 30px; font-size: 120px; color: var(--bg-main); font-family: serif; line-height: 1; z-index: 0; pointer-events: none; }
        
        .stars { color: var(--warning); font-size: 22px; letter-spacing: 3px; margin-bottom: 32px; position: relative; z-index: 1; }
        .test-quote { font-size: 17px; color: var(--text-main); line-height: 1.8; margin-bottom: 40px; font-style: italic; position: relative; z-index: 1; }
        
        .test-profile { display: flex; align-items: center; gap: 20px; border-top: 1px solid var(--border); padding-top: 32px; position: relative; z-index: 1; }
        .test-profile img { width: 64px; height: 64px; border-radius: 50%; object-fit: cover; border: 2px solid var(--blue-primary); padding: 2px; }
        .test-info h4 { font-size: 18px; font-weight: 800; color: var(--text-main); margin-bottom: 4px; }
        .test-info p { font-size: 14px; font-weight: 600; color: var(--blue-primary); }

        /* --- 10. PRICING SECTION (NEW) --- */
        .pricing-section { padding: 160px 0; background: var(--bg-white); }
        .pricing-toggle { display: flex; align-items: center; justify-content: center; gap: 16px; margin: 0 auto 64px; background: var(--bg-main); padding: 8px; border-radius: 100px; width: max-content; border: 1px solid var(--border); }
        .toggle-btn { padding: 12px 24px; border-radius: 100px; font-weight: 700; font-size: 15px; cursor: pointer; transition: all 0.3s; border: none; background: transparent; color: var(--text-muted); }
        .toggle-btn.active { background: var(--bg-white); color: var(--text-main); box-shadow: var(--shadow-sm); }
        .save-badge { background: rgba(34,197,94,0.1); color: var(--success); padding: 4px 10px; border-radius: 10px; font-size: 12px; font-weight: 800; margin-left: 8px; }

        .pricing-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 32px; align-items: center; max-width: 1100px; margin: 0 auto; }
        .price-card { background: var(--bg-main); border: 1px solid var(--border); border-radius: 32px; padding: 48px; transition: all 0.4s; position: relative; }
        .price-card:hover { border-color: var(--blue-primary); box-shadow: var(--shadow-lg); }
        .price-card.popular { background: var(--blue-dark); color: #fff; border: none; transform: scale(1.05); box-shadow: var(--shadow-xl), 0 20px 40px -10px var(--blue-glow); }
        .price-card.popular:hover { transform: scale(1.05) translateY(-5px); }
        
        .popular-badge { position: absolute; top: 0; left: 50%; transform: translate(-50%, -50%); background: linear-gradient(90deg, var(--warning), #f97316); color: #fff; padding: 8px 24px; border-radius: 100px; font-size: 13px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; box-shadow: var(--shadow-md); white-space: nowrap;}
        
        .plan-name { font-size: 24px; font-weight: 800; margin-bottom: 16px; }
        .plan-price { font-size: 56px; font-weight: 900; margin-bottom: 8px; letter-spacing: -2px; display: flex; align-items: baseline; }
        .plan-price span { font-size: 16px; font-weight: 600; color: var(--text-muted); letter-spacing: 0; margin-left: 8px; }
        .price-card.popular .plan-price span { color: rgba(255,255,255,0.7); }
        .plan-desc { font-size: 15px; color: var(--text-muted); margin-bottom: 32px; padding-bottom: 32px; border-bottom: 1px solid var(--border); }
        .price-card.popular .plan-desc { color: rgba(255,255,255,0.8); border-color: rgba(255,255,255,0.1); }
        
        .plan-features { display: flex; flex-direction: column; gap: 16px; margin-bottom: 48px; }
        .plan-features li { display: flex; align-items: center; gap: 12px; font-size: 15px; font-weight: 500; }
        .plan-features svg { width: 20px; height: 20px; color: var(--blue-primary); flex-shrink: 0; }
        .price-card.popular .plan-features svg { color: var(--blue-light); }
        
        /* --- 11. INTERACTIVE FAQ --- */
        .faq-section { padding: 140px 0; background: var(--bg-main); }
        .faq-container { max-width: 900px; margin: 0 auto; }
        .faq-item { border-bottom: 1px solid var(--border); transition: all 0.3s; }
        .faq-question { width: 100%; text-align: left; background: none; border: none; padding: 32px 0; font-size: 20px; font-weight: 800; color: var(--text-main); cursor: pointer; display: flex; justify-content: space-between; align-items: center; transition: color 0.3s; }
        .faq-question:hover { color: var(--blue-primary); }
        .faq-icon { font-size: 28px; color: var(--blue-primary); transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1); font-weight: 300; }
        .faq-answer { max-height: 0; overflow: hidden; transition: max-height 0.5s cubic-bezier(0.16, 1, 0.3, 1); }
        .faq-answer-inner { padding-bottom: 32px; font-size: 16px; color: var(--text-muted); line-height: 1.8; }
        
        .faq-item.active .faq-question { color: var(--blue-primary); }
        .faq-item.active .faq-icon { transform: rotate(45deg); }
        .faq-item.active .faq-answer { max-height: 500px; }

        /* --- 12. PRE-FOOTER CTA --- */
        .cta-section { padding: 120px 0; background: var(--bg-white); }
        .cta-box { 
            background: linear-gradient(135deg, var(--blue-dark), var(--blue-primary), var(--purple)); 
            border-radius: 40px; padding: 100px 40px; text-align: center; color: #fff; 
            position: relative; overflow: hidden; box-shadow: 0 40px 80px -20px rgba(26,127,212,0.5); 
        }
        .cta-box::before { content: ''; position: absolute; inset: 0; background: url('data:image/svg+xml;utf8,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><circle cx="50" cy="50" r="40" stroke="rgba(255,255,255,0.05)" stroke-width="2" fill="none"/></svg>') repeat; opacity: 0.6; pointer-events: none; }
        .cta-box h2 { font-size: 56px; font-weight: 900; margin-bottom: 24px; position: relative; z-index: 2; letter-spacing: -1.5px; }
        .cta-box p { font-size: 22px; color: rgba(255,255,255,0.9); margin-bottom: 48px; max-width: 700px; margin-inline: auto; position: relative; z-index: 2; line-height: 1.6; }

        /* --- 13. ULTIMATE MEGA FOOTER --- */
        .mega-footer { background: var(--bg-darker); color: #fff; padding: 120px 0 0; position: relative; border-top: 4px solid var(--blue-primary); overflow: hidden; }
        .mega-footer::before { content: ''; position: absolute; top: 0; right: 0; width: 600px; height: 600px; background: radial-gradient(circle, rgba(26,127,212,0.05) 0%, transparent 70%); border-radius: 50%; pointer-events: none; }
        
        .footer-grid { display: grid; grid-template-columns: 2.5fr 1fr 1fr 1.5fr; gap: 80px; margin-bottom: 80px; position: relative; z-index: 2; }
        
        .f-brand h3 { font-size: 32px; font-weight: 900; color: #fff; margin-bottom: 24px; display: flex; align-items: center; gap: 12px; letter-spacing: -0.5px; }
        .f-brand p { color: rgba(255,255,255,0.6); font-size: 16px; line-height: 1.8; max-width: 400px; margin-bottom: 40px; }
        
        .f-socials { display: flex; gap: 16px; }
        .f-socials a { width: 48px; height: 48px; background: rgba(255,255,255,0.05); border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: all 0.3s; color: #fff; border: 1px solid rgba(255,255,255,0.1); }
        .f-socials a:hover { background: var(--blue-primary); border-color: var(--blue-primary); transform: translateY(-5px); box-shadow: 0 10px 20px rgba(26,127,212,0.3); }
        
        .f-links h4 { font-size: 16px; font-weight: 800; text-transform: uppercase; letter-spacing: 2px; color: #fff; margin-bottom: 32px; }
        .f-links ul li { margin-bottom: 16px; }
        .f-links a { color: rgba(255,255,255,0.6); font-size: 15px; font-weight: 500; transition: all 0.2s; display: inline-flex; align-items: center; gap: 8px; }
        .f-links a:hover { color: var(--blue-light); transform: translateX(5px); }
        
        .f-newsletter { background: rgba(255,255,255,0.03); padding: 40px; border-radius: 24px; border: 1px solid rgba(255,255,255,0.08); backdrop-filter: blur(10px); }
        .f-newsletter h4 { font-size: 20px; font-weight: 800; margin-bottom: 16px; color: #fff; }
        .f-newsletter p { font-size: 15px; color: rgba(255,255,255,0.6); margin-bottom: 32px; line-height: 1.6; }
        .nl-form { display: flex; flex-direction: column; gap: 16px; }
        .nl-input { width: 100%; background: rgba(0,0,0,0.5); border: 1px solid rgba(255,255,255,0.15); padding: 16px 20px; border-radius: 12px; color: #fff; font-size: 15px; outline: none; transition: border 0.3s, box-shadow 0.3s; }
        .nl-input:focus { border-color: var(--blue-primary); box-shadow: 0 0 0 4px rgba(26,127,212,0.1); }
        .nl-btn { background: var(--blue-primary); color: #fff; border: none; padding: 16px; border-radius: 12px; font-weight: 800; font-size: 16px; cursor: pointer; transition: background 0.3s, transform 0.3s; display: flex; justify-content: center; align-items: center; gap: 8px; }
        .nl-btn:hover { background: var(--blue-light); color: var(--blue-dark); transform: translateY(-2px); }
        
        .f-bottom { border-top: 1px solid rgba(255,255,255,0.1); padding: 40px 0; display: flex; justify-content: space-between; align-items: center; position: relative; z-index: 2; }
        .copyright-text { font-size: 15px; color: rgba(255,255,255,0.5); }
        .copyright-text span { color: #fff; font-weight: 700; }
        
        .f-badges { display: flex; gap: 12px; flex-wrap: wrap; }
        .f-badge { background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); padding: 8px 16px; border-radius: 10px; font-size: 13px; font-weight: 700; font-family: var(--font-mono); letter-spacing: 0.5px; }

        /* --- 14. RESPONSIVE DESIGN MEDIA QUERIES --- */
        @media (max-width: 1400px) {
            .container { padding: 0 40px; }
        }
        @media (max-width: 1200px) {
            .hero h1 { font-size: 60px; }
            .hero-grid { gap: 40px; }
            .bento-large, .bento-small { grid-column: span 6; }
            .test-grid { grid-template-columns: repeat(2, 1fr); }
            .pricing-grid { grid-template-columns: repeat(2, 1fr); max-width: 800px; }
            .price-card.popular { transform: none; grid-column: span 2; display: flex; justify-content: space-between; align-items: center; text-align: left; }
            .price-card.popular .plan-features { margin-bottom: 0; }
            .footer-grid { grid-template-columns: 1fr 1fr; gap: 64px; }
        }
        @media (max-width: 992px) {
            .hero { padding: 160px 0 100px; text-align: center; }
            .hero-grid { grid-template-columns: 1fr; gap: 64px; }
            .hero-actions, .hero-stats { justify-content: center; }
            .nav-links { display: none; }
            .timeline-grid, .roi-grid { grid-template-columns: 1fr; gap: 64px; }
            .roi-text { text-align: center; }
            .roi-features { align-items: center; }
            .mac-mockup { width: 100%; transform: none; animation: float 6s ease-in-out infinite; }
            .pricing-grid { grid-template-columns: 1fr; }
            .price-card.popular { grid-column: span 1; flex-direction: column; text-align: center; align-items: stretch; }
            .price-card.popular .plan-features { margin-bottom: 48px; align-items: center; }
        }
        @media (max-width: 768px) {
            .container { padding: 0 24px; }
            .hero h1 { font-size: 48px; letter-spacing: -1px; }
            .bento-large, .bento-small { grid-column: span 12; }
            .bento-large .bento-image { height: 240px; }
            .test-grid, .footer-grid { grid-template-columns: 1fr; gap: 48px; }
            .f-bottom { flex-direction: column; gap: 24px; text-align: center; }
            .f-badges { justify-content: center; }
            .roi-total { font-size: 48px; }
            .cta-box h2 { font-size: 40px; }
        }
    </style>
</head>
<body>

    <div class="scroll-progress" id="progressBar"></div>

    <nav class="navbar" id="navbar">
        <div class="container nav-wrapper">
            <a href="index.php" class="logo">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
                BizInsight
            </a>
            <div class="nav-links">
                <a href="#platform">Architecture</a>
                <a href="#workflow">Pipeline</a>
                <a href="#roi">ROI Engine</a>
                <a href="#pricing">Pricing</a>
                <a href="#testimonials">Clients</a>
            </div>
            <div class="nav-auth">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <span class="nav-greeting-text" style="font-size:14px;font-weight:600;color:var(--text-muted);"><?= htmlspecialchars($greeting) ?>!</span>
                    <a href="dashboard.php" class="btn btn-primary">Open System</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-outline">Client Login</a>
                    <a href="register.php" class="btn btn-primary">Start Free Trial</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <section class="hero">
        <div class="hero-bg-elements">
            <div class="orb-1"></div>
            <div class="orb-2"></div>
        </div>
        <div class="container hero-grid">
            <div class="hero-text reveal reveal-left">
                <div class="hero-badge">
                    <div class="live-dot"></div> BizInsight Analytics
                </div>
                <h1>
                    Turn Raw Data Into<br>
                    <span class="typing-container"><span class="typing-text">Business Intelligence</span></span>
                </h1>
                <p>Stop managing your business on messy spreadsheets. Upload your sales data and let our dynamic RDBMS-backed system generate real-time charts, KPIs, and AI-driven growth insights instantly.</p>
                
                <div class="hero-actions">
                    <a href="register.php" class="btn btn-primary" style="padding: 18px 40px; font-size: 18px;">Deploy Dashboard Free</a>
                    <a href="#platform" class="btn btn-outline" style="padding: 18px 40px; font-size: 18px;">View Architecture</a>
                </div>
                
                <div class="hero-stats">
                    <div class="stat-item">
                        <h4 id="ticker-1">500+</h4>
                        <p>Active Workspaces</p>
                    </div>
                    <div class="stat-item">
                        <h4 id="ticker-2">₹10Cr+</h4>
                        <p>Revenue Analyzed</p>
                    </div>
                    <div class="stat-item">
                        <h4>99.9%</h4>
                        <p>Uptime SLA</p>
                    </div>
                </div>
            </div>
            
            <div class="hero-visual-wrapper reveal reveal-right reveal-delay-2">
                <div class="mac-mockup">
                    <div class="mac-header">
                        <div class="mac-dot r"></div><div class="mac-dot y"></div><div class="mac-dot g"></div>
                        <div class="mac-search"></div>
                    </div>
                    <div class="mac-body">
                        <div class="mockup-kpi-grid">
                            <div class="m-card"><span>Gross Revenue YTD</span><strong>₹8,42,500</strong></div>
                            <div class="m-card"><span>Net Profit Margin</span><strong style="color: var(--success);">25.0% 📈</strong></div>
                        </div>
                        <div style="height: 240px; width: 100%; position: relative;">
                            <canvas id="heroChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="marquee-section">
        <div class="marquee-title">Empowering Data-Driven Indian Businesses & Enterprises</div>
        <div class="marquee-container">
            <div class="marquee-track">
                <div class="comp-logo"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg><span>Apex Retail</span></div>
                <div class="comp-logo"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="2" y1="12" x2="22" y2="12"></line><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path></svg><span>Nexa Global</span></div>
                <div class="comp-logo"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 2 7 12 12 22 7 12 2"></polygon><polyline points="2 17 12 22 22 17"></polyline><polyline points="2 12 12 17 22 12"></polyline></svg><span>Elevate E-Com</span></div>
                <div class="comp-logo"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path></svg><span>Nexus Systems</span></div>
                <div class="comp-logo"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg><span>Apex Retail</span></div>
                <div class="comp-logo"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="2" y1="12" x2="22" y2="12"></line><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path></svg><span>Nexa Global</span></div>
                <div class="comp-logo"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 2 7 12 12 22 7 12 2"></polygon><polyline points="2 17 12 22 22 17"></polyline><polyline points="2 12 12 17 22 12"></polyline></svg><span>Elevate E-Com</span></div>
                <div class="comp-logo"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path></svg><span>Nexus Systems</span></div>
            </div>
        </div>
    </div>

    <section class="bento-section" id="platform">
        <div class="container">
            <div class="section-header reveal">
                <h2>Enterprise Architecture,<br>Simplified for You.</h2>
                <p>A fully functional, relational database-driven business intelligence architecture built to scale infinitely with your daily operations.</p>
            </div>
            
            <div class="bento-grid">
                <div class="bento-card bento-large reveal">
                    <div class="bento-content">
                        <div class="bento-icon-wrapper"><?= $features[0]['icon'] ?></div>
                        <h3><?= $features[0]['title'] ?></h3>
                        <p><?= $features[0]['desc'] ?></p>
                    </div>
                    <img src="<?= $features[0]['img'] ?>" alt="Dashboard UI" class="bento-image">
                </div>
                
                <div class="bento-card bento-small reveal reveal-delay-1">
                    <div class="bento-content">
                        <div class="bento-icon-wrapper"><?= $features[1]['icon'] ?></div>
                        <h3><?= $features[1]['title'] ?></h3>
                        <p><?= $features[1]['desc'] ?></p>
                    </div>
                </div>

                <div class="bento-card bento-small reveal">
                    <div class="bento-content">
                        <div class="bento-icon-wrapper"><?= $features[2]['icon'] ?></div>
                        <h3><?= $features[2]['title'] ?></h3>
                        <p><?= $features[2]['desc'] ?></p>
                    </div>
                </div>

                <div class="bento-card bento-large reveal reveal-delay-1">
                    <div class="bento-content">
                        <div class="bento-icon-wrapper"><?= $features[3]['icon'] ?></div>
                        <h3><?= $features[3]['title'] ?></h3>
                        <p><?= $features[3]['desc'] ?></p>
                    </div>
                    <img src="<?= $features[3]['img'] ?>" alt="AI Analytics" class="bento-image">
                </div>
            </div>
        </div>
    </section>

    <section class="timeline-section" id="workflow">
        <div class="container timeline-grid">
            <div class="reveal reveal-left">
                <div class="timeline-img-wrapper">
                    <img src="https://images.unsplash.com/photo-1551434678-e076c223a692?auto=format&fit=crop&w=800&q=80" alt="Team working">
                </div>
            </div>
            <div class="reveal reveal-right">
                <h2 style="font-size: 48px; font-weight: 900; margin-bottom: 48px; color: var(--text-main); letter-spacing: -1px; line-height: 1.1;">From Raw CSV to Boardroom Ready in Seconds.</h2>
                <div class="step-list">
                    <div class="step-item">
                        <div class="step-num">1</div>
                        <div class="step-content">
                            <h4>Upload Securely</h4>
                            <p>Export your messy sales data from your POS, tally, or legacy system and upload the raw CSV file securely into our isolated backend.</p>
                        </div>
                    </div>
                    <div class="step-item">
                        <div class="step-num">2</div>
                        <div class="step-content">
                            <h4>Automated Structuring</h4>
                            <p>Our backend engine parses the data, sanitizes it, and inserts it into optimized relational tables for instant querying.</p>
                        </div>
                    </div>
                    <div class="step-item">
                        <div class="step-num">3</div>
                        <div class="step-content">
                            <h4>Visualize & Export</h4>
                            <p>Instantly view your interactive Chart.js dashboards. Apply date filters, monitor KPIs, and export 1-click PDF reports.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="roi-section" id="roi">
        <div class="container roi-grid">
            <div class="roi-text reveal reveal-left">
                <h2>The True Cost of Manual Excel Management.</h2>
                <p>Businesses using BizInsight typically recover 15 hours a week previously wasted on manual entry, and increase their net profit margins by automatically identifying underperforming assets.</p>
                
                <div class="roi-features">
                    <div class="r-feat"><div class="r-check">✓</div> Eliminate manual data entry human errors</div>
                    <div class="r-feat"><div class="r-check">✓</div> Real-time indexing for instant queries</div>
                    <div class="r-feat"><div class="r-check">✓</div> Generate executive PDF reports automatically</div>
                </div>
            </div>
            
            <div class="roi-terminal reveal reveal-right reveal-delay-2">
                <div class="terminal-header">
                    <h3>ROI Simulator</h3>
                    <span class="terminal-status"><span class="live-dot"></span> PHP Engine Live</span>
                </div>

                <div class="roi-group">
                    <label>
                        <span>Average Monthly Revenue</span>
                        <span class="val-display" id="revValDisplay">₹ 5,00,000</span>
                    </label>
                    <input type="range" id="monthlyRevenue" min="50000" max="10000000" step="50000" value="500000" oninput="updateROI()">
                </div>
                
                <div class="roi-group">
                    <label>
                        <span>Manual Excel Hours / Week</span>
                        <span class="val-display" id="hoursValDisplay">15 hrs</span>
                    </label>
                    <input type="range" id="excelHours" min="0" max="60" step="1" value="15" oninput="updateROI()">
                </div>

                <div class="roi-result-box">
                    <span>Projected Annual Value Created</span>
                    <div class="roi-total">
                        <span id="profitIncrease">₹ 8,40,000</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="pricing-section" id="pricing">
        <div class="container">
            <div class="section-header reveal">
                <h2>Simple, Transparent Pricing</h2>
                <p>Start for free, scale when you are ready. No hidden fees.</p>
            </div>
            
            <div class="pricing-toggle reveal reveal-delay-1">
                <button class="toggle-btn active" id="btnMonthly">Monthly</button>
                <button class="toggle-btn" id="btnYearly">Annually <span class="save-badge">Save 20%</span></button>
            </div>
            
            <div class="pricing-grid">
                <div class="price-card reveal">
                    <div class="plan-name">Starter</div>
                    <div class="plan-price">Free<span>/forever</span></div>
                    <div class="plan-desc">Perfect for testing the dashboard.</div>
                    <ul class="plan-features">
                        <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg> Basic Analytics Dashboard</li>
                        <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg> Up to 500 sales records</li>
                        <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg> 3 PDF Reports / month</li>
                        <li style="color:var(--text-light);"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:var(--border-dark);"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg> No AI Insights</li>
                    </ul>
                    <a href="register.php" class="btn btn-outline" style="width:100%;">Create Free Account</a>
                </div>
                
                <div class="price-card popular reveal reveal-delay-1">
                    <div class="popular-badge">Most Popular</div>
                    <div class="plan-name">Business</div>
                    <div class="plan-price"><span style="font-size:32px; margin-right:4px;">₹</span><span id="priceBusiness" style="font-size:56px; color:#fff; font-weight:900; margin:0;">999</span><span id="periodBusiness">/mo</span></div>
                    <div class="plan-desc">For growing businesses and operations.</div>
                    <ul class="plan-features">
                        <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg> Advanced Real-time Dashboard</li>
                        <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg> Unlimited Data Records</li>
                        <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg> Unlimited PDF & Excel Exports</li>
                        <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg> Full AI Predictive Insights</li>
                    </ul>
                    <a href="register.php" class="btn btn-primary" style="width:100%; background:#fff; color:var(--blue-dark);">Start 14-Day Trial</a>
                </div>
                
                <div class="price-card reveal reveal-delay-2">
                    <div class="plan-name">Enterprise</div>
                    <div class="plan-price">Custom</div>
                    <div class="plan-desc">For large chains requiring custom setups.</div>
                    <ul class="plan-features">
                        <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg> Everything in Business</li>
                        <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg> Multi-location Database Setup</li>
                        <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg> Custom API Integrations</li>
                        <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg> Dedicated Account Manager</li>
                    </ul>
                    <a href="#" class="btn btn-outline" style="width:100%;">Contact Sales</a>
                </div>
            </div>
        </div>
    </section>

    <section class="testimonials-section" id="testimonials">
        <div class="container">
            <div class="section-header reveal">
                <h2>Scaling Real Operations</h2>
                <p>See how real businesses are leveraging our analytics core.</p>
            </div>
            
            <div class="test-grid">
                <?php foreach($testimonials as $index => $test): ?>
                <div class="test-card reveal" style="transition-delay: <?= $index * 0.15 ?>s;">
                    <div>
                        <div class="stars">★★★★★</div>
                        <p class="test-quote">"<?= $test['text'] ?>"</p>
                    </div>
                    <div class="test-profile">
                        <img src="<?= $test['img'] ?>" alt="<?= $test['name'] ?>">
                        <div class="test-info">
                            <h4><?= $test['name'] ?></h4>
                            <p><?= $test['role'] ?>, <?= $test['company'] ?></p>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="faq-section">
        <div class="container faq-container">
            <div class="section-header reveal" style="margin-bottom: 60px;">
                <h2>Platform FAQs</h2>
            </div>
            
            <div class="faq-list reveal">
                <?php foreach($faqs as $index => $faq): ?>
                <div class="faq-item <?= $index === 0 ? 'active' : '' ?>">
                    <button class="faq-question" onclick="toggleFAQ(this)">
                        <?= $faq['q'] ?>
                        <span class="faq-icon">+</span>
                    </button>
                    <div class="faq-answer">
                        <div class="faq-answer-inner"><?= $faq['a'] ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="cta-section">
        <div class="container reveal">
            <div class="cta-box">
                <h2>Ready to Deploy Your Dashboard?</h2>
                <p>Join hundreds of forward-thinking businesses and transition your raw data into an enterprise-grade reporting system today. Setup takes less than 2 minutes.</p>
                <div style="display: flex; gap: 20px; justify-content: center; flex-wrap: wrap;">
                    <a href="register.php" class="btn btn-dark" style="padding: 20px 48px; font-size: 18px; box-shadow: 0 10px 30px rgba(0,0,0,0.3);">Create Free Account</a>
                    <a href="login.php" class="btn btn-outline" style="background: rgba(255,255,255,0.1); color: #fff; border-color: rgba(255,255,255,0.3); padding: 20px 48px; font-size: 18px;">Try Demo Login</a>
                </div>
            </div>
        </div>
    </section>

    <footer class="mega-footer">
        <div class="container">
            <div class="footer-grid">
                <div class="f-brand reveal">
                    <h3>
                        <svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                        BizInsight
                    </h3>
                    <p>The ultimate dynamic business intelligence platform. We transform your raw CSV and POS data into actionable, visual decisions leveraging the power of PHP and MySQL.</p>
                    <div class="f-socials">
                        <a href="#" aria-label="Twitter"><svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/></svg></a>
                        <a href="https://github.com/Hetsoni28" aria-label="GitHub"><svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/></svg></a>
                        <a href="#" aria-label="LinkedIn"><svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg></a>
                    </div>
                </div>
                
                <div class="f-links reveal reveal-delay-1">
                    <h4>Platform</h4>
                    <ul>
                        <li><a href="#">Analytics Engine <span style="font-size:10px; background:var(--blue-primary); color:#fff; padding:2px 6px; border-radius:4px; margin-left:6px;">NEW</span></a></li>
                        <li><a href="#">Data Ingestion API</a></li>
                        <li><a href="#">Export Modules</a></li>
                        <li><a href="#">Security Architecture</a></li>
                    </ul>
                </div>
                
                <div class="f-links reveal reveal-delay-2">
                    <h4>Company</h4>
                    <ul>
                        <li><a href="#">About Us</a></li>
                        <li><a href="#">Careers</a></li>
                        <li><a href="#">System Status</a></li>
                        <li><a href="#">Contact Support</a></li>
                    </ul>
                </div>
                
                <div class="f-newsletter reveal reveal-delay-3">
                    <h4>Developer Updates</h4>
                    <p>Get notified about new framework updates, API releases, and RDBMS optimization tips.</p>
                    <form class="nl-form" onsubmit="event.preventDefault(); alert('Subscribed securely!');">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        <input type="email" class="nl-input" placeholder="developer@company.com" required>
                        <button type="submit" class="nl-btn">Subscribe Securely &rarr;</button>
                    </form>
                </div>
            </div>
            
            <div class="f-bottom reveal">
                <div class="copyright-text">
                    &copy; <?= date('Y') ?> BizInsight Analytics Dashboard. All rights reserved. <span>Designed &amp; Built by Mili Purohit.</span>
                </div>
                <div class="f-badges">
                    <span class="f-badge" style="color: #61dafb; border-color: rgba(97,218,251,0.3);">Core PHP</span>
                    <span class="f-badge" style="color: #8892BF; border-color: rgba(136,146,191,0.3);">MySQL</span>
                </div>
            </div>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            
            // 1. Scroll Progress Bar
            const progressBar = document.getElementById('progressBar');
            window.addEventListener('scroll', () => {
                const winScroll = document.body.scrollTop || document.documentElement.scrollTop;
                const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
                const scrolled = (winScroll / height) * 100;
                progressBar.style.width = scrolled + "%";
            });

            // 2. Chart.js Initialization (Dynamic PHP Data)
            const ctx = document.getElementById('heroChart');
            if (ctx) {
                const chartLabels = <?= $live_chart_labels ?>;
                const chartData = <?= $live_chart_data ?>;
                
                let gradient = ctx.getContext('2d').createLinearGradient(0, 0, 0, 300);
                gradient.addColorStop(0, 'rgba(26, 127, 212, 0.5)');
                gradient.addColorStop(1, 'rgba(26, 127, 212, 0.0)');
                
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: chartLabels,
                        datasets: [{
                            label: 'Net Revenue',
                            data: chartData,
                            borderColor: '#1a7fd4',
                            backgroundColor: gradient,
                            borderWidth: 4,
                            pointBackgroundColor: '#ffffff',
                            pointBorderColor: '#1a7fd4',
                            pointBorderWidth: 3,
                            pointRadius: 0, // Hidden until hover
                            pointHoverRadius: 8,
                            fill: true,
                            tension: 0.4 
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            x: { display: false },
                            y: { display: false, min: 0 }
                        },
                        interaction: { intersect: false, mode: 'index' },
                        animation: { duration: 2500, easing: 'easeOutExpo' }
                    }
                });
            }

            // 3. Navbar Sticky Effect
            const nav = document.getElementById('navbar');
            window.addEventListener('scroll', () => {
                if (window.scrollY > 50) {
                    nav.classList.add('scrolled');
                } else {
                    nav.classList.remove('scrolled');
                }
            });

            // 4. Advanced Scroll Reveals (Intersection Observer)
            const revealElements = document.querySelectorAll('.reveal, .reveal-left, .reveal-right');
            const revealObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('active');
                        revealObserver.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.1, rootMargin: "0px 0px -50px 0px" });

            revealElements.forEach(el => revealObserver.observe(el));
            
            // 5. Initialize Live ROI Calc on load
            if (typeof updateROI === 'function') updateROI();

            // 6. Pricing Toggle Logic
            const btnMonthly = document.getElementById('btnMonthly');
            const btnYearly = document.getElementById('btnYearly');
            const priceBusiness = document.getElementById('priceBusiness');
            const periodBusiness = document.getElementById('periodBusiness');

            btnMonthly.addEventListener('click', () => {
                btnMonthly.classList.add('active');
                btnYearly.classList.remove('active');
                priceBusiness.innerText = '999';
                periodBusiness.innerText = '/mo';
            });

            btnYearly.addEventListener('click', () => {
                btnYearly.classList.add('active');
                btnMonthly.classList.remove('active');
                priceBusiness.innerText = '9,590';
                periodBusiness.innerText = '/yr';
            });
        });

        // 7. Live ROI Terminal Logic
        function updateROI() {
            const revInput = document.getElementById('monthlyRevenue').value;
            const hoursInput = document.getElementById('excelHours').value;
            
            // Update UI Labels
            const formatRev = new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR', maximumFractionDigits: 0 }).format(revInput);
            document.getElementById('revValDisplay').innerText = formatRev;
            document.getElementById('hoursValDisplay').innerText = hoursInput + " hrs";
            
            // BizInsight Logic: Assume 8.5% optimization on revenue, plus value of time saved (₹500/hr)
            const revenue = parseFloat(revInput) || 0;
            const hours = parseFloat(hoursInput) || 0;
            
            const annualRevenueOptimization = revenue * 12 * 0.085;
            const annualTimeSavedValue = hours * 52 * 500;
            const totalValue = annualRevenueOptimization + annualTimeSavedValue;
            
            // Format Final Result
            const formattedTotal = new Intl.NumberFormat('en-IN', {
                style: 'currency',
                currency: 'INR',
                maximumFractionDigits: 0
            }).format(totalValue);
            
            document.getElementById('profitIncrease').innerText = formattedTotal;
        }

        // 8. FAQ Accordion Logic
        function toggleFAQ(button) {
            const item = button.parentElement;
            const isActive = item.classList.contains('active');
            
            // Close all others
            document.querySelectorAll('.faq-item').forEach(el => {
                el.classList.remove('active');
            });
            
            // Open clicked if it wasn't already active
            if (!isActive) {
                item.classList.add('active');
            }
        }
    </script>
</body>
</html>