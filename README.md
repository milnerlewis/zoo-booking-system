# Zoo Booking System

![PHP](https://img.shields.io/badge/PHP-7.4-blue)
![MySQL](https://img.shields.io/badge/MySQL-8.0-blue)
![JavaScript](https://img.shields.io/badge/JavaScript-ES6-yellow)
![CSS](https://img.shields.io/badge/CSS-3.0-blue)

A full stack PHP/MySQL web application for **Riget Zoo Adventures**, featuring user authentication, zoo visit booking, loyalty rewards, and a user dashboard.  
This project demonstrates front-end and back-end skills, database design, and full-stack integration.

---

## Features

- User sign up, login, and logout
- Zoo visit booking with live price summary
- Loyalty points and discount system
- Contact form with validation
- User dashboard displaying booking history

---

## Tech Stack

- **Frontend:** HTML, CSS, JavaScript  
- **Backend:** PHP (procedural + class-based utilities)  
- **Database:** MySQL / MariaDB  

---

## Project Structure

- `index.php` – Home page  
- `booking.php` – Booking type selection  
- `zoobooking.php` – Zoo booking form  
- `hotelbooking.php` – Hotel booking page (placeholder)  
- `contact.php` – Contact form  
- `dashboard.php` – Logged-in user dashboard  
- `login.php` / `signup.php` / `logout.php` – Authentication pages  
- `functions.php` – Procedural helper functions  
- `db_config.php` – Database connection and configuration  
- `classes/` – Class-based managers  
- `js/main.js` – Frontend interactivity and validation  
- `styles.css` – Shared styles  
- `database_simple.sql` – Database schema and sample data

---

## Screenshots

**Login / Sign Up:**  
#### Need to add screenshot...

**Booking Page:**  
#### Need to add screenshot...

**User Dashboard / Loyalty Points:**  
#### Need to add screenshot...

---

## Local Setup

1. Ensure you have MySQL/MariaDB running
2. Import the `database_simple.sql` file if you wish to use a preset database structure
   It will give you instructions on the creation of the database and it's tables (plus some sample data)
3. Update your database credentials in the `db_config.php` file or your own `.env` file to match your local setup
4. Start your local web server (I used Apache + MySQL)
5. Open `index.php` in your browser to test the application and ensure everything works as intended
