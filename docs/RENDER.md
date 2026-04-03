# Render Deployment

This project is ready to deploy on Render with the files below:

- `Dockerfile`
- `render.yaml`
- `docker/start-container.sh`
- `docker/nginx/default.conf.template`

## What this setup does

- Builds Composer dependencies
- Builds Vite assets
- Serves Laravel through Nginx + PHP-FPM
- Runs `php artisan migrate --force` on container start
- Creates the `public/storage` symlink if needed

## Recommended first deploy

1. Push this repository to GitHub.
2. In Render, create a new Blueprint service from the repo.
3. When prompted, provide:
   - `APP_KEY`
   - `APP_URL`
4. After the service is created, open the app URL.

## Required env values

Generate your Laravel app key locally:

```bash
php artisan key:generate --show
```

Use that output as `APP_KEY` in Render.

Set `APP_URL` to your Render domain, for example:

```text
https://sprout-tracker.onrender.com
```

## Database

The included `render.yaml` is configured for Render PostgreSQL.

If you want to keep using MySQL instead:

- remove the `databases` block from `render.yaml`
- change `DB_CONNECTION` to `mysql`
- add your MySQL connection env vars in Render

## Uploaded files

This app stores receipt and profile photos on the local `public` disk.

Important:
- On Render, the filesystem is ephemeral by default.
- Without a persistent disk or S3-compatible storage, uploaded files can disappear on redeploy or restart.

You have two good options:

1. Attach a persistent disk to `/var/www/html/storage/app/public`
2. Move uploads to S3-compatible storage later

The blueprint includes a commented disk example you can enable on paid plans.
