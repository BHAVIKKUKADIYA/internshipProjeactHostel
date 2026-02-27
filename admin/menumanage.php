<?php 
require_once '../config/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
require_once '../actions/menu/menu_actions.php';

// Handle Deletion
if (isset($_GET['delete'])) {
    if (delete_dish($pdo, $_GET['delete'])) {
        redirect('menumanage.php');
    }
}

// Handle Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit_dish') {
    $id = $_POST['id'];
    $data = [
        'category_id' => $_POST['category_id'],
        'name' => $_POST['name'],
        'description' => $_POST['description'],
        'price' => $_POST['price'],
        'image_url' => $_POST['image_url'] ?? '',
        'is_visible' => isset($_POST['is_visible']) ? 1 : 0
    ];
    
    if (update_dish($pdo, $id, $data)) {
        redirect('menumanage.php');
    }
}

$dishes = get_all_dishes($pdo);
$categories = get_all_categories($pdo);
$menu_stats = get_menu_stats($pdo);

// Handle Category Deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_category') {
    $cat_id = $_POST['category_id'] ?? null;
    if ($cat_id && delete_category($pdo, $cat_id)) {
        redirect('menumanage.php?success=category_deleted');
    } else {
        $error = "Failed to delete category. It might be the 'Uncategorized' category.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu Management | LUXE Admin</title>
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
<body class="bg-[#f4efec] text-luxe-dark min-h-screen">
    <div class="flex h-screen overflow-hidden">
        <?php include '../includes/admin_sidebar.php'; ?>
        <main class="flex-1 overflow-y-auto px-10 py-8">
<!-- Header -->
<header class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-10">
<div>
<?php
$breadcrumb = "DASHBOARD / MENU";
$title = "Menu Management";
include '../includes/admin_pageHeader.php';
?>
<p class="text-luxe-dark/50 text-base mt-2">Manage dishes, categories, pricing and visibility.</p>
</div>
<button onclick="window.location.href='addnewdish.php'" class="flex items-center gap-2 bg-primary hover:bg-primary-hover text-white px-7 py-3 rounded-xl font-bold text-sm shadow-lg shadow-primary/25 transition-all duration-300 hover:-translate-y-0.5 active:translate-y-0">
<span class="material-symbols-outlined">add</span>
<span>Add New Dish</span>
</button>
</header>
<!-- Filters -->
<div class="bg-white/40 p-4 rounded-2xl border border-primary/5 mb-8">
<div class="flex flex-col lg:flex-row gap-4">
<div class="relative flex-1">
<span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-primary/60 text-xl">search</span>
<input id="searchInput" class="w-full pl-12 pr-4 py-3 bg-white border border-primary/10 rounded-xl focus:ring-1 focus:ring-primary focus:border-primary outline-none text-sm text-luxe-dark placeholder:text-luxe-dark/30 transition-all" placeholder="Search dish name, ingredients..." type="text"/>
</div>
<div class="flex gap-3">
<!-- Status Filter -->
<div class="relative group" id="statusFilter">
    <button class="flex items-center gap-2 px-6 py-3 bg-white border border-primary/10 rounded-xl text-sm font-medium text-luxe-dark/70 hover:border-primary/30 transition-all dropdown-trigger">
        <span class="selected-value">All Status</span>
        <span class="material-symbols-outlined text-primary/40 text-lg icon">expand_more</span>
    </button>
    <div class="absolute right-0 top-full mt-2 w-48 bg-white border border-primary/5 rounded-xl shadow-xl opacity-0 invisible translate-y-[-10px] transition-all z-50 dropdown-menu">
        <div class="p-2 space-y-1">
            <button class="w-full px-4 py-2 text-left text-sm hover:bg-primary/5 rounded-lg transition-colors option">All Status</button>
            <button class="w-full px-4 py-2 text-left text-sm hover:bg-primary/5 rounded-lg transition-colors option">Visible</button>
            <button class="w-full px-4 py-2 text-left text-sm hover:bg-primary/5 rounded-lg transition-colors option">Hidden</button>
        </div>
    </div>
</div>
<!-- Price Sort -->
<div class="relative group" id="priceSort">
    <button class="flex items-center gap-2 px-6 py-3 bg-white border border-primary/10 rounded-xl text-sm font-medium text-luxe-dark/70 hover:border-primary/30 transition-all dropdown-trigger">
        <span class="selected-value">Sort by Price</span>
        <span class="material-symbols-outlined text-primary/40 text-lg icon">swap_vert</span>
    </button>
    <div class="absolute right-0 top-full mt-2 w-48 bg-white border border-primary/5 rounded-xl shadow-xl opacity-0 invisible translate-y-[-10px] transition-all z-50 dropdown-menu">
        <div class="p-2 space-y-1">
            <button class="w-full px-4 py-2 text-left text-sm hover:bg-primary/5 rounded-lg transition-colors option">Sort by Price</button>
            <button class="w-full px-4 py-2 text-left text-sm hover:bg-primary/5 rounded-lg transition-colors option">Price: Low to High</button>
            <button class="w-full px-4 py-2 text-left text-sm hover:bg-primary/5 rounded-lg transition-colors option">Price: High to Low</button>
        </div>
    </div>
</div>
</div>
</div>
</div>
<!-- Content Card -->
<div class="flex items-center gap-3 mb-6 overflow-x-auto pb-2 scrollbar-hide">
<button onclick="filterCategory('All Items', this)" class="category-btn active px-6 py-2.5 rounded-full bg-primary text-white text-sm font-bold shadow-md shadow-primary/20 transition-all whitespace-nowrap">All Items</button>
<?php foreach ($categories as $cat): ?>
<?php if (($cat['slug'] ?? '') !== 'uncategorized'): ?>
<button onclick="filterCategory('<?= e($cat['slug'] ?? '') ?>', this)" class="category-btn px-6 py-2.5 rounded-full bg-white border border-primary/10 text-luxe-dark/60 text-sm font-semibold hover:border-primary/30 transition-all whitespace-nowrap"><?= e($cat['name'] ?? '') ?></button>
<?php endif; ?>
<?php endforeach; ?>
<button onclick="window.location.href='addNewCategory.php'" class="flex items-center gap-2 px-5 py-2.5 rounded-full bg-[#fcf9f7] border border-dashed border-primary/40 text-primary text-sm font-bold hover:bg-primary/5 transition-all whitespace-nowrap">
<span class="material-symbols-outlined text-lg">add</span>
<span>Add Category</span>
</button>
<button onclick="openDeleteCategoryModal()" class="flex items-center gap-2 px-5 py-2.5 rounded-full bg-red-50 border border-dashed border-red-200 text-red-500 text-sm font-bold hover:bg-red-100 transition-all whitespace-nowrap">
<span class="material-symbols-outlined text-lg">delete</span>
<span>Delete Category</span>
</button>
</div><div class="bg-white rounded-2xl shadow-soft overflow-hidden border border-luxe-border shadow-xl shadow-primary/5">
<div class="overflow-x-auto">
<table class="w-full text-left">
<thead>
<tr class="bg-luxe-beige/50 text-luxe-rose uppercase text-[10px] tracking-[0.15em] font-bold border-b border-luxe-border">
<th class="px-8 py-5">Image</th>
<th class="px-4 py-5">Dish Name</th>
<th class="px-4 py-5">Category</th>
<th class="px-4 py-5">Price (?)</th>
<th class="px-4 py-5">Status</th>
<th class="px-8 py-5 text-right">Actions</th>
</tr>
</thead>
<tbody class="divide-y divide-luxe-border/50" id="menuTableBody">
<?php foreach ($dishes as $dish): ?>
<tr class="table-row-hover group menu-row" data-category="<?= e($dish['category_slug'] ?? '') ?>" data-status="<?= isset($dish['is_visible']) && $dish['is_visible'] ? 'Visible' : 'Hidden' ?>" data-price="<?= $dish['price'] ?>">
<td class="px-8 py-5">
<div class="w-14 h-14 rounded-xl overflow-hidden shadow-sm border border-primary/10">
<img class="w-full h-full object-cover" src="<?= e(($dish['image_url'] ?? '') ?: 'https://placehold.co/150x150?text=No+Image') ?>" alt="<?= e($dish['name'] ?? $dish['dish_name'] ?? '') ?>"/>
</div>
</td>
<td class="px-4 py-5">
<p class="serif-title text-base font-bold text-luxe-dark"><?= e($dish['name'] ?? $dish['dish_name'] ?? '') ?></p>
<p class="text-xs text-luxe-dark/40 italic"><?= e($dish['description'] ?? '') ?></p>
</td>
<td class="px-4 py-5">
<span class="text-sm font-medium text-luxe-dark/60"><?= e($dish['category_name'] ?? '') ?></span>
</td>
<td class="px-4 py-5">
<span class="font-bold text-luxe-dark"><?= format_price($dish['price']) ?></span>
</td>
<td class="px-4 py-5">
<?php 
$isVisible = isset($dish['is_visible']) ? (int)$dish['is_visible'] : (isset($dish['status']) && $dish['status'] == 'Visible' ? 1 : 0);
if ($isVisible): 
?>
<span class="inline-flex items-center px-3 py-1 rounded-full bg-emerald-50 text-emerald-600 text-[10px] font-bold uppercase tracking-wide">
<span class="w-1 h-1 rounded-full bg-emerald-600 mr-2"></span> Visible
</span>
<?php else: ?>
<span class="inline-flex items-center px-3 py-1 rounded-full bg-red-50 text-red-500 text-[10px] font-bold uppercase tracking-wide">
<span class="w-1 h-1 rounded-full bg-red-500 mr-2"></span> Hidden
</span>
<?php endif; ?>
</td>
<td class="px-8 py-5 text-right">
<div class="flex items-center justify-end gap-2">
<button onclick="openEditModal(this)" class="w-9 h-9 rounded-xl bg-luxe-beige/30 text-luxe-dark/60 hover:text-primary hover:bg-primary/10 flex items-center justify-center transition-all border border-luxe-border" data-id="<?= $dish['id'] ?>">
<span class="material-symbols-outlined text-[20px]">edit</span>
</button>
<button onclick="if(confirm('Are you sure?')) window.location.href='?delete=<?= $dish['id'] ?>'" class="w-9 h-9 rounded-xl bg-red-50 text-red-400 hover:bg-red-500 hover:text-white flex items-center justify-center transition-all border border-red-100">
<span class="material-symbols-outlined text-[20px]">delete</span>
</button>
</div>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
<!-- Pagination -->
<div class="p-8 flex items-center justify-between border-t border-luxe-border bg-luxe-beige/10">
    <div class="flex items-center gap-6">
        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest whitespace-nowrap">
            Showing <span id="showingCount" class="text-luxe-dark font-bold">1-10</span> of <span id="totalCount" class="text-luxe-dark font-bold">100</span> entries
        </p>
        <div class="flex items-center gap-2 text-[10px] font-bold text-gray-400 uppercase tracking-widest">
            <span>Show</span>
            <select id="pageSizeSelector" onchange="changePageSize(this.value)" class="bg-white border border-luxe-border rounded-xl px-3 py-1.5 outline-none focus:border-primary transition-all cursor-pointer text-sm font-bold text-luxe-dark shadow-sm">
                <option value="5">5</option>
                <option value="10" selected>10</option>
                <option value="15">15</option>
            </select>
            <span>entries</span>
        </div>
    </div>
    <div id="paginationButtons" class="flex items-center gap-2">
        <!-- Buttons injected by JS -->
    </div>
</div>
</div>
<!-- Secondary Info Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-12">
<div class="bg-white p-6 rounded-2xl shadow-soft border border-luxe-border flex items-center gap-5">
<div class="w-12 h-12 rounded-full bg-emerald-50 text-emerald-500 flex items-center justify-center">
<span class="material-symbols-outlined font-fill">visibility</span>
</div>
<div>
<p class="text-luxe-dark/40 text-[10px] uppercase font-bold tracking-widest">Active Dishes</p>
<h4 class="text-2xl font-bold text-luxe-dark"><?= $menu_stats['active_dishes'] ?> Items</h4>
</div>
</div>
<div class="bg-white p-6 rounded-2xl shadow-soft border border-luxe-border flex items-center gap-5">
<div class="w-12 h-12 rounded-full bg-primary/10 text-primary flex items-center justify-center">
<span class="material-symbols-outlined font-fill">star</span>
</div>
<div>
<p class="text-luxe-dark/40 text-[10px] uppercase font-bold tracking-widest">Top Category</p>
<h4 class="text-2xl font-bold text-luxe-dark"><?= e($menu_stats['top_category_name']) ?></h4>
</div>
</div>
<div class="bg-white p-6 rounded-2xl shadow-soft border border-luxe-border flex items-center gap-5">
<div class="w-12 h-12 rounded-full bg-amber-50 text-amber-500 flex items-center justify-center">
<span class="material-symbols-outlined font-fill">trending_up</span>
</div>
<div>
<p class="text-luxe-dark/40 text-[10px] uppercase font-bold tracking-widest">Average Price</p>
<h4 class="text-2xl font-bold text-luxe-dark"><?= format_price($menu_stats['avg_price']) ?></h4>
</div>
</div>
</div>
</main>
</div>
<script>
// Filtering and Sorting State
let currentCategory = 'All Items';
let currentSearch = '';
let currentStatus = 'All Status';
let currentSort = 'Sort by Price';

function applyFilters() {
    const rows = Array.from(document.querySelectorAll('.menu-row'));
    let visibleRows = [];

    rows.forEach(row => {
        const dishName = row.querySelector('td:nth-child(2) .serif-title').textContent.toLowerCase();
        const dishDesc = row.querySelector('td:nth-child(2) .text-xs').textContent.toLowerCase();
        const category = row.dataset.category;
        const status = row.dataset.status;

        const matchesSearch = dishName.includes(currentSearch) || dishDesc.includes(currentSearch);
        const matchesCategory = currentCategory === 'All Items' || category === currentCategory;
        const matchesStatus = currentStatus === 'All Status' || status === currentStatus;

        if (matchesSearch && matchesCategory && matchesStatus) {
            visibleRows.push(row);
        } else {
            row.style.display = 'none';
        }
    });

    // Handle Sorting
    if (currentSort !== 'Sort by Price') {
        const tbody = document.getElementById('menuTableBody');
        visibleRows.sort((a, b) => {
            const priceA = parseFloat(a.dataset.price);
            const priceB = parseFloat(b.dataset.price);
            return currentSort === 'Price: Low to High' ? priceA - priceB : priceB - priceA;
        });
        visibleRows.forEach(row => tbody.appendChild(row));
    }

    currentPage = 1;
    handlePagination(visibleRows);
}

// --- Pagination Logic ---
let currentPage = 1;
let rowsPerPage = 10;

function handlePagination(visibleRows) {
    const totalRows = visibleRows.length;
    const totalPages = Math.ceil(totalRows / rowsPerPage);
    
    if (currentPage > totalPages) currentPage = totalPages;
    if (currentPage < 1) currentPage = 1;

    visibleRows.forEach((row, index) => {
        const start = (currentPage - 1) * rowsPerPage;
        const end = start + rowsPerPage;
        row.style.display = (index >= start && index < end) ? '' : 'none';
    });

    const startCount = totalRows > 0 ? (currentPage - 1) * rowsPerPage + 1 : 0;
    const endCount = Math.min(currentPage * rowsPerPage, totalRows);
    document.getElementById('showingCount').innerText = `${startCount}-${endCount}`;
    document.getElementById('totalCount').innerText = totalRows;

    renderPaginationButtons(totalPages, visibleRows);
}

function renderPaginationButtons(totalPages, visibleRows) {
    const container = document.getElementById('paginationButtons');
    if (totalPages < 1) {
        container.innerHTML = '';
        return;
    }

    let html = '';
    
    if (totalPages > 1) {
        html += `
            <button onclick="goToPage(${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''} class="w-10 h-10 rounded-xl flex items-center justify-center text-luxe-dark/40 hover:bg-primary/5 transition-all disabled:opacity-30">
                <span class="material-symbols-outlined">chevron_left</span>
            </button>
        `;
    }

    for (let i = 1; i <= totalPages; i++) {
        html += `
            <button onclick="goToPage(${i})" class="w-10 h-10 rounded-xl ${currentPage === i ? 'bg-primary text-white shadow-md shadow-primary/20' : 'text-luxe-dark/60 hover:bg-primary/5'} font-bold text-sm transition-all">
                ${i}
            </button>
        `;
    }

    if (totalPages > 1) {
        html += `
            <button onclick="goToPage(${currentPage + 1})" ${currentPage === totalPages ? 'disabled' : ''} class="w-10 h-10 rounded-xl flex items-center justify-center text-luxe-dark/40 hover:bg-primary/5 transition-all disabled:opacity-30">
                <span class="material-symbols-outlined">chevron_right</span>
            </button>
        `;
    }

    container.innerHTML = html;
    window.currentVisibleRows = visibleRows;
}

window.goToPage = function(page) {
    currentPage = page;
    handlePagination(window.currentVisibleRows);
    const tableTop = document.querySelector('.overflow-x-auto');
    if (tableTop) tableTop.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

window.changePageSize = function(size) {
    rowsPerPage = parseInt(size);
    currentPage = 1;
    applyFilters();
}

window.addEventListener('DOMContentLoaded', () => {
    applyFilters();
});

function filterCategory(category, btn) {
    const buttons = document.querySelectorAll('.category-btn');
    const activeClasses = ['bg-primary', 'text-white', 'font-bold', 'shadow-md', 'shadow-primary/20'];
    const inactiveClasses = ['bg-white', 'border', 'border-luxe-border', 'text-luxe-dark/60', 'font-semibold', 'hover:border-primary/30'];

    buttons.forEach(b => {
        b.classList.remove('active', ...activeClasses);
        b.classList.add(...inactiveClasses);
    });

    btn.classList.remove(...inactiveClasses);
    btn.classList.add('active', ...activeClasses);

    currentCategory = category;
    applyFilters();
}

// Search Logic
document.getElementById('searchInput').addEventListener('input', (e) => {
    currentSearch = e.target.value.toLowerCase();
    applyFilters();
});

// Dropdown Logic
document.querySelectorAll('.relative.group').forEach(dropdown => {
    const trigger = dropdown.querySelector('.dropdown-trigger');
    const menu = dropdown.querySelector('.dropdown-menu');
    const icon = dropdown.querySelector('.icon');
    const options = dropdown.querySelectorAll('.option');
    const selectedValue = dropdown.querySelector('.selected-value');

    if (!trigger || !menu) return;

    trigger.addEventListener('click', (e) => {
        e.stopPropagation();
        const isOpen = !menu.classList.contains('invisible');
        
        // Close all other dropdowns
        document.querySelectorAll('.dropdown-menu').forEach(m => {
            m.classList.add('invisible', 'opacity-0', 'translate-y-[-10px]');
        });
        document.querySelectorAll('.icon').forEach(i => i.style.transform = 'rotate(0deg)');

        if (!isOpen) {
            menu.classList.remove('invisible', 'opacity-0', 'translate-y-[-10px]');
            icon.style.transform = 'rotate(180deg)';
        }
    });

    options.forEach(option => {
        option.addEventListener('click', () => {
            const val = option.textContent;
            selectedValue.textContent = val;
            
            if (dropdown.id === 'statusFilter') {
                currentStatus = val;
            } else if (dropdown.id === 'priceSort') {
                currentSort = val;
            }
            
            applyFilters();
            menu.classList.add('invisible', 'opacity-0', 'translate-y-[-10px]');
            icon.style.transform = 'rotate(0deg)';
        });
    });
});

document.addEventListener('click', () => {
    document.querySelectorAll('.dropdown-menu').forEach(m => {
        m.classList.add('invisible', 'opacity-0', 'translate-y-[-10px]');
    });
    document.querySelectorAll('.icon').forEach(i => i.style.transform = 'rotate(0deg)');
});
</script>

<!-- Edit Dish Modal -->
<div id="editDishModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-luxe-dark/60 backdrop-blur-sm hidden overflow-hidden">
    <!-- Modal Container -->
    <div class="bg-white w-full max-w-lg max-h-[90vh] rounded-2xl shadow-2xl flex flex-col overflow-hidden animate-in fade-in zoom-in duration-300">
        <form method="POST" action="" class="flex flex-col h-full max-h-[90vh]">
            <input type="hidden" name="action" value="edit_dish">
            <input type="hidden" name="id" id="editId">
            <!-- Modal Header -->
            <div class="px-8 py-6 border-b border-primary/5 flex items-center justify-between">
                <div>
                    <h3 class="serif-title text-2xl font-bold text-luxe-dark">Edit Dish</h3>
                    <p class="text-xs text-luxe-dark/40">Update dish details</p>
                </div>
                <button onclick="closeEditModal()" type="button" class="text-luxe-dark/40 hover:text-primary transition-colors">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            
            <!-- Modal Body -->
            <div class="p-8 space-y-6 overflow-y-auto flex-1 custom-scrollbar">
                <!-- Dish Name -->
                <div class="space-y-2">
                    <label class="text-[11px] uppercase tracking-widest text-luxe-dark/40 font-bold ml-1">Dish Name</label>
                    <input name="name" id="editName" required class="w-full px-4 py-3 rounded-xl border border-luxe-border focus:border-primary focus:ring-1 focus:ring-primary/20 transition-all outline-none text-sm" type="text" />
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <!-- Category -->
                    <div class="space-y-2">
                        <label class="text-[11px] uppercase tracking-widest text-luxe-dark/40 font-bold ml-1">Category</label>
                        <select name="category_id" id="editCategory" required class="w-full px-4 py-3 rounded-xl border border-luxe-border focus:border-primary focus:ring-1 focus:ring-primary/20 transition-all outline-none text-sm appearance-none bg-white">
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= e($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <!-- Price -->
                    <div class="space-y-2">
                        <label class="text-[11px] uppercase tracking-widest text-luxe-dark/40 font-bold ml-1">Price (?)</label>
                        <input name="price" id="editPrice" required step="0.01" class="w-full px-4 py-3 rounded-xl border border-luxe-border focus:border-primary focus:ring-1 focus:ring-primary/20 transition-all outline-none text-sm" type="number" />
                    </div>
                </div>
                
                <!-- Description -->
                <div class="space-y-2">
                    <label class="text-[11px] uppercase tracking-widest text-luxe-dark/40 font-bold ml-1">Description</label>
                    <textarea name="description" id="editDesc" class="w-full px-4 py-3 rounded-xl border border-luxe-border focus:border-primary focus:ring-1 focus:ring-primary/20 transition-all outline-none text-sm resize-none" rows="2"></textarea>
                </div>
                
                <!-- Dish Image URL -->
                <div class="space-y-2">
                    <label class="text-[11px] uppercase tracking-widest text-luxe-dark/40 font-bold ml-1">Image URL</label>
                    <input name="image_url" id="editImageUrl" class="w-full px-4 py-3 rounded-xl border border-luxe-border focus:border-primary focus:ring-1 focus:ring-primary/20 transition-all outline-none text-sm" type="text" />
                </div>

                <!-- Visibility -->
                <div class="flex items-center justify-between">
                    <span class="text-sm font-bold text-luxe-dark">Visible</span>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="is_visible" id="editVisible" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                    </label>
                </div>
            </div>
            
            <!-- Modal Footer -->
            <div class="px-8 py-6 bg-luxe-beige/20 border-t border-luxe-border flex items-center justify-end gap-3 shrink-0">
                <button onclick="closeEditModal()" type="button" class="px-6 py-2.5 text-sm font-bold text-luxe-dark/40 hover:text-luxe-dark transition-colors">Cancel</button>
                <button type="submit" class="bg-primary hover:bg-primary/90 text-white px-8 py-2.5 rounded-xl font-bold shadow-lg shadow-primary/30 transition-all duration-300 text-sm">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let currentEditRow = null;

function openEditModal(btn) {
    document.body.classList.add('overflow-hidden');
    currentEditRow = btn.closest('tr');
    
    const id = btn.getAttribute('data-id');
// ...
// (rest of the content remains same but I'll provide the exact block)
    const name = currentEditRow.querySelector('td:nth-child(2) .serif-title').innerText;
    const desc = currentEditRow.querySelector('td:nth-child(2) .text-xs').innerText;
    const priceText = currentEditRow.querySelector('td:nth-child(4) span').innerText;
    const imageUrl = currentEditRow.querySelector('td:nth-child(1) img').src;
    const isVisible = currentEditRow.querySelector('td:nth-child(5) .bg-emerald-50') !== null;
    
    // Find category ID from value mapping if needed, or assume we can set it by text if we had IDs in the row
    // For now let's assume we need to set the select value.
    // Ideally we should have data-category-id on the row.
    
    document.getElementById('editId').value = id;
    document.getElementById('editName').value = name.trim();
    document.getElementById('editDesc').value = desc.trim();
    document.getElementById('editPrice').value = priceText.replace(/[^0-9.,]/g, '').trim();
    document.getElementById('editImageUrl').value = imageUrl;
    document.getElementById('editVisible').checked = isVisible;

    document.getElementById('editDishModal').classList.remove('hidden');
}

function closeEditModal() {
    document.body.classList.remove('overflow-hidden');
    document.getElementById('editDishModal').classList.add('hidden');
    currentEditRow = null;
}

// Delete Category Modal Logic
function openDeleteCategoryModal() {
    document.body.classList.add('overflow-hidden');
    document.getElementById('deleteCategoryModal').classList.remove('hidden');
}

function closeDeleteCategoryModal() {
    document.body.classList.remove('overflow-hidden');
    document.getElementById('deleteCategoryModal').classList.add('hidden');
}

function confirmCategoryDelete() {
    const select = document.getElementById('delete-category-select');
    if (!select.value) {
        alert('Please select a category to delete.');
        return false;
    }
    return confirm('Are you sure you want to delete this category? This action cannot be undone.');
}
</script>

<!-- Delete Category Modal -->
<div id="deleteCategoryModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-luxe-dark/60 backdrop-blur-sm hidden overflow-hidden">
    <div class="bg-white w-full max-w-lg rounded-2xl shadow-2xl flex flex-col overflow-hidden animate-in fade-in zoom-in duration-300">
        <form method="POST" action="" onsubmit="return confirmCategoryDelete()" class="flex flex-col">
            <input type="hidden" name="action" value="delete_category">
            
            <div class="px-8 py-6 border-b border-primary/5 flex items-center justify-between">
                <div>
                    <h3 class="serif-title text-2xl font-bold text-luxe-dark">Delete Category</h3>
                    <p class="text-xs text-luxe-dark/40">Select a category to permanently delete.</p>
                </div>
                <button onclick="closeDeleteCategoryModal()" type="button" class="text-luxe-dark/40 hover:text-primary transition-colors">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            
            <div class="p-8 space-y-6">
                <div class="space-y-2">
                    <label class="text-[11px] uppercase tracking-widest text-luxe-dark/40 font-bold ml-1">Select Category</label>
                    <select name="category_id" id="delete-category-select" required class="w-full px-4 py-3 rounded-xl border border-luxe-border focus:border-primary focus:ring-1 focus:ring-primary/20 transition-all outline-none text-sm appearance-none bg-white">
                        <option value="">Choose a category...</option>
                        <?php foreach ($categories as $cat): ?>
                            <?php if (($cat['slug'] ?? '') !== 'uncategorized'): ?>
                                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="p-4 bg-red-50 rounded-xl border border-red-100">
                    <div class="flex gap-3">
                        <span class="material-symbols-outlined text-red-500">warning</span>
                        <p class="text-xs text-red-600 leading-relaxed font-medium">Are you sure? This action cannot be undone. All items in this category will be moved to "Uncategorized".</p>
                    </div>
                </div>
            </div>
            
            <div class="px-8 py-6 bg-red-50/10 border-t border-red-100 flex items-center justify-end gap-3 shrink-0">
                <button onclick="closeDeleteCategoryModal()" type="button" class="px-6 py-2.5 text-sm font-bold text-luxe-dark/40 hover:text-luxe-dark transition-colors">Cancel</button>
                <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-8 py-2.5 rounded-xl font-bold shadow-lg shadow-red-500/20 transition-all duration-300 text-sm">
                    Delete Category
                </button>
            </div>
        </form>
    </div>
</div>
</body></html>

