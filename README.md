# LeanPublic — дневник питания

LeanPublic — веб-приложение для учёта КБЖУ, вдохновлённое FatSecret. Пользователи создают ингредиенты, собирают из них блюда и ведут дневник питания. Тренеры подключаются к клиентам и видят их дневник в реальном времени, оценивая блюда прямо в интерфейсе без перезагрузки страницы.

---

## 2. Схема архитектуры

```
Браузер ──HTTPS──► Nginx
                        │
              ┌─────────┴──────────┐
              ▼                    ▼
  leanpublic.nfrozensky.*    api.leanpublic.nfrozensky.*
              │                    │
        Laravel (PHP-FPM)    FastAPI (Uvicorn)
        SSR + запись БД      REST API + WebSocket
              │                    │
              ├──► MySQL ◄─────────┤ (read-only)
              │                    │
              └──► Redis ◄─────────┘
                   Pub/Sub: diary.{user_id}
```

**Цепочка реального времени:**
1. Клиент добавляет/удаляет запись в дневнике → Laravel сохраняет в MySQL
2. Laravel публикует событие в Redis канал `diary.{user_id}`
3. FastAPI получает событие через подписку (psubscribe `diary.*`)
4. FastAPI пушит данные всем подключённым WebSocket-клиентам
5. React-компонент на странице тренера обновляет таблицу без перезагрузки

---

## 3. Запуск

```bash
# 1. Клонировать репозиторий
git clone <repo-url>
cd LeanPublic

# 2. Создать файлы окружения
cp .env.example .env
cp fastapi/.env.example fastapi/.env
cp laravel/.env.example laravel/.env

# 3. Заполнить секреты (при необходимости)
# laravel/.env: GITHUB_CLIENT_SECRET=<ваш_секрет>

# 4. Поднять все сервисы
docker compose up -d --build

# 5. Дождаться полного старта (30-60 секунд)
docker compose ps
# Все сервисы должны быть Up / healthy

# 6. Открыть в браузере
# https://leanpublic.nfrozensky.ai-info.ru

# Примечание: используется самоподписанный сертификат.
# При первом открытии нажмите «Дополнительно» → «Перейти на сайт».
# Сделайте то же самое для API домена:
# https://api.leanpublic.nfrozensky.ai-info.ru/api/status
```

Миграции и сиды запускаются **автоматически** при старте Laravel-контейнера.

---

## 4. Основные сценарии

### Регистрация и вход
- Через email/пароль (Laravel Breeze)
- Через GitHub OAuth (Laravel Socialite)
- Гостям защищённые страницы недоступны

### Управление ингредиентами
- Создать ингредиент с КБЖУ на 100 г
- Редактировать и удалять свои ингредиенты

### Сборка блюд
- Создать блюдо, добавить ингредиенты с граммовкой
- Автоматический подсчёт суммарного КБЖУ блюда и на порцию

### Дневник питания
- Добавить приём пищи: выбрать блюдо или ингредиент, указать граммы и время
- Фильтрация по дате
- Итоги КБЖУ за день

### Функции тренера
- Пригласить тренера по email (в настройках профиля)
- Тренер видит вкладку «Клиенты» в навигации
- Дневник клиента загружается через FastAPI REST и обновляется через WebSocket в реальном времени
- Тренер оценивает блюда клиента кнопками Good / ok / Bad — оценки сохраняются через FastAPI

### Redis Pub/Sub (реальное время)
- При добавлении записи в дневник: React у тренера немедленно добавляет строку
- При удалении записи: строка исчезает без перезагрузки
- WebSocket автоматически переподключается при разрыве (через 3 сек)

---

## 5. Структура БД

```
users
  id, github_id, name, email, password, remember_token, timestamps

ingredients
  id, owner_id → users, name
  kcal_100, protein_100, fat_100, carb_100 (decimal 8,2)
  timestamps
  INDEX: name, owner_id (FK)

dishes
  id, owner_id → users, name, servings
  timestamps
  INDEX: name, owner_id (FK)

dish_ingredient  (pivot M:N)
  id, dish_id → dishes, ingredient_id → ingredients, grams
  UNIQUE: (dish_id, ingredient_id)
  timestamps

diary_entries
  id, user_id → users
  dish_id → dishes (nullable)
  ingredient_id → ingredients (nullable)
  grams, eaten_at
  timestamps
  INDEX: (user_id, eaten_at)

trainer_links  (M:N users ↔ users)
  id, trainer_id → users, client_id → users
  UNIQUE: (trainer_id, client_id)
  timestamps

dish_ratings  (создаётся FastAPI через init.sql)
  id, dish_id → dishes, trainer_id → users
  verdict ENUM('up','mid','down')
  UNIQUE: (dish_id, trainer_id)
  timestamps
```

**Связи:**
- `users` 1→N `ingredients`, `dishes`, `diary_entries`
- `dishes` M→N `ingredients` через `dish_ingredient`
- `users` M→N `users` через `trainer_links` (тренер ↔ клиент)
- `dishes` M→N `users` через `dish_ratings` (оценки тренера)

---

## 6. API эндпоинты FastAPI

| Метод | URL | Описание |
|-------|-----|----------|
| GET | `/api/status` | Проверка работоспособности |
| GET | `/api/clients/{client_id}/diary?date=YYYY-MM-DD` | Дневник клиента за дату |
| GET | `/api/dishes/{dish_id}?trainer_id={id}` | Блюдо с КБЖУ и оценкой тренера |
| POST | `/api/dishes/{dish_id}/rating` | Создать оценку блюда |
| PUT | `/api/dishes/{dish_id}/rating?trainer_id={id}` | Изменить оценку |
| DELETE | `/api/dishes/{dish_id}/rating?trainer_id={id}` | Удалить оценку |
| WS | `/ws/clients/{client_id}/diary` | WebSocket-поток дневника |

---
