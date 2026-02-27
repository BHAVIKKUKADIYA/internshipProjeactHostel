<?php 
require_once '../config/config.php';
require_once '../includes/functions.php';
?>
<!DOCTYPE html>
<html lang="en"><head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set New Password | LUXE Admin</title>
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
<body class="bg-gradient-to-br from-[#f4efec] to-[#e8dfdb] text-luxe-dark min-h-screen flex items-center justify-center p-6">
<!-- BEGIN: New Password Card -->
<main class="w-full max-w-md" data-purpose="auth-container">
<div class="bg-white rounded-3xl shadow-xl shadow-primary/10 p-8 md:p-12">
<!-- BEGIN: Header Section -->
<header class="text-center mb-10" data-purpose="card-header">
<!-- LUXE Logo Placeholder -->
<div class="flex justify-center mb-6">
<div class="text-3xl font-serif tracking-widest border-y border-primary py-2 px-4 text-primary">
            LUXE
          </div>
</div>
<h1 class="text-2xl font-serif font-bold mb-2 text-luxe-dark">Set New Password</h1>
<p class="text-sm text-luxe-grey-text font-light">
          Please enter a strong password to secure your account.
        </p>
</header>
<!-- END: Header Section -->
<!-- BEGIN: Password Form -->
<form action="#" class="space-y-6" data-purpose="password-reset-form" method="POST">
<!-- New Password Field -->
<div class="space-y-2">
<label class="block text-xs uppercase tracking-wider font-semibold text-luxe-grey-text" for="new-password">
            New Password
          </label>
<div class="relative">
<input class="w-full px-4 py-3 rounded-2xl border border-luxe-border focus:ring-1 focus:ring-primary focus:border-primary transition-colors duration-200 outline-none text-sm placeholder:text-gray-300" id="new-password" name="new-password" placeholder="••••••••" required="" type="password"/>
<!-- Password Visibility Toggle -->
<button class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-primary" data-purpose="password-visibility-toggle" id="togglePassword" type="button">
<svg class="h-5 w-5" fill="none" stroke="currentColor" viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
<path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path>
<path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path>
</svg>
</button>
</div>
</div>
<!-- Confirm Password Field -->
<div class="space-y-2">
<label class="block text-xs uppercase tracking-wider font-semibold text-luxe-grey-text" for="confirm-password">
            Confirm New Password
          </label>
<input class="w-full px-4 py-3 rounded-2xl border border-luxe-border focus:ring-1 focus:ring-primary focus:border-primary transition-colors duration-200 outline-none text-sm placeholder:text-gray-300" id="confirm-password" name="confirm-password" placeholder="••••••••" required="" type="password"/>
</div>
<!-- Submit Button -->
<div class="pt-4">
<button class="w-full bg-primary hover:bg-primary-hover text-white font-medium py-3.5 px-4 rounded-xl shadow-lg shadow-primary/20 hover-lift uppercase tracking-widest text-sm transition-all" type="submit">
            Update Password
          </button>
</div>
</form>
<!-- END: Password Form -->
<!-- BEGIN: Footer Navigation -->
<footer class="mt-10 text-center" data-purpose="card-footer">
<a class="text-sm text-luxe-grey-text hover:text-primary transition-colors duration-200 flex items-center justify-center gap-2 group" href="login.php">
<svg class="h-4 w-4 transition-transform group-hover:-translate-x-1" fill="none" stroke="currentColor" viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
<path d="M10 19l-7-7m0 0l7-7m-7 7h18" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path>
</svg>
          Back to Login
        </a>
</footer>
<!-- END: Footer Navigation -->
</div>
</main>
<!-- END: New Password Card -->
<script data-purpose="interactivity">
    // Simple toggle functionality for password visibility
    const toggleBtn = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('new-password');

    toggleBtn.addEventListener('click', function() {
      const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
      passwordInput.setAttribute('type', type);
      
      // Update icon visual state
      this.classList.toggle('text-primary');
    });
  </script>
</body></html>
