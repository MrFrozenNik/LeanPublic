import os
import aiomysql

DB_CONFIG = {
    'host': os.getenv('DB_HOST', '127.0.0.1'),
    'port': int(os.getenv('DB_PORT', 3306)),
    'user': os.getenv('DB_USER', 'leanpublic'),
    'password': os.getenv('DB_PASSWORD', 'leanpublic_password'),
    'db': os.getenv('DB_NAME', 'leanpublic_laravel'),
    'charset': 'utf8mb4',
}

async def get_db():
    return await aiomysql.connect(**DB_CONFIG)