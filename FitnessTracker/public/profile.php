<?php
session_start(); require_once __DIR__ . '/../src/db.php';
if (empty($_SESSION['user_id'])) { header('Location: login.php'); exit; }
$userId = intval($_SESSION['user_id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $poids = floatval($_POST['weight'] ?? 0);
    $taille = floatval($_POST['height'] ?? 0);
    $objectif = trim($_POST['goal'] ?? '');
    $stmt = $pdo->prepare('UPDATE utilisateurs SET poids=:poids, taille=:taille, objectif=:objectif, modifie_le=NOW() WHERE id=:id');
    $stmt->execute(['poids'=>$poids,'taille'=>$taille,'objectif'=>$objectif,'id'=>$userId]);
    if ($stmt->rowCount()) {
        $success = 'Profil mis à jour.';
    }
}

$stmt = $pdo->prepare('SELECT * FROM utilisateurs WHERE id = :id');
$stmt->execute(['id' => $userId]);
$user = $stmt->fetch();
?>
<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Profil</title><link rel="stylesheet" href="../assets/css/style.css"></head><body>
<nav class="topnav"><a href="home.php">Jour</a><a href="nutrition.php">Nutrition</a><a href="stats.php">Stats</a><a href="profile.php">Profil</a><a href="logout.php">Déconnexion</a></nav>
<main class="container"><h1>Profil</h1>
<?php if (!empty($success)) echo '<p class="success">'.htmlspecialchars($success).'</p>'; ?>
<form method="post" class="form">
    <label>Poids (kg)<input type="number" name="weight" step="0.1" value="<?php echo htmlspecialchars($user['poids']); ?>" required></label>
    <label>Taille (cm)<input type="number" name="height" step="0.1" value="<?php echo htmlspecialchars($user['taille']); ?>" required></label>
    <label>Objectif<input type="text" name="goal" value="<?php echo htmlspecialchars($user['objectif']); ?>" required></label>
    <button class="btn" type="submit">Enregistrer</button>
</form>
</main></body></html>
