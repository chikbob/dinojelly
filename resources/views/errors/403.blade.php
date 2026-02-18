<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 - Access Denied</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body { width: 100%; height: 100%; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #f5f5f5 0%, #fafafa 100%);
            display: flex; align-items: center; justify-content: center; min-height: 100vh;
        }
        .access-denied { width: 100%; padding: 60px 20px; display: flex; align-items: center; justify-content: center; }
        .access-denied__container { display: grid; grid-template-columns: 1fr 1fr; gap: 60px; max-width: 1000px; width: 100%; align-items: center; }
        .access-denied__content { display: flex; flex-direction: column; gap: 24px; }
        .access-denied__code {
            font-family: "Press Start 2P", system-ui; font-size: 120px; font-weight: 700; line-height: 1;
            background: linear-gradient(90deg, #A8E62E, #29CC5F);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; margin: 0;
        }
        .access-denied__title {
            font-family: "Press Start 2P", system-ui; font-size: 28px; font-weight: 400; color: #333; margin: 0;
            position: relative; padding-bottom: 16px;
        }
        .access-denied__title::after {
            content: ''; display: block; width: 60px; height: 4px;
            background: linear-gradient(90deg, #A8E62E, #29CC5F); margin: 12px 0 0 0; border-radius: 2px;
        }
        .access-denied__message { font-size: 16px; color: #666; line-height: 1.6; margin: 0; max-width: 450px; }
        .access-denied__actions { display: flex; gap: 12px; margin-top: 12px; }
        .access-denied__btn-home {
            font-family: "Press Start 2P", system-ui;
            background: linear-gradient(135deg, #A8E62E, #29CC5F); color: white; padding: 16px 32px;
            border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer;
            transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(41, 204, 95, 0.3);
            text-decoration: none; display: inline-block;
        }
        .access-denied__btn-home:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(41, 204, 95, 0.4); }
        .access-denied__hint { font-size: 12px; color: #999; line-height: 1.5; margin: 0; max-width: 450px; }
        .access-denied__illustration { display: flex; align-items: center; justify-content: center; height: 300px; }
        .access-denied__lock { font-size: 150px; opacity: 0.8; animation: float 3s ease-in-out infinite; }
        @keyframes float { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-20px); } }
        @media (max-width: 768px) {
            .access-denied { padding: 40px 20px; }
            .access-denied__container { grid-template-columns: 1fr; gap: 40px; }
            .access-denied__code { font-size: 80px; }
            .access-denied__title { font-size: 20px; }
            .access-denied__message { font-size: 14px; }
            .access-denied__illustration { height: 200px; }
            .access-denied__lock { font-size: 100px; }
        }
    </style>
</head>
<body>
    <div class="access-denied">
        <div class="access-denied__container">
            <div class="access-denied__content">
                <h1 class="access-denied__code">403</h1>
                <h2 class="access-denied__title" id="title">Доступ запрещён</h2>
                <p class="access-denied__message" id="message">У вас нет прав для входа в админ-панель. Только администраторы могут получить доступ к этому разделу.</p>
                <div class="access-denied__actions">
                    <a href="/" class="access-denied__btn-home" id="btn-text">На главную</a>
                </div>
                <p class="access-denied__hint" id="hint">Если вы считаете, что это ошибка, свяжитесь с администратором сайта.</p>
            </div>
            <div class="access-denied__illustration">
                <div class="access-denied__lock">🔒</div>
            </div>
        </div>
    </div>
    <script>
        const lang = localStorage.getItem('lang') || 'ru';
        const translations = {
            ru: { title: "Доступ запрещён", message: "У вас нет прав для входа в админ-панель. Только администраторы могут получить доступ к этому разделу.", button: "На главную", hint: "Если вы считаете, что это ошибка, свяжитесь с администратором сайта." },
            en: { title: "Access Denied", message: "You don't have permission to access the admin panel. Only administrators can access this section.", button: "Go Home", hint: "If you think this is a mistake, please contact the site administrator." },
            uk: { title: "Доступ заборонено", message: "Ви не маєте прав для входу в адмін-панель. Тільки адміністратори можуть отримати доступ до цього розділу.", button: "На головну", hint: "Якщо ви вважаєте, що це помилка, будь ласка, зв'яжіться з адміністратором сайту." }
        };
        const current = translations[lang] || translations.ru;
        document.getElementById('title').textContent = current.title;
        document.getElementById('message').textContent = current.message;
        document.getElementById('btn-text').textContent = current.button;
        document.getElementById('hint').textContent = current.hint;
    </script>
</body>
</html>
