<?php 
require_once '../config/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
require_once '../actions/reservation/reservation_actions.php';

// Status updates are now handled via POST to actions/reservation/update_status.php

// Pull filters from GET
$filters = [
    'search' => $_GET['search'] ?? '',
    'status' => $_GET['status'] ?? 'All Statuses',
    'start_date' => $_GET['start_date'] ?? '',
    'end_date' => $_GET['end_date'] ?? '',
    'guest_count' => $_GET['guest_count'] ?? ''
];

$reservations = get_all_reservations($pdo, $filters);
$res_stats = get_reservation_stats($pdo);
$today_arrivals = $pdo->query("SELECT COUNT(*) FROM reservations WHERE reservation_date = CURDATE() AND status = 'Confirmed'")->fetchColumn() ?: 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservations | LUXE Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght@700;900&display=swap">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
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
<header class="h-16 flex-shrink-0 bg-white dark:bg-background-dark border-b border-gray-200 dark:border-white/10 px-8 flex items-center justify-center sticky top-0 z-50">
    <div class="w-full max-w-[1400px] flex items-center justify-between">
        <div class="flex items-center gap-4 flex-1">
            <div class="relative w-full max-w-md">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">search</span>
                <input class="w-full bg-background-light dark:bg-white/5 border-none rounded-lg pl-10 pr-4 py-2 text-sm focus:ring-1 focus:ring-primary focus:border-primary transition-all" placeholder="Search reservations..." type="text"/>
            </div>
        </div>
        <div class="flex items-center gap-4">
            <button class="size-10 flex items-center justify-center rounded-lg hover:bg-gray-100 dark:hover:bg-white/5 relative translate-y-[1px]">
                <span class="material-symbols-outlined">notifications</span>
                <span class="absolute top-2 right-2 size-2 bg-primary rounded-full border-2 border-white dark:border-background-dark"></span>
            </button>
            <button class="px-5 h-10 flex items-center gap-2 bg-primary text-white rounded-lg font-bold text-xs uppercase tracking-wider hover:brightness-110 transition-all shadow-lg shadow-primary/10" onclick="toggleModal()">
                <span class="material-symbols-outlined text-sm">add</span>
                New Reservation
            </button>
        </div>
    </div>
</header>
<!-- Scrollable Page Content -->
<div class="flex-1 overflow-y-auto dark:bg-background-dark/50 bg-ivory pb-12">
    <div class="max-w-[1400px] mx-auto px-8 pt-8">
        <!-- Page Title -->
        <div class="mb-8">
            <div class="flex items-end justify-between">
                <div>
                    <?php
                    $breadcrumb = "DASHBOARD / RESERVATIONS";
                    $title = "Reservations Management";
                    include '../includes/admin_pageHeader.php';
                    ?>
                    <p class="text-sm text-gray-500 font-medium mt-1">Overview and manage all guest table bookings.</p>
                </div>
                <p class="text-[10px] text-gray-400 mb-1 uppercase tracking-[0.2em]">Last updated: Oct 12, 10:45 AM</p>
            </div>
        </div>
        <!-- Stats Row -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white dark:bg-white/5 p-6 rounded-xl border border-gray-100 dark:border-white/5 shadow-sm hover:shadow-md transition-shadow flex flex-col justify-between h-full">
                <div class="flex items-center justify-between mb-4">
                    <span class="material-symbols-outlined p-2 bg-gray-100 dark:bg-white/5 rounded-lg text-gray-600 dark:text-gray-300">book_online</span>
                    <span class="text-xs font-bold text-green-500 flex items-center">+12.5% <span class="material-symbols-outlined text-xs ml-1">trending_up</span></span>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.2em]">Total Bookings</p>
                    <h3 class="text-3xl font-bold mt-1"><?= number_format($res_stats['total']) ?></h3>
                </div>
            </div>
            <div class="bg-white dark:bg-white/5 p-6 rounded-xl border border-primary shadow-sm hover:shadow-md transition-shadow relative overflow-hidden flex flex-col justify-between h-full">
                <div class="absolute top-0 right-0 p-4 opacity-5">
                    <span class="material-symbols-outlined text-7xl text-primary">pending_actions</span>
                </div>
                <div class="flex items-center justify-between mb-4">
                    <span class="material-symbols-outlined p-2 bg-primary/10 rounded-lg text-primary">hourglass_empty</span>
                    <span class="text-[10px] font-black text-primary px-2 py-0.5 bg-primary/10 rounded-full">ACTION REQUIRED</span>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.2em]">Pending Requests</p>
                    <h3 class="text-3xl font-bold mt-1 text-primary"><?= $res_stats['pending'] ?></h3>
                </div>
            </div>
            <div class="bg-white dark:bg-white/5 p-6 rounded-xl border border-gray-100 dark:border-white/5 shadow-sm hover:shadow-md transition-shadow flex flex-col justify-between h-full">
                <div class="flex items-center justify-between mb-4">
                    <span class="material-symbols-outlined p-2 bg-gray-100 dark:bg-white/5 rounded-lg text-gray-600 dark:text-gray-300">hail</span>
                    <span class="text-xs font-bold text-blue-500 flex items-center">+5% <span class="material-symbols-outlined text-xs ml-1">trending_up</span></span>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.2em]">Today's Arrivals</p>
                    <h3 class="text-3xl font-bold mt-1"><?= $today_arrivals ?></h3>
                </div>
            </div>
        </div>
        <!-- Filter Bar -->
        <div class="bg-white dark:bg-white/5 p-3 rounded-xl border border-gray-100 dark:border-white/5 mb-6 flex flex-wrap items-center gap-4">
            <div class="flex-1 min-w-[200px]">
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">person_search</span>
                    <input class="w-full h-10 text-sm border-gray-100 dark:border-white/10 dark:bg-transparent rounded-lg pl-10 focus:ring-primary focus:border-primary" id="search-filter" placeholder="Search guest name..." type="text" value="<?= e($filters['search']) ?>"/>
                </div>
            </div>
            <select class="h-10 text-sm border-gray-100 dark:border-white/10 dark:bg-transparent rounded-lg focus:ring-primary focus:border-primary min-w-[160px]" id="status-filter">
                <option <?= $filters['status'] === 'All Statuses' ? 'selected' : '' ?>>All Statuses</option>
                <option <?= $filters['status'] === 'Confirmed' ? 'selected' : '' ?>>Confirmed</option>
                <option <?= $filters['status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                <option <?= $filters['status'] === 'Completed' ? 'selected' : '' ?>>Completed</option>
                <option <?= $filters['status'] === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
            </select>
            <div class="flex items-center h-10 gap-2 bg-background-light dark:bg-white/5 px-3 rounded-lg border border-gray-100 dark:border-white/10 cursor-pointer" id="date-range-picker">
                <span class="material-symbols-outlined text-gray-400 text-sm">calendar_today</span>
                <span class="text-xs font-semibold" id="date-display">
                    <?php if (!empty($filters['start_date']) && !empty($filters['end_date'])): ?>
                        <?= date('M d', strtotime($filters['start_date'])) ?> - <?= date('M d, Y', strtotime($filters['end_date'])) ?>
                    <?php else: ?>
                        Select Date Range
                    <?php endif; ?>
                </span>
                <span class="material-symbols-outlined text-gray-400 text-xs cursor-pointer ml-1" onclick="clearDateFilter(event)">close</span>
                <input class="hidden" id="start-date-input" type="hidden" value="<?= e($filters['start_date']) ?>"/>
                <input class="hidden" id="end-date-input" type="hidden" value="<?= e($filters['end_date']) ?>"/>
            </div>
            <button class="flex items-center gap-2 px-4 h-10 border border-gray-100 dark:border-white/10 rounded-lg text-xs font-bold uppercase tracking-wide hover:bg-gray-50 dark:hover:bg-white/5 transition-colors ml-auto" id="more-filters-btn">
                <span class="material-symbols-outlined text-sm">tune</span>
                More Filters
            </button>
        </div>
        <!-- Extra Filters Panel -->
        <div class="bg-white dark:bg-white/5 p-6 rounded-xl border border-gray-100 dark:border-white/5 mb-6 hidden" id="extra-filters-panel">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-[0.2em] mb-2">Guest Count</label>
                    <select class="w-full h-10 text-sm border-gray-100 dark:border-white/10 dark:bg-transparent rounded-lg focus:ring-primary focus:border-primary" id="guest-count-filter">
                        <option value="">Any Size</option>
                        <option value="1" <?= $filters['guest_count'] == '1' ? 'selected' : '' ?>>1 Person</option>
                        <option value="2" <?= $filters['guest_count'] == '2' ? 'selected' : '' ?>>2 People</option>
                        <option value="3" <?= $filters['guest_count'] == '3' ? 'selected' : '' ?>>3 People</option>
                        <option value="4" <?= $filters['guest_count'] == '4' ? 'selected' : '' ?>>4 People</option>
                        <option value="5+" <?= $filters['guest_count'] == '5+' ? 'selected' : '' ?>>5+ People</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button class="h-10 px-6 bg-primary text-white rounded-lg text-[10px] font-bold uppercase tracking-widest hover:brightness-110 transition-all" onclick="applyFilters()">Apply Extra Filters</button>
                    <button class="h-10 px-6 text-[10px] font-bold uppercase tracking-widest text-gray-400 hover:text-gray-600 transition-all ml-2" onclick="resetExtraFilters()">Reset</button>
                </div>
            </div>
        </div>
        <!-- Table Container -->
        <div class="bg-white dark:bg-white/5 rounded-xl border border-luxe-border shadow-md overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead class="sticky top-0 z-10">
                    <tr class="bg-luxe-beige/50 backdrop-blur-sm dark:bg-white/5 border-b border-luxe-border">
                        <th class="px-6 py-4 text-[10px] font-bold text-luxe-rose uppercase tracking-[0.2em]">ID</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-luxe-rose uppercase tracking-[0.2em]">Guest</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-luxe-rose uppercase tracking-[0.2em]">Date &amp; Time</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-luxe-rose uppercase tracking-[0.2em]">Party Size</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-luxe-rose uppercase tracking-[0.2em]">Contact</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-luxe-rose uppercase tracking-[0.2em]">Status</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-luxe-rose uppercase tracking-[0.2em] text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="reservationTableBody" class="divide-y divide-gray-100 dark:divide-white/5">
                    <?php foreach ($reservations as $res): ?>
<tr class="group hover:bg-gray-50 dark:hover:bg-white/5 transition-all reservation-row" 
    data-res-id="<?= $res['id'] ?>"
    data-guest-name="<?= e($res['guest_name']) ?>"
    data-status="<?= e($res['status']) ?>"
    data-date="<?= $res['reservation_date'] ?>"
    data-guest-count="<?= e($res['guest_count']) ?>"
    data-search="<?= e($res['guest_name']) ?> <?= e($res['phone']) ?> <?= e($res['email']) ?>">
<td class="px-6 py-5 align-middle text-sm font-mono text-gray-400">#<?= e($res['id']) ?></td>
<td class="px-6 py-5 align-middle">
<div class="flex items-center gap-3">
<div class="size-8 rounded-full bg-luxe-rose/20 flex items-center justify-center text-luxe-rose font-bold text-xs uppercase"><?= substr(e($res['guest_name']), 0, 2) ?></div>
<p class="text-sm font-bold text-luxe-charcoal"><?= e($res['guest_name']) ?></p>
</div>
</td>
<td class="px-6 py-5 align-middle">
<div class="flex flex-col">
<p class="text-sm font-bold text-luxe-charcoal"><?= date("M d, H:i", strtotime($res['reservation_date'] . ' ' . $res['reservation_time'])) ?></p>
<p class="text-[10px] font-medium text-gray-400 uppercase tracking-tight">Table Booking</p>
</div>
</td>
<td class="px-6 py-5 align-middle">
<span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-gray-100 dark:bg-white/5 rounded-md text-[10px] font-bold uppercase tracking-wide">
<span class="material-symbols-outlined text-[14px]">groups</span> <?= e($res['guest_count']) ?> People
</span>
</td>
<td class="px-6 py-5 align-middle">
<div class="flex flex-col">
<p class="text-xs font-bold"><?= e($res['phone']) ?></p>
<p class="text-[10px] text-gray-400"><?= e($res['email']) ?></p>
</div>
</td>
<td class="px-6 py-5 align-middle" id="status-cell-<?= $res['id'] ?>">
<?php
$statusClass = '';
if ($res['status'] == 'Pending') $statusClass = 'bg-luxe-rose/15 text-luxe-rose border-luxe-rose/30';
elseif ($res['status'] == 'Confirmed') $statusClass = 'bg-green-500/15 text-green-500 border-green-500/30';
elseif ($res['status'] == 'Completed') $statusClass = 'bg-gray-400/15 text-gray-400 border-gray-400/30';
elseif ($res['status'] == 'Cancelled') $statusClass = 'bg-red-500/10 text-red-500 border-red-500/20';
?>
<span id="status-badge-<?= $res['id'] ?>" class="status-badge px-3 py-1 text-[9px] font-black uppercase rounded-full tracking-widest border <?= $statusClass ?>"><?= e($res['status']) ?></span>
</td>
<td class="px-6 py-5 align-middle text-right">
<div class="flex items-center justify-end gap-2" id="actions-container-<?= $res['id'] ?>">
<?php if ($res['status'] === 'Pending'): ?>
    <!-- Confirm Action -->
    <button onclick="handleStatusAction(<?= $res['id'] ?>, 'confirm')" class="size-8 flex items-center justify-center rounded-lg bg-primary/10 text-primary hover:bg-primary hover:text-white transition-all shadow-sm status-btn" title="Confirm">
        <span class="material-symbols-outlined text-sm">check</span>
    </button>
    <!-- Reject Action -->
    <button onclick="handleStatusAction(<?= $res['id'] ?>, 'cancel')" class="size-8 flex items-center justify-center rounded-lg border border-gray-200 dark:border-white/10 hover:bg-red-50 hover:text-red-600 transition-all shadow-sm status-btn" title="Reject">
        <span class="material-symbols-outlined text-sm">close</span>
    </button>
<?php elseif ($res['status'] === 'Confirmed'): ?>
    <!-- Delete Action (No more cancel/confirm if confirmed as per display rules) -->
    <button onclick="deleteReservation(<?= $res['id'] ?>)" class="size-8 flex items-center justify-center rounded-lg border border-gray-200 dark:border-white/10 hover:bg-red-600 hover:text-white transition-all shadow-sm" title="Delete">
        <span class="material-symbols-outlined text-sm text-red-500 group-hover:text-inherit">delete</span>
    </button>
<?php else: ?>
    <!-- Completed/Cancelled: Show only Delete -->
    <button onclick="deleteReservation(<?= $res['id'] ?>)" class="size-8 flex items-center justify-center rounded-lg border border-gray-200 dark:border-white/10 hover:bg-red-600 hover:text-white transition-all shadow-sm" title="Delete">
        <span class="material-symbols-outlined text-sm text-red-500 group-hover:text-inherit">delete</span>
    </button>
<?php endif; ?>
</div>
</td>
</tr>
<?php endforeach; ?>
                </tbody>
            </table>
            <!-- Pagination -->
            <div class="px-6 py-4 bg-gray-50/50 dark:bg-white/5 border-t border-luxe-border flex items-center justify-between">
                <div class="flex items-center gap-6">
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest whitespace-nowrap">
                        Showing <span id="showingCount" class="text-luxe-dark font-bold">1-10</span> of <span id="totalCount" class="text-luxe-dark font-bold">100</span> entries
                    </p>
                    <div class="flex items-center gap-2 text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                        <span>Show</span>
                        <select id="pageSizeSelector" onchange="changePageSize(this.value)" class="bg-transparent border border-gray-200 dark:border-white/10 rounded px-2 py-0.5 outline-none focus:border-primary transition-colors cursor-pointer text-[10px] font-black">
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
        </div>
    </div>
</div>
<div class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center hidden" id="reservation-modal">
<div class="bg-white dark:bg-background-dark w-full max-w-md p-8 rounded-2xl shadow-2xl border border-gray-100 dark:border-white/10">
<div class="flex justify-between items-center mb-6">
<h3 class="text-xl font-bold">New Reservation</h3>
<button class="text-gray-400 hover:text-gray-600" onclick="toggleModal()"><span class="material-symbols-outlined">close</span></button>
</div>
<div class="space-y-4">
<div>
<label class="block text-xs font-bold text-gray-500 uppercase mb-1">Date</label>
<input class="w-full rounded-lg border-gray-200 dark:bg-white/5 dark:border-white/10 text-sm" id="res-date" onchange="checkAvailability()" type="date"/>
</div>
<div>
<label class="block text-xs font-bold text-gray-500 uppercase mb-1">Time Slot</label>
<select class="w-full rounded-lg border-gray-200 dark:bg-white/5 dark:border-white/10 text-sm" id="res-time" onchange="checkAvailability()">
<option value="">Select time</option>
<option value="18:00">18:00</option>
<option value="18:30">18:30</option>
<option value="19:00">19:00</option>
<option value="19:30">19:30</option>
<option value="20:00">20:00</option>
<option value="20:30">20:30</option>
<option value="21:00">21:00</option>
</select>
</div>
<div class="min-h-[20px]" id="availability-feedback"></div>
<button class="w-full py-3 bg-primary text-white rounded-lg font-bold shadow-lg shadow-primary/20 mt-4 transition-all" id="submit-reservation">Reserve Now</button>
</div>
</div>
</div></main>
</div>
<script>
    const MAX_TABLES = 5;
    // Mock data for existing bookings
    const mockBookings = {
        '2023-10-12T20:30': 4,
        '2023-10-21T19:00': 2,
        '2023-10-19T18:30': 5
    };

    function checkAvailability() {
        const date = document.getElementById('res-date').value;
        const time = document.getElementById('res-time').value;
        const feedback = document.getElementById('availability-feedback');
        const submitBtn = document.getElementById('submit-reservation');

        if (!date || !time) return;

        const slotKey = `${date}T${time}`;
        const booked = mockBookings[slotKey] || 0;
        const remaining = MAX_TABLES - booked;

        if (remaining <= 0) {
            feedback.innerHTML = `<span class='text-red-500 flex items-center gap-1 text-xs mt-2'><span class='material-symbols-outlined text-sm'>error</span> This time slot is fully booked. Please select another available time.</span>`;
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
        } else {
            feedback.innerHTML = `<span class='text-green-600 flex items-center gap-1 text-xs mt-2'><span class='material-symbols-outlined text-sm'>check_circle</span> Only ${remaining} tables left for ${time}</span>`;
            submitBtn.disabled = false;
            submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        }
    }

    function toggleModal() {
        const modal = document.getElementById('reservation-modal');
        modal.classList.toggle('hidden');
    }

    // Filter Logic
    const searchFilter = document.getElementById('search-filter');
    const statusFilter = document.getElementById('status-filter');
    const startDateInput = document.getElementById('start-date-input');
    const endDateInput = document.getElementById('end-date-input');
    const guestCountFilter = document.getElementById('guest-count-filter');
    const moreFiltersBtn = document.getElementById('more-filters-btn');
    const extraFiltersPanel = document.getElementById('extra-filters-panel');

    if (moreFiltersBtn) {
        moreFiltersBtn.addEventListener('click', () => {
            extraFiltersPanel.classList.toggle('hidden');
        });
    }

    function applyFilters() {
        if (!searchFilter) return;
        const query = searchFilter.value.toLowerCase();
        const status = statusFilter.value;
        const guestCount = guestCountFilter.value;
        const startDate = startDateInput.value;
        const endDate = endDateInput.value;

        const rows = Array.from(document.querySelectorAll('.reservation-row'));
        let visibleRows = [];

        rows.forEach(row => {
            const rowSearch = row.dataset.search.toLowerCase();
            const rowStatus = row.dataset.status;
            const rowDate = row.dataset.date;
            const rowGuestCount = row.dataset.guestCount;

            const matchesSearch = rowSearch.includes(query);
            const matchesStatus = status === 'All Statuses' || rowStatus === status;
            const matchesGuestCount = !guestCount || (guestCount === 'Any Size' || (guestCount === '5+' ? parseInt(rowGuestCount) >= 5 : rowGuestCount === guestCount));
            
            let matchesDate = true;
            if (startDate && endDate) {
                matchesDate = rowDate >= startDate && rowDate <= endDate;
            }

            if (matchesSearch && matchesStatus && matchesGuestCount && matchesDate) {
                visibleRows.push(row);
            } else {
                row.style.display = 'none';
            }
        });

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
                <button onclick="goToPage(${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''} class="size-8 flex items-center justify-center border border-gray-200 dark:border-white/10 rounded-lg hover:bg-gray-100 dark:hover:bg-white/10 transition-colors disabled:opacity-30">
                    <span class="material-symbols-outlined text-sm">chevron_left</span>
                </button>
            `;
        }

        for (let i = 1; i <= totalPages; i++) {
            html += `
                <button onclick="goToPage(${i})" class="size-8 flex items-center justify-center ${currentPage === i ? 'bg-primary text-white shadow-sm shadow-primary/20' : 'hover:bg-gray-100 dark:hover:bg-white/10 text-xs font-bold transition-colors'} rounded-lg">
                    ${i}
                </button>
            `;
        }

        if (totalPages > 1) {
            html += `
                <button onclick="goToPage(${currentPage + 1})" ${currentPage === totalPages ? 'disabled' : ''} class="size-8 flex items-center justify-center border border-gray-200 dark:border-white/10 rounded-lg hover:bg-gray-100 dark:hover:bg-white/10 transition-colors disabled:opacity-30">
                    <span class="material-symbols-outlined text-sm">chevron_right</span>
                </button>
            `;
        }

        container.innerHTML = html;
        window.currentVisibleRows = visibleRows;
    }

    window.goToPage = function(page) {
        currentPage = page;
        handlePagination(window.currentVisibleRows);
        const tableTop = document.querySelector('.overflow-hidden');
        if (tableTop) tableTop.scrollIntoView({ behavior: 'smooth', block: 'start' });
    };

    window.changePageSize = function(size) {
        rowsPerPage = parseInt(size);
        currentPage = 1;
        applyFilters();
    };

    function resetExtraFilters() {
        guestCountFilter.value = '';
        applyFilters();
    }

    if (searchFilter) searchFilter.addEventListener('input', applyFilters);
    if (statusFilter) statusFilter.addEventListener('change', applyFilters);

    flatpickr("#date-range-picker", {
        mode: "range",
        dateFormat: "Y-m-d",
        defaultDate: [startDateInput.value, endDateInput.value],
        onClose: function(selectedDates, dateStr, instance) {
            if (selectedDates.length === 2) {
                startDateInput.value = instance.formatDate(selectedDates[0], "Y-m-d");
                endDateInput.value = instance.formatDate(selectedDates[1], "Y-m-d");
                applyFilters();
            }
        }
    });

    function clearDateFilter(e) {
        e.stopPropagation();
        startDateInput.value = '';
        endDateInput.value = '';
        applyFilters();
    }

    // Initial call
    document.addEventListener('DOMContentLoaded', applyFilters);

    async function handleStatusAction(reservationId, action) {
        console.log("Updating status:", reservationId, action);
        
        const container = document.getElementById(`actions-container-${reservationId}`);
        const badge = document.getElementById(`status-badge-${reservationId}`);
        const buttons = container.querySelectorAll('.status-btn');

        // Disable buttons
        buttons.forEach(btn => {
            btn.disabled = true;
            btn.style.opacity = '0.5';
        });

        try {
            const formData = new FormData();
            formData.append('reservation_id', reservationId);
            formData.append('action', action);

            const response = await fetch('actions/update_reservation_status.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();
            console.log("Response:", result);

            if (result.success) {
                // Update badge text and style
                badge.innerText = result.display_status.toUpperCase();
                
                // Remove old status classes
                badge.classList.remove('bg-rose-accent/15', 'text-rose-accent', 'border-rose-accent/30');
                
                if (result.new_status === 'confirmed') {
                    badge.classList.add('bg-green-500/15', 'text-green-500', 'border-green-500/30');
                    // Show success message if confirm
                    const msg = document.createElement('div');
                    msg.className = 'fixed bottom-5 right-5 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-[100] animate-bounce';
                    msg.innerText = "Reservation Confirmed Successfully";
                    document.body.appendChild(msg);
                    setTimeout(() => msg.remove(), 3000);
                } else if (result.new_status === 'cancelled') {
                    badge.classList.add('bg-red-500/10', 'text-red-500', 'border-red-500/20');
                }

                // Remove action buttons as per display rules
                // Show only Delete button if it should be there (standard for non-pending)
                container.innerHTML = `
                    <button onclick="deleteReservation(${reservationId})" class="size-8 flex items-center justify-center rounded-lg border border-gray-200 dark:border-white/10 hover:bg-red-600 hover:text-white transition-all shadow-sm" title="Delete">
                        <span class="material-symbols-outlined text-sm text-red-500 group-hover:text-inherit">delete</span>
                    </button>
                `;

            } else {
                alert('Error: ' + (result.message || 'Failed to update status'));
                // Re-enable buttons if failed
                buttons.forEach(btn => {
                    btn.disabled = false;
                    btn.style.opacity = '1';
                });
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An unexpected error occurred.');
            // Re-enable buttons
            buttons.forEach(btn => {
                btn.disabled = false;
                btn.style.opacity = '1';
            });
        }
    }

    async function updateStatus(id, status) {
        // Legacy function - kept for compatibility if needed elsewhere, 
        // but main logic moved to handleStatusAction
        handleStatusAction(id, status.toLowerCase() === 'confirmed' ? 'confirm' : 'cancel');
    }

    async function deleteReservation(id) {
        if (!confirm('Are you sure you want to PERMANENTLY delete this reservation? This action cannot be undone.')) return;
        
        try {
            const formData = new FormData();
            formData.append('id', id);

            const response = await fetch('../actions/reservation/delete_reservation.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();
            if (result.success) {
                location.reload();
            } else {
                alert('Error: ' + (result.message || 'Failed to delete reservation'));
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An unexpected error occurred.');
        }
    }
</script></body></html>




