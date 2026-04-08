<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Facture — ClientBase</title>
    <link rel="stylesheet" href="factures.css">
</head>
<body>

<?php
$conn = new mysqli("localhost", "root", "", "gestion_client");
if ($conn->connect_error) die('<div class="error-banner">Erreur de connexion.</div>');

$facture = null;
if (isset($_GET['id'])) {
    $stmt = $conn->prepare("SELECT * FROM facture WHERE id_facture = ?");
    $stmt->bind_param("i", $_GET['id']);
    $stmt->execute();
    $facture = $stmt->get_result()->fetch_assoc();
}

if (!$facture) die('<div style="padding:40px">Facture introuvable. <a href="factures.php">Retour</a></div>');

if (isset($_POST['update'])) {
    $id            = (int)$_POST['id'];
    $date_facture  = $_POST['date_facture'];
    $montant_total = (float)$_POST['montant_total'];
    $statut        = htmlspecialchars(trim($_POST['statut']));

    $stmt = $conn->prepare("UPDATE facture SET date_facture=?, montant_total=?, statut=? WHERE id_facture=?");
    $stmt->bind_param("sdsi", $date_facture, $montant_total, $statut, $id);
    $stmt->execute();

    header("Location: factures.php");
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
        <a href="categories.php" class="nav-item"><span class="nav-icon">⊟</span> Catégories</a>
        <a href="produits.php" class="nav-item"><span class="nav-icon">⬡</span> Produits</a>
        <a href="factures.php" class="nav-item active"><span class="nav-icon">◫</span> Factures</a>
    </nav>
</aside>

<main class="main">

    <div class="page-header">
        <div>
            <h1>Modifier Facture</h1>
            <p class="header-sub">Facture #<?= htmlspecialchars($facture['id_facture']) ?></p>
        </div>
        <div class="header-actions">
            <a href="afficher_facture.php?id=<?= (int)$facture['id_facture'] ?>" class="btn btn-ghost">⊙ Voir</a>
            <a href="factures.php" class="btn btn-ghost">← Retour</a>
        </div>
    </div>

    <div class="form-card">
        <form method="POST">
            <input type="hidden" name="id" value="<?= (int)$facture['id_facture'] ?>">

            <div class="form-grid">

                <div class="form-group full">
                    <label>Commande liée</label>
                    <div class="readonly-field">Commande #<?= htmlspecialchars($facture['id_commande']) ?></div>
                </div>

                <div class="form-group">
                    <label for="date_facture">Date Facture <span class="required">*</span></label>
                    <input type="date" id="date_facture" name="date_facture"
                           value="<?= htmlspecialchars($facture['date_facture']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="montant_total">Montant Total (MAD) <span class="required">*</span></label>
                    <input type="number" id="montant_total" name="montant_total"
                           step="0.01" min="0"
                           value="<?= htmlspecialchars($facture['montant_total']) ?>" required>
                </div>

                <div class="form-group full">
                    <label for="statut">Statut <span class="required">*</span></label>
                    <select id="statut" name="statut" required>
                        <?php foreach (['non payée', 'en attente', 'payée', 'annulée'] as $s): ?>
                            <option value="<?= $s ?>" <?= strtolower($facture['statut']) === $s ? 'selected' : '' ?>>
                                <?= ucfirst($s) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

            </div>

            <div class="form-actions">
                <button type="submit" name="update" class="btn btn-primary">✓ Mettre à jour</button>
                <a href="factures.php" class="btn btn-ghost">← Annuler</a>
            </div>
        </form>
    </div>

</main>

<?php $conn->close(); ?>
</body>
</html>