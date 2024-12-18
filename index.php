<?php
// Подключение к базе данных
$pdo = new PDO("mysql:host=localhost;dbname=user", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Получение всех категорий
$categoriesQuery = $pdo->query("SELECT * FROM categories WHERE parent_id IS NULL");
$categories = $categoriesQuery->fetchAll(PDO::FETCH_ASSOC);

// Получение ID выбранной категории, если оно есть
$categoryId = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;

// Если выбрана категория, получаем товары для этой категории
if ($categoryId > 0) {
    $productsQuery = $pdo->prepare("SELECT * FROM products WHERE category_id = ?");
    $productsQuery->execute([$categoryId]);
} else {
    // Если категория не выбрана, показываем все товары
    $productsQuery = $pdo->query("SELECT * FROM products");
}

$products = $productsQuery->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Каталог товаров</title>

    <!-- Добавим новогодний стиль -->
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color:rgba(0, 81, 152, 0.52); /* Легкий голубой фон */
            color: white;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
            position: relative;
        }

        h1 {
            text-align: center;
            padding: 24px;
            background-color:rgba(0, 81, 152, 0.92);
            margin: 0;
            color: white;
        }

        nav ul {
            list-style-type: none;
            padding: 0;
            text-align: center;
            margin: 20px 0;
        }

        nav ul li {
            display: inline-block;
            margin: 10px;
        }

        nav ul li a {
            text-decoration: none;
            color: #fff;
            font-size: 18px;
            padding: 10px 20px;
            background-color:rgba(0, 81, 152, 0.64);
            border-radius: 5px;
            transition: background-color 0.3s ease, transform 0.3s ease;
        }

        nav ul li a:hover {
            background-color:rgba(0, 87, 179, 0.8);
            transform: scale(1.05);
        }

        .products {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
            padding: 30px;
        }

        .product-card {
            background-color:rgba(0, 81, 152, 0.64);
            padding: 20px;
            width: 250px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: transform 0.3s ease-in-out;
        }

        .product-card:hover {
            transform: translateY(-10px);
        }

        .product-card img {
            width: 100%;
            border-radius: 8px;
            margin-bottom: 15px;
            max-height: 200px;
            object-fit: cover;
        }

        .product-card h2 {
            font-size: 20px;
        }

        .product-card p {
            font-size: 16px;
            color: #ccc;
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

        /* Эффект для снежинок */
        @keyframes fall {
            0% {
                transform: translateY(0);
            }
            100% {
                transform: translateY(100vh);
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

    <h1>Каталог товаров</h1>

    <!-- Навигация по категориям -->
    <nav>
        <ul>
            <li><a href="index.php">Все товары</a></li>
            <?php foreach ($categories as $category): ?>
                <li><a href="index.php?category_id=<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></a></li>
            <?php endforeach; ?>
        </ul>
    </nav>

    <!-- Список товаров -->
    <div class="products">
        <?php if (count($products) > 0): ?>
            <?php foreach ($products as $product): ?>
                <div class="product-card">
                    <h2><a href="product.php?id=<?= $product['id'] ?>" style="color: white;"><?= htmlspecialchars($product['name']) ?></a></h2>
                    <p><?= htmlspecialchars($product['description']) ?></p>
                    <p>Цена: <?= $product['price'] ?> руб.</p>
                    <?php
                    // Получаем основное изображение
                    $sqlMainImage = "SELECT image_name FROM product_images WHERE product_id = ? AND is_main = 1";
                    $stmtMainImage = $pdo->prepare($sqlMainImage);
                    $stmtMainImage->execute([$product['id']]);
                    $mainImage = $stmtMainImage->fetchColumn();

                   
                    ?>
                   <img src="images/<?= htmlspecialchars($product['main_image']) ?>" alt="Основное изображение">
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
            <p>Товары не найдены.</p>
        <?php endif; ?>
    </div>

    <!-- Снегопад -->
    <div id="snowflakes"></div>

    <script>
        // Создание снежинок для эффекта снегопада
        let snowflakes = document.getElementById('snowflakes');

        for (let i = 0; i < 100; i++) {
            let snowflake = document.createElement('div');
            snowflake.classList.add('snowflake');
            snowflake.innerHTML = '❄';  // Снежинка

            let startPosition = Math.random() * 100;  // Начальная позиция по оси X
            let delay = Math.random() * 5 + 's';  // Задержка начала анимации
            let animationDuration = Math.random() * 5 + 5 + 's';  // Длительность анимации

            snowflake.style.left = `${startPosition}%`;
            snowflake.style.animation = `fall ${animationDuration} linear ${delay} infinite`;

            snowflakes.appendChild(snowflake);
        }
    </script>

</body>
</html>


