#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$(readlink -f "$0")")"

# tag datang dari SSH (kunci terkunci) atau argumen saat dites manual
TAG="${SSH_ORIGINAL_COMMAND:-${1:-}}"
[[ "$TAG" =~ ^v[0-9]+(\.[0-9]+){0,2}$ ]] || { echo "Tag tidak valid: '$TAG'"; exit 1; }

DC="docker compose -f docker-compose.prod.yml"
PREV="$(grep -E '^TAG=' .env | head -1 | cut -d= -f2)"
echo "==> Deploy $PREV -> $TAG"

set_tag() { sed -i "s/^TAG=.*/TAG=$1/" .env; }

healthy() {
  for _ in $(seq 1 30); do
    if curl -fsS -o /dev/null http://127.0.0.1:8081/api/v1/projects \
       && curl -fsS -o /dev/null http://127.0.0.1:3010; then
      return 0
    fi
    sleep 2
  done
  return 1
}

# backup sebelum migration (dilewati kalau skrip backup belum ada)
if [ -x docker/backup.sh ]; then
  echo "==> Backup"
  ./docker/backup.sh
fi

deploy() {
  set_tag "$TAG"                              || return 1
  $DC pull                                    || return 1
  $DC up -d --remove-orphans                  || return 1
  sleep 3
  $DC exec -T app php artisan migrate --force || return 1
  healthy                                     || return 1
}

if ! deploy; then
  echo "!! GAGAL. Kembali ke $PREV"
  set_tag "$PREV"
  $DC up -d || true
  exit 1
fi

docker image prune -f >/dev/null
$DC ps
echo "==> Selesai: $TAG"