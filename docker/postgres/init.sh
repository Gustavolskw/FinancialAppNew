#!/bin/sh
set -eu

: "${POSTGRES_APP_USER:?POSTGRES_APP_USER não definido}"
: "${POSTGRES_APP_PASSWORD:?POSTGRES_APP_PASSWORD não definido}"

psql \
  --username "$POSTGRES_USER" \
  --dbname postgres \
  --set dbname="$POSTGRES_DB" \
  --set app_user="$POSTGRES_APP_USER" \
  --set app_password="$POSTGRES_APP_PASSWORD" <<'SQL'
SELECT format('CREATE ROLE %I LOGIN PASSWORD %L', :'app_user', :'app_password')
WHERE NOT EXISTS (
    SELECT 1 FROM pg_roles WHERE rolname = :'app_user'
)\gexec

SELECT format('ALTER ROLE %I WITH LOGIN PASSWORD %L', :'app_user', :'app_password')\gexec

SELECT format(
    'CREATE DATABASE %I WITH ENCODING %L TEMPLATE template0 OWNER %I',
    :'dbname',
    'UTF8',
    :'app_user'
)
WHERE NOT EXISTS (
    SELECT 1 FROM pg_database WHERE datname = :'dbname'
)\gexec

SELECT format('ALTER DATABASE %I OWNER TO %I', :'dbname', :'app_user')\gexec
SELECT format('GRANT ALL PRIVILEGES ON DATABASE %I TO %I', :'dbname', :'app_user')\gexec
SQL

psql \
  --username "$POSTGRES_USER" \
  --dbname "$POSTGRES_DB" \
  --set app_user="$POSTGRES_APP_USER" <<'SQL'
SELECT format('ALTER SCHEMA public OWNER TO %I', :'app_user')\gexec
SELECT format('GRANT USAGE, CREATE ON SCHEMA public TO %I', :'app_user')\gexec

SELECT format('ALTER TABLE IF EXISTS %I.%I OWNER TO %I', schemaname, tablename, :'app_user')
FROM pg_tables
WHERE schemaname = 'public'\gexec

SELECT format('ALTER SEQUENCE IF EXISTS %I.%I OWNER TO %I', sequence_schema, sequence_name, :'app_user')
FROM information_schema.sequences
WHERE sequence_schema = 'public'\gexec

SELECT format('GRANT SELECT, INSERT, UPDATE, DELETE ON ALL TABLES IN SCHEMA public TO %I', :'app_user')\gexec
SELECT format('GRANT USAGE, SELECT, UPDATE ON ALL SEQUENCES IN SCHEMA public TO %I', :'app_user')\gexec
SELECT format(
    'ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT SELECT, INSERT, UPDATE, DELETE ON TABLES TO %I',
    :'app_user'
)\gexec
SELECT format(
    'ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT USAGE, SELECT, UPDATE ON SEQUENCES TO %I',
    :'app_user'
)\gexec
SQL
