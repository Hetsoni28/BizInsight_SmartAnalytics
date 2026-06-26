# 📊 BizInsight — Smart Business Analytics Dashboard

A full-stack PHP + MySQL web application for tracking business KPIs, uploading sales data, and generating reports.

---

## 🚀 Tech Stack

| Layer      | Technology                        |
|------------|-----------------------------------|
| Frontend   | HTML5, CSS3, JavaScript (ES6+)    |
| Charts     | Chart.js (CDN)                    |
| Backend    | PHP 8.x                           |
| Database   | MySQL 8.x / MariaDB               |
| Server     | Apache (XAMPP / WAMP / LAMP)      |

---

## 📁 Project Structure

```
bizinsight/
├── index.php              ← Landing / home page
├── login.php              ← Login page
├── register.php           ← Registration page
├── logout.php             ← Session destroy & redirect
├── dashboard.php          ← Main analytics dashboard
├── analytics.php          ← Deep analytics & AI insights
├── sales.php              ← Sales CRUD + search + pagination
├── reports.php            ← Report generator + CSV export
├── upload.php             ← CSV file upload & import
├── settings.php           ← Profile, business, security tabs
│
├── assets/
│   ├── css/
│   │   ├── style.css      ← Global variables + components
│   │   ├── dashboard.css  ← Sidebar + topbar + app layout
│   │   ├── landing.css    ← Landing page styles
│   │   └── auth.css       ← Login / register styles
│   └── js/
│       ├── app.js         ← Toast, modals, shared utilities
│       ├── charts.js      ← All Chart.js configurations
│       └── landing.js     ← Landing page interactions
│
├── includes/
│   ├── db.php             ← Database connection (configure here)
│   ├── auth_check.php     ← Session guard for protected pages
│   ├── functions.php      ← Helper functions (revenue, profit...)
│   ├── header.php         ← App page HTML head + topbar open
│   ├── footer.php         ← Toast div + JS includes + close tags
│   └── sidebar.php        ← Navigation sidebar
│
├── database/
│   └── setup.sql          ← Run once to create DB, tables & demo data
│
└── uploads/
    ├── .htaccess          ← Blocks PHP execution in uploads folder
    ├── sample_data.csv    ← Sample CSV to test file upload
    └── files/             ← Uploaded CSVs stored here (auto-created)
```

---

## ⚙️ Setup Instructions

### Step 1 — Install XAMPP
Download from https://www.apachefriends.org  
Start **Apache** and **MySQL** from the XAMPP Control Panel.

### Step 2 — Place project files
Copy the entire `bizinsight/` folder to:
```
C:\xampp\htdocs\bizinsight\
```

### Step 3 — Create the database
Open your browser and go to:  
`http://localhost/phpmyadmin`

1. Click **SQL** tab at the top
2. Open `database/setup.sql` in a text editor
3. Copy all the SQL content
4. Paste it into the SQL box in phpMyAdmin
5. Click **Go**

This will:
- Create the `bizinsight_db` database
- Create `users`, `sales`, and `uploads` tables
- Insert demo user and 20 sample sales records

### Step 4 — Configure database (if needed)
Open `includes/db.php` and update if your settings differ:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');          // your MySQL password
define('DB_NAME', 'bizinsight_db');
```

### Step 5 — Run the project
Open your browser and visit:  
`http://localhost/bizinsight/`

---

## 🔐 Demo Login Credentials

| Field    | Value                   |
|----------|-------------------------|
| Email    | demo@bizinsight.com     |
| Password | Demo@1234               |

---

## 📄 Pages & Features

| Page           | URL               | Feature                                          |
|----------------|-------------------|--------------------------------------------------|
| Landing        | `/`               | Hero, Features, Pricing, Preview charts          |
| Login          | `/login.php`      | Email + password authentication                  |
| Register       | `/register.php`   | Account creation with business type              |
| Dashboard      | `/dashboard.php`  | KPI cards, revenue chart, pie chart, table       |
| Analytics      | `/analytics.php`  | Revenue vs Profit bar, growth line, AI insights  |
| Sales          | `/sales.php`      | Full CRUD, search, filter, pagination            |
| Reports        | `/reports.php`    | 6 report types, CSV export, print-ready layout   |
| Upload         | `/upload.php`     | CSV drag-and-drop, import, upload history        |
| Settings       | `/settings.php`   | Profile, Business, Security, Notifications tabs  |

---

## 📊 CSV Upload Format

Your CSV file must have these **6 columns** (header row required):

```csv
product_name,category,sales_amount,profit,quantity,month_name
Laptop Pro X1,Electronics,50000,8000,5,January
Mobile Galaxy,Phones,30000,5000,8,February
```

A sample file is available at `/uploads/sample_data.csv`.

---

## 🎨 Design

- **Color Scheme:** White + Light Blue  
- **Primary:** `#1a7fd4`  
- **Background:** `#f0f7ff`  
- **Sidebar:** `#1a4a7a` (dark navy)
- **Fonts:** Segoe UI / System UI  
- **Charts:** Chart.js (Bar, Line, Doughnut, Pie)

---

## 🔒 Security Notes

- Passwords hashed with `password_hash()` (bcrypt)
- All queries use `mysqli` prepared statements (SQL injection safe)
- Session-based authentication with `auth_check.php`
- Upload folder protected by `.htaccess`
- All output escaped with `htmlspecialchars()`

---

## 📦 Requirements

- PHP 8.0 or higher
- MySQL 8.0 / MariaDB 10.4+
- Apache with `mod_rewrite` enabled
- XAMPP (recommended for local development)

---

*Built with ❤️ using PHP · MySQL · Chart.js · Pure CSS*
