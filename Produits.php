<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produits — ClientBase</title>
    <link rel="stylesheet" href="produits.css">
</head>
<body>

<?php
$conn = new mysqli("localhost", "root", "", "gestion_client");
if ($conn->connect_error) {
    die('<div class="error-banner">Erreur de connexion à la base de données.</div>');
}

$search    = isset($_GET['search']) ? trim($_GET['search']) : '';
$hasSearch = $search !== '';

if ($hasSearch) {
    $stmt = $conn->prepare("SELECT p.*, c.nom AS categorie_nom 
                            FROM produit p 
                            LEFT JOIN categorie c ON p.id_categorie = c.id_categorie 
                            WHERE p.nom LIKE ? 
                            ORDER BY p.nom ASC");
    $like = '%' . $search . '%';
    $stmt->bind_param("s", $like);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("SELECT p.*, c.nom AS categorie_nom 
                            FROM produit p 
                            LEFT JOIN categorie c ON p.id_categorie = c.id_categorie 
                            ORDER BY p.nom ASC");
}

$total    = $conn->query("SELECT COUNT(*) as t FROM produit")->fetch_assoc()['t'];
$rowCount = $result->num_rows;

function stockBadge($stock) {
    if ($stock <= 0)  return '<span class="badge badge-red">Rupture</span>';
    if ($stock <= 5)  return '<span class="badge badge-amber">Stock bas</span>';
    return '<span class="badge badge-green">En stock</span>';
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
        <a href="categories.php" class="nav-item"><span class="nav-icon">⊟</span> Catégories</a>
        <a href="produits.php" class="nav-item active"><span class="nav-icon">⬡</span> Produits</a>
        <a href="factures.php" class="nav-item"><span class="nav-icon">◫</span> Factures</a>
    </nav>
    <div class="sidebar-stat">
        <span class="stat-number"><?= $total ?></span>
        <span class="stat-label">produits</span>
    </div>
</aside>

<main class="main">

    <header class="page-header">
        <div class="header-left">
            <h1>Produits</h1>
            <p class="header-sub">
                <?php if ($hasSearch): ?>
                    <?= $rowCount ?> résultat<?= $rowCount > 1 ? 's' : '' ?> pour
                    <em>"<?= htmlspecialchars($search) ?>"</em>
                <?php else: ?>
                    <?= $rowCount ?> produit<?= $rowCount > 1 ? 's' : '' ?>
                <?php endif; ?>
            </p>
        </div>
        <div class="header-actions">
            <button class="btn btn-ghost" onclick="window.print()"><span>⎙</span> Imprimer</button>
            <a href="ajouter_produit.php" class="btn btn-primary"><span>+</span> Nouveau Produit</a>
        </div>
    </header>

    <div class="toolbar">
        <form method="GET" class="search-form">
            <div class="search-wrap">
                <span class="search-icon">🔍</span>
                <input type="text" name="search"
                    value="<?= htmlspecialchars($search) ?>"
                    placeholder="Rechercher un produit…"
                    autocomplete="off">
                <?php if ($hasSearch): ?>
                    <a href="produits.php" class="search-clear" title="Effacer">✕</a>
                <?php endif; ?>
            </div>
            <button type="submit" class="btn btn-search">Rechercher</button>
        </form>
    </div>

    <?php if ($rowCount === 0): ?>
        <div class="empty-state">
            <div class="empty-icon">◎</div>
            <h3>Aucun produit trouvé</h3>
            <p>
                <?php if ($hasSearch): ?>
                    Aucun résultat pour "<?= htmlspecialchars($search) ?>".
                    <a href="produits.php">Voir tous les produits</a>
                <?php else: ?>
                    <a href="ajouter_produit.php">Ajouter le premier produit</a>
                <?php endif; ?>
            </p>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Produit</th>
                        <th>Catégorie</th>
                        <th>Prix</th>
                        <th>Stock</th>
                        <th>État</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td class="cell-code"><?= htmlspecialchars($row['id_produit']) ?></td>
                        <td class="cell-name"><?= htmlspecialchars($row['nom']) ?></td>
                        <td class="cell-cat">
                            <?php if (!empty($row['categorie_nom'])): ?>
                                <span class="badge badge-blue"><?= htmlspecialchars($row['categorie_nom']) ?></span>
                            <?php else: ?>
                                <span class="empty">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="cell-prix">
                            <?= number_format($row['prix'], 2, ',', ' ') ?>
                            <span class="currency">MAD</span>
                        </td>
                        <td class="cell-stock"><?= (int)$row['stock'] ?></td>
                        <td><?= stockBadge($row['stock']) ?></td>
                        <td class="cell-actions">
                            <a href="modifier_produit.php?id=<?= (int)$row['id_produit'] ?>" class="action-btn edit">✎ Éditer</a>
                            <a href="supprimer_produit.php?id=<?= (int)$row['id_produit'] ?>"
                               class="action-btn delete"
                               onclick="return confirm('Supprimer ce produit ?')">⊗ Suppr.</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

</main>

<?php $conn->close(); ?>
</body>
</html>