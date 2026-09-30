# StudentHub Portal - Comprehensive Test Execution Report

**Student Details:**
- **Name:** Malkesh Patel
- **Roll Number:** 25DCS089
- **Subject:** Web Development Frameworks (ITUE203)
- **Academic Term:** 3rd Semester, 2026-27 (ODD)
- **Repository:** https://github.com/Malkesh5117/StudentHub-Portal.git

---

## 1. Test Summary

| Test Suite | Module | Test Cases Executed | Passed | Failed | Status |
|---|---|---|---|---|---|
| TS-01 | Semantic HTML5 & Navigation | 12 | 12 | 0 | PASSED |
| TS-02 | CSS Grid & Flexbox Responsiveness | 8 | 8 | 0 | PASSED |
| TS-03 | Client-Side JS Interactivity | 10 | 10 | 0 | PASSED |
| TS-04 | Registration Form Validation (Regex) | 14 | 14 | 0 | PASSED |
| TS-05 | JSON Data Consumption (Fetch API) | 8 | 8 | 0 | PASSED |
| TS-06 | Server-Side Form Processing & Sanitization | 10 | 10 | 0 | PASSED |
| TS-07 | Database Connectivity & Prepared Statements | 8 | 8 | 0 | PASSED |
| TS-08 | User Registration & Password Hashing | 9 | 9 | 0 | PASSED |
| TS-09 | Session Authentication & RBAC | 11 | 11 | 0 | PASSED |
| TS-10 | Student Management CRUD | 10 | 10 | 0 | PASSED |
| TS-11 | Event Management & Poster Uploads | 8 | 8 | 0 | PASSED |
| TS-12 | RESTful JSON API (Async Fetch) | 9 | 9 | 0 | PASSED |
| TS-13 | Admin Dashboard & Audit Trail | 7 | 7 | 0 | PASSED |
| **Total** | | **124** | **124** | **0** | **100% PASS** |

---

## 2. Key Test Cases

### TC-REG-01: Valid Registration
- **Input:** Name = "Malkesh Patel", Email = "malkesh.patel@studenthub.edu", Mobile = "9876543210", Password = "Password@123", Course = "B.Tech CSE", Year = "2nd Year", Terms = Checked.
- **Expected:** Account created in MySQL, password hashed with BCRYPT, redirection with success banner.
- **Result:** PASSED.

### TC-AUTH-02: Role-Based Redirection
- **Action:** Sign in with student account vs admin account.
- **Expected:** Student redirected to `Dashboard.php`; admin redirected to `admin_dashboard.php`.
- **Result:** PASSED.

### TC-SEC-03: SQL Injection Immunity
- **Input:** `' OR '1'='1` in login email field.
- **Expected:** Prepared statement safely binds string literal; query fails cleanly with "Invalid email or password".
- **Result:** PASSED.
