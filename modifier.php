<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Client — ClientBase</title>
    <link rel="stylesheet" href="modifier.css">
</head>
<body>

<?php
$conn = new mysqli("localhost", "root", "", "gestion_client");

if ($conn->connect_error) die('<div style="padding:40px;color:red">Erreur de connexion.</div>');

$client = null;
if (isset($_GET['id'])) {
    $stmt = $conn->prepare("SELECT * FROM client WHERE code_client = ?");
    $stmt->bind_param("i", $_GET['id']);
    $stmt->execute();
    $client = $stmt->get_result()->fetch_assoc();
}

if (isset($_POST['update'])) {
    $id                = (int)$_POST['id'];
    $nom               = htmlspecialchars(trim($_POST['nom']));
    $adresse           = htmlspecialchars(trim($_POST['adresse']));
    $code_postal       = htmlspecialchars(trim($_POST['code_postal']));
    $registre_commerce = htmlspecialchars(trim($_POST['registre_commerce']));
    $patente           = htmlspecialchars(trim($_POST['patente']));
    $observation       = htmlspecialchars(trim($_POST['observation']));

    if (!empty($_FILES['image']['name'])) {
        $image = basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], "images/" . $image);
        $stmt = $conn->prepare("UPDATE client SET nom=?, adresse=?, code_postal=?, registre_commerce=?, patente=?, observation=?, image=? WHERE code_client=?");
        $stmt->bind_param("sssssssi", $nom, $adresse, $code_postal, $registre_commerce, $patente, $observation, $image, $id);
    } else {
        $stmt = $conn->prepare("UPDATE client SET nom=?, adresse=?, code_postal=?, registre_commerce=?, patente=?, observation=? WHERE code_client=?");
        $stmt->bind_param("ssssssi", $nom, $adresse, $code_postal, $registre_commerce, $patente, $observation, $id);
    }

    $stmt->execute();
    header("Location: index.php");
    exit;
}

if (!$client) die('<div style="padding:40px">Client introuvable. <a href="index.php">Retour</a></div>');
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
        <div>
            <h2>Modifier Client</h2>
            <p class="header-sub">Client #<?= htmlspecialchars($client['code_client']) ?></p>
        </div>
        <div class="header-actions">
            <a href="afficher.php?id=<?= (int)$client['code_client'] ?>" class="btn btn-ghost">⊙ Voir fiche</a>
        </div>
    </div>

    <div class="form-card">
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= (int)$client['code_client'] ?>">

            <div class="form-grid">

                <div class="form-group full">
                    <label for="nom">Nom <span class="required">*</span></label>
                    <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($client['nom']) ?>" required>
                </div>

                <div class="form-group full">
                    <label for="adresse">Adresse</label>
                    <input type="text" id="adresse" name="adresse" value="<?= htmlspecialchars($client['adresse']) ?>">
                </div>

                <div class="form-group">
                    <label for="code_postal">Code Postal</label>
                    <input type="text" id="code_postal" name="code_postal" value="<?= htmlspecialchars($client['code_postal']) ?>">
                </div>

                <div class="form-group">
                    <label for="registre_commerce">Registre Commerce</label>
                    <input type="text" id="registre_commerce" name="registre_commerce" value="<?= htmlspecialchars($client['registre_commerce']) ?>">
                </div>

                <div class="form-group">
                    <label for="patente">Patente</label>
                    <input type="text" id="patente" name="patente" value="<?= htmlspecialchars($client['patente']) ?>">
                </div>

                <div class="form-group">
                    <label>Photo actuelle</label>
                    <div class="current-image">
                        <?php if (!empty($client['image'])): ?>
                            <img src="images/<?= htmlspecialchars($client['image']) ?>" alt="Photo actuelle">
                        <?php else: ?>
                            <div class="no-image">
                                <?= strtoupper(substr($client['nom'], 0, 1)) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-group full">
                    <label for="observation">Observation</label>
                    <textarea id="observation" name="observation"><?= htmlspecialchars($client['observation']) ?></textarea>
                </div>

                <div class="form-group full">
                    <label for="image">Changer la photo</label>
                    <input type="file" id="image" name="image" accept="image/*">
                </div>

            </div>

            <div class="form-actions">
                <button type="submit" name="update" class="btn btn-primary">✓ Mettre à jour</button>
                <a href="index.php" class="btn btn-ghost">← Retour</a>
            </div>
        </form>
    </div>

</main>

<?php $conn->close(); ?>
</body>
</html>