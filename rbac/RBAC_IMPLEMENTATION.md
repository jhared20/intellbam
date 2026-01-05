# Role-Based Access Control (RBAC) Implementation

## Overview
This document describes the complete Role-Based Access Control (RBAC) system implemented for the Inventory & POS System. The system uses session-based authentication with two user roles: **Admin** and **Cashier**.

## User Roles & Permissions

### 1. Admin Access (Full Access)
Admins have complete access to all system features:

#### Permissions:
- **User Management**: Add, edit, delete admin and cashier users
- **Inventory Management**: 
  - Manage products (add, edit, delete)
  - Manage categories (add, edit, delete)
  - View and edit inventory stocks
  - View low stock alerts
- **Sales & Transactions**:
  - View all sales transactions and reports
  - Access daily and monthly sales reports
  - View sales from all cashiers
- **Customer Management**: Add, edit, delete customers
- **System Access**: 
  - Full access to all pages and features
  - View activity logs (if implemented)
  - Access system settings (if implemented)

#### Pages Secured with `requireAdmin()`:
- `users/index.php` - User management list
- `users/add.php` - Add new user
- `users/edit.php` - Edit user
- `users/delete.php` - Delete user
- `categories/index.php` - Categories list
- `categories/add.php` - Add category
- `categories/edit.php` - Edit category
- `categories/delete.php` - Delete category
- `products/index.php` - Products list
- `products/add.php` - Add product
- `products/edit.php` - Edit product
- `products/delete.php` - Delete product
- `customers/index.php` - Customers list
- `customers/add.php` - Add customer
- `customers/edit.php` - Edit customer
- `customers/delete.php` - Delete customer
- `reports/index.php` - Sales reports and analytics

---

### 2. Cashier Access (Limited Access)
Cashiers have restricted access to only sales-related operations:

#### Permissions:
- **Authentication**: 
  - Login to the system ✓
  - Logout ✓
- **Sales Operations**:
  - View product list and available stock (read-only) ✓
  - Create sales transactions ✓
  - Process payments and print receipts ✓
  - View own sales history only ✓
- **Restricted Operations** (No Access):
  - ✗ Cannot manage users
  - ✗ Cannot manage products, categories, or suppliers
  - ✗ Cannot access inventory management
  - ✗ Cannot view system-wide sales reports
  - ✗ Cannot access system settings
  - ✗ Cannot manage customers
  - ✗ Cannot view other cashiers' sales

#### Accessible Pages:
- `index.php` - Dashboard (restricted stats - shows only own sales)
- `sales/pos.php` - Point of Sale interface
- `sales/checkout.php` - Checkout and payment processing
- `sales/receipt.php` - Receipt viewing
- `sales/index.php` - Sales history (shows only own sales)
- `auth/logout.php` - Logout

---

## Implementation Details

### 1. Session-Based Authentication
**File**: `config.php`

Session variables set during login:
```php
$_SESSION['user_id']   // User's unique ID
$_SESSION['username']  // User's login name
$_SESSION['role']      // User's role: 'admin' or 'cashier'
```

### 2. Access Control Functions
**File**: `config.php`

#### `requireLogin()`
Redirects to login page if user is not authenticated.

#### `requireAdmin()`
- Checks if user is logged in
- Redirects to dashboard if user is not an admin
- Used on all admin-only pages

#### `isAdmin()`
Returns `true` if user is logged in and has 'admin' role.

#### `isCashier()`
Returns `true` if user is logged in and has 'cashier' role.

#### `requireCashier()` (Optional)
Returns `true` if user is logged in and is either cashier or admin.

#### `requireAdminOrOwner($user_id_to_check)`
Verifies that user is either an admin OR owns the record being accessed.
Used for viewing personal sales histories.

---

### 3. Navigation & UI
**File**: `includes/header.php`

#### Role-Based Menu Items:
- **Visible to All Logged-In Users**:
  - Dashboard
  - POS / Sales

- **Visible to Admin Only**:
  - Products
  - Categories
  - Customers
  - Reports
  - Users

#### User Role Display:
The header shows the current user's role in a badge format:
```php
Logged in as: <username>
Role: [admin|cashier]
```

---

### 4. Dashboard Statistics
**File**: `index.php`

#### Admin Dashboard Shows:
- Total Products (card)
- Today's Sales (system-wide)
- This Month's Sales (system-wide)
- Low Stock Items (card)
- Recent Sales (from all cashiers)

#### Cashier Dashboard Shows:
- My Sales Today (personal)
- My Monthly Sales (personal)
- Recent Sales (only their own transactions)

The admin-only cards are hidden using `<?php if (isAdmin()): ?>` conditionals.

---

### 5. Sales History Filtering
**File**: `sales/index.php`

**Admin Access**: Sees all sales from all cashiers with filters for date range.

**Cashier Access**: Sees only their own sales, filtered by date range.

```php
// Cashiers can only see their own sales
if (!isAdmin()) {
    $query .= " AND s.user_id = ? ";
    // ... execute with user_id parameter
} else {
    // Admins see all sales
    // ... execute without user filter
}
```

---

## Database Schema
**File**: `database/schema.sql`

### Users Table
```sql
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'cashier') NOT NULL DEFAULT 'cashier',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
```

The `role` field uses an ENUM to restrict values to 'admin' or 'cashier'.

---

## Security Features

### 1. Session Security
- `session.cookie_httponly = 1` - Prevents JavaScript access to session cookies
- `session.use_only_cookies = 1` - Sessions only via cookies, not URLs
- `session.cookie_secure = 0` - Set to 1 if using HTTPS (recommended)

### 2. Password Security
- Passwords are hashed using `password_hash()` with bcrypt
- Verification using `password_verify()`

### 3. SQL Injection Prevention
- All database queries use prepared statements
- User inputs are bound as parameters

### 4. XSS Prevention
- All output is escaped using `escape()` function
- Uses `htmlspecialchars()` with ENT_QUOTES

### 5. Access Control Enforcement
- Every sensitive page calls `requireAdmin()` early
- Unauthorized access attempts redirect to safe page (dashboard)
- No sensitive data shown in error messages

---

## Testing the RBAC System

### Test Case 1: Admin Access
1. Login with admin user (username: `admin`)
2. Verify all menu items are visible
3. Verify dashboard shows system-wide statistics
4. Try accessing any admin page - should succeed
5. Verify users can add/edit/delete any resource

### Test Case 2: Cashier Access
1. Login with cashier user (username: `cashier`)
2. Verify only Dashboard and POS/Sales are visible in menu
3. Verify all admin menu items are hidden
4. Try accessing `/users/index.php` - should redirect to dashboard
5. Try accessing `/products/index.php` - should redirect to dashboard
6. Verify dashboard shows only personal sales statistics
7. Verify sales history shows only own transactions
8. Verify can create sales and checkout successfully

### Test Case 3: Unauthorized Access
1. Try accessing admin page directly via URL without login - redirects to login
2. Try accessing protected page as cashier - redirects to dashboard
3. Try modifying URL to access other user's data - no access granted

---

## File Changes Summary

### Modified Files:

#### 1. `config.php`
- Added `requireCashier()` function
- Added `requireAdminOrOwner($user_id_to_check)` function
- Functions already had `isAdmin()` and `isCashier()` helpers

#### 2. `includes/header.php`
- Wrapped admin-only menu items in `<?php if (isAdmin()): ?>` conditions
- Moved Products, Categories, Customers, Reports, and Users menu items
- Role badge already displayed in user info section

#### 3. `index.php` (Dashboard)
- Added role-based statistics queries
- Admin sees system-wide data, cashier sees personal data
- Wrapped admin-only dashboard cards in conditionals
- Customized card labels for cashiers ("My Sales Today" vs "Today's Sales")

#### 4. Category Management Pages
- `categories/index.php` - Changed `requireLogin()` to `requireAdmin()`
- `categories/add.php` - Changed `requireLogin()` to `requireAdmin()`
- `categories/edit.php` - Changed `requireLogin()` to `requireAdmin()`
- `categories/delete.php` - Changed `requireLogin()` to `requireAdmin()`

#### 5. Product Management Pages
- `products/index.php` - Changed `requireLogin()` to `requireAdmin()`
- `products/add.php` - Changed `requireLogin()` to `requireAdmin()`
- `products/edit.php` - Changed `requireLogin()` to `requireAdmin()`
- `products/delete.php` - Changed `requireLogin()` to `requireAdmin()`

#### 6. Customer Management Pages
- `customers/index.php` - Changed `requireLogin()` to `requireAdmin()`
- `customers/add.php` - Changed `requireLogin()` to `requireAdmin()`
- `customers/edit.php` - Changed `requireLogin()` to `requireAdmin()`
- `customers/delete.php` - Changed `requireLogin()` to `requireAdmin()`

#### 7. Reports Page
- `reports/index.php` - Changed `requireLogin()` to `requireAdmin()`

#### 8. Sales History Page
- `sales/index.php` - Added role-based query filtering for sales data

#### 9. User Management Pages
- Already secured with `requireAdmin()` (no changes needed)
- `users/index.php`, `users/add.php`, `users/edit.php`, `users/delete.php`

### Unchanged Files (Already Secure):
- `sales/pos.php` - Accessible to all logged-in users (cashiers and admins)
- `sales/checkout.php` - Accessible to all logged-in users
- `sales/receipt.php` - Accessible to all logged-in users (for viewing own receipts)
- `auth/login.php` - Already handles role assignment
- `auth/logout.php` - Works for all users

---

## Default Users

The database includes two default test users:

### Admin User
- **Username**: `admin`
- **Password**: `admin123` (if created with bcrypt hash)
- **Role**: admin
- **Access**: Full system access

### Cashier User
- **Username**: `cashier`
- **Password**: `cashier123` (if created with bcrypt hash)
- **Role**: cashier
- **Access**: Sales operations only

---

## Future Enhancements

1. **User Activity Logs**: Track all user actions in `activity_logs` table
2. **Permission Tiers**: Extend beyond 2 roles to support:
   - Manager role (reports + inventory)
   - Supervisor role (limited admin features)
3. **Two-Factor Authentication**: Add 2FA for admin accounts
4. **IP Whitelisting**: Restrict admin access to specific IPs
5. **Session Timeout**: Implement automatic logout after inactivity
6. **Audit Trail**: Detailed logging of all sensitive operations
7. **API Token Authentication**: For external integrations
8. **Dynamic Permissions**: Store permissions in database for flexibility

---

## Troubleshooting

### Issue: Cashier can't see POS page
- **Cause**: Possible error in sales/pos.php
- **Solution**: Check that `requireLogin()` is used, not `requireAdmin()`

### Issue: Admin can't see all sales
- **Cause**: Sales filtering code might be wrong
- **Solution**: Verify `isAdmin()` check in sales/index.php query logic

### Issue: Session not persisting
- **Cause**: Session start issue or cookie problem
- **Solution**: Verify `session_start()` is called in config.php before any headers

### Issue: Redirect loop
- **Cause**: Access control function redirecting to wrong location
- **Solution**: Check BASE_URL constant and redirect paths in functions

---

## Contact & Support
For questions about the RBAC implementation, refer to the code comments in:
- `config.php` - For access control functions
- `includes/header.php` - For navigation logic
- Respective page files for page-level access control
