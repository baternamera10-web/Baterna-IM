<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: ../login.php"); exit; }
require_once "../connection.php";
require_once "../classes/product.php";

$productObj = new Product($conn);

$id = $_GET['id'] ?? null;
if ($id) {
    $productObj->delete($id);
}

header("Location: ../index.php");
exit;
?>