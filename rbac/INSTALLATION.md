# Installation Guide

## Quick Start

### Step 1: Database Setup

1. Open phpMyAdmin or MySQL command line
2. Create a new database named `inventory_pos`
3. Import the schema file:
   ```sql
   source database/schema.sql
   ```
   Or use phpMyAdmin's Import feature to import `database/schema.sql`

### Step 2: Configuration

1. Open `config.php` in a text editor
2. Update database credentials:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'inventory_pos');
   define('DB_USER', 'root');
   define('DB_PASS', 'your_password');
   ```
3. Update `BASE_URL` to match your installation:
   ```php
   define('BASE_URL', 'http://localhost/inventory/');
   ```

### Step 3: Setup Default Users

**Option A: Via Browser**
1. Navigate to: `http://localhost/inventory/setup.php`
2. Click "Run Setup"
3. Default users will be created with proper password hashes

**Option B: Via Command Line**
```bash
php setup.php
```

### Step 4: Access the System

1. Navigate to: `http://localhost/inventory/auth/login.php`
2. Login with default credentials:
   - **Admin:** admin / admin123
   - **Cashier:** cashier / cashier123

### Step 5: Change Default Passwords

⚠️ **Important:** Change the default passwords immediately after first login!

1. Login as admin
2. Go to Users module
3. Edit each user and set new passwords

## Troubleshooting

### Database Connection Error

**Problem:** "Database connection failed"

**Solutions:**
- Verify MySQL service is running
- Check database credentials in `config.php`
- Ensure database `inventory_pos` exists
- Check MySQL user has proper permissions

### Session Issues

**Problem:** Login doesn't persist

**Solutions:**
- Check PHP session directory is writable
- Verify `session_start()` is called before any output
- Check PHP `session.save_path` setting

### 404 Errors

**Problem:** Pages not found

**Solutions:**
- Verify `BASE_URL` in `config.php` matches your installation path
- Check Apache mod_rewrite is enabled (if using .htaccess)
- Verify file permissions

### Permission Denied

**Problem:** Cannot write to files

**Solutions:**
- Ensure web server user has read/write permissions
- Check file ownership
- On Linux: `chmod -R 755 inventory/`

## File Permissions

Recommended permissions:
- Directories: `755`
- PHP files: `644`
- Config files: `600` (more secure)

## Production Deployment

Before deploying to production:

1. ✅ Change all default passwords
2. ✅ Update `BASE_URL` to production domain
3. ✅ Disable error display in `config.php`:
   ```php
   ini_set('display_errors', 0);
   error_reporting(0);
   ```
4. ✅ Use HTTPS (update `session.cookie_secure` in `config.php`)
5. ✅ Set up regular database backups
6. ✅ Review and restrict file permissions
7. ✅ Remove or protect `setup.php`

## Support

For issues or questions, check:
- README.md for feature documentation
- Code comments for implementation details

