<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails Commande — ClientBase</title>
    <link rel="stylesheet" href="details_commande.css">
</head>
<body>

<?php
$conn = new mysqli("localhost", "root", "", "gestion_client");
if ($conn->connect_error) die('<div class="error-banner">Erreur de connexion.</div>');

// Récupérer la commande
$commande = null;
if (!isset($_GET['id'])) {
    header("Location: commandes.php");
    exit;
}

$id_commande = (int)$_GET['id'];

$stmt = $conn->prepare("SELECT co.*, cl.nom AS client_nom 
                        FROM commande co 
                        JOIN client cl ON co.code_client = cl.code_client 
                        WHERE co.id_commande = ?");
$stmt->bind_param("i", $id_commande);
$stmt->execute();
$commande = $stmt->get_result()->fetch_assoc();

if (!$commande) die('<div style="padding:40px">Commande introuvable. <a href="commandes.php">Retour</a></div>');

// Ajouter un produit à la commande
$message = '';
if (isset($_POST['add_detail'])) {
    $id_produit    = (int)$_POST['id_produit'];
    $quantite      = (int)$_POST['quantite'];

    // Récupérer le prix du produit
    $pStmt = $conn->prepare("SELECT prix, stock FROM produit WHERE id_produit = ?");
    $pStmt->bind_param("i", $id_produit);
    $pStmt->execute();
    $produit = $pStmt->get_result()->fetch_assoc();

    if (!$produit) {
        $message = '<div class="alert alert-error">⚠ Produit introuvable.</div>';
    } elseif ($quantite > $produit['stock']) {
        $message = '<div class="alert alert-error">⚠ Stock insuffisant. Stock disponible : ' . (int)$produit['stock'] . '</div>';
    } else {
        $prix_unitaire = $produit['prix'];

        // Vérifier si le produit existe déjà dans cette commande
        $cStmt = $conn->prepare("SELECT id FROM commande_detail WHERE id_commande = ? AND id_produit = ?");
        $cStmt->bind_param("ii", $id_commande, $id_produit);
        $cStmt->execute();
        $exists = $cStmt->get_result()->fetch_assoc();

        if ($exists) {
            $message = '<div class="alert alert-error">⚠ Ce produit est déjà dans la commande.</div>';
        } else {
            $iStmt = $conn->prepare("INSERT INTO commande_detail (id_commande, id_produit, quantite, prix_unitaire) VALUES (?, ?, ?, ?)");
            $iStmt->bind_param("iiid", $id_commande, $id_produit, $quantite, $prix_unitaire);
            $iStmt->execute();

            // Mettre à jour le stock
            $uStmt = $conn->prepare("UPDATE produit SET stock = stock - ? WHERE id_produit = ?");
            $uStmt->bind_param("ii", $quantite, $id_produit);
            $uStmt->execute();

            // Recalculer montant total de la commande
            $conn->query("UPDATE commande SET montant = (
                SELECT SUM(quantite * prix_unitaire) FROM commande_detail WHERE id_commande = $id_commande
            ) WHERE id_commande = $id_commande");

            $message = '<div class="alert alert-success">✓ Produit ajouté avec succès.</div>';
        }
    }
}

// Supprimer un détail
if (isset($_GET['delete_detail'])) {
    $id_detail = (int)$_GET['delete_detail'];

    // Récupérer la quantité pour restaurer le stock
    $dStmt = $conn->prepare("SELECT id_produit, quantite FROM commande_detail WHERE id = ?");
    $dStmt->bind_param("i", $id_detail);
    $dStmt->execute();
    $detail = $dStmt->get_result()->fetch_assoc();

    if ($detail) {
        $conn->prepare("DELETE FROM commande_detail WHERE id = ?")->execute() ;
        $del = $conn->prepare("DELETE FROM commande_detail WHERE id = ?");
        $del->bind_param("i", $id_detail);
        $del->execute();

        // Restaurer le stock
        $rStmt = $conn->prepare("UPDATE produit SET stock = stock + ? WHERE id_produit = ?");
        $rStmt->bind_param("ii", $detail['quantite'], $detail['id_produit']);
        $rStmt->execute();

        // Recalculer montant
        $conn->query("UPDATE commande SET montant = COALESCE((
            SELECT SUM(quantite * prix_unitaire) FROM commande_detail WHERE id_commande = $id_commande
        ), 0) WHERE id_commande = $id_commande");
    }

    header("Location: details_commande.php?id=$id_commande");
    exit;
}

// Récupérer les détails actuels
$details = $conn->query("SELECT cd.*, p.nom AS produit_nom, p.stock AS stock_dispo
                         FROM commande_detail cd
                         JOIN produit p ON cd.id_produit = p.id_produit
                         WHERE cd.id_commande = $id_commande");

// Récupérer les produits disponibles (non encore ajoutés à cette commande)
$produits = $conn->query("SELECT * FROM produit 
                          WHERE stock > 0 
                          AND id_produit NOT IN (
                              SELECT id_produit FROM commande_detail WHERE id_commande = $id_commande
                          )
                          ORDER BY nom ASC");

// Calcul total
$totalResult = $conn->query("SELECT SUM(quantite * prix_unitaire) as total FROM commande_detail WHERE id_commande = $id_commande");
$total = $totalResult->fetch_assoc()['total'] ?? 0;

function statutBadge($statut) {
    $map = ['en attente' => 'badge-amber', 'confirmée' => 'badge-blue', 'livrée' => 'badge-green', 'annulée' => 'badge-red'];
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
        <a href="commandes.php" class="nav-item active"><span class="nav-icon">◧</span> Commandes</a>
        <a href="categories.php" class="nav-item"><span class="nav-icon">⊟</span> Catégories</a>
        <a href="produits.php" class="nav-item"><span class="nav-icon">⬡</span> Produits</a>
        <a href="factures.php" class="nav-item"><span class="nav-icon">◫</span> Factures</a>
    </nav>
</aside>

<main class="main">

    <!-- En-tête commande -->
    <div class="page-header">
        <div>
            <h1>Commande #<?= $id_commande ?></h1>
            <p class="header-sub">
                <?= htmlspecialchars($commande['client_nom']) ?> &nbsp;·&nbsp;
                <?= htmlspecialchars($commande['date_commande']) ?> &nbsp;·&nbsp;
                <?= statutBadge($commande['statut']) ?>
            </p>
        </div>
        <div class="header-actions">
            <button class="btn btn-ghost" onclick="window.print()"><span>⎙</span> Imprimer</button>
            <a href="commandes.php" class="btn btn-ghost">← Retour</a>
        </div>
    </div>

    <?= $message ?>

    <div class="layout">

        <!-- Tableau des produits de la commande -->
        <div class="section">
            <div class="section-header">
                <h2>Produits de la commande</h2>
            </div>

            <?php if ($details->num_rows === 0): ?>
                <div class="empty-state">
                    <div class="empty-icon">◎</div>
                    <h3>Aucun produit ajouté</h3>
                    <p>Ajoutez des produits depuis le formulaire ci-dessous.</p>
                </div>
            <?php else: ?>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Produit</th>
                                <th>Prix unitaire</th>
                                <th>Quantité</th>
                                <th>Sous-total</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $details->fetch_assoc()): ?>
                            <tr>
                                <td class="cell-name"><?= htmlspecialchars($row['produit_nom']) ?></td>
                                <td class="cell-prix">
                                    <?= number_format($row['prix_unitaire'], 2, ',', ' ') ?>
                                    <span class="currency">MAD</span>
                                </td>
                                <td class="cell-qty"><?= (int)$row['quantite'] ?></td>
                                <td class="cell-sous-total">
                                    <?= number_format($row['quantite'] * $row['prix_unitaire'], 2, ',', ' ') ?>
                                    <span class="currency">MAD</span>
                                </td>
                                <td>
                                    <a href="details_commande.php?id=<?= $id_commande ?>&delete_detail=<?= (int)$row['id'] ?>"
                                       class="action-btn delete"
                                       onclick="return confirm('Retirer ce produit ?')">⊗ Retirer</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="total-label">Total</td>
                                <td class="total-value">
                                    <?= number_format($total, 2, ',', ' ') ?>
                                    <span class="currency">MAD</span>
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- Formulaire ajout produit -->
        <?php if ($produits->num_rows > 0): ?>
        <div class="section">
            <div class="section-header">
                <h2>Ajouter un produit</h2>
            </div>
            <div class="form-card">
                <form method="POST">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="id_produit">Produit <span class="required">*</span></label>
                            <select id="id_produit" name="id_produit" required onchange="updatePrix(this)">
                                <option value="">— Sélectionner —</option>
                                <?php while ($p = $produits->fetch_assoc()): ?>
                                    <option value="<?= (int)$p['id_produit'] ?>"
                                            data-prix="<?= $p['prix'] ?>"
                                            data-stock="<?= $p['stock'] ?>">
                                        <?= htmlspecialchars($p['nom']) ?>
                                        (<?= number_format($p['prix'], 2, ',', ' ') ?> MAD — stock: <?= $p['stock'] ?>)
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="quantite">Quantité <span class="required">*</span></label>
                            <input type="number" id="quantite" name="quantite" min="1" value="1" required>
                            <span class="field-hint" id="stock-hint"></span>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" name="add_detail" class="btn btn-primary">＋ Ajouter</button>
                    </div>
                </form>
            </div>
        </div>
        <?php else: ?>
        <div class="section">
            <div class="info-banner">
                ℹ Tous les produits disponibles ont déjà été ajoutés à cette commande.
                <a href="produits.php">Gérer les produits</a>
            </div>
        </div>
        <?php endif; ?>

    </div>

</main>

<script>
function updatePrix(select) {
    const opt   = select.options[select.selectedIndex];
    const stock = opt.dataset.stock;
    const hint  = document.getElementById('stock-hint');
    const qtyInput = document.getElementById('quantite');

    if (stock !== undefined) {
        hint.textContent = 'Stock disponible : ' + stock;
        qtyInput.max = stock;
    } else {
        hint.textContent = '';
        qtyInput.removeAttribute('max');
    }
}
</script>

<?php $conn->close(); ?>
</body>
</html>