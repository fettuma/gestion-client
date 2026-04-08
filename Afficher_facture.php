<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facture — ClientBase</title>
    <link rel="stylesheet" href="factures.css">
</head>
<body>

<?php
$conn = new mysqli("localhost", "root", "", "gestion_client");
if ($conn->connect_error) die('<div class="error-banner">Erreur de connexion.</div>');

$facture = null;
if (isset($_GET['id'])) {
    $stmt = $conn->prepare("SELECT f.*, co.date_commande, co.statut AS cmd_statut, co.code_client,
                                   cl.nom, cl.adresse, cl.code_postal, cl.registre_commerce, cl.patente
                            FROM facture f
                            JOIN commande co ON f.id_commande = co.id_commande
                            JOIN client cl ON co.code_client = cl.code_client
                            WHERE f.id_facture = ?");
    $stmt->bind_param("i", $_GET['id']);
    $stmt->execute();
    $facture = $stmt->get_result()->fetch_assoc();
}

if (!$facture) die('<div style="padding:40px">Facture introuvable. <a href="factures.php">Retour</a></div>');

// Détails produits de la commande
$details = $conn->query("SELECT cd.*, p.nom AS produit_nom
                         FROM commande_detail cd
                         JOIN produit p ON cd.id_produit = p.id_produit
                         WHERE cd.id_commande = " . (int)$facture['id_commande']);
?>

<aside class="sidebar no-print">
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

    <div class="page-header no-print">
        <div>
            <h1>Facture #<?= htmlspecialchars($facture['id_facture']) ?></h1>
        </div>
        <div class="header-actions">
            <button class="btn btn-ghost" onclick="window.print()"><span>⎙</span> Imprimer</button>
            <a href="modifier_facture.php?id=<?= (int)$facture['id_facture'] ?>" class="btn btn-primary">✎ Modifier</a>
            <a href="factures.php" class="btn btn-ghost">← Retour</a>
        </div>
    </div>

    <!-- Document Facture imprimable -->
    <div class="invoice">

        <div class="invoice-header">
            <div class="invoice-brand">
                <div class="invoice-logo">◈</div>
                <div>
                    <div class="invoice-company">ClientBase</div>
                    <div class="invoice-tagline">Système de gestion</div>
                </div>
            </div>
            <div class="invoice-meta">
                <div class="invoice-title">FACTURE</div>
                <div class="invoice-number">#<?= str_pad($facture['id_facture'], 5, '0', STR_PAD_LEFT) ?></div>
                <div class="invoice-dates">
                    <div><span>Date facture :</span> <?= htmlspecialchars($facture['date_facture']) ?></div>
                    <div><span>Date commande :</span> <?= htmlspecialchars($facture['date_commande']) ?></div>
                </div>
                <?php
                $badgeMap = ['payée'=>'badge-green','non payée'=>'badge-red','en attente'=>'badge-amber','annulée'=>'badge-gray'];
                $bc = $badgeMap[strtolower($facture['statut'])] ?? 'badge-gray';
                ?>
                <span class="badge <?= $bc ?>"><?= htmlspecialchars($facture['statut']) ?></span>
            </div>
        </div>

        <div class="invoice-parties">
            <div class="invoice-from">
                <div class="party-label">De</div>
                <div class="party-name">ClientBase SARL</div>
                <div class="party-detail">123 Rue du Commerce</div>
                <div class="party-detail">Agadir, Maroc</div>
            </div>
            <div class="invoice-to">
                <div class="party-label">Facturer à</div>
                <div class="party-name"><?= htmlspecialchars($facture['nom']) ?></div>
                <?php if (!empty($facture['adresse'])): ?>
                    <div class="party-detail"><?= htmlspecialchars($facture['adresse']) ?></div>
                <?php endif; ?>
                <?php if (!empty($facture['code_postal'])): ?>
                    <div class="party-detail">CP: <?= htmlspecialchars($facture['code_postal']) ?></div>
                <?php endif; ?>
                <?php if (!empty($facture['registre_commerce'])): ?>
                    <div class="party-detail">RC: <?= htmlspecialchars($facture['registre_commerce']) ?></div>
                <?php endif; ?>
                <?php if (!empty($facture['patente'])): ?>
                    <div class="party-detail">Patente: <?= htmlspecialchars($facture['patente']) ?></div>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($details && $details->num_rows > 0): ?>
        <table class="invoice-table">
            <thead>
                <tr>
                    <th>Produit</th>
                    <th class="text-right">Prix unitaire</th>
                    <th class="text-center">Qté</th>
                    <th class="text-right">Sous-total</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $details->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['produit_nom']) ?></td>
                    <td class="text-right"><?= number_format($row['prix_unitaire'], 2, ',', ' ') ?> MAD</td>
                    <td class="text-center"><?= (int)$row['quantite'] ?></td>
                    <td class="text-right"><?= number_format($row['quantite'] * $row['prix_unitaire'], 2, ',', ' ') ?> MAD</td>
                </tr>
                <?php endwhile; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" class="text-right total-label-inv">Total TTC</td>
                    <td class="text-right total-value-inv">
                        <?= number_format($facture['montant_total'], 2, ',', ' ') ?> MAD
                    </td>
                </tr>
            </tfoot>
        </table>
        <?php else: ?>
        <div class="invoice-no-details">
            <p>Commande #<?= (int)$facture['id_commande'] ?> — Montant : <?= number_format($facture['montant_total'], 2, ',', ' ') ?> MAD</p>
        </div>
        <?php endif; ?>

        <div class="invoice-footer">
            <p>Merci pour votre confiance.</p>
            <p class="invoice-footer-note">Document généré par ClientBase</p>
        </div>

    </div>

</main>

<?php $conn->close(); ?>
</body>
</html>