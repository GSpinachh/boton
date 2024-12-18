<?php
include('../includes/db.php');

$category_id = $_GET['id'];
$query = "SELECT * FROM products WHERE category_id = :category_id";
$stmt = $pdo->prepare($query);
$stmt->execute(['category_id' => $category_id]);
$products = $stmt->fetchAll();

include('../includes/header.php');
?>

<h1>Товары в категории</h1>

<div class="products">
    <?php foreach ($products as $product): ?>
        <div class="product">
            <img src="images/<?php echo $product['main_image']; ?>" alt="<?php echo $product['name']; ?>">
            <h3><?php echo $product['name']; ?></h3>
            <p><?php echo $product['description']; ?></p>
            <a href="product.php?id=<?php echo $product['id']; ?>">Подробнее</a>
        </div>
    <?php endforeach; ?>
</div>

<?php include('../includes/footer.php'); ?>
