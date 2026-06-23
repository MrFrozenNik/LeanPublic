#!/bin/sh
set -e

: "${DB_HOST:=mysql}"
: "${DB_PORT:=3306}"
: "${DB_USER:?leanpublic}"
: "${DB_PASSWORD:?leanpublic_password}"
: "${DB_NAME:?leanpublic}"

echo "wait for laravel migrations"
until mysqlshow -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p"$DB_PASSWORD" "$DB_NAME" dishes >/dev/null 2>&1; do
    echo "dishes table not ready, wait 3s..."
    sleep 3
done

echo "database ready, start init.sql..."
mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p"$DB_PASSWORD" "$DB_NAME" < /app/init.sql

echo "starting FastAPI..."
exec uvicorn main:app --host 0.0.0.0 --port 8000