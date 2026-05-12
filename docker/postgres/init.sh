#!/bin/sh
set -eu

psql --username "$POSTGRES_USER" --dbname postgres --set dbname="$POSTGRES_DB" <<'SQL'
SELECT format('CREATE DATABASE %I WITH ENCODING %L TEMPLATE template0', :'dbname', 'UTF8')
WHERE NOT EXISTS (
    SELECT 1 FROM pg_database WHERE datname = :'dbname'
)\gexec
SQL
