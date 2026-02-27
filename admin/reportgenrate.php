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

if ($report_type === 'All Reports') {
    // 1. Get Reservations
    $res_filters = ['search' => $search, 'start_date' => $from_date, 'end_date' => $to_date, 'status' => 'All Statuses'];
    $reservations = get_all_reservations($pdo, $res_filters);
    foreach($reservations as &$r) $r['row_type'] = 'Reservation';

    // 2. Get Feedback
    $sql_fb = "SELECT * FROM feedback WHERE 1=1";
    $fb_params = [];
    if (!empty($search)) {
        $sql_fb .= " AND (name LIKE ? OR email LIKE ? OR review_text LIKE ?)";
        $fb_params[] = "%$search%"; $fb_params[] = "%$search%"; $fb_params[] = "%$search%";
    }
    if (!empty($from_date) && !empty($to_date)) {
        $sql_fb .= " AND created_at BETWEEN ? AND ?";
        $fb_params[] = $from_date . " 00:00:00";
        $fb_params[] = $to_date . " 23:59:59";
    }
    $stmt_fb = $pdo->prepare($sql_fb);
    $stmt_fb->execute($fb_params);
    $feedbacks = $stmt_fb->fetchAll();
    foreach($feedbacks as &$f) $f['row_type'] = 'Feedback';

    // 3. Get Popular Food
    $sql_menu = "SELECT * FROM menu_items WHERE 1=1";
    $menu_params = [];
    if (!empty($search)) {
        $sql_menu .= " AND (name LIKE ? OR description LIKE ?)";
        $menu_params[] = "%$search%"; $menu_params[] = "%$search%";
    }
    $stmt_menu = $pdo->prepare($sql_menu);
    $stmt_menu->execute($menu_params);
    $menu_items = $stmt_menu->fetchAll();
    foreach($menu_items as &$m) $m['row_type'] = 'Popular Food';

    $data_to_display = array_merge($reservations, $feedbacks, $menu_items);
    
    // Normalized sort by date
    usort($data_to_display, function($a, $b) {
        $dateA = $a['reservation_date'] ?? substr($a['created_at'] ?? '', 0, 10) ?? '0000-00-00';
        $dateB = $b['reservation_date'] ?? substr($b['created_at'] ?? '', 0, 10) ?? '0000-00-00';
        return strcmp($dateB, $dateA);
    });
} elseif ($report_type === 'Feedback / Reviews') {
    $sql = "SELECT * FROM feedback WHERE 1=1";
    $params = [];
    if (!empty($search)) {
        $sql .= " AND (name LIKE ? OR email LIKE ? OR review_text LIKE ?)";
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

// Peak Hour (Filtered)
$peak_hour_query = "SELECT reservation_time, COUNT(*) as count FROM reservations WHERE 1=1";
$peak_params = [];
if (!empty($from_date) && !empty($to_date)) {
    $peak_hour_query .= " AND reservation_date BETWEEN ? AND ?";
    $peak_params[] = $from_date;
    $peak_params[] = $to_date;
}
if (!empty($search)) {
    $peak_hour_query .= " AND (guest_name LIKE ? OR email LIKE ? OR phone LIKE ?)";
    $peak_params[] = "%$search%"; $peak_params[] = "%$search%"; $peak_params[] = "%$search%";
}
$peak_hour_query .= " GROUP BY reservation_time ORDER BY count DESC LIMIT 1";
$stmt_peak = $pdo->prepare($peak_hour_query);
$stmt_peak->execute($peak_params);
$peak_hour_data = $stmt_peak->fetch();
$peak_hour = $peak_hour_data ? date("H:i", strtotime($peak_hour_data['reservation_time'])) : 'N/A';

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
        $breadcrumb = "DASHBOARD / REPORTS";
        $title = "Report Generation";
        include '../includes/admin_pageHeader.php';
        ?>
        <p class="text-luxe-grey-text mt-1">Generate analytics and performance reports for restaurant management.</p>
    </div>
</header>
<!-- Preview Section Area -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
<div class="bg-white dark:bg-zinc-800 p-4 rounded-xl border border-rose-accent/5 shadow-sm">
<p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Total Bookings</p>
<p class="text-xl font-bold text-charcoal dark:text-white"><?= $total_stats['total_bookings'] ?? 0 ?></p>
</div>
<div class="bg-white dark:bg-zinc-800 p-4 rounded-xl border border-rose-accent/5 shadow-sm">
<p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Peak Hour</p>
<p id="peak-hour-value" class="text-xl font-bold text-charcoal dark:text-white"><?= $peak_hour ?></p>
</div>
</div><section class="bg-white dark:bg-zinc-800 rounded-2xl shadow-sm border border-luxe-border overflow-hidden">
<!-- Toolbar -->
<div class="px-6 py-4 border-b border-luxe-border flex flex-col md:flex-row justify-between items-center gap-4">
    <div class="flex items-center gap-2 w-full md:w-auto">
        <div class="relative w-full md:w-96">
            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">search</span>
            <input id="search_input" class="w-full h-10 pl-10 pr-4 rounded-xl border border-luxe-border dark:border-zinc-700 dark:bg-zinc-900 focus:ring-primary focus:border-primary text-sm" placeholder="Search report data..." type="text" value="<?= htmlspecialchars($search) ?>"/>
        </div>
        <button onclick="applyFilters()" class="h-10 px-4 bg-primary text-white text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-primary-hover transition-all shadow-sm">Search</button>
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
        <button id="printBtn" class="h-10 px-6 flex items-center justify-center gap-2 bg-primary/10 text-primary rounded-xl text-xs font-black whitespace-nowrap hover:bg-primary/20 transition-all uppercase tracking-wide border border-primary/20 shadow-sm">
            <span class="material-symbols-outlined text-sm">print</span>
            Print Report
        </button>
    </div>
</div><div class="px-6 py-4 bg-background-light/30 border-b border-luxe-border flex flex-wrap gap-4 items-end">
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

            <option <?= $report_type === 'Popular Food' ? 'selected' : '' ?>>Popular Food</option>

        </select>
    </div>
    <button onclick="applyFilters()" class="h-10 px-6 bg-charcoal text-white text-[10px] font-black uppercase tracking-widest rounded-lg hover:bg-charcoal/90 transition-all shadow-sm flex items-center justify-center">Apply Filters</button>
</div>
<!-- Data Table -->
<div class="overflow-x-auto">
<table class="w-full text-left">
<thead class="bg-luxe-beige/50 dark:bg-zinc-900/50 border-b border-luxe-border sticky top-0 z-10">
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
<tbody class="divide-y divide-luxe-border">
<?php if (empty($data_to_display)): ?>
    <tr><td colspan="6" class="px-6 py-10 text-center text-slate-400">No report data found for selected filters.</td></tr>
<?php else: ?>
    <?php foreach ($data_to_display as $row): ?>
    <tr class="hover:bg-primary/5 transition-colors" <?= (($row['row_type'] ?? '') === 'Reservation' || !isset($row['row_type'])) ? 'data-reservation-time="'.($row['reservation_time'] ?? '').'"' : '' ?>>
    <?php if ($report_type === 'Feedback / Reviews'): ?>
        <td class="px-6 py-4 text-sm whitespace-nowrap"><?= date("M d, Y", strtotime($row['created_at'])) ?></td>
        <td class="px-6 py-4 text-sm font-medium"><?= htmlspecialchars($row['name'] ?? '') ?></td>
        <td class="px-6 py-4 text-sm">
            <div class="flex gap-0.5 text-amber-400">
                <?php 
                $rating = (int)($row['rating'] ?? 0);
                for($i=1; $i<=5; $i++): 
                ?>
                    <svg class="w-4 h-4 <?= $i <= $rating ? 'fill-current' : 'text-gray-300' ?>" viewBox="0 0 20 20">
                        <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"></path>
                    </svg>
                <?php endfor; ?>
            </div>
        </td>
        <td class="px-6 py-4 text-sm max-w-xs truncate"><?= htmlspecialchars($row['review_text'] ?? '') ?></td>
        <td class="px-6 py-4 text-xs">
            <span class="px-2 py-1 bg-slate-100 text-slate-600 rounded-full font-bold uppercase"><?= $row['status'] ?></span>
        </td>
        <td class="px-6 py-4">
            <button 
                onclick="openReportModal(this)" 
                data-type="Feedback"
                data-name="<?= htmlspecialchars($row['name'] ?? '') ?>"
                data-email="<?= htmlspecialchars($row['email'] ?? 'N/A') ?>"
                data-rating="<?= (int)($row['rating'] ?? 0) ?>"
                data-comment="<?= htmlspecialchars($row['review_text'] ?? '') ?>"
                data-date="<?= date('M d, Y', strtotime($row['created_at'])) ?>"
                data-status="<?= htmlspecialchars($row['status']) ?>"
                class="text-rose-accent hover:text-rose-accent/70">
                <span class="material-symbols-outlined">visibility</span>
            </button>
        </td>
    <?php elseif ($report_type === 'Popular Food'): ?>
        <td class="px-6 py-4 text-sm font-medium"><?= htmlspecialchars($row['name']) ?></td>
        <td class="px-6 py-4 text-sm max-w-xs truncate"><?= !empty(trim($row['description'] ?? '')) ? htmlspecialchars($row['description']) : 'N/A' ?></td>
        <td class="px-6 py-4 text-sm">Category #<?= htmlspecialchars($row['category_id']) ?></td>
        <td class="px-6 py-4 text-sm font-bold">?<?= number_format($row['price'], 2) ?></td>
        <td class="px-6 py-4 text-xs">
            <span class="px-2 py-1 <?= $row['is_visible'] ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' ?> rounded-full font-bold uppercase"><?= $row['is_visible'] ? 'Active' : 'Hidden' ?></span>
        </td>
        <td class="px-6 py-4">
            <button 
                onclick="openReportModal(this)" 
                data-type="Menu Item"
                data-name="<?= htmlspecialchars($row['name']) ?>"
                data-desc="<?= htmlspecialchars($row['description'] ?? 'N/A') ?>"
                data-price="<?= number_format($row['price'], 2) ?>"
                data-status="<?= $row['is_visible'] ? 'Active' : 'Hidden' ?>"
                class="text-rose-accent hover:text-rose-accent/70">
                <span class="material-symbols-outlined">visibility</span>
            </button>
        </td>
    <?php else: ?>
        <?php if (($row['row_type'] ?? '') === 'Feedback'): ?>
            <td class="px-6 py-4 text-sm whitespace-nowrap"><?= date("M d, Y", strtotime($row['created_at'])) ?></td>
            <td class="px-6 py-4 text-sm font-medium"><?= htmlspecialchars($row['name'] ?? '') ?></td>
            <td class="px-6 py-4 text-sm text-slate-400">Rating: <?= (int)$row['rating'] ?>/5</td>
            <td class="px-6 py-4 text-xs">
                <span class="px-2 py-1 bg-slate-100 text-slate-600 rounded-full font-bold uppercase"><?= $row['status'] ?></span>
            </td>
            <td class="px-6 py-4 text-sm font-bold text-slate-300">N/A</td>
            <td class="px-6 py-4">
                <button onclick="openReportModal(this)" data-type="Feedback" data-name="<?= htmlspecialchars($row['name'] ?? '') ?>" data-email="<?= htmlspecialchars($row['email'] ?? 'N/A') ?>" data-rating="<?= (int)($row['rating'] ?? 0) ?>" data-comment="<?= htmlspecialchars($row['review_text'] ?? '') ?>" data-date="<?= date('M d, Y', strtotime($row['created_at'])) ?>" data-status="<?= htmlspecialchars($row['status']) ?>" class="text-rose-accent hover:text-rose-accent/70"><span class="material-symbols-outlined">visibility</span></button>
            </td>
        <?php elseif (($row['row_type'] ?? '') === 'Popular Food'): ?>
            <td class="px-6 py-4 text-sm text-slate-300">N/A</td>
            <td class="px-6 py-4 text-sm font-medium"><?= htmlspecialchars($row['name']) ?></td>
            <td class="px-6 py-4 text-sm text-slate-400">Cat: #<?= htmlspecialchars($row['category_id']) ?></td>
            <td class="px-6 py-4 text-xs">
                <span class="px-2 py-1 <?= $row['is_visible'] ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' ?> rounded-full font-bold uppercase"><?= $row['is_visible'] ? 'Active' : 'Hidden' ?></span>
            </td>
            <td class="px-6 py-4 text-sm font-bold">?<?= number_format($row['price'], 2) ?></td>
            <td class="px-6 py-4">
                <button onclick="openReportModal(this)" data-type="Menu Item" data-name="<?= htmlspecialchars($row['name']) ?>" data-desc="<?= htmlspecialchars($row['description'] ?? 'N/A') ?>" data-price="<?= number_format($row['price'], 2) ?>" data-status="<?= $row['is_visible'] ? 'Active' : 'Hidden' ?>" class="text-rose-accent hover:text-rose-accent/70"><span class="material-symbols-outlined">visibility</span></button>
            </td>
        <?php else: ?>
            <td class="px-6 py-4 text-sm whitespace-nowrap"><?= date("M d, Y - H:i", strtotime(($row['reservation_date'] ?? '') . ' ' . ($row['reservation_time'] ?? ''))) ?></td>
            <td class="px-6 py-4 text-sm font-medium"><?= htmlspecialchars($row['guest_name'] ?? 'N/A') ?></td>
            <td class="px-6 py-4 text-sm">Table <?= htmlspecialchars($row['table_number'] ?? 'N/A') ?></td>
            <td class="px-6 py-4 text-xs">
                <?php
                $status = $row['status'] ?? 'Unknown';
                $statusClass = 'bg-slate-100 text-slate-600';
                if ($status === 'Completed') $statusClass = 'bg-emerald-100 text-emerald-700';
                elseif ($status === 'Confirmed') $statusClass = 'bg-blue-100 text-blue-700';
                elseif ($status === 'Pending') $statusClass = 'bg-amber-100 text-amber-700';
                elseif ($status === 'Cancelled') $statusClass = 'bg-red-100 text-red-700';
                ?>
                <span class="px-2 py-1 <?= $statusClass ?> rounded-full font-bold uppercase"><?= $status ?></span>
            </td>
            <td class="px-6 py-4 text-sm font-bold">?<?= ($row['status'] ?? '') === 'Completed' ? '100.00' : '0.00' ?></td>
            <td class="px-6 py-4">
                <button 
                    onclick="openReportModal(this)"
                    data-type="Reservation" 
                    data-name="<?= htmlspecialchars($row['guest_name'] ?? 'N/A') ?>"
                    data-email="<?= htmlspecialchars($row['email'] ?? 'N/A') ?>"
                    data-date="<?= date('M d, Y', strtotime($row['reservation_date'] ?? 'now')) ?>"
                    data-time="<?= date('h:i A', strtotime($row['reservation_time'] ?? 'now')) ?>"
                    data-table="<?= htmlspecialchars($row['table_number'] ?? 'N/A') ?>"
                    data-status="<?= htmlspecialchars($row['status'] ?? 'N/A') ?>"
                    data-amount="<?= ($row['status'] ?? '') === 'Completed' ? '100.00' : '0.00' ?>"
                    class="text-rose-accent hover:text-rose-accent/70">
                    <span class="material-symbols-outlined">visibility</span>
                </button>
            </td>
        <?php endif; ?>
    <?php endif; ?>
    </tr>
    <?php endforeach; ?>
<?php endif; ?>
</tbody>
</table>
</div>
<!-- Pagination -->
<div class="p-6 border-t border-luxe-border flex items-center justify-between">
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
    <div id="paginationButtons" class="flex items-center gap-1">
        <!-- Buttons injected by JS -->
    </div>
</div>
</section>
</main>
</div>

<!-- Report Details Modal -->
<div id="reportModal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
    <!-- Overlay -->
    <div onclick="closeReportModal()" class="absolute inset-0 bg-luxe-dark/40 backdrop-blur-sm"></div>
    
    <!-- Modal Card -->
    <div class="relative bg-[#faf9f8] w-full max-w-lg mx-4 rounded-[2.5rem] shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-300">
        <!-- Header -->
        <header class="bg-primary px-8 py-6 text-white flex justify-between items-center">
            <div>
                <h2 id="modalTitle" class="text-xl font-black font-serif">Report Details</h2>
                <p id="modalSubtitle" class="text-[10px] font-bold uppercase tracking-[0.2em] opacity-80">Information Overview</p>
            </div>
            <button onclick="closeReportModal()" class="size-10 rounded-xl bg-white/20 hover:bg-white/30 flex items-center justify-center transition-colors">
                <span class="material-symbols-outlined">close</span>
            </button>
        </header>

        <!-- Body -->
        <div class="px-8 py-8 space-y-6 max-h-[70vh] overflow-y-auto custom-scrollbar">
            <!-- Dynamic Content Area -->
            <div id="modalContentGrid" class="grid grid-cols-2 gap-4">
                <!-- Data items will be injected here -->
            </div>

            <!-- Detailed Section -->
            <div id="modalTextSection" class="hidden">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Content / Description</p>
                <div class="bg-luxe-beige/30 p-4 rounded-2xl border border-luxe-border italic text-sm text-luxe-dark">
                    <span id="modalLongText"></span>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #c67c7c44; border-radius: 10px; }
</style>

<script>
function openReportModal(btn) {
    const data = btn.dataset;
    const grid = document.getElementById('modalContentGrid');
    const textSection = document.getElementById('modalTextSection');
    const longText = document.getElementById('modalLongText');
    const subtitle = document.getElementById('modalSubtitle');
    
    subtitle.innerText = `${data.type} Information`;
    grid.innerHTML = '';
    textSection.classList.add('hidden');

    // Default fields to always show if present
    const fields = [
        { label: 'Name / Item', value: data.name },
        { label: 'Email', value: data.email },
        { label: 'Date', value: data.date },
        { label: 'Time', value: data.time },
        { label: 'Table', value: data.table },
        { label: 'Status', value: data.status },
        { label: 'Price / Amt', value: data.price || data.amount }
    ];

    fields.forEach(field => {
        if (field.value && field.value !== 'N/A') {
            const item = document.createElement('div');
            item.innerHTML = `
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">${field.label}</p>
                <p class="text-sm font-bold text-luxe-dark">${field.value}</p>
            `;
            grid.appendChild(item);
        }
    });

    // Handle Ratings
    if (data.rating && data.rating > 0) {
        const ratingItem = document.createElement('div');
        let stars = '';
        for(let i=1; i<=5; i++) {
            stars += `<span class="material-symbols-outlined text-xs ${i <= data.rating ? 'text-amber-400' : 'text-slate-300'}" style="font-variation-settings: 'FILL' ${i <= data.rating ? 1 : 0}">star</span>`;
        }
        ratingItem.innerHTML = `
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Rating</p>
            <div class="flex gap-0.5">${stars}</div>
        `;
        grid.appendChild(ratingItem);
    }

    // Handle Description / Comment
    const detailedText = data.comment || data.desc;
    if (detailedText && detailedText !== 'N/A' && detailedText !== '') {
        longText.innerText = `"${detailedText}"`;
        textSection.classList.remove('hidden');
    }

    document.getElementById('reportModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeReportModal() {
    document.getElementById('reportModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

// --- Pagination Logic ---
let currentPage = 1;
let rowsPerPage = 10;
let tableRows = [];

function initPagination() {
    const tbody = document.querySelector('tbody');
    // Skip "No report data found" row
    tableRows = Array.from(tbody.querySelectorAll('tr')).filter(row => !row.innerText.includes('No report data found'));
    
    if (tableRows.length === 0) {
        document.getElementById('paginationButtons').innerHTML = '';
        document.getElementById('showingCount').innerText = '0';
        document.getElementById('totalCount').innerText = '0';
        return;
    }
    paginateTable();
}

function paginateTable() {
    const totalRows = tableRows.length;
    const totalPages = Math.ceil(totalRows / rowsPerPage);
    
    // Bounds check
    if (currentPage > totalPages) currentPage = totalPages;
    if (currentPage < 1) currentPage = 1;

    // Slice rows
    tableRows.forEach((row, index) => {
        const start = (currentPage - 1) * rowsPerPage;
        const end = start + rowsPerPage;
        row.style.display = (index >= start && index < end) ? '' : 'none';
    });

    // Update count text
    const startCount = totalRows > 0 ? (currentPage - 1) * rowsPerPage + 1 : 0;
    const endCount = Math.min(currentPage * rowsPerPage, totalRows);
    document.getElementById('showingCount').innerText = `${startCount}-${endCount}`;
    document.getElementById('totalCount').innerText = totalRows;

    // Generate Buttons
    renderPaginationButtons(totalPages);
}

function renderPaginationButtons(totalPages) {
    const container = document.getElementById('paginationButtons');
    if (totalPages < 1) {
        container.innerHTML = '';
        return;
    }

    let html = '';
    
    if (totalPages > 1) {
        html += `
            <button onclick="goToPage(${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''} class="p-2 rounded-lg border border-rose-accent/10 hover:bg-rose-accent/5 disabled:opacity-50 transition-colors">
                <span class="material-symbols-outlined text-sm">chevron_left</span>
            </button>
        `;
    }

    // Simple page numbers
    for (let i = 1; i <= totalPages; i++) {
        html += `
            <button onclick="goToPage(${i})" class="size-8 rounded-lg ${currentPage === i ? 'bg-primary text-white' : 'border border-rose-accent/10 hover:bg-rose-accent/5 text-slate-600'} text-xs font-bold transition-all">
                ${i}
            </button>
        `;
    }

    if (totalPages > 1) {
        html += `
            <button onclick="goToPage(${currentPage + 1})" ${currentPage === totalPages ? 'disabled' : ''} class="p-2 rounded-lg border border-rose-accent/10 hover:bg-rose-accent/5 disabled:opacity-50 transition-colors">
                <span class="material-symbols-outlined text-sm">chevron_right</span>
            </button>
        `;
    }

    container.innerHTML = html;
}

window.goToPage = function(page) {
    currentPage = page;
    paginateTable();
}

window.changePageSize = function(size) {
    rowsPerPage = parseInt(size);
    currentPage = 1;
    paginateTable();
}

// --- Filter Integration ---
function handleSearch(query) {
    const tableBody = document.querySelector('tbody');
    const rows = Array.from(tableBody.querySelectorAll('tr')).filter(row => !row.innerText.includes('No report data found'));
    
    tableRows = rows.filter(row => {
        const text = row.innerText.toLowerCase();
        return text.includes(query.toLowerCase());
    });

    currentPage = 1;
    paginateTable();
    if (typeof calculatePeakHour === 'function') calculatePeakHour();
}

function calculatePeakHour() {
    const timeCounts = {};
    if (typeof tableRows === 'undefined' || tableRows.length === 0) {
        const displayEl = document.getElementById('peak-hour-value');
        if (displayEl) displayEl.innerText = 'N/A';
        return;
    }

    tableRows.forEach(row => {
        const time = row.getAttribute('data-reservation-time');
        if (time && time !== '') {
            timeCounts[time] = (timeCounts[time] || 0) + 1;
        }
    });

    let peakTime = 'N/A';
    let maxCount = 0;

    for (const time in timeCounts) {
        if (timeCounts[time] > maxCount) {
            maxCount = timeCounts[time];
            peakTime = time;
        }
    }

    const displayEl = document.getElementById('peak-hour-value');
    if (displayEl) {
        if (peakTime !== 'N/A') {
            const [h, m] = peakTime.split(':');
            const hours = h.padStart(2, '0');
            const minutes = m.padStart(2, '0');
            displayEl.innerText = `${hours}:${minutes}`;
        } else {
            displayEl.innerText = 'N/A';
        }
    }
}

document.getElementById('search_input').addEventListener('input', function (e) {
    handleSearch(e.target.value);
});

// Overriding enter key to prevent reload
document.getElementById('search_input').addEventListener('keypress', function (e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        handleSearch(e.target.value);
    }
});

window.addEventListener('DOMContentLoaded', () => {
    initPagination();
    calculatePeakHour();
});

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


