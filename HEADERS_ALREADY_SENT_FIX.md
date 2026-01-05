# Headers Already Sent - Issue Fixed

## Problem
**Error**: "Cannot modify header information - headers already sent"

**Root Cause**: HTML output was being sent before `header()` redirect calls were executed, which is not allowed in PHP.

**Example of Wrong Order**:
```php
require_once '../config.php';
requireAdmin();
$page_title = 'Page';
require_once '../includes/header.php';  // ← HTML OUTPUT STARTS HERE

// ... later in code
if (!$record_id) {
    header('Location: index.php');  // ← TOO LATE! Can't modify headers
    exit;
}
```

## Solution
Reorganized all affected files to follow this order:

**Correct Order**:
```php
<?php
// 1. Load configuration and check access control
require_once '../config.php';
requireAdmin();

// 2. Initialize variables
$pdo = getDB();
$error = '';

// 3. Handle GET parameter validation and any redirects
$record_id = $_GET['id'] ?? 0;
if (!$record_id) {
    header('Location: index.php');  // ← CAN STILL MODIFY HEADERS
    exit;
}

// 4. Load record data and any other validation that might redirect
try {
    $stmt = $pdo->prepare("SELECT * FROM table WHERE id = ?");
    $stmt->execute([$record_id]);
    $record = $stmt->fetch();
    
    if (!$record) {
        header('Location: index.php');  // ← CAN STILL MODIFY HEADERS
        exit;
    }
} catch (PDOException $e) {
    $error = "Error: " . $e->getMessage();
}

// 5. NOW include header.php which outputs HTML
$page_title = 'Page Title';
require_once '../includes/header.php';  // ← HTML STARTS HERE
?>

<!-- HTML content here -->
```

## Files Fixed (4 files)

### Edit Pages (with validation before header)
1. ✅ **categories/edit.php** - Moved validation before header include
2. ✅ **products/edit.php** - Moved validation before header include, added missing header include
3. ✅ **customers/edit.php** - Moved validation before header include, added missing header include
4. ✅ **users/edit.php** - Moved validation before header include, added missing header include
5. ✅ **sales/receipt.php** - Fixed path from `../../` to `../`

## Key Changes

### Before (Wrong):
```php
<?php
require_once '../config.php';
requireAdmin();
$page_title = 'Edit Page';
require_once '../includes/header.php';  // HTML output starts

$id = $_GET['id'] ?? 0;
if (!$id) {
    header('Location: index.php');  // ERROR: Too late!
    exit;
}
```

### After (Correct):
```php
<?php
require_once '../config.php';
requireAdmin();

$id = $_GET['id'] ?? 0;
if (!$id) {
    header('Location: index.php');  // OK: No HTML output yet
    exit;
}

$page_title = 'Edit Page';
require_once '../includes/header.php';  // Now safe to output HTML
```

## Why This Works

In PHP:
- **Headers** (including `header()` calls and session functions) must be sent BEFORE any output
- **Output** can be HTML, plain text, spaces, newlines, or anything else
- Once ANY output is sent, headers cannot be modified
- The `require_once '../includes/header.php'` file outputs HTML, so headers must be set first

## Testing

All pages should now work without the "headers already sent" error:
- ✓ Editing categories should redirect properly if ID is invalid
- ✓ Editing products should redirect properly if ID is invalid
- ✓ Editing customers should redirect properly if ID is invalid
- ✓ Editing users should redirect properly if ID is invalid
- ✓ Receipt page should load properly with correct path

---

**Fix Date**: January 5, 2026
**Files Fixed**: 5
**Status**: ✅ **RESOLVED**
