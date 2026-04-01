<?php

//vérification de la conformité de l'email
function emailConforme($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

//vérification de l'existence de l'email dans la base de données
function emailExiste($pdo, $email) {
    $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = :email");
    $stmt->execute(['email' => $email]);
    return $stmt->fetch() !== false;
}

//vérification de l'existence du pseudo dans la base de données
function pseudoExiste($pdo, $pseudo) {
    $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE pseudo = :pseudo");
    $stmt->execute(['pseudo' => $pseudo]);
    return $stmt->fetch() !== false;
}

//vérification de l'email et du mot de passe
function verifEmailPassword($pdo, $email, $password) {
    $stmt = $pdo->prepare("SELECT id, password FROM utilisateurs WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        return false;
    }

    if (password_verify($password, $user['password'])) {
        return (int) $user['id'];
    }

    return false;
}

//récupération des séances pour un utilisateur donné
define('DATE_FORMAT_SQL', 'Y-m-d');

//récupération de toutes les séances d'un utilisateur donné
function getSeancesParUtilisateur($pdo, $userId) {
    $stmt = $pdo->prepare(
        "SELECT s.*, e.nom AS exercice_nom, e.categorie AS exercice_categorie, e.difficulte AS exercice_difficulte, e.muscles AS exercice_muscles
         FROM seances s
         JOIN exercices e ON s.exercice_id = e.id
         WHERE s.utilisateur_id = :user_id
         ORDER BY s.date_programmee DESC"
    );
    $stmt->execute(['user_id' => $userId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

//récupération des séances pour un utilisateur donné et une date spécifique
function getSeancesParUtilisateurParDate($pdo, $userId, $date) {
    $stmt = $pdo->prepare(
        "SELECT s.*, e.nom AS exercice_nom, e.categorie AS exercice_categorie, e.difficulte AS exercice_difficulte, e.muscles AS exercice_muscles
         FROM seances s
         JOIN exercices e ON s.exercice_id = e.id
         WHERE s.utilisateur_id = :user_id
           AND s.date_programmee = :date_programmee
         ORDER BY s.date_programmee DESC"
    );
    $stmt->execute([
        'user_id' => $userId,
        'date_programmee' => $date,
    ]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

//récupération des séances de la semaine courante pour un utilisateur donné
function getSeancesSemaineCouranteParUtilisateur($pdo, $userId) {
    // calcul de début et fin de semaine (lundi -> dimanche)
    $aujourdhui = new DateTime();
    $debutSemaine = clone $aujourdhui;
    $debutSemaine->modify('monday this week');
    $finSemaine = clone $debutSemaine;
    $finSemaine->modify('sunday this week');

    $dateDebut = $debutSemaine->format(DATE_FORMAT_SQL);
    $dateFin = $finSemaine->format(DATE_FORMAT_SQL);

    $stmt = $pdo->prepare(
        "SELECT s.*, e.nom AS exercice_nom, e.categorie AS exercice_categorie, e.difficulte AS exercice_difficulte, e.muscles AS exercice_muscles
         FROM seances s
         JOIN exercices e ON s.exercice_id = e.id
         WHERE s.utilisateur_id = :user_id
           AND s.date_programmee BETWEEN :date_debut AND :date_fin
         ORDER BY s.date_programmee DESC"
    );

    $stmt->execute([
        'user_id' => $userId,
        'date_debut' => $dateDebut,
        'date_fin' => $dateFin,
    ]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

//fonction pour obtenir le nom du jour en français à partir d'une date
function getNomJourFr($date) {
    $timestamp = strtotime($date);
    if ($timestamp === false) {
        return '';
    }

    $jours = ['Dimanche','Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'];
    return $jours[date('w', $timestamp)] ?? '';
}