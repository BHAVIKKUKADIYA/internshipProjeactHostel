<?php 
require_once '../config/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
require_once '../actions/menu/menu_actions.php';
require_once '../actions/reservation/reservation_actions.php';
require_once '../actions/feedback/feedback_actions.php';

// Fetch metrics using centralized actions
$menu_stats = get_menu_stats($pdo);
$res_stats = get_reservation_stats($pdo);
$fb_stats = get_feedback_stats($pdo);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | LUXE Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght@700;900&display=swap">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
<!-- Top Bar -->
<header class="flex justify-between items-start mb-10">
<div>
<?php
$breadcrumb = "DASHBOARD / HOME";
$title = "Admin Dashboard";
include '../includes/admin_pageHeader.php';
?>
<p class="text-gray-500 dark:text-gray-400 mt-1">Manage website content and reservations</p>
</div>
<div class="flex items-center gap-6"><div class="flex gap-3 mr-4">
<button class="px-4 py-2 bg-primary text-white text-[10px] font-bold uppercase tracking-widest rounded-lg hover:bg-primary/90 transition-colors flex items-center gap-2">
<span class="material-symbols-outlined text-sm">add_circle</span> Add Reservation
  </button>
<button class="px-4 py-2 border border-primary text-primary text-[10px] font-bold uppercase tracking-widest rounded-lg hover:bg-primary/5 transition-colors flex items-center gap-2">
<span class="material-symbols-outlined text-sm">restaurant_menu</span> Add Menu Item
  </button>
</div>
<div class="text-right">
<p class="text-xs font-bold text-primary uppercase tracking-widest">Admin</p>
<p class="text-xs text-gray-400"><?= date('F j, Y') ?></p>
</div>
<div class="relative">
<img alt="Admin Profile" class="size-12 rounded-full object-cover border-2 border-primary/20 shadow-md" data-alt="Close up portrait of male administrator" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDpTjIHSBdNKFRznjf6IRHM_Thxuag9-C__AO9GsQS7OsgaGGPwa90AwtmAYG5rS8b0u7vxXexbLOz0yZ612DeC24iykVFbmhQ0rdCDfXddpGiTn0YqTEnVbuct-MWV0AmwbAakuFLs2BTNYjb7w_sfnqhDT3rCFy93TYP-upypxoukZswM6yL-bkp_UywrwpdQ3c2uM9ynuRohtB7x5JZtw3eq7rxC42Ys5W5e7HSTMHBDf0gPHr_zX60FxetkNOJ-zmwsAkLRF5U"/>
<div class="absolute bottom-0 right-0 size-3 bg-green-500 border-2 border-white dark:border-luxe-dark rounded-full"></div>
</div>
</div>
</header>
<!-- Stats Bar -->
<section class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
<!-- Metric 1 -->
<div class="bg-white dark:bg-luxe-dark rounded-xl shadow-sm border border-primary/5 flex items-center justify-between p-8 shadow-md">
<div>
<p class="text-gray-500 dark:text-gray-400 font-medium text-[10px] uppercase tracking-widest font-bold opacity-70">Total Dishes</p>
<h3 class="font-serif text-4xl font-bold mt-1"><?= $menu_stats['active_dishes'] ?></h3>
<div class="flex items-center gap-1 mt-2 text-green-500 text-xs font-bold font-semibold">
<span class="material-symbols-outlined text-sm">trending_up</span>
<span>Actual items in menu</span>
</div>
</div>
<div class="size-14 bg-primary/10 rounded-full flex items-center justify-center text-primary">
<span class="material-symbols-outlined text-3xl">restaurant</span>
</div>
</div>
<!-- Metric 2 -->
<div class="bg-white dark:bg-luxe-dark rounded-xl shadow-sm border border-primary/5 flex items-center justify-between p-8 shadow-md">
<div>
<p class="text-gray-500 dark:text-gray-400 font-medium text-[10px] uppercase tracking-widest font-bold opacity-70">Pending Reservations</p>
<h3 class="font-serif text-4xl font-bold mt-1"><?= $res_stats['pending'] ?></h3>
<div class="flex items-center gap-1 mt-2 text-primary text-xs font-bold font-semibold">
<span class="material-symbols-outlined text-sm">notification_important</span>
<span>Needs review</span>
</div>
</div>
<div class="size-14 bg-primary/10 rounded-full flex items-center justify-center text-primary">
<span class="material-symbols-outlined text-3xl">event_seat</span>
</div>
</div>
<!-- Metric 3 -->
<div class="bg-white dark:bg-luxe-dark rounded-xl shadow-sm border border-primary/5 flex items-center justify-between p-8 shadow-md">
<div>
<p class="text-gray-500 dark:text-gray-400 font-medium text-[10px] uppercase tracking-widest font-bold opacity-70">Total Reviews</p>
<h3 class="font-serif text-4xl font-bold mt-1"><?= str_pad($fb_stats['total'], 2, '0', STR_PAD_LEFT) ?></h3>
<div class="flex items-center gap-1 mt-2 text-primary text-xs font-bold font-semibold">
<span class="material-symbols-outlined text-sm">grade</span>
<span>Avg. <?= number_format($fb_stats['average'], 1) ?> stars</span>
</div>
</div>
<div class="size-14 bg-primary/10 rounded-full flex items-center justify-center text-primary">
<span class="material-symbols-outlined text-3xl">rate_review</span>
</div>
</div>
</section>
<!-- Main Content Grid -->
<h4 class="font-serif font-bold text-[#2b2b2b] tracking-tight mb-6 flex items-center">
            Management Modules
            <span class="h-px bg-primary/20 flex-1 ml-4"></span>
</h4>
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
<!-- Module 1: Homepage -->
<!-- Module 2: Menu -->
<div class="action-card bg-white dark:bg-luxe-dark p-8 rounded-xl shadow-sm border border-transparent flex flex-col items-center text-center group cursor-pointer">
<div class="size-16 bg-background-light dark:bg-white/5 rounded-2xl flex items-center justify-center text-primary mb-6 group-hover:bg-primary group-hover:text-white transition-all">
<span class="material-symbols-outlined text-4xl">flatware</span>
</div>
<div class="flex items-center gap-2 mb-2">
<span class="size-2 bg-green-500 rounded-full"></span>
<h5 class="font-serif font-bold text-[#2b2b2b]">Manage Menu</h5>
</div>
<p class="text-sm text-gray-500 dark:text-gray-400 mb-6 leading-relaxed">
                    Add new seasonal dishes, edit existing items, and update pricing or categories.
                </p>
<button onclick="window.location.href='menumanage.php'" class="mt-auto text-primary font-bold text-sm uppercase tracking-widest flex items-center gap-2 hover:gap-3 transition-all">
                    Edit Menu <span class="material-symbols-outlined text-sm">arrow_forward</span>
</button>
</div>
<!-- Module 3: Reservations -->
<div class="action-card bg-white dark:bg-luxe-dark p-8 rounded-xl shadow-sm border border-transparent flex flex-col items-center text-center group cursor-pointer">
<div class="size-16 bg-background-light dark:bg-white/5 rounded-2xl flex items-center justify-center text-primary mb-6 group-hover:bg-primary group-hover:text-white transition-all">
<span class="material-symbols-outlined text-4xl">confirmation_number</span>
</div>
<div class="flex items-center gap-2 mb-2">
<span class="size-2 bg-primary animate-pulse rounded-full"></span>
<h5 class="font-serif font-bold text-[#2b2b2b]">Reservations</h5>
</div>
<p class="text-sm text-gray-500 dark:text-gray-400 mb-6 leading-relaxed">
                    Review incoming table bookings, approve requests, or manage waitlists.
                </p>
<button onclick="window.location.href='reservation.php'" class="mt-auto text-primary font-bold text-sm uppercase tracking-widest flex items-center gap-2 hover:gap-3 transition-all">
                    View Bookings <span class="material-symbols-outlined text-sm">arrow_forward</span>
</button>
</div>
<!-- Module 4: Gallery Manager -->
<!-- Module 5: Testimonials -->
<div class="action-card bg-white dark:bg-luxe-dark p-8 rounded-xl shadow-sm border border-transparent flex flex-col items-center text-center group cursor-pointer">
<div class="size-16 bg-background-light dark:bg-white/5 rounded-2xl flex items-center justify-center text-primary mb-6 group-hover:bg-primary group-hover:text-white transition-all">
<span class="material-symbols-outlined text-4xl">star</span>
</div>
<div class="flex items-center gap-2 mb-2">
<span class="size-2 bg-gray-400 rounded-full"></span>
<h5 class="font-serif font-bold text-[#2b2b2b]">Feedback</h5>
</div>
<p class="text-sm text-gray-500 dark:text-gray-400 mb-6 leading-relaxed">
                    Moderate customer reviews, feature top testimonials, and hide outdated feedback.
                </p>
<button onclick="window.location.href='feedback.php'" class="mt-auto text-primary font-bold text-sm uppercase tracking-widest flex items-center gap-2 hover:gap-3 transition-all">
                    Moderate <span class="material-symbols-outlined text-sm">arrow_forward</span>
</button>
</div>
<!-- Module 6: Footer & Contact -->
<div class="action-card bg-white dark:bg-luxe-dark p-8 rounded-xl shadow-sm border border-transparent flex flex-col items-center text-center group cursor-pointer">
<div class="size-16 bg-background-light dark:bg-white/5 rounded-2xl flex items-center justify-center text-primary mb-6 group-hover:bg-primary group-hover:text-white transition-all">
<span class="material-symbols-outlined text-4xl">contact_support</span>
</div>
<div class="flex items-center gap-2 mb-2">
<span class="size-2 bg-green-500 rounded-full"></span>
<h5 class="font-serif font-bold text-[#2b2b2b]">Settings</h5>
</div>
<p class="text-sm text-gray-500 dark:text-gray-400 mb-6 leading-relaxed">
                    Update phone numbers, physical address, and linked social media profiles.
                </p>
<button onclick="window.location.href='setting.php'" class="mt-auto text-primary font-bold text-sm uppercase tracking-widest flex items-center gap-2 hover:gap-3 transition-all">
                    Update Info <span class="material-symbols-outlined text-sm">arrow_forward</span>
</button>
</div>
</div>
<!-- Performance Analytics Section -->
<section class="mt-16">
<h4 class="font-serif font-bold text-[#2b2b2b] tracking-tight mb-8 flex items-center">
        Performance Analytics
        <span class="h-px bg-primary/20 flex-1 ml-4"></span>
</h4>
<!-- Mini Stats Row -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
<div class="bg-white dark:bg-luxe-dark p-6 rounded-xl shadow-sm border border-primary/5 flex flex-col">
<span class="text-[10px] uppercase tracking-widest text-gray-400 font-bold mb-1">Average Rating</span>
<div class="flex items-center gap-2">
<span class="text-2xl font-serif font-bold"><?= number_format($fb_stats['average'] ?? 0, 1) ?></span>
<span class="material-symbols-outlined text-primary text-sm">grade</span>
</div>
</div>
<div class="bg-white dark:bg-luxe-dark p-6 rounded-xl shadow-sm border border-primary/5 flex flex-col">
<span class="text-[10px] uppercase tracking-widest text-gray-400 font-bold mb-1">Booking Growth</span>
<div class="flex items-center gap-2">
<span class="text-2xl font-serif font-bold text-green-600">+12.5%</span>
<span class="material-symbols-outlined text-green-600 text-sm">trending_up</span>
</div>
</div>
<div class="bg-white dark:bg-luxe-dark p-6 rounded-xl shadow-sm border border-primary/5 flex flex-col">
<span class="text-[10px] uppercase tracking-widest text-gray-400 font-bold mb-1">Peak Booking Time</span>
<div class="flex items-center gap-2">
<span class="text-2xl font-serif font-bold">08:00 PM</span>
<span class="material-symbols-outlined text-primary text-sm">schedule</span>
</div>
</div>
</div>
<!-- Main Analytics Grid -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
<!-- Table Booking Analytics -->
<div class="bg-white dark:bg-luxe-dark p-8 rounded-xl shadow-sm border border-primary/5">
<div class="flex justify-between items-center mb-8">
<h5 class="font-serif font-bold text-[#2b2b2b]">Table Booking Analytics</h5><p class="text-[10px] text-gray-400 uppercase tracking-widest mt-1">Weekly reservation trends</p>
<select class="text-xs border-primary/20 rounded-lg bg-transparent text-gray-500 focus:ring-primary focus:border-primary">
<option>Last 30 Days</option>
<option>Last 7 Days</option>
</select>
</div>
<div class="h-64 relative">
    <canvas id="reservationsChart"></canvas>
</div>
</div>
<!-- Review Sentiment -->
<div class="bg-white dark:bg-luxe-dark p-8 rounded-xl shadow-sm border border-primary/5">
<h5 class="font-serif font-bold text-[#2b2b2b] mb-8">Review Sentiment</h5><p class="text-[10px] text-gray-400 uppercase tracking-widest mt-1">Customer satisfaction metrics</p>
<div class="flex items-center justify-around h-64">
    <div class="size-60">
        <canvas id="statusChart"></canvas>
    </div>
</div>
</div>
</div>
<!-- Booking Heatmap -->
<div class="bg-white dark:bg-luxe-dark p-8 rounded-xl shadow-sm border border-primary/5">
<h5 class="font-serif font-bold text-[#2b2b2b] mb-6">Most Ordered Food <span class="text-xs font-normal text-gray-400 ml-2">(Top Dishes)</span></h5><p class="text-[10px] text-gray-400 uppercase tracking-widest mt-1">Customer favorite items</p>
<div class="h-64 relative mt-6">
    <canvas id="topFoodChart"></canvas>
</div>
</div>
</section>
<!-- System Status Bar -->
<footer class="flex items-center justify-between py-6 border-t border-primary/10 text-gray-400 text-[10px] uppercase tracking-widest mt-24">
<div class="flex gap-6">
<span class="flex items-center gap-2"><span class="size-1.5 bg-green-500 rounded-full"></span> System Live</span>
<span class="flex items-center gap-2"><span class="size-1.5 bg-green-500 rounded-full"></span> Database Encrypted</span>
</div>
<p>© 2023 LUXE Restaurant Group | Admin Portal v2.4.0</p>
</footer>
</main>
    </div>
<script>
async function initCharts() {
    // 1. Reservations & Revenue trend chart
    try {
        const resResponse = await fetch('../api/dashboard_reservations_chart.php');
        const resData = await resResponse.json();
        const revResponse = await fetch('../api/dashboard_revenue_chart.php');
        const revData = await revResponse.json();
        
        new Chart(document.getElementById('reservationsChart'), {
            type: 'line',
            data: {
                labels: resData.map(d => d.date),
                datasets: [
                    {
                        label: 'Reservations',
                        data: resData.map(d => d.count),
                        borderColor: '#c67c7c',
                        backgroundColor: 'rgba(198, 124, 124, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Revenue',
                        data: revData.map(d => d.revenue),
                        borderColor: '#2b2b2b',
                        backgroundColor: 'transparent',
                        borderWidth: 2,
                        borderDash: [5, 5],
                        tension: 0.4,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 10 } } } },
                scales: {
                    x: { ticks: { maxRotation: 0, autoSkip: true, maxTicksLimit: 7, font: { size: 10 } } },
                    y: { type: 'linear', display: true, position: 'left', beginAtZero: true, grid: { color: '#e5e0dd' }, title: { display: true, text: 'Reservations', font: { size: 10 } } },
                    y1: { type: 'linear', display: true, position: 'right', beginAtZero: true, grid: { drawOnChartArea: false }, title: { display: true, text: 'Revenue ($)', font: { size: 10 } } }
                }
            }
        });
    } catch (e) { console.error('Trend Chart Err:', e); }

    // 2. Status distribution chart
    try {
        const statusResponse = await fetch('../api/dashboard_status_chart.php');
        const statusData = await statusResponse.json();
        new Chart(document.getElementById('statusChart'), {
            type: 'doughnut',
            data: {
                labels: statusData.labels,
                datasets: [{
                    data: statusData.counts,
                    backgroundColor: ['#c67c7c', '#b26a6a', '#d4a1a1', '#f4efec'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 10 } } } }
            }
        });
    } catch (e) { console.error('Status Chart Err:', e); }

    // 3. Top Food Chart
    try {
        const foodResponse = await fetch('../api/dashboard_top_food_chart.php');
        const foodData = await foodResponse.json();
        new Chart(document.getElementById('topFoodChart'), {
            type: 'bar',
            data: {
                labels: foodData.labels,
                datasets: [{
                    label: 'Orders',
                    data: foodData.values,
                    backgroundColor: '#c67c7c',
                    borderRadius: 8
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { beginAtZero: true, ticks: { font: { size: 10 } } },
                    y: { grid: { display: false }, ticks: { font: { size: 10 } } }
                }
            }
        });
    } catch (e) { console.error('Food Chart Err:', e); }
}

document.addEventListener('DOMContentLoaded', initCharts);
</script>
</body>
</html>



