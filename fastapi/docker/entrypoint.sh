#!/bin/sh
set -e

echo "Waiting for Laravel to be ready..."
until python3 -c "
import aiomysql, asyncio, sys
async def check():
    try:
        conn = await aiomysql.connect(
            host='mysql', port=3306,
            user='leanpublic', password='leanpublic_password',
            db='leanpublic', ssl=None
        )
        async with conn.cursor() as cur:
            await cur.execute('SELECT 1 FROM dishes LIMIT 1')
            await cur.fetchone()
        conn.close()
        sys.exit(0)
    except Exception as e:
        sys.exit(1)
asyncio.run(check())
" 2>/dev/null; do
    echo "Laravel not ready yet..."
    sleep 3
done

echo "Laravel is ready. Running init.sql..."
python3 -c "
import aiomysql, asyncio

async def run():
    conn = await aiomysql.connect(
        host='mysql', port=3306,
        user='leanpublic', password='leanpublic_password',
        db='leanpublic', ssl=None
    )
    with open('/app/init.sql', 'r') as f:
        sql = f.read()
    async with conn.cursor() as cur:
        for statement in sql.split(';'):
            s = statement.strip()
            if s:
                await cur.execute(s)
    await conn.commit()
    conn.close()
    print('init.sql executed')

asyncio.run(run())
"

echo "Starting FastAPI..."
exec uvicorn main:app --host 0.0.0.0 --port 8000