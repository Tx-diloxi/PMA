<?php
// Helper functions réutilisables dans toutes les pages

function startSession(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function getPdo(): PDO
{
    global $pdo;
    if (!isset($pdo)) {
        require_once __DIR__ . '/selectBDD.php';
    }
    return $pdo;
}

function isUserLoggedIn(): bool
{
    return !empty($_SESSION['user_id']);
}

function getCurrentUserId(): ?int
{
    if (!isUserLoggedIn()) {
        return null;
    }
    return intval($_SESSION['user_id']);
}

function requireLogin(): void
{
    if (!isUserLoggedIn()) {
        redirect('login.php');
    }
}

function redirect(string $location): void
{
    header('Location: ' . $location);
    exit;
}

function fetchWeightHistory(PDO $pdo, int $userId): array
{
    $stmt = $pdo->prepare('SELECT date_mesure, poids FROM journal_poids WHERE utilisateur_id = :user ORDER BY date_mesure ASC');
    $stmt->execute(['user' => $userId]);
    return $stmt->fetchAll();
}

function fetchSessions(PDO $pdo, int $userId, string $extraWhere = '', array $params = []): array
{
    $sql = 'SELECT * FROM seances WHERE utilisateur_id = :user ' . $extraWhere . ' ORDER BY date_programmee ASC';
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array_merge(['user' => $userId], $params));
    return $stmt->fetchAll();
}

function fetchTodaysSessions(PDO $pdo, int $userId): array
{
    $today = date('Y-m-d');
    $stmt = $pdo->prepare(
        'SELECT s.id AS seance_id, s.statut, s.series, s.repetitions, s.repos_secondes, e.id AS exercice_id, e.nom, e.description, e.image
         FROM seances s
         JOIN exercices e ON e.id = s.exercice_id
         WHERE s.utilisateur_id = :user_id AND s.date_programmee = :today
         ORDER BY s.id'
    );
    $stmt->execute(['user_id' => $userId, 'today' => $today]);
    return $stmt->fetchAll();
}

function fetchRandomExercises(PDO $pdo, int $limit = 5): array
{
    $stmt = $pdo->prepare('SELECT * FROM exercices ORDER BY RAND() LIMIT :limit');
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function fetchNutritionRecommendations(PDO $pdo, int $userId): array
{
    $stmt = $pdo->prepare('SELECT * FROM recommandations_nutrition WHERE utilisateur_id = :user_id ORDER BY cree_le DESC LIMIT 10');
    $stmt->execute(['user_id' => $userId]);
    return $stmt->fetchAll();
}

function getUserById(PDO $pdo, int $userId): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM utilisateurs WHERE id = :id');
    $stmt->execute(['id' => $userId]);
    $user = $stmt->fetch();
    return $user === false ? null : $user;
}

function updateUserProfile(PDO $pdo, int $userId, float $poids, float $taille, string $objectif): bool
{
    $stmt = $pdo->prepare('UPDATE utilisateurs SET poids=:poids, taille=:taille, objectif=:objectif, modifie_le=NOW() WHERE id=:id');
    $stmt->execute(['poids' => $poids, 'taille' => $taille, 'objectif' => $objectif, 'id' => $userId]);
    return $stmt->rowCount() > 0;
}

function countCompletedSessions(array $sessions): int
{
    return count(array_filter($sessions, fn($s) => ($s['statut'] ?? '') === 'terminee'));
}

function computeWeeklyStats(array $sessions): array
{
    $weekly = [];
    foreach ($sessions as $row) {
        $week = date('oW', strtotime($row['date_programmee'] ?? ''));
        if (!isset($weekly[$week])) $weekly[$week] = 0;
        $weekly[$week]++;
    }
    return $weekly;
}

function registerUser(PDO $pdo, array $data): int
{
    $stmt = $pdo->prepare(
        'INSERT INTO utilisateurs (email,mot_de_passe,age,sexe,poids,taille,objectif,jours_disponibilite,materiel,date_fin)
         VALUES (:email,:mot_de_passe,:age,:sexe,:poids,:taille,:objectif,:jours,:materiel,:date_fin)'
    );
    $stmt->execute($data);
    return intval($pdo->lastInsertId());
}

function findUserByEmail(PDO $pdo, string $email): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM utilisateurs WHERE email = :email');
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();
    return $user === false ? null : $user;
}

function sendN8nPayload(array $payload, string $webhookUrl = 'http://localhost:5678/webhook/fitness'): array
{
    $ch = curl_init($webhookUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $response = curl_exec($ch);
    $curlError = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [
        'is_success' => !$curlError && $httpCode >= 200 && $httpCode < 400,
        'http_code' => $httpCode,
        'error' => $curlError,
        'response' => $response,
        'webhook_url' => $webhookUrl,
    ];
}