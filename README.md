# Cit-E Cycling — Competition Portal
A lightweight admin portal and public site for a local cycling competition. Includes an admin dashboard, participant management, and registration flow.

The project includes:
- Admin participant management dashboard
- Public registration form
- MySQL-backed data storage
- Docker Compose setup for easy local development

## ⚡ Features
- Participant CRUD operations via `admin_menu.php`, `view_participants_edit_delete.php`, and `register.php`
- Login/logout flow with `login.php` and `logout.php`
- Search participants using `search_form.php` and `search_result.php`
- Database initialization from `cycling.sql`
- Docker-ready PHP Apache + MySQL environment

## 🚀 Quick start with Docker
1. Copy the example environment file:

```bash
cp .env.example .env
```

2. Edit `.env` if you want to change database credentials.

3. Build and start the app:

```bash
docker-compose up --build
```

4. Open in your browser:

```text
http://localhost:8080
```

### Default admin credentials
The sample SQL data includes a default admin login:
- Username: admin
- Password: password123

> For security, update the credentials before using this in production.

## 🧩 Project files
- `index.html` — public landing page
- `register_form.html` / `register.php` — participant registration
- `login.php` — admin login page
- `admin_menu.php` — admin dashboard entry point
- `view_participants_edit_delete.php` — list participants and manage records
- `edit_participant_form.php` / `edit_participant.php` — edit participant details
- `delete.php` — delete participant records
- `search_form.php` / `search_result.php` — search workflow
- `dbconnect.php` — database connection helper
- `style.css` — site styling
- `cycling.sql` — initial database schema and seed data

## ⚙️ Local setup without Docker
If you prefer to run the app directly on a PHP/Apache server:
1. Copy the project into your web server document root.
2. Create a MySQL database named `cycling`.
3. Import `cycling.sql` into the database.
4. Configure database settings in `dbconnect.php` or use `.env` variables.
5. Open `http://localhost/<project-folder>` in your browser.

## 📝 Notes
- `dbconnect.php` checks environment variables when available, making the app compatible with Docker and local installations.
- The app is designed for a small local competition and is not production-hardened.
- Use secure passwords, disable sample credentials, and add HTTPS before deploying publicly.

## 📚 Useful commands
```bash
cp .env.example .env

docker-compose up --build
```

## 🙋‍♂️ Want to extend this project?
- Add user registration and admin roles
- Add input validation and session security
- Replace static HTML with a frontend framework
- Use prepared statements or ORM for safer database access





