<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<header class="sticky top-0 z-50 bg-white/80 backdrop-blur-md border-b border-gray-100">
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
<!-- Brand Logo -->
<div class="flex-shrink-0">
<a class="text-3xl font-serif tracking-widest font-bold" href="home.php">KUKI</a>
</div>
<!-- Navigation Links -->
<nav class="hidden md:flex space-x-10 text-sm font-medium tracking-wide">
<a class="<?php echo ($current_page == 'home.php' || $current_page == 'index.php') ? 'text-luxe-rose border-b border-luxe-rose' : 'hover:text-luxe-rose transition-colors'; ?>" href="home.php">Home</a>
<a class="<?php echo ($current_page == 'food_menu.php') ? 'text-luxe-rose border-b border-luxe-rose' : 'hover:text-luxe-rose transition-colors'; ?>" href="food_menu.php">Menu</a>
<a class="<?php echo ($current_page == 'table_booking.php' || $current_page == 'booking.php') ? 'text-luxe-rose border-b border-luxe-rose' : 'hover:text-luxe-rose transition-colors'; ?>" href="table_booking.php">Reservations</a>
<a class="<?php echo ($current_page == 'about.php') ? 'text-luxe-rose border-b border-luxe-rose' : 'hover:text-luxe-rose transition-colors'; ?>" href="about.php">About</a>
<a class="<?php echo ($current_page == 'feedback.php') ? 'text-luxe-rose border-b border-luxe-rose' : 'hover:text-luxe-rose transition-colors'; ?>" href="feedback.php">Reviews</a>
</nav>
<!-- Search & CTA -->
<div class="flex items-center space-x-6">

<a class="bg-luxe-rose text-white px-6 py-2.5 rounded-full text-sm font-medium hover:bg-opacity-90 transition-all shadow-md" href="table_booking.php">Book a Table</a>
</div>
</div>
</header>
