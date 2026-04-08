<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouveau Produit — ClientBase</title>
    <link rel="stylesheet" href="produits.css">
</head>
<body>

<?php
$conn = new mysqli("localhost", "root", "", "gestion_client");
if ($conn->connect_error) die('<div class="error-banner">Erreur de connexion.</div>');

$message = '';

if (isset($_POST['save'])) {
    $nom          = htmlspecialchars(trim($_POST['nom']));
    $prix         = (float)$_POST['prix'];
    $stock        = (int)$_POST['stock'];
    $id_categorie = !empty($_POST['id_categorie']) ? (int)$_POST['id_categorie'] : null;

    $stmt = $conn->prepare("INSERT INTO produit (nom, prix, stock, id_categorie) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sdii", $nom, $prix, $stock, $id_categorie);

    if ($stmt->execute()) {
        header("Location: produits.php");
        exit;
    } else {
        $message = '<div class="alert alert-error">⚠ Erreur : ' . htmlspecialchars($conn->error) . '</div>';
    }
}

$categories = $conn->query("SELECT * FROM categorie ORDER BY nom ASC");
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
        <a href="categories.php" class="nav-item"><span class="nav-icon">⊟</span> Catégories</a>
        <a href="produits.php" class="nav-item active"><span class="nav-icon">⬡</span> Produits</a>
    </nav>
</aside>

<main class="main">

    <div class="page-header">
        <div><h1>Nouveau Produit</h1></div>
        <div class="header-actions">
            <a href="produits.php" class="btn btn-ghost">← Retour</a>
        </div>
    </div>

    <?= $message ?>

    <div class="form-card">
        <form method="POST">
            <div class="form-grid">

                <div class="form-group full">
                    <label for="nom">Nom du produit <span class="required">*</span></label>
                    <input type="text" id="nom" name="nom" placeholder="Ex: Chaise de bureau, Laptop…" required>
                </div>

                <div class="form-group">
                    <label for="prix">Prix (MAD) <span class="required">*</span></label>
                    <input type="number" id="prix" name="prix" step="0.01" min="0" placeholder="0.00" required>
                </div>

                <div class="form-group">
                    <label for="stock">Stock initial <span class="required">*</span></label>
                    <input type="number" id="stock" name="stock" min="0" placeholder="0" required>
                </div>

                <div class="form-group full">
                    <label for="id_categorie">Catégorie</label>
                    <select id="id_categorie" name="id_categorie">
                        <option value="">— Sans catégorie —</option>
                        <?php while ($cat = $categories->fetch_assoc()): ?>
                            <option value="<?= (int)$cat['id_categorie'] ?>">
                                <?= htmlspecialchars($cat['nom']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

            </div>

            <div class="form-actions">
                <button type="submit" name="save" class="btn btn-primary">＋ Créer le produit</button>
                <a href="produits.php" class="btn btn-ghost">← Annuler</a>
            </div>
        </form>
    </div>

</main>

<?php $conn->close(); ?>
</body>
</html>