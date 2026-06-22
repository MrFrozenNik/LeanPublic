from fastapi import APIRouter, Query
from database import get_db
import aiomysql

router = APIRouter(prefix='/api')


async def calc_entry_totals(cursor, entry: dict) -> dict:
    factor = float(entry['grams']) / 100

    if entry['ingredient_id']:
        await cursor.execute(
            'SELECT kcal_100, protein_100, fat_100, carb_100 FROM ingredients WHERE id = %s',
            (entry['ingredient_id'],),
        )
        ing = await cursor.fetchone()
        if ing:
            return {
                'kcal': float(ing[0]) * factor,
                'protein': float(ing[1]) * factor,
                'fat': float(ing[2]) * factor,
                'carb': float(ing[3]) * factor,
            }
        return {'kcal': 0, 'protein': 0, 'fat': 0, 'carb': 0}

    if entry['dish_id']:
        await cursor.execute(
            '''SELECT i.kcal_100, i.protein_100, i.fat_100, i.carb_100, di.grams
               FROM dish_ingredient di
               JOIN ingredients i ON i.id = di.ingredient_id
               WHERE di.dish_id = %s''',
            (entry['dish_id'],),
        )
        rows = await cursor.fetchall()
        dish_weight = float(sum(r[4] for r in rows))
        if dish_weight <= 0:
            return {'kcal': 0, 'protein': 0, 'fat': 0, 'carb': 0}
        portion_factor = entry['grams'] / dish_weight
        totals = {'kcal': 0, 'protein': 0, 'fat': 0, 'carb': 0}
        for r in rows:
            ing_factor = float(r[4]) / 100
            totals['kcal'] += float(r[0]) * ing_factor * portion_factor
            totals['protein'] += float(r[1]) * ing_factor * portion_factor
            totals['fat'] += float(r[2]) * ing_factor * portion_factor
            totals['carb'] += float(r[3]) * ing_factor * portion_factor
        return totals

    return {'kcal': 0, 'protein': 0, 'fat': 0, 'carb': 0}


async def row_to_entry(cursor, row) -> dict:
    entry = {
        'id': row[0],
        'grams': float(row[1]),
        'eaten_at': str(row[2]),
        'dish_id': row[3],
        'ingredient_id': row[4],
    }
    totals = await calc_entry_totals(cursor, entry)

    dish_name = None
    ingredient_name = None
    if row[3]:
        await cursor.execute('SELECT name FROM dishes WHERE id = %s', (row[3],))
        d = await cursor.fetchone()
        dish_name = d[0] if d else None
    if row[4]:
        await cursor.execute('SELECT name FROM ingredients WHERE id = %s', (row[4],))
        ing = await cursor.fetchone()
        ingredient_name = ing[0] if ing else None

    return {
        'id': entry['id'],
        'grams': entry['grams'],
        'eaten_at': entry['eaten_at'],
        'dish_name': dish_name,
        'ingredient_name': ingredient_name,
        'totals': {k: round(v, 2) for k, v in totals.items()},
    }


@router.get('/clients/{client_id}/diary')
async def get_client_diary(client_id: int, date: str = Query('')):
    conn = await get_db()
    async with conn.cursor() as cursor:
        if date:
            await cursor.execute(
                '''SELECT id, grams, eaten_at, dish_id, ingredient_id
                   FROM diary_entries
                   WHERE user_id = %s AND DATE(eaten_at) = %s
                   ORDER BY eaten_at''',
                (client_id, date),
            )
        else:
            await cursor.execute(
                '''SELECT id, grams, eaten_at, dish_id, ingredient_id
                   FROM diary_entries
                   WHERE user_id = %s AND DATE(eaten_at) = CURDATE()
                   ORDER BY eaten_at''',
                (client_id,),
            )
        rows = await cursor.fetchall()
        results = []
        for row in rows:
            results.append(await row_to_entry(cursor, row))
    conn.close()
    return {'client_id': client_id, 'date': date or 'today', 'entries': results}