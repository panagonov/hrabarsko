#!/bin/sh
# Избира сертификат за nginx: Let's Encrypt, ако вече е издаден,
# иначе временен самоподписан. На всяка минута проверява за нов или
# подновен сертификат и презарежда nginx при промяна.
set -e

: "${DOMAIN:?Променливата DOMAIN не е зададена}"
LE_DIR="/etc/letsencrypt/live/${DOMAIN}"
SELF_DIR="/etc/nginx/ssl/selfsigned"
ACTIVE="/etc/nginx/ssl/active.conf"
mkdir -p /etc/nginx/ssl

select_cert() {
    if [ -s "$LE_DIR/fullchain.pem" ] && [ -s "$LE_DIR/privkey.pem" ]; then
        cert="$LE_DIR/fullchain.pem"
        key="$LE_DIR/privkey.pem"
    else
        if [ ! -s "$SELF_DIR/fullchain.pem" ]; then
            mkdir -p "$SELF_DIR"
            openssl req -x509 -nodes -newkey rsa:2048 -days 30 \
                -subj "/CN=${DOMAIN}" \
                -keyout "$SELF_DIR/privkey.pem" -out "$SELF_DIR/fullchain.pem" >/dev/null 2>&1
        fi
        cert="$SELF_DIR/fullchain.pem"
        key="$SELF_DIR/privkey.pem"
    fi
    printf 'ssl_certificate %s;\nssl_certificate_key %s;\n' "$cert" "$key" > "$ACTIVE"
    # Отпечатък: активният път + съдържанието на сертификата (сменя се при подновяване)
    { cat "$ACTIVE"; cksum "$cert"; } | cksum
}

current=$(select_cert)
echo "40-ssl-select: $(head -n1 "$ACTIVE")"

(
    while sleep 60; do
        new=$(select_cert)
        if [ "$new" != "$current" ]; then
            echo "40-ssl-select: нов сертификат, презареждане на nginx"
            nginx -s reload && current="$new"
        fi
    done
) &
