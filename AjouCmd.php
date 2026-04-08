<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouvelle Commande — ClientBase</title>
    <link rel="stylesheet" href="commandes.css">
</head>
<body>

<?php
$conn = new mysqli("localhost", "root", "", "gestion_client");
if ($conn->connect_error) {
    die('<div class="error-banner">Erreur de connexion.</div>');
}

$message = '';

if (isset($_POST['save'])) {
    $code_client       = (int)$_POST['code_client'];
    $date_commande     = $_POST['date_commande'];
    $montant           = (float)$_POST['montant'];
    $statut            = htmlspecialchars(trim($_POST['statut']));

    $stmt = $conn->prepare("INSERT INTO commande (code_client, date_commande, montant, statut) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isds", $code_client, $date_commande, $montant, $statut);

    if ($stmt->execute()) {
        header("Location: commandes.php");
        exit;
    } else {
        $message = '<div class="alert alert-error">⚠ Erreur : ' . htmlspecialchars($conn->error) . '</div>';
    }
}

$clients = $conn->query("SELECT code_client, nom FROM client ORDER BY nom ASC");
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
    </nav>
</aside>

<main class="main">

    <div class="page-header">
        <div>
            <h1>Nouvelle Commande</h1>
        </div>
        <div class="header-actions">
            <a href="commandes.php" class="btn btn-ghost">← Retour</a>
        </div>
    </div>

    <?= $message ?>

    <div class="form-card">
        <form method="POST">
            <div class="form-grid">

                <div class="form-group full">
                    <label for="code_client">Client <span class="required">*</span></label>
                    <select id="code_client" name="code_client" required>
                        <option value="">— Sélectionner un client —</option>
                        <?php while ($cl = $clients->fetch_assoc()): ?>
                            <option value="<?= (int)$cl['code_client'] ?>">
                                <?= htmlspecialchars($cl['nom']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="date_commande">Date <span class="required">*</span></label>
                    <input type="date" id="date_commande" name="date_commande"
                           value="<?= date('Y-m-d') ?>" required>
                </div>

                <div class="form-group">
                    <label for="montant">Montant (MAD) <span class="required">*</span></label>
                    <input type="number" id="montant" name="montant"
                           step="0.01" min="0" placeholder="0.00" required>
                </div>

                <div class="form-group full">
                    <label for="statut">Statut <span class="required">*</span></label>
                    <select id="statut" name="statut" required>
                        <option value="en attente">En attente</option>
                        <option value="confirmée">Confirmée</option>
                        <option value="livrée">Livrée</option>
                        <option value="annulée">Annulée</option>
                    </select>
                </div>

            </div>

            <div class="form-actions">
                <button type="submit" name="save" class="btn btn-primary">＋ Créer la commande</button>
                <a href="commandes.php" class="btn btn-ghost">← Annuler</a>
            </div>
        </form>
    </div>

</main>

<?php $conn->close(); ?>
</body>
</html>