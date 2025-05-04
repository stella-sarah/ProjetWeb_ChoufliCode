<?php
session_start();
require_once '../../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo = Config::getConnexion();
        $pdo->beginTransaction();

        // Insert main order
        $stmt = $pdo->prepare("
            INSERT INTO orders (customer_name, delivery_time, status)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([
            $_POST['customer_name'],
            $_POST['delivery_time'],
            $_POST['status']
        ]);
        $orderId = $pdo->lastInsertId();

        // Insert order dishes
        $stmt = $pdo->prepare("
            INSERT INTO order_dishes (order_id, dish_id, quantity)
            VALUES (?, ?, ?)
        ");
        
        foreach ($_POST['dishes'] as $dish) {
            if (!empty($dish['id'])) {
                $stmt->execute([$orderId, $dish['id'], $dish['quantity']]);
            }
        }

        $pdo->commit();
        $_SESSION['success'] = "Réservation créée avec succès (ID: $orderId)";
    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Erreur: " . $e->getMessage();
    }
}

header("Location: backrestauration.php");
exit;