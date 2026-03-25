# DinoJelly

Интернет-магазин сладостей на Laravel + Inertia + Vue.

## Локальная разработка с мгновенным обновлением UI

Для разработки используй dev-стек с отдельным Vite HMR. Тогда изменения во `vue`, `js` и `scss` появляются в браузере сразу, без `docker build`.

### Первый запуск

```bash
cp .env.example .env
make dev-up
docker compose -f docker-compose.dev.yml exec app composer install
docker compose -f docker-compose.dev.yml exec app php artisan migrate --seed
```

Доступные адреса:

- приложение: `http://localhost:8000`
- Vite HMR: `http://localhost:5173`
- Adminer: `http://localhost:8080`

### Обычный рабочий цикл

```bash
make dev-up
make dev-logs
```

После этого редактируй файлы в `resources/` и обновляй страницу. Если dev-сервер активен, фронтенд будет подтягиваться из Vite, а не из `public/build`.

### Полезные команды

```bash
make dev-up
make dev-down
make dev-logs
make dev-fresh
make dev-shell
```

## Production-like режим

Если нужен режим, близкий к production:

```bash
docker compose up -d --build
```

В этом режиме изменения во фронтенде не появляются автоматически, пока не пересоберешь bundle.
