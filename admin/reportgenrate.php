<?php 
require_once '../config/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
require_once '../actions/reservation/reservation_actions.php';

// --- Filtering Logic ---
$search = $_GET['search'] ?? '';
$from_date = $_GET['from_date'] ?? date('Y-m-d');
$to_date = $_GET['to_date'] ?? date('Y-m-d');
$report_type = $_GET['report_type'] ?? 'All Reports';

$data_to_display = [];

if ($report_type === 'Feedback / Reviews') {
    $sql = "SELECT * FROM feedback WHERE 1=1";
    $params = [];
    if (!empty($search)) {
        $sql .= " AND (customer_name LIKE ? OR email LIKE ? OR comment LIKE ?)";
        $params[] = "%$search%"; $params[] = "%$search%"; $params[] = "%$search%";
    }
    if (!empty($from_date) && !empty($to_date)) {
        $sql .= " AND created_at BETWEEN ? AND ?";
        $params[] = $from_date . " 00:00:00";
        $params[] = $to_date . " 23:59:59";
    }
    $sql .= " ORDER BY created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $data_to_display = $stmt->fetchAll();
} elseif ($report_type === 'Popular Food') {
    $sql = "SELECT * FROM menu_items WHERE 1=1";
    $params = [];
    if (!empty($search)) {
        $sql .= " AND (name LIKE ? OR description LIKE ?)";
        $params[] = "%$search%"; $params[] = "%$search%";
    }
    $sql .= " ORDER BY id DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $data_to_display = $stmt->fetchAll();
} else {
    $filters = [
        'search' => $search,
        'start_date' => $from_date,
        'end_date' => $to_date,
        'status' => 'All Statuses'
    ];

    if ($report_type === 'Revenue Summary') {
        $filters['status'] = 'Completed';
    } elseif ($report_type === 'Cancelled Reservations') {
        $filters['status'] = 'Cancelled';
    }

    $data_to_display = get_all_reservations($pdo, $filters);
}

// --- Stats Logic (Filtered) ---
$stats_sql = "SELECT 
    COUNT(*) as total_bookings,
    SUM(CASE WHEN status = 'Completed' THEN 100 ELSE 0 END) as total_revenue,
    AVG(CASE WHEN status = 'Completed' THEN 100 ELSE NULL END) as avg_order
    FROM reservations WHERE 1=1";
$stats_params = [];

if (!empty($from_date) && !empty($to_date)) {
    $stats_sql .= " AND reservation_date BETWEEN ? AND ?";
    $stats_params[] = $from_date;
    $stats_params[] = $to_date;
}
if (!empty($search)) {
    $stats_sql .= " AND (guest_name LIKE ? OR email LIKE ? OR phone LIKE ?)";
    $stats_params[] = "%$search%"; $stats_params[] = "%$search%"; $stats_params[] = "%$search%";
}
$stmt = $pdo->prepare($stats_sql);
$stmt->execute($stats_params);
$total_stats = $stmt->fetch();

$daily_count = $pdo->query("SELECT COUNT(*) FROM reservations WHERE reservation_date = CURDATE()")->fetchColumn() ?: 0;

// Monthly trend (this month vs last month)
$this_month = $pdo->query("SELECT COUNT(*) FROM reservations WHERE MONTH(reservation_date) = MONTH(CURDATE()) AND YEAR(reservation_date) = YEAR(CURDATE())")->fetchColumn() ?: 0;
$last_month = $pdo->query("SELECT COUNT(*) FROM reservations WHERE MONTH(reservation_date) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) AND YEAR(reservation_date) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))")->fetchColumn() ?: 0;
$trend = $last_month > 0 ? round((($this_month - $last_month) / $last_month) * 100) : 0;

// Most ordered (placeholder until orders table is confirmed)
$most_ordered = ['Wagyu Beef', 'Grilled Salmon', 'Truffle Pasta'];

// Peak Hour
$peak_hour_query = "SELECT reservation_time, COUNT(*) as count FROM reservations GROUP BY reservation_time ORDER BY count DESC LIMIT 1";
$peak_hour_data = $pdo->query($peak_hour_query)->fetch();
$peak_hour = $peak_hour_data ? date("h:i P", strtotime($peak_hour_data['reservation_time'])) : 'N/A';

// Repeat Customers
$repeat_query = "SELECT COUNT(*) FROM (SELECT email FROM reservations GROUP BY email HAVING COUNT(*) > 1) as repeats";
$repeat_count = $pdo->query($repeat_query)->fetchColumn() ?: 0;
$total_customers = $pdo->query("SELECT COUNT(DISTINCT email) FROM reservations")->fetchColumn() ?: 1;
$repeat_percent = round(($repeat_count / $total_customers) * 100);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports | LUXE Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght@700;900&display=swap">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: '#b76e79',
                        'primary-hover': '#a55f69',
                        'rose-accent': '#b76e79',
                        'rose-accent-hover': '#a35d68',
                        'luxe-dark': '#2b2b2b',
                        'luxe-beige': '#f4efec',
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
        .sidebar-active { background: #b76e79; color: white; }
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
        $breadcrumb = "DASHBOARD / REPORTS";
        $title = "Report Generation";
        include '../includes/admin_pageHeader.php';
        ?>
        <p class="text-gray-500 mt-1">Generate analytics and performance reports for restaurant management.</p>
    </div>
</header>
<!-- Report Configuration Cards -->
<section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-20">
<!-- Daily Booking Report -->
<div class="bg-white dark:bg-zinc-800 p-6 rounded-2xl shadow-sm border border-rose-accent/5 hover:shadow-md transition-shadow hover:-translate-y-1 hover:shadow-lg transition-all duration-300 h-full flex flex-col justify-between">
<div class="flex justify-between items-start mb-4">
<div class="p-2 bg-rose-accent/10 rounded-lg text-rose-accent">
<span class="material-symbols-outlined">event_available</span>
</div>
<span class="text-[10px] font-bold text-rose-accent bg-rose-accent/10 px-2 py-1 rounded uppercase">Daily</span>
</div>
<h3 class="font-bold text-lg mb-2">Daily Booking Report</h3>
<p class="text-xs text-slate-500 mb-4">Summary: Total <?= $daily_count ?> bookings today</p>
<div class="space-y-4">
<input id="daily_date" class="w-full rounded-lg border-rose-accent/20 focus:ring-rose-accent focus:border-rose-accent bg-background-light/30 text-sm py-2" type="date" value="<?= date('Y-m-d') ?>"/>
<button onclick="updateDailyReport()" class="w-full bg-rose-accent text-white py-2 rounded-lg text-sm font-bold hover:bg-rose-accent/90 transition-colors">Generate</button>
</div>
</div>
<!-- Monthly Reservation Report -->
<div class="bg-white dark:bg-zinc-800 p-6 rounded-2xl shadow-sm border border-rose-accent/5 hover:shadow-md transition-shadow hover:-translate-y-1 hover:shadow-lg transition-all duration-300 h-full flex flex-col justify-between">
<div class="flex justify-between items-start mb-4">
<div class="p-2 bg-rose-accent/10 rounded-lg text-rose-accent">
<span class="material-symbols-outlined">calendar_month</span>
</div>
<span class="text-[10px] font-bold text-rose-accent bg-rose-accent/10 px-2 py-1 rounded uppercase">Monthly</span>
</div>
<h3 class="font-bold text-lg mb-2">Monthly Reservation Report</h3>
<p class="text-xs <?= $trend >= 0 ? 'text-emerald-600' : 'text-rose-600' ?> mb-4 flex items-center gap-1">
<span class="material-symbols-outlined text-sm"><?= $trend >= 0 ? 'trending_up' : 'trending_down' ?></span> Trend: <?= $trend >= 0 ? '+' : '' ?><?= $trend ?>% from last month
                    </p>
<div class="space-y-4">
<select id="monthly_month" class="w-full rounded-lg border-rose-accent/20 focus:ring-rose-accent focus:border-rose-accent bg-background-light/30 text-sm py-2">
<option value="<?= date('Y-m') ?>"><?= date('F Y') ?></option>
<option value="<?= date('Y-m', strtotime('-1 month')) ?>"><?= date('F Y', strtotime('-1 month')) ?></option>
<option value="<?= date('Y-m', strtotime('-2 months')) ?>"><?= date('F Y', strtotime('-2 months')) ?></option>
</select>
<button onclick="updateMonthlyReport()" class="w-full bg-rose-accent text-white py-2 rounded-lg text-sm font-bold hover:bg-rose-accent/90 transition-colors">Generate</button>
</div>
</div>
<!-- Most Ordered Food -->
<div class="bg-white dark:bg-zinc-800 p-6 rounded-2xl shadow-sm border border-rose-accent/5 hover:shadow-md transition-shadow hover:-translate-y-1 hover:shadow-lg transition-all duration-300 h-full flex flex-col justify-between">
<div class="flex justify-between items-start mb-4">
<div class="p-2 bg-rose-accent/10 rounded-lg text-rose-accent">
<span class="material-symbols-outlined">restaurant_menu</span>
</div>
<span class="text-[10px] font-bold text-rose-accent bg-rose-accent/10 px-2 py-1 rounded uppercase">Menu</span>
</div>
<h3 class="font-bold text-lg mb-2">Most Ordered Food</h3>
<div class="text-[11px] text-slate-500 mb-4 flex flex-wrap gap-2">
<?php foreach ($most_ordered as $item): ?>
<span class="px-2 py-0.5 bg-slate-100 dark:bg-zinc-700 rounded-full"><?= $item ?></span>
<?php endforeach; ?>
</div>
<div class="space-y-4">
<select id="menu_limit" class="w-full rounded-lg border-rose-accent/20 focus:ring-rose-accent focus:border-rose-accent bg-background-light/30 text-sm py-2">
<option value="10">Top 10 Dishes</option>
<option value="25">Top 25 Dishes</option>
<option value="100">All Items</option>
</select>
<button onclick="updateMenuReport()" class="w-full bg-rose-accent text-white py-2 rounded-lg text-sm font-bold hover:bg-rose-accent/90 transition-colors">Generate</button>
</div>
</div>
<!-- Peak Hour Booking -->
<div class="bg-white dark:bg-zinc-800 p-6 rounded-2xl shadow-sm border border-rose-accent/5 hover:shadow-md transition-shadow hover:-translate-y-1 hover:shadow-lg transition-all duration-300 h-full flex flex-col justify-between">
<div class="flex justify-between items-start mb-4">
<div class="p-2 bg-rose-accent/10 rounded-lg text-rose-accent">
<span class="material-symbols-outlined">schedule</span>
</div>
<span class="text-[10px] font-bold text-rose-accent bg-rose-accent/10 px-2 py-1 rounded uppercase">Analytics</span>
</div>
<h3 class="font-bold text-lg mb-2">Peak Hour Booking</h3>
<p class="text-xs text-slate-500 mb-4">Busiest window: <?= $peak_hour ?></p>
<div class="space-y-4">
<div class="flex gap-2">
<input id="analytics_start" class="w-1/2 rounded-lg border-rose-accent/20 focus:ring-rose-accent focus:border-rose-accent bg-background-light/30 text-xs py-2" type="time" value="17:00"/>
<input id="analytics_end" class="w-1/2 rounded-lg border-rose-accent/20 focus:ring-rose-accent focus:border-rose-accent bg-background-light/30 text-xs py-2" type="time" value="23:00"/>
</div>
<button onclick="updateAnalyticsReport()" class="w-full bg-rose-accent text-white py-2 rounded-lg text-sm font-bold hover:bg-rose-accent/90 transition-colors">Generate</button>
</div>
</div>
<!-- Customer Visit Frequency -->
<div class="bg-white dark:bg-zinc-800 p-6 rounded-2xl shadow-sm border border-rose-accent/5 hover:shadow-md transition-shadow hover:-translate-y-1 hover:shadow-lg transition-all duration-300 h-full flex flex-col justify-between">
<div class="flex justify-between items-start mb-4">
<div class="p-2 bg-rose-accent/10 rounded-lg text-rose-accent">
<span class="material-symbols-outlined">group_work</span>
</div>
<span class="text-[10px] font-bold text-rose-accent bg-rose-accent/10 px-2 py-1 rounded uppercase">Loyalty</span>
</div>
<h3 class="font-bold text-lg mb-2">Customer Visit Frequency</h3>
<div class="mb-4">
<div class="flex justify-between text-[10px] font-bold mb-1">
<span>Repeat</span>
<span><?= $repeat_percent ?>%</span>
</div>
<div class="w-full bg-slate-100 dark:bg-zinc-700 h-1.5 rounded-full overflow-hidden">
<div class="bg-rose-accent h-full" style="width: <?= $repeat_percent ?>%"></div>
</div>
</div>
<div class="space-y-4">
<select id="loyalty_period" class="w-full rounded-lg border-rose-accent/20 focus:ring-rose-accent focus:border-rose-accent bg-background-light/30 text-sm py-2">
<option value="3">Last Quarter</option>
<option value="6">Last 6 Months</option>
<option value="12">Year to Date</option>
</select>
<button onclick="updateLoyaltyReport()" class="w-full bg-rose-accent text-white py-2 rounded-lg text-sm font-bold hover:bg-rose-accent/90 transition-colors">Generate</button>
</div>
</div>
<!-- Blank / Additional Action -->
<div class="bg-rose-accent/5 border-2 border-dashed border-rose-accent/20 p-6 rounded-2xl flex flex-col items-center justify-center text-center hover:-translate-y-1 hover:shadow-lg transition-all duration-300 h-full flex flex-col justify-between"><div class="group cursor-pointer flex flex-col items-center justify-center text-center py-10">
<span class="material-symbols-outlined text-rose-accent/40 text-4xl mb-2 group-hover:scale-110 group-hover:text-rose-accent transition-all duration-300">add_circle</span>
<p class="text-sm font-bold text-rose-accent/60 group-hover:text-rose-accent">Create Custom Report</p>
</div></div>
</section>
<!-- Preview Section Area -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
<div class="bg-white dark:bg-zinc-800 p-4 rounded-xl border border-rose-accent/5 shadow-sm">
<p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Total Revenue</p>
<p class="text-xl font-bold text-charcoal dark:text-white">?<?= number_format($total_stats['total_revenue'] ?? 0) ?></p>
</div>
<div class="bg-white dark:bg-zinc-800 p-4 rounded-xl border border-rose-accent/5 shadow-sm">
<p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Total Bookings</p>
<p class="text-xl font-bold text-charcoal dark:text-white"><?= $total_stats['total_bookings'] ?? 0 ?></p>
</div>
<div class="bg-white dark:bg-zinc-800 p-4 rounded-xl border border-rose-accent/5 shadow-sm">
<p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Avg Order</p>
<p class="text-xl font-bold text-charcoal dark:text-white">?<?= number_format($total_stats['avg_order'] ?? 0, 2) ?></p>
</div>
<div class="bg-white dark:bg-zinc-800 p-4 rounded-xl border border-rose-accent/5 shadow-sm">
<p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Peak Hour</p>
<p class="text-xl font-bold text-charcoal dark:text-white"><?= $peak_hour ?></p>
</div>
</div><section class="bg-white dark:bg-zinc-800 rounded-2xl shadow-sm border border-rose-accent/5 overflow-hidden">
<!-- Toolbar -->
<div class="px-6 py-4 border-b border-rose-accent/10 flex flex-col md:flex-row justify-between items-center gap-4">
    <div class="flex items-center gap-2 w-full md:w-auto">
        <div class="relative w-full md:w-96">
            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">search</span>
            <input id="search_input" class="w-full h-10 pl-10 pr-4 rounded-xl border-slate-200 dark:border-zinc-700 dark:bg-zinc-900 focus:ring-rose-accent text-sm" placeholder="Search report data..." type="text" value="<?= htmlspecialchars($search) ?>"/>
        </div>
        <button onclick="applyFilters()" class="h-10 px-4 bg-rose-accent text-white text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-rose-accent/90 transition-all shadow-sm">Search</button>
    </div>
    <div class="flex items-center gap-3 w-full md:w-auto overflow-x-auto pb-1 md:pb-0">
        <button id="exportBtn" class="h-10 px-5 flex items-center justify-center gap-2 bg-white dark:bg-zinc-900 text-charcoal dark:text-white border border-rose-accent/10 rounded-xl text-xs font-bold whitespace-nowrap hover:bg-rose-accent/5 transition-all shadow-sm">
            <span class="material-symbols-outlined text-sm">picture_as_pdf</span>
            Export PDF
        </button>
        <button id="exportExcelBtn" class="h-10 px-5 flex items-center justify-center gap-2 bg-white dark:bg-zinc-900 text-charcoal dark:text-white border border-rose-accent/10 rounded-xl text-xs font-bold whitespace-nowrap hover:bg-rose-accent/5 transition-all shadow-sm">
            <span class="material-symbols-outlined text-sm">description</span>
            Export Excel
        </button>
        <button id="printBtn" class="h-10 px-6 flex items-center justify-center gap-2 bg-rose-accent/10 text-rose-accent rounded-xl text-xs font-black whitespace-nowrap hover:bg-rose-accent/20 transition-all uppercase tracking-wide border border-rose-accent/20 shadow-sm">
            <span class="material-symbols-outlined text-sm">print</span>
            Print Report
        </button>
    </div>
</div><div class="px-6 py-4 bg-background-light/30 border-b border-rose-accent/10 flex flex-wrap gap-4 items-end">
    <div class="flex flex-col gap-1.5">
        <label class="text-[10px] font-bold text-rose-accent uppercase ml-1 tracking-wider">From Date</label>
        <input id="from_date" class="h-10 rounded-lg border-rose-accent/20 focus:ring-rose-accent bg-white dark:bg-zinc-900 text-xs px-3 w-40" type="date" value="<?= $from_date ?>"/>
    </div>
    <div class="flex flex-col gap-1.5">
        <label class="text-[10px] font-bold text-rose-accent uppercase ml-1 tracking-wider">To Date</label>
        <input id="to_date" class="h-10 rounded-lg border-rose-accent/20 focus:ring-rose-accent bg-white dark:bg-zinc-900 text-xs px-3 w-40" type="date" value="<?= $to_date ?>"/>
    </div>
    <div class="flex flex-col gap-1.5">
        <label class="text-[10px] font-bold text-rose-accent uppercase ml-1 tracking-wider">Report Type</label>
        <select id="report_type" onchange="applyFilters()" class="h-10 rounded-lg border-rose-accent/20 focus:ring-rose-accent bg-white dark:bg-zinc-900 text-xs px-3 w-48">
            <option <?= $report_type === 'All Reports' ? 'selected' : '' ?>>All Reports</option>
            <option <?= $report_type === 'Reservations' ? 'selected' : '' ?>>Reservations</option>
            <option <?= $report_type === 'Feedback / Reviews' ? 'selected' : '' ?>>Feedback / Reviews</option>
            <option <?= $report_type === 'Revenue Summary' ? 'selected' : '' ?>>Revenue Summary</option>
            <option <?= $report_type === 'Popular Food' ? 'selected' : '' ?>>Popular Food</option>
            <option <?= $report_type === 'Cancelled Reservations' ? 'selected' : '' ?>>Cancelled Reservations</option>
        </select>
    </div>
    <button onclick="applyFilters()" class="h-10 px-6 bg-charcoal text-white text-[10px] font-black uppercase tracking-widest rounded-lg hover:bg-charcoal/90 transition-all shadow-sm flex items-center justify-center">Apply Filters</button>
</div>
<!-- Data Table -->
<div class="overflow-x-auto">
<table class="w-full text-left">
<thead class="bg-background-light/50 dark:bg-zinc-900/50 border-b border-rose-accent/10 sticky top-0 z-10">
<?php if ($report_type === 'Feedback / Reviews'): ?>
<tr>
<th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider text-slate-500">Date</th>
<th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider text-slate-500">Customer Name</th>
<th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider text-slate-500">Rating</th>
<th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider text-slate-500">Comment</th>
<th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider text-slate-500">Status</th>
<th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider text-slate-500">Action</th>
</tr>
<?php elseif ($report_type === 'Popular Food'): ?>
<tr>
<th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider text-slate-500">Item Name</th>
<th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider text-slate-500">Description</th>
<th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider text-slate-500">Category</th>
<th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider text-slate-500">Price</th>
<th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider text-slate-500">Status</th>
<th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider text-slate-500">Action</th>
</tr>
<?php else: ?>
<tr>
<th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider text-slate-500"><div class="flex items-center gap-1">Date <span class="material-symbols-outlined text-xs cursor-pointer">unfold_more</span></div></th>
<th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider text-slate-500">Customer Name</th>
<th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider text-slate-500">Table No.</th>
<th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider text-slate-500"><div class="flex items-center gap-1">Status <span class="material-symbols-outlined text-xs cursor-pointer">unfold_more</span></div></th>
<th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider text-slate-500"><div class="flex items-center gap-1">Amount <span class="material-symbols-outlined text-xs cursor-pointer">unfold_more</span></div></th>
<th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider text-slate-500">Action</th>
</tr>
<?php endif; ?>
</thead>
<tbody class="divide-y divide-rose-accent/5">
<?php if (empty($data_to_display)): ?>
    <tr><td colspan="6" class="px-6 py-10 text-center text-slate-400">No report data found for selected filters.</td></tr>
<?php else: ?>
    <?php foreach ($data_to_display as $row): ?>
    <tr class="hover:bg-rose-accent/5 transition-colors">
    <?php if ($report_type === 'Feedback / Reviews'): ?>
        <td class="px-6 py-4 text-sm whitespace-nowrap"><?= date("M d, Y", strtotime($row['created_at'])) ?></td>
        <td class="px-6 py-4 text-sm font-medium"><?= htmlspecialchars($row['customer_name'] ?? 'N/A') ?></td>
        <td class="px-6 py-4 text-sm">
            <div class="flex text-amber-400">
                <?php for($i=1; $i<=5; $i++): ?>
                    <span class="material-symbols-outlined text-xs"><?= $i <= $row['rating'] ? 'star' : 'star_outline' ?></span>
                <?php endfor; ?>
            </div>
        </td>
        <td class="px-6 py-4 text-sm max-w-xs truncate"><?= htmlspecialchars($row['comment'] ?? '') ?></td>
        <td class="px-6 py-4 text-xs">
            <span class="px-2 py-1 bg-slate-100 text-slate-600 rounded-full font-bold uppercase"><?= $row['status'] ?></span>
        </td>
        <td class="px-6 py-4">
            <button onclick="window.location.href='feedback.php?search=<?= urlencode($row['customer_name']) ?>'" class="text-rose-accent hover:text-rose-accent/70"><span class="material-symbols-outlined">visibility</span></button>
        </td>
    <?php elseif ($report_type === 'Popular Food'): ?>
        <td class="px-6 py-4 text-sm font-medium"><?= htmlspecialchars($row['name']) ?></td>
        <td class="px-6 py-4 text-sm max-w-xs truncate"><?= htmlspecialchars($row['description']) ?></td>
        <td class="px-6 py-4 text-sm">Category #<?= htmlspecialchars($row['category_id']) ?></td>
        <td class="px-6 py-4 text-sm font-bold">?<?= number_format($row['price'], 2) ?></td>
        <td class="px-6 py-4 text-xs">
            <span class="px-2 py-1 <?= $row['is_visible'] ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' ?> rounded-full font-bold uppercase"><?= $row['is_visible'] ? 'Active' : 'Hidden' ?></span>
        </td>
        <td class="px-6 py-4">
            <button onclick="window.location.href='menumanage.php?search=<?= urlencode($row['name']) ?>'" class="text-rose-accent hover:text-rose-accent/70"><span class="material-symbols-outlined">visibility</span></button>
        </td>
    <?php else: ?>
        <td class="px-6 py-4 text-sm whitespace-nowrap"><?= date("M d, Y - H:i", strtotime($row['reservation_date'] . ' ' . $row['reservation_time'])) ?></td>
        <td class="px-6 py-4 text-sm font-medium"><?= htmlspecialchars($row['guest_name']) ?></td>
        <td class="px-6 py-4 text-sm">Table <?= htmlspecialchars($row['table_number'] ?? 'N/A') ?></td>
        <td class="px-6 py-4 text-xs">
            <?php
            $statusClass = 'bg-slate-100 text-slate-600';
            if ($row['status'] === 'Completed') $statusClass = 'bg-emerald-100 text-emerald-700';
            elseif ($row['status'] === 'Confirmed') $statusClass = 'bg-blue-100 text-blue-700';
            elseif ($row['status'] === 'Pending') $statusClass = 'bg-amber-100 text-amber-700';
            elseif ($row['status'] === 'Cancelled') $statusClass = 'bg-red-100 text-red-700';
            ?>
            <span class="px-2 py-1 <?= $statusClass ?> rounded-full font-bold uppercase"><?= $row['status'] ?></span>
        </td>
        <td class="px-6 py-4 text-sm font-bold">?<?= $row['status'] === 'Completed' ? '100.00' : '0.00' ?></td>
        <td class="px-6 py-4">
            <button onclick="window.location.href='reservation.php?search=<?= urlencode($row['guest_name']) ?>'" class="text-rose-accent hover:text-rose-accent/70"><span class="material-symbols-outlined">visibility</span></button>
        </td>
    <?php endif; ?>
    </tr>
    <?php endforeach; ?>
<?php endif; ?>
</tbody>
</table>
</div>
<!-- Pagination -->
<div class="p-6 border-t border-rose-accent/10 flex justify-between items-center">
<p class="text-xs text-slate-500 font-medium">Showing <?= count($data_to_display) ?> report results</p>
<div class="flex items-center gap-1">
<button class="p-2 rounded-lg border border-rose-accent/10 hover:bg-rose-accent/5 disabled:opacity-50" disabled="">
<span class="material-symbols-outlined text-sm">chevron_left</span>
</button>
<button class="size-8 rounded-lg bg-rose-accent text-white text-xs font-bold">1</button>
<button class="size-8 rounded-lg border border-rose-accent/10 text-xs font-bold hover:bg-rose-accent/5">2</button>
<button class="size-8 rounded-lg border border-rose-accent/10 text-xs font-bold hover:bg-rose-accent/5">3</button>
<button class="p-2 rounded-lg border border-rose-accent/10 hover:bg-rose-accent/5">
<span class="material-symbols-outlined text-sm">chevron_right</span>
</button>
</div>
</div>
</section>
</main>
</div>
<script>
document.getElementById('exportBtn').addEventListener('click', function () {
    const from = document.getElementById('from_date').value;
    const to = document.getElementById('to_date').value;
    const type = document.getElementById('report_type').value;
    window.location.href = `exportReport.php?format=pdf&from_date=${from}&to_date=${to}&report_type=${type}`;
});

document.getElementById('exportExcelBtn').addEventListener('click', function () {
    const from = document.getElementById('from_date').value;
    const to = document.getElementById('to_date').value;
    const type = document.getElementById('report_type').value;
    window.location.href = `exportReport.php?format=excel&from_date=${from}&to_date=${to}&report_type=${type}`;
});

document.getElementById('printBtn').addEventListener('click', function () {
    const from = document.getElementById('from_date').value;
    const to = document.getElementById('to_date').value;
    const type = document.getElementById('report_type').value;
    window.open(`printRopart.php?from_date=${from}&to_date=${to}&report_type=${type}`, '_blank');
});

function applyFilters() {
    const from = document.getElementById('from_date').value;
    const to = document.getElementById('to_date').value;
    const type = document.getElementById('report_type').value;
    const search = document.getElementById('search_input').value;
    
    let url = new URL(window.location.href);
    url.searchParams.set('from_date', from);
    url.searchParams.set('to_date', to);
    url.searchParams.set('report_type', type);
    url.searchParams.set('search', search);
    window.location.href = url.toString();
}

document.getElementById('search_input').addEventListener('keypress', function (e) {
    if (e.key === 'Enter') {
        applyFilters();
    }
});

// AJAX Report Card Handlers
async function fetchReportCard(type, data) {
    try {
        const params = new URLSearchParams({ report_type: type, ...data });
        const response = await fetch(`../actions/reports/get_report_data.php?${params.toString()}`);
        return await response.json();
    } catch (error) {
        console.error('Error fetching report:', error);
        return null;
    }
}

// Helper to find parent card by title
function getCardByTitle(title) {
    return Array.from(document.querySelectorAll('h3')).find(h => h.innerText.includes(title))?.closest('div');
}

async function updateDailyReport() {
    const date = document.getElementById('daily_date').value;
    const data = await fetchReportCard('daily', { date });
    if (data) {
        const card = getCardByTitle("Daily Booking Report");
        if (card) card.querySelector('p').innerText = `Summary: Total ${data.count} bookings for ${date}`;
    }
}

async function updateMonthlyReport() {
    const month = document.getElementById('monthly_month').value;
    const data = await fetchReportCard('monthly', { month });
    if (data) {
        const card = getCardByTitle("Monthly Reservation Report");
        if (card) {
            const trendEl = card.querySelector('p');
            trendEl.className = `text-xs ${data.trend >= 0 ? 'text-emerald-600' : 'text-rose-600'} mb-4 flex items-center gap-1`;
            trendEl.innerHTML = `<span class="material-symbols-outlined text-sm">${data.trend >= 0 ? 'trending_up' : 'trending_down'}</span> Trend: ${data.trend >= 0 ? '+' : ''}${data.trend}% from last month`;
        }
    }
}

async function updateMenuReport() {
    const limit = document.getElementById('menu_limit').value;
    const data = await fetchReportCard('menu', { limit });
    if (data && data.items) {
        const card = getCardByTitle("Most Ordered Food");
        if (card) {
            const container = card.querySelector('div.flex-wrap');
            container.innerHTML = data.items.map(item => `<span class="px-2 py-0.5 bg-slate-100 dark:bg-zinc-700 rounded-full">${item}</span>`).join('');
        }
    }
}

async function updateAnalyticsReport() {
    const start = document.getElementById('analytics_start').value;
    const end = document.getElementById('analytics_end').value;
    const data = await fetchReportCard('analytics', { start, end });
    if (data) {
        const card = getCardByTitle("Peak Hour Booking");
        if (card) card.querySelector('p').innerText = `Busiest window: ${data.peak_hour}`;
    }
}

async function updateLoyaltyReport() {
    const period = document.getElementById('loyalty_period').value;
    const data = await fetchReportCard('loyalty', { period });
    if (data) {
        const card = getCardByTitle("Customer Visit Frequency");
        if (card) {
            card.querySelector('span:last-child').innerText = `${data.percent}%`;
            card.querySelector('.bg-rose-accent').style.width = `${data.percent}%`;
        }
    }
}

// Cleanup
window.addEventListener('DOMContentLoaded', () => {
    // Ensuring date inputs match native format
    document.querySelectorAll('input[type="date"]').forEach(el => {
        if (!el.value) el.value = new Date().toISOString().split('T')[0];
    });
});
</script>
    </div>
</body>
</html>


