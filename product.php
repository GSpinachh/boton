<?php
// Подключаемся к базе данных
$pdo = new PDO("mysql:host=localhost;dbname=user", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Получаем ID товара из URL
$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($productId <= 0) {
    die('Неверный ID товара');
}

// Запрос для получения информации о товаре
$sql = "SELECT * FROM products WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$productId]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    die('Товар не найден');
}

// Получаем основное изображение
$sqlMainImage = "SELECT image_name FROM product_images WHERE product_id = ? AND is_main = 1";
$stmtMainImage = $pdo->prepare($sqlMainImage);
$stmtMainImage->execute([$productId]);
$mainImage = $stmtMainImage->fetchColumn();

// Получаем дополнительные изображения
$sqlGalleryImages = "SELECT image_name FROM product_images WHERE product_id = ? AND is_main = 0";
$stmtGalleryImages = $pdo->prepare($sqlGalleryImages);
$stmtGalleryImages->execute([$productId]);
$galleryImages = $stmtGalleryImages->fetchAll(PDO::FETCH_ASSOC);

// Обработка отправки комментария (обновленная часть для AJAX)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['comment'], $_POST['user_name'], $_POST['stars'])) {
    $comment = htmlspecialchars($_POST['comment']);
    $userName = htmlspecialchars($_POST['user_name']);
    $stars = (int)$_POST['stars'];  // Получаем количество звезд
    $parentId = isset($_POST['parent_id']) ? (int)$_POST['parent_id'] : NULL;

    // Проверка, оставлял ли уже этот пользователь комментарий
    $checkComment = "SELECT COUNT(*) FROM comments WHERE product_id = ? AND user_name = ?";
    $stmtCheck = $pdo->prepare($checkComment);
    $stmtCheck->execute([$productId, $userName]);
    $commentCount = $stmtCheck->fetchColumn();

    if ($commentCount > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Вы уже оставляли комментарий для этого товара']);
        exit;
    }

    // Добавляем комментарий в базу данных
    $sqlInsertComment = "INSERT INTO comments (product_id, parent_id, user_name, content, stars) VALUES (?, ?, ?, ?, ?)";
    $stmtInsertComment = $pdo->prepare($sqlInsertComment);
    $stmtInsertComment->execute([$productId, $parentId, $userName, $comment, $stars]);

    // Отправляем успех
    echo json_encode(['status' => 'success', 'message' => 'Комментарий успешно добавлен']);
    exit;
}

// Получаем комментарии
$sqlComments = "SELECT * FROM comments WHERE product_id = ? AND parent_id IS NULL ORDER BY id DESC";
$stmtComments = $pdo->prepare($sqlComments);
$stmtComments->execute([$productId]);
$comments = $stmtComments->fetchAll(PDO::FETCH_ASSOC);

// Функция для отображения комментариев и их ответов
function displayComments($comments, $pdo) {
    foreach ($comments as $comment) {
        echo "<div class='comment'>";
        echo "<strong>" . htmlspecialchars($comment['user_name']) . ":</strong><p>" . nl2br(htmlspecialchars($comment['content'])) . "</p>";

        // Отображаем звезды
        echo "<p>Рейтинг: " . str_repeat("⭐", $comment['stars']) . "</p>";  // Выводим звезды

        // Форма для ответа
        echo "<form method='POST'>
                <input type='hidden' name='parent_id' value='" . $comment['id'] . "'>
                <input type='text' name='user_name' placeholder='Ваше имя' required>
                <textarea name='comment' placeholder='Ваш ответ' required></textarea>
                <label for='stars'>Рейтинг:</label>
                <select name='stars'>
                    <option value='1'>1 звезда</option>
                    <option value='2'>2 звезды</option>
                    <option value='3'>3 звезды</option>
                    <option value='4'>4 звезды</option>
                    <option value='5'>5 звезд</option>
                </select>
                <button type='submit'>Ответить</button>
              </form>";

        // Выводим ответы на комментарий
        $sqlReplies = "SELECT * FROM comments WHERE parent_id = ? ORDER BY id ASC";
        $stmtReplies = $pdo->prepare($sqlReplies);
        $stmtReplies->execute([$comment['id']]);
        $replies = $stmtReplies->fetchAll(PDO::FETCH_ASSOC);

        if ($replies) {
            echo "<div class='replies'>";
            displayComments($replies, $pdo);  // Рекурсивный вызов для отображения ответов
            echo "</div>";
        }

        echo "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Товар - <?= htmlspecialchars($product['name']) ?></title>
    <style>
        /* Новогодний стиль */
        body {
            font-family: 'Arial', sans-serif;
            background-color: rgba(0, 81, 152, 0.52); /* Легкий голубой фон */
            color: white;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
            position: relative;
        }

        h1 {
            text-align: center;
            padding: 24px;
            background-color: rgba(0, 81, 152, 0.92);
            margin: 0;
            color: white;
        }

        p {
            text-align: center;
            font-size: 18px;
        }

        h2 {
            text-align: center;
            padding: 10px;
        }

        .conteiner {
            display: flex;
            flex-wrap: wrap;
        }

        .gallery {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: space-between;
            position: relative;
            left: 100px;
        }

        .gallery img {
            width: 100px;
            height: auto;
            border-radius: 8px;
            border: 2px solid #fff;
            cursor: pointer;
        }

        .osnov {
            position: relative;
            left: 100px;
            margin-top: 20px;
        }

        .osnov img {
            width: 550px;
            height: auto;
            border-radius: 8px;
            border: 1px solid #fff;
        }

        /* Контейнер для комментариев */
        .comments-container {
            margin: 100px;
            padding: 20px;
            background-color: rgba(0, 81, 152, 0.64);
            border-radius: 10px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
        }

        .comment {
            background-color: rgba(0, 81, 152, 0.92);
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .comment strong {
            color: #fff;
        }

        .comment p {
            color: #ddd;
            text-align: left;
        }

        /* Стили для формы комментариев в углу */
        form {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background-color: rgba(0, 81, 152, 0.64);
            padding: 30px;
            border-radius: 10px;
            width: 400px;
            max-width: 100%;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            z-index: 9999;
        }

        form input[type="text"], form textarea {
            margin: 5px 0;
            padding: 10px -0px;
            width: 100%;
            border-radius: 5px;
            border: 1px solid #ddd;
            background-color: #fff;
            color: #333;
        }

        form button {
            background-color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            font-size: 18px;
            transition: background-color 0.3s ease, transform 0.3s ease;
        }

        form button:hover {
            background-color: rgba(0, 81, 152, 0.64);
            transform: scale(1.05);
        }

        /* Стили для сообщения об успешном комментарии (слева внизу) */
        #message {
            position: fixed;
            bottom: 20px;
            left: 20px;
            background-color: rgba(0, 81, 152, 0.64);
            padding: 15px;
            color: white;
            border-radius: 5px;
            display: none;
        }

        /* Контейнер для информации о товаре */
        .product-info-container {
            max-width: 800px;
            background-color: rgba(0, 81, 152, 0.64); /* Прозрачный синий фон */
            color: white; /* Белый цвет текста */
            padding: 20px;
            margin: 100px;
            border-radius: 10px;
            margin-top: 20px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1); /* Легкая тень */
            margin-left: 800px;
            margin-top: -646px;
        }
      
        .product-info-container p {
            font-size: 18px;
            line-height: 1.6;
            left: -100px;
        }

        .product-info-container strong {
            font-weight: bold;
        }

        /* Стили для эффекта падающего снега */
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

        @keyframes snow {
            0% { transform: translateY(0); }
            100% { transform: translateY(100vh); }
        }

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

<h1><?= htmlspecialchars($product['name']) ?></h1>
<!-- Основное изображение -->
<div class="osnov">
            <img src="images/<?= htmlspecialchars($product['main_image']) ?>" alt="Основное изображение">
        </div>
    <div class="conteiner">
        <!-- Контейнер для описания и цены -->
        <div class="product-info-container">
            <p><?= htmlspecialchars($product['description']) ?></p>
            <p><strong>Цена:</strong> <?= htmlspecialchars($product['price']) ?> руб.</p>
        </div>
    </div>

        <!-- Галерея изображений -->
    <div class="gallery">
        <?php foreach ($galleryImages as $image): ?>
        <img src="images/<?= htmlspecialchars($image['image_name']) ?>" alt="Изображение товара">
        <?php endforeach; ?>
    </div>

    <!-- Контейнер для комментариев -->
    <div class="comments-container">
        <h2>Комментарии</h2>
        <?php displayComments($comments, $pdo); ?>
    </div>

    <!-- Форма для добавления комментария -->
    <form id="commentForm">
        <input type="text" name="user_name" placeholder="Ваше имя" required>
        <textarea name="comment" placeholder="Ваш комментарий" required></textarea>
        <label for="stars">Рейтинг:</label>
        <select name="stars">
            <option value="1">1 звезда</option>
            <option value="2">2 звезды</option>
            <option value="3">3 звезды</option>
            <option value="4">4 звезды</option>
            <option value="5">5 звезд</option>
        </select>
        <button type="submit">Отправить</button>
    </form>

    <!-- Сообщение об успешном комментарии -->
    <div id="message">Комментарий успешно добавлен!</div>

    <!-- Падающие снежинки -->
    <div id="snowflakes"></div>

    <script>
        let snowflakes = document.getElementById('snowflakes');
        for (let i = 0; i < 100; i++) {
            let snowflake = document.createElement('div');
            snowflake.classList.add('snowflake');
            snowflake.innerHTML = '❄';  // Снежинка

            let startPosition = Math.random() * 100;  // Начальная позиция по оси X
            let delay = Math.random() * 5 + 's';  // Задержка начала анимации
            let animationDuration = Math.random() * 5 + 5 + 's';  // Длительность анимации

            snowflake.style.left = `${startPosition}%`;
            snowflake.style.animation = `snow ${animationDuration} linear ${delay} infinite`;

            snowflakes.appendChild(snowflake);
        }

        // Обработка отправки формы с помощью AJAX
        document.getElementById('commentForm').addEventListener('submit', function(event) {
            event.preventDefault();  // Предотвращаем перезагрузку страницы

            let formData = new FormData(this);

            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    document.getElementById('message').style.display = 'block';  // Показываем сообщение
                    setTimeout(() => {
                        document.getElementById('message').style.display = 'none';
                    }, 3000);  // Скрываем сообщение через 3 секунды
                    document.getElementById('commentForm').reset();  // Очищаем форму
                } else {
                    alert(data.message);  // Ошибка
                }
            });
        });
    </script>
</body>
</html>


