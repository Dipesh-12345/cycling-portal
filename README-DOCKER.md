# Run the Cycling app with Docker

This project can be run locally using Docker and Docker Compose (PHP + Apache + MySQL).

Prerequisites
- Docker and Docker Compose installed

Quick start

1. Copy the example environment file and edit if needed:

```bash
cp .env.example .env
# (edit .env to change passwords if required)
```

2. Build and start the services:

```bash
docker-compose up --build
```

3. Open the app in your browser:

http://localhost:8080

Notes
- The Compose file mounts the project into the container; you can edit files locally and refresh the browser.
- On first run MySQL will initialise the `cycling` database from `cycling.sql`.
- `dbconnect.php` reads DB connection info from environment variables when present.

Deploying
- For production deploys consider building an image without mounting source, secure credentials (do not use root/password), and use managed database services or volume backups.
