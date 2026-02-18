# Admin Access Control - Настройка защиты админ-панели

## Что было сделано:

### 1. Создан middleware `IsAdmin`
**Файл:** `app/Http/Middleware/IsAdmin.php`

Проверяет:
- Авторизован ли пользователь
- Имеет ли пользователь роль `admin`

Если нет - выдает ошибку 403 (Доступ запрещён)

### 2. Зарегистрирован middleware
**Файл:** `app/Http/Kernel.php`

Добавлен алиас: `'admin' => \App\Http\Middleware\IsAdmin::class`

### 3. Применен к admin роутам
**Файл:** `routes/web.php`

Изменено с `->middleware(['auth'])` на `->middleware(['auth', 'admin'])`

Теперь все роуты в группе `/admin/*` защищены проверкой роли.

### 4. Обновлена модель User
**Файл:** `app/Models/User.php`

Добавлено поле `role` в `$fillable` для массового заполнения.

### 5. Создана страница ошибки 403
**Файл:** `resources/views/errors/403.blade.php`

Красивая страница с объяснением для пользователей без прав администратора.

## Как использовать:

### Админ пользователь:
- **Email:** admin@dinogel.ru
- **Password:** password
- **Роль:** admin

### Тестовый пользователь:
- **Email:** ricarrdo1488@gmail.com
- **Password:** password
- **Роль:** user (НЕ может заходить в админку)

### Создать нового админа вручную:

```bash
docker-compose -f docker-compose.dev.yml exec app php artisan tinker
```

Затем в tinker:
```php
$user = User::find(1); // или User::where('email', 'user@example.com')->first()
$user->role = 'admin';
$user->save();
```

### Или через SQL:
```bash
docker-compose -f docker-compose.dev.yml exec app php artisan tinker
```

```php
DB::table('users')->where('email', 'user@example.com')->update(['role' => 'admin']);
```

## Проверка работы:

1. Зайдите как обычный пользователь (ricarrdo1488@gmail.com)
2. Попробуйте открыть: `http://localhost:8000/admin`
3. Должна показаться страница **403 - Доступ запрещён**

4. Выйдите и зайдите как админ (admin@dinogel.ru)
5. Откройте: `http://localhost:8000/admin`
6. Должна открыться админ-панель

## Защищённые роуты:

Все роуты в группе `/admin`:
- `/admin` - Dashboard
- `/admin/products` - Управление продуктами
- `/admin/orders` - Управление заказами
- `/admin/users` - Управление пользователями

## Безопасность:

✅ Проверка авторизации
✅ Проверка роли администратора
✅ Красивая страница ошибки
✅ Невозможно обойти проверку через URL
