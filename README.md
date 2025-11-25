

# Miziedi — Premium Outdoor Gear E-Commerce


![Miziedi Banner](public/assets/images/logo.svg)

Miziedi is a robust, full-stack e-commerce platform built with **PHP** and **MongoDB**. It features a premium, mobile-responsive UI inspired by top fashion brands, complete with an Admin Dashboard, Inventory Management, Paystack Payment Integration, and dynamic PDF Invoice generation.

-----

## 🚀 Features

### **User Storefront**

  * **Premium UI/UX:** Mobile-first design with hero sliders, product showcases, and typewriter effects.
  * **Dynamic Product Grid:** Filter by category, search by keyword, and view stock status (In Stock / Sold Out).
  * **Smart Cart:** AJAX-based cart with instant quantity updates (+/-) and size selection.
  * **Checkout:** Integrated **Paystack** payment gateway for secure transactions.
  * **Order Tracking:** Visual timeline (Paid → Confirmed → Shipped → Delivered) and real-time status updates.
  * **Invoicing:** Auto-generated, downloadable PDF invoices with dynamic "Bill To" addresses and CEO signature.

### **Admin Dashboard**

  * **Product Management:** Add, Edit, and Delete products. Upload images, set prices, and manage stock levels.
  * **Inventory Control:** Real-time view of stock levels with visual indicators for low stock.
  * **Order Management:** View all orders, update statuses (e.g., "Shipped"), and add internal notes.
  * **Category Management:** Create and edit product categories dynamically.
  * **System Settings:** Configure Global Delivery Fees, Tax Labels, Invoice Taglines, and upload the CEO Signature directly from the dashboard.

-----

## 🛠 Tech Stack

  * **Backend:** PHP 8.2+ (MVC Architecture)
  * **Database:** MongoDB (Atlas or Local)
  * **Frontend:** HTML5, CSS3 (Custom Premium Styles), JavaScript (Vanilla)
  * **Payment:** Paystack API
  * **PDF Generation:** html2pdf.js / html2canvas
  * **Server:** Apache (via `.htaccess` routing)

-----

## ⚙️ Installation (Local Development)

### 1\. Prerequisites

  * PHP 8.1 or higher
  * Composer
  * MongoDB PHP Extension (`ext-mongodb`)

### 2\. Setup

Clone the repository and install dependencies:

```bash
git clone https://github.com/yourusername/miziedi.git
cd miziedi
composer install
```

### 3\. Environment Configuration

Create a `.env` file in the root directory (copy from `.env.example`):

```ini
APP_ENV=local
APP_URL=http://localhost:8000

# MongoDB Connection
MONGODB_URI=mongodb+srv://<user>:<password>@cluster.mongodb.net/?retryWrites=true&w=majority
MONGODB_DB=miziedi_ecommerce

# Paystack Credentials (https://dashboard.paystack.com)
PAYSTACK_PUBLIC_KEY=
PAYSTACK_SECRET_KEY=

# Admin Fallback Login
ADMIN_EMAIL=admin@miziedi.com
```

### 4\. Run the Application

Start the built-in PHP server from the `public` folder:

```bash
cd public
php -S localhost:8000
```

Visit **http://localhost:8000** in your browser.

-----

## 🌍 Deployment (cPanel / Shared Hosting)

Since shared hosting typically uses Apache, this project includes specific routing configurations.

### 1\. File Structure Preparation

Ensure your project has the following `.htaccess` files to route traffic correctly:

  * **Root (`/.htaccess`):** Redirects all traffic to the `public/` folder.
  * **Public (`/public/.htaccess`):** Routes all requests to `index.php`.

### 2\. Upload Steps

1.  Zip the entire project (**excluding** `.env` and `.git`).
2.  Upload the zip to the `public_html` folder on your cPanel.
3.  Extract the files directly into `public_html` (do not nest them in a subfolder).

### 3\. Server Configuration

1.  Create a new `.env` file in `public_html` and paste your **Live** Paystack keys and MongoDB connection string.
2.  Go to **Select PHP Version** in cPanel and enable the `mongodb` extension.
3.  Ensure your domain points to `public_html`.

-----

## 🔐 Admin Access

To access the admin panel, navigate to `/admin/login`.

  * **Email:** `admin@miziedi.com` (or as set in .env)
  * **Password:** `admin123` (Hardcoded in AuthController for initial setup, recommended to change in production).

-----

## 📂 Project Structure

```text
miziedi/
├── config/             # Database & Paystack config
├── public/             # Publicly accessible files
│   ├── assets/         # CSS, JS, Images, Uploads
│   ├── index.php       # Entry point (Router)
│   └── .htaccess       # Apache rules
├── routes/             # Route definitions (Web & API)
├── src/
│   ├── Controllers/    # Business logic (Order, Product, Admin)
│   ├── Models/         # Database interactions
│   └── Utils/          # Helper functions
├── views/              # HTML Templates (Layouts, Pages, Admin)
├── .env.example        # Template for environment variables
└── composer.json       # Dependencies
```

-----

## 📄 License

This project is proprietary software built for **Miziedi**. Unauthorized copying or distribution is strictly prohibited.