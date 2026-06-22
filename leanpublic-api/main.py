from fastapi import FastAPI
from datetime import datetime
from contextlib import asynccontextmanager
import asyncio
import json
import os
import logging

from database import get_db
from routers import diary, dishes, ws
from routers.ws import manager

logger = logging.getLogger('uvicorn.error')


async def redis_subscriber():
    import redis.asyncio as aioredis

    while True:
        try:
            redis_host = os.getenv('REDIS_HOST', '127.0.0.1')
            r = aioredis.from_url(f'redis://{redis_host}:6379', decode_responses=True)
            pubsub = r.pubsub()
            await pubsub.psubscribe('diary.*')
            logger.info('[startup] Redis subscriber запущен')

            while True:
                message = await pubsub.get_message(ignore_subscribe_messages=True, timeout=1.0)
                if message is None or message['type'] != 'pmessage':
                    continue

                channel: str = message['channel']
                try:
                    client_id = int(channel.split('.')[-1])
                except (ValueError, IndexError):
                    continue

                payload = json.loads(message['data'])
                logger.info(f'[redis] diary.{client_id}: {payload}')

                if payload.get('event') == 'entry.created':
                    conn = await get_db()
                    async with conn.cursor() as cursor:
                        await cursor.execute(
                            '''SELECT id, grams, eaten_at, dish_id, ingredient_id
                               FROM diary_entries WHERE id = %s''',
                            (payload['entry_id'],),
                        )
                        row = await cursor.fetchone()
                        if row:
                            from routers.diary import row_to_entry
                            enriched = await row_to_entry(cursor, row)
                            await manager.broadcast(client_id, {
                                'event': 'entry.created',
                                'entry_id': payload['entry_id'],
                                'entry': enriched,
                            })
                    conn.close()

                elif payload.get('event') == 'entry.deleted':
                    await manager.broadcast(client_id, {
                        'event': 'entry.deleted',
                        'entry_id': payload['entry_id'],
                    })

        except asyncio.CancelledError:
            raise
        except Exception as e:
            logger.error(f'[redis] subscriber упал: {e!r}, переподключение через 3с')
            await asyncio.sleep(3)


@asynccontextmanager
async def lifespan(app: FastAPI):
    task = asyncio.create_task(redis_subscriber())
    yield
    task.cancel()


app = FastAPI(title='LeanPublic API', version='0.1.0', lifespan=lifespan)

@app.middleware("http")
async def cors_middleware(request, call_next):
    response = await call_next(request)
    response.headers["Access-Control-Allow-Origin"] = "*"
    response.headers["Access-Control-Allow-Methods"] = "*"
    response.headers["Access-Control-Allow-Headers"] = "*"
    return response

app.include_router(diary.router)
app.include_router(dishes.router)
app.include_router(ws.router)


@app.get('/api/status')
async def status():
    return {'status': 'ok', 'time': str(datetime.now())}