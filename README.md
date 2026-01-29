```markdown
# 💊 MediVault - Advanced Pharmacy Management System

![PHP](https://img.shields.io/badge/Backend-Core%20PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/Database-MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![TailwindCSS](https://img.shields.io/badge/Frontend-Tailwind%20CSS-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)
![JavaScript](https://img.shields.io/badge/Scripting-JavaScript%20(ES6)-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)

**MediVault** is a secure and efficient web-based Pharmacy Management System developed using **Raw PHP** and **MySQL**. It bridges the gap between customers and pharmacy administration by offering features like prescription uploads, batch-wise inventory tracking, and automated invoice generation.

---

## 🚀 Key Features

### 👤 User Module (Patient)
- **🛍️ Smart Cart System:** Real-time stock validation prevents ordering out-of-stock items.
- **📄 Prescription Upload:** Mandatory prescription upload for restricted medicines before placing orders.
- **❌ Order Cancellation:** Users can cancel orders only when the status is *'Pending'*.
- **🖨️ Automated Invoice:** Generate professional, print-friendly invoices after order confirmation.
- **🔒 Secure Profile:** Manage personal details and change passwords securely (Hashed).

### 🛡️ Admin Module
- **📊 Interactive Dashboard:** Overview of Total Sales, Pending Orders, and Active Users.
- **📦 Advanced Inventory:** Manage medicines with **Batch Tracking** (FIFO/FEFO logic based on Expiry Date).
- **✅ Order Processing:** Update status (Pending → Confirmed → Delivered) and verify uploaded prescriptions.
- **🚫 Security:** Admin pages are protected against unauthorized access.

---

## 🛠️ Technical Highlights

- **Backend:** Built with **Core PHP** (No Framework) to demonstrate strong fundamental understanding.
- **Database:** **MySQL** with complex relationships (Foreign Keys, Joins).
- **Security:**
  - **PDO Prepared Statements** to prevent SQL Injection.
  - **XSS Protection** using input sanitization.
  - **Password Hashing** using `password_hash()` (Bcrypt).
  - **Transactions (ACID):** Ensures data integrity during order placement and stock deduction.
- **Frontend:** Responsive UI designed with **Tailwind CSS**.

```

---

```markdown
---

## ⚙️ Installation Guide

Follow these steps to run the project locally:

### 1️⃣ Prerequisites
- **XAMPP** or **WAMP** Server installed.
- A Code Editor (VS Code recommended).
- Git installed (optional).

### 2️⃣ Clone the Repository
```bash
git clone [https://github.com/YourUsername/medivault.git](https://github.com/YourUsername/medivault.git)

```

*(Or download the ZIP file and extract it).*

### 3️⃣ Setup Directory

* Move the project folder to your server's root directory:
* **XAMPP:** `C:/xampp/htdocs/medivault`
* **WAMP:** `C:/wamp64/www/medivault`



### 4️⃣ Database Configuration

1. Run mySQL server. Then open mysql workbench.
2. Then connect it to the mySQL server.
3. Import the database.

Or,

1. Open **phpMyAdmin** (`http://localhost/phpmyadmin`).
2. Create a new database named **`medivault`**.
3. Import the `medivault.sql` file provided in the project folder.

### 5️⃣ Connect to Database

Open `config.php` and verify the settings:

```php
$host = 'localhost';
$db   = 'medivault';
$user = 'root';
$pass = ''; // Default for XAMPP is empty

```

### 6️⃣ Run the Project

Open your browser and visit:
`http://localhost/medivault`

---

## 📸 Screenshots

| User Dashboard |
| --- |
| <img width="1916" height="891" alt="image" src="https://github.com/user-attachments/assets/d971c350-8c21-4f3c-b7f9-076792d466b9" /> | 
| Admin Panel |
| --- |
| <img width="1918" height="894" alt="image" src="https://github.com/user-attachments/assets/e07f40a5-810c-46d4-969d-408e191ba37b" /> |

| Invoice View |
| --- |
| <img width="1896" height="901" alt="image" src="https://github.com/user-attachments/assets/ca2d6bed-0fad-4456-b476-64cce5d5d9a2" /> | 
| Prescription Upload |
| --- |
| <img width="1919" height="875" alt="image" src="https://github.com/user-attachments/assets/1103b41e-f8dc-4f55-ad4c-87b933e304db" /> |

---

## 🧪 Login Credentials (Demo)

| Role | Email | Password |
| --- | --- | --- |
| **Admin** | `admin@medivault.com` | `admin123` (Change in DB) |
| **User** | `badhon'4863@gmail.com` | `12345` |

---

## 🤝 Contribution

This is an educational project. Suggestions and improvements are welcome!

1. Fork the Project
2. Create your Feature Branch (`git checkout -b feature/AmazingFeature`)
3. Commit your Changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the Branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

---

## 👨‍💻 Developer

Developed with ❤️ by **Badhon Saha** 📧 Email: badhon4863@gmail.com

```

```
