#!/bin/sh
set -e

echo "Waiting for Laravel to be ready..."
until mysql -h mysql -u leanpublic -pleanpublic_password leanpublic -e "SELECT 1 FROM dishes LIMIT 1" 2>/dev/null; do
    echo "Laravel not ready yet..."
    sleep 3
done

echo "Laravel is ready. Running init.sql..."
mysql -h mysql -u leanpublic -pleanpublic_password leanpublic < /app/init.sql

echo "Starting FastAPI..."
exec uvicorn main:app --host 0.0.0.0 --port 8000