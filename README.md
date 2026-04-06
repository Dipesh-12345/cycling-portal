# Cit-E Cycling — Competition Portal

A lightweight admin portal and public site for a local cycling competition. Includes an admin dashboard, participant management, and registration flow.

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

- Username: admin
- Password: password123





