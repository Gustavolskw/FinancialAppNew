#!/bin/sh
set -eu

CERT_FILE="/etc/nginx/certs/fullchain.pem"
KEY_FILE="/etc/nginx/certs/privkey.pem"
CERT_SUBJECT="${NGINX_SELF_SIGNED_CERT_SUBJECT:-/CN=localhost}"

if [ -f "$CERT_FILE" ] && [ -f "$KEY_FILE" ]; then
  exit 0
fi

mkdir -p /etc/nginx/certs

openssl req -x509 -nodes -newkey rsa:2048 \
  -keyout "$KEY_FILE" \
  -out "$CERT_FILE" \
  -days 365 \
  -subj "$CERT_SUBJECT"
