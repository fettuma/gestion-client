<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion Clients</title>
    <link rel="stylesheet" href="index.css">
</head>
<body>

<?php
$conn = new mysqli("localhost", "root", "", "gestion_client");
// pour connecter php avec mysql (server,user,password,dbname)

if ($conn->connect_error) {
    die('<div class="error-banner">Erreur de connexion à la base de données.</div>');
}

// Secure search with prepared statement
$search = isset($_GET['search']) ? trim($_GET['search']) : '';    //the value entered by the user
$hasSearch = $search !== '';   //to know if the user is searching or not

if ($hasSearch) {
    $stmt = $conn->prepare("SELECT * FROM client WHERE nom LIKE ? ORDER BY nom ASC");  //$conn->prepare secure from sql injection
    $like = '%' . $search . '%'; // means the search is partial juz2ii Al Ali Alia..
    $stmt->bind_param("s", $like);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("SELECT * FROM client ORDER BY nom ASC");  // sorted by names ABC...
}

$totalClients = $conn->query("SELECT COUNT(*) as total FROM client")->fetch_assoc()['total'];
$rowCount = $result->num_rows;
?>

<aside class="sidebar">
    <div class="sidebar-brand">
        <span class="brand-icon">◈</span>
        <span class="brand-name">ClientBase</span>
    </div>
    <nav class="sidebar-nav">
        <a href="index.php" class="nav-item active">
            <span class="nav-icon">⊞</span> Clients
        </a>
        <a href="ajouter.php" class="nav-item">
            <span class="nav-icon">⊕</span> Ajouter
        </a>
        <a href="commandes.php" class="nav-item">
            <span class="nav-icon">◧</span> Commandes
        </a>
        <a href="categories.php" class="nav-item"><span class="nav-icon">⊟</span> Catégories</a>
        <a href="produits.php" class="nav-item"><span class="nav-icon">⬡</span> Produits</a>
        <a href="factures.php" class="nav-item"><span class="nav-icon">◫</span> Factures</a>
    </nav>
    <div class="sidebar-stat">
        <span class="stat-number"><?= $totalClients ?></span>
        <span class="stat-label">clients total</span>
    </div>
</aside>

<main class="main">

    <header class="page-header">
        <div class="header-left">
            <h1>Système de gestion de la clientèle</h1>
            <p class="header-sub">
                <?php if ($hasSearch): ?>
                    <?= $rowCount ?> résultat<?= $rowCount > 1 ? 's' : '' ?> pour
                    <em>"<?= htmlspecialchars($search) ?>"</em>
                <?php else: ?>
                    <?= $rowCount ?> client<?= $rowCount > 1 ? 's' : '' ?> enregistré<?= $rowCount > 1 ? 's' : '' ?>
                <?php endif; ?>
            </p>
        </div>
        <div class="header-actions">
            <button class="btn btn-ghost" onclick="window.print()">
                <span>⎙</span> Imprimer
            </button>
            <a href="ajouter.php" class="btn btn-primary">
                <span>+</span> Nouveau Client
            </a>
        </div>
    </header>

    <div class="toolbar">
        <form method="GET" class="search-form">
            <div class="search-wrap">
                <span class="search-icon">🔍</span>
                <input
                    type="text"
                    name="search"
                    value="<?= htmlspecialchars($search) ?>"
                    placeholder="Rechercher un client par nom…"
                    autocomplete="off"
                >
                <?php if ($hasSearch): ?>
                    <a href="index.php" class="search-clear" title="Effacer">✕</a>
                <?php endif; ?>
            </div>
            <button type="submit" class="btn btn-search">Rechercher</button>
        </form>
    </div>

    <?php if ($rowCount === 0): ?>
        <div class="empty-state">
            <div class="empty-icon">◎</div>
            <h3>Aucun client trouvé</h3>
            <p>
                <?php if ($hasSearch): ?>
                    Aucun résultat pour "<?= htmlspecialchars($search) ?>".
                    <a href="index.php">Voir tous les clients</a>
                <?php else: ?>
                    Commencez par <a href="ajouter.php">ajouter un client</a>.
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
                        <th>Adresse</th>
                        <th>Photo</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td class="cell-code"><?= htmlspecialchars($row['code_client']) ?></td>
                        <td class="cell-name">
                            <span class="name-text"><?= htmlspecialchars($row['nom']) ?></span>
                        </td>
                        <td class="cell-addr">
                            <?= htmlspecialchars($row['adresse']) ?>
                        </td>
                        <td class="cell-img">
                            <?php if (!empty($row['image'])): ?>
                                <img
                                    src="images/<?= htmlspecialchars($row['image']) ?>"
                                    alt="Photo de <?= htmlspecialchars($row['nom']) ?>"
                                    loading="lazy"
                                >
                            <?php else: ?>
                                <div class="img-placeholder">
                                    <?= strtoupper(substr($row['nom'], 0, 1)) ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td class="cell-actions">
                            <a href="afficher.php?id=<?= (int)$row['code_client'] ?>" class="action-btn view" title="Afficher">
                                ⊙ Voir
                            </a>
                            <a href="commandes.php?client=<?= (int)$row['code_client'] ?>" class="action-btn view">
                                ◧ Cmds
                            </a>
                            <a href="modifier.php?id=<?= (int)$row['code_client'] ?>" class="action-btn edit" title="Modifier">
                                ✎ Éditer
                            </a>
                            <a
                                href="supprimer.php?id=<?= (int)$row['code_client'] ?>"
                                class="action-btn delete"
                                title="Supprimer"
                                onclick="return confirm('Supprimer ce client définitivement ?')"
                            >
                                ⊗ Suppr.
                            </a>
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