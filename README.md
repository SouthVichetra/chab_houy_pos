# 🏪 Chab Houy POS & Inventory Management System

A lightweight, secure, web-based Point of Sale (POS) and inventory management system built specifically for local convenience stores (*Chab Houy*). Designed to run locally via XAMPP, it provides real-time stock tracking, role-based staff authentication, receipt generation, and transaction history tracking.

---

## 🚀 Core Features

* **📦 Inventory & Stock Management**: Track product names, categories, pricing, and live stock quantities with full Add, Edit, and Delete capabilities.
* **🛒 Point of Sale (POS) Checkout**: Interactive grid-based shopping cart interface that automatically calculates totals and deducts stock in real time upon sale completion.
* **🔒 User Management & Authentication**: Secure role-based login system separating Admin privileges from regular Cashier access, utilizing hashed passwords (`password_hash()`) and PHP sessions.
* **📜 Transaction History & Digital Receipts**: Audit past sales, view itemized breakdowns per transaction, and print clean physical receipts.
* **📊 Store Dashboard Overview**: Dynamic real-time metrics showing total inventory items, today's total sales revenue, low-stock warnings, and recent transaction logs.

---

## 💻 Tech Stack

* **Frontend**: HTML5, CSS3 (Modern Slate UI design), JavaScript (Fetch API for asynchronous checkout)
* **Backend**: PHP (using PHP Data Objects / PDO for secure prepared statements)
* **Database**: MySQL (relational database management)
* **Local Server**: XAMPP (Apache & MySQL)

---

## 🗄️ Database Structure

The MySQL database (`convenience_store`) consists of 5 interconnected relational tables:
1. **`roles`**: Defines access control levels (Admin, Cashier).
2. **`users`**: Stores staff login credentials, password hashes, and role relationships.
3. **`products`**: Manages store inventory items, pricing, and stock levels.
4. **`transactions`**: Records overall POS sales, total amounts, timestamps, and which staff member processed the sale.
5. **`transaction_items`**: Tracks individual line items sold per transaction, linking products to transactions.

---

## 📂 Project Directory Structure

```text
convenience-store/
│
├── assets/
│   └── css/
│       └── style.css
│
├── db.php                 # Database connection script (PDO)
├── index.php              # Store overview dashboard
├── inventory.php          # Stock management view & add form
├── edit_product.php       # Product modification interface
├── delete_product.php     # Product deletion handler
├── pos.php                # Point of sale checkout interface
├── process_sale.php       # Backend sales & inventory deduction processor
├── history.php            # Complete transaction history log
├── receipt.php            # Itemized printable digital receipt view
├── users.php              # Admin staff management panel
├── edit_user.php          # Staff role & password modification
├── delete_user.php        # Staff deletion handler
├── login.php              # Secure login authentication interface
└── logout.php             # Session destruction script

```

---


## 🛠️ Installation & Setup Guide
To run this project locally on your machine, follow these steps:
1. **Clone or Download the Repository:**
Place your project folder inside your local XAMPP htdocs directory (e.g., xampp/htdocs/convenience-store).
2. **Start Local Server:**
Open XAMPP Control Panel and start Apache and MySQL.
3. **Set Up the Database:**
    * Open phpMyAdmin (`http://localhost/phpmyadmin/`).
    * Create a new database named convenience_store.
    * Run the SQL schema script to generate the tables, default roles, sample products, and the default administrator account.
4. **Default Admin Credentials:**
    * **Username:** `admin`
    * **Password:** `admin123`
5. Launch the Application:
Open your browser and navigate to:
`http://localhost/convenience-store/login.php`

## 📄 License
This project is open-source and developed for local business management. Feel free to adapt and expand it for your store needs!