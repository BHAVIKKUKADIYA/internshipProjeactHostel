?php
/**
 * Sample Data Migration for LUXE Rose Edition
 * Populates categories and dishes from the hardcoded frontend data.
 */

require_once '../config/config.php';

try {
    // 1. Initial Categories
    $categories = [
        ['name' => 'Appetizers', 'slug' => 'appetizers'],
        ['name' => 'Main Course', 'slug' => 'main-course'],
        ['name' => 'Desserts', 'slug' => 'desserts'],
        ['name' => 'Wine List', 'slug' => 'wine-list'],
        ['name' => 'Chef\'s Specials', 'slug' => 'chefs-specials']
    ];

    $stmt = $pdo->prepare("INSERT INTO menu_categories (name, slug) VALUES (:name, :slug)");
    foreach ($categories as $cat) {
        // Check if exists
        $check = $pdo->prepare("SELECT id FROM menu_categories WHERE slug = ?");
        $check->execute([$cat['slug']]);
        if (!$check->fetch()) {
            $stmt->execute($cat);
            echo "Category '{$cat['name']}' added.<br>";
        }
    }

    // 2. Initial Dishes
    // Get category IDs
    $catIds = $pdo->query("SELECT id, name FROM menu_categories")->fetchAll(PDO::FETCH_KEY_PAIR);
    $catIds = array_flip($catIds);

    $dishes = [
        [
            'category_id' => $catIds['Main Course'],
            'name' => 'Hand-picked seasonal truffles',
            'description' => 'Seasonal special edition',
            'price' => 1250.00,
            'is_visible' => 1
        ],
        [
            'category_id' => $catIds['Desserts'],
            'name' => 'Signature floral delicate pastry',
            'description' => 'Infused with rose essence',
            'price' => 650.00,
            'is_visible' => 1
        ],
        [
            'category_id' => $catIds['Main Course'],
            'name' => 'Wild Mediterranean bass',
            'description' => 'Butter-poached with lemon',
            'price' => 1850.00,
            'is_visible' => 0
        ],
        [
            'category_id' => $catIds['Wine List'],
            'name' => 'Reserve 2015 Collection',
            'description' => 'Limited vintage availability',
            'price' => 4200.00,
            'is_visible' => 1
        ]
    ];

    $stmtDish = $pdo->prepare("INSERT INTO menu_items (category_id, name, description, price, is_visible) VALUES (:category_id, :name, :description, :price, :is_visible)");
    foreach ($dishes as $dish) {
         // Check if exists
         $check = $pdo->prepare("SELECT id FROM menu_items WHERE name = ?");
         $check->execute([$dish['name']]);
         if (!$check->fetch()) {
             $stmtDish->execute($dish);
             echo "Dish '{$dish['name']}' added.<br>";
         }
    }

    echo "Migration completed successfully!";

} catch (PDOException $e) {
    die("Migration failed: " . $e->getMessage());
}
?>
