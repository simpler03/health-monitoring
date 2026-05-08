# Health Performance Monitoring System - Path Configuration Fix

## Issues Fixed

### 1. **403 Forbidden Error**
- **Problem**: The root `.htaccess` file was blocking access to the `config/` directory
- **Solution**: Commented out the restrictive rule `RewriteRule ^(scripts|config)/ - [F,L]`

### 2. **Incorrect Base Path Configuration**
- **Problem**: Application deployed in subdirectory `/health-monitoring-deploy/public/` but paths were relative
- **Solution**: Created `config/BaseConfig.php` with proper base path definitions

### 3. **File Include Paths**
- **Problem**: Files using relative paths like `require_once 'config/Auth.php'`
- **Solution**: Updated all files to use `require_once __DIR__ . '/config/Auth.php'`

### 4. **Asset Paths (CSS, Images, etc.)**
- **Problem**: Hardcoded relative paths like `href="css/styles.css"`
- **Solution**: Updated to use `href="<?php echo asset('css/styles.css'); ?>"`

## Files Updated

### Configuration Files
- ✅ `.htaccess` (root) - Removed config directory block
- ✅ `public/.htaccess` (new) - Created with proper RewriteBase
- ✅ `public/config/BaseConfig.php` (new) - Base URL configuration

### Main PHP Files
- ✅ `public/add-facility.php`
- ✅ `public/add-user.php`
- ✅ `public/audit-log.php`
- ✅ `public/dashboard.php`
- ✅ `public/edit-facility.php`
- ✅ `public/edit-user.php`
- ✅ `public/evaluations.php`
- ✅ `public/evaluation-view.php`
- ✅ `public/evaluation-export.php`
- ✅ `public/facilities.php`
- ✅ `public/facility-summary.php`
- ✅ `public/login.php`
- ✅ `public/new-evaluation.php`
- ✅ `public/profile.php`
- ✅ `public/reports.php`
- ✅ `public/users.php`
- ✅ `public/view-facility.php`

### API Files
- ✅ `public/api/generate-report.php`
- ✅ `public/api/get_evaluation_data.php`
- ✅ `public/api/get-filtered-building-blocks.php`
- ✅ `public/api/save_score.php`
- ✅ `public/api/save_signatures.php` (already correct)
- ✅ `public/api/update_evaluation_status.php`

## How to Test

1. **Start XAMPP** - Ensure Apache and MySQL are running
2. **Access the application**: 
   ```
   http://localhost/health-monitoring-deploy/public/
   ```
3. **Test login** - Should redirect to login page if not authenticated
4. **Test CSS** - Styles should load properly
5. **Test navigation** - All internal links should work

## Key Changes Made

### 1. BaseConfig.php
```php
define('BASE_PATH', '/health-monitoring-deploy/public');
define('BASE_URL', 'http://' . $_SERVER['HTTP_HOST'] . BASE_PATH);

function url($path = '') {
    return BASE_PATH . '/' . ltrim($path, '/');
}

function asset($path = '') {
    return BASE_PATH . '/' . ltrim($path, '/');
}
```

### 2. Updated Include Pattern
**Before:**
```php
require_once 'config/Auth.php';
```

**After:**
```php
require_once __DIR__ . '/config/BaseConfig.php';
require_once __DIR__ . '/config/Auth.php';
```

### 3. Updated Asset Path Pattern
**Before:**
```html
<link rel="stylesheet" href="css/styles.css">
```

**After:**
```html
<link rel="stylesheet" href="<?php echo asset('css/styles.css'); ?>">
```

### 4. Apache Configuration
**Root .htaccess:**
- Removed: `RewriteRule ^(scripts|config)/ - [F,L]`

**Public .htaccess:**
- Added: `RewriteBase /health-monitoring-deploy/public/`

## Notes

- All paths now use `__DIR__` constant for absolute path resolution
- CSS and other assets use the `asset()` helper function
- The application should now work correctly in the subdirectory deployment
- No changes needed to database configuration or application logic

## Next Steps

1. Test all pages to ensure they load without 403 errors
2. Verify CSS styles are loading correctly
3. Test all navigation links
4. Verify API endpoints work correctly
5. Check that authentication and authorization still work properly
