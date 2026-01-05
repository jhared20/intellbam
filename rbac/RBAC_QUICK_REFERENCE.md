# RBAC Quick Reference Guide

## Access Control at a Glance

### Admin Role
```
✓ Full Access to Everything
├── User Management (Add, Edit, Delete)
├── Inventory Management (Products, Categories, Stock)
├── Customer Management
├── View All Sales & Reports
└── System Configuration
```

### Cashier Role
```
✓ Sales Operations Only
├── Create Sales Transactions
├── Process Payments & Receipts
├── View Own Sales History
└── View Product Stock (Read-only)

✗ No Access To:
├── User Management
├── Inventory Management
├── Customer Management
├── System Reports
└── System Settings
```

---

## How to Check User Role in Code

### In PHP Files:
```php
// Check if user is admin
if (isAdmin()) {
    // Admin-only code
}

// Check if user is cashier
if (isCashier()) {
    // Cashier-only code
}

// Check if logged in
if (isLoggedIn()) {
    // Any logged-in user
}

// Require admin (with redirect)
requireAdmin();

// Require login (with redirect)
requireLogin();

// Require admin or the owner of a record
requireAdminOrOwner($user_id);
```

---

## Page Protection Pattern

### For Admin-Only Pages:
```php
<?php
/**
 * Page Description
 */

require_once '../../config.php';
requireAdmin();  // ← Add this line

$page_title = 'Page Title';
require_once '../../includes/header.php';

// Rest of page code...
```

### For All Logged-In Users:
```php
<?php
require_once '../../config.php';
requireLogin();  // ← Use this for general access

// Rest of page code...
```

---

## Menu Navigation Rules

### Visible to All:
- Dashboard
- POS / Sales

### Admin Only:
- Products
- Categories  
- Customers
- Reports
- Users

Code in `includes/header.php`:
```php
<?php if (isAdmin()): ?>
    <!-- Show admin menu items -->
<?php endif; ?>
```

---

## Session Variables

After login, these are available:
```php
$_SESSION['user_id']   // Numeric ID
$_SESSION['username']  // Username string
$_SESSION['role']      // 'admin' or 'cashier'
```

---

## Filtering Data by Role

### For Sales Reports:
```php
if (isAdmin()) {
    // Get all sales
    $query = "SELECT * FROM sales";
} else {
    // Get only this user's sales
    $query = "SELECT * FROM sales WHERE user_id = ?";
    // bind $_SESSION['user_id']
}
```

---

## Default Test Users

| Username | Password | Role |
|----------|----------|------|
| admin | admin123 | admin |
| cashier | cashier123 | cashier |

---

## Access Denied Response

When unauthorized user tries to access restricted page:
- Automatically redirects to: `/index.php` (Dashboard)
- No error message shown to user
- Action is logged (if activity logging enabled)

---

## Testing Checklist

### Admin User Should:
- [ ] See all menu items
- [ ] Access all pages
- [ ] See system-wide dashboard statistics
- [ ] Manage users, products, categories, customers
- [ ] View all sales and reports

### Cashier User Should:
- [ ] See only Dashboard and POS/Sales menu items
- [ ] Access only sales pages
- [ ] See only personal dashboard statistics
- [ ] See only own sales history
- [ ] NOT access admin pages (redirects to dashboard)
- [ ] NOT see user/product/category/customer management

---

## Common Patterns

### Conditionally Show UI Element:
```php
<?php if (isAdmin()): ?>
    <a href="products/index.php">Products</a>
<?php endif; ?>
```

### Conditionally Show Data:
```php
<?php if (isAdmin()): ?>
    <td><?php echo $item['created_by']; ?></td>
<?php endif; ?>
```

### Get Current User Info:
```php
$username = $_SESSION['username'];
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
```

---

## Pages by Access Level

### Public (No Login Required):
- `/auth/login.php`

### All Users:
- `/index.php` (Dashboard - filtered content)
- `/sales/pos.php` (POS Interface)
- `/sales/checkout.php` (Checkout)
- `/sales/receipt.php` (Receipt)
- `/sales/index.php` (Sales History - filtered)
- `/auth/logout.php`

### Admin Only:
- `/users/*` (All user management)
- `/products/*` (All product management)
- `/categories/*` (All category management)
- `/customers/*` (All customer management)
- `/reports/*` (All reporting)

---

## Security Notes

✓ Uses session-based authentication (no API tokens)
✓ Passwords hashed with bcrypt
✓ SQL injection protected (prepared statements)
✓ XSS protected (output escaping)
✓ CSRF protected (session-based)
✓ No sensitive data in error messages
✓ Role checked on every sensitive page
✓ Automatic redirect on unauthorized access

---

## Quick Troubleshooting

| Problem | Solution |
|---------|----------|
| Cashier sees admin menu | Check `isAdmin()` condition in header.php |
| Can't access admin page | Verify `requireAdmin()` is called in page |
| Session lost | Check config.php session settings |
| Wrong data shown | Verify role-based query filtering |
| Redirect loop | Check redirect URLs match BASE_URL |

---

## Files to Know

| File | Purpose |
|------|---------|
| `config.php` | Access control functions |
| `includes/header.php` | Navigation & role-based menu |
| `index.php` | Dashboard with role-filtered stats |
| `sales/index.php` | Sales history with role filtering |
| `auth/login.php` | Session initialization |

---

*Last Updated: January 2026*
