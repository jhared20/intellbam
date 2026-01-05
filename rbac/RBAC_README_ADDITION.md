# Role-Based Access Control (RBAC) Addition to README

## 🔐 Role-Based Access Control

The system now includes complete role-based access control (RBAC) with two user roles:

### Admin Role (Full Access)
- Manage users (add, edit, delete admins and cashiers)
- Manage products, categories, and customers
- View and edit inventory stocks
- View all sales, transactions, and reports
- Access system settings and activity logs
- Perform all system operations

### Cashier Role (Sales-Only Access)
- Login to the system
- View product list and available stock (read-only)
- Create sales transactions
- Process payments and print receipts
- View own sales history only
- No access to user management, system settings, or reports

### Default Test Users

```
Admin Account:
- Username: admin
- Password: admin123
- Role: admin

Cashier Account:
- Username: cashier
- Password: cashier123
- Role: cashier
```

## 🔑 Access Control Features

### Session-Based Authentication
- Secure session management with role tracking
- Automatic login/logout
- Session variables: user_id, username, role
- HTTP-only cookies for security

### Role-Based Navigation
- Menu items dynamically shown/hidden based on user role
- Admin users see all menu items
- Cashier users see only POS and Sales menus
- Role badge displayed in sidebar

### Data Isolation
- Cashiers can only view their own sales
- Admins can view all sales system-wide
- Dashboard statistics filtered by role
- Reports show role-appropriate data

### Automatic Redirects
- Unauthorized access attempts redirect to dashboard
- No sensitive data exposed in error messages
- Seamless user experience

## 📁 RBAC Implementation Files

### Documentation (Read These First)
- **RBAC_IMPLEMENTATION_SUMMARY.md** - Quick overview of changes
- **RBAC_QUICK_REFERENCE.md** - Developer quick reference
- **RBAC_IMPLEMENTATION.md** - Complete technical documentation
- **RBAC_TESTING_CHECKLIST.md** - Testing guide

### Core RBAC Functions (config.php)
```php
requireLogin()              // Require any logged-in user
requireAdmin()              // Require admin role
requireCashier()            // Require cashier or admin
requireAdminOrOwner()       // Require admin OR record owner
isAdmin()                   // Check if admin
isCashier()                 // Check if cashier
isLoggedIn()                // Check if logged in
```

### Pages Secured with Admin Access
- User Management: users/* (4 pages)
- Product Management: products/* (4 pages)
- Category Management: categories/* (4 pages)
- Customer Management: customers/* (4 pages)
- Reports: reports/index.php (1 page)

### Pages with Role-Based Filtering
- Dashboard (index.php) - Shows different stats based on role
- Sales History (sales/index.php) - Filters sales by user for cashiers

### Pages Accessible to All Logged-In Users
- POS Interface (sales/pos.php)
- Checkout (sales/checkout.php)
- Receipt (sales/receipt.php)
- Logout (auth/logout.php)

## 🔒 Security Features

✅ **Session Security**
- HTTP-only cookies
- Session-based authentication only
- Secure password hashing (bcrypt)

✅ **Data Protection**
- SQL injection prevention (prepared statements)
- XSS prevention (output escaping)
- CSRF protection (session-based)

✅ **Access Control**
- Role checked on every sensitive page
- Automatic redirection for unauthorized access
- Data isolation by user/role

## 🧪 Testing RBAC

### Quick Test as Admin
1. Login with `admin` / `admin123`
2. Verify all menu items are visible:
   - Dashboard, POS, Products, Categories, Customers, Reports, Users
3. Try accessing `/products/index.php` - should work
4. Check dashboard shows system-wide statistics

### Quick Test as Cashier
1. Login with `cashier` / `cashier123`
2. Verify only these menu items visible:
   - Dashboard, POS / Sales
3. Try accessing `/products/index.php` - redirects to dashboard
4. Check dashboard shows only personal sales statistics

### Full Testing
See **RBAC_TESTING_CHECKLIST.md** for comprehensive test cases.

## 🛠️ Development: Adding Access Control to New Pages

### For Admin-Only Pages
```php
<?php
require_once '../../config.php';
requireAdmin();  // Add this line

$page_title = 'Admin Page';
require_once '../../includes/header.php';
// Your page code...
```

### For Pages Visible to Specific Roles
```php
<?php if (isAdmin()): ?>
    <!-- Admin-only content -->
<?php elseif (isCashier()): ?>
    <!-- Cashier-only content -->
<?php endif; ?>
```

### For Filtering Data by Role
```php
if (isAdmin()) {
    // Get all data
} else {
    // Get only user's data
    $query .= " WHERE user_id = ?";
    $stmt->execute([$_SESSION['user_id']]);
}
```

## 📊 Database Schema

### Users Table
```sql
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'cashier') NOT NULL DEFAULT 'cashier',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

The `role` field uses ENUM to restrict values to 'admin' or 'cashier'.

## 🚀 Next Steps

1. **Read Documentation**: Start with RBAC_IMPLEMENTATION_SUMMARY.md
2. **Test the System**: Follow RBAC_TESTING_CHECKLIST.md
3. **Create Users**: Use admin account to add more users
4. **Deploy**: For production, set `session.cookie_secure = 1` in config.php if using HTTPS

## 📚 Additional Resources

- **config.php** - Contains all access control functions
- **includes/header.php** - Navigation with role-based menu items
- **index.php** - Dashboard with role-filtered statistics
- **auth/login.php** - Authentication with role assignment

## ⚠️ Important Security Notes

1. Change default passwords immediately before production use
2. Use HTTPS in production and set `session.cookie_secure = 1`
3. Regular database backups are recommended
4. Monitor activity logs for suspicious behavior
5. Keep PHP and MySQL updated

## 🐛 Troubleshooting

| Issue | Solution |
|-------|----------|
| Can't see admin menu | Make sure you're logged in as admin user |
| Redirected to login | Check that you're logged in |
| Cashier sees products page | Clear browser cache and log in again |
| Wrong sales shown | Check role filtering in sales/index.php |

## 📞 Support

For questions about the RBAC system:
1. Check RBAC_QUICK_REFERENCE.md for quick answers
2. Review RBAC_IMPLEMENTATION.md for detailed explanations
3. Check code comments in modified files
4. Review error logs and PHP error reporting

---

**Last Updated:** January 5, 2026
**RBAC Version:** 1.0
**Status:** ✅ IMPLEMENTED & TESTED
