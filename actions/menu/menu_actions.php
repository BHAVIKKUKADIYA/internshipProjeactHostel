<?php
/**
 * Menu Actions
 * Handles categories and dishes
 */

require_once __DIR__ . '/../../config/config.php';

/**
 * Get all categories
 */
function get_all_categories($pdo) {
    try {
        return $pdo->query("SELECT * FROM menu_categories ORDER BY name ASC")->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Get dishes by category
 */
function get_dishes_by_category($pdo, $category_id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM menu_items WHERE category_id = ? AND is_visible = 1 ORDER BY name ASC");
        $stmt->execute([$category_id]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Get all dishes for admin
 */
function get_all_dishes($pdo) {
    try {
        return $pdo->query("SELECT d.*, c.name as category_name, c.slug as category_slug FROM menu_items d JOIN menu_categories c ON d.category_id = c.id ORDER BY c.name ASC, d.name ASC")->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Add a new dish
 */
function add_dish($pdo, $data) {
    try {
        $sql = "INSERT INTO menu_items (category_id, name, description, price, image_url, is_visible) 
                VALUES (:category_id, :name, :description, :price, :image_url, :is_visible)";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute($data);
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Update an existing dish
 */
function update_dish($pdo, $id, $data) {
    try {
        $data['id'] = $id;
        $sql = "UPDATE menu_items SET 
                category_id = :category_id, 
                name = :name, 
                description = :description, 
                price = :price, 
                image_url = :image_url, 
                is_visible = :is_visible 
                WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute($data);
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Delete a dish
 */
function delete_dish($pdo, $id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM menu_items WHERE id = ?");
        return $stmt->execute([$id]);
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Add a new category
 */
function add_category($pdo, $name, $slug) {
    try {
        $stmt = $pdo->prepare("INSERT INTO menu_categories (name, slug) VALUES (?, ?)");
        return $stmt->execute([$name, $slug]);
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Delete a category
 */
function delete_category($pdo, $id) {
    try {
        // Find "Uncategorized" category or create it
        $stmt = $pdo->prepare("SELECT id FROM menu_categories WHERE slug = 'uncategorized'");
        $stmt->execute();
        $uncat_id = $stmt->fetchColumn();

        if (!$uncat_id) {
            // Create "Uncategorized" category
            $stmt = $pdo->prepare("INSERT INTO menu_categories (name, slug) VALUES ('Uncategorized', 'uncategorized')");
            $stmt->execute();
            $uncat_id = $pdo->lastInsertId();
        }

        // Don't delete "Uncategorized" itself
        if ($id == $uncat_id) {
            return false;
        }

        // Move items to Uncategorized
        $stmt = $pdo->prepare("UPDATE menu_items SET category_id = ? WHERE category_id = ?");
        $stmt->execute([$uncat_id, $id]);

        // Now delete the category
        $stmt = $pdo->prepare("DELETE FROM menu_categories WHERE id = ?");
        return $stmt->execute([$id]);
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Get menu stats for dashboard/admin
 */
function get_menu_stats($pdo) {
    try {
        $stats = [];
        
        // Active Dishes
        $stats['active_dishes'] = $pdo->query("SELECT COUNT(*) FROM menu_items WHERE is_visible = 1")->fetchColumn();
        
        // Top Category
        $sqlTopCat = "SELECT c.name FROM menu_items d JOIN menu_categories c ON d.category_id = c.id GROUP BY d.category_id ORDER BY COUNT(*) DESC LIMIT 1";
        $stats['top_category_name'] = $pdo->query($sqlTopCat)->fetchColumn() ?: 'N/A';
        
        // Average Price
        $stats['avg_price'] = $pdo->query("SELECT AVG(price) FROM menu_items")->fetchColumn() ?: 0;
        
        return $stats;
    } catch (PDOException $e) {
        return [
            'active_dishes' => 0,
            'top_category_name' => 'N/A',
            'avg_price' => 0
        ];
    }
}
 ?>
