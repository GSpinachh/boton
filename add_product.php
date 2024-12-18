<?php
session_start();

// Проверка, что пользователь является администратором
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header("Location: login.php");
    exit;
}

try {
    // Подключение к базе данных
    $pdo = new PDO("mysql:host=localhost;dbname=user", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Функция для добавления водяного знака
    function addWatermark($imagePath, $watermarkPath, $outputPath) {
        // Проверка существования основного изображения
        if (!file_exists($imagePath)) {
            die("Основное изображение не найдено: " . $imagePath);
        }

        // Загружаем основное изображение
        $image = imagecreatefromjpeg($imagePath);
        if (!$image) {
            $image = imagecreatefrompng($imagePath);
        }
        if (!$image) {
            die("Не удалось загрузить изображение: " . $imagePath);
        }

        // Проверка существования водяного знака
        if (!file_exists($watermarkPath)) {
            die("Файл водяного знака не найден: " . $watermarkPath);
        }

        // Загружаем водяной знак
        $watermark = imagecreatefrompng($watermarkPath);
        if (!$watermark) {
            die("Не удалось загрузить водяной знак: " . $watermarkPath);
        }

        // Получаем размеры изображений
        $imageWidth = imagesx($image);
        $imageHeight = imagesy($image);
        $watermarkWidth = imagesx($watermark);
        $watermarkHeight = imagesy($watermark);

        // Определяем позицию для водяного знака
        $destX = $imageWidth - $watermarkWidth - 10;
        $destY = $imageHeight - $watermarkHeight - 10;

        // Наложение водяного знака
        imagecopy($image, $watermark, $destX, $destY, 0, 0, $watermarkWidth, $watermarkHeight);

        // Сохраняем результат
        imagejpeg($image, $outputPath);

        // Освобождаем память
        imagedestroy($image);
        imagedestroy($watermark);
    }

    // Добавление товара
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $name = $_POST['name'];
        $description = $_POST['description'];
        $price = $_POST['price'];
        $category_id = $_POST['category_id'];

        // Загрузка основного изображения
        if ($_FILES['main_image']['error'] === UPLOAD_ERR_OK) {
            $main_image_tmp = $_FILES['main_image']['tmp_name'];
            $main_image_name = time() . '_' . $_FILES['main_image']['name'];
            $main_image_size = $_FILES['main_image']['size'];
            $main_image_type = $_FILES['main_image']['type'];

            // Проверка на допустимый формат и размер
            if ($main_image_size > 5000000) {
                echo "Размер файла слишком большой!";
                exit;
            }

            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            if (!in_array($main_image_type, $allowed_types)) {
                echo "Неподдерживаемый формат изображения!";
                exit;
            }
            error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

            move_uploaded_file($main_image_tmp, 'images/' . $main_image_name);

            // Добавление водяного знака на основное изображение
            $watermarkPath = 'images/water.png'; // Путь к водяному знаку
            $outputPath = 'images/' . $main_image_name; // Путь, куда сохраняем изображение с водяным знаком

            addWatermark('images/' . $main_image_name, $watermarkPath, $outputPath);
        }

        // Вставка товара в базу
        $sql = "INSERT INTO products (name, description, price, category_id, main_image) VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$name, $description, $price, $category_id, $main_image_name]);

        // Получаем ID последнего добавленного товара
        $productId = $pdo->lastInsertId();

        // Загрузка дополнительных изображений (если есть)
        if (isset($_FILES['gallery_images'])) {
            foreach ($_FILES['gallery_images']['tmp_name'] as $key => $tmp_name) {
                if ($_FILES['gallery_images']['error'][$key] == 0) {
                    $gallery_image_name = time() . '_' . $_FILES['gallery_images']['name'][$key];
                    $gallery_image_tmp = $_FILES['gallery_images']['tmp_name'][$key];
                    $gallery_image_size = $_FILES['gallery_images']['size'][$key];
                    $gallery_image_type = $_FILES['gallery_images']['type'][$key];

                    if ($gallery_image_size > 5000000) {
                        echo "Размер изображения в галерее слишком большой!";
                        exit;
                    }

                    if (!in_array($gallery_image_type, $allowed_types)) {
                        echo "Неподдерживаемый формат изображения!";
                        exit;
                    }

                    move_uploaded_file($gallery_image_tmp, 'images/' . $gallery_image_name);

                    // Вставка в таблицу product_images
                    $sql = "INSERT INTO product_images (product_id, image_name, is_main) VALUES (?, ?, 0)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$productId, $gallery_image_name]);
                }
            }
        }

        echo "Товар успешно добавлен!";
    }

} catch (PDOException $e) {
    echo "Ошибка базы данных: " . $e->getMessage();
}
?>


<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">  
    <title>Добавление товара</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color:rgba(0, 81, 152, 0.52); /* Легкий голубой фон */
            overflow: hidden;
            position: relative;
        }

        h1 {
            text-align: center;
            color: #fff;
            margin-top: 50px;
        }

        form {
            margin: 0 auto;
            width: 60%;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            box-sizing: border-box;
        }

        label {
            display: block;
            margin-bottom: 10px;
            font-weight: bold;
            color: #333;
        }

        input, select, textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
        }

        button {
            padding: 10px 20px;
            background-color:rgba(0, 81, 152, 0.64);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 18px;
            transition: background-color 0.3s ease, transform 0.3s ease;
        }

        button:hover {
            background-color:rgba(0, 87, 179, 0.8);
            transform: scale(1.05);
        }

        /* Снегопад */
        .snowflake {
            position: absolute;
            top: -10px;
            z-index: 9999;
            color: #fff;
            font-size: 24px;
            opacity: 0.8;
            user-select: none;
            pointer-events: none;
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

        /* Создание разных размеров снежинок */
        .snowflake:nth-child(odd) {
            font-size: 18px;
            animation-duration: 12s;
        }

        .snowflake:nth-child(even) {
            font-size: 28px;
            animation-duration: 8s;
        }

        /* Размещение снежинок на экране */
        .snowflake:nth-child(1) { left: 10%; animation-duration: 10s; }
        .snowflake:nth-child(2) { left: 20%; animation-duration: 12s; }
        .snowflake:nth-child(3) { left: 30%; animation-duration: 9s; }
        .snowflake:nth-child(4) { left: 40%; animation-duration: 11s; }
        .snowflake:nth-child(5) { left: 50%; animation-duration: 10s; }
        .snowflake:nth-child(6) { left: 60%; animation-duration: 13s; }
        .snowflake:nth-child(7) { left: 70%; animation-duration: 12s; }
        .snowflake:nth-child(8) { left: 80%; animation-duration: 15s; }
        .snowflake:nth-child(9) { left: 90%; animation-duration: 14s; }

        /* Модальное окно для просмотра изображений */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.8);
        }

        .modal-content {
            margin: auto;
            display: block;
            width: 80%;
            max-width: 700px;
        }

        .close {
            position: absolute;
            top: 15px;
            right: 35px;
            color: #fff;
            font-size: 40px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover,
        .close:focus {
            color: #bbb;
            text-decoration: none;
            cursor: pointer;
        }

        .modal img {
            width: 100%;
        }

    </style>
</head>
<body>

    <h1>Добавить товар</h1>

    <!-- Кнопка выхода -->
    <form method="POST" action="logout.php" style="text-align: center; margin-bottom: 30px;">
        <button type="submit" name="logout">Выйти</button>
    </form>

    <form action="add_product.php" method="POST" enctype="multipart/form-data">
        <label for="name">Название товара:</label>
        <input type="text" name="name" id="name" required>

        <label for="description">Описание товара:</label>
        <textarea name="description" id="description" required></textarea>

        <label for="price">Цена:</label>
        <input type="number" name="price" id="price" required>

        <label for="category_id">Категория:</label>
        <select name="category_id" id="category_id">
            <?php
            // Получение категорий
            $categoriesQuery = $pdo->query("SELECT * FROM categories");
            $categories = $categoriesQuery->fetchAll(PDO::FETCH_ASSOC);
            foreach ($categories as $category):
            ?>
                <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
            <?php endforeach; ?>
        </select>

        <label for="main_image">Основное изображение:</label>
        <input type="file" name="main_image" required>

        <label for="gallery_images">Дополнительные изображения:</label>
        <input type="file" name="gallery_images[]" multiple>

        <button type="submit">Добавить товар</button>
    </form>

    <!-- Модальное окно для просмотра изображений -->
    <div id="imageModal" class="modal">
        <span class="close">&times;</span>
        <img class="modal-content" id="img01">
    </div>

    <script>
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

            // Открытие изображения в модальном окне
            const modal = document.getElementById("imageModal");
            const modalImg = document.getElementById("img01");
            const close = document.getElementsByClassName("close")[0];

            document.querySelectorAll('img').forEach(image => {
                image.onclick = function() {
                    modal.style.display = "block";
                    modalImg.src = this.src;
                }
            });

            close.onclick = function() {
                modal.style.display = "none";
            }
        });
    </script>

</body>
</html>

