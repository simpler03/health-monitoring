# Health Performance Monitoring System - Deployment Package

## Province-Wide Local Health System Monitoring Tool

A comprehensive PHP/MySQL web application for managing health facility performance evaluations across provinces in the Philippines.

---

## Quick Setup (3 Steps)

### Step 1: Create Database
```bash
mysql -u root -p < health_monitoring.sql
```
Or import via phpMyAdmin.

### Step 2: Configure Database Connection
Edit `public/config/Database.php`:
```php
private $host = 'localhost';
private $db_name = 'health_monitoring';
private $username = 'root';
private $password = '';
```

### Step 3: Access the System
1. Point your web server to the `public/` folder
2. Navigate to: `http://localhost/login.php`
3. Login with demo credentials:
   - **Admin**: `admin` / `admin123`
   - **Input**: `input` / `input123`
   - **Viewer**: `viewer` / `viewer123`

---

## System Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher (or MariaDB 10.2+)
- Apache/Nginx with URL rewriting
- 20MB disk space
- PDO MySQL extension enabled

---

## Features

✅ **Spreadsheet-Style Interface** - Double-click cells to edit scores
✅ **Real-Time AJAX Updates** - No page reloads
✅ **Role-Based Access Control** - Admin, Input, Viewer roles
✅ **Digital Signatures** - 5 signature types with e-signatures
✅ **Audit Logging** - Complete activity trail
✅ **7 Building Blocks** - 163 KPIs for comprehensive evaluation
✅ **Multi-Facility Support** - Manage multiple health facilities
✅ **Export Capabilities** - Print to PDF, Word export

---

## Building Blocks

1. Leadership and Governance (15%)
2. Regulation (15%)
3. Health Financing (15%)
4. Human Resource for Health (15%)
5. Health Service Delivery (15%)
6. Information Communication and Technology (25%)
7. Special Indicator (0%)

---

## File Structure

```
health-monitoring-deploy/
├── public/                      ← Web root (point server here)
│   ├── config/                  ← Database & authentication
│   ├── api/                     ← AJAX endpoints
│   ├── css/                     ← Stylesheets
│   ├── images/                  ← Images and logos
│   └── [PHP pages]              ← Application pages
├── health_monitoring.sql        ← Database schema + sample data
├── README_DEPLOY.txt            ← This file
└── .htaccess                    ← Apache configuration
```

---

## Security Features

- ✅ Bcrypt password hashing
- ✅ PDO prepared statements (SQL injection prevention)
- ✅ HTTP-only session cookies
- ✅ Role-based access control
- ✅ Input validation and sanitization
- ✅ Comprehensive audit logging
- ✅ Error logging (not displayed to users)

---

## Support

For technical support or questions, refer to the full documentation or check the code comments in PHP files.

**Version**: 1.0.0
**Compatible**: PHP 7.4+, MySQL 5.7+
**License**: Republic of the Philippines - Department of Health
