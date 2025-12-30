<?php
$host = 'ep-billowing-wildflower-a1sdzq5c-pooler.ap-southeast-1.aws.neon.tech';
$db = 'neondb';
$user = 'neondb_owner';
$pass = 'npg_utXMQePK1pE7';
$port = '5432';
$sslmode = 'require';

$dsn = "pgsql:host=$host;port=$port;dbname=$db;sslmode=$sslmode";

try {
    echo "Attempting connection to $host..." . PHP_EOL;
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 5
    ]);
    
    echo "Connection Successful!" . PHP_EOL;
    
    $stmt = $pdo->query("SELECT version()");
    $version = $stmt->fetchColumn();
    echo "PostgreSQL Version: " . $version . PHP_EOL;
    
    // Check if tables exist
    $stmt = $pdo->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'");
    echo "Tables in database:" . PHP_EOL;
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "- " . $row['table_name'] . PHP_EOL;
    }
    
} catch (PDOException $e) {
    echo "Connection Failed: " . $e->getMessage() . PHP_EOL;
    exit(1);
}
