<?php 
require_once '../config/config.php';
require_once '../includes/functions.php';
require_once '../actions/reservation/reservation_actions.php';

// --- Filtering Logic ---
$from_date = $_GET['from_date'] ?? '';
$to_date = $_GET['to_date'] ?? '';
$report_type = $_GET['report_type'] ?? 'All Reports';

if ($report_type === 'Feedback / Reviews') {
    $transactions = $pdo->query("SELECT * FROM feedback ORDER BY created_at DESC")->fetchAll();
} elseif ($report_type === 'Popular Food') {
    $transactions = $pdo->query("SELECT * FROM menu_items ORDER BY id DESC")->fetchAll();
} else {
    $filters = [
        'start_date' => $from_date,
        'end_date' => $to_date,
        'status' => 'All Statuses'
    ];

    if ($report_type === 'Revenue Summary') {
        $filters['status'] = 'Completed';
    } elseif ($report_type === 'Cancelled Reservations') {
        $filters['status'] = 'Cancelled';
    }

    $transactions = get_all_reservations($pdo, $filters);
}

$stats_query = "SELECT 
    COUNT(*) as total_bookings,
    SUM(CASE WHEN status = 'Completed' THEN 100 ELSE 0 END) as total_revenue,
    AVG(CASE WHEN status = 'Completed' THEN 100 ELSE NULL END) as avg_order
    FROM reservations";
$total_stats = $pdo->query($stats_query)->fetch();

$this_month = $pdo->query("SELECT COUNT(*) FROM reservations WHERE MONTH(reservation_date) = MONTH(CURDATE()) AND YEAR(reservation_date) = YEAR(CURDATE())")->fetchColumn() ?: 0;
$last_month = $pdo->query("SELECT COUNT(*) FROM reservations WHERE MONTH(reservation_date) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) AND YEAR(reservation_date) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))")->fetchColumn() ?: 0;
$trend = $last_month > 0 ? round((($this_month - $last_month) / $last_month) * 100) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Report | LUXE Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght@700;900&display=swap">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'luxe-rose': '#B76E79',
                        'luxe-roseDark': '#a35d68',
                        'luxe-charcoal': '#2B2B2B',
                        'luxe-ivory': '#F8F6F2',
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
        .serif-heading { font-family: 'Playfair Display', serif; }
        .paper-shadow {
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
        }
        @media print {
            body { background: white !important; padding: 0 !important; }
            .dashboard-backdrop, aside, header, footer[data-purpose="modal-footer"], aside[data-purpose="print-settings"] {
                display: none !important;
            }
            .bg-black\/20 { background: transparent !important; backdrop-filter: none !important; }
            #print-modal { 
                position: static !important; 
                width: 100% !important; 
                height: auto !important; 
                box-shadow: none !important;
                border: none !important;
            }
            article {
                transform: none !important;
                box-shadow: none !important;
                border: none !important;
                width: 100% !important;
                padding: 0 !important;
            }
            section[data-purpose="document-preview"] {
                padding: 0 !important;
                background: white !important;
                overflow: visible !important;
            }
        }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #B76E79; border-radius: 10px; }
    </style>
</head>
<body class="h-screen w-full overflow-hidden flex items-center justify-center relative bg-stone-100">
<!-- BEGIN: Blurred Background Dashboard -->
<div class="absolute inset-0 z-0 dashboard-backdrop flex overflow-hidden filter blur-[2px] opacity-40">
<!-- Sidebar -->
<aside class="w-64 bg-white border-r border-gray-200 p-6 flex flex-col">
<div class="mb-10 flex items-center gap-3">
<div class="w-10 h-10 bg-luxe-rose rounded-full flex items-center justify-center text-white">Ψ</div>
<div>
<h1 class="font-bold text-lg leading-none">LUXE</h1>
<p class="text-[10px] uppercase tracking-widest text-luxe-rose">Management</p>
</div>
</div>
<nav class="space-y-4 opacity-50">
<div class="flex items-center gap-3 p-2 rounded text-gray-500"><span class="w-5 h-5 bg-gray-200"></span> Dashboard</div>
<div class="flex items-center gap-3 p-2 rounded text-gray-500"><span class="w-5 h-5 bg-gray-200"></span> Reservations</div>
<div class="flex items-center gap-3 p-2 rounded bg-luxe-rose/10 text-luxe-rose font-medium border-r-4 border-luxe-rose"><span class="w-5 h-5 bg-luxe-rose"></span> Reports</div>
</nav>
</aside>
<!-- Main Area Mock -->
<main class="flex-1 p-10 bg-luxe-ivory">
<header class="mb-8">
<h2 class="text-4xl serif-heading text-luxe-charcoal">Report Generation</h2>
</header>
<div class="grid grid-cols-3 gap-6">
<div class="h-48 bg-white rounded-xl"></div>
<div class="h-48 bg-white rounded-xl"></div>
<div class="h-48 bg-white rounded-xl"></div>
</div>
</main>
</div>
<!-- END: Blurred Background Dashboard -->
<!-- BEGIN: Modal Overlay -->
<div class="absolute inset-0 z-10 flex items-center justify-center bg-black/20 backdrop-blur-[2px]">
<!-- BEGIN: Print Preview Dialog -->
<div class="bg-white w-[1100px] h-[85vh] rounded-2xl shadow-2xl flex flex-col overflow-hidden animate-in fade-in zoom-in duration-300" data-purpose="print-preview-dialog" id="print-modal">
<!-- Modal Content Area -->
<div class="flex flex-1 overflow-hidden">
<!-- BEGIN: Left Side (Preview) -->
<section class="flex-1 bg-gray-100 flex justify-center overflow-y-auto p-12 custom-scrollbar border-r border-gray-200" data-purpose="document-preview">
<!-- The "Paper" -->
<article class="bg-white w-[210mm] min-h-[297mm] p-16 paper-shadow flex flex-col transform origin-top scale-95">
<!-- Paper Header -->
<div class="flex justify-between items-start border-b-2 border-luxe-rose pb-8 mb-10">
<div>
<h3 class="serif-heading text-3xl text-luxe-charcoal mb-1"><?= htmlspecialchars($report_type) ?> Report</h3>
<p class="text-sm text-gray-500 uppercase tracking-wide">Period: <?= $from_date ?: 'All Time' ?> <?= $to_date ? ' to ' . $to_date : '' ?></p>
</div>
<div class="text-right">
<p class="text-xs font-bold text-luxe-rose">LUXE RESTAURANT GROUP</p>
<p class="text-[10px] text-gray-400">Generated on: <?= date("M d, Y H:i A") ?></p>
</div>
</div>
<!-- Paper Content (Stats) -->
<div class="grid grid-cols-3 gap-8 mb-12">
<div class="border-l-2 border-luxe-rose/30 pl-4">
<p class="text-[10px] text-gray-400 uppercase font-semibold">Total Bookings</p>
<p class="text-2xl font-serif text-luxe-charcoal"><?= count($transactions) ?></p>
</div>
<div class="border-l-2 border-luxe-rose/30 pl-4">
<p class="text-[10px] text-gray-400 uppercase font-semibold">Growth Trend</p>
<p class="text-2xl font-serif <?= $trend >= 0 ? 'text-green-600' : 'text-rose-600' ?>"><?= $trend >= 0 ? '+' : '' ?><?= $trend ?>%</p>
</div>
<div class="border-l-2 border-luxe-rose/30 pl-4">
<p class="text-[10px] text-gray-400 uppercase font-semibold">Avg Revenue</p>
<p class="text-2xl font-serif text-luxe-charcoal">?<?= number_format($total_stats['avg_order'] ?? 0, 2) ?></p>
</div>
</div>
<!-- Paper Content (Mock Chart) -->
<div class="mb-12">
<p class="text-xs font-bold text-gray-700 uppercase mb-4">Weekly Booking Velocity</p>
<div class="h-48 w-full bg-gray-50 flex items-end justify-between px-10 pb-4 border rounded-lg">
<div class="w-12 bg-luxe-rose/40 h-24 rounded-t"></div>
<div class="w-12 bg-luxe-rose/60 h-32 rounded-t"></div>
<div class="w-12 bg-luxe-rose/80 h-40 rounded-t"></div>
<div class="w-12 bg-luxe-rose h-44 rounded-t"></div>
</div>
</div>
<!-- Paper Content (Table) -->
<div class="flex-1">
<p class="text-xs font-bold text-gray-700 uppercase mb-4">Top Performance Metrics</p>
<table class="w-full text-left text-sm border-collapse">
<thead>
<tr class="border-b border-gray-100 text-[11px] text-gray-400 uppercase">
<?php if ($report_type === 'Feedback / Reviews'): ?>
<th class="py-3 font-medium">Date</th>
<th class="py-3 font-medium">Customer</th>
<th class="py-3 font-medium text-right">Rating</th>
<th class="py-3 font-medium text-right">Comment</th>
<?php elseif ($report_type === 'Popular Food'): ?>
<th class="py-3 font-medium">Item Name</th>
<th class="py-3 font-medium">Category</th>
<th class="py-3 font-medium text-right">Price</th>
<th class="py-3 font-medium text-right">Status</th>
<?php else: ?>
<th class="py-3 font-medium">Date</th>
<th class="py-3 font-medium">Customer</th>
<th class="py-3 font-medium text-right">Status</th>
<th class="py-3 font-medium text-right">Amount</th>
<?php endif; ?>
</tr>
</thead>
<tbody class="text-gray-600">
<?php foreach (array_slice($transactions, 0, 15) as $row): ?>
<tr class="border-b border-gray-50">
<?php if ($report_type === 'Feedback / Reviews'): ?>
<td class="py-4"><?= date("M d, Y", strtotime($row['created_at'])) ?></td>
<td class="py-4"><?= htmlspecialchars($row['customer_name'] ?? 'N/A') ?></td>
<td class="py-4 text-right"><?= $row['rating'] ?> Stars</td>
<td class="py-4 text-right truncate max-w-[200px]"><?= htmlspecialchars($row['comment'] ?? '') ?></td>
<?php elseif ($report_type === 'Popular Food'): ?>
<td class="py-4"><?= htmlspecialchars($row['name']) ?></td>
<td class="py-4">Category #<?= $row['category_id'] ?></td>
<td class="py-4 text-right">?<?= number_format($row['price'], 2) ?></td>
<td class="py-4 text-right"><?= $row['is_visible'] ? 'Active' : 'Hidden' ?></td>
<?php else: ?>
<td class="py-4"><?= date("M d, Y", strtotime($row['reservation_date'])) ?></td>
<td class="py-4"><?= htmlspecialchars($row['guest_name']) ?></td>
<td class="py-4 text-right"><?= $row['status'] ?></td>
<td class="py-4 text-right">?<?= $row['status'] === 'Completed' ? '100.00' : '0.00' ?></td>
<?php endif; ?>
</tr>
<?php endforeach; ?>
<?php if (count($transactions) > 15): ?>
<tr><td colspan="4" class="py-4 text-center text-gray-400 italic">... and <?= count($transactions) - 15 ?> more records</td></tr>
<?php endif; ?>
</tbody>
</table>
</div>
<!-- Paper Footer -->
<div class="mt-auto pt-10 border-t border-gray-100 text-center">
<p class="text-[9px] text-gray-400 uppercase tracking-widest">Confidential Internal Document - Luxe Restaurant Management Systems</p>
<p class="text-[9px] text-gray-300 mt-1">Page 1 of 3</p>
</div>
</article>
</section>
<!-- END: Left Side (Preview) -->
<!-- BEGIN: Right Side (Settings) -->
<aside class="w-[320px] p-8 flex flex-col bg-white" data-purpose="print-settings">
<h4 class="text-xl serif-heading text-luxe-charcoal mb-8 pb-4 border-b">Print Settings</h4>
<div class="space-y-6 flex-1 overflow-y-auto custom-scrollbar pr-2">
<!-- Destination -->
<div class="space-y-2">
<label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider">Destination</label>
<select class="w-full border-gray-200 rounded-lg text-sm focus:ring-luxe-rose focus:border-luxe-rose">
<option>Save as PDF</option>
<option>Luxe Office Printer (HP-M404)</option>
<option>Kitchen Receipt Printer</option>
</select>
</div>
<!-- Pages -->
<div class="space-y-3">
<label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider">Pages</label>
<div class="space-y-2">
<label class="flex items-center text-sm text-gray-700 cursor-pointer">
<input checked="" class="text-luxe-rose focus:ring-luxe-rose mr-3" name="pages" type="radio"/>
                  All (3 pages)
                </label>
<label class="flex items-center text-sm text-gray-700 cursor-pointer">
<input class="text-luxe-rose focus:ring-luxe-rose mr-3" name="pages" type="radio"/>
                  Custom
                </label>
<input class="w-full border-gray-200 rounded-lg text-sm mt-1 focus:ring-luxe-rose focus:border-luxe-rose placeholder:text-gray-300" placeholder="e.g. 1-2" type="text"/>
</div>
</div>
<!-- Copies -->
<div class="space-y-2">
<label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider">Copies</label>
<input class="w-24 border-gray-200 rounded-lg text-sm focus:ring-luxe-rose focus:border-luxe-rose" min="1" type="number" value="1"/>
</div>
<!-- Layout -->
<div class="space-y-2">
<label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider">Layout</label>
<select class="w-full border-gray-200 rounded-lg text-sm focus:ring-luxe-rose focus:border-luxe-rose">
<option>Portrait</option>
<option>Landscape</option>
</select>
</div>
<!-- Color -->
<div class="space-y-2">
<label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider">Color</label>
<select class="w-full border-gray-200 rounded-lg text-sm focus:ring-luxe-rose focus:border-luxe-rose">
<option>Color</option>
<option>Black &amp; White</option>
</select>
</div>
<!-- More Settings Toggle (Visual Only) -->
<button class="text-luxe-rose text-sm font-medium hover:underline flex items-center gap-1">
              More settings
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
<path d="M19 9l-7 7-7-7" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path>
</svg>
</button>
</div>
</aside>
<!-- END: Right Side (Settings) -->
</div>
<!-- BEGIN: Footer Actions -->
<footer class="p-6 bg-gray-50 border-t flex justify-end items-center gap-4" data-purpose="modal-footer">
<button onclick="window.location.href='reportgenrate.php'" class="px-8 py-2.5 rounded-full text-sm font-semibold border border-gray-300 text-gray-600 hover:bg-gray-100 transition-colors">
          Cancel
        </button>
<button class="px-10 py-2.5 rounded-full text-sm font-semibold bg-luxe-rose text-white hover:bg-luxe-roseDark shadow-lg shadow-luxe-rose/20 transition-all flex items-center gap-2">
<svg class="h-4 w-4" fill="none" stroke="currentColor" viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
<path d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path>
</svg>
          Print Report
        </button>
</footer>
<!-- END: Footer Actions -->
</div>
<!-- END: Print Preview Dialog -->
</div>
<!-- END: Modal Overlay -->
<script data-purpose="ui-interactivity">

    // Handle actual window print
    document.querySelector('button.bg-luxe-rose').addEventListener('click', () => {
       window.print();
    });
  </script>
</body></html>
