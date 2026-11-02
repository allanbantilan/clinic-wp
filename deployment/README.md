# Production deployment for the four WordPress sites

This directory is the shared deployment source for Clinic, Hydration Product Launch, Home Search, and Restaurant Reservations on the Ubuntu VPS. It runs each WordPress site with its own MariaDB database and persistent volumes. The existing Caddy container provides HTTPS through the external `apps_default` Docker network.

## Live sites

| Site | URL | Source directory |
| --- | --- | --- |
| Clinic | https://clinic-wp.duckdns.org | `src/clinic` |
| Hydration | https://product-hydration.duckdns.org | `src/hydration` |
| Home Search | https://home-search-wp.duckdns.org | `src/search` |
| Restaurant | https://resto-reserve.duckdns.org | `src/resto` |

On the VPS this directory is `/root/deploy/wordpress-sites`. Clone the four public repositories into the source directories before running `sh setup.sh`:

```sh
mkdir -p src
git clone https://github.com/allanbantilan/clinic-wp.git src/clinic
git clone https://github.com/allanbantilan/product-hydration-wp.git src/hydration
git clone https://github.com/allanbantilan/home-search-web.git src/search
git clone https://github.com/allanbantilan/resto-reserve-web.git src/resto
sh setup.sh
```

`setup.sh` creates a root-only `.env` with random database and WordPress administrator passwords on first run. It installs WordPress, activates each site's theme and required plugins, creates the clinic and hydration Elementor homepages, and installs FluentSMTP for the sites that send enquiries. It preserves existing database volumes when rerun. The three FluentSMTP connections need a verified Brevo sender and API key; `configure-mail.php` applies a connection when passed `BREVO_API_KEY`, `BREVO_SENDER_EMAIL`, and `BREVO_SENDER_NAME` to the corresponding WP-CLI service. Keep provider keys outside Git.

Add [Caddyfile.sites](Caddyfile.sites) to the existing Caddyfile, validate it, then reload Caddy. The WordPress containers publish no host ports; only Caddy exposes HTTP and HTTPS.

## Backups and recovery

`backup.sh` dumps each database and archives its uploads, validates the archives, then copies them through the `wp-b2-crypt:` rclone remote to the private Backblaze B2 bucket. Local copies remain for seven days; encrypted offsite copies remain for fourteen days. Install [wordpress-sites-backup.cron](wordpress-sites-backup.cron) under `/etc/cron.d/` to run it daily.

The rclone configuration contains the B2 credentials and the encryption keys. Keep a separate secure copy of `/root/.config/rclone/rclone.conf`; without its encryption keys, the offsite backups cannot be restored. To test a restore, copy `wp-b2-crypt:` to an empty directory with rclone and verify its SQL and uploads archives before importing any data. Never use `docker compose down -v` on this deployment: that removes the site databases and WordPress files.
