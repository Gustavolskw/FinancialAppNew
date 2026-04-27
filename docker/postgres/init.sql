SELECT 'CREATE DATABASE financial_app WITH ENCODING ''UTF8'' TEMPLATE template0'
WHERE NOT EXISTS (
    SELECT 1 FROM pg_database WHERE datname = 'financial_app'
)\gexec
