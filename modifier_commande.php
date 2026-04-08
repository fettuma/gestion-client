<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Commande — ClientBase</title>
    <link rel="stylesheet" href="commandes.css">
</head>
<body>

<?php
$conn = new mysqli("localhost", "root", "", "gestion_client");
if ($conn->connect_error) die('<div class="error-banner">Erreur de connexion.</div>');

$commande = null;
if (isset($_GET['id'])) {
    $stmt = $conn->prepare("SELECT * FROM commande WHERE id_commande = ?");
    $stmt->bind_param("i", $_GET['id']);
    $stmt->execute();
    $commande = $stmt->get_result()->fetch_assoc();
}

if (!$commande) die('<div style="padding:40px">Commande introuvable. <a href="commandes.php">Retour</a></div>');

if (isset($_POST['update'])) {
    $id            = (int)$_POST['id'];
    $code_client   = (int)$_POST['code_client'];
    $date_commande = $_POST['date_commande'];
    $montant       = (float)$_POST['montant'];
    $statut        = htmlspecialchars(trim($_POST['statut']));

    $stmt = $conn->prepare("UPDATE commande SET code_client=?, date_commande=?, montant=?, statut=? WHERE id_commande=?");
    $stmt->bind_param("isdsi", $code_client, $date_commande, $montant, $statut, $id);
    $stmt->execute();

    header("Location: commandes.php");
    exit;
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
            <h1>Modifier Commande</h1>
            <p class="header-sub">Commande #<?= htmlspecialchars($commande['id_commande']) ?></p>
        </div>
        <div class="header-actions">
            <a href="commandes.php" class="btn btn-ghost">← Retour</a>
        </div>
    </div>

    <div class="form-card">
        <form method="POST">
            <input type="hidden" name="id" value="<?= (int)$commande['id_commande'] ?>">

            <div class="form-grid">

                <div class="form-group full">
                    <label for="code_client">Client <span class="required">*</span></label>
                    <select id="code_client" name="code_client" required>
                        <?php while ($cl = $clients->fetch_assoc()): ?>
                            <option value="<?= (int)$cl['code_client'] ?>"
                                <?= $cl['code_client'] == $commande['code_client'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cl['nom']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="date_commande">Date <span class="required">*</span></label>
                    <input type="date" id="date_commande" name="date_commande"
                           value="<?= htmlspecialchars($commande['date_commande']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="montant">Montant (MAD) <span class="required">*</span></label>
                    <input type="number" id="montant" name="montant"
                           step="0.01" min="0"
                           value="<?= htmlspecialchars($commande['montant']) ?>" required>
                </div>

                <div class="form-group full">
                    <label for="statut">Statut <span class="required">*</span></label>
                    <select id="statut" name="statut" required>
                        <?php
                        $statuts = ['en attente', 'confirmée', 'livrée', 'annulée'];
                        foreach ($statuts as $s):
                        ?>
                            <option value="<?= $s ?>" <?= strtolower($commande['statut']) === $s ? 'selected' : '' ?>>
                                <?= ucfirst($s) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

            </div>

            <div class="form-actions">
                <button type="submit" name="update" class="btn btn-primary">✓ Mettre à jour</button>
                <a href="commandes.php" class="btn btn-ghost">← Annuler</a>
            </div>
        </form>
    </div>

</main>

<?php $conn->close(); ?>
</body>
</html>