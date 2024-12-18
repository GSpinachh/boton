<?php
// Простая форма авторизации администратора
session_start();
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Простая проверка логина и пароля
    if ($_POST['username'] == 'admin' && $_POST['password'] == 'admin') {
        $_SESSION['admin_logged_in'] = true;
        header("Location: add_product.php");
        exit;
    } else {
        $error_message = "Неверный логин или пароль.";
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Авторизация</title>

    <style>
        /* Основные стили для страницы */
        body {
            font-family: Arial, sans-serif;
            background-color:rgba(0, 81, 152, 0.52); /* Легкий голубой фон */
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh; /* Занимает всю высоту экрана */
            margin: 0;
            overflow: hidden;
        }

        /* Обертка для формы */              
        .container {
            background-color: white;
            padding: 40px 60px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            width: 100%;
            max-width: 400px;
            text-align: center;
            box-sizing: border-box;
        }

        /* Заголовок страницы */
        h1 {
            color: #333;
            font-size: 28px;
            margin-bottom: 20px;
            font-weight: bold;
        }

        /* Текст внутри формы */
        label {
            display: block;
            text-align: left;
            margin: 10px 0 5px;
            font-size: 16px;
            color: #555;
        }

        /* Поля ввода */
        input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
            font-size: 16px;
        }

        /* Стили для кнопки входа */
        button {
            width: 100%;
            padding: 14px;
            background-color:rgba(0, 81, 152, 0.64);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 18px;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.3s ease;
        }

        button:hover {
            background-color:rgba(0, 87, 179, 0.8);
            transform: scale(1.05);
        }

        /* Стиль для ошибок или сообщений */
        .error-message {
            color: red;
            font-size: 14px;
            margin-top: 10px;
        }

        /* Эффект снега для фона */
        .snowflake {
            position: absolute;
            top: -10px;
            z-index: 9999;
            font-size: 30px;
            color: #fff;
            user-select: none;
            pointer-events: none;
            opacity: 0.8;
            animation: snow 10s linear infinite;
        }

        /* Анимация для снежинок */
        @keyframes snow {
            0% {
                transform: translateY(0) rotate(0);
            }
            100% {
                transform: translateY(100vh) rotate(360deg);
            }
        }

        /* Размещение снежинок на экране */
        .snowflake:nth-child(odd) {
            font-size: 18px;
            animation-duration: 12s;
        }

        .snowflake:nth-child(even) {
            font-size: 28px;
            animation-duration: 8s;
        }

        .snowflake:nth-child(1) { left: 10%; animation-duration: 10s; }
        .snowflake:nth-child(2) { left: 20%; animation-duration: 12s; }
        .snowflake:nth-child(3) { left: 30%; animation-duration: 9s; }
        .snowflake:nth-child(4) { left: 40%; animation-duration: 11s; }
        .snowflake:nth-child(5) { left: 50%; animation-duration: 10s; }
        .snowflake:nth-child(6) { left: 60%; animation-duration: 13s; }
        .snowflake:nth-child(7) { left: 70%; animation-duration: 12s; }
        .snowflake:nth-child(8) { left: 80%; animation-duration: 15s; }
        .snowflake:nth-child(9) { left: 90%; animation-duration: 14s; }
    </style>
</head>
<body>

    <div class="container">
        <h1>Вход для администратора</h1>

        <!-- Форма для входа -->
        <form action="login.php" method="POST">
            <label for="username">Логин:</label>
            <input type="text" name="username" id="username" required>

            <label for="password">Пароль:</label>
            <input type="password" name="password" id="password" required>

            <button type="submit">Войти</button>

            <!-- Отображение ошибки -->
            <?php if ($error_message): ?>
                <div class="error-message"><?= $error_message ?></div>
            <?php endif; ?>
        </form>
    </div>

    <script>
        // Создание снежинок для эффекта снегопада
        document.addEventListener('DOMContentLoaded', () => {
            const snowflakesCount = 50; // Количество снежинок
            const body = document.body;
            
            for (let i = 0; i < snowflakesCount; i++) {
                const snowflake = document.createElement('div');
                snowflake.classList.add('snowflake');
                snowflake.textContent = '❄'; // Символ снежинки
                snowflake.style.left = `${Math.random() * 100}%`; // Позиция по горизонтали
                snowflake.style.animationDuration = `${Math.random() * 5 + 5}s`; // Случайная продолжительность анимации
                snowflake.style.fontSize = `${Math.random() * 10 + 20}px`; // Случайный размер
                body.appendChild(snowflake);
            }
        });
    </script>

</body>
</html>
