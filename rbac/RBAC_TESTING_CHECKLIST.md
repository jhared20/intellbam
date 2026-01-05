# RBAC Testing Checklist

## Pre-Testing Requirements
- [ ] Database is running and populated
- [ ] Web server is running (XAMPP/WAMP)
- [ ] Application is accessible at `http://localhost/inventory/`
- [ ] You have two test users available:
  - Admin: username=`admin`, password=`admin123`
  - Cashier: username=`cashier`, password=`cashier123`

---

## Test Case 1: Login Functionality

### Scenario: Valid Credentials
- [ ] Navigate to login page
- [ ] Enter admin username and password
- [ ] Click Login
- **Expected Result**: Redirected to Dashboard, session created

### Scenario: Invalid Credentials
- [ ] Enter incorrect username/password
- [ ] Click Login
- **Expected Result**: Error message displayed, not logged in

### Scenario: Session Persistence
- [ ] Login successfully
- [ ] Navigate to different pages
- **Expected Result**: Session persists, no redirect to login

---

## Test Case 2: Admin Access - Navigation Menu

### Scenario: Check Visible Menu Items (Admin User)
Login as admin and verify menu shows:
- [ ] Dashboard ✓
- [ ] POS / Sales ✓
- [ ] Products ✓
- [ ] Categories ✓
- [ ] Customers ✓
- [ ] Reports ✓
- [ ] Users ✓

All 7 items should be visible.

### Scenario: User Info Display
- [ ] Check sidebar shows "Logged in as: admin"
- [ ] Check role badge shows "admin"
- [ ] Check Logout link is present

---

## Test Case 3: Cashier Access - Navigation Menu

### Scenario: Check Visible Menu Items (Cashier User)
Login as cashier and verify menu shows:
- [ ] Dashboard ✓
- [ ] POS / Sales ✓
- [ ] Products ✗ (Hidden)
- [ ] Categories ✗ (Hidden)
- [ ] Customers ✗ (Hidden)
- [ ] Reports ✗ (Hidden)
- [ ] Users ✗ (Hidden)

Only 2 items should be visible.

### Scenario: User Info Display
- [ ] Check sidebar shows "Logged in as: cashier"
- [ ] Check role badge shows "cashier"
- [ ] Check Logout link is present

---

## Test Case 4: Admin Dashboard Statistics

### Scenario: Admin Dashboard Cards
Login as admin and check Dashboard displays:
- [ ] Total Products card (blue) - shows a number
- [ ] Today's Sales card (green) - shows currency amount
- [ ] This Month card (cyan) - shows currency amount
- [ ] Low Stock Items card (orange) - shows a number

**Expected**: 4 cards visible, all with system-wide data

### Scenario: Check Recent Sales
- [ ] Scroll down to "Recent Sales" section
- [ ] Verify table shows sales from all users (not just admin)
- [ ] Check usernames column shows different cashier names

---

## Test Case 5: Cashier Dashboard Statistics

### Scenario: Cashier Dashboard Cards
Login as cashier and check Dashboard displays:
- [ ] "My Sales Today" card (green) - shows currency
- [ ] "My Monthly Sales" card (cyan) - shows currency
- [ ] Total Products card ✗ (Hidden)
- [ ] Low Stock Items card ✗ (Hidden)

**Expected**: 2 cards visible, both showing personal data

### Scenario: Check Recent Sales
- [ ] Scroll down to "Recent Sales" section
- [ ] Verify table shows ONLY this cashier's sales
- [ ] Check all usernames shown are "cashier"

---

## Test Case 6: Admin Product Management

### Scenario: Access Products Page
As admin:
- [ ] Click "Products" in menu
- [ ] Navigate to `/products/index.php`
- **Expected Result**: Page loads successfully

### Scenario: Add Product
- [ ] Click "Add Product" button
- [ ] Fill in product details
- [ ] Submit form
- **Expected Result**: Product created successfully

### Scenario: Edit Product
- [ ] Click Edit button on any product
- [ ] Modify details
- [ ] Submit form
- **Expected Result**: Product updated successfully

### Scenario: Delete Product
- [ ] Click Delete button on any product
- [ ] Confirm deletion
- **Expected Result**: Product removed successfully

---

## Test Case 7: Cashier Product Access Denied

### Scenario: Try to Access Products Page
As cashier:
- [ ] Try to navigate to `/products/index.php` directly
- **Expected Result**: Redirected to Dashboard, NOT shown products page

### Scenario: Try to Access Add Product
- [ ] Try to navigate to `/products/add.php` directly
- **Expected Result**: Redirected to Dashboard

### Scenario: Try to Access Edit Product
- [ ] Try to navigate to `/products/edit.php?id=1` directly
- **Expected Result**: Redirected to Dashboard

---

## Test Case 8: Admin Category Management

### Scenario: Access Categories Page
As admin:
- [ ] Click "Categories" in menu
- [ ] Navigate to `/categories/index.php`
- **Expected Result**: Page loads successfully, see categories list

### Scenario: Add Category
- [ ] Click "Add Category" button
- [ ] Enter category name
- [ ] Submit form
- **Expected Result**: Category created successfully

### Scenario: Edit Category
- [ ] Click Edit on a category
- [ ] Modify category name
- [ ] Submit form
- **Expected Result**: Category updated successfully

### Scenario: Delete Category
- [ ] Click Delete on a category
- [ ] Confirm deletion
- **Expected Result**: Category removed successfully

---

## Test Case 9: Cashier Category Access Denied

### Scenario: Try to Access Categories Page
As cashier:
- [ ] Try to navigate to `/categories/index.php` directly
- **Expected Result**: Redirected to Dashboard

### Scenario: Try to Access Add Category
- [ ] Try to navigate to `/categories/add.php` directly
- **Expected Result**: Redirected to Dashboard

---

## Test Case 10: Admin User Management

### Scenario: Access Users Page
As admin:
- [ ] Click "Users" in menu
- [ ] Navigate to `/users/index.php`
- **Expected Result**: Page loads successfully, see users list

### Scenario: Add New User
- [ ] Click "Add User" button
- [ ] Fill in username, password, and role
- [ ] Select role (admin or cashier)
- [ ] Submit form
- **Expected Result**: New user created successfully

### Scenario: Edit User
- [ ] Click Edit on any user
- [ ] Modify user details
- [ ] Submit form
- **Expected Result**: User updated successfully

### Scenario: Delete User
- [ ] Click Delete on any user
- [ ] Confirm deletion
- **Expected Result**: User removed successfully

---

## Test Case 11: Cashier User Management Access Denied

### Scenario: Try to Access Users Page
As cashier:
- [ ] Try to navigate to `/users/index.php` directly
- **Expected Result**: Redirected to Dashboard

### Scenario: Try to Access Add User
- [ ] Try to navigate to `/users/add.php` directly
- **Expected Result**: Redirected to Dashboard

---

## Test Case 12: Admin Customer Management

### Scenario: Access Customers Page
As admin:
- [ ] Click "Customers" in menu
- [ ] Navigate to `/customers/index.php`
- **Expected Result**: Page loads, see customers list

### Scenario: Add Customer
- [ ] Click "Add Customer" button
- [ ] Fill in customer details
- [ ] Submit form
- **Expected Result**: Customer created successfully

---

## Test Case 13: Cashier Customer Access Denied

### Scenario: Try to Access Customers Page
As cashier:
- [ ] Try to navigate to `/customers/index.php` directly
- **Expected Result**: Redirected to Dashboard

---

## Test Case 14: Admin Reports Access

### Scenario: Access Reports Page
As admin:
- [ ] Click "Reports" in menu
- [ ] Navigate to `/reports/index.php`
- **Expected Result**: Page loads, see sales report data
- [ ] Check report shows all sales (system-wide)

### Scenario: Filter Reports by Date
- [ ] Select different date ranges
- [ ] Change report type (daily/monthly)
- **Expected Result**: Report data updates accordingly

---

## Test Case 15: Cashier Reports Access Denied

### Scenario: Try to Access Reports Page
As cashier:
- [ ] Try to navigate to `/reports/index.php` directly
- **Expected Result**: Redirected to Dashboard

---

## Test Case 16: POS Sales Operations (Cashier)

### Scenario: Access POS
As cashier:
- [ ] Click "POS / Sales" in menu
- [ ] Navigate to `/sales/pos.php`
- **Expected Result**: POS interface loads successfully

### Scenario: Create New Sale
- [ ] Add products to cart
- [ ] Enter quantity
- [ ] View total
- [ ] Proceed to checkout
- **Expected Result**: Sale process works normally

### Scenario: Complete Sale
- [ ] Fill in customer information
- [ ] Select payment method
- [ ] Process payment
- **Expected Result**: Sale saved, receipt displayed

---

## Test Case 17: Sales History - Role Filtering

### Scenario: Admin Views Sales History
As admin:
- [ ] Click "POS / Sales" > "Sales History"
- [ ] Navigate to `/sales/index.php`
- **Expected Result**: 
  - [ ] Page loads
  - [ ] Shows sales from ALL users (multiple usernames)
  - [ ] Can see all transactions system-wide

### Scenario: Cashier Views Sales History
As cashier:
- [ ] Click "POS / Sales" > "Sales History"
- [ ] Navigate to `/sales/index.php`
- **Expected Result**:
  - [ ] Page loads
  - [ ] Shows ONLY this cashier's sales
  - [ ] All usernames shown are "cashier"
  - [ ] Cannot see other cashiers' sales

### Scenario: Filter Sales by Date (Cashier)
- [ ] Select date range
- [ ] Apply filter
- **Expected Result**: Shows only cashier's sales within date range

---

## Test Case 18: Unauthorized Direct URL Access

### Scenario: Non-Login Direct Access
- [ ] Logout completely
- [ ] Try accessing `/users/index.php` directly
- **Expected Result**: Redirected to login page

### Scenario: Cashier Direct Admin Access
Login as cashier:
- [ ] Type `/products/index.php` in URL bar
- **Expected Result**: Redirected to Dashboard (not error page)

### Scenario: Cashier Direct Reports Access
- [ ] Type `/reports/index.php` in URL bar
- **Expected Result**: Redirected to Dashboard

---

## Test Case 19: Session & Logout

### Scenario: Logout as Admin
As admin:
- [ ] Click Logout in sidebar
- [ ] Navigate to Dashboard
- **Expected Result**: Redirected to login page

### Scenario: Logout as Cashier
As cashier:
- [ ] Click Logout in sidebar
- [ ] Navigate to POS page
- **Expected Result**: Redirected to login page

### Scenario: Session Destruction
After logout:
- [ ] Check browser cookies/storage
- [ ] Session should be cleared
- [ ] Cannot access protected pages

---

## Test Case 20: Edge Cases

### Scenario: Multiple Browser Tabs
- [ ] Login in Tab 1
- [ ] Open new tab with same URL
- **Expected Result**: Both tabs show logged-in state

### Scenario: Session Timeout (if implemented)
- [ ] Login
- [ ] Wait for configured timeout period
- **Expected Result**: Automatically logged out (if feature exists)

### Scenario: Invalid URL Parameters
- [ ] Try accessing `/sales/receipt.php?id=999` (non-existent)
- **Expected Result**: Error message or redirect, not data leak

---

## Performance & Security Checks

### Database Queries
- [ ] Check that cashiers' sales queries filter by user_id
- [ ] Verify dashboard stats use correct aggregation
- [ ] Confirm no N+1 query problems

### Password Security
- [ ] Verify passwords are hashed (bcrypt)
- [ ] Check login accepts correct password hash
- [ ] Confirm wrong password is rejected

### SQL Injection Prevention
- [ ] Try SQL injection in login: `' OR '1'='1`
- **Expected Result**: Login rejected, no data leak

### XSS Prevention
- [ ] Try XSS in product name: `<script>alert('xss')</script>`
- **Expected Result**: Stored safely, displayed as text

---

## Final Verification Checklist

### Admin User:
- [ ] Has access to all 7 menu items
- [ ] Can perform CRUD on all resources
- [ ] Sees system-wide statistics
- [ ] Views all sales from all cashiers
- [ ] Can manage users and roles

### Cashier User:
- [ ] Has access to only 2 menu items (Dashboard, POS/Sales)
- [ ] Cannot access admin pages (redirected)
- [ ] Sees only personal statistics
- [ ] Views only own sales history
- [ ] Cannot manage any resources except creating sales

### Guest User (Not Logged In):
- [ ] Cannot access any protected pages
- [ ] Redirected to login for all restricted pages
- [ ] Can only see login page

### Documentation:
- [ ] RBAC_IMPLEMENTATION.md exists
- [ ] RBAC_QUICK_REFERENCE.md exists
- [ ] RBAC_IMPLEMENTATION_SUMMARY.md exists
- [ ] All documentation is readable and complete

---

## Test Results Summary

### Total Test Cases: 20
- [ ] Passed: ___ / 20
- [ ] Failed: ___ / 20
- [ ] Skipped: ___ / 20

### Critical Tests (Must Pass):
- [ ] Admin can access all admin pages
- [ ] Cashier cannot access admin pages
- [ ] Sales data filtered by role
- [ ] Dashboard shows role-appropriate data
- [ ] Login/Logout works for both roles

### Status: 
**PASSED ✓ / FAILED ✗ / IN PROGRESS ◐**

---

## Sign-Off

**Tester Name**: _______________________

**Date**: _______________________

**Overall Result**: ✓ ALL TESTS PASSED / ✗ ISSUES FOUND

**Issues Found**:
```
[List any issues found during testing]
```

**Recommendations**:
```
[List any recommendations for improvements]
```

---

*This checklist should be completed after implementing the RBAC system to ensure all functionality works as expected.*
