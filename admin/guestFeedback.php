<?php 
require_once '../config/config.php';
require_once '../includes/functions.php';
?>
<!DOCTYPE html>
<html lang="en"><head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guest Feedback | LUXE Admin</title>
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
                        'background-light': '#fdfbf9',
                        'luxe-charcoal': '#2b2b2b',
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
        h1, h2, h3, h4, .serif-title { font-family: 'Playfair Display', serif; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
<!-- BEGIN: Feedback Details Modal Container -->
<div class="fixed inset-0 bg-black/20 backdrop-blur-sm flex items-center justify-center z-50 p-4" data-purpose="modal-overlay">
<!-- BEGIN: Modal Card -->
<article class="bg-white w-full max-w-2xl rounded-2xl shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-300" data-purpose="feedback-modal-card">
<!-- BEGIN: Modal Header -->
<header class="px-8 pt-8 pb-4 flex justify-between items-start">
<div class="space-y-1">
<p class="text-[10px] uppercase tracking-widest text-[#8A8A8A] font-semibold">Review Details</p>
<h1 class="text-3xl serif-title text-luxe-dark">Guest Feedback</h1>
</div>
<!-- Close Button -->
<button class="text-luxe-grey-text hover:text-luxe-dark transition-colors p-2">
<svg class="h-6 w-6" fill="none" stroke="currentColor" viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
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
<div class="h-16 w-16 rounded-full bg-primary/10 flex items-center justify-center text-primary font-serif text-xl border border-primary/10">
              MW
            </div>
<div>
<h2 class="text-xl font-semibold text-luxe-dark">Marcus Wright</h2>
<p class="text-sm text-luxe-grey-text">m.wright@luxury.co</p>
</div>
</div>
<div class="flex flex-col md:items-end gap-1">
<div class="flex gap-1 text-primary">
<!-- 5-Star Rating Display -->
<svg class="w-5 h-5 fill-current" viewbox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
<svg class="w-5 h-5 fill-current" viewbox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
<svg class="w-5 h-5 fill-current" viewbox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
<svg class="w-5 h-5 fill-current" viewbox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
<svg class="w-5 h-5 text-gray-300 fill-current" viewbox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
</div>
<p class="text-xs font-medium text-luxe-grey-text uppercase tracking-wider">OCT 25, 2023</p>
</div>
</section>
<!-- Section: Feedback Message -->
<section class="space-y-4">
<h3 class="text-xs font-bold uppercase tracking-widest text-luxe-grey-text">Review Content</h3>
<p class="text-luxe-dark leading-relaxed text-lg italic serif-title">
            "Wait time was longer than expected despite a reservation, but the Truffle Risotto was an absolute masterpiece. The ambiance in the evening is unmatched. I would suggest slightly more attentive service near the bar area."
          </p>
</section>
<!-- Section: Metadata Grid -->
<section class="grid grid-cols-2 md:grid-cols-4 gap-6 pt-4">
<div data-purpose="meta-item">
<p class="text-[10px] font-bold uppercase tracking-widest text-luxe-grey-text mb-1">Visit Date</p>
<p class="text-sm font-semibold text-luxe-dark">Oct 24, 2023</p>
</div>
<div data-purpose="meta-item">
<p class="text-[10px] font-bold uppercase tracking-widest text-luxe-grey-text mb-1">Table No.</p>
<p class="text-sm font-semibold text-luxe-dark">14 (Window Side)</p>
</div>
<div data-purpose="meta-item">
<p class="text-[10px] font-bold uppercase tracking-widest text-luxe-grey-text mb-1">Service Staff</p>
<p class="text-sm font-semibold text-luxe-dark">Julian S.</p>
</div>
<div data-purpose="meta-item">
<p class="text-[10px] font-bold uppercase tracking-widest text-luxe-grey-text mb-1">Status</p>
<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
              Pending
            </span>
</div>
</section>
</main>
<!-- END: Modal Content -->
<!-- BEGIN: Modal Footer / Actions -->
<footer class="px-8 py-8 bg-luxe-beige/50 border-t border-luxe-border flex flex-col sm:flex-row justify-end items-center gap-4">
<button class="w-full sm:w-auto px-8 py-3 rounded-lg border-2 border-primary text-primary font-semibold hover:bg-primary hover:text-white transition-all duration-300 active:scale-95">
          Reject Review
        </button>
<button class="w-full sm:w-auto px-8 py-3 rounded-lg bg-primary text-white font-semibold hover:bg-primary-hover shadow-lg shadow-primary/20 transition-all duration-300 active:scale-95">
          Approve Review
        </button>
</footer>
<!-- END: Modal Footer / Actions -->
</article>
<!-- END: Modal Card -->
</div>
<!-- END: Feedback Details Modal Container -->
</body></html>


