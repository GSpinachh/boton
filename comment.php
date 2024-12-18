<?php
// Подключение к базе данных
$pdo = new PDO("mysql:host=localhost;dbname=user", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Добавление комментария
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $productId = $_POST['product_id'];
    $userName = $_POST['user_name'];
    $comment = $_POST['comment'];

    $sql = "INSERT INTO comments (product_id, user_name, comment) VALUES (?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$productId, $userName, $comment]);

    header("Location: product.php?id=" . $productId);
    exit;
}
?>
