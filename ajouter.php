<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter Client — ClientBase</title>
    <link rel="stylesheet" href="ajouter.css">
</head>
<body>

<?php
$conn = new mysqli("localhost", "root", "", "gestion_client");
if ($conn->connect_error) {
    die('<div class="alert alert-error">⚠ Erreur de connexion à la base de données.</div>');
}

$message = '';

if (isset($_POST['save'])) {
    $nom               = htmlspecialchars(trim($_POST['nom']));
    $adresse           = htmlspecialchars(trim($_POST['adresse']));
    $code_postal       = htmlspecialchars(trim($_POST['code_postal']));
    $registre_commerce = htmlspecialchars(trim($_POST['registre_commerce']));
    $patente           = htmlspecialchars(trim($_POST['patente']));
    $observation       = htmlspecialchars(trim($_POST['observation']));

    $image = '';
    if (!empty($_FILES['image']['name'])) {
        $image = basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], "images/" . $image);
    }

    $stmt = $conn->prepare("INSERT INTO client (nom, adresse, code_postal, registre_commerce, patente, observation, image) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $nom, $adresse, $code_postal, $registre_commerce, $patente, $observation, $image);

    if ($stmt->execute()) {
        $message = '<div class="alert alert-success">✓ Client <strong>' . $nom . '</strong> ajouté avec succès.</div>';
    } else {
        $message = '<div class="alert alert-error">⚠ Erreur : ' . htmlspecialchars($conn->error) . '</div>';
    }
}
?>

<aside class="sidebar">
    <div class="sidebar-brand">
        <span class="brand-icon">◈</span>
        <span class="brand-name">ClientBase</span>
    </div>
    <nav class="sidebar-nav">
        <a href="index.php" class="nav-item">
            <span class="nav-icon">⊞</span> Clients
        </a>
        <a href="ajouter.php" class="nav-item active">
            <span class="nav-icon">⊕</span> Ajouter
        </a>
         <a href="commandes.php" class="nav-item">
            <span class="nav-icon">◧</span> Commandes
        </a>
        <a href="categories.php" class="nav-item"><span class="nav-icon">⊟</span> Catégories</a>
        <a href="produits.php" class="nav-item"><span class="nav-icon">⬡</span> Produits</a>
        <a href="factures.php" class="nav-item"><span class="nav-icon">◫</span> Factures</a>
    </nav>
</aside>

<main class="main">

    <div class="page-header">
        <h2>Nouveau Client</h2>
    </div>

    <?= $message ?>

    <div class="form-card">
        <form method="POST" enctype="multipart/form-data">
            <div class="form-grid">

                <div class="form-group full">
                    <label for="nom">Nom <span class="required">*</span></label>
                    <input type="text" id="nom" name="nom" placeholder="Nom du client" required>
                </div>

                <div class="form-group full">
                    <label for="adresse">Adresse</label>
                    <input type="text" id="adresse" name="adresse" placeholder="Adresse complète">
                </div>

                <div class="form-group">
                    <label for="code_postal">Code Postal</label>
                    <input type="text" id="code_postal" name="code_postal" placeholder="Ex: 80000">
                </div>

                <div class="form-group">
                    <label for="registre_commerce">Registre Commerce</label>
                    <input type="text" id="registre_commerce" name="registre_commerce" placeholder="N° RC">
                </div>

                <div class="form-group">
                    <label for="patente">Patente</label>
                    <input type="text" id="patente" name="patente" placeholder="N° Patente">
                </div>

                <div class="form-group">
                    <label for="image">Photo</label>
                    <input type="file" id="image" name="image" accept="image/*">
                </div>

                <div class="form-group full">
                    <label for="observation">Observation</label>
                    <textarea id="observation" name="observation" placeholder="Notes ou remarques…"></textarea>
                </div>

            </div>

            <div class="form-actions">
                <button type="submit" name="save" class="btn btn-primary">
                    ＋ Ajouter le client
                </button>
                <a href="index.php" class="btn btn-ghost">
                    ← Retour
                </a>
            </div>
        </form>
    </div>

</main>

<?php $conn->close(); ?>
</body>
</html>