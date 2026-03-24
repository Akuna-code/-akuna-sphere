<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Configuration base de données
$host = 'localhost';
$dbname = 'akuna_sync';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die(json_encode(['error' => 'DB connection failed']));
}

// Création des tables si besoin
$pdo->exec("CREATE TABLE IF NOT EXISTS activities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    day VARCHAR(10) NOT NULL,
    time_slot VARCHAR(20) NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    category VARCHAR(20),
    user_id INT DEFAULT 1,
    created_at DATETIME NOT NULL
)");

$input = json_decode(file_get_contents('php://input'), true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($input) {
        $stmt = $pdo->prepare("INSERT INTO activities (day, time_slot, title, description, category, user_id, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([
            $input['day'] ?? '',
            $input['time'] ?? '',
            $input['title'] ?? '',
            $input['desc'] ?? '',
            $input['category'] ?? '',
            1
        ]);
        echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
    } else {
        echo json_encode(['success' => false, 'error' => 'No data']);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $pdo->query("SELECT * FROM activities ORDER BY created_at DESC LIMIT 100");
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
}
?>