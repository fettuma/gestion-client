<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catégories — ClientBase</title>
    <link rel="stylesheet" href="categories.css">
</head>
<body>

<?php
$conn = new mysqli("localhost", "root", "", "gestion_client");
if ($conn->connect_error) {
    die('<div class="error-banner">Erreur de connexion à la base de données.</div>');
}

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$hasSearch = $search !== '';

if ($hasSearch) {
    $stmt = $conn->prepare("SELECT c.*, COUNT(p.id_produit) as nb_produits 
                            FROM categorie c 
                            LEFT JOIN produit p ON c.id_categorie = p.id_categorie 
                            WHERE c.nom LIKE ? 
                            GROUP BY c.id_categorie ORDER BY c.nom ASC");
    $like = '%' . $search . '%';
    $stmt->bind_param("s", $like);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("SELECT c.*, COUNT(p.id_produit) as nb_produits 
                            FROM categorie c 
                            LEFT JOIN produit p ON c.id_categorie = p.id_categorie 
                            GROUP BY c.id_categorie ORDER BY c.nom ASC");
}

$total    = $conn->query("SELECT COUNT(*) as t FROM categorie")->fetch_assoc()['t'];
$rowCount = $result->num_rows;
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
        <a href="factures.php" class="nav-item"><span class="nav-icon">◫</span> Factures</a>
    </nav>
    <div class="sidebar-stat">
        <span class="stat-number"><?= $total ?></span>
        <span class="stat-label">catégories</span>
    </div>
</aside>

<main class="main">

    <header class="page-header">
        <div class="header-left">
            <h1>Catégories</h1>
            <p class="header-sub">
                <?php if ($hasSearch): ?>
                    <?= $rowCount ?> résultat<?= $rowCount > 1 ? 's' : '' ?> pour
                    <em>"<?= htmlspecialchars($search) ?>"</em>
                <?php else: ?>
                    <?= $rowCount ?> catégorie<?= $rowCount > 1 ? 's' : '' ?>
                <?php endif; ?>
            </p>
        </div>
        <div class="header-actions">
            <button class="btn btn-ghost" onclick="window.print()"><span>⎙</span> Imprimer</button>
            <a href="ajouter_categorie.php" class="btn btn-primary"><span>+</span> Nouvelle Catégorie</a>
        </div>
    </header>

    <div class="toolbar">
        <form method="GET" class="search-form">
            <div class="search-wrap">
                <span class="search-icon">🔍</span>
                <input type="text" name="search"
                    value="<?= htmlspecialchars($search) ?>"
                    placeholder="Rechercher une catégorie…"
                    autocomplete="off">
                <?php if ($hasSearch): ?>
                    <a href="categories.php" class="search-clear" title="Effacer">✕</a>
                <?php endif; ?>
            </div>
            <button type="submit" class="btn btn-search">Rechercher</button>
        </form>
    </div>

    <?php if ($rowCount === 0): ?>
        <div class="empty-state">
            <div class="empty-icon">◎</div>
            <h3>Aucune catégorie trouvée</h3>
            <p>
                <?php if ($hasSearch): ?>
                    Aucun résultat pour "<?= htmlspecialchars($search) ?>".
                    <a href="categories.php">Voir toutes les catégories</a>
                <?php else: ?>
                    <a href="ajouter_categorie.php">Créer la première catégorie</a>
                <?php endif; ?>
            </p>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nom</th>
                        <th>Description</th>
                        <th>Produits</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td class="cell-code"><?= htmlspecialchars($row['id_categorie']) ?></td>
                        <td class="cell-name"><?= htmlspecialchars($row['nom']) ?></td>
                        <td class="cell-desc">
                            <?= !empty($row['description'])
                                ? htmlspecialchars($row['description'])
                                : '<span class="empty">—</span>' ?>
                        </td>
                        <td>
                            <span class="badge badge-blue">
                                <?= (int)$row['nb_produits'] ?> produit<?= $row['nb_produits'] > 1 ? 's' : '' ?>
                            </span>
                        </td>
                        <td class="cell-actions">
                            <a href="modifier_categorie.php?id=<?= (int)$row['id_categorie'] ?>" class="action-btn edit">✎ Éditer</a>
                            <a href="supprimer_categorie.php?id=<?= (int)$row['id_categorie'] ?>"
                               class="action-btn delete"
                               onclick="return confirm('Supprimer cette catégorie ?')">⊗ Suppr.</a>
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