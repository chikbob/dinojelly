<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Корзина DinoJelly ждёт вас</title>
</head>
<body style="margin:0;padding:24px;background:#f8fafc;font-family:Arial,sans-serif;color:#0f172a;">
<div style="max-width:640px;margin:0 auto;background:#ffffff;border-radius:20px;padding:32px;border:1px solid #e2e8f0;">
    <p style="margin:0 0 12px;color:#16a34a;font-size:12px;letter-spacing:0.08em;text-transform:uppercase;">DinoJelly</p>
    <h1 style="margin:0 0 16px;font-size:28px;line-height:1.2;">Вы забыли товары в корзине</h1>
    <p style="margin:0 0 24px;font-size:16px;line-height:1.6;color:#475569;">
        Мы сохранили вашу корзину. Вернитесь к оформлению и завершите заказ, пока выбранные позиции ещё доступны.
    </p>

    <div style="margin:0 0 24px;padding:20px;background:#f8fafc;border-radius:16px;">
        @foreach($reminder->cart_snapshot as $item)
            <div style="padding:10px 0;border-bottom:1px solid #e2e8f0;">
                <strong>{{ $item['name'] }}</strong><br>
                <span style="color:#64748b;">{{ $item['quantity'] }} × {{ $item['price'] }} ₽</span>
            </div>
        @endforeach
    </div>

    <a href="{{ $recoveryUrl }}" style="display:inline-block;padding:14px 22px;background:#2563eb;color:#ffffff;text-decoration:none;border-radius:12px;font-weight:700;">
        Вернуться к корзине
    </a>
</div>
</body>
</html>
