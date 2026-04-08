<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails Client — ClientBase</title>
    <link rel="stylesheet" href="afficher.css">
</head>
<body>

<?php
$conn = new mysqli("localhost", "root", "", "gestion_client");


if ($conn->connect_error) {
    die('<div style="padding:40px;color:red">Erreur de connexion.</div>');
}

$client = null;
if (isset($_GET['id'])) {
    $stmt = $conn->prepare("SELECT * FROM client WHERE code_client = ?");
    $stmt->bind_param("i", $_GET['id']);
    $stmt->execute();
    $client = $stmt->get_result()->fetch_assoc();
}

if (!$client) {
    die('<div style="padding:40px">Client introuvable. <a href="index.php">Retour</a></div>');
}

function val($v, $fallback = '—') {
    return !empty(trim($v)) ? htmlspecialchars($v) : '<span class="empty">' . $fallback . '</span>';
}
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
    </nav>
</aside>

<main class="main">

    <div class="page-header">
        <h2>Détails Client</h2>
        <div class="header-actions">
            <button class="btn btn-ghost" onclick="window.print()">⎙ Imprimer</button>
            <a href="modifier.php?id=<?= (int)$client['code_client'] ?>" class="btn btn-primary">✎ Modifier</a>
        </div>
    </div>

    <div class="detail-card">

        <div class="detail-hero">
            <?php if (!empty($client['image'])): ?>
                <img
                    class="detail-avatar"
                    src="images/<?= htmlspecialchars($client['image']) ?>"
                    alt="Photo de <?= htmlspecialchars($client['nom']) ?>"
                >
            <?php else: ?>
                <div class="detail-avatar-placeholder">
                    <?= strtoupper(substr($client['nom'], 0, 1)) ?>
                </div>
            <?php endif; ?>
            <div class="detail-hero-info">
                <div class="detail-hero-name"><?= htmlspecialchars($client['nom']) ?></div>
                <div class="detail-hero-code">Client #<?= htmlspecialchars($client['code_client']) ?></div>
            </div>
        </div>

        <div class="detail-body">

            <div class="detail-field">
                <div class="detail-label">Adresse</div>
                <div class="detail-value"><?= val($client['adresse']) ?></div>
            </div>

            <div class="detail-field">
                <div class="detail-label">Code Postal</div>
                <div class="detail-value"><?= val($client['code_postal']) ?></div>
            </div>

            <div class="detail-field">
                <div class="detail-label">Registre Commerce</div>
                <div class="detail-value"><?= val($client['registre_commerce']) ?></div>
            </div>

            <div class="detail-field">
                <div class="detail-label">Patente</div>
                <div class="detail-value"><?= val($client['patente']) ?></div>
            </div>

            <?php if (!empty(trim($client['observation']))): ?>
            <div class="detail-field full">
                <div class="detail-label">Observation</div>
                <div class="detail-observation"><?= htmlspecialchars($client['observation']) ?></div>
            </div>
            <?php endif; ?>

        </div>
    </div>

    <div style="margin-top: 20px;">
        <a href="index.php" class="btn btn-ghost">← Retour à la liste</a>
    </div>

</main>

<?php $conn->close(); ?>
</body>
</html>