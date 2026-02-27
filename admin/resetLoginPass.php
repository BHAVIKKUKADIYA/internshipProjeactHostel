<?php 
require_once '../config/config.php';
require_once '../includes/functions.php';
?>
<!DOCTYPE html>
<html lang="en"><head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | LUXE Admin</title>
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
<body class="min-h-screen flex items-center justify-center p-6 bg-gradient-to-br from-[#f4efec] to-[#e8dfdb]">
<!-- BEGIN: RecoveryCard -->
<main class="w-full max-w-md" data-purpose="forgot-password-container">
<div class="bg-white rounded-3xl shadow-xl shadow-primary/10 p-8 md:p-12 overflow-hidden relative">
<!-- Decorative Rose Accent Top -->
<div class="absolute top-0 left-0 w-full h-1 bg-primary"></div>
<!-- BEGIN: HeaderSection -->
<header class="text-center mb-10">
<!-- Brand Logo Placeholder -->
<div class="flex justify-center mb-6">
<div class="w-16 h-16 rounded-full bg-primary/10 flex items-center justify-center border border-primary/20">
<span class="serif-title text-2xl font-bold text-primary italic">L</span>
</div>
</div>
<h1 class="text-3xl font-bold text-luxe-dark mb-3">Reset Your Password</h1>
<p class="text-luxe-grey-text text-sm leading-relaxed px-4">
          Enter your admin email address and we'll send you a link to reset your password.
        </p>
</header>
<!-- END: HeaderSection -->
<!-- BEGIN: PasswordResetForm -->
<form action="#" class="space-y-6" data-purpose="recovery-form" method="POST">
<!-- Email Field Group -->
<div class="space-y-2">
<label class="block text-xs font-semibold text-luxe-grey-text uppercase tracking-widest ml-1" for="email">
            Admin Email Address
          </label>
<div class="relative">
<div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-primary/60">
<!-- Simple SVG Mail Icon -->
<svg class="h-5 w-5" fill="none" stroke="currentColor" viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
<path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"></path>
</svg>
</div>
<input class="block w-full pl-11 pr-4 py-3.5 bg-luxe-beige/30 border border-luxe-border rounded-xl focus:ring-1 focus:ring-primary focus:border-primary transition-all placeholder:text-gray-400 text-luxe-dark" id="email" name="email" placeholder="admin@luxe-panel.com" required="" type="email"/>
</div>
</div>
<!-- Submit Button -->
<div class="pt-2">
<button class="hover-lift w-full bg-primary text-white font-medium py-4 px-6 rounded-full shadow-lg shadow-primary/20 transition-all duration-300 flex items-center justify-center gap-2 group" type="submit">
<span>Send Reset Link</span>
<svg class="h-4 w-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
<path d="M14 5l7 7m0 0l-7 7m7-7H3" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path>
</svg>
</button>
</div>
</form>
<!-- END: PasswordResetForm -->
<!-- BEGIN: FooterLinks -->
<footer class="mt-10 text-center border-t border-gray-50 pt-8">
<a class="inline-flex items-center gap-2 text-sm font-medium text-primary hover:text-primary-hover transition-colors duration-200" href="login.php">
<svg class="h-4 w-4" fill="none" stroke="currentColor" viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
<path d="M10 19l-7-7m0 0l7-7m-7 7h18" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path>
</svg>
          Back to Login
        </a>
</footer>
<!-- END: FooterLinks -->
</div>
<!-- Small footer copyright for admin panel -->
<p class="text-center text-gray-400 text-xs mt-8 tracking-widest uppercase">
       © 2024 LUXE Admin Portfolio. Rose Edition.
    </p>
</main>
<!-- END: RecoveryCard -->
</body></html>
