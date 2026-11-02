#!/bin/sh
set -eu
cd "$(dirname "$0")"
umask 077

if [ ! -f .env ]; then
  {
    for site in CLINIC HYDRATION SEARCH RESTO; do
      printf '%s_DB_PASSWORD=%s\n' "$site" "$(openssl rand -hex 24)"
      printf '%s_ADMIN_PASSWORD=%s\n' "$site" "$(openssl rand -hex 24)"
    done
    printf 'AERA_MAILCHIMP_API_KEY=\nAERA_MAILCHIMP_AUDIENCE_ID=\n'
  } > .env
fi
set -a
. ./.env
set +a

docker compose config --quiet
docker compose up -d clinic hydration search resto

wp() { site=$1; shift; docker compose run --rm -T "$site-cli" wp "$@"; }
ready() {
  site=$1
  n=0
  until wp "$site" db check >/dev/null 2>&1; do
    n=$((n + 1))
    [ "$n" -lt 30 ] || { echo "Database did not become ready: $site" >&2; exit 1; }
    sleep 3
  done
}
install() {
  site=$1 url=$2 title=$3 password=$4
  ready "$site"
  if ! wp "$site" core is-installed >/dev/null 2>&1; then
    wp "$site" core install --url="$url" --title="$title" --admin_user=site_admin \
      --admin_password="$password" --admin_email=allanbantilan11@gmail.com --skip-email --quiet
  fi
  wp "$site" rewrite structure '/%postname%/' >/dev/null
}

install clinic https://clinic-wp.duckdns.org 'Health Checkup Clinic' "$CLINIC_ADMIN_PASSWORD"
wp clinic plugin install elementor --activate --quiet
wp clinic plugin activate healthcare-clinic-tools --quiet
wp clinic eval-file /setup/import-clinic.php

install hydration https://product-hydration.duckdns.org 'Hydration Product Launch' "$HYDRATION_ADMIN_PASSWORD"
wp hydration theme activate hydration-launch --quiet
wp hydration plugin install elementor --activate --quiet
wp hydration eval-file wp-content/themes/hydration-launch/install-elementor.php

install search https://home-search-wp.duckdns.org 'Home Search' "$SEARCH_ADMIN_PASSWORD"
wp search plugin install advanced-custom-fields --activate --quiet
wp search plugin activate estate-search --quiet
wp search theme activate property-search --quiet

install resto https://resto-reserve.duckdns.org 'Restaurant Reservations' "$RESTO_ADMIN_PASSWORD"
wp resto theme activate salo --quiet
wp resto option update timezone_string Asia/Manila >/dev/null

for site in clinic search resto; do
  if ! wp "$site" plugin is-installed fluent-smtp >/dev/null 2>&1; then
    wp "$site" plugin install fluent-smtp --quiet
  fi
done

echo 'WordPress setup complete. Admin passwords are in the root-only .env file.'
