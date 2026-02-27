<?php 
require_once '../config/config.php';
require_once '../includes/functions.php';
require_once '../actions/menu/menu_actions.php';

$page_title = "Exquisite Menu | KUKI";
include '../includes/user_header.php'; 

$categories = get_all_categories($pdo);
$all_dishes = get_all_dishes($pdo); // Assuming this returns all with category_name

// Filter for visible dishes
$dishes = array_filter($all_dishes, function($d) {
    return (int)$d['is_visible'] === 1;
});
?>

<!-- BEGIN: HeroSection -->
<section class="py-20 text-center px-4" data-purpose="hero">
    <h1 class="text-5xl md:text-7xl mb-6">From Our Kitchen</h1>
    <p class="text-lg md:text-xl text-gray-600 font-light tracking-widest uppercase">Crafted with Passion & Precision</p>
</section>
<!-- END: HeroSection -->

<!-- BEGIN: CategoryNavigation -->
<section class="max-w-4xl mx-auto mb-16" data-purpose="category-filter">
    <div class="flex flex-wrap justify-center gap-6 md:gap-12 border-b border-gray-200 pb-4">
        <a class="nav-tab text-luxe-rose font-medium transition-colors duration-300 cursor-pointer" onclick="filterMenu(event, 'all')">All Dishes</a>
        <?php foreach ($categories as $cat): ?>
        <?php if (($cat['slug'] ?? '') !== 'uncategorized'): ?>
        <a class="nav-tab text-gray-500 hover:text-luxe-charcoal transition-colors duration-300 cursor-pointer" onclick="filterMenu(event, '<?= strtolower(str_replace(' ', '-', $cat['name'])) ?>')"><?= e($cat['name']) ?></a>
        <?php endif; ?>
        <?php endforeach; ?>
    </div>
</section>
<!-- END: CategoryNavigation -->

<!-- BEGIN: MenuGrid -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-0" data-purpose="menu-listing">
    
    <?php foreach ($categories as $cat): ?>
    <?php if (($cat['slug'] ?? '') !== 'uncategorized'): ?>
    <?php 
    $cat_id = strtolower(str_replace(' ', '-', $cat['name']));
    $cat_dishes = array_filter($dishes, function($d) use ($cat) {
        return $d['category_id'] == $cat['id'];
    });
    ?>
    <div class="mb-16 menu-category transition-all duration-500 ease-in-out" id="<?= $cat_id ?>">
        <h2 class="text-3xl mb-10 pb-2 border-b border-luxe-rose w-fit"><?= e($cat['name']) ?></h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10">
            <?php foreach ($cat_dishes as $dish): ?>
            <div class="menu-card bg-white rounded-[14px] overflow-hidden shadow-sm hover:shadow-md transition-shadow duration-300">
                <img alt="<?= e($dish['name']) ?>" class="w-full h-64 object-cover" src="<?= e($dish['image_url'] ?: 'https://placehold.co/800x600?text='.urlencode($dish['name'])) ?>"/>
                <div class="p-6">
                    <div class="flex justify-between items-start mb-2">
                        <h3 class="text-xl font-bold"><?= e($dish['name']) ?></h3>
                        <span class="text-luxe-rose font-medium"><?= format_price($dish['price']) ?></span>
                    </div>
                    <p class="text-gray-500 text-sm leading-relaxed"><?= e($dish['description']) ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
    <?php endforeach; ?>

</section>
<!-- END: MenuGrid -->



<!-- BEGIN: CTASection -->
<section class="pt-4 pb-12 text-center px-4" data-purpose="call-to-action">
    <div class="max-w-2xl mx-auto">
        <h2 class="text-4xl mb-8">Ready for an Unforgettable Experience?</h2>
        <a class="inline-block bg-luxe-rose text-white px-10 py-4 rounded-full text-lg font-medium hover:bg-opacity-90 transition-all shadow-xl hover:shadow-2xl" href="table_booking.php">Reserve Your Table</a>
    </div>
</section>
<!-- END: CTASection -->

<!-- Menu Filtering Script -->
<script>
function filterMenu(event, category) {
    if (event) event.preventDefault();
    
    // 1. Update Active Tab Styling
    const tabs = document.querySelectorAll('.nav-tab');
    tabs.forEach(tab => {
        tab.classList.remove('text-luxe-rose', 'font-medium', 'text-luxe-charcoal');
        tab.classList.add('text-gray-500');
    });
    
    if (event) {
        event.target.classList.remove('text-gray-500');
        event.target.classList.add('text-luxe-rose', 'font-medium');
    }

    // 2. Filter Categories and Items
    const categories = document.querySelectorAll('.menu-category');
    
    categories.forEach(el => {
        // Reset transition styles first
        el.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
        
        // Dynamic Item Selection
        const items = el.querySelectorAll('.menu-card');
        
        if (category === 'all' || el.id === category) {
            el.style.display = 'block';
            
            // VISIBILITY LOGIC: 
            // - If 'All Dishes' (category === 'all'): Show only first 3 items per section.
            // - If Specific Category: Show ALL items in that section.
            
            if (category === 'all') {
                items.forEach((item, index) => {
                    if (index < 3) {
                        item.style.display = 'block';
                    } else {
                        item.style.display = 'none';
                    }
                });
            } else {
                items.forEach(item => {
                    item.style.display = 'block';
                });
            }

            // Use setTimeout to allow display:block to apply before changing opacity for transition
            setTimeout(() => {
                el.style.opacity = '1';
                el.style.transform = 'translateY(0)';
            }, 10);
        } else {
            // Hide the category container completely
            el.style.opacity = '0';
            el.style.transform = 'translateY(20px)';
            setTimeout(() => {
                // Check if it's still supposed to be hidden (user might have clicked quickly)
                if (el.style.opacity === '0') {
                    el.style.display = 'none';
                }
            }, 500); // Wait for transition to finish
        }
    });
}

// Initialize default state on page load
document.addEventListener('DOMContentLoaded', function() {
    // Trigger "All Dishes" view to limit items to 3 per category
    filterMenu(null, 'all');
});
</script>

<?php include '../includes/user_footer.php'; ?>
