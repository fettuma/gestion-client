<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Supprimer Commande — ClientBase</title>
</head>
<body>
<?php
$conn = new mysqli("localhost", "root", "", "gestion_client");
if ($conn->connect_error) die("Erreur de connexion.");

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $conn->prepare("DELETE FROM commande WHERE id_commande = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

$conn->close();
header("Location: commandes.php");
exit;
?>
</body>
</html>