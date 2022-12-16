<?php

function userConnected()
{
    if (!isset($_SESSION['membre'])) {
        return false;
    } else {
        return true;
    }
}



function userIsAdmin()
{
    if (userConnected() && $_SESSION['membre']['statut'] == 1) {
        return true;
    } else {
        return false;
    }
}


// Fonction de creation de la session panier
function creation_panier()
{
    if (!isset($_SESSION['panier'])) { // S'il n'y a pas de session panier alors je crée ma session qui va contenir l'id,qtt et le prix

        $_SESSION['panier'] = array();
        $_SESSION['panier']['id_produit'] = array();
        $_SESSION['panier']['quantite'] = array();
        $_SESSION['panier']['prix'] = array();
    }
}

// Fonction d'ajout au panier
function ajoutProduit($id_produit, $quantite, $prix) // Qui prend id,qtt et le prix
{
    creation_panier(); // J'execute la function de creation lors de l'ajout

    //Je vérifie si le produit est déjà dans a session panier 
    // array_search() Recherche dans un tableau la première clé associée à la valeur

    $position = array_search($id_produit, $_SESSION['panier']['id_produit']);

    if ($position !== false) { // Si le produit est trouvé alors j'incrémente la qtt

        $_SESSION['panier']['quantite'][$position]  += $quantite;
    } else { // Sinon je l'ajoute comme un nouveau produit. [] à la fin permet d'ajouter un nouveau produit et non écrasser le produit qui était dans le panier

        $_SESSION['panier']['id_produit'][] = $id_produit;
        $_SESSION['panier']['quantite'][] = $quantite;
        $_SESSION['panier']['prix'][] = $prix;
    }
}

// Cette fonction calcul le montant total
function montantTotal(){
    $total = 0;

    for($i=0; $i < count($_SESSION['panier']['id_produit']);$i++){
        $total += $_SESSION['panier']['quantite'][$i] * $_SESSION['panier']['prix'][$i]; 
    }
    return $total;
}


function retireProduit($id_produit){

    // Je cherche la position du produit dans le panier
    $positionProduit = array_search($id_produit, $_SESSION['panier']['id_produit']);

    // array_splice() remplace une portion d'un tableau
    // En mode 1 remplace et reclasse les éléments dans le tableau
    array_splice($_SESSION['panier']['id_produit'],$positionProduit,1);
    array_splice($_SESSION['panier']['quantite'],$positionProduit,1);
    array_splice($_SESSION['panier']['prix'],$positionProduit,1);

}