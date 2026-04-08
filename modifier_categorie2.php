<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Catégorie — ClientBase</title>
    <link rel="stylesheet" href="categories.css">
</head>
<body>

<?php
$conn = new mysqli("localhost", "root", "", "gestion_client");
if ($conn->connect_error) die('<div class="error-banner">Erreur de connexion.</div>');

$categorie = null;
if (isset($_GET['id'])) {
    $stmt = $conn->prepare("SELECT * FROM categorie WHERE id_categorie = ?");
    $stmt->bind_param("i", $_GET['id']);
    $stmt->execute();
    $categorie = $stmt->get_result()->fetch_assoc();
}

if (!$categorie) die('<div style="padding:40px">Catégorie introuvable. <a href="categories.php">Retour</a></div>');

if (isset($_POST['update'])) {
    $id          = (int)$_POST['id'];
    $nom         = htmlspecialchars(trim($_POST['nom']));
    $description = htmlspecialchars(trim($_POST['description']));

    $stmt = $conn->prepare("UPDATE categorie SET nom=?, description=? WHERE id_categorie=?");
    $stmt->bind_param("ssi", $nom, $description, $id);
    $stmt->execute();

    header("Location: categories.php");
    exit;
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
            <h1>Modifier Catégorie</h1>
            <p class="header-sub">Catégorie #<?= htmlspecialchars($categorie['id_categorie']) ?></p>
        </div>
        <div class="header-actions">
            <a href="categories.php" class="btn btn-ghost">← Retour</a>
        </div>
    </div>

    <div class="form-card">
        <form method="POST">
            <input type="hidden" name="id" value="<?= (int)$categorie['id_categorie'] ?>">

            <div class="form-grid">

                <div class="form-group full">
                    <label for="nom">Nom <span class="required">*</span></label>
                    <input type="text" id="nom" name="nom"
                           value="<?= htmlspecialchars($categorie['nom']) ?>" required>
                </div>

                <div class="form-group full">
                    <label for="description">Description</label>
                    <textarea id="description" name="description"><?= htmlspecialchars($categorie['description']) ?></textarea>
                </div>

            </div>

            <div class="form-actions">
                <button type="submit" name="update" class="btn btn-primary">✓ Mettre à jour</button>
                <a href="categories.php" class="btn btn-ghost">← Annuler</a>
            </div>
        </form>
    </div>

</main>

<?php $conn->close(); ?>
</body>
</html>