<?php
/**
 * Configuration de la connexion à la base de données
 * 
 * Ce fichier établit la connexion MySQLi avec la base de données Yu-Gi-Oh!
 * Utilise une approche procédurale stricte sans POO
 * 
 * @author DeckForge Team
 * @version 1.0
 */

// Paramètres de connexion à la base de données
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'yugioh');

/**
 * Établit la connexion à la base de données
 * 
 * @return mysqli|false Retourne l'objet de connexion ou false en cas d'erreur
 */
function db_connect() {
    // Création de la connexion MySQLi
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Vérification de la connexion
    if (!$conn) {
        // Log de l'erreur
        error_log("Erreur de connexion à la base de données : " . mysqli_connect_error());
        return false;
    }
    
    // Définition du charset UTF-8 pour éviter les problèmes d'encodage
    mysqli_set_charset($conn, 'utf8mb4');
    
    return $conn;
}

/**
 * Ferme la connexion à la base de données
 * 
 * @param mysqli $conn L'objet de connexion à fermer
 * @return void
 */
function db_close($conn) {
    if ($conn) {
        mysqli_close($conn);
    }
}

/**
 * Échappe une chaîne pour éviter les injections SQL
 * 
 * @param mysqli $conn L'objet de connexion
 * @param string $string La chaîne à échapper
 * @return string La chaîne échappée
 */
function db_escape($conn, $string) {
    return mysqli_real_escape_string($conn, $string);
}

/**
 * Prépare une requête SQL sécurisée
 * 
 * @param mysqli $conn L'objet de connexion
 * @param string $query La requête SQL avec des placeholders ?
 * @return mysqli_stmt|false Le statement préparé ou false
 */
function db_prepare($conn, $query) {
    $stmt = mysqli_prepare($conn, $query);
    
    if (!$stmt) {
        error_log("Erreur de préparation de requête : " . mysqli_error($conn));
        return false;
    }
    
    return $stmt;
}

/**
 * Execute une requête préparée et retourne le résultat
 * 
 * @param mysqli_stmt $stmt Le statement à exécuter
 * @return mysqli_result|bool Le résultat ou false
 */
function db_execute($stmt) {
    if (!mysqli_stmt_execute($stmt)) {
        error_log("Erreur d'exécution de requête : " . mysqli_stmt_error($stmt));
        return false;
    }
    
    return mysqli_stmt_get_result($stmt);
}

/**
 * Récupère toutes les lignes d'un résultat
 * 
 * @param mysqli_result $result Le résultat de la requête
 * @return array Tableau associatif des résultats
 */
function db_fetch_all($result) {
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

/**
 * Récupère une seule ligne d'un résultat
 * 
 * @param mysqli_result $result Le résultat de la requête
 * @return array|null Tableau associatif de la ligne ou null
 */
function db_fetch_one($result) {
    return mysqli_fetch_assoc($result);
}

/**
 * Retourne l'ID de la dernière insertion
 * 
 * @param mysqli $conn L'objet de connexion
 * @return int L'ID inséré
 */
function db_insert_id($conn) {
    return mysqli_insert_id($conn);
}

/**
 * Retourne le nombre de lignes affectées
 * 
 * @param mysqli $conn L'objet de connexion
 * @return int Nombre de lignes affectées
 */
function db_affected_rows($conn) {
    return mysqli_affected_rows($conn);
}