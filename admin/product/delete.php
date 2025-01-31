<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/admin/includes/fonction.php";

require_once $_SERVER['DOCUMENT_ROOT'] . "/admin/includes/protect.php";

require_once $_SERVER['DOCUMENT_ROOT'] . "/admin/includes/connect.php";

// Si on a pas de token on redirige directement vers l'index évitant une faille CSRF
if (!isset($_GET["token"])){
    redirect('/admin/product/index.php');
}


if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $stmt = $db->prepare("DELETE FROM table_product 
                            WHERE product_id=:id");
    $stmt->execute([":id" => $_GET['id']]);
}

header("Location:index.php");
