<?php 
require_once '../config/config.php';
require_once '../includes/functions.php';
require_once '../actions/feedback/feedback_actions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'name' => $_POST['name'] ?? 'Guest',
        'email' => $_POST['email'] ?? '',
        'rating' => (int)($_POST['rating'] ?? 5),
        'review_text' => $_POST['message'] ?? '',
        'status' => 'pending'
    ];
    
    if (add_feedback($pdo, $data)) {
        $success = "Thank you for your feedback! Your review has been submitted for moderation.";
    } else {
        $error = "Failed to submit feedback. Please try again.";
    }
}

$page_title = "Guest Reviews | KUKI";
include '../includes/user_header.php'; 
$reviews = get_all_feedback($pdo, 'approved');
?>

<!-- BEGIN: Hero Section -->
<header class="pt-20 pb-8 text-center max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
<span class="text-xs font-bold tracking-[0.3em] uppercase block mb-4 text-primary">Experience</span>
<h1 class="text-5xl md:text-6xl font-serif mb-6 italic">What Our Guests Say</h1>
<p class="text-soft-grey text-lg max-w-2xl mx-auto italic">"Refined moments, shared memories."</p>
<div class="w-16 h-[1px] mx-auto mt-8 bg-primary"></div>
</header>
<!-- END: Hero Section -->
<!-- BEGIN: Featured Testimonials -->
<section class="pt-8 pb-20 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
<div id="reviews-grid" class="grid md:grid-cols-3 gap-8">
<?php if (empty($reviews)): ?>
    <p class="text-soft-grey italic text-center col-span-full py-10">No reviews yet. Be the first to share your experience!</p>
<?php else: ?>
    <?php foreach ($reviews as $review): 
        $initials = '';
        if (!empty($review['name'])) {
            $parts = explode(' ', $review['name']);
            $initials = strtoupper(substr($parts[0], 0, 1) . (count($parts) > 1 ? substr($parts[count($parts)-1], 0, 1) : ''));
        }
    ?>
    <div class="bg-white p-10 rounded-xl card-shadow relative animate-fade-in review-card">
        <div class="flex space-x-1 mb-6 text-sm text-primary">
            <?php for ($i = 1; $i <= 5; $i++): ?>
                <span><?= $i <= $review['rating'] ? '★' : '<span class="opacity-30">★</span>' ?></span>
            <?php endfor; ?>
        </div>
        <p class="text-charcoal italic mb-8 leading-relaxed">"<?= htmlspecialchars($review['review_text']) ?>"</p>
        <div class="flex items-center space-x-4">
            <div class="w-12 h-12 bg-background-ivory rounded-full flex items-center justify-center font-bold text-sm text-primary">
                <?= htmlspecialchars($initials) ?>
            </div>
            <div>
                <h4 class="font-bold text-sm uppercase tracking-wider"><?= htmlspecialchars($review['name']) ?></h4>
                <p class="text-xs text-soft-grey"><?= date("F j, Y", strtotime($review['created_at'])) ?></p>
            </div>
        </div>
        <span class="absolute top-8 right-10 text-4xl text-border-neutral font-serif">"</span>
    </div>
    <?php endforeach; ?>
<?php endif; ?>
</div>

<div class="text-center mt-16">
    <button id="load-more-btn" class="border border-border-neutral px-10 py-3 rounded-full text-xs font-bold uppercase tracking-widest transition-all hover:border-primary hover:text-primary disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:border-border-neutral disabled:hover:text-border-neutral">
        Load More Reviews
    </button>
</div>

</section>
<!-- END: Featured Testimonials -->
<!-- BEGIN: Rating Summary -->
<section class="py-16 bg-white/50 border-y border-border-neutral">
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row items-center justify-between gap-12">
<div class="text-center md:text-left">
<div class="text-6xl font-serif text-charcoal mb-2">4.8</div>
<div class="flex justify-center md:justify-start space-x-1 mb-2 text-primary">
<span>★</span><span>★</span><span>★</span><span>★</span><span class="opacity-50">★</span>
</div>
<p class="text-xs uppercase tracking-widest font-semibold">Based on 1,240 Reviews</p>
</div>
<div class="flex-grow w-full max-w-md space-y-3">
<!-- Progress Bar Row -->
<div class="flex items-center space-x-4">
<span class="text-[10px] font-bold w-12 text-right uppercase tracking-wider">5 Stars</span>
<div class="flex-grow bg-border-neutral/40 h-1.5 rounded-full overflow-hidden">
<div class="h-full bg-primary/60 rounded-full" style="width: 85%"></div>
</div>
<span class="text-[10px] text-soft-grey w-8 italic text-right font-medium">85%</span>
</div>
<div class="flex items-center space-x-4">
<span class="text-[10px] font-bold w-12 text-right uppercase tracking-wider">4 Stars</span>
<div class="flex-grow bg-border-neutral/40 h-1.5 rounded-full overflow-hidden">
<div class="h-full bg-primary/60 rounded-full" style="width: 10%"></div>
</div>
<span class="text-[10px] text-soft-grey w-8 italic text-right font-medium">10%</span>
</div>
<div class="flex items-center space-x-4">
<span class="text-[10px] font-bold w-12 text-right uppercase tracking-wider">3 Stars</span>
<div class="flex-grow bg-border-neutral/40 h-1.5 rounded-full overflow-hidden">
<div class="h-full bg-primary/60 rounded-full" style="width: 3%"></div>
</div>
<span class="text-[10px] text-soft-grey w-8 italic text-right font-medium">3%</span>
</div>
<div class="flex items-center space-x-4">
<span class="text-[10px] font-bold w-12 text-right uppercase tracking-wider">2 Stars</span>
<div class="flex-grow bg-border-neutral/40 h-1.5 rounded-full overflow-hidden">
<div class="h-full bg-primary/60 rounded-full" style="width: 1%"></div>
</div>
<span class="text-[10px] text-soft-grey w-8 italic text-right font-medium">1%</span>
</div>
<div class="flex items-center space-x-4">
<span class="text-[10px] font-bold w-12 text-right uppercase tracking-wider">1 Star</span>
<div class="flex-grow bg-border-neutral/40 h-1.5 rounded-full overflow-hidden">
<div class="h-full bg-primary/60 rounded-full" style="width: 1%"></div>
</div>
<span class="text-[10px] text-soft-grey w-8 italic text-right font-medium">1%</span>
</div>
</div>
</div>
</section>
<!-- END: Rating Summary -->
<!-- BEGIN: Reviews Grid -->

<!-- END: Reviews Grid -->
<!-- BEGIN: Add Review Form -->
<section class="py-20 bg-white/30">
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
<div class="bg-white p-12 rounded-xl card-shadow border border-border-neutral max-w-2xl mx-auto">
<div class="text-center mb-10">
<h2 class="text-3xl font-serif mb-2">Share Your Experience</h2>
<p class="text-xs text-soft-grey uppercase tracking-widest">Your feedback is our greatest inspiration</p>
<?php if (isset($success)): ?>
    <p class="text-green-600 text-sm mt-4"><?= $success ?></p>
<?php endif; ?>
<?php if (isset($error)): ?>
    <p class="text-red-500 text-sm mt-4"><?= $error ?></p>
<?php endif; ?>
</div>
<form action="" method="POST" class="space-y-6">
<div class="grid md:grid-cols-2 gap-6">
<div>
<label class="block text-[10px] font-bold uppercase tracking-widest mb-2 text-charcoal">Full Name</label>
<input name="name" required class="luxury-input" placeholder="Enter your name" type="text"/>
</div>
<div>
<label class="block text-[10px] font-bold uppercase tracking-widest mb-2 text-charcoal">Email Address</label>
<input name="email" required class="luxury-input" placeholder="your@email.com" type="email"/>
</div>
</div>
<div>
<label class="block text-[10px] font-bold uppercase tracking-widest mb-2 text-charcoal">Rating</label>
<div class="flex space-x-2 text-2xl text-border-neutral cursor-pointer" id="rating-stars">
<span class="hover:text-primary transition-colors duration-200" data-value="1">★</span>
<span class="hover:text-primary transition-colors duration-200" data-value="2">★</span>
<span class="hover:text-primary transition-colors duration-200" data-value="3">★</span>
<span class="hover:text-primary transition-colors duration-200" data-value="4">★</span>
<span class="hover:text-primary transition-colors duration-200" data-value="5">★</span>
</div>
<input type="hidden" name="rating" id="rating-input" value="5">
</div>
<div>
<label class="block text-[10px] font-bold uppercase tracking-widest mb-2 text-charcoal">Your Review</label>
<textarea name="message" required class="luxury-input h-32" placeholder="Tell us about your dining experience..."></textarea>
</div>
<button class="w-full text-white py-4 rounded-sm font-bold uppercase tracking-[0.2em] text-xs transition-colors shadow-lg shadow-primary/20 bg-primary hover:bg-primary-hover btn-lift-glow" type="submit">
            Share Your Experience
          </button>
</form>
</div>
</div>
</section>
<!-- END: Add Review Form -->
<!-- BEGIN: CTA Section -->
<section class="py-24 text-center bg-background-ivory border-t border-border-neutral">
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
<h2 class="text-4xl font-serif mb-8 italic">Ready to Experience KUKI?</h2>
<a class="inline-block bg-primary text-white px-12 py-4 rounded-sm font-bold uppercase tracking-[0.2em] text-xs hover:bg-primary-hover transition-all hover:scale-105" href="table_booking.php">
        Reserve a Table
      </a>
</div>
</section>


<script>
document.addEventListener('DOMContentLoaded', function() {
    const reviewsGrid = document.getElementById('reviews-grid');
    // Select all direct children (review cards)
    const allReviews = Array.from(reviewsGrid.children);
    const loadMoreBtn = document.getElementById('load-more-btn');
    const itemsPerLoad = 3;
    let visibleCount = 3;

    // Initially hide all reviews beyond the first 3
    allReviews.forEach((review, index) => {
        if (index >= visibleCount) {
            review.classList.add('hidden');
            review.classList.remove('animate-fade-in'); // Ensure no pre-animation
        }
    });

    // Handle "Load More" click
    // Handle "Load More" click
    if(loadMoreBtn) {
        loadMoreBtn.addEventListener('click', function() {
            const currentlyHidden = allReviews.filter(r => r.classList.contains('hidden'));
            const nextBatch = currentlyHidden.slice(0, itemsPerLoad);
    
            nextBatch.forEach(review => {
                review.classList.remove('hidden');
                // Add a small delay for stagger effect or just fade in
                review.classList.add('animate-fade-in');
            });
    
            visibleCount += itemsPerLoad;
    
            // Check if we reached the end
            if (visibleCount >= allReviews.length) {
                loadMoreBtn.innerText = "No More Reviews";
                loadMoreBtn.disabled = true;
                // Optional: Hide the button completely after a delay or immediately
                // loadMoreBtn.style.display = 'none'; 
            }
        });
    }

    // Rating Logic
    const ratingStars = document.querySelectorAll('#rating-stars span');
    const ratingInput = document.getElementById('rating-input');
    let currentRating = 5;

    function highlightStars(count) {
        ratingStars.forEach(star => {
            const value = parseInt(star.getAttribute('data-value'));
            if (value <= count) {
                star.classList.add('text-primary');
                star.classList.remove('text-border-neutral');
            } else {
                star.classList.remove('text-primary');
                star.classList.add('text-border-neutral');
            }
        });
    }

    // Initial highlight
    highlightStars(currentRating);

    if(ratingStars.length > 0) {
        ratingStars.forEach(star => {
            star.addEventListener('mouseover', function() {
                const value = parseInt(this.getAttribute('data-value'));
                highlightStars(value);
            });

            star.addEventListener('mouseout', function() {
                highlightStars(currentRating);
            });

            star.addEventListener('click', function() {
                currentRating = parseInt(this.getAttribute('data-value'));
                if(ratingInput) ratingInput.value = currentRating;
                highlightStars(currentRating);
            });
        });
    }

    const reviewForm = document.querySelector('form');
    // We removed the event.preventDefault() so the PHP POST can handle it.
    // If you want AJAX, you can implement it here, but for simplicity, we use standard POST.
});
</script>
<?php include '../includes/user_footer.php'; ?>
