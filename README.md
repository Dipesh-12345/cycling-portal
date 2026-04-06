# Cit-E Cycling — Competition Portal

A lightweight admin portal and public site for a local cycling competition. Includes an admin dashboard, participant management, and registration flow.

Recommended repo name: `cit-e-cycling-portal` (or `cit-e-cycling`).

## Features
- Admin dashboard (PHP) with participant management
- Public registration form (static HTML)
- Docker-ready: PHP (Apache) + MySQL via docker-compose
- Animated 3D-style dashboard illustration

## Quick start (Docker)
1. Copy example env and edit as needed:

```bash
cp .env.example .env
# edit .env to set secure passwords
```

2. Build and run:

```bash
docker-compose up --build
```

3. Visit: http://localhost:8080

Default admin credentials (from the supplied SQL):

- Username: `admin`
- Password: `password123`

## Notes
- Do NOT commit real credentials. Use `.env` (listed in `.gitignore`) and platform secrets when deploying.
- GitHub Pages only serves static files — to run the full app you need a PHP+MySQL host or Docker (instructions above).
- `dbconnect.php` reads DB settings from environment variables when present.

## Next steps (suggested)
- Create a GitHub repo named `cit-e-cycling-portal` and push the project.
- Remove or secure plaintext passwords in the database; migrate to hashed passwords (`password_hash`).
- Harden Docker/production settings before public deployment.

## License
Add a `LICENSE` file if you want to publish under an open-source license.
