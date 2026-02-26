<?php
// Ensure variables are set
$breadcrumb = $breadcrumb ?? 'DASHBOARD / PAGE';
$title = $title ?? 'Page Title';

$parts = explode('/', $breadcrumb);
$part1 = trim($parts[0] ?? 'DASHBOARD');
$part2 = trim($parts[1] ?? '');

if (empty($part2)) {
    $part2 = $part1;
    $part1 = 'DASHBOARD';
}
?>
<div data-purpose="header-title-container">
    <nav class="text-xs uppercase tracking-[0.2em] text-[#c67c7c] mb-2 flex items-center gap-2" style="font-family: 'Inter', 'Public Sans', sans-serif;">
        <span><?= htmlspecialchars($part1) ?></span>
        <span class="text-gray-600">/</span>
        <span class="text-gray-400"><?= htmlspecialchars($part2) ?></span>
    </nav>
    <h1 class="text-3xl text-[#2b2b2b] tracking-wide" style="font-family: 'Playfair Display', 'Georgia', serif; font-weight: 700;"><?= htmlspecialchars($title) ?></h1>
</div>
