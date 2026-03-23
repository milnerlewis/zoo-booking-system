# RZA Test Project

A PHP/MySQL web application for **Riget Zoo Adventures** with account management, zoo booking, contact messaging, and user dashboard features.

## Features

- User sign up and login
- Zoo visit booking with live price summary
- Loyalty points and discount support
- Contact form with validation
- User dashboard with booking history

## Tech Stack

- PHP (procedural + class-based utilities)
- MySQL / MariaDB
- Vanilla JavaScript
- CSS

## Project Structure

- `index.php` - Home page
- `booking.php` - Booking type selection
- `zoobooking.php` - Zoo booking form
- `hotelbooking.php` - Hotel booking page (placeholder)
- `contact.php` - Contact form
- `dashboard.php` - Logged-in user dashboard
- `login.php` / `signup.php` / `logout.php` - Authentication pages
- `functions.php` - Main procedural helpers
- `db_config.php` - Database config and connection setup
- `classes/` - Class-based managers
- `js/main.js` - Front-end interactivity and validation
- `styles.css` - Shared styles
- `database_simple.sql` - Database schema and sample data

## Local Setup

1. Place the project in your web root (e.g. `htdocs`).
2. Copy `.env.example` to `.env` and fill in your database credentials.
3. Create/import the database schema:
   - In `database_simple.sql`, replace `your_database_name` with the same value as `DB_NAME` in `.env`.
   - Run `database_simple.sql` in your MySQL server.
4. Start Apache + MySQL.
5. Open `index.php` in your browser.

## Notes

- The current implementation stores passwords as plain text in parts of the procedural flow.
- Before production use, migrate all authentication paths to `password_hash` / `password_verify` consistently.

## License

This project is intended as a personal primary repository unless you add a separate license file.
