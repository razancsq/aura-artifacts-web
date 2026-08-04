# Full Project

The complete implementation of Aura Artifacts: PHP and MySQL backend, dynamic pages, and the admin panel.

## Folders

| Folder | Contents |
|---|---|
| `source/` | The PHP application source code and the database schema (`source/database.sql`) |
| `documentation/` | Final project report, JavaScript and API integration documentation, and the full-system ERD |

## Running locally

Requirements: PHP 8.x with the `mysqli` extension, and MySQL or MariaDB.

1. Create a database and import the schema:
   ```
   mysql -u root -p -e "CREATE DATABASE auradb;"
   mysql -u root -p auradb < source/database.sql
   ```
2. Copy `source/.env.example` to `source/.env` and adjust the database credentials if needed.
3. Serve the `source/` folder, for example with the PHP built-in server:
   ```
   cd source
   php -S localhost:8000
   ```
4. Open `http://localhost:8000/index.php` in a browser.

The default admin account is `admin@auraartifacts.com`; the seeded password hash in `database.sql` is a placeholder and should be reset before any real deployment.
