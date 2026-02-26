<?php
$currentPage = basename($_SERVER['PHP_SELF']);

function is_active($page, $current) {
    // Group active pages for Menu Management
    $menuPages = ['menumanage.php', 'addnewdish.php', 'addNewCategory.php'];
    if ($page == 'menumanage.php' && in_array($current, $menuPages)) return true;
    
    // Group active pages for Reports
    $reportPages = ['exportReport.php', 'reportgenrate.php'];
    if ($page == 'reportgenrate.php' && in_array($current, $reportPages)) return true;
    
    return $current == $page;
}

function getLinkClass($page, $current) {
    if (is_active($page, $current)) {
        return 'flex items-center gap-3 px-4 py-3 rounded-[14px] bg-[#c67c7c] text-white shadow-lg shadow-[#c67c7c]/20 transition-all duration-300 ease-out';
    }
    return 'flex items-center gap-3 px-4 py-3 rounded-[14px] text-[#707070] hover:bg-white transition-all duration-300 ease-out group';
}

function getSvgClass($page, $current) {
    if (is_active($page, $current)) {
        return 'w-5 h-5';
    }
    return 'w-5 h-5 group-hover:text-[#c67c7c]';
}
?>
<aside class="fixed inset-y-0 left-0 w-64 bg-[#f4efec] border-r border-[#e5e0dd] flex flex-col z-[60]">
<div class="p-8 pb-12">
<div class="flex items-center gap-3">
<div class="bg-[#c67c7c]/20 p-2 rounded-lg">
<svg class="w-6 h-6 text-[#c67c7c]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
<path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" stroke-linecap="round" stroke-linejoin="round"></path>
</svg>
</div>
<div>
<div class="text-lg font-serif font-bold tracking-tighter text-[#2b2b2b] leading-none">LUXE</div>
<div class="text-[10px] uppercase tracking-[0.2em] text-[#c67c7c]">Rose Edition Admin</div>
</div>
</div>
</div>
<nav class="flex-1 px-4 space-y-1 overflow-y-auto">
<a class="<?= getLinkClass('dashboard.php', $currentPage) ?>" href="dashboard.php">
<svg class="<?= getSvgClass('dashboard.php', $currentPage) ?>" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
<span class="text-sm font-medium">Dashboard</span>
</a>
<a class="<?= getLinkClass('menumanage.php', $currentPage) ?>" href="menumanage.php">
<svg class="<?= getSvgClass('menumanage.php', $currentPage) ?>" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
<span class="text-sm font-medium">Menu Management</span>
</a>
<a class="<?= getLinkClass('reservation.php', $currentPage) ?>" href="reservation.php">
<svg class="<?= getSvgClass('reservation.php', $currentPage) ?>" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
<span class="text-sm font-medium">Reservations</span>
</a>
<a class="<?= getLinkClass('feedback.php', $currentPage) ?>" href="feedback.php">
<svg class="<?= getSvgClass('feedback.php', $currentPage) ?>" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
<span class="text-sm font-medium">Feedback</span>
</a>
<a class="<?= getLinkClass('tableslot.php', $currentPage) ?>" href="tableslot.php">
<svg class="<?= getSvgClass('tableslot.php', $currentPage) ?>" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
<span class="text-sm font-medium">Table Slots</span>
</a>
<a class="<?= getLinkClass('reportgenrate.php', $currentPage) ?>" href="reportgenrate.php">
<svg class="<?= getSvgClass('reportgenrate.php', $currentPage) ?>" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
<span class="text-sm font-medium">Reports</span>
</a>
<div class="pt-8 pb-4">
<span class="px-4 text-[10px] uppercase tracking-[0.2em] text-gray-400 font-bold">System</span>
</div>
<a class="<?= getLinkClass('setting.php', $currentPage) ?>" href="setting.php">
<svg class="<?= getSvgClass('setting.php', $currentPage) ?>" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
<span class="text-sm font-medium">Settings</span>
</a>
</nav>
<div class="p-4 border-t border-[#e5e0dd] mt-auto">
<a class="flex items-center gap-3 px-4 py-3 rounded-[14px] text-[#c67c7c] hover:bg-[#c67c7c]/5 transition-all duration-300 ease-out" href="logout.php">
<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
<span class="text-sm font-medium">Logout</span>
</a>
</div>
</aside>

<!-- Spacer to preserve flex layouts -->
<div class="w-64 shrink-0"></div>


