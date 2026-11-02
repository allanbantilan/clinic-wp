#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")"
set -a
source ./.env
set +a
umask 077
target=/root/wordpress-backups
mkdir -p "$target"
stamp=$(date -u +%Y%m%d-%H%M%S)

for site in clinic hydration search resto; do
  key=${site^^}_DB_PASSWORD
  db="$target/$site-$stamp.sql.gz"
  uploads="$target/$site-$stamp-uploads.tar.gz"
  docker compose exec -T "$site-db" mariadb-dump --single-transaction -uwordpress -p"${!key}" wordpress | gzip > "$db.tmp"
  gzip -t "$db.tmp"
  mv "$db.tmp" "$db"
  docker compose exec -T "$site" sh -c 'mkdir -p /var/www/html/wp-content/uploads; tar -czf - -C /var/www/html/wp-content uploads' > "$uploads.tmp"
  tar -tzf "$uploads.tmp" >/dev/null
  mv "$uploads.tmp" "$uploads"
done

find "$target" -maxdepth 1 -type f -mtime +7 -delete
rclone copy "$target" wp-b2-crypt:
rclone cryptcheck --one-way "$target" wp-b2-crypt:
rclone delete wp-b2-crypt: --min-age 14d --b2-hard-delete
