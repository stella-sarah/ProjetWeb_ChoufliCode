<?php
require_once __DIR__ . '/../config';
require_once __DIR__ . '/../model/Commande.php';

class CommandeC {
    private $pdo;

    public function __construct() {
        $this->pdo = Config::getConnexion();
    }

    // Ajouter une nouvelle commande
    public function ajouterCommande($customer_name, $email, $city, $delivery_time, $depart_place, $arrive_place) {
        try {
            // Validate inputs
            if (empty($customer_name) || empty($email) || empty($city) || empty($delivery_time) || empty($depart_place) || empty($arrive_place)) {
                error_log("Missing required fields for order creation");
                return false;
            }
    
            // Create the order
            $sql = "INSERT INTO commandes (customer_name, email, city, delivery_time, status, created_at, updated_at, depart_place, arrive_place) 
                    VALUES (:customer_name, :email, :city, :delivery_time, 'en attente', NOW(), NOW(), :depart_place, :arrive_place)";
            
            $db = Config::getConnexion();
            $query = $db->prepare($sql);
            
            $result = $query->execute([
                'customer_name' => $customer_name,
                'email' => $email,
                'city' => $city,
                'delivery_time' => $delivery_time,
                'depart_place' => $depart_place,
                'arrive_place' => $arrive_place
            ]);
    
            if (!$result) {
                error_log("Failed to execute order creation query");
                return false;
            }
            
            $orderId = $db->lastInsertId();
            error_log("Created order with ID: " . $orderId);
            return $orderId;
        } catch (Exception $e) {
            error_log("Error adding order: " . $e->getMessage());
            return false;
        }
    }
    

    // Ajouter un plat à une commande
    public function ajouterPlatCommande($commande_id, $plat_id, $quantity) {
        try {
            // Validate inputs
            if (!$commande_id || !$plat_id || $quantity <= 0) {
                error_log("Invalid parameters for adding dish to order");
                return false;
            }

            $db = Config::getConnexion();

            // Check if the order exists
            $checkOrder = $db->prepare("SELECT id FROM commandes WHERE id = ?");
            $checkOrder->execute([$commande_id]);
            if (!$checkOrder->fetch()) {
                error_log("Order not found: " . $commande_id);
                return false;
            }

            // Check if the dish exists
            $checkDish = $db->prepare("SELECT id FROM dishes WHERE id = ?");
            $checkDish->execute([$plat_id]);
            if (!$checkDish->fetch()) {
                error_log("Dish not found: " . $plat_id);
                return false;
            }

            // Insert the order dish
            $sql = "INSERT INTO order_dishes (commande_id, dish_id, quantity) 
                    VALUES (:commande_id, :dish_id, :quantity)";
            
            $query = $db->prepare($sql);
            $result = $query->execute([
                'commande_id' => $commande_id,
                'dish_id' => $plat_id,
                'quantity' => $quantity
            ]);

            if (!$result) {
                error_log("Failed to add dish to order");
                return false;
            }

            return true;
        } catch (Exception $e) {
            error_log("Error adding dish to order: " . $e->getMessage());
            return false;
        }
    }

    // Afficher toutes les commandes
    public function afficherCommandes() {
        $sql = "SELECT * FROM commandes ORDER BY created_at DESC";
        try {
            $query = $this->pdo->query($sql);
            return $query->fetchAll();
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    // Afficher une commande par ID
    public function afficherCommande($id) {
        $sql = "SELECT * FROM commandes WHERE id = :id";
        try {
            $query = $this->pdo->prepare($sql);
            $query->execute(['id' => $id]);
            return $query->fetch();
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    // Modifier le statut d'une commande
    public function modifierStatutCommande($id, $statut) {
        $sql = "UPDATE commandes SET status = :status WHERE id = :id";
        try {
            $query = $this->pdo->prepare($sql);
            return $query->execute([
                'status' => $statut,
                'id' => $id
            ]);
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

   // Supprimer une commande
   public function supprimerCommande($id) {
    try {
        // First check if the order exists
        $checkOrder = $this->pdo->prepare("SELECT id FROM commandes WHERE id = ?");
        $checkOrder->execute([$id]);
        if (!$checkOrder->fetch()) {
            error_log("Order not found for deletion: " . $id);
            return false;
        }

        // Start a transaction
        $this->pdo->beginTransaction();

        // Delete the order (this will cascade delete order_dishes due to foreign key constraint)
        $sql = "DELETE FROM commandes WHERE id = :id";
        $query = $this->pdo->prepare($sql);
        $result = $query->execute(['id' => $id]);

        if ($result) {
            $this->pdo->commit();
            error_log("Successfully deleted order: " . $id);
            return true;
        } else {
            $this->pdo->rollBack();
            error_log("Failed to delete order: " . $id);
            return false;
        }
    } catch (Exception $e) {
        if ($this->pdo->inTransaction()) {
            $this->pdo->rollBack();
        }
        error_log("Error deleting order: " . $e->getMessage());
        return false;
    }
}


    // Récupérer les plats d'une commande
    public function getOrderDishes($commande_id) {
        $sql = "SELECT od.dish_id, od.quantity, d.name, d.price 
                FROM order_dishes od 
                JOIN dishes d ON od.dish_id = d.id 
                WHERE od.commande_id = :commande_id";
        try {
            $query = $this->pdo->prepare($sql);
            $query->execute(['commande_id' => $commande_id]);
            return $query->fetchAll();
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    // Get orders by email
    public function getCommandesByEmail($email) {
        $sql = "SELECT * FROM commandes WHERE email = :email ORDER BY created_at DESC";
        try {
            $query = $this->pdo->prepare($sql);
            $query->execute(['email' => $email]);
            return $query->fetchAll();
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    // Calculate order total
    public function calculateOrderTotal($commande_id) {
        try {
            $sql = "SELECT SUM(od.quantity * d.price) as total 
                    FROM order_dishes od 
                    JOIN dishes d ON od.dish_id = d.id 
                    WHERE od.commande_id = :commande_id";
            
            $query = $this->pdo->prepare($sql);
            $query->execute(['commande_id' => $commande_id]);
            $result = $query->fetch();
            
            return $result['total'] ?? 0;
        } catch (Exception $e) {
            error_log("Error calculating order total for ID $commande_id: " . $e->getMessage());
            return 0;
        }
    }

    // Get order with total
    public function getOrderWithTotal($commande_id) {
        try {
            $commande = $this->afficherCommande($commande_id);
            if ($commande) {
                $commande['subtotal'] = $this->calculateOrderTotal($commande_id);
                $commande['total'] = $commande['subtotal'] + 7.00; // Add delivery fee
            }
            return $commande;
        } catch (Exception $e) {
            error_log("Error getting order with total for ID $commande_id: " . $e->getMessage());
            return null;
        }
    }

    // Get all orders with totals
    public function getOrdersWithTotals() {
        try {
            $commandes = $this->afficherCommandes();
            foreach ($commandes as &$commande) {
                $commande['subtotal'] = $this->calculateOrderTotal($commande['id']);
                $commande['delivery_fee'] = 7.00;
                $commande['total'] = $commande['subtotal'] + $commande['delivery_fee'];
            }
            return $commandes;
        } catch (Exception $e) {
            error_log("Error getting orders with totals: " . $e->getMessage());
            return [];
        }
    }





    ///////////////////////////////////////////////////////////////////////////////////////////////////////




    public function countCommandes()
    {
        $sql = "SELECT COUNT(*) AS total FROM commandes";
        
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute();
            return $query->fetch(PDO::FETCH_ASSOC)['total'];
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }
    public function fetchSortedCommandes($sort, $limit, $offset)
    {
        // Sécuriser le tri et les limites
        $sort = strtoupper($sort) === 'DESC' ? 'DESC' : 'ASC';
        $limit = (int) $limit;
        $offset = (int) $offset;
    
        $sql = "SELECT * FROM commandes
                ORDER BY created_at $sort
                LIMIT $limit OFFSET $offset";
    
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }
        
} 