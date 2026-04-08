<?php
$conn = new mysqli("localhost", "root", "", "gestion_client");
if ($conn->connect_error) die("Erreur de connexion.");

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $conn->prepare("DELETE FROM facture WHERE id_facture = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

$conn->close();
header("Location: factures.php");
exit;
?>