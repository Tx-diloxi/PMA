<?php
session_start(); require_once __DIR__ . '/../src/db.php';
if (empty($_SESSION['user_id'])) { header('Location: login.php'); exit; }
$userId = intval($_SESSION['user_id']);

$weights = $pdo->prepare('SELECT date_mesure, poids FROM journal_poids WHERE utilisateur_id = :user ORDER BY date_mesure ASC');
$weights->execute(['user' => $userId]);
$weightData = $weights->fetchAll();

$sessionsData = $pdo->prepare('SELECT date_programmee, statut FROM seances WHERE utilisateur_id = :user ORDER BY date_programmee ASC');
$sessionsData->execute(['user' => $userId]);
$sessions = $sessionsData->fetchAll();

$weekly = [];
foreach ($sessions as $row) {
    $week = date('oW', strtotime($row['date_programmee']));
    if (!isset($weekly[$week])) $weekly[$week] = 0;
    $weekly[$week]++;
}

$weeks = array_keys($weekly);
$volumes = array_values($weekly);
$doneCount = count(array_filter($sessions, fn($s) => $s['statut'] === 'terminee'));
$totalSessions = count($sessions);
$startWeight = $weightData ? floatval($weightData[0]['poids']) : 0;
$lastWeight = $weightData ? floatval(end($weightData)['poids']) : $startWeight;
?>
<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Statistiques</title><link rel="stylesheet" href="../assets/css/style.css"><script src="https://cdn.jsdelivr.net/npm/chart.js"></script></head><body>
<nav class="topnav"><a href="home.php">Jour</a><a href="nutrition.php">Nutrition</a><a href="stats.php">Stats</a><a href="profile.php">Profil</a><a href="logout.php">Déconnexion</a></nav>
<main class="container"><h1>Statistiques</h1>
<canvas id="weightChart"></canvas>
<canvas id="weeklyVolumeChart"></canvas>
<canvas id="sessionsChart"></canvas>
<canvas id="lossChart"></canvas>
</main>
<script>
const weightSeries = <?php echo json_encode($weightData); ?>;
const weeks = <?php echo json_encode($weeks); ?>;
const volumes = <?php echo json_encode($volumes); ?>;
const doneCount = <?php echo json_encode($doneCount); ?>;
const totalSessions = <?php echo json_encode($totalSessions); ?>;
const startWeight = <?php echo json_encode($startWeight); ?>;
const lastWeight = <?php echo json_encode($lastWeight); ?>;

new Chart(document.getElementById('weightChart'),{type:'line',data:{labels:weightSeries.map(v=>v.date_mesure),datasets:[{label:'Poids (kg)',data:weightSeries.map(v=>v.poids),borderColor:'#007bff',fill:false}]}});
new Chart(document.getElementById('weeklyVolumeChart'),{type:'bar',data:{labels:weeks,datasets:[{label:'Volume par semaine',data:volumes,backgroundColor:'#28a745'}]}});
new Chart(document.getElementById('sessionsChart'),{type:'doughnut',data:{labels:['Terminées','Autres'],datasets:[{data:[doneCount,totalSessions-doneCount],backgroundColor:['#17a2b8','#ffc107']}]}});
new Chart(document.getElementById('lossChart'),{type:'bar',data:{labels:['Perte de poids'],datasets:[{label:'kg',data:[(startWeight-lastWeight).toFixed(2)],backgroundColor:'#dc3545'}]}});
</script>
</body></html>
