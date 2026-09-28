#!/bin/sh
# Издава Let's Encrypt сертификат при първо стартиране и го подновява на 12 часа.
# Проверката е HTTP-01 през nginx (порт 80, /.well-known/acme-challenge/).
set -u

: "${DOMAIN:?Променливата DOMAIN не е зададена}"
WEBROOT=/var/www/certbot

if [ -n "${LE_EMAIL:-}" ]; then
    EMAIL_ARGS="--email ${LE_EMAIL} --no-eff-email"
else
    EMAIL_ARGS="--register-unsafely-without-email"
fi

trap 'exit 0' TERM INT

# Изчакване nginx да започне да слуша
sleep 10

while :; do
    if [ -s "/etc/letsencrypt/live/${DOMAIN}/fullchain.pem" ]; then
        certbot renew --webroot -w "$WEBROOT" --quiet
        sleep 43200 & wait $!
    else
        echo "certbot: издаване на сертификат за ${DOMAIN}..."
        # shellcheck disable=SC2086
        if certbot certonly --webroot -w "$WEBROOT" -d "$DOMAIN" \
            $EMAIL_ARGS --agree-tos --non-interactive; then
            echo "certbot: сертификатът е издаден, nginx ще го зареди до минута."
        else
            echo "certbot: неуспешно издаване (портове 80/443 пренасочени ли са към този компютър?). Нов опит след 15 минути."
            sleep 900 & wait $!
        fi
    fi
done
