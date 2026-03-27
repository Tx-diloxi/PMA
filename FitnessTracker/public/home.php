<?php
session_start();
require_once __DIR__ . '/../src/db.php';

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$userId = intval($_SESSION['user_id']);

$today = date('Y-m-d');
$stmt = $pdo->prepare(
    'SELECT s.id AS seance_id, s.statut, s.series, s.repetitions, s.repos_secondes, e.id AS exercice_id, e.nom, e.description, e.image
     FROM seances s
     JOIN exercices e ON e.id = s.exercice_id
     WHERE s.utilisateur_id = :user_id AND s.date_programmee = :today
     ORDER BY s.id'
);
$stmt->execute(['user_id' => $userId, 'today' => $today]);
$sessions = $stmt->fetchAll();

if (empty($sessions)) {
    $stmt2 = $pdo->query('SELECT * FROM exercices ORDER BY RAND() LIMIT 5');
    $rand = $stmt2->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Accueil - FitTracker</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <nav class="topnav">
        <a href="home.php">Jour</a>
        <a href="nutrition.php">Nutrition</a>
        <a href="stats.php">Stats</a>
        <a href="profile.php">Profil</a>
        <a href="logout.php">Déconnexion</a>
    </nav>

    <main class="container">
        <h1>Programme du jour</h1>
        <?php if (empty($sessions)): ?>
            <p>Aucune séance planifiée pour aujourd'hui. Voici quelques exercices suggérés :</p>
            <div id="swipe-cards" class="card-stack">
                <?php foreach ($rand as $e): ?>
                    <article class="card" data-seance-id="0" data-exercice-id="<?php echo $e['id']; ?>">
                        <img src="../assets/img/<?php echo htmlspecialchars($e['image']); ?>" alt="<?php echo htmlspecialchars($e['nom']); ?>">
                        <h3><?php echo htmlspecialchars($e['nom']); ?></h3>
                        <p><?php echo htmlspecialchars($e['description']); ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div id="swipe-cards" class="card-stack">
                <?php foreach ($sessions as $s): ?>
                    <article class="card" data-seance-id="<?php echo $s['seance_id']; ?>" data-exercice-id="<?php echo $s['exercice_id']; ?>">
                        <img src="../assets/img/<?php echo htmlspecialchars($s['image']); ?>" alt="<?php echo htmlspecialchars($s['nom']); ?>">
                        <h3><?php echo htmlspecialchars($s['nom']); ?></h3>
                        <p><?php echo htmlspecialchars($s['description']); ?></p>
                        <p>Series: <?php echo $s['series']; ?> - Reps: <?php echo $s['repetitions']; ?> - Repos: <?php echo $s['repos_secondes']; ?>s</p>
                        <p>Status: <span class="status-text"><?php echo htmlspecialchars($s['statut']); ?></span></p>
                    </article>
                <?php endforeach; ?>
            </div>
            <div id="stats-summary"></div>
        <?php endif; ?>

        <div class="legend">
            <p>Swipe à droite = Terminée, Swipe à gauche = Ignorée.</p>
        </div>
    </main>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="../assets/js/swipe.js"></script>
    <script>
        $(document).ready(function () {
            setupSwipeCards({
                container: '#swipe-cards',
                onSwipe: function (seanceId, exerciceId, direction) {
                    var statut = direction === 'right' ? 'terminee' : 'ignoree';
                    $.ajax({
                        method: 'POST',
                        url: 'api/exercise_update.php',
                        data: { seance_id: seanceId, statut: statut },
                        dataType: 'json'
                    }).done(function (data) {
                        console.log('Mise à jour :', data);
                    }).fail(function () {
                        alert('Erreur lors de la mise à jour du statut de la séance.');
                    });
                }
            });
        });
    </script>
</body>
</html>
