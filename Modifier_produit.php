<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Produit — ClientBase</title>
    <link rel="stylesheet" href="produits.css">
</head>
<body>

<?php
$conn = new mysqli("localhost", "root", "", "gestion_client");
if ($conn->connect_error) die('<div class="error-banner">Erreur de connexion.</div>');

$produit = null;
if (isset($_GET['id'])) {
    $stmt = $conn->prepare("SELECT * FROM produit WHERE id_produit = ?");
    $stmt->bind_param("i", $_GET['id']);
    $stmt->execute();
    $produit = $stmt->get_result()->fetch_assoc();
}

if (!$produit) die('<div style="padding:40px">Produit introuvable. <a href="produits.php">Retour</a></div>');

if (isset($_POST['update'])) {
    $id           = (int)$_POST['id'];
    $nom          = htmlspecialchars(trim($_POST['nom']));
    $prix         = (float)$_POST['prix'];
    $stock        = (int)$_POST['stock'];
    $id_categorie = !empty($_POST['id_categorie']) ? (int)$_POST['id_categorie'] : null;

    $stmt = $conn->prepare("UPDATE produit SET nom=?, prix=?, stock=?, id_categorie=? WHERE id_produit=?");
    $stmt->bind_param("sdiii", $nom, $prix, $stock, $id_categorie, $id);
    $stmt->execute();

    header("Location: produits.php");
    exit;
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
        <div>
            <h1>Modifier Produit</h1>
            <p class="header-sub">Produit #<?= htmlspecialchars($produit['id_produit']) ?></p>
        </div>
        <div class="header-actions">
            <a href="produits.php" class="btn btn-ghost">← Retour</a>
        </div>
    </div>

    <div class="form-card">
        <form method="POST">
            <input type="hidden" name="id" value="<?= (int)$produit['id_produit'] ?>">

            <div class="form-grid">

                <div class="form-group full">
                    <label for="nom">Nom du produit <span class="required">*</span></label>
                    <input type="text" id="nom" name="nom"
                           value="<?= htmlspecialchars($produit['nom']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="prix">Prix (MAD) <span class="required">*</span></label>
                    <input type="number" id="prix" name="prix"
                           step="0.01" min="0"
                           value="<?= htmlspecialchars($produit['prix']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="stock">Stock <span class="required">*</span></label>
                    <input type="number" id="stock" name="stock"
                           min="0"
                           value="<?= (int)$produit['stock'] ?>" required>
                </div>

                <div class="form-group full">
                    <label for="id_categorie">Catégorie</label>
                    <select id="id_categorie" name="id_categorie">
                        <option value="">— Sans catégorie —</option>
                        <?php while ($cat = $categories->fetch_assoc()): ?>
                            <option value="<?= (int)$cat['id_categorie'] ?>"
                                <?= $cat['id_categorie'] == $produit['id_categorie'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['nom']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

            </div>

            <div class="form-actions">
                <button type="submit" name="update" class="btn btn-primary">✓ Mettre à jour</button>
                <a href="produits.php" class="btn btn-ghost">← Annuler</a>
            </div>
        </form>
    </div>

</main>

<?php $conn->close(); ?>
</body>
</html>