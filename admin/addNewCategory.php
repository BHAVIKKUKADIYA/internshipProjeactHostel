<?php 
require_once '../config/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
require_once '../actions/menu/menu_actions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_category') {
    $name = $_POST['category-name'] ?? '';
    $slug = $_POST['category-slug'] ?? '';
    
    if (!empty($name) && !empty($slug)) {
        if (add_category($pdo, $name, $slug)) {
            redirect('menumanage.php');
        } else {
            $error = "Failed to add category.";
        }
    } else {
        $error = "Please fill in all fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Category | LUXE Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght@700;900&display=swap">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: '#c67c7c',
                        'primary-hover': '#b26a6a',
                        'luxe-rose': '#c67c7c',
                        'luxe-dark': '#2b2b2b',
                        'luxe-beige': '#f4efec',
                        'luxe-border': '#e5e0dd',
                        'luxe-grey-text': '#707070',
                        'background-light': '#fdfbf9',
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        serif: ['Playfair Display', 'serif'],
                    }
                }
            }
        }
    </script>
    <style>
        .sidebar-active { background: #c67c7c; color: white; }
        .transition-custom { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        body { font-family: 'Inter', sans-serif; }
        h1, h2, h3, h4, .serif-title { font-family: 'Playfair Display', serif; }
    </style>
</head>
<body class="text-luxe-dark overflow-hidden">
<!-- BEGIN: Dashboard Layout Container (Dimmed Background) -->
<div class="flex h-screen w-full filter blur-sm pointer-events-none select-none">
<!-- BEGIN: Sidebar -->
<?php include '../includes/admin_sidebar.php'; ?>
<!-- END: Sidebar -->
<!-- BEGIN: Main Dashboard Content -->
<main class="flex-1 p-12 overflow-y-auto">
<div class="flex justify-between items-start mb-10">
<div>
<h2 class="text-4xl serif-title font-semibold mb-2">Menu Management</h2>
<p class="text-luxe-grey-text">Manage dishes, categories, pricing and visibility.</p>
</div>
<button class="bg-primary text-white px-6 py-3 rounded-2xl flex items-center gap-2 shadow-lg shadow-primary/30">
<svg class="h-5 w-5" fill="none" stroke="currentColor" viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M12 4v16m8-8H4" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
          Add New Dish
        </button>
</div>
<!-- Filters Placeholder -->
<div class="flex gap-4 mb-8 overflow-x-auto pb-2">
<span class="px-6 py-2 bg-white text-primary border border-primary/20 rounded-full font-medium whitespace-nowrap">All Dishes</span>
<span class="px-6 py-2 text-gray-400 font-medium whitespace-nowrap">Appetizers</span>
<span class="px-6 py-2 text-gray-400 font-medium whitespace-nowrap">Main Course</span>
<span class="px-6 py-2 text-gray-400 font-medium whitespace-nowrap">Desserts</span>
</div>
<!-- Table Container -->
<div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-8 min-h-[500px]">
<!-- Table structure placeholder matching original screenshot -->
</div>
</main>
<!-- END: Main Dashboard Content -->
</div>
<!-- END: Dashboard Layout Container -->
<!-- BEGIN: Modal Overlay -->
<div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-[2px]" data-purpose="modal-overlay">
<!-- BEGIN: Add New Category Modal -->
<div class="bg-white w-full max-w-lg max-h-[90vh] rounded-2xl shadow-2xl p-0 flex flex-col overflow-hidden transform transition-all" data-purpose="category-modal">
    <div class="flex flex-col h-full max-h-[90vh]">
<!-- Modal Header -->
<div class="px-8 pt-8 pb-4">
<h3 class="text-2xl serif-title font-bold text-luxe-dark">Add New Category</h3>
<p class="text-sm text-gray-400 mt-1">Organize your menu with logical groupings.</p>
<?php if (isset($error)): ?>
                <p class="text-red-500 text-xs mt-1"><?= $error ?></p>
            <?php endif; ?>
</div>
<!-- Modal Body (Form) -->
<form method="POST" action="" class="px-8 py-4 space-y-6 overflow-y-auto flex-1 custom-scrollbar">
<input type="hidden" name="action" value="add_category">
<!-- Category Name Field -->
<div class="space-y-2">
<label class="block text-xs font-semibold uppercase tracking-wider text-luxe-dark/60 ml-1" for="category-name">Category Name</label>
<input required class="w-full px-4 py-3 bg-[#fcf9f7] border border-luxe-border rounded-xl focus:border-primary focus:ring-1 focus:ring-primary/20 outline-none text-sm text-luxe-dark placeholder:text-gray-300 transition-all font-medium" id="category-name" name="category-name" placeholder="e.g. Signature Cocktails" type="text"/>
</div>
<!-- Category Slug Field -->
<div class="space-y-2">
<label class="block text-xs font-semibold uppercase tracking-wider text-luxe-dark/60 ml-1" for="category-slug">Category Slug</label>
<input required class="w-full px-4 py-3 bg-[#fcf9f7] border border-luxe-border rounded-xl focus:border-primary focus:ring-1 focus:ring-primary/20 outline-none text-sm text-luxe-dark placeholder:text-gray-300 transition-all font-medium" id="category-slug" name="category-slug" placeholder="e.g. signature-cocktails" type="text"/>
</div>
<!-- Row: Display Order & Status -->

<!-- Modal Footer Actions -->
<div class="flex items-center justify-end gap-3 pt-6 pb-2">
<button onclick="window.location.href='menumanage.php'" class="px-6 py-3 text-sm font-bold text-luxe-dark/40 hover:text-luxe-dark transition-colors" type="button">
            Cancel
          </button>
<button class="px-10 py-3 bg-primary text-white font-bold rounded-xl shadow-lg shadow-primary/25 hover:bg-primary-hover transition-all duration-300 hover:-translate-y-0.5 active:translate-y-0 text-sm tracking-wide" type="submit">
            Save Category
          </button>
</div>
</form>
</div>
</div>
<!-- END: Add New Category Modal -->
</div>
<!-- END: Modal Overlay -->
</body></html>
