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