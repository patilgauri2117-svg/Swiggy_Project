# Swiggy – Food Delivery Website (Core PHP + MySQL)

A simple food ordering website made for a college project.
Built with **Core PHP, MySQL (PDO), HTML, CSS, Bootstrap 5 and JavaScript**. No frameworks.

---

## 1. Default Login Details

| Who      | Login page                                   | Email                   | Password   |
|----------|----------------------------------------------|-------------------------|------------|
| Admin    | http://localhost:8080/swiggy/admin/login.php | admin@foodexpress.com   | admin123   |
| Customer | http://localhost:8080/swiggy/login.php       | rahul@example.com       | rahul123   |

You can also register a new customer from the Register page.

---

## 2. How to Run in XAMPP (step by step)

1. **Install XAMPP** from https://www.apachefriends.org (PHP 7.4 or newer).
2. **Copy the project folder.** Copy the whole `swiggy` folder into:
   - Windows: `C:\xampp\htdocs\swiggy`
   - macOS: `/Applications/XAMPP/htdocs/swiggy`
3. **Start the servers.** Open the *XAMPP Control Panel* and click **Start** next to **Apache** and **MySQL**.
4. **Import the database.**
   1. Open http://localhost/phpmyadmin
   2. Click the **Import** tab at the top.
   3. Click **Choose File** and select `swiggy/database/food_delivery.sql`.
   4. Click **Import** (or **Go**) at the bottom.
   The file creates the `food_delivery` database, all 6 tables and the sample data by itself.
5. **Open the website:** http://localhost/swiggy
6. **Open the admin panel:** http://localhost/swiggy/admin/login.php

> If your MySQL `root` user has a password, open `config/database.php` and put it in `$password`.

---

## 3. Folder Structure

```
swiggy/
├── index.php            Home page (hero, search, categories, restaurants, popular dishes)
├── restaurants.php      All restaurants + search results (?search=...)
├── menu.php             Food menu (?restaurant=ID or ?category=ID)
├── cart.php             Cart: add, + / −, remove, totals
├── checkout.php         Delivery details, Cash on Delivery, saves order, confirmation
├── login.php            Customer login
├── register.php         Customer registration
├── logout.php           Customer logout
├── my-orders.php        Customer order history with status
├── config/
│   └── database.php     PDO connection + session_start()
├── includes/
│   ├── header.php       <head> section, CSS links, includes navbar
│   ├── navbar.php       Top menu (Home, Restaurants, Login, Register, Cart)
│   ├── footer.php       About Us, Contact, Copyright, JS files
│   └── functions.php    Helper functions (escape output, flash messages, cards)
├── admin/
│   ├── login.php        Admin login
│   ├── dashboard.php    Totals: food items, customers, orders, sales
│   ├── food-items.php   Add / edit / delete food items (with image upload)
│   ├── orders.php       View orders and update order status
│   ├── logout.php
│   └── includes/        auth.php (protects pages), header.php, footer.php
├── assets/
│   ├── css/style.css    Orange-and-white theme
│   ├── js/script.js     Form validation, confirm boxes, button effects
│   ├── images/          Category pictures + uploads/ folder for admin images
│   └── vendor/          Bootstrap 5 and Bootstrap Icons (works without internet)
└── database/
    └── food_delivery.sql
```

---

## 4. Database Tables

| Table             | What it stores                                                      |
|-------------------|---------------------------------------------------------------------|
| `users`           | Customers and admin: name, email, phone, address, hashed password, role |
| `restaurants`     | Name, image, cuisine, rating, delivery time, location               |
| `food_categories` | Pizza, Burger, Biryani, Chinese, South Indian + image               |
| `food_items`      | Name, description, price, image, veg/non-veg, restaurant, category  |
| `orders`          | Customer, phone, address, total amount, payment method, status      |
| `order_items`     | Each food item in an order with quantity and price                  |

Sample data: 2 users, 6 restaurants, 5 categories, 24 food items and 1 sample order.

---

## 5. How the Main Features Work (for your presentation)

- **Database connection** – `config/database.php` creates one `PDO` object. Every query uses
  **prepared statements** (`$pdo->prepare()` + `execute()`), which prevents SQL injection.
- **Registration** – `register.php` validates name, email, 10-digit mobile, address and password,
  then saves the password with `password_hash()` (never plain text).
- **Login** – `login.php` finds the user by email and checks the password with `password_verify()`.
  The user id and name are saved in `$_SESSION`.
- **Cart** – stored in the session as `$_SESSION['cart'][food_id] = quantity`.
  Add, increase, decrease, remove and clear are simple POST forms handled at the top of `cart.php`.
- **Checkout** – only for logged-in users. Prices are **read again from the database**
  and the total (items + ₹40 delivery) is calculated on the server, so nobody can change prices
  in the browser. The order and its items are saved inside a **transaction**.
  Payment method is Cash on Delivery.
- **Admin protection** – every admin page starts with `require 'includes/auth.php'`, which sends
  anyone without `$_SESSION['admin_id']` back to the admin login page.
- **Order status** – Pending → Confirmed → Preparing → Out for Delivery → Delivered (or Cancelled).
  The admin changes it in `admin/orders.php`; the customer sees it in **My Orders**.
- **Security basics** – `htmlspecialchars()` (the `e()` helper) on all output to stop XSS,
  `session_regenerate_id()` after login, upload checks (type and 2 MB size) for food images.

---

## 6. Demo Flow (suggested for viva / presentation)

1. Open the home page → search "biryani" → click a category.
2. Restaurants → **View Menu** → **Add** a few dishes.
3. Cart → change quantity with − / + → remove one item → **Proceed to Checkout**.
4. Log in as `rahul@example.com` / `rahul123` (or register a new account).
5. Check the address → **Place Order** → see the confirmation → **My Orders**.
6. Open the admin panel → Dashboard counts → change the new order's status to *Out for Delivery*.
7. Refresh **My Orders** as the customer to show the updated status.
8. In **Food Items**, add a new dish with an image, edit its price, then delete it.

---

## 7. Images

- Category pictures are stored in `assets/images/` and always work.
- Restaurant and food photos load from Unsplash, so they need internet.
  When there is no internet, each card automatically shows a colourful bundled picture instead,
  so the website still looks complete offline.
- Food photos uploaded from the admin panel are saved in `assets/images/uploads/`.

---

## 8. Troubleshooting

| Problem | Fix |
|---------|-----|
| "Database connection failed" | Start MySQL in XAMPP and import `database/food_delivery.sql`. |
| "Access denied for user root" | Put your MySQL password in `config/database.php`. |
| Page not found (404) | Folder must be `htdocs/swiggy` and Apache must be running. |
| Apache will not start (port 80 busy) | Close Skype/IIS, or change Apache's port to 8080 and open http://localhost:8080/swiggy |
| Image upload fails | Make sure `assets/images/uploads/` exists and is writable. |
| Want to reset all data | Import `food_delivery.sql` again (it drops and recreates the database). |
