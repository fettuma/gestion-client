<?php
$conn = new mysqli("localhost", "root", "", "gestion_client");
if ($conn->connect_error) die("Erreur de connexion.");

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];

    // Vérifier si des produits utilisent cette catégorie
    $check = $conn->prepare("SELECT COUNT(*) as total FROM produit WHERE id_categorie = ?");
    $check->bind_param("i", $id);
    $check->execute();
    $nb = $check->get_result()->fetch_assoc()['total'];

    if ($nb > 0) {
        // Redirige avec message d'erreur
        header("Location: categories.php?error=used");
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM categorie WHERE id_categorie = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

$conn->close();
header("Location: categories.php");
exit;
?>