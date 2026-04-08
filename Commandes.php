<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Commandes — ClientBase</title>
    <link rel="stylesheet" href="commandes.css">
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
    $stmt = $conn->prepare("SELECT co.*, cl.nom FROM commande co 
                            JOIN client cl ON co.code_client = cl.code_client 
                            WHERE cl.nom LIKE ? ORDER BY co.date_commande DESC");
    $like = '%' . $search . '%';
    $stmt->bind_param("s", $like);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("SELECT co.*, cl.nom FROM commande co 
                            JOIN client cl ON co.code_client = cl.code_client 
                            ORDER BY co.date_commande DESC");
}

$total = $conn->query("SELECT COUNT(*) as t FROM commande")->fetch_assoc()['t'];
$rowCount = $result->num_rows;

// Statut badge helper
function statutBadge($statut) {
    $map = [
        'en attente'  => 'badge-amber',
        'confirmée'   => 'badge-blue',
        'livrée'      => 'badge-green',
        'annulée'     => 'badge-red',
    ];
    $class = $map[strtolower($statut)] ?? 'badge-gray';
    return '<span class="badge ' . $class . '">' . htmlspecialchars($statut) . '</span>';
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
        <a href="ajouter.php" class="nav-item">
            <span class="nav-icon">⊕</span> Ajouter
        </a>
        <a href="commandes.php" class="nav-item active">
            <span class="nav-icon">◧</span> Commandes
        </a>
        <a href="categories.php" class="nav-item"><span class="nav-icon">⊟</span> Catégories</a>
        <a href="produits.php" class="nav-item"><span class="nav-icon">⬡</span> Produits</a>
        <a href="factures.php" class="nav-item"><span class="nav-icon">◫</span> Factures</a>
    </nav>
    <div class="sidebar-stat">
        <span class="stat-number"><?= $total ?></span>
        <span class="stat-label">commandes total</span>
    </div>
</aside>

<main class="main">

    <header class="page-header">
        <div class="header-left">
            <h1>Commandes</h1>
            <p class="header-sub">
                <?php if ($hasSearch): ?>
                    <?= $rowCount ?> résultat<?= $rowCount > 1 ? 's' : '' ?> pour
                    <em>"<?= htmlspecialchars($search) ?>"</em>
                <?php else: ?>
                    <?= $rowCount ?> commande<?= $rowCount > 1 ? 's' : '' ?>
                <?php endif; ?>
            </p>
        </div>
        <div class="header-actions">
            <button class="btn btn-ghost" onclick="window.print()"><span>⎙</span> Imprimer</button>
            <a href="AjouCmd.php" class="btn btn-primary"><span>+</span> Nouvelle Commande</a>
        </div>
    </header>

    <div class="toolbar">
        <form method="GET" class="search-form">
            <div class="search-wrap">
                <span class="search-icon">🔍</span>
                <input type="text" name="search"
                    value="<?= htmlspecialchars($search) ?>"
                    placeholder="Rechercher par nom client…"
                    autocomplete="off">
                <?php if ($hasSearch): ?>
                    <a href="commandes.php" class="search-clear" title="Effacer">✕</a>
                <?php endif; ?>
            </div>
            <button type="submit" class="btn btn-search">Rechercher</button>
        </form>
    </div>

    <?php if ($rowCount === 0): ?>
        <div class="empty-state">
            <div class="empty-icon">◎</div>
            <h3>Aucune commande trouvée</h3>
            <p>
                <?php if ($hasSearch): ?>
                    Aucun résultat pour "<?= htmlspecialchars($search) ?>".
                    <a href="commandes.php">Voir toutes les commandes</a>
                <?php else: ?>
                    <a href="AjouCmd.php">Créer la première commande</a>
                <?php endif; ?>
            </p>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Client</th>
                        <th>Date</th>
                        <th>Montant</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td class="cell-code"><?= htmlspecialchars($row['id_commande']) ?></td>
                        <td class="cell-name">
                            <a href="afficher.php?id=<?= (int)$row['code_client'] ?>" class="client-link">
                                <?= htmlspecialchars($row['nom']) ?>
                            </a>
                        </td>
                        <td class="cell-date"><?= htmlspecialchars($row['date_commande']) ?></td>
                        <td class="cell-montant"><?= number_format($row['montant'], 2, ',', ' ') ?> <span class="currency">MAD</span></td>
                        <td><?= statutBadge($row['statut']) ?></td>
                        <td class="cell-actions">
                            <a href="details_commande.php?id=<?= (int)$row['id_commande'] ?>" class="action-btn view">⊙ Détails</a>
                            <a href="modifier_commande.php?id=<?= (int)$row['id_commande'] ?>" class="action-btn edit">✎ Éditer</a>
                            <a href="supprimer_commande.php?id=<?= (int)$row['id_commande'] ?>"
                               class="action-btn delete"
                               onclick="return confirm('Supprimer cette commande ?')">⊗ Suppr.</a>
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