# Inventory and Point of Sale (POS) System

A complete Inventory and Point of Sale (POS) System built with PHP and MySQL, featuring a modular architecture and clean separation of concerns.

## 🚀 Features

- **User Authentication** - Secure login with role-based access control
- **User Management** - Admin can manage users (admin/cashier roles)
- **Product Management** - Full CRUD operations for products with inventory tracking
- **Category Management** - Organize products by categories
- **Customer Management** - Track customer information and purchase history
- **Point of Sale (POS)** - Complete POS interface with cart system
- **Payment Processing** - Support for multiple payment methods (Cash, GCash, PayMaya, Card, etc.)
- **Sales Receipts** - Generate and print sales receipts
- **Inventory Management** - Automatic stock deduction after sales
- **Sales Reports** - Daily, monthly, and yearly sales reports
- **Activity Logging** - Track all important system actions

## 📋 Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher (or MariaDB)
- Apache/Nginx web server
- PDO extension enabled

## 🗄️ Database Setup

1. Create a new database in MySQL
2. Import the database schema:

```bash
mysql -u root -p inventory_pos < database/schema.sql
```

Or use phpMyAdmin:
- Create database named `inventory_pos`
- Import `database/schema.sql`

## ⚙️ Configuration

1. Edit `config.php` and update database credentials:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'inventory_pos');
define('DB_USER', 'root');
define('DB_PASS', '');
define('BASE_URL', 'http://localhost/inventory/');
```

2. Make sure `BASE_URL` matches your installation path.

## 👤 Default Login Credentials

**Admin:**
- Username: `admin`
- Password: `admin123`

**Cashier:**
- Username: `cashier`
- Password: `cashier123`

⚠️ **Important:** Change these passwords after first login!

## 📁 Project Structure

```
inventory/
├── auth/
│   ├── login.php
│   └── logout.php
├── users/
│   ├── index.php
│   ├── add.php
│   ├── edit.php
│   └── delete.php
├── products/
│   ├── index.php
│   ├── add.php
│   ├── edit.php
│   └── delete.php
├── categories/
│   ├── index.php
│   ├── add.php
│   ├── edit.php
│   └── delete.php
├── customers/
│   ├── index.php
│   ├── add.php
│   ├── edit.php
│   └── delete.php
├── sales/
│   ├── pos.php
│   ├── checkout.php
│   ├── receipt.php
│   ├── index.php
│   └── clear_cart.php
├── payments/
│   └── (integrated in sales/checkout.php)
├── reports/
│   └── index.php
├── includes/
│   ├── header.php
│   └── footer.php
├── database/
│   └── schema.sql
├── config.php
└── index.php
```

## 🗄️ Database Schema (8 Tables)

1. **users** - User accounts (admin/cashier)
2. **categories** - Product categories
3. **products** - Product inventory
4. **customers** - Customer information
5. **sales** - Sales transactions
6. **sale_items** - Individual items in each sale
7. **payments** - Payment records
8. **activity_logs** - System activity tracking

## 🔐 User Roles

- **Admin** - Full access to all modules
- **Cashier/Staff** - Access to POS, products, customers, and reports (limited access)

## 🛒 POS Workflow

1. Go to **POS / Sales** from the sidebar
2. Search and add products to cart
3. Adjust quantities as needed
4. Click **Checkout**
5. Select customer (optional)
6. Choose payment method
7. Enter amount paid
8. Complete payment
9. Receipt is generated automatically

## 📊 Reports

- **Daily Report** - View sales for a specific date
- **Monthly Report** - View sales for a specific month
- **Yearly Report** - View sales for a specific year

All reports include:
- Total sales amount
- Number of transactions
- Total items sold
- Customer count

## 🔒 Security Features

- Password hashing using PHP `password_hash()`
- Prepared statements (SQL injection protection)
- Session-based authentication
- Role-based access control
- Input sanitization and validation

## 🎨 UI Features

- Responsive Bootstrap 5 design
- Modern sidebar navigation
- Clean and intuitive interface
- Print-friendly receipts
- Mobile-friendly layout

## 📝 Notes

- Stock is automatically deducted when a sale is completed
- Barcode support is available (optional field)
- Activity logging tracks all important actions
- Receipts can be printed directly from the browser
- All monetary values are in Philippine Peso (₱)

## 🐛 Troubleshooting

**Database Connection Error:**
- Check database credentials in `config.php`
- Ensure MySQL service is running
- Verify database exists

**Session Issues:**
- Check PHP session configuration
- Ensure `session_start()` is called before any output

**Permission Errors:**
- Ensure web server has read/write permissions
- Check file permissions on the project directory

## 📄 License

This project is open source and available for educational purposes.

## 👨‍💻 Development

Built with:
- PHP (Procedural)
- MySQL (PDO)
- Bootstrap 5
- Bootstrap Icons

## 🔄 Future Enhancements

Potential features to add:
- Barcode scanner integration
- Advanced inventory reports
- Product image uploads
- Email receipts
- Multi-currency support
- Export reports to PDF/Excel

---

**Note:** This is a production-ready system designed for learning and small business use. For production deployment, consider additional security measures like HTTPS, regular backups, and security audits.

