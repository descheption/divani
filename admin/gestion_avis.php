<?php require_once('header.php'); ?>

<?php require_once('../inc/init.php'); ?>



<?php
// Verifier si la SESSION['membre'] existe et est admin sinon il n'aura pas accès au backoffice donc je le renvoi s'il n'a pas les autorisations nécéssaire
// if (!userIsAdmin()) {
//     header('location:../index.php');
//     exit();
// }


if ($_POST) {

    // Si nous sommes dans le cas d'une modification alors on garde la photo d'origine
    //photo_actuelle est le name de la photo d'origine dans l'input hidden 
    if (isset($_GET['action']) && $_GET['action'] == 'modification') {
        $img_bdd = $_POST['photo_actuelle'];
    }

    // Faire une addslashes sur tous les champs pour éviter les apostrophes sinon cela va générer une erreur lors de l'enrégistrement
    // Pour tous les $_POST as key=>value alors $_POST[$key]= htmlspecialchars(addslashes($value))
    foreach ($_POST as $key => $value) {
        $_POST[$key] = htmlspecialchars(addslashes($value));
    }

    // si la photo n'est pas vide je récupère les infos de la photo grâce à la superglobale $_FILES
    if (!empty($_FILES['photo']['name'])) {

        // Je crée le nom de la photo en concatenant le timestamp (time()) avec la référence produit fournit et le nom de l'image ajouté . J'ai fait cela afin d'éviter que plusieurs produits aient le même nom car le time() sera toujours diférent même si le nom de l'image est le même

        $nom_img = time() . '_' . $_POST['reference'] . '_' . $_FILES['photo']['name'];

        // j'ajoute les chemins pour l'ajout des photos

        // chemin 1 - vers le dossier photo . Pour cela je vais utilisé la constante RACINE qui est dans mon  fichier init
        $img_doc = RACINE . "photo/$nom_img";

        // chemin 2 - vers la bdd . Pour cela je vais utilisé la constante URL qui est dans mon fichier init
        $img_bdd = URL . "photo/$nom_img";


        if ($_FILES['photo']['size'] <= 8000000) { // Si la taille de la photo est inférieur ou égale à 8Mo

            //Je récupère l'extension de l'image
            $data = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);

            //$ext = $data['extension'];

            // Je déclare un tableau d'extension autorisées
            $tab = ['jpg', 'png', 'jpeg'];

            // Si l'extension de l'image de l'image se trouve dans le tableau d'extension avec in_array()
            if (in_array($data, $tab)) {

                // Enfin pour enrégistrer la photo nous allons utiliser fonction copy()
                // copy() Fait une copie du fichier from vers le fichier to.
                // Copy prend 2 arguments 1: le nom temmporaire $_FILES['photo']['tmp_name'] et 2: l'emplacement où la photo sera mise $photo_dossier (dans le dossier photo de ma boutique)
                copy($_FILES['photo']['tmp_name'], $img_doc);

            } else { // Sinon le format ne se trouve pas dans mon tableau de format autorisé
                $content .= '<div class="alert alert-danger" role="alert">Format non autorisé</div>';
            }
        } else { // Sinon la taille est supérieur à 8Mo

            $content .= '<div class="alert alert-danger" role="alert">Vérifier taille de votre image</div>';
        }
    }


    // Si nous sommes dans le cas d'une modification
    if (isset($_GET['action']) && $_GET['action'] == 'modification') {

        $pdo->query("UPDATE avis 
                        SET id_user = '$_POST[id_user]', post_author = '$_POST[post_author]', post_title = '$_POST[post_title]', post_date = '$_POST[post_date]', post_content = '$_POST[post_content]'
                        WHERE id_avis = '$_POST[id_avis]'");

        header('location:gestion_avis.php'); // je renvoi l'user sur le même fichier pour ne pas avoir infos du produit qui vient d'être update par défaut dans l'url

    } else { //Sinon nous sommes dans le cas d'un ajout

        // J'ajoute une image par défaut à mon produit

        if (empty($img_bdd)) {// Si $img_bdd est vide alors il n'y a pas d'image

            $img_bdd = ''; // Ceci est une image par défaut que j'ai ajouté dans ma bdd

            $content .= '<div class="alert alert-info" role="alert text-center">Vous avez une image par défaut pour le produit</div>';
        }

        $pdo->query("INSERT INTO avis (id_user, post_author, post_title, post_date, post_content) VALUES ('$_POST[id_user]', '$_POST[post_author]', '$_POST[post_title]', '$_POST[post_date]', '$_POST[post_content]')");
    }
}



// s'il y a une action dans l'url et que cette action = suppression alors je fais une requête pour supprimer le produit

if (isset($_GET['action']) && $_GET['action'] == 'suppression') {

    $pdo->query("DELETE 
                    FROM avis
                    WHERE id_avis = '$_GET[id_avis]'");

    header('location:gestion_avis.php'); // je renvoi l'user sur le même fichier pour ne pas avoir infos du produit qui vient d'être delete par défaut dans l'url
}


// Requete pour avoir tous les produits de la bdd

$r = $pdo->query("SELECT * FROM avis");

// rowCount() renvoie le nombre de lignes affectées par la dernière instruction (donc le nombre produits)
if($r->rowCount() >1){ // Si j'ai plus de 1 produit dans ma boutique j'affiche Liste des n produits
    $content .= '<h1 class="text-center display-4">Liste des ' . $r->rowCount() . ' avis de la boutique</h1>';
}else{ //Sinon j'affiche Liste de 1 produit
    $content .= '<h1 class="text-center display-4">Liste de ' . $r->rowCount() . ' avis de la boutique</h1>';
}




$content .= '<table class="table table-striped"><tr>';
// Faire une boucle pour afficher les produits dans la table

// tant que $i est inférieur à la liste des colones alors on va tourner autant de fois qu'il y a de colone à afficher

for ($i = 0; $i < $r->columnCount(); $i++) {

    //getColumnMeta() Récupère les métadonnées d'une colonne indexée à 0 dans un jeu de résultats sous la forme d'un tableau associatif.

    $colone = $r->getColumnMeta($i); // Récupérer les infos de chaque colone (les en-tête) . Tout le résultat sera dans un tableau

    $content .= '<th>' . $colone['name'] . '</th>';
}

$content .= '<th>Update</th>';
$content .= '<th>Delete</th>';

$content .= '</tr>';


// tant qu'il y a des lignes ( des données à afficher) Je vais récupérer le contenu de chaque ligne
while ($row = $r->fetch(PDO::FETCH_ASSOC)) {
    $content .= '<tr>';

    foreach ($row as $key => $value) {
        if ($key == 'photo') {
            $content .= "<td class=\"align-middle\"><img src=\"$value\" width=\"60\"></td>";
        } else {
            // Ici je coupe la longueur du texte qui sera affiché avec substr()
            $value = substr($value, 0, 100);
            $content .= "<td class=\"align-middle\">$value</td>";
        }
    }

    $content .= "<td class=\"align-middle\"><a href=?action=modification&id_avis=$row[id_avis]>update</a></td>";
    $content .= "<td class=\"align-middle\"><a href=?action=suppression&id_avis=$row[id_avis]>Delete</a></td>";

    $content .= '</tr>';
}

$content .= '</table>';

$content .= '<hr><hr><hr>';


// s'il y a une action dans l'url et que cette action = modification alors je fais une requête pour modifierr le produit

if (isset($_GET['action']) && $_GET['action'] == 'modification') {

    $r = $pdo->query("SELECT *
                    FROM avis 
                    WHERE id_avis = '$_GET[id_avis]'");

    $actual_avis = $r->fetch(PDO::FETCH_ASSOC);
}

// Utiliser les conditions ternaire pour pré-remplir les champs 

// si l'id produit_actuel existe alors alors je le récupère sinon je mets rien dans la
$id_avis = (isset($actual_avis['id_avis'])) ? $actual_avis['id_avis'] : '';
$id_user = (isset($actual_avis['id_user'])) ? $actual_avis['id_user'] : '';
$post_author = (isset($actual_avis['post_author'])) ? $actual_avis['post_author'] : '';
$post_title = (isset($actual_avis['post_title'])) ? $actual_avis['post_title'] : '';
$post_date = (isset($actual_avis['post_date'])) ? $actual_avis['post_date'] : '';
$post_content = (isset($actual_avis['post_content'])) ? $actual_avis['post_content'] : '';



?>




<!-----------------------PARTIE AFFICHAGE -------------------------------->



<?= $content; ?>

<h1 class="text-center display-4">GESTION DES AVIS</h1>

<!--Je modifie l'affichage du titre h3 afin d'afficher Modification si on clic sur modifier et Ajout par défaut a faire pour la partie 4-------->

<?php if (isset($_GET['action']) && $_GET['action'] == 'modification') : ?>
    <h4 class="text-center display-4  text-warning">Modification d'un avis</h4>
<?php else : ?>
    <h4 class="text-center display-4">Ajouter un avis</h4>
<?php endif ?>

<link rel="stylesheet" href="../style.css">

<form method="post" action="" enctype="multipart/form-data">
    <!--- Je récupère l'id du produit que je veux modifier dans un input hidden-------->
    <input type="hidden" name="id_avis" value="<?= $id_avis?>">

    <!----Pour tous les inputs je vais pré-remplir l'attribut value avec le resultat issu de ma condtion ternaire------->
    <label for="post_author">Auteur</label>
    <input type="text" name="post_author" placeholder="Auteur de l'avis" id="post_author" class="form-control" value="<?= $post_author ?>"><br>

    <label for="post_title">Titre</label>
    <input type="text" name="post_title" placeholder="Titre de l'avis" id="post_title" class="form-control" value="<?= $post_title ?>"><br>

    <label for="post_date">Date</label>
    <input type="date" name="post_date" placeholder="Date de l'avis" id="post_date" class="form-control" value="<?= $post_date ?>"><br>

    <label for="post_content">Contenu du commentaire</label>
    <textarea name="post_content" placeholder="Commentaire" id="post_content" class="form-control"><?= $post_content ?></textarea><br>

  



    <!----Si la photo n'est pas vide (le cas ou le produit a déjà une photo----->
  
    <br>



    <div class="text-center mb-5">
        <?php if (isset($_GET['action']) && $_GET['action'] == 'modification') : ?>
            <br><input type="submit" value="Valider la modification" class="btn btn-lg btn-info">
        <?php else : ?>
            <br><input type="submit" value="Ajouter l'avis" class="btn btn-lg btn-primary">
        <?php endif ?>
    </div>


</form>


