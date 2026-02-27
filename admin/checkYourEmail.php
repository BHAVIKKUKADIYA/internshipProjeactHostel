<?php 
require_once '../config/config.php';
require_once '../includes/functions.php';
?>
<!DOCTYPE html>
<html lang="en"><head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check Your Email | LUXE Admin</title>
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
<body class="flex flex-col min-h-screen font-sans text-luxe-dark bg-gradient-to-br from-[#f4efec] to-[#e8dfdb]">
<!-- BEGIN: MainContent -->
<main class="flex-grow flex items-center justify-center p-6 relative overflow-hidden">
<!-- Background Decoration: Subtle restaurant icon -->
<div class="absolute right-[-5%] top-1/4 opacity-[0.03] pointer-events-none hidden lg:block">
<span class="material-symbols-outlined text-[400px] text-primary">restaurant</span>
</div>
<!-- BEGIN: SuccessCard -->
<section class="w-full max-w-md bg-white rounded-3xl p-10 luxe-card text-center z-10" data-purpose="success-message-container">
<!-- Icon Wrapper -->
<div class="mb-8 flex justify-center">
<div class="w-20 h-20 bg-primary/10 rounded-full flex items-center justify-center" data-purpose="success-icon">
<svg class="h-10 w-10 text-primary" fill="none" stroke="currentColor" stroke-width="2" viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
<path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round"></path>
</svg>
</div>
</div>
<!-- Heading -->
<h1 class="font-serif text-3xl mb-4 text-luxe-dark">Check Your Email</h1>
<!-- Subtext -->
<p class="text-luxe-grey-text text-sm leading-relaxed mb-10 px-4">
        A password reset link has been sent to your email address. Please follow the instructions to reset your password.
      </p>
<!-- Action Button -->
<div class="mb-8">
<a class="inline-block w-full bg-primary hover:bg-primary-hover text-white font-medium py-3.5 rounded-xl transition-colors duration-200 shadow-md" href="login.php">
          Back to Login
        </a>
</div>
<!-- Secondary Info -->
<p class="text-xs text-luxe-grey-text leading-relaxed">
        Didn't receive the email? <br class="sm:hidden"/>
        Check your <span class="text-primary cursor-pointer hover:underline">spam folder</span> or 
        <a class="text-primary font-medium hover:underline" href="#">try again</a>.
      </p>
</section>
<!-- END: SuccessCard -->
</main>
<!-- END: MainContent -->
<!-- BEGIN: Footer -->
<footer class="py-10 text-center" data-purpose="page-footer">
<div class="flex justify-center space-x-6 text-[10px] tracking-widest uppercase text-luxe-grey-text mb-4 font-medium">
<a class="hover:text-primary transition-colors" href="#">Support</a>
<span>•</span>
<a class="hover:text-primary transition-colors" href="#">Privacy Policy</a>
<span>•</span>
<a class="hover:text-primary transition-colors" href="#">Terms</a>
</div>
<p class="text-[10px] tracking-[0.15em] uppercase text-luxe-grey-text opacity-70">
      © 2024 LUXE HOSPITALITY GROUP
    </p>
</footer>
<!-- END: Footer -->
</body></html>
