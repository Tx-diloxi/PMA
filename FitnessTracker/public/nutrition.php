<?php
require_once __DIR__ . '/../src/functions.php';
startSession();
requireLogin();
$pdo = getPdo();
$userId = getCurrentUserId();

$recos = fetchNutritionRecommendations($pdo, $userId);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Nutrition</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <nav class="topnav"><a href="home.php">Jour</a><a href="nutrition.php">Nutrition</a><a href="stats.php">Stats</a><a
            href="profile.php">Profil</a><a href="logout.php">Déconnexion</a></nav>
    <main class="container">
        <h1>Nutrition personnalisée</h1>
        <?php if (!$recos) echo '<p>Aucune recommandation disponible, attendez le traitement n8n.</p>'; ?>
        <?php foreach ($recos as $r): ?>
        <article class="card">
            <h3><?php echo htmlspecialchars($r['titre']); ?></h3>
            <p><?php echo htmlspecialchars($r['description']); ?></p>
            <small><?php echo ucfirst(str_replace('_',' ',$r['type_repas'])); ?> · <?php echo $r['calories']; ?>
                kcal</small>
        </article>
        <?php endforeach; ?>
    </main>
</body>

</html>