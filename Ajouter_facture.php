<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouvelle Facture — ClientBase</title>
    <link rel="stylesheet" href="factures.css">
</head>
<body>

<?php
$conn = new mysqli("localhost", "root", "", "gestion_client");
if ($conn->connect_error) die('<div class="error-banner">Erreur de connexion.</div>');

$message = '';

if (isset($_POST['save'])) {
    $id_commande   = (int)$_POST['id_commande'];
    $date_facture  = $_POST['date_facture'];
    $montant_total = (float)$_POST['montant_total'];
    $statut        = htmlspecialchars(trim($_POST['statut']));

    $stmt = $conn->prepare("INSERT INTO facture (id_commande, date_facture, montant_total, statut) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isds", $id_commande, $date_facture, $montant_total, $statut);

    if ($stmt->execute()) {
        header("Location: factures.php");
        exit;
    } else {
        $message = '<div class="alert alert-error">⚠ Erreur : ' . htmlspecialchars($conn->error) . '</div>';
    }
}

// Commandes sans facture
$commandes = $conn->query("SELECT co.id_commande, co.montant, co.date_commande, cl.nom
                           FROM commande co
                           JOIN client cl ON co.code_client = cl.code_client
                           WHERE co.id_commande NOT IN (SELECT id_commande FROM facture)
                           ORDER BY co.date_commande DESC");
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
        <div><h1>Nouvelle Facture</h1></div>
        <div class="header-actions">
            <a href="factures.php" class="btn btn-ghost">← Retour</a>
        </div>
    </div>

    <?= $message ?>

    <div class="form-card">
        <form method="POST">
            <div class="form-grid">

                <div class="form-group full">
                    <label for="id_commande">Commande <span class="required">*</span></label>
                    <select id="id_commande" name="id_commande" required onchange="fillMontant(this)">
                        <option value="">— Sélectionner une commande —</option>
                        <?php while ($cmd = $commandes->fetch_assoc()): ?>
                            <option value="<?= (int)$cmd['id_commande'] ?>"
                                    data-montant="<?= $cmd['montant'] ?>">
                                #<?= $cmd['id_commande'] ?> — <?= htmlspecialchars($cmd['nom']) ?>
                                (<?= htmlspecialchars($cmd['date_commande']) ?>)
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="date_facture">Date Facture <span class="required">*</span></label>
                    <input type="date" id="date_facture" name="date_facture"
                           value="<?= date('Y-m-d') ?>" required>
                </div>

                <div class="form-group">
                    <label for="montant_total">Montant Total (MAD) <span class="required">*</span></label>
                    <input type="number" id="montant_total" name="montant_total"
                           step="0.01" min="0" placeholder="0.00" required>
                </div>

                <div class="form-group full">
                    <label for="statut">Statut <span class="required">*</span></label>
                    <select id="statut" name="statut" required>
                        <option value="non payée">Non payée</option>
                        <option value="en attente">En attente</option>
                        <option value="payée">Payée</option>
                        <option value="annulée">Annulée</option>
                    </select>
                </div>

            </div>

            <div class="form-actions">
                <button type="submit" name="save" class="btn btn-primary">＋ Créer la facture</button>
                <a href="factures.php" class="btn btn-ghost">← Annuler</a>
            </div>
        </form>
    </div>

</main>

<script>
function fillMontant(select) {
    const opt     = select.options[select.selectedIndex];
    const montant = opt.dataset.montant;
    if (montant) {
        document.getElementById('montant_total').value = parseFloat(montant).toFixed(2);
    }
}
</script>

<?php $conn->close(); ?>
</body>
</html>