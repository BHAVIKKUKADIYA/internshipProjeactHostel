<?php 
require_once '../config/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
require_once '../actions/feedback/feedback_actions.php';

// Handle Moderation Actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $action = $_GET['action'];
    
    if ($action === 'approve') {
        moderate_feedback($pdo, $id, 'Approved');
    } elseif ($action === 'reject') {
        moderate_feedback($pdo, $id, 'Rejected');
    } elseif ($action === 'delete') {
        // We might need a delete_feedback function in feedback_actions.php
        // For now let's assume it exists or use moderate to 'Deleted'
        // Actually I'll add delete_feedback to feedback_actions.php later if needed
        // For now use a direct PDO call or moderate
        $stmt = $pdo->prepare("DELETE FROM feedback WHERE id = ?");
        $stmt->execute([$id]);
    }
    redirect('feedback.php');
}

$feedbacks = get_all_feedback($pdo);
$stats = get_feedback_stats($pdo);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback Management | LUXE Admin</title>
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
<!-- BEGIN: MainHeader -->
<!-- Top Bar -->
<header class="flex flex-col md:flex-row justify-between items-start mb-10 gap-6">
    <div>
        <?php 
        $breadcrumb = "DASHBOARD / REVIEWS";
        $title = "Customer Feedback Management"; 
        include '../includes/admin_pageHeader.php'; 
        ?>
        <p class="text-gray-500 mt-1">Review and manage guest feedback, ratings, and testimonials.</p>
    </div>
</header>
<!-- END: MainHeader -->
<main class="max-w-[1400px] mx-auto px-8 py-10 space-y-10">
<!-- BEGIN: SummarySection -->
<section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6" data-purpose="stats-overview">
<!-- Total Feedback -->
<div class="bg-white p-6 rounded-luxe border border-luxe-border hover-card transition-custom shadow-sm">
<div class="flex justify-between items-start mb-4">
<span class="text-luxe-grey-text text-sm font-medium uppercase tracking-wider">Total Feedback</span>
<div class="p-2 bg-luxe-rose/10 rounded-lg text-luxe-rose">
<svg class="h-5 w-5" fill="currentColor" viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2z"></path></svg>
</div>
</div>
<div class="text-3xl font-serif text-luxe-charcoal"><?= number_format($stats['total']) ?></div>
<p class="text-green-500 text-xs mt-2">Latest guest insights</p>
</div>
<!-- 5-Star Reviews -->
<div class="bg-white p-6 rounded-luxe border border-luxe-border hover-card transition-custom shadow-sm">
<div class="flex justify-between items-start mb-4">
<span class="text-luxe-grey-text text-sm font-medium uppercase tracking-wider">5-Star Reviews</span>
<div class="p-2 bg-luxe-rose/10 rounded-lg text-luxe-rose">
<svg class="h-5 w-5" fill="currentColor" viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"></path></svg>
</div>
</div>
<div class="text-3xl font-serif text-luxe-charcoal"><?= number_format($stats['five_star']) ?></div>
<p class="text-green-500 text-xs mt-2"><?= $stats['total'] > 0 ? round(($stats['five_star'] / $stats['total']) * 100) : 0 ?>% of total volume</p>
</div>
<!-- Pending Reviews -->
<div class="bg-white p-6 rounded-luxe border border-luxe-border hover-card transition-custom shadow-sm">
<div class="flex justify-between items-start mb-4">
<span class="text-luxe-grey-text text-sm font-medium uppercase tracking-wider">Pending Reviews</span>
<div class="p-2 bg-luxe-rose/10 rounded-lg text-luxe-rose">
<svg class="h-5 w-5" fill="currentColor" viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8z"></path><path d="M12.5 7H11v6l5.25 3.15.75-1.23-4.5-2.67z"></path></svg>
</div>
</div>
<div class="text-3xl font-serif text-luxe-charcoal"><?= number_format($stats['pending']) ?></div>
<p class="text-amber-500 text-xs mt-2"><?= $stats['pending'] > 0 ? 'Requires immediate action' : 'All caught up' ?></p>
</div>
<!-- Average Rating -->
<div class="bg-white p-6 rounded-luxe border border-luxe-border hover-card transition-custom shadow-sm">
<div class="flex justify-between items-start mb-4">
<span class="text-luxe-grey-text text-sm font-medium uppercase tracking-wider">Average Rating</span>
<div class="p-2 bg-luxe-rose/10 rounded-lg text-luxe-rose">
<svg class="h-5 w-5" fill="currentColor" viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M13.5 2c-5.62 0-10.19 4.48-10.47 10h2.01c.28-4.39 3.92-7.85 8.39-7.97l-.01 2.02L17.5 3.5 13.5 1v1zm1.5 10c0-1.66-1.34-3-3-3s-3 1.34-3 3 1.34 3 3 3 3-1.34 3-3zm-9 0c0 5.52 4.48 10 10 10 3.31 0 6.24-1.61 8.06-4.09l-1.47-1.35c-1.4 1.91-3.66 3.16-6.21 3.42V18h-2v2.01c-3.92-.12-7.14-3.13-7.85-6.91l-1.95-.14c.28 5.52 4.85 10 10.47 10h1v-2l4 2.5-4 2.5v-2h-1c-6.63 0-12-5.37-12-12s5.37-12 12-12h1v2l4-2.5-4-2.5v2h-1c-5.18 0-9.44 3.93-9.96 9z"></path></svg>
</div>
</div>
<div class="text-3xl font-serif text-luxe-charcoal"><?= number_format($stats['average'], 1) ?> <span class="text-sm text-gray-500 font-sans">/ 5.0</span></div>
<div class="flex gap-0.5 mt-2">
    <?php 
    $avg = round($stats['average']);
    for ($i = 1; $i <= 5; $i++): 
    ?>
    <svg class="w-3 h-3 <?= $i <= $avg ? 'text-[#d4af37]' : 'text-gray-300' ?> fill-current" viewbox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"></path></svg>
    <?php endfor; ?>
</div>
</div>
</section>
<!-- END: SummarySection -->
<!-- BEGIN: FilterBar -->
<section class="flex flex-col md:flex-row items-center gap-6" data-purpose="filtering-controls">
<div class="flex items-center gap-4 w-full md:w-auto">
<label class="text-sm font-medium text-gray-400 whitespace-nowrap">Filter by Rating:</label>
<div class="relative w-full md:w-[160px] custom-dropdown">
  <button type="button" class="w-full bg-white border border-luxe-border text-luxe-charcoal rounded-luxe py-2 px-4 focus:border-luxe-rose focus:ring-1 focus:ring-luxe-rose transition-custom hover:border-luxe-rose/50 flex justify-between items-center h-[42px] dropdown-btn">
    <span class="truncate text-sm dropdown-value font-medium">All Ratings</span>
    <svg class="w-4 h-4 text-gray-400 transition-transform duration-200 dropdown-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
  </button>
  <div class="absolute z-50 top-[calc(100%+4px)] left-0 w-full bg-white border border-luxe-border rounded-luxe shadow-lg opacity-0 invisible transition-all duration-200 transform translate-y-[-5px] dropdown-menu overflow-hidden">
    <div class="py-1">
      <div class="px-4 py-2.5 text-sm text-luxe-charcoal hover:bg-luxe-rose/10 hover:text-luxe-rose cursor-pointer transition-custom dropdown-item font-medium">All Ratings</div>
      <div class="px-4 py-2.5 text-sm text-luxe-charcoal hover:bg-luxe-rose/10 hover:text-luxe-rose cursor-pointer transition-custom dropdown-item font-medium">5 Stars</div>
      <div class="px-4 py-2.5 text-sm text-luxe-charcoal hover:bg-luxe-rose/10 hover:text-luxe-rose cursor-pointer transition-custom dropdown-item font-medium">4 Stars</div>
      <div class="px-4 py-2.5 text-sm text-luxe-charcoal hover:bg-luxe-rose/10 hover:text-luxe-rose cursor-pointer transition-custom dropdown-item font-medium">3 Stars</div>
      <div class="px-4 py-2.5 text-sm text-luxe-charcoal hover:bg-luxe-rose/10 hover:text-luxe-rose cursor-pointer transition-custom dropdown-item font-medium">2 Stars</div>
      <div class="px-4 py-2.5 text-sm text-luxe-charcoal hover:bg-luxe-rose/10 hover:text-luxe-rose cursor-pointer transition-custom dropdown-item font-medium">1 Star</div>
    </div>
  </div>
</div>
</div>
<div class="flex items-center gap-4 w-full md:w-auto">
<label class="text-sm font-medium text-gray-400 whitespace-nowrap">Date Period:</label>
<div class="relative w-full md:w-[160px] custom-dropdown">
  <button type="button" class="w-full bg-white border border-luxe-border text-luxe-charcoal rounded-luxe py-2 px-4 focus:border-luxe-rose focus:ring-1 focus:ring-luxe-rose transition-custom hover:border-luxe-rose/50 flex justify-between items-center h-[42px] dropdown-btn">
    <span class="truncate text-sm dropdown-value font-medium">Last 30 Days</span>
    <svg class="w-4 h-4 text-gray-400 transition-transform duration-200 dropdown-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
  </button>
  <div class="absolute z-50 top-[calc(100%+4px)] left-0 w-full bg-white border border-luxe-border rounded-luxe shadow-lg opacity-0 invisible transition-all duration-200 transform translate-y-[-5px] dropdown-menu overflow-hidden">
    <div class="py-1">
      <div class="px-4 py-2.5 text-sm text-luxe-charcoal hover:bg-luxe-rose/10 hover:text-luxe-rose cursor-pointer transition-custom dropdown-item font-medium">Last 30 Days</div>
      <div class="px-4 py-2.5 text-sm text-luxe-charcoal hover:bg-luxe-rose/10 hover:text-luxe-rose cursor-pointer transition-custom dropdown-item font-medium">Last 3 Months</div>
      <div class="px-4 py-2.5 text-sm text-luxe-charcoal hover:bg-luxe-rose/10 hover:text-luxe-rose cursor-pointer transition-custom dropdown-item font-medium">Last 6 Months</div>
      <div class="px-4 py-2.5 text-sm text-luxe-charcoal hover:bg-luxe-rose/10 hover:text-luxe-rose cursor-pointer transition-custom dropdown-item font-medium">All Time</div>
    </div>
  </div>
</div>
</div>
<div class="relative w-full md:w-96 md:ml-auto" data-purpose="filter-search">
  <input id="header-search-input" class="w-full bg-white border border-luxe-border text-sm text-luxe-charcoal rounded-luxe pl-11 pr-4 py-2.5 focus:border-luxe-rose focus:ring-1 focus:ring-luxe-rose/30 transition-custom outline-none placeholder:text-gray-400 h-[42px]" placeholder="Search reviews..." type="text"/>
  <div class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
      <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path>
    </svg>
  </div>
</div>
</section>
<!-- END: FilterBar -->
<!-- BEGIN: FeedbackTable -->
<section class="bg-white border border-luxe-border rounded-luxe overflow-hidden shadow-sm" data-purpose="table-container">
<div class="overflow-x-auto">
<table class="w-full text-left border-collapse">
<thead>
<tr class="bg-luxe-beige/50 border-b border-luxe-border">
<th class="px-6 py-4 text-xs uppercase tracking-widest text-luxe-rose font-semibold">Customer Details</th>
<th class="px-6 py-4 text-xs uppercase tracking-widest text-luxe-rose font-semibold">Rating</th>
<th class="px-6 py-4 text-xs uppercase tracking-widest text-luxe-rose font-semibold">Review Content</th>
<th class="px-6 py-4 text-xs uppercase tracking-widest text-luxe-rose font-semibold">Status</th>
<th class="px-6 py-4 text-xs uppercase tracking-widest text-luxe-rose font-semibold">Actions</th>
</tr>
</thead>
<tbody class="divide-y divide-luxe-border/50">
<?php foreach ($feedbacks as $fb): ?>
<?php 
$initials = '';
if (!empty($fb['customer_name'])) {
    $parts = explode(' ', $fb['customer_name']);
    $initials = strtoupper(substr($parts[0], 0, 1) . (count($parts) > 1 ? substr($parts[count($parts)-1], 0, 1) : ''));
}
?>
<tr class="table-row-hover transition-custom" data-id="<?= e($fb['id']) ?>" data-name="<?= e($fb['customer_name']) ?>" data-email="<?= e($fb['email']) ?>" data-rating="<?= e($fb['rating']) ?>" data-date="<?= date("M d, Y", strtotime($fb['created_at'])) ?>" data-review="<?= e($fb['comment']) ?>" data-visit="<?= date("M d, Y", strtotime($fb['created_at'])) ?>" data-table="N/A" data-staff="N/A" data-status="<?= e($fb['status']) ?>" data-initials="<?= e($initials) ?>">
<td class="px-6 py-5">
<div class="flex items-center gap-3">
<div class="w-10 h-10 rounded-full bg-luxe-beige flex items-center justify-center text-luxe-rose font-serif text-lg border border-luxe-border"><?= e($initials) ?></div>
<div>
<div class="text-sm font-medium text-luxe-charcoal"><?= e($fb['customer_name']) ?></div>
<div class="text-xs text-gray-500"><?= e($fb['email']) ?></div>
</div>
</div>
</td>
<td class="px-6 py-5">
<div class="flex gap-0.5">
<?php for ($i = 1; $i <= 5; $i++): ?>
<svg class="w-4 h-4 <?= $i <= $fb['rating'] ? 'text-luxe-rose' : 'text-gray-300' ?> fill-current" viewbox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"></path></svg>
<?php endfor; ?>
</div>
<div class="text-[10px] text-gray-500 mt-1 uppercase tracking-tighter"><?= date("M d, Y", strtotime($fb['created_at'])) ?></div>
</td>
<td class="px-6 py-5">
<p class="text-sm text-luxe-charcoal/80 max-w-xs truncate"><?= e($fb['comment']) ?></p>
</td>
<td class="px-6 py-5">
<?php
$statusClass = '';
if ($fb['status'] == 'Approved') $statusClass = 'bg-green-500/10 text-green-500 border-green-500/20 bg-emerald-50 text-emerald-600 border-emerald-100';
elseif ($fb['status'] == 'Pending') $statusClass = 'bg-amber-500/10 text-amber-500 border-amber-500/20 bg-amber-50 text-amber-600 border-amber-100';
elseif ($fb['status'] == 'Rejected') $statusClass = 'bg-red-500/10 text-red-500 border-red-500/20 bg-rose-50 text-rose-600 border-rose-100';
?>
<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $statusClass ?>"><?= e($fb['status']) ?></span>
</td>
<td class="px-6 py-5 text-right">
<div class="flex items-center justify-end gap-2">
<?php if ($fb['status'] == 'Pending'): ?>
<a href="?action=approve&id=<?= $fb['id'] ?>" class="p-2 hover:bg-green-500/10 rounded-lg text-gray-400 hover:text-green-500 transition-custom" title="Approve">
<svg class="h-4 w-4" fill="none" stroke="currentColor" viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
</a>
<a href="?action=reject&id=<?= $fb['id'] ?>" class="p-2 hover:bg-orange-500/10 rounded-lg text-gray-400 hover:text-orange-500 transition-custom" title="Reject">
<svg class="h-4 w-4" fill="none" stroke="currentColor" viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M6 18L18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
</a>
<?php endif; ?>
<button class="p-2 hover:bg-luxe-border rounded-lg text-gray-400 hover:text-white transition-custom" title="View Details">
<svg class="h-4 w-4" fill="none" stroke="currentColor" viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
</button>
<a href="?action=delete&id=<?= $fb['id'] ?>" onclick="return confirm('Are you sure you want to delete this review?')" class="p-2 hover:bg-red-500/10 rounded-lg text-gray-400 hover:text-red-500 transition-custom" title="Delete">
<svg class="h-4 w-4" fill="none" stroke="currentColor" viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
</a>
</div>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
<!-- Pagination -->
<div class="bg-white px-6 py-4 border-t border-luxe-border flex items-center justify-between">
<span class="text-xs text-gray-500">Showing 1 to 4 of 1,284 entries</span>
<div class="flex items-center gap-2">
<button class="p-2 text-gray-500 hover:text-luxe-gold transition-custom disabled:opacity-30" disabled="">
<svg class="h-5 w-5" fill="currentColor" viewbox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path clip-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" fill-rule="evenodd"></path></svg>
</button>
<div class="flex gap-1">
<button class="w-8 h-8 rounded-lg bg-luxe-rose text-white text-xs font-bold shadow-luxe-rose/20">1</button>
<button class="w-8 h-8 rounded-lg text-gray-400 hover:bg-luxe-border text-xs transition-custom">2</button>
<button class="w-8 h-8 rounded-lg text-gray-400 hover:bg-luxe-border text-xs transition-custom">3</button>
<span class="text-gray-600 px-1">...</span>
<button class="w-8 h-8 rounded-lg text-gray-400 hover:bg-luxe-border text-xs transition-custom">32</button>
</div>
<button class="p-2 text-gray-500 hover:text-luxe-gold transition-custom">
<svg class="h-5 w-5" fill="currentColor" viewbox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path clip-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" fill-rule="evenodd"></path></svg>
</button>
</div>
</div>
</section>
<!-- END: FeedbackTable -->
</main>
<!-- BEGIN: PageFooter -->
<footer class="max-w-[1400px] mx-auto px-8 py-10 border-t border-luxe-border flex flex-col md:flex-row justify-between items-center gap-4 text-xs text-luxe-grey-text">
<div>© 2023 LUXE Restaurant Management. All Rights Reserved.</div>
<div class="flex gap-6 uppercase tracking-widest">
<a class="hover:text-luxe-gold transition-custom hover:text-luxe-rose" href="#">Privacy Policy</a>
<a class="hover:text-luxe-gold transition-custom hover:text-luxe-rose" href="#">Terms of Service</a>
<a class="hover:text-luxe-gold transition-custom hover:text-luxe-rose" href="#">System Status</a>
</div>
</footer>
<!-- END: PageFooter -->

<!-- BEGIN: Feedback Details Modal Container -->
<div id="feedbackModal" class="fixed inset-0 bg-black/20 backdrop-blur-sm flex items-center justify-center z-50 p-4 hidden transistion-opacity" data-purpose="modal-overlay">
<!-- BEGIN: Modal Card -->
<article class="bg-white w-full max-w-2xl rounded-2xl shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-300" data-purpose="feedback-modal-card" style="font-family: 'Inter', sans-serif;">
<!-- BEGIN: Modal Header -->
<header class="px-8 pt-8 pb-4 flex justify-between items-start">
<div class="space-y-1">
<p class="text-[10px] uppercase tracking-widest text-[#8A8A8A] font-semibold">Review Details</p>
<h1 class="text-3xl font-serif text-[#4A4A4A]" style="font-family: 'Georgia', serif;">Guest Feedback</h1>
</div>
<!-- Close Button -->
<button id="modalCloseBtn" class="text-[#8A8A8A] hover:text-[#4A4A4A] transition-colors p-2">
<svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
<path d="M6 18L18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path>
</svg>
</button>
</header>
<!-- END: Modal Header -->
<!-- BEGIN: Modal Content -->
<main class="px-8 py-6 space-y-8">
<!-- Section: Customer Profile & Rating -->
<section class="flex flex-col md:flex-row md:items-center justify-between gap-6 border-b border-luxe-border pb-8">
<div class="flex items-center gap-4">
<!-- Avatar Circle -->
<div id="modalInitials" class="h-16 w-16 rounded-full bg-[#F9F1F0] flex items-center justify-center text-[#C38280] font-serif text-xl border border-[#C38280]/10" style="font-family: 'Georgia', serif;">MW</div>
<div>
<h2 id="modalGuestName" class="text-xl font-semibold text-[#4A4A4A]">Marcus Wright</h2>
<p id="modalEmail" class="text-sm text-[#8A8A8A]">m.wright@luxury.co</p>
</div>
</div>
<div class="flex flex-col md:items-end gap-1">
<div id="modalRating" class="flex gap-1 text-[#C38280]">
</div>
<p id="modalReviewDate" class="text-xs font-medium text-[#8A8A8A] uppercase tracking-wider">OCT 25, 2023</p>
</div>
</section>
<!-- Section: Feedback Message -->
<section class="space-y-4">
<h3 class="text-xs font-bold uppercase tracking-widest text-[#8A8A8A]">Review Content</h3>
<p id="modalReviewContent" class="text-[#4A4A4A] leading-relaxed text-lg italic font-serif" style="font-family: 'Georgia', serif;">
"Wait time was longer than expected despite a reservation, but the Truffle Risotto was an absolute masterpiece. The ambiance in the evening is unmatched. I would suggest slightly more attentive service near the bar area."
</p>
</section>
<!-- Section: Metadata Grid -->
<section class="grid grid-cols-2 md:grid-cols-4 gap-6 pt-4">
<div data-purpose="meta-item">
<p class="text-[10px] font-bold uppercase tracking-widest text-[#8A8A8A] mb-1">Visit Date</p>
<p id="modalVisitDate" class="text-sm font-semibold text-[#4A4A4A]">Oct 24, 2023</p>
</div>
<div data-purpose="meta-item">
<p class="text-[10px] font-bold uppercase tracking-widest text-[#8A8A8A] mb-1">Table No.</p>
<p id="modalTableNo" class="text-sm font-semibold text-[#4A4A4A]">14 (Window Side)</p>
</div>
<div data-purpose="meta-item">
<p class="text-[10px] font-bold uppercase tracking-widest text-[#8A8A8A] mb-1">Service Staff</p>
<p id="modalServiceStaff" class="text-sm font-semibold text-[#4A4A4A]">Julian S.</p>
</div>
<div data-purpose="meta-item">
<p class="text-[10px] font-bold uppercase tracking-widest text-[#8A8A8A] mb-1">Status</p>
<span id="modalStatus" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">Pending</span>
</div>
</section>
</main>
<!-- END: Modal Content -->
<!-- BEGIN: Modal Footer / Actions -->
<footer class="px-8 py-8 bg-[#F4EFEC]/50 border-t border-luxe-border flex flex-col sm:flex-row justify-end items-center gap-4">
<button id="modalRejectBtn" class="w-full sm:w-auto px-8 py-3 rounded-lg border-2 border-[#C38280] text-[#C38280] font-semibold hover:bg-[#C38280] hover:text-white transition-all duration-300 active:scale-95">
Reject Review
</button>
<button id="modalApproveBtn" class="w-full sm:w-auto px-8 py-3 rounded-lg bg-[#C38280] text-white font-semibold hover:bg-[#b07270] shadow-lg shadow-[#C38280]/20 transition-all duration-300 active:scale-95">
Approve Review
</button>
</footer>
<!-- END: Modal Footer / Actions -->
</article>
<!-- END: Modal Card -->
</div>
<!-- END: Feedback Details Modal Container -->

<script>
document.addEventListener('DOMContentLoaded', function() {
    const closeModalBtn = document.getElementById('modalCloseBtn');
    if (closeModalBtn) {
        closeModalBtn.addEventListener('click', () => {
            document.getElementById('feedbackModal').classList.add('hidden');
        });
    }

    const modalRejectBtn = document.getElementById('modalRejectBtn');
    if (modalRejectBtn) {
        modalRejectBtn.addEventListener('click', () => {
            const el = document.getElementById('modalStatus');
            el.textContent = 'Rejected';
            el.className = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-rose-50 text-rose-600 border border-rose-100';
            setTimeout(() => document.getElementById('feedbackModal').classList.add('hidden'), 500);
        });
    }

    const modalApproveBtn = document.getElementById('modalApproveBtn');
    if (modalApproveBtn) {
        modalApproveBtn.addEventListener('click', () => {
            const el = document.getElementById('modalStatus');
            el.textContent = 'Approved';
            el.className = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-600 border border-emerald-100';
            setTimeout(() => document.getElementById('feedbackModal').classList.add('hidden'), 500);
        });
    }
    const tableBody = document.querySelector('tbody');
    if (!tableBody) return;

    tableBody.addEventListener('click', function(e) {
        const btn = e.target.closest('button');
        if (!btn) return;
        
        const row = btn.closest('tr');
        if (!row) return;

        const title = btn.getAttribute('title');
        
        if (title === 'Approve') {
            if (confirm("Are you sure you want to approve this review?")) {
                updateStatus(row, 'Approved');
                hideActionButtons(row);
            }
        } else if (title === 'Reject') {
            if (confirm("Are you sure you want to reject this review?")) {
                updateStatus(row, 'Rejected');
                hideActionButtons(row);
            }
        } else if (title === 'View' || title === 'View Details') {
            document.getElementById('modalGuestName').textContent = row.dataset.name;
            document.getElementById('modalEmail').textContent = row.dataset.email;
            document.getElementById('modalInitials').textContent = row.dataset.initials;
            document.getElementById('modalReviewDate').textContent = row.dataset.date;
            document.getElementById('modalReviewContent').textContent = '"' + row.dataset.review + '"';
            document.getElementById('modalVisitDate').textContent = row.dataset.visit;
            document.getElementById('modalTableNo').textContent = row.dataset.table;
            document.getElementById('modalServiceStaff').textContent = row.dataset.staff;
            
            const ratingContainer = document.getElementById('modalRating');
            const ratingVal = parseInt(row.dataset.rating) || 5;
            ratingContainer.innerHTML = '';
            for(let i=1; i<=5; i++) {
                if(i <= ratingVal) {
                    ratingContainer.innerHTML += `<svg class="w-5 h-5 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>`;
                } else {
                    ratingContainer.innerHTML += `<svg class="w-5 h-5 text-[#D1D5DB] fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>`;
                }
            }

            const statusEl = document.getElementById('modalStatus');
            const statusTxt = row.dataset.status;
            statusEl.textContent = statusTxt;
            if (statusTxt.toLowerCase() === 'approved') {
                statusEl.className = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-600 border border-emerald-100';
            } else if (statusTxt.toLowerCase() === 'rejected') {
                statusEl.className = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-rose-50 text-rose-600 border border-rose-100';
            } else {
                statusEl.className = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 border border-amber-200';
            }
            
            document.getElementById('feedbackModal').classList.remove('hidden');
        } else if (title === 'Delete') {
            if (confirm("Are you sure you want to delete this review?")) {
                row.style.transition = 'opacity 0.3s ease';
                row.style.opacity = '0';
                setTimeout(() => row.remove(), 300);
            }
        }
    });

    function updateStatus(row, statusStr) {
        const statusCell = row.querySelector('td:nth-child(4)');
        if (!statusCell) return;
        
        const badge = statusCell.querySelector('span');
        if (!badge) return;
        
        if (statusStr === 'Approved') {
            badge.className = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-500/10 text-green-500 border border-green-500/20 bg-emerald-50 text-emerald-600 border-emerald-100';
            badge.innerText = 'Approved';
        } else if (statusStr === 'Rejected') {
            badge.className = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-500/10 text-red-500 border border-red-500/20 bg-rose-50 text-rose-600 border-rose-100';
            badge.innerText = 'Rejected';
        }
    }

    function hideActionButtons(row) {
        const approveBtn = row.querySelector('button[title="Approve"]');
        const rejectBtn = row.querySelector('button[title="Reject"]');
        if (approveBtn) approveBtn.style.display = 'none';
        if (rejectBtn) rejectBtn.style.display = 'none';
    }

    // Custom Dropdown Logic
    const dropdowns = document.querySelectorAll('.custom-dropdown');
    
    dropdowns.forEach(dropdown => {
        const btn = dropdown.querySelector('.dropdown-btn');
        const menu = dropdown.querySelector('.dropdown-menu');
        const valueSpan = dropdown.querySelector('.dropdown-value');
        const icon = dropdown.querySelector('.dropdown-icon');
        const items = dropdown.querySelectorAll('.dropdown-item');
        
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            const isOpen = menu.classList.contains('opacity-100');
            
            // Close all other dropdowns
            document.querySelectorAll('.custom-dropdown .dropdown-menu').forEach(m => {
                m.classList.remove('opacity-100', 'visible', 'translate-y-0');
                m.classList.add('opacity-0', 'invisible', 'translate-y-[-5px]');
            });
            document.querySelectorAll('.custom-dropdown .dropdown-icon').forEach(i => {
                i.style.transform = 'rotate(0deg)';
            });

            if (!isOpen) {
                menu.classList.remove('opacity-0', 'invisible', 'translate-y-[-5px]');
                menu.classList.add('opacity-100', 'visible', 'translate-y-0');
                icon.style.transform = 'rotate(180deg)';
                btn.focus();
            }
        });
        
        items.forEach(item => {
            item.addEventListener('click', (e) => {
                e.stopPropagation();
                const selectedValue = item.textContent;
                valueSpan.textContent = selectedValue;
                
                menu.classList.remove('opacity-100', 'visible', 'translate-y-0');
                menu.classList.add('opacity-0', 'invisible', 'translate-y-[-5px]');
                icon.style.transform = 'rotate(0deg)';
                
                // Trigger filtering if it's the Rating dropdown
                if (dropdown.querySelector('label')?.textContent.includes('Rating') || dropdown.previousElementSibling?.textContent.includes('Rating')) {
                    filterByRating(selectedValue);
                }
            });
        });
    });

    function filterByRating(ratingText) {
        applyAllFilters();
    }

    const searchInput = document.getElementById('header-search-input');
    if (searchInput) {
        searchInput.addEventListener('input', applyAllFilters);
    }

    function applyAllFilters() {
        const rows = document.querySelectorAll('tbody tr');
        const countSpan = document.querySelector('[data-purpose="table-container"] .text-xs.text-gray-500');
        const searchInput = document.getElementById('header-search-input');
        const ratingBtn = document.querySelector('.custom-dropdown .dropdown-value');
        
        const searchQuery = searchInput ? searchInput.value.toLowerCase().trim() : '';
        const ratingText = ratingBtn ? ratingBtn.textContent : 'All Ratings';
        
        let visibleCount = 0;

        rows.forEach(row => {
            const name = (row.dataset.name || '').toLowerCase();
            const comment = (row.dataset.review || '').toLowerCase();
            const email = (row.dataset.email || '').toLowerCase();
            const rating = parseInt(row.dataset.rating);
            
            const matchesSearch = name.includes(searchQuery) || comment.includes(searchQuery) || email.includes(searchQuery);
            let matchesRating = false;

            if (ratingText === 'All Ratings') {
                matchesRating = true;
            } else {
                const targetRating = parseInt(ratingText.split(' ')[0]);
                matchesRating = (rating === targetRating);
            }

            const shouldShow = matchesSearch && matchesRating;
            row.style.display = shouldShow ? '' : 'none';
            if (shouldShow) visibleCount++;
        });

        if (countSpan) {
            countSpan.textContent = `Showing ${visibleCount} entries`;
        }
    }
    
    // Close dropdowns on outside click
    document.addEventListener('click', () => {
        document.querySelectorAll('.custom-dropdown .dropdown-menu').forEach(menu => {
            menu.classList.remove('opacity-100', 'visible', 'translate-y-0');
            menu.classList.add('opacity-0', 'invisible', 'translate-y-[-5px]');
        });
        document.querySelectorAll('.custom-dropdown .dropdown-icon').forEach(icon => {
            icon.style.transform = 'rotate(0deg)';
        });
    });

});
</script>
</body></html>
