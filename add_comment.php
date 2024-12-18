<?php
include('../includes/db.php');

$content = $_POST['content'];
$product_id = $_POST['product_id'];
$parent_id = $_POST['parent_id'];
$author = 'Гость'; // В реальном приложении будет авторизация

$query = "INSERT INTO comments (product_id, author, content, parent_id) VALUES (:product_id, :author, :content, :parent_id)";
$stmt = $pdo->prepare($query);
$stmt->execute(['product_id' => $product_id, 'author' => $author, 'content' => $content, 'parent_id' => $parent_id]);

header("Location: product.php?id=$product_id");
exit;
?>
