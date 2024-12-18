<?php
session_start();

// Уничтожаем все данные сессии
session_unset();

// Уничтожаем сессию
session_destroy();

// Перенаправляем на страницу входа
header("Location: login.php");
exit;
?>
