<?php
$conn = new mysqli("localhost", "root", "", "gestion_client");
if ($conn->connect_error) die("Erreur de connexion.");

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];

    // Vérifier si le produit est utilisé dans des commandes
    $check = $conn->prepare("SELECT COUNT(*) as total FROM commande_detail WHERE id_produit = ?");
    $check->bind_param("i", $id);
    $check->execute();
    $nb = $check->get_result()->fetch_assoc()['total'];

    if ($nb > 0) {
        header("Location: produits.php?error=used");
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM produit WHERE id_produit = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

$conn->close();
header("Location: produits.php");
exit;
?>