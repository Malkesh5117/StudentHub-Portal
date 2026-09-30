# StudentHub Portal — Enterprise Campus Academic & Resource System

[![CHARUSAT](https://img.shields.io/badge/Institute-FTE%20CHARUSAT-blue.svg)](https://www.charusat.ac.in)
[![Course](https://img.shields.io/badge/Subject-ITUE203%20WDF-green.svg)](Practical_List_ITUE203_WDF_2026_27.pdf)
[![Semester](https://img.shields.io/badge/Semester-3rd%20(2026--27)-orange.svg)](README.md)
[![Status](https://img.shields.io/badge/Practicals-1%20to%2015%20Completed-brightgreen.svg)](README.md)

---

## 👨‍🎓 Student & Academic Details
- **Student Name:** Malkesh Patel
- **Roll Number / Student ID:** 25DCS089
- **Course & Code:** Web Development Frameworks (ITUE203)
- **Academic Program:** Bachelor of Technology in Computer Science & Engineering
- **Department:** Department of Computer Science & Engineering (FTE)
- **Institution:** Faculty of Technology and Engineering (FTE), CHARUSAT
- **GitHub Username:** [Malkesh5117](https://github.com/Malkesh5117)
- **Repository URL:** [https://github.com/Malkesh5117/StudentHub-Portal.git](https://github.com/Malkesh5117/StudentHub-Portal.git)

---

## 📖 Project Overview
**StudentHub** is an academic management web application engineered across 15 practical laboratory sessions for the **ITUE203 Web Development Frameworks** curriculum. It provides a centralized digital ecosystem for students, faculty, and administrators to interact with campus academic data.

### 🌟 Core Capabilities
1. **Academic Dashboard:** Real-time subject-wise attendance monitoring, weekly timetables, semester examination grade cards, and fee receipt tracking.
2. **Campus Event Hub:** Event browsing with real-time category filtering, search, pagination, and multi-part poster uploads.
3. **Multi-Tier Security:** Password hashing using BCRYPT, MySQLi prepared statements (zero SQL injection risk), CSRF token validation on mutating actions, and 30-minute idle session expiration.
4. **Role-Based Access Control (RBAC):** Distinct dashboards, privileges, and navigation structures for `student` and `admin` accounts.
5. **Asynchronous REST API:** Decoupled backend architecture providing JSON endpoints consumed via the JavaScript Fetch API without full page reloads.
6. **Responsive UI:** Fluid layouts built with CSS Grid and Flexbox, featuring dark/light theme switching with `localStorage` state persistence.

---

## 🛠️ Technology Stack
- **Frontend Architecture:** Semantic HTML5, Vanilla CSS3 (Custom Properties, Flexbox, Grid), JavaScript (ES6+, DOM Manipulation, Fetch API, LocalStorage).
- **Backend Architecture:** PHP 8.x (Session management, BCRYPT password hashing, input sanitization, RESTful JSON APIs, file uploads).
- **Database Engine:** MySQL 8.x / MariaDB with normalized schema, foreign keys, and indexes.
- **Web Server:** Apache 2.4 (Virtual Host configuration, `.htaccess` security headers).
- **Version Control:** Git & GitHub (Incremental laboratory practical commits).

---

## 📑 Syllabus Mapping (Practicals 1 to 15)

| Practical No. | Topic / Practical Title | Implemented Modules & Source Files |
|---|---|---|
| **Practical 1** | Project Initiation, Requirements, Sitemap, Wireframe & GitHub | `README.md`, `docs/sitemap.md`, `docs/wireframes.md`, `.gitignore` |
| **Practical 2** | Semantic HTML5 Pages with Accessibility Structure | `index.html`, `about.html`, `registration.html`, `login.html`, `dashboard.html`, `attendence.html`, `timetable.html`, `faculty.html`, `events.html`, `results.html`, `fees.html`, `contact.html` |
| **Practical 3** | Responsive UI Design using CSS Grid and Flexbox | `style.css` (CSS Variables, Flexbox Navbar, Grid layouts, Media Queries) |
| **Practical 4** | JavaScript DOM Manipulation, Events & UI Interactivity | `script.js` (Dark/Light mode, Hamburger menu, FAQ accordion, Modal popup, Notice carousel) |
| **Practical 5** | Registration Form with Frontend Validation & Password Meter | `registration.html`, `script.js` (Regex validation, password strength meter) |
| **Practical 6** | Rendering External JSON Data using Fetch API | `data/events.json`, `data/students.json`, `data/faqs.json`, `data/notices.json`, `events.html` |
| **Practical 7** | PHP Form Processing with Server Validation & Storage | `signup.php`, `process_contact.php`, `data/submissions/` (JSON & CSV storage) |
| **Practical 8** | MySQL Schema Design, ER Model & Database Connectivity | `studenthub_database.sql`, `db_connect.php` |
| **Practical 9** | Secure User Registration with DB Insert & Password Hashing | `signup.php` (BCRYPT hashing, duplicate email/username verification) |
| **Practical 10** | Secure Login/Logout, Sessions, RBAC & Remember-Me Token | `auth.php`, `login.php`, `logout.php`, `Dashboard.php` |
| **Practical 11** | Student Management CRUD with Search, Filter & CSV Export | `student_management.php` (SELECT, INSERT, UPDATE, DELETE, CSV Export) |
| **Practical 12** | Event Management CRUD with Poster Upload & File Validation | `event_management.php`, `uploads/event-posters/` (MIME & size validation) |
| **Practical 13** | PHP JSON REST API & AJAX/Fetch-Based CRUD | `students_api.php`, `student_management.js` (Asynchronous CRUD) |
| **Practical 14** | Admin Dashboard with RBAC, Dynamic Menu & Audit Log | `admin_dashboard.php`, `dashboard_audit_log` table |
| **Practical 15** | Final Integration, Testing, Deployment & Virtual Host | `.htaccess`, `data/submissions/.htaccess`, `test_report.md` |

---

## 🚀 Installation & Local Deployment Guide

### Prerequisites
- [XAMPP](https://www.apachefriends.org/) (Apache 2.4+ and MySQL 8.x+)
- Web Browser (Chrome, Firefox, or Edge)
- Git Command Line Tool

### Step 1: Clone the Repository
Clone the repository into your local development workspace:
```bash
git clone https://github.com/Malkesh5117/StudentHub-Portal.git
cd StudentHub-Portal
```

### Step 2: Database Setup
1. Launch **XAMPP Control Panel** and start **Apache** and **MySQL**.
2. Open phpMyAdmin at `http://localhost/phpmyadmin`.
3. Create a new database named `studenthub`.
4. Import the schema and seed data from `studenthub_database.sql`:
```bash
mysql -u root -p studenthub < studenthub_database.sql
```

### Step 3: Configure Database Connection
Edit `db_connect.php` if your MySQL configuration differs from default XAMPP credentials:
```php
$dbHost = 'localhost';
$dbUser = 'root';
$dbPass = ''; // Leave blank for default XAMPP
$dbName = 'studenthub';
```

### Step 4: Configure Apache Virtual Host (Optional)
To run the project under a dedicated local domain (`studenthub.localhost`):
1. Open `C:\xampp\apache\conf\extra\httpd-vhosts.conf` in an editor.
2. Add the virtual host directive:
```apache
<VirtualHost *:80>
    ServerName studenthub.localhost
    DocumentRoot "c:/Users/malke/Downloads/25dcs088/StudentHub-Portal"
    <Directory "c:/Users/malke/Downloads/25dcs088/StudentHub-Portal">
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    ErrorLog "logs/studenthub-error.log"
    CustomLog "logs/studenthub-access.log" combined
</VirtualHost>
```
3. Restart Apache from the XAMPP Control Panel.
4. Visit `http://studenthub.localhost/` in your web browser.

---

## 🔑 Default Credentials for Evaluation

| User Role | Email Address | Password | Privileges / Landing Page |
|---|---|---|---|
| **Student** | `malkesh.patel@studenthub.edu` | `Password@123` | Student Workspace (`Dashboard.php`) |
| **Administrator** | `admin@studenthub.edu` | `Password@123` | Administrative Oversight (`admin_dashboard.php`) |

---

## 🔒 Security Architecture
- **Password Protection:** Standardized on `password_hash()` using `PASSWORD_BCRYPT` with salt rounds.
- **Prepared Statements:** 100% of SQL write and lookup queries utilize parameterized `mysqli_stmt` bindings.
- **CSRF Defense:** Cryptographic tokens generated via `bin2hex(random_bytes(32))` verified on every mutating POST/PUT/DELETE request.
- **Session Hardening:** Cookies flagged with `HttpOnly`, `SameSite=Lax`, strict session mode, and session ID regeneration upon privilege escalation.
- **File Upload Safeguards:** Multi-tier validation verifying upload errors, maximum 2MB size, extension whitelist, and binary MIME type inspection using `finfo`.

---

## 📜 Academic Declaration
This project is submitted in partial fulfillment of the requirements for the **Web Development Frameworks (ITUE203)** course at the **Faculty of Technology and Engineering (FTE), Charotar University of Science and Technology (CHARUSAT)** for the 3rd Semester, Academic Year 2026-27.

**Developer:**
Malkesh Patel  
Roll Number: **25DCS089**  
GitHub: [@Malkesh5117](https://github.com/Malkesh5117)
