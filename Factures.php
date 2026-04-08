<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factures — ClientBase</title>
    <link rel="stylesheet" href="factures.css">
</head>
<body>

<?php
$conn = new mysqli("localhost", "root", "", "gestion_client");
if ($conn->connect_error) die('<div class="error-banner">Erreur de connexion.</div>');

$search    = isset($_GET['search']) ? trim($_GET['search']) : '';
$hasSearch = $search !== '';

if ($hasSearch) {
    $stmt = $conn->prepare("SELECT f.*, co.date_commande, co.statut AS cmd_statut, cl.nom AS client_nom
                            FROM facture f
                            JOIN commande co ON f.id_commande = co.id_commande
                            JOIN client cl ON co.code_client = cl.code_client
                            WHERE cl.nom LIKE ?
                            ORDER BY f.date_facture DESC");
    $like = '%' . $search . '%';
    $stmt->bind_param("s", $like);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("SELECT f.*, co.date_commande, co.statut AS cmd_statut, cl.nom AS client_nom
                            FROM facture f
                            JOIN commande co ON f.id_commande = co.id_commande
                            JOIN client cl ON co.code_client = cl.code_client
                            ORDER BY f.date_facture DESC");
}

$total    = $conn->query("SELECT COUNT(*) as t FROM facture")->fetch_assoc()['t'];
$rowCount = $result->num_rows;

// Montant total toutes factures payées
$totalPaye = $conn->query("SELECT SUM(montant_total) as s FROM facture WHERE statut='payée'")->fetch_assoc()['s'] ?? 0;
$totalImpaye = $conn->query("SELECT SUM(montant_total) as s FROM facture WHERE statut='non payée'")->fetch_assoc()['s'] ?? 0;

function statutFactureBadge($statut) {
    $map = [
        'payée'      => 'badge-green',
        'non payée'  => 'badge-red',
        'en attente' => 'badge-amber',
        'annulée'    => 'badge-gray',
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
        <a href="index.php" class="nav-item"><span class="nav-icon">⊞</span> Clients</a>
        <a href="ajouter.php" class="nav-item"><span class="nav-icon">⊕</span> Ajouter</a>
        <a href="commandes.php" class="nav-item"><span class="nav-icon">◧</span> Commandes</a>
        <a href="categories.php" class="nav-item"><span class="nav-icon">⊟</span> Catégories</a>
        <a href="produits.php" class="nav-item"><span class="nav-icon">⬡</span> Produits</a>
        <a href="factures.php" class="nav-item active"><span class="nav-icon">◫</span> Factures</a>
    </nav>
    <div class="sidebar-stat">
        <span class="stat-number"><?= $total ?></span>
        <span class="stat-label">factures</span>
    </div>
</aside>

<main class="main">

    <header class="page-header">
        <div class="header-left">
            <h1>Factures</h1>
            <p class="header-sub">
                <?php if ($hasSearch): ?>
                    <?= $rowCount ?> résultat<?= $rowCount > 1 ? 's' : '' ?> pour
                    <em>"<?= htmlspecialchars($search) ?>"</em>
                <?php else: ?>
                    <?= $rowCount ?> facture<?= $rowCount > 1 ? 's' : '' ?>
                <?php endif; ?>
            </p>
        </div>
        <div class="header-actions">
            <button class="btn btn-ghost" onclick="window.print()"><span>⎙</span> Imprimer</button>
            <a href="ajouter_facture.php" class="btn btn-primary"><span>+</span> Nouvelle Facture</a>
        </div>
    </header>

    <!-- KPI Cards -->
    <div class="kpi-row">
        <div class="kpi-card kpi-green">
            <span class="kpi-label">Total Payé</span>
            <span class="kpi-value"><?= number_format($totalPaye, 2, ',', ' ') ?> <span class="kpi-currency">MAD</span></span>
        </div>
        <div class="kpi-card kpi-red">
            <span class="kpi-label">Total Impayé</span>
            <span class="kpi-value"><?= number_format($totalImpaye, 2, ',', ' ') ?> <span class="kpi-currency">MAD</span></span>
        </div>
        <div class="kpi-card kpi-blue">
            <span class="kpi-label">Total Général</span>
            <span class="kpi-value"><?= number_format($totalPaye + $totalImpaye, 2, ',', ' ') ?> <span class="kpi-currency">MAD</span></span>
        </div>
    </div>

    <div class="toolbar">
        <form method="GET" class="search-form">
            <div class="search-wrap">
                <span class="search-icon">🔍</span>
                <input type="text" name="search"
                    value="<?= htmlspecialchars($search) ?>"
                    placeholder="Rechercher par nom client…"
                    autocomplete="off">
                <?php if ($hasSearch): ?>
                    <a href="factures.php" class="search-clear" title="Effacer">✕</a>
                <?php endif; ?>
            </div>
            <button type="submit" class="btn btn-search">Rechercher</button>
        </form>
    </div>

    <?php if ($rowCount === 0): ?>
        <div class="empty-state">
            <div class="empty-icon">◎</div>
            <h3>Aucune facture trouvée</h3>
            <p>
                <?php if ($hasSearch): ?>
                    Aucun résultat pour "<?= htmlspecialchars($search) ?>".
                    <a href="factures.php">Voir toutes les factures</a>
                <?php else: ?>
                    <a href="ajouter_facture.php">Créer la première facture</a>
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
                        <th>Commande</th>
                        <th>Date Facture</th>
                        <th>Montant</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td class="cell-code"><?= htmlspecialchars($row['id_facture']) ?></td>
                        <td class="cell-name"><?= htmlspecialchars($row['client_nom']) ?></td>
                        <td class="cell-cmd">
                            <a href="details_commande.php?id=<?= (int)$row['id_commande'] ?>" class="cmd-link">
                                #<?= htmlspecialchars($row['id_commande']) ?>
                            </a>
                        </td>
                        <td class="cell-date"><?= htmlspecialchars($row['date_facture']) ?></td>
                        <td class="cell-montant">
                            <?= number_format($row['montant_total'], 2, ',', ' ') ?>
                            <span class="currency">MAD</span>
                        </td>
                        <td><?= statutFactureBadge($row['statut']) ?></td>
                        <td class="cell-actions">
                            <a href="afficher_facture.php?id=<?= (int)$row['id_facture'] ?>" class="action-btn view">⊙ Voir</a>
                            <a href="modifier_facture.php?id=<?= (int)$row['id_facture'] ?>" class="action-btn edit">✎ Éditer</a>
                            <a href="supprimer_facture.php?id=<?= (int)$row['id_facture'] ?>"
                               class="action-btn delete"
                               onclick="return confirm('Supprimer cette facture ?')">⊗ Suppr.</a>
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