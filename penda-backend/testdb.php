<?php
try {
    $pdo = new PDO("mysql:host=127.0.0.1;dbname=Penda-backend", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected to DB successfully!";
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
