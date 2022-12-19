<!----------------treatment-------------  -->
<?php require_once('../inc/init.php') ?>

<?php

    // redirection if user is not connected as admin

    if ($_POST) {
// if the admin wants to modify an article
        if (isset($_GET['action']) && $_GET['action'] == 'modification') {
            $img_bdd = $_POST['picture'];
     }
    // var_dump($img_bdd);


    //loop to escape quotes and protect my req
    foreach ($_POST as $key => $value) {
        $_POST[$key] = htmlspecialchars(addslashes($value));
    }

    //picture upload treatment 
    if (!empty($_FILES['picture']['name'])) {

        //rename the file
        $nom_img = time() . '_' . $_FILES['picture']['name'];
        //path to save the file
        $img_doc = RACINE . "picture/$nom_img";
        //path to save in the database
        $img_bdd = URL . "picture/$nom_img";
        //picture size check if it's less than 8Mo
        if ($_FILES['picture']['size'] <= 8000000) {
            $data = pathinfo($_FILES['picture']['name'], PATHINFO_EXTENSION);
        //picture format check if it's jpg, png, jpeg
        $tab = ['jpg', 'png', 'jpeg'];
        //if picture format is correct then copy it to the folder else display error message
        if (in_array($data, $tab)) {
             copy($_FILES['picture']['tmp_name'], $img_doc);
        } else {
            $content .= '<div class="alert alert-danger" role="alert">Format non autorisé</div>';
            }
        } else {
            $content .= '<div class="alert alert-danger" role="alert">Vérifier votre image</div>';
        }
    }
        
         // if the admin wants to modify an article
    if (isset($_GET['action']) && $_GET['action'] == 'modification') {
   
        // update the article in the database
        $pdo->query("UPDATE products 
                        SET reference = '$_POST[reference]', category = '$_POST[category]', name = '$_POST[name]', description = '$_POST[description]', stock = '$_POST[stock]', picture = '$img_bdd', price = '$_POST[price]'
                        WHERE id_product = '$_POST[id_product]'");
        // redirect to the article management page
    header('location:gestion_produits.php');

        // else the admin wants to add an article
    } else {
        // add the article in the database
        $pdo->query("INSERT INTO products (reference, category, name, description, stock, picture, price) VALUES ('$_POST[reference]', '$_POST[category]', '$_POST[name]', '$_POST[description]', '$_POST[stock]', '$img_bdd', '$_POST[price]')");
    }
}

        // if the admin wants to delete an article
    if (isset($_GET['action']) && $_GET['action'] == 'suppression') {
    // delete the article in the database
    $pdo->query("DELETE FROM products WHERE id_product = '$_GET[id_product]'");
    // redirect to the article management page
    header('location:gestion_produits.php');
}

        // preapare the form to modify an article
        $r = $pdo->query("SELECT * FROM products");
        
        $content .= '<h1 class="text-center display-4 mt-5"> ' . $r->rowCount() . ' produits </h1>';

        $content .= '<table class="table table-responsive table-bordered w-75 rounded mx-auto"><tr>';
        // loop to display the table header
    for ($i = 0; $i < $r->columnCount(); $i++) {
        $colone = $r->getColumnMeta($i);
        $content .= '<th>' . $colone['name'] . '</th>';
}       // add the update and delete buttons
        $content .= '<th>Update</th>';
        $content .= '<th>Delete</th>';
        $content .= '</tr>';
         
        // loop to display the table content
    while ($row = $r->fetch(PDO::FETCH_ASSOC)) {
        $content .= '<tr>';

    foreach ($row as $key => $value) {
    if ($key == 'picture') {
            // display the picture in the table
        $content .= "<td class=\"align-middle\"><img src=\"$value\" width=\"60\"></td>";
    } else {
            // display the content in the table
        $content .= "<td class=\"align-middle\">$value </td>";
        }
    }
        // add the update and delete buttons
        $content .= "<td class=\"align-middle\"><a href=\"?action=modification&id_product=$row[id_product]\" class=\"btn btn-warning\"><i class=\"fas fa-edit\"></i>Update</a></td>";
        $content .= "<td class=\"align-middle\"><a href=\"?action=suppression&id_product=$row[id_product]\" class=\"btn btn-danger\" onclick=\"return(confirm('Etes-vous sûr de vouloir supprimer cet article ?'))\"><i class=\"fas fa-trash-alt\"></i>Delete</a></td>";

        $content .= '</tr>';
}

        $content .= '</table>';


        // if the admin wants to modify an article
    if (isset($_GET['action']) && $_GET['action'] == 'modification') {
        // get the article to modify in the database
        $r = $pdo->query("SELECT * FROM products WHERE id_product = '$_GET[id_product]'");
        // fetch the article to modify
        $actual_product = $r->fetch(PDO::FETCH_ASSOC);

}
        //display the article to modify in the form
        $id_product = (isset($actual_product['id_product'])) ? $actual_product['id_product'] : '';
        $reference = (isset($actual_product['reference'])) ? $actual_product['reference'] : '';
        $category = (isset($actual_product['category'])) ? $actual_product['category'] : '';
        $name = (isset($actual_product['name'])) ? $actual_product['name'] : '';
        $description = (isset($actual_product['description'])) ? $actual_product['description'] : '';
        $stock = (isset($actual_product['stock'])) ? $actual_product['stock'] : '';
        $picture = (isset($actual_product['picture'])) ? $actual_product['picture'] : '';
        $price = (isset($actual_product['price'])) ? $actual_product['price'] : '';
?>



<!----------------display--------------  -->

<?php require_once('header.php'); ?>

        <!-- display article content -->
        <?= $content; ?>

    <h1 class="text-center display-4">Ajouter un article</h1>
        <!-- form update and modification -->
<div class="container  mx-auto">

    <form action="" method="POST" enctype="multipart/form-data">

        <input type="hidden" name="id_product" id="id_product" value="<?= $id_product ?>">

        <label for="reference" class="form-label">Reference</label>
        <input type="text" class="form-control" id="reference" name="reference" value="<?= $reference ?>"><br>

        <label for="category" class="form-label">Catégorie</label>
        <input type="text" class="form-control" id="category" name="category" value="<?= $category ?>">

        <label for="name" class="form-label">Nom</label>
        <input type="text" class="form-control" id="name" name="name" value="<?= $name ?>"><br>
       

        <label for="description" class="form-label">description</label><br>
        <textarea name="description" id="description" cols="30" rows="10" class="form-control"><?= $description ?></textarea>

     
         
        <label for="picture" class="form-label">picture</label><br>
        <?php if (!empty($picture)) : ?>
            <img src="<?= $picture ?>" width="100px"><br>
        <?php endif; ?>
        <input type="file" class="form-control" id="picture" name="picture" ><br>
        <p><?= $picture ?></p>
        <!-- <input type="text" class="form-control" id="picture" name="picture" value="<?= $picture ?>"><br> -->
       
        <input type="hidden" name="picture" value="<?= $picture ?>">

        
    <label for="price">Prix</label>
    <input type="text" name="price" placeholder="price du produit" id="price" class="form-control" value="<?= $price ?>"><br>

    <label for="stock">Stock</label>
    <input type="text" name="stock" placeholder="stock du produit" id="stock" class="form-control" value="<?= $stock ?>"><br>

        <input type="submit" value="Ajouter" class="btn btn-warning mb-3 btn-lg w-100">

    </form>
</div>
