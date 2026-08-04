# Aura Artifacts

Aura Artifacts is an e-commerce platform for handcrafted, customizable gemstone jewelry. It covers product browsing, a custom bracelet builder, shopping cart and checkout, user accounts, and an admin panel for catalog, order, and stock management.

Full overview: [Project Report](report/Aura-Artifacts-Project-Report.pdf)

## Repository structure

| Folder | Description |
|---|---|
| [`01-frontend-html-css-erd/`](01-frontend-html-css-erd) | Static HTML and CSS prototype covering every public, customer, and admin page, plus the database ERD |
| [`02-full-project/`](02-full-project) | Complete implementation: PHP and MySQL backend, dynamic pages, and the admin panel |
| [`report/`](report) | Project report (PDF) |

## Features

| Area | Details |
|---|---|
| Storefront | Home, collections with category filtering, product detail pages, live search, gemstone meanings quiz, size guide |
| Custom bracelet builder | Select gemstones and charms across 13 charm categories with real-time price calculation and preview |
| Accounts | Registration, login with a 7-day remember-me option, profile management, order history, wishlist |
| Shopping | Cart, checkout, order confirmation, newsletter subscription, contact form with a map |
| Admin panel | Dashboard with sales analytics, product management, order management, customer management, stock management, message and subscriber management, site settings |
| Other | Dark mode, live currency conversion, accessibility page |

## Technology

| Layer | Technology |
|---|---|
| Frontend | HTML5, CSS3, vanilla JavaScript |
| Backend | PHP with mysqli and prepared statements |
| Database | MySQL / MariaDB |
| Authentication | Session-based, bcrypt password hashing, remember-me tokens |
| External services | [ExchangeRate-API](https://open.er-api.com) for currency conversion, Google Maps Embed for the contact page |

## Database

The schema (`02-full-project/source/database.sql`) defines 16 tables: `admin`, `category`, `customer`, `product`, `gemstone`, `charm`, `braceletpreview`, `cart`, `cart_item`, `order`, `order_item`, `wishlist`, `contact_message`, `newsletter`, `password_reset_request`, `notification_read`.

The full entity-relationship diagram is in [`02-full-project/documentation/ERD-Diagram-Full-System.pdf`](02-full-project/documentation/ERD-Diagram-Full-System.pdf).

## Documentation

| Document | Location |
|---|---|
| Project report (overview) | [`report/Aura-Artifacts-Project-Report.pdf`](report/Aura-Artifacts-Project-Report.pdf) |
| Frontend prototype report | [`01-frontend-html-css-erd/documentation/HTML-CSS-Submission-Report.pdf`](01-frontend-html-css-erd/documentation/HTML-CSS-Submission-Report.pdf) |
| Frontend ERD and explanation | [`01-frontend-html-css-erd/documentation/`](01-frontend-html-css-erd/documentation) |
| Full project report | [`02-full-project/documentation/Aura-Artifacts-Final-Project-Report.pdf`](02-full-project/documentation/Aura-Artifacts-Final-Project-Report.pdf) |
| JavaScript and API integration | [`02-full-project/documentation/JavaScript-and-API-Documentation.pdf`](02-full-project/documentation/JavaScript-and-API-Documentation.pdf) |
| Full-system ERD | [`02-full-project/documentation/ERD-Diagram-Full-System.pdf`](02-full-project/documentation/ERD-Diagram-Full-System.pdf) |

## Running the full project locally

Requirements: PHP 8.x with the `mysqli` extension, and MySQL or MariaDB.

```
mysql -u root -p -e "CREATE DATABASE auradb;"
mysql -u root -p auradb < 02-full-project/source/database.sql
cp 02-full-project/source/.env.example 02-full-project/source/.env
cd 02-full-project/source
php -S localhost:8000
```

Then open `http://localhost:8000/index.php`. See [`02-full-project/README.md`](02-full-project/README.md) for details.

## Viewing the frontend prototype

The files in `01-frontend-html-css-erd/` are static and can be opened directly in a browser, starting from `index.html`.
