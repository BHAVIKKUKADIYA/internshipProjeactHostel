<?php 
require_once '../config/config.php';
require_once '../includes/functions.php';
require_once '../actions/menu/menu_actions.php';
require_once '../includes/auth.php'; // Protect admin page

$categories = get_all_categories($pdo);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Dish | LUXE Admin</title>
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
<body class="bg-background-light text-luxe-dark min-h-screen overflow-hidden">
<div class="flex h-screen overflow-hidden">
<!-- Sidebar -->
<?php include '../includes/admin_sidebar.php'; ?>
<!-- Main Content -->
<main class="flex-1 overflow-y-auto bg-background-light p-8 lg:p-12">
<!-- Header -->
<header class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-10">
<div>
<h2 class="serif-title text-4xl font-bold mb-2">Menu Management</h2>
<p class="text-luxe-grey-text text-base">Manage dishes, categories, pricing and visibility.</p>
</div>
<button onclick="window.location.href='menumanage.php'" class="flex items-center gap-2 bg-primary hover:bg-primary/90 text-white px-6 py-3.5 rounded-2xl font-semibold shadow-lg shadow-primary/30 transition-all duration-300 hover:-translate-y-0.5">
<span class="material-symbols-outlined">menu_open</span>
<span>Back to Menu</span>
</button>
</header>

<!-- Add New Dish Modal Overlay (Embedded in page for simplicity as it was) -->
<div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 bg-luxe-dark/60 backdrop-blur-sm">
    <!-- Modal Container -->
    <div class="bg-white w-full max-w-[95%] md:max-w-[90%] lg:max-w-[640px] max-h-[90vh] rounded-2xl shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-300">
        <form method="POST" action="../actions/menu/add_dish.php" class="flex flex-col h-full max-h-[90vh]">
            <input type="hidden" name="action" value="add_dish">
            <!-- Modal Header -->
            <div class="px-6 sm:px-8 py-6 border-b border-primary/5 flex items-center justify-between shrink-0">
                <div>
                    <h3 class="serif-title text-xl sm:text-2xl font-bold text-luxe-dark">Add New Dish</h3>
                    <p class="text-[11px] sm:text-xs text-luxe-dark/40 mt-1">Create a new culinary masterpiece for your menu</p>
                </div>
                <button type="button" onclick="window.location.href='menumanage.php'" class="w-8 h-8 rounded-full flex items-center justify-center text-luxe-dark/40 hover:text-primary hover:bg-primary/5 transition-colors">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="p-6 sm:p-8 space-y-6 sm:space-y-7 overflow-y-auto flex-1 custom-scrollbar">
                <!-- Dish Name -->
                <div class="space-y-2">
                    <label class="text-[11px] uppercase tracking-widest text-luxe-dark/40 font-bold ml-1">Dish Name</label>
                    <input name="name" required class="w-full px-4 py-3 rounded-xl border border-luxe-border focus:border-primary focus:ring-1 focus:ring-primary/20 transition-all outline-none text-sm placeholder:text-luxe-dark/20" placeholder="e.g. Pan-seared Scallops" type="text"/>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 sm:gap-6">
                    <!-- Category -->
                    <div class="space-y-2">
                        <label class="text-[11px] uppercase tracking-widest text-luxe-dark/40 font-bold ml-1">Category</label>
                        <div class="relative">
                            <select name="category_id" required class="w-full px-4 py-3 rounded-xl border border-luxe-border focus:border-primary focus:ring-1 focus:ring-primary/20 transition-all outline-none text-sm appearance-none bg-white">
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"><?= e($cat['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <span class="material-symbols-outlined absolute right-4 top-1/2 -translate-y-1/2 text-primary/40 pointer-events-none text-lg">expand_more</span>
                        </div>
                    </div>
                    <!-- Price -->
                    <div class="space-y-2">
                        <label class="text-[11px] uppercase tracking-widest text-luxe-dark/40 font-bold ml-1">Price (?)</label>
                        <input name="price" required step="0.01" class="w-full px-4 py-3 rounded-xl border border-luxe-border focus:border-primary focus:ring-1 focus:ring-primary/20 transition-all outline-none text-sm" placeholder="0.00" type="number"/>
                    </div>
                </div>

                <!-- Description -->
                <div class="space-y-2">
                    <label class="text-[11px] uppercase tracking-widest text-luxe-dark/40 font-bold ml-1">Description</label>
                    <textarea name="description" class="w-full px-4 py-3 rounded-xl border border-luxe-border focus:border-primary focus:ring-1 focus:ring-primary/20 transition-all outline-none text-sm resize-none placeholder:text-luxe-dark/20 custom-scrollbar" placeholder="Brief description of the dish..." rows="3"></textarea>
                </div>

                <!-- Image URL -->
                <div class="space-y-2">
                    <label class="text-[11px] uppercase tracking-widest text-luxe-dark/40 font-bold ml-1">Image URL</label>
                    <input name="image_url" class="w-full px-4 py-3 rounded-xl border border-luxe-border focus:border-primary focus:ring-1 focus:ring-primary/20 transition-all outline-none text-sm placeholder:text-luxe-dark/20" placeholder="https://..." type="text"/>
                </div>

                <!-- Visibility Toggle -->
                <div class="flex flex-row items-center justify-between p-4 bg-primary/[0.03] rounded-xl border border-primary/5">
                    <div>
                        <p class="text-sm font-bold text-luxe-dark">Visibility</p>
                        <p class="text-[10px] sm:text-[11px] text-luxe-dark/40 uppercase tracking-wider mt-0.5">Visible to customers on the menu</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="is_visible" checked class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                    </label>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="px-6 sm:px-8 py-5 sm:py-6 bg-primary/[0.02] border-t border-primary/5 flex flex-row items-center justify-end gap-3 sm:gap-4 shrink-0">
                <button type="button" onclick="window.location.href='menumanage.php'" class="px-5 sm:px-6 py-2.5 text-sm font-bold text-luxe-dark/40 hover:text-luxe-dark transition-colors shrink-0">Cancel</button>
                <button type="submit" class="bg-primary hover:bg-primary/90 text-white px-6 sm:px-8 py-2.5 rounded-xl font-bold shadow-lg shadow-primary/30 transition-all duration-300 hover:-translate-y-0.5 text-sm shrink-0 whitespace-nowrap">
                    Save Dish
                </button>
            </div>
        </form>
    </div>
</div>
</main>
</div>
</body>
</html>
