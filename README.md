1. Название и описание
LeanPublic — веб-приложение — дневник питания (аналог FatSecret). Пользователи ведут учёт КБЖУ: создают ингредиенты, собирают из них блюда, записывают приёмы пищи. Тренеры могут подключаться к клиентам, видеть их дневник в реальном времени и оценивать блюда.
2. Архитектура (ASCII-схема)
Браузер ──HTTPS──► Nginx
                         │
               ┌─────────┼─────────┐
               ▼                   ▼
     leanpublic.*           api.leanpublic.*
               │                   │
         Laravel PHP-FPM      FastAPI Uvicorn
         (SSR + запись)       (REST + WebSocket)
               │                   │
               ├──► MySQL ◄───────┤(read only)
               │                   │
               └──► Redis ◄───────┘
                    Pub/Sub
               diary.{user_id}
Описание цепочки: запросы → nginx (2 virtual hosts, HTTPS) → Laravel (Breeze, Blade SSR, CRUD) / FastAPI (REST + WS). Redis Pub/Sub соединяет сервисы. FastAPI читает MySQL (не пишет). WebSocket от FastAPI к React-компоненту на странице тренера.
3. Запуск
Пошагово: clone → cp .env.example .env → docker compose up -d → docker compose exec laravel php artisan migrate --force && php artisan db:seed --force → открыть в браузере
4. Основные сценарии
- Регистрация и вход — через email (Breeze) или GitHub OAuth (Socialite)
- Управление ингредиентами — создать/редактировать/удалить ингредиент с КБЖУ на 100г
- Сборка блюд — создать блюдо, добавить в него ингредиенты с граммовкой, посмотреть суммарное КБЖУ
- Дневник питания — добавить приём пищи (блюдо или ингредиент + граммы), итоги за день
- Тренер — пригласить тренера по email; тренер видит дневник клиента в реальном времени через WebSocket
- Оценка блюд (React + FastAPI) — тренер оценивает блюда клиента up/mid/down; данные приходят через WebSocket без перезагрузки
- Redis Pub/Sub — Laravel публикует события при создании/удалении записи → FastAPI получает через подписку → шлёт в WebSocket → React обновляет таблицу
5. Структура БД
Перечислить 7 основных + служебные таблицы, ключи и связи:
- users (id, github_id, name, email, password)
- ingredients (id, owner_id→users, name, kcal/protein/fat/carb_100)
- dishes (id, owner_id→users, name, servings)
- dish_ingredient (id, dish_id→dishes, ingredient_id→ingredients, grams) — M:N pivot
- diary_entries (id, user_id→users, dish_id→dishes?, ingredient_id→ingredients?, grams, eaten_at)
- trainer_links (id, trainer_id→users, client_id→users) — M:N users
- dish_ratings (id, dish_id→dishes, trainer_id→users, verdict: up/mid/down) — создаётся FastAPI
