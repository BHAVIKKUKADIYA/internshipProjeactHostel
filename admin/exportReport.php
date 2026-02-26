<?php 
require_once '../config/config.php';
require_once '../includes/functions.php';
require_once '../actions/reservation/reservation_actions.php';

// --- Filtering Logic ---
$from_date = $_GET['from_date'] ?? '';
$to_date = $_GET['to_date'] ?? '';
$report_type = $_GET['report_type'] ?? 'All Reports';
$format = $_GET['format'] ?? 'pdf';

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

// --- Export Logic ---
if ($format === 'csv' || $format === 'excel') {
    $filename = "report_" . date("Y-m-d") . ".csv";
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    if ($report_type === 'Feedback / Reviews') {
        fputcsv($output, ['Date', 'Customer Name', 'Rating', 'Comment', 'Status']);
        foreach ($transactions as $row) {
            fputcsv($output, [date("Y-m-d", strtotime($row['created_at'])), $row['customer_name'], $row['rating'], $row['comment'], $row['status']]);
        }
    } elseif ($report_type === 'Popular Food') {
        fputcsv($output, ['Item Name', 'Description', 'Category ID', 'Price', 'Visible']);
        foreach ($transactions as $row) {
            fputcsv($output, [$row['name'], $row['description'], $row['category_id'], $row['price'], $row['is_visible'] ? 'Yes' : 'No']);
        }
    } else {
        fputcsv($output, ['Date', 'Guest Name', 'Status', 'Table', 'Amount']);
        foreach ($transactions as $row) {
            fputcsv($output, [$row['reservation_date'], $row['guest_name'], $row['status'], $row['table_number'] ?? 'N/A', $row['status'] === 'Completed' ? '100.00' : '0.00'
            ]);
        }
    }
    fclose($output);
    exit;
}

$stats_query = "SELECT 
    COUNT(*) as total_bookings,
    SUM(CASE WHEN status = 'Completed' THEN 100 ELSE 0 END) as total_revenue,
    AVG(CASE WHEN status = 'Completed' THEN 100 ELSE NULL END) as avg_order
    FROM reservations";
$total_stats = $pdo->query($stats_query)->fetch();
?>
<!DOCTYPE html>

<html lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>LUXE - Export to PDF Preview</title>
<!-- Tailwind CSS CDN with Plugins -->
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<!-- Google Fonts: Playfair Display for Serifs, Inter for Sans-Serif -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&amp;family=Playfair+Display:wght@600;700&amp;display=swap" rel="stylesheet"/>
<script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            luxe: {
              ivory: '#F8F6F2',
              rose: '#B76E79',
              roseHover: '#a35d68',
              charcoal: '#2B2B2B',
              muted: '#717171',
              border: '#E5E1DA'
            }
          },
          fontFamily: {
            serif: ['"Playfair Display"', 'serif'],
            sans: ['Inter', 'sans-serif'],
          }
        }
      }
    }
  </script>
<style data-purpose="typography">
    body {
      font-family: 'Inter', sans-serif;
      color: #2B2B2B;
    }
    h1, h2, h3, .serif-text {
      font-family: 'Playfair Display', serif;
    }
  </style>
<style data-purpose="custom-ui">
    .paper-shadow {
      box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
    }
    .scrollbar-hide::-webkit-scrollbar {
      display: none;
    }
    .toggle-checkbox:checked {
      right: 0;
      border-color: #B76E79;
      background-color: #B76E79;
    }
    .toggle-checkbox:checked + .toggle-label {
      background-color: #B76E79;
    }
  </style>
</head>
<body class="bg-stone-200/50 min-h-screen flex items-center justify-center p-4 lg:p-8">
<span class="text-xs text-luxe-muted italic">Scale: 75%</span>
</div>
<!-- BEGIN: The "PDF" Paper -->
<div class="bg-white w-full max-w-[595px] min-h-[842px] paper-shadow p-12 flex flex-col" id="pdf-document">
<!-- Header Section -->
<header class="flex justify-between items-start border-b border-stone-100 pb-8 mb-8">
<div>
<div class="text-2xl font-bold tracking-tighter luxe-charcoal serif-text mb-1">LUXE</div>
<p class="text-[10px] text-luxe-muted uppercase tracking-widest">Fine Dining Excellence</p>
</div>
<div class="text-right">
<h1 class="text-xl font-bold text-luxe-charcoal serif-text"><?= htmlspecialchars($report_type) ?> Report</h1>
<p class="text-xs text-luxe-muted mt-1">Generated: <?= date("F d, Y") ?></p>
</div>
</header>
<!-- Metrics Summary -->
<div class="grid grid-cols-3 gap-4 mb-10">
<div class="bg-luxe-ivory p-4 rounded border border-luxe-border/50">
<p class="text-[10px] uppercase text-luxe-muted mb-1">Total Revenue</p>
<p class="text-lg font-semibold text-luxe-charcoal serif-text">?<?= number_format($total_stats['total_revenue'] ?? 0, 2) ?></p>
</div>
<div class="bg-luxe-ivory p-4 rounded border border-luxe-border/50">
<p class="text-[10px] uppercase text-luxe-muted mb-1">Total Bookings</p>
<p class="text-lg font-semibold text-luxe-charcoal serif-text"><?= count($transactions) ?></p>
</div>
<div class="bg-luxe-ivory p-4 rounded border border-luxe-border/50">
<p class="text-[10px] uppercase text-luxe-muted mb-1">Avg. Ticket</p>
<p class="text-lg font-semibold text-luxe-charcoal serif-text">?<?= number_format($total_stats['avg_order'] ?? 0, 2) ?></p>
</div>
</div>
<!-- Trend Chart Placeholder -->
<div class="mb-10">
<h3 class="text-xs font-semibold uppercase text-luxe-charcoal mb-4 tracking-wider">Revenue Trend</h3>
<div class="w-full h-32 flex items-end justify-between px-2 border-b border-luxe-border pt-4">
<!-- Simulated Line Chart -->
<svg class="w-full h-full" preserveaspectratio="none" viewbox="0 0 500 100">
<path d="M0,80 Q50,70 100,40 T200,50 T300,20 T400,30 T500,10" fill="none" stroke="#B76E79" stroke-width="2"></path>
<circle cx="100" cy="40" fill="#B76E79" r="3"></circle>
<circle cx="300" cy="20" fill="#B76E79" r="3"></circle>
<circle cx="500" cy="10" fill="#B76E79" r="3"></circle>
</svg>
</div>
<div class="flex justify-between mt-2 text-[9px] text-luxe-muted font-medium uppercase">
<span>Oct 01</span><span>Oct 08</span><span>Oct 15</span><span>Oct 22</span><span>Oct 31</span>
</div>
</div>
<!-- Data Table -->
<div class="flex-grow">
<h3 class="text-xs font-semibold uppercase text-luxe-charcoal mb-4 tracking-wider">Top Performing Dates</h3>
<table class="w-full text-left text-[11px]">
<thead>
<tr class="border-b border-luxe-border text-luxe-muted uppercase tracking-tighter">
<?php if ($report_type === 'Feedback / Reviews'): ?>
<th class="py-2 font-medium">Date</th>
<th class="py-2 font-medium text-center">Customer</th>
<th class="py-2 font-medium text-center">Rating</th>
<th class="py-2 font-medium text-right">Comment</th>
<?php elseif ($report_type === 'Popular Food'): ?>
<th class="py-2 font-medium">Item Name</th>
<th class="py-2 font-medium text-center">Category</th>
<th class="py-2 font-medium text-center">Price</th>
<th class="py-2 font-medium text-right">Status</th>
<?php else: ?>
<th class="py-2 font-medium">Date</th>
<th class="py-2 font-medium text-center">Guest</th>
<th class="py-2 font-medium text-center">Status</th>
<th class="py-2 font-medium text-right">Amount</th>
<?php endif; ?>
</tr>
</thead>
<tbody class="text-luxe-charcoal">
<?php foreach (array_slice($transactions, 0, 10) as $row): ?>
<tr class="border-b border-stone-50">
<?php if ($report_type === 'Feedback / Reviews'): ?>
<td class="py-3"><?= date("M d, Y", strtotime($row['created_at'])) ?></td>
<td class="py-3 text-center"><?= htmlspecialchars($row['customer_name'] ?? 'N/A') ?></td>
<td class="py-3 text-center"><?= $row['rating'] ?> Stars</td>
<td class="py-3 text-right truncate max-w-[150px]"><?= htmlspecialchars($row['comment'] ?? '') ?></td>
<?php elseif ($report_type === 'Popular Food'): ?>
<td class="py-3"><?= htmlspecialchars($row['name']) ?></td>
<td class="py-3 text-center">Category #<?= $row['category_id'] ?></td>
<td class="py-3 text-center">?<?= number_format($row['price'], 2) ?></td>
<td class="py-3 text-right"><?= $row['is_visible'] ? 'Active' : 'Hidden' ?></td>
<?php else: ?>
<td class="py-3"><?= date("M d, Y", strtotime($row['reservation_date'])) ?></td>
<td class="py-3 text-center"><?= htmlspecialchars($row['guest_name']) ?></td>
<td class="py-3 text-center"><?= $row['status'] ?></td>
<td class="py-3 text-right">?<?= $row['status'] === 'Completed' ? '100.00' : '0.00' ?></td>
<?php endif; ?>
</tr>
<?php endforeach; ?>
<?php if (count($transactions) > 10): ?>
<tr><td colspan="4" class="py-3 text-center text-luxe-muted italic">... and <?= count($transactions) - 10 ?> more records</td></tr>
<?php endif; ?>
</tbody>
</table>
</div>
<!-- Footer -->
<footer class="mt-8 pt-6 border-t border-stone-100 flex justify-between items-center text-[9px] text-luxe-muted italic">
<span>Confidentially prepared for LUXE Admin</span>
<span>Page 1 of 12</span>
</footer>
</div>
<!-- END: The "PDF" Paper -->
</section>
<!-- END: Left Side -->
<!-- BEGIN: Right Side - Export Settings -->
<aside class="w-full md:w-80 lg:w-96 bg-luxe-ivory p-8 flex flex-col" data-purpose="settings-panel">
<div class="mb-8">
<h2 class="text-2xl font-bold text-luxe-charcoal serif-text">Export Settings</h2>
<p class="text-sm text-luxe-muted mt-1">Configure your document output</p>
</div>
<div class="flex-grow space-y-6 overflow-y-auto scrollbar-hide pr-2">
<!-- File Name Input -->
<div data-purpose="setting-field">
<label class="block text-xs font-semibold text-luxe-charcoal uppercase tracking-wider mb-2" for="file-name">File Name</label>
<input class="w-full bg-white border-luxe-border rounded-md text-sm focus:ring-luxe-rose focus:border-luxe-rose" id="file-name" type="text" value="LUXE_Monthly_Report_Oct23"/>
</div>
<!-- Format Selection -->
<div data-purpose="setting-field">
<label class="block text-xs font-semibold text-luxe-charcoal uppercase tracking-wider mb-2">File Format</label>
<div class="grid grid-cols-3 gap-2">
<button onclick="changeFormat('pdf')" class="format-btn py-2 px-3 text-xs font-medium rounded border <?= $format === 'pdf' ? 'border-luxe-rose bg-luxe-rose text-white' : 'border-luxe-border bg-white text-luxe-charcoal hover:bg-stone-50' ?>">PDF</button>
<button onclick="changeFormat('excel')" class="format-btn py-2 px-3 text-xs font-medium rounded border <?= $format === 'excel' ? 'border-luxe-rose bg-luxe-rose text-white' : 'border-luxe-border bg-white text-luxe-charcoal hover:bg-stone-50' ?>">Excel</button>
<button onclick="changeFormat('csv')" class="format-btn py-2 px-3 text-xs font-medium rounded border <?= $format === 'csv' ? 'border-luxe-rose bg-luxe-rose text-white' : 'border-luxe-border bg-white text-luxe-charcoal hover:bg-stone-50' ?>">CSV</button>
</div>
<script>
function changeFormat(f) {
    let url = new URL(window.location.href);
    url.searchParams.set('format', f);
    window.location.href = url.toString();
}
</script>
</div>
<!-- Page Range -->
<div data-purpose="setting-field">
<label class="block text-xs font-semibold text-luxe-charcoal uppercase tracking-wider mb-2">Page Range</label>
<select class="w-full bg-white border-luxe-border rounded-md text-sm focus:ring-luxe-rose focus:border-luxe-rose">
<option>All Pages (12)</option>
<option>Current Page Only</option>
<option>Custom Range...</option>
</select>
</div>
<!-- Toggles Section -->
<div class="pt-4 border-t border-luxe-border space-y-4" data-purpose="toggles">
<!-- Charts Toggle -->
<div class="flex items-center justify-between">
<span class="text-sm text-luxe-charcoal font-medium">Include Charts</span>
<label class="relative inline-flex items-center cursor-pointer">
<input checked="" class="sr-only peer" type="checkbox"/>
<div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-luxe-rose"></div>
</label>
</div>
<!-- Summary Tables Toggle -->
<div class="flex items-center justify-between">
<span class="text-sm text-luxe-charcoal font-medium">Summary Tables</span>
<label class="relative inline-flex items-center cursor-pointer">
<input checked="" class="sr-only peer" type="checkbox"/>
<div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-luxe-rose"></div>
</label>
</div>
<!-- Detailed Logs Toggle -->
<div class="flex items-center justify-between">
<span class="text-sm text-luxe-charcoal font-medium">Detailed Logs</span>
<label class="relative inline-flex items-center cursor-pointer">
<input class="sr-only peer" type="checkbox"/>
<div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-luxe-rose"></div>
</label>
</div>
</div>
</div>
<!-- Action Buttons -->
<div class="mt-8 space-y-3 pt-6 border-t border-luxe-border" data-purpose="actions">
<button onclick="window.print()" class="w-full bg-luxe-rose text-white py-3 rounded-md font-semibold text-sm hover:bg-luxe-roseHover transition-colors shadow-lg shadow-luxe-rose/20">
          Download <?= strtoupper($format) ?>
        </button>
<button onclick="window.location.href='reportgenrate.php'" class="w-full bg-transparent border border-luxe-border text-luxe-muted py-3 rounded-md font-semibold text-sm hover:bg-stone-50 transition-colors">
          Cancel
        </button>
</div>
</aside>
<!-- END: Right Side -->
</main>
<!-- END: Export Modal Container -->
</body></html>



