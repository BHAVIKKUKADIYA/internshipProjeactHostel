<?php 
require_once '../config/config.php';
require_once '../includes/functions.php';
?>
<!DOCTYPE html>
<html lang="en"><head>
... (rest of head)
<body class="min-h-screen flex items-center justify-center p-6">
<!-- BEGIN: RecoveryCard -->
<main class="w-full max-w-md" data-purpose="forgot-password-container">
<div class="bg-white rounded-3xl shadow-xl shadow-gray-200/50 p-8 md:p-12 overflow-hidden relative">
<!-- Decorative Rose Accent Top -->
<div class="absolute top-0 left-0 w-full h-1 bg-luxe-rose"></div>
<!-- BEGIN: HeaderSection -->
<header class="text-center mb-10">
<!-- Brand Logo Placeholder -->
<div class="flex justify-center mb-6">
<div class="w-16 h-16 rounded-full bg-luxe-bg flex items-center justify-center border border-luxe-rose/20">
<span class="font-playfair text-2xl font-bold text-luxe-rose italic">L</span>
</div>
</div>
<h1 class="text-3xl font-bold text-luxe-dark mb-3">Reset Your Password</h1>
<p class="text-gray-500 text-sm leading-relaxed px-4">
          Enter your admin email address and we'll send you a link to reset your password.
        </p>
</header>
<!-- END: HeaderSection -->
<!-- BEGIN: PasswordResetForm -->
<form action="#" class="space-y-6" data-purpose="recovery-form" method="POST">
<!-- Email Field Group -->
<div class="space-y-2">
<label class="block text-xs font-semibold text-gray-500 uppercase tracking-widest ml-1" for="email">
            Admin Email Address
          </label>
<div class="relative">
<div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-luxe-rose/60">
<!-- Simple SVG Mail Icon -->
<svg class="h-5 w-5" fill="none" stroke="currentColor" viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
<path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"></path>
</svg>
</div>
<input class="block w-full pl-11 pr-4 py-3.5 bg-luxe-bg/30 border border-gray-100 rounded-xl focus:ring-1 focus:ring-luxe-rose focus:border-luxe-rose transition-all placeholder:text-gray-400 text-luxe-dark" id="email" name="email" placeholder="admin@luxe-panel.com" required="" type="email"/>
</div>
</div>
<!-- Submit Button -->
<div class="pt-2">
<button class="hover-lift w-full bg-luxe-rose text-white font-medium py-4 px-6 rounded-full shadow-md transition-all duration-300 flex items-center justify-center gap-2 group" type="submit">
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
<a class="inline-flex items-center gap-2 text-sm font-medium text-luxe-rose hover:text-luxe-rose/80 transition-colors duration-200" href="login.php">
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
      © 2023 LUXE Admin Portfolio. Rose Edition.
    </p>
</main>
<!-- END: RecoveryCard -->
</body></html>
