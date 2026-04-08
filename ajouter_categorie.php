<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouvelle Catégorie — ClientBase</title>
    <link rel="stylesheet" href="categories.css">
</head>
<body>

<?php
$conn = new mysqli("localhost", "root", "", "gestion_client");
if ($conn->connect_error) die('<div class="error-banner">Erreur de connexion.</div>');

$message = '';

if (isset($_POST['save'])) {
    $nom         = htmlspecialchars(trim($_POST['nom']));
    $description = htmlspecialchars(trim($_POST['description']));

    $stmt = $conn->prepare("INSERT INTO categorie (nom, description) VALUES (?, ?)");
    $stmt->bind_param("ss", $nom, $description);

    if ($stmt->execute()) {
        header("Location: categories.php");
        exit;
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
        <a href="index.php" class="nav-item"><span class="nav-icon">⊞</span> Clients</a>
        <a href="ajouter.php" class="nav-item"><span class="nav-icon">⊕</span> Ajouter</a>
        <a href="commandes.php" class="nav-item"><span class="nav-icon">◧</span> Commandes</a>
        <a href="categories.php" class="nav-item active"><span class="nav-icon">⊟</span> Catégories</a>
        <a href="produits.php" class="nav-item"><span class="nav-icon">⬡</span> Produits</a>
    </nav>
</aside>

<main class="main">

    <div class="page-header">
        <div>
            <h1>Nouvelle Catégorie</h1>
        </div>
        <div class="header-actions">
            <a href="categories.php" class="btn btn-ghost">← Retour</a>
        </div>
    </div>

    <?= $message ?>

    <div class="form-card">
        <form method="POST">
            <div class="form-grid">

                <div class="form-group full">
                    <label for="nom">Nom <span class="required">*</span></label>
                    <input type="text" id="nom" name="nom" placeholder="Ex: Électronique, Mobilier…" required>
                </div>

                <div class="form-group full">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" placeholder="Description de la catégorie…"></textarea>
                </div>

            </div>

            <div class="form-actions">
                <button type="submit" name="save" class="btn btn-primary">＋ Créer la catégorie</button>
                <a href="categories.php" class="btn btn-ghost">← Annuler</a>
            </div>
        </form>
    </div>

</main>

<?php $conn->close(); ?>
</body>
</html>