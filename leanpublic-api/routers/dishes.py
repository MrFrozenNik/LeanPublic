from fastapi import APIRouter, HTTPException, Query
from pydantic import BaseModel
from enum import Enum
from database import get_db

router = APIRouter(prefix='/api')

class VerdictEnum(str, Enum):
    up = 'up'
    mid = 'mid'
    down = 'down'

class RatingIn(BaseModel):
    trainer_id: int
    verdict: VerdictEnum

class RatingUpdate(BaseModel):
    verdict: VerdictEnum


@router.get('/dishes/{dish_id}')
async def get_dish(dish_id: int, trainer_id: int | None = Query(None)):
    conn = await get_db()
    async with conn.cursor() as cursor:
        await cursor.execute(
            'SELECT id, name, servings, owner_id FROM dishes WHERE id = %s',
            (dish_id,),
        )
        dish_row = await cursor.fetchone()
        if not dish_row:
            conn.close()
            raise HTTPException(404, 'Dish not found')

        await cursor.execute(
            '''SELECT i.id, i.name, i.kcal_100, i.protein_100, i.fat_100, i.carb_100, di.grams
               FROM dish_ingredient di
               JOIN ingredients i ON i.id = di.ingredient_id
               WHERE di.dish_id = %s''',
            (dish_id,),
        )
        ing_rows = await cursor.fetchall()

        ingredients = []
        totals = {'kcal': 0, 'protein': 0, 'fat': 0, 'carb': 0}
        for r in ing_rows:
            factor = float(r[6]) / 100
            ingredients.append({
                'id': r[0],
                'name': r[1],
                'kcal_100': float(r[2]),
                'protein_100': float(r[3]),
                'fat_100': float(r[4]),
                'carb_100': float(r[5]),
                'grams': float(r[6]),
            })
            totals['kcal'] += float(r[2]) * factor
            totals['protein'] += float(r[3]) * factor
            totals['fat'] += float(r[4]) * factor
            totals['carb'] += float(r[5]) * factor

        rating = None
        if trainer_id is not None:
            await cursor.execute(
                '''SELECT id, dish_id, trainer_id, verdict, created_at, updated_at
                   FROM dish_ratings
                   WHERE dish_id = %s
                     AND trainer_id = %s''',
                (dish_id, trainer_id),
            )
            r_row = await cursor.fetchone()
            if r_row:
                rating = {
                    'id': r_row[0],
                    'dish_id': r_row[1],
                    'trainer_id': r_row[2],
                    'verdict': r_row[3],
                    'created_at': str(r_row[4]) if r_row[4] else None,
                    'updated_at': str(r_row[5]) if r_row[5] else None,
                }

    conn.close()
    return {
        'id': dish_row[0],
        'name': dish_row[1],
        'servings': dish_row[2],
        'owner_id': dish_row[3],
        'ingredients': ingredients,
        'totals': {k: round(v, 2) for k, v in totals.items()},
        'rating': rating,
    }


@router.post('/dishes/{dish_id}/rating', status_code=201)
async def create_rating(dish_id: int, body: RatingIn):
    conn = await get_db()
    async with conn.cursor() as cursor:
        await cursor.execute('SELECT id FROM dishes WHERE id = %s', (dish_id,))
        if not await cursor.fetchone():
            conn.close()
            raise HTTPException(404, 'Dish not found')

        await cursor.execute(
            'SELECT id, verdict FROM dish_ratings WHERE dish_id = %s AND trainer_id = %s',
            (dish_id, body.trainer_id),
        )
        existing = await cursor.fetchone()

        if existing:
            existing_id, existing_verdict = existing
            if existing_verdict == body.verdict.value:
                await cursor.execute(
                    '''SELECT id, dish_id, trainer_id, verdict, created_at, updated_at
                       FROM dish_ratings WHERE id = %s''',
                    (existing_id,),
                )
                row = await cursor.fetchone()
                conn.close()
                return {
                    'id': row[0], 'dish_id': row[1], 'trainer_id': row[2],
                    'verdict': row[3],
                    'created_at': str(row[4]) if row[4] else None,
                    'updated_at': str(row[5]) if row[5] else None,
                }
            else:
                conn.close()
                raise HTTPException(409, 'Rating already exists with a different verdict; use PUT to change it')

        await cursor.execute(
            'INSERT INTO dish_ratings (dish_id, trainer_id, verdict) VALUES (%s, %s, %s)',
            (dish_id, body.trainer_id, body.verdict.value),
        )
        await conn.commit()
        new_id = cursor.lastrowid

        await cursor.execute(
            '''SELECT id, dish_id, trainer_id, verdict, created_at, updated_at
               FROM dish_ratings
               WHERE id = %s''',
            (new_id,),
        )
        row = await cursor.fetchone()
    conn.close()
    return {
        'id': row[0],
        'dish_id': row[1],
        'trainer_id': row[2],
        'verdict': row[3],
        'created_at': str(row[4]) if row[4] else None,
        'updated_at': str(row[5]) if row[5] else None,
    }


@router.put('/dishes/{dish_id}/rating')
async def update_rating(dish_id: int, trainer_id: int = Query(...), body: RatingUpdate = None):
    conn = await get_db()
    async with conn.cursor() as cursor:
        await cursor.execute(
            'SELECT id FROM dish_ratings WHERE dish_id = %s AND trainer_id = %s',
            (dish_id, trainer_id),
        )
        row = await cursor.fetchone()
        if not row:
            conn.close()
            raise HTTPException(404, 'Rating not found')

        await cursor.execute(
            'UPDATE dish_ratings SET verdict = %s WHERE id = %s',
            (body.verdict.value, row[0]),
        )
        await conn.commit()

        await cursor.execute(
            '''SELECT id, dish_id, trainer_id, verdict, created_at, updated_at
               FROM dish_ratings
               WHERE id = %s''',
            (row[0],),
        )
        updated = await cursor.fetchone()
    conn.close()
    return {
        'id': updated[0],
        'dish_id': updated[1],
        'trainer_id': updated[2],
        'verdict': updated[3],
        'created_at': str(updated[4]) if updated[4] else None,
        'updated_at': str(updated[5]) if updated[5] else None,
    }


@router.delete('/dishes/{dish_id}/rating')
async def delete_rating(dish_id: int, trainer_id: int = Query(...)):
    conn = await get_db()
    async with conn.cursor() as cursor:
        await cursor.execute(
            'SELECT id FROM dish_ratings WHERE dish_id = %s AND trainer_id = %s',
            (dish_id, trainer_id),
        )
        row = await cursor.fetchone()
        if not row:
            conn.close()
            raise HTTPException(404, 'Rating not found')

        await cursor.execute('DELETE FROM dish_ratings WHERE id = %s', (row[0],))
        await conn.commit()
    conn.close()
    return {'detail': 'Rating deleted'}