<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$host = "localhost";
$dbname = "u153072617_prms_ims";  // Fixed: Corrected database name to match SQL schema
$user = "u153072617_dgc_ims";
$pass = "|yXdB4qM1X";

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $user,
        $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    // Set MySQL/MariaDB timezone to America/Jamaica (UTC-5)
    // This ensures timestamps are stored and retrieved in the correct timezone
    $pdo->exec("SET SESSION time_zone = '-05:00'");
    
} catch (PDOException $e) {
    die("Database connection failed.");
}
