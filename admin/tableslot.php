<?php 
require_once '../config/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
require_once '../actions/reservation/reservation_actions.php';

// Initial data
$today = date('Y-m-d');
$active_slots = get_active_slots($pdo);

// Stats for today
// Stats for today based on table_slots
try {
    // Sync first to ensure initial load is accurate
    sync_all_slot_counts($pdo, $today);
    
    // Refresh slot data after sync
    $active_slots = get_active_slots($pdo, true);
    
    // 1. Total Restaurant Capacity (Sum of all active slot capacities)
    // IMPORTANT: Fix calculation to sum capacity from table_slots
    $total_tables = $pdo->query("SELECT SUM(capacity) FROM table_slots WHERE is_active = 1")->fetchColumn() ?: 0;
    
    // 2. Booked Today (Count tables assigned to reservations)
    $booked_today = $pdo->query("
        SELECT COUNT(DISTINCT rt.table_id) 
        FROM reservation_tables rt
        JOIN reservations r ON r.id = rt.reservation_id
        WHERE r.reservation_date = '$today' AND r.status IN ('Pending', 'Confirmed')
    ")->fetchColumn() ?: 0;
    
    // If reservation_tables doesn't exist or is empty, fallback to reservation count
    if ($booked_today == 0) {
        $booked_today = $pdo->query("SELECT COUNT(*) FROM reservations WHERE reservation_date = '$today' AND status IN ('Pending', 'Confirmed')")->fetchColumn() ?: 0;
    }
    
    $available_today = max(0, $total_tables - $booked_today);


    // Peak time slots
    $stmt_peak = $pdo->query("SELECT time_slot FROM table_slots WHERE is_peak_hour = 1 AND is_active = 1");
    $peak_slots = $stmt_peak->fetchAll(PDO::FETCH_COLUMN);
    $peak_display = !empty($peak_slots) ? implode(', ', array_slice($peak_slots, 0, 2)) : 'None';
} catch (Exception $e) {
    $total_tables = 0;
    $booked_today = 0;
    $available_today = 0;
    $peak_display = 'None';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Table Booking Management | LUXE Admin</title>
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
                        'background-light': '#f4efec',
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
        body { font-family: 'Inter', sans-serif; background-color: #f4efec; }
        .serif-title { font-family: 'Playfair Display', serif; }
        .glass-card { background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(8px); border: 1px solid #e5e0dd; }
        .calendar-day { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .calendar-day:hover:not(.disabled) { transform: translateY(-2px); }
        .slot-card { transition: all 0.3s ease; border-left: 4px solid transparent; }
        .slot-card.active { border-left-color: #c67c7c; background: #fdfbf9; }
        .table-btn { transition: all 0.2s ease; transform-origin: center; cursor: default; }
        .table-btn.available { background: #ecfdf5; border: 1px solid #10b981; color: #059669; }
        .table-btn.booked { background: #fef2f2; border: 1px solid #ef4444; color: #dc2626; cursor: not-allowed; }
        .table-btn.selected { background: #c67c7c; border-color: #c67c7c; color: white; transform: scale(1.05); }
        .custom-scrollbar::-webkit-scrollbar { width: 4px; height: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #c67c7c44; border-radius: 10px; }
    </style>
</head>
<body class="text-luxe-dark">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <?php include '../includes/admin_sidebar.php'; ?>

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto px-8 py-8 custom-scrollbar">
            <!-- Header Section -->
            <header class="flex justify-between items-center mb-10">
                <div>
                    <h1 class="serif-title text-4xl font-black text-luxe-charcoal leading-tight">Smart Table Booking Management</h1>
                    <p class="text-luxe-grey-text font-medium mt-1">Day-wise table availability and booking overview.</p>
                </div>
                <div class="flex items-center gap-6">
                    <div class="text-right hidden md:block">
                        <p class="text-sm font-bold text-luxe-charcoal">Admin Panel</p>
                        <p class="text-xs text-primary font-bold uppercase tracking-widest">Luxe Rose Edition</p>
                    </div>
                    <div class="size-12 rounded-2xl bg-primary/10 flex items-center justify-center text-primary shadow-sm border border-primary/20">
                        <span class="material-symbols-outlined">person</span>
                    </div>
                </div>
            </header>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
                <div class="glass-card p-6 rounded-[2rem] shadow-sm hover:shadow-md transition-all">
                    <div class="flex justify-between items-start mb-4">
                        <p class="text-luxe-grey-text text-[10px] font-black uppercase tracking-[0.2em]">Total Tables</p>
                        <div class="size-10 rounded-xl bg-luxe-beige flex items-center justify-center text-luxe-charcoal">
                            <span class="material-symbols-outlined text-xl">table_restaurant</span>
                        </div>
                    </div>
                    <div class="flex items-baseline gap-2">
                        <h2 id="stat-total-tables" class="text-3xl font-black text-luxe-charcoal"><?= number_format($total_tables) ?></h2>
                        <span class="text-xs text-luxe-grey-text font-bold">Tables Total</span>
                    </div>
                </div>
                <div class="glass-card p-6 rounded-[2rem] shadow-sm hover:shadow-md transition-all">
                    <div class="flex justify-between items-start mb-4">
                        <p class="text-luxe-grey-text text-[10px] font-black uppercase tracking-[0.2em]">Available Today</p>
                        <div class="size-10 rounded-xl bg-green-50 flex items-center justify-center text-green-600">
                            <span class="material-symbols-outlined text-xl">check_circle</span>
                        </div>
                    </div>
                    <div class="flex items-baseline gap-2">
                        <h2 id="stat-available-today" class="text-3xl font-black text-luxe-charcoal"><?= number_format($available_today) ?></h2>
                        <span class="text-xs text-luxe-grey-text font-bold">Ready to Book</span>
                    </div>
                </div>
                <div class="glass-card p-6 rounded-[2rem] shadow-sm hover:shadow-md transition-all border-l-4 border-primary">
                    <div class="flex justify-between items-start mb-4">
                        <p class="text-luxe-grey-text text-[10px] font-black uppercase tracking-[0.2em]">Booked Today</p>
                        <div class="size-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary">
                            <span class="material-symbols-outlined text-xl">bookmarks</span>
                        </div>
                    </div>
                    <div class="flex items-baseline gap-2">
                        <h2 id="stat-booked-today" class="text-3xl font-black text-luxe-charcoal"><?= number_format($booked_today) ?></h2>
                        <span class="text-xs text-luxe-grey-text font-bold">Reservations</span>
                    </div>
                </div>
                <div class="glass-card p-6 rounded-[2rem] shadow-sm hover:shadow-md transition-all">
                    <div class="flex justify-between items-start mb-4">
                        <p class="text-luxe-grey-text text-[10px] font-black uppercase tracking-[0.2em]">Peak Time Slots</p>
                        <div class="size-10 rounded-xl bg-amber-50 flex items-center justify-center text-amber-600">
                            <span class="material-symbols-outlined text-xl">bolt</span>
                        </div>
                    </div>
                    <div class="flex items-baseline gap-2">
                        <h2 id="stat-peak-display" class="text-lg font-black text-luxe-charcoal truncate"><?= htmlspecialchars($peak_display) ?></h2>
                        <span class="text-xs text-luxe-grey-text font-bold">High Demand</span>
                    </div>
                </div>
            </div>

            <!-- Main Interactive Section -->
            <div class="grid grid-cols-1 xl:grid-cols-12 gap-8">
                <!-- Left: Calendar Selection (3 cols) -->
                <div class="xl:col-span-4 space-y-8">
                    <div class="glass-card p-6 rounded-[2.5rem] shadow-sm">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-lg font-black text-luxe-charcoal">Booking Calendar</h3>
                            <div class="flex items-center gap-2">
                                <button onclick="changeMonth(-1)" class="size-8 rounded-lg hover:bg-slate-100 flex items-center justify-center text-slate-400 transition-colors">
                                    <span class="material-symbols-outlined text-lg">chevron_left</span>
                                </button>
                                <span id="current-month-display" class="text-[10px] font-black text-luxe-grey-text uppercase tracking-widest px-4 py-2 border border-luxe-border rounded-xl min-w-[140px] text-center">Checking...</span>
                                <button onclick="changeMonth(1)" class="size-8 rounded-lg hover:bg-slate-100 flex items-center justify-center text-slate-400 transition-colors">
                                    <span class="material-symbols-outlined text-lg">chevron_right</span>
                                </button>
                            </div>
                        </div>

                        <!-- Mini Calendar View -->
                        <div id="calendar-container" class="grid grid-cols-7 gap-2">
                            <!-- JS will populate this -->
                        </div>

                        <div class="mt-8 pt-6 border-t border-luxe-border flex justify-between items-center">
                            <div class="flex gap-4">
                                <div class="flex items-center gap-2">
                                    <div class="size-2 rounded-full bg-green-500"></div>
                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Available</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="size-2 rounded-full bg-red-500"></div>
                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Full</span>
                                </div>
                            </div>
                            <span id="selected-date-display" class="text-xs font-black text-primary uppercase">Feb 26, 2026</span>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-col gap-3">
                        <button class="w-full py-4 bg-primary text-white rounded-2xl font-bold flex items-center justify-center gap-3 shadow-lg shadow-primary/20 hover:bg-primary-hover transition-all group">
                            <span class="material-symbols-outlined group-hover:rotate-12 transition-transform">add_circle</span>
                            Add New Time Slot
                        </button>

                    </div>
                </div>

                <!-- Right: Slot & Table Details (8 cols) -->
                <div class="xl:col-span-8 space-y-8">
                    <!-- Time Slot Selection -->
                    <div class="glass-card p-6 rounded-[2.5rem] shadow-sm">
                        <h3 class="text-lg font-black text-luxe-charcoal mb-6 font-serif">Time Slot Availability</h3>
                        <div id="slots-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <?php foreach ($active_slots as $slot): 
                                $isActive = (bool)$slot['is_active'];
                                $booked = (int)$slot['current_bookings']; 
                                $capacity = (int)$slot['capacity'];
                                $remain = max(0, $capacity - $booked);
                                $percent = $capacity > 0 ? round(($booked / $capacity) * 100) : 0;
                                $isFull = $booked >= $capacity;
                                
                                $colorClass = 'bg-green-500';
                                $bgClass = 'bg-green-50/50';
                                if (!$isActive) { $colorClass = 'bg-slate-300'; $bgClass = 'bg-slate-50'; }
                                elseif ($isFull) { $colorClass = 'bg-red-500'; $bgClass = 'bg-red-50/50'; }
                                elseif ($percent > 60) { $colorClass = 'bg-amber-500'; $bgClass = 'bg-amber-50/50'; }
                            ?>
                            <button onclick="<?= $isActive ? "selectSlot('".$slot['time_slot']."')" : "void(0)" ?>" class="slot-card w-full p-4 rounded-2xl border text-left flex flex-col gap-3 group transition-all duration-300 bg-white <?= $isActive ? 'border-luxe-border hover:border-primary/20 hover:shadow-md' : 'opacity-60 grayscale border-luxe-border cursor-not-allowed' ?>">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="text-sm font-black text-luxe-charcoal"><?= $slot['time_slot'] ?></p>
                                        <p class="text-[10px] font-bold <?= !$isActive ? 'text-slate-400' : ($isFull ? 'text-red-500' : 'text-slate-400') ?>">
                                            <?= !$isActive ? 'DISABLED' : ($isFull ? 'FULLY BOOKED' : $remain . ' Tables Left') ?>
                                        </p>
                                    </div>
                                    <?php if ($slot['is_peak_hour']): ?>
                                        <span class="material-symbols-outlined text-amber-500 text-base" style="font-variation-settings: 'FILL' 1">bolt</span>
                                    <?php endif; ?>
                                </div>
                                <div class="w-full h-1.5 <?= $bgClass ?> rounded-full overflow-hidden">
                                    <div class="h-full <?= $colorClass ?> transition-all duration-500" style="width: <?= $percent ?>%"></div>
                                </div>
                                <div class="flex justify-between items-center text-[8px] font-black uppercase tracking-widest">
                                    <span class="<?= !$isActive ? 'text-slate-400' : ($isFull ? 'text-red-600' : 'text-slate-400') ?>"><?= $booked ?> Booked</span>
                                    <span class="text-slate-300"><?= $percent ?>% Occupancy</span>
                                </div>
                            </button>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Table Availability Details -->
                    <div id="table-selection-area" class="glass-card p-8 rounded-[3rem] shadow-sm relative overflow-hidden hidden mt-8">
                        <!-- BG Decoration -->
                        <div class="absolute -top-10 -right-10 size-64 bg-primary/5 rounded-full blur-3xl"></div>
                        
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-10 relative z-10">
                            <div>
                                <h3 class="text-2xl font-black text-luxe-charcoal font-serif">Booking Details</h3>
                                <p class="text-sm text-luxe-grey-text font-medium">Detailed reservation information for selected slot</p>
                            </div>
                        </div>

                        <div id="table-grid" class="flex flex-col mb-4">
                            <!-- Bookings will be injected via JS -->
                        </div>

                        <div id="no-tables-message" class="hidden py-12 text-center bg-luxe-beige/10 rounded-3xl border border-dashed border-luxe-border">
                            <span class="material-symbols-outlined text-4xl text-luxe-grey-text mb-3">event_busy</span>
                            <h4 class="text-luxe-charcoal font-black">No bookings for this time slot.</h4>
                            <p class="text-sm text-luxe-grey-text mt-1">Select another time or date to see more information.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <footer class="mt-16 pt-8 border-t border-luxe-border flex flex-col md:flex-row justify-between items-center gap-4 text-luxe-grey-text text-[10px] font-black uppercase tracking-[0.2em]">
                <p>&copy; 2024 LUXE RESTAURANT MANAGEMENT. ROSE EDITION V2.5.0</p>
                <div class="flex gap-8">
                    <a href="#" class="hover:text-primary transition-colors">Documentation</a>
                    <a href="#" class="hover:text-primary transition-colors">Operational logs</a>
                    <a href="#" class="hover:text-primary transition-colors">Support center</a>
                </div>
            </footer>
        </main>
    </div>

    <!-- Toast Component -->
    <div id="toast" class="fixed bottom-10 right-10 z-[100] transform translate-y-20 opacity-0 transition-all duration-300 pointer-events-none">
        <div class="bg-white px-6 py-4 rounded-2xl shadow-2xl border border-luxe-border flex items-center gap-4">
            <div id="toast-icon-bg" class="size-10 rounded-xl flex items-center justify-center text-white">
                <span id="toast-icon" class="material-symbols-outlined">check</span>
            </div>
            <div>
                <p id="toast-title" class="text-sm font-black text-slate-900 leading-tight"></p>
                <p id="toast-message" class="text-xs text-slate-500 mt-0.5 font-medium"></p>
            </div>
        </div>
    </div>

    <script>
        const state = {
            selectedDate: '<?= $today ?>',
            selectedSlot: null,
            slots: [],
            tables: [],
            currentMonth: new Date().getMonth(),
            currentYear: new Date().getFullYear()
        };

        const elements = {
            calendar: document.getElementById('calendar-container'),
            dateDisplay: document.getElementById('selected-date-display'),
            slotsGrid: document.getElementById('slots-grid'),
            tableArea: document.getElementById('table-selection-area'),
            tableGrid: document.getElementById('table-grid'),
            noTables: document.getElementById('no-tables-message'),
            toast: document.getElementById('toast'),
            monthDisplay: document.getElementById('current-month-display'),
            statTotal: document.getElementById('stat-total-tables'),
            statAvailable: document.getElementById('stat-available-today'),
            statBooked: document.getElementById('stat-booked-today'),
            statPeak: document.getElementById('stat-peak-display')
        };

        function init() {
            renderCalendar();
            refreshBookingData();
        }

        // Enterprise Architecture: Single Source of Truth
        const toYMD = (y, m, d) => `${y}-${String(m + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`;

        function updateSelectedDate(newDate) {
            state.selectedDate = newDate;
            
            // Sync month/year view to the selected date
            const [y, m, d] = newDate.split('-').map(Number);
            state.currentMonth = m - 1;
            state.currentYear = y;
            
            renderCalendar();
            refreshBookingData();
        }

        function changeMonth(delta) {
            state.currentMonth += delta;
            if (state.currentMonth < 0) { state.currentMonth = 11; state.currentYear--; }
            else if (state.currentMonth > 11) { state.currentMonth = 0; state.currentYear++; }
            
            // Auto-select 1st of the month when navigating
            updateSelectedDate(toYMD(state.currentYear, state.currentMonth, 1));
        }

        function renderCalendar() {
            const months = ['JANUARY', 'FEBRUARY', 'MARCH', 'APRIL', 'MAY', 'JUNE', 'JULY', 'AUGUST', 'SEPTEMBER', 'OCTOBER', 'NOVEMBER', 'DECEMBER'];
            const viewMonth = state.currentMonth;
            const viewYear = state.currentYear;
            
            document.getElementById('current-month-display').innerText = `${months[viewMonth]} ${viewYear}`;

            const formatDateLabel = (dateStr) => {
                const [y, m, d] = dateStr.split('-').map(Number);
                return new Date(y, m - 1, d).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
            };

            elements.dateDisplay.innerText = formatDateLabel(state.selectedDate);
            
            const firstDay = new Date(viewYear, viewMonth, 1).getDay();
            const daysInMonth = new Date(viewYear, viewMonth + 1, 0).getDate();
            
            elements.calendar.innerHTML = '';
            ['S', 'M', 'T', 'W', 'T', 'F', 'S'].forEach(day => {
                const el = document.createElement('div');
                el.className = 'text-center text-[10px] font-black text-slate-300 mb-2';
                el.innerText = day;
                elements.calendar.appendChild(el);
            });

            for (let i = 0; i < firstDay; i++) {
                elements.calendar.appendChild(document.createElement('div'));
            }

            const now = new Date();
            const todayStr = toYMD(now.getFullYear(), now.getMonth(), now.getDate());

            for (let i = 1; i <= daysInMonth; i++) {
                const dayStr = toYMD(viewYear, viewMonth, i);
                const isSelected = dayStr === state.selectedDate;
                const isToday = dayStr === todayStr;
                
                const dayEl = document.createElement('button');
                dayEl.className = `calendar-day size-10 rounded-xl text-xs font-bold flex items-center justify-center transition-all ${isSelected ? 'bg-primary text-white shadow-lg shadow-primary/20' : isToday ? 'text-primary border border-primary/20' : 'text-slate-600 hover:bg-primary/5 hover:text-primary'}`;
                dayEl.innerText = i;
                dayEl.onclick = () => updateSelectedDate(dayStr);
                elements.calendar.appendChild(dayEl);
            }
        }

        async function refreshBookingData() {
            const date = state.selectedDate;
            
            // Reset UI states
            elements.slotsGrid.innerHTML = `
                <div class="col-span-full py-12 flex flex-col items-center gap-4 text-slate-400">
                    <div class="animate-spin size-8 border-2 border-primary/30 border-t-primary rounded-full"></div>
                    <p class="text-[10px] font-black uppercase tracking-widest">Refreshing Data...</p>
                </div>
            `;
            elements.tableArea.classList.add('hidden');
            state.selectedSlot = null;

            try {
                const res = await fetch(`../actions/reservation/get_all_availabilities.php?date=${date}&guests=1&admin=1`);
                const data = await res.json();
                
                if (data.success) {
                    state.slots = data.slots;
                    
                    // Unified Stats Refresh
                    if (data.summary_stats) {
                        elements.statTotal.innerText = data.summary_stats.total_tables.toLocaleString();
                        elements.statAvailable.innerText = data.summary_stats.available_today.toLocaleString();
                        elements.statBooked.innerText = data.summary_stats.booked_today.toLocaleString();
                        elements.statPeak.innerText = data.summary_stats.peak_display;
                    }

                    renderSlots();
                } else {
                    showToast('Refresh Failed', data.message || 'Unable to fetch booking data.', 'error');
                }
            } catch (err) {
                showToast('Network Error', 'Check your connection and try again.', 'error');
            }
        }

        function renderSlots() {
            elements.slotsGrid.innerHTML = '';
            if (state.slots.length === 0) {
                elements.slotsGrid.innerHTML = '<p class="col-span-full text-center py-10 text-slate-400 text-sm italic">No active slots found for this date.</p>';
                return;
            }

            state.slots.forEach(slot => {
                const card = document.createElement('button');
                const p = slot.percent || 0;
                const isActive = slot.is_active;
                const isFull = slot.booked >= slot.capacity;
                let colorClass = 'bg-green-500';
                let bgClass = 'bg-green-50/50';
                
                if (!isActive) {
                    colorClass = 'bg-slate-300';
                    bgClass = 'bg-slate-50';
                } else if (isFull) { 
                    colorClass = 'bg-red-500'; 
                    bgClass = 'bg-red-50/50'; 
                } else if (p > 60) { 
                    colorClass = 'bg-amber-500'; 
                    bgClass = 'bg-amber-50/50'; 
                }

                card.className = `slot-card w-full p-4 rounded-2xl border text-left flex flex-col gap-3 group transition-all duration-300 ${state.selectedSlot === slot.time ? 'active border-primary/20 bg-white shadow-lg scale-[1.02]' : 'bg-white border-slate-100 hover:border-primary/20 hover:shadow-md'} ${!isActive ? 'opacity-60 grayscale cursor-not-allowed' : ''}`;
                
                if (isActive) {
                    card.onclick = () => selectSlot(slot.time);
                }
                
                card.innerHTML = `
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-sm font-black text-slate-900">${slot.time}</p>
                            <p class="text-[10px] font-bold ${!isActive ? 'text-slate-400' : (isFull ? 'text-red-500' : 'text-slate-400')}">
                                ${!isActive ? 'DISABLED' : (isFull ? 'FULLY BOOKED' : slot.remaining + ' Tables Left')}
                            </p>
                        </div>
                        ${slot.is_peak ? '<span class="material-symbols-outlined text-amber-500 text-base" style="font-variation-settings: \'FILL\' 1">bolt</span>' : ''}
                    </div>
                    <div class="w-full h-1.5 ${bgClass} rounded-full overflow-hidden">
                        <div class="h-full ${colorClass} transition-all duration-500" style="width: ${p}%"></div>
                    </div>
                    <div class="flex justify-between items-center text-[8px] font-black uppercase tracking-widest">
                        <span class="${!isActive ? 'text-slate-400' : (isFull ? 'text-red-600' : 'text-slate-400')}">${slot.booked || 0} Booked</span>
                        <span class="text-slate-300">${p}% Occupancy</span>
                    </div>
                `;
                elements.slotsGrid.appendChild(card);
            });
        }

        function selectSlot(time) {
            state.selectedSlot = time;
            renderSlots();
            fetchSlotBookings(state.selectedDate, time);
        }

        async function fetchSlotBookings(date, time) {
            elements.tableArea.classList.remove('hidden');
            elements.tableGrid.innerHTML = `
                <div class="col-span-full py-20 flex flex-col items-center gap-4 text-slate-400">
                    <div class="animate-spin size-10 border-2 border-primary/20 border-t-primary rounded-full"></div>
                    <p class="text-xs font-black uppercase tracking-widest">Fetching booking details...</p>
                </div>
            `;
            elements.noTables.classList.add('hidden');
            elements.tableGrid.classList.remove('hidden');

            try {
                const res = await fetch(`../api/admin/get_slot_bookings.php?date=${date}&time=${time}`);
                const data = await res.json();
                if (data.success) {
                    renderBookings(data.bookings);
                } else {
                    showToast('Error', data.message || 'Failed to load bookings.', 'error');
                }
            } catch (err) {
                showToast('Network Error', 'Unable to reach the server.', 'error');
            }
        }

        function renderBookings(bookings) {
            elements.tableGrid.innerHTML = '';
            elements.tableGrid.className = "w-full overflow-hidden rounded-[2rem] border border-slate-100 bg-white/50";
            
            if (!bookings || bookings.length === 0) {
                elements.tableGrid.classList.add('hidden');
                elements.noTables.classList.remove('hidden');
                return;
            }

            elements.tableGrid.classList.remove('hidden');
            elements.noTables.classList.add('hidden');

            // Add Table Header
            const header = document.createElement('div');
            header.className = "grid grid-cols-6 gap-4 p-4 bg-slate-50/80 rounded-t-2xl border-b border-slate-100 text-[8px] font-black text-slate-400 uppercase tracking-[0.2em]";
            header.innerHTML = `
                <div>Table</div>
                <div>Guest</div>
                <div>Phone</div>
                <div>Guests</div>
                <div>Status</div>
                <div class="text-right">Time</div>
            `;
            elements.tableGrid.appendChild(header);

            bookings.forEach(booking => {
                const row = document.createElement('div');
                row.className = "grid grid-cols-6 gap-4 p-4 border-b border-slate-50 hover:bg-slate-50/50 transition-colors items-center";
                row.innerHTML = `
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-xs text-slate-400">table_restaurant</span>
                        <span class="text-[10px] font-black text-slate-900 uppercase tracking-widest">${booking.table_name}</span>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-slate-900">${booking.guest_name}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-slate-400">${booking.phone || 'N/A'}</p>
                    </div>
                    <div>
                        <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 text-[8px] font-black uppercase tracking-widest">${booking.guest_count} Guests</span>
                    </div>
                    <div>
                        <span class="px-2 py-1 rounded-lg text-[8px] font-black uppercase tracking-widest ${booking.status === 'Confirmed' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700'}">${booking.status}</span>
                    </div>
                    <div class="text-right">
                        <span class="text-[10px] font-bold text-slate-600 uppercase tracking-widest">${booking.reservation_time}</span>
                    </div>
                `;
                elements.tableGrid.appendChild(row);
            });
        }


        function showToast(title, message, type = 'success') {
            const iconBg = document.getElementById('toast-icon-bg');
            const icon = document.getElementById('toast-icon');
            
            if (type === 'error') {
                iconBg.className = 'size-10 rounded-xl flex items-center justify-center text-white bg-red-500 shadow-lg shadow-red-200';
                icon.innerText = 'warning';
            } else {
                iconBg.className = 'size-10 rounded-xl flex items-center justify-center text-white bg-primary shadow-lg shadow-primary/20';
                icon.innerText = 'check';
            }

            document.getElementById('toast-title').innerText = title;
            document.getElementById('toast-message').innerText = message;
            
            elements.toast.classList.remove('translate-y-20', 'opacity-0');
            setTimeout(() => {
                elements.toast.classList.add('translate-y-20', 'opacity-0');
            }, 3000);
        }

        // Run init on load
        document.addEventListener('DOMContentLoaded', init);
    </script>
</body>
</html>
