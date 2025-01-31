<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/admin/includes/fonction.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/admin/includes/protect.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/admin/includes/connect.php";

if (!isset($_POST["token"])) {
    redirect('/admin/product/index.php');
}

if (isset($_POST["sent"]) && $_POST["sent"] == "ok") {


    // Petit bout de code qui permet de recuperer les images dans un fichier 
    // var_dump($_FILES['product_image']);
    // move_uploaded_file($_FILES["product_image"]['tmp_name'],$_SERVER['DOCUMENT_ROOT']. "/upload/images/".$_FILES["product_image"]["name"]);


    // if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] !== UPLOAD_ERR_NO_FILE) {
    //     $resultat = securiseImage($_FILES['product_image']);

    //     if (!$resultat['success']) {
    //         // Si l'upload a échoué, on redirige avec l'erreur
    //         header("Location:index.php?error=" . urlencode($resultat['message']));
    //         exit();
    //     }
    //     // Si succès, on utilise le nom du fichier retourné par securiseImage
    //     $_POST['product_image'] = $resultat['nom_fichier'];
    // }


    // Si on a pas de product id, donc c'est a dire on est la pour un ajout et non une modification
    if ($_POST["product_id"] == 0) {
        // On prepare la requete d'ajout a la base de données a partir du formulaire recuperer grace a la method POST
        $stmt = $db->prepare("INSERT INTO table_product 
        (product_name,
        product_price,
        product_serie,
        product_volume,
        product_description,
        product_stock,
        product_publisher,
        product_author,
        product_cartoonist,
        product_resume,
        product_date,
        product_status,
        product_type_id)
        
         VALUES (:product_name,
          :product_price,
           :product_serie,
           :product_volume,
           :product_description,
           :product_stock,
           :product_publisher,
           :product_author,
           :product_cartoonist,
           :product_resume,
           :product_date,
           :product_status,
           :product_type_id)");

        $stmt->bindValue(":product_name", $_POST["product_name"]);
        $stmt->bindValue(":product_price", $_POST["product_price"]);
        $stmt->bindValue(":product_serie", $_POST["product_serie"]);
        $stmt->bindValue(":product_volume", $_POST["product_volume"]);
        $stmt->bindValue(":product_description", $_POST["product_description"]);
        $stmt->bindValue(":product_stock", $_POST["product_stock"]);
        $stmt->bindValue(":product_publisher", $_POST["product_publisher"]);
        $stmt->bindValue(":product_author", $_POST["product_author"]);
        $stmt->bindValue(":product_cartoonist", $_POST["product_cartoonist"]);
        $stmt->bindValue(":product_resume", $_POST["product_resume"]);
        $stmt->bindValue(":product_date", $_POST["product_date"]);
        $stmt->bindValue(":product_status", $_POST["product_status"]);
        $stmt->bindValue(":product_type_id", $_POST["type_id"]);
        $stmt->execute();

        // ici on stock le le dernier ID qu'on a inserer dans la table avec "lastInsertId()" pour pouvoir l'utiliser en bas 
        $productID = $db->lastInsertId();


        // On prepare la requete d'ajout a la table table product, qui va faire le lien entre notre product ID et sa catégorie ID pour les lier ensemble 
        $stmt2 = $db->prepare("INSERT INTO table_product_category (product_category_product_id,product_category_category_id) VALUES(:product_category_product_id,:product_category_category_id)");
        // ici on utilise le $productID initié en haut pour pouvoir l'assigne dans la table table_product_category qui va faire le lien entre l'id et la catégorie du produit (voir BDD)
        $stmt2->bindValue(":product_category_category_id", $_POST["category_id"]);
        $stmt2->bindValue(":product_category_product_id", $productID);
        $stmt2->execute();
    } else {
        $stmt = $db->prepare("UPDATE table_product
        SET product_name=:product_name, 
            product_price=:product_price, 
            product_serie=:product_serie, 
            product_volume=:product_volume,
            product_description=:product_description, 
            product_stock=:product_stock, 
            product_publisher=:product_publisher, 
            product_author=:product_author,
            product_cartoonist=:product_cartoonist,
            product_resume=:product_resume,
            product_date=:product_date, 
            product_status=:product_status,
            product_type_id=:product_type_id
        WHERE product_id=:product_id
        ");

        $stmt->bindValue(":product_id", $_POST["product_id"]);
        $stmt->bindValue(":product_name", $_POST["product_name"]);
        $stmt->bindValue(":product_price", $_POST["product_price"]);
        $stmt->bindValue(":product_serie", $_POST["product_serie"]);
        $stmt->bindValue(":product_volume", $_POST["product_volume"]);
        $stmt->bindValue(":product_description", $_POST["product_description"]);
        $stmt->bindValue(":product_stock", $_POST["product_stock"]);
        $stmt->bindValue(":product_publisher", $_POST["product_publisher"]);
        $stmt->bindValue(":product_author", $_POST["product_author"]);
        $stmt->bindValue(":product_cartoonist", $_POST["product_cartoonist"]);
        $stmt->bindValue(":product_resume", $_POST["product_resume"]);
        $stmt->bindValue(":product_date", $_POST["product_date"]);
        $stmt->bindValue(":product_status", $_POST["product_status"]);
        $stmt->bindValue(":product_type_id", $_POST["type_id"]);
        $stmt->execute();


        // ici il faut update aussi ta table associative
        $stmt2 = $db->prepare("UPDATE table_product_category SET product_category_category_id=:product_category_category_id, product_category_product_id=:product_category_product_id 
                                WHERE product_category_product_id=:product_category_product_id");
        $stmt2->bindValue(":product_category_category_id", $_POST["category_id"]);
        $stmt2->bindValue(":product_category_product_id", $_POST["product_id"]);
        $stmt2->execute();
    }

    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0) {
        // Appel de la fonction securiseImage
        $resultat = securiseImage($_FILES['product_image']);

        if ($resultat['success']) {
            $id = $_POST['product_id'] > 0 ? $_POST['product_id'] : $productID;

            // Suppression de l'ancienne image si elle existe
            if ($_POST['product_id'] > 0) {
                $stmt = $db->prepare('SELECT product_image FROM table_product 
                                WHERE product_id =:product_id');
                $stmt->execute([':product_id' => $id]);
                if ($row = $stmt->fetch()) {
                    if ($row['product_image'] != "" && !is_null($row['product_image'])) {
                        if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/upload/images/' . $row["product_image"])) {
                            unlink($_SERVER['DOCUMENT_ROOT'] . '/upload/images/' . $row["product_image"]);
                        }
                    }
                }
            }

            // Mise à jour de la base de données avec le nouveau nom d'image
            $stmt = $db->prepare("UPDATE table_product SET product_image=:product_image
                            WHERE product_id=:product_id");
            $stmt->execute([
                ":product_image" => $resultat['nom_fichier'],
                ":product_id" => $id
            ]);
        } else {
            // Gestion des erreurs
            $_SESSION['error'] = $resultat['message'];
            header('Location: add.php');
            exit;
        }
    }
}

header("Location:index.php");
