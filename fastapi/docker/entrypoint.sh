#!/bin/sh
set -e

: "${DB_HOST:=mysql}"
: "${DB_PORT:=3306}"
: "${DB_USER:?leanpublic}"
: "${DB_PASSWORD:?leanpublic_password}"
: "${DB_NAME:?leanpublic}"


echo "Executing init.sql..."
mysql --ssl-mode=DISABLED -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p"$DB_PASSWORD" "$DB_NAME" < /app/init.sql
echo "init.sql done"

echo "Starting FastAPI..."
exec uvicorn main:app --host 0.0.0.0 --port 8000