<?php 
require_once '../config/config.php';
require_once '../includes/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Simple redirect if already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: dashboard.php");
    exit;
}

$error = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // In a real app, verify against DB. For now, we allow the demo admin.
    if ($email === 'admin@luxe.com' && $password === 'admin123') {
        $_SESSION['admin_logged_in'] = true;
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Invalid credentials. Try admin@luxe.com / admin123";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | LUXE Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght@700;900&display=swap">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: '#b76e79',
                        'primary-hover': '#a55f69',
                        'luxe-dark': '#2b2b2b',
                        'luxe-beige': '#f4efec',
                        'background-light': '#fdfbf9',
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
        .sidebar-active { background: #b76e79; color: white; }
        .transition-custom { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        body { font-family: 'Inter', sans-serif; }
        h1, h2, h3, h4, .serif-title { font-family: 'Playfair Display', serif; }
        .animate-fade-in { animation: fadeIn 0.8s ease-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body class="bg-[#f4efec] text-luxe-dark min-h-screen flex items-center justify-center p-6 bg-gradient-to-br from-[#f4efec] to-[#e8dfdb]">
<!-- Main Container -->
<div class="w-full max-w-[480px] animate-fade-in">
<!-- Login Card -->
<div class="bg-white/80 backdrop-blur-xl shadow-[0_20px_50px_rgba(183,110,121,0.15)] rounded-[2.5rem] overflow-hidden border border-white/40 p-1">
<div class="bg-white rounded-[2.3rem] p-10 sm:p-12">
<!-- Branding & Header Section -->
<div class="text-center mb-10">
<div class="flex justify-center mb-8">
<div class="w-20 h-20 rounded-3xl bg-primary/10 flex items-center justify-center text-primary shadow-inner">
<span class="material-symbols-outlined text-4xl" style="font-variation-settings: 'FILL' 1">restaurant</span>
</div>
</div>
<h2 class="serif-title text-4xl font-black text-luxe-dark tracking-tight mb-3">LUXE Admin</h2>
<p class="text-luxe-dark/40 text-sm font-medium">Enter your credentials to manage your establishment</p>
<?php if ($error): ?>
    <div class="mt-4 p-3 bg-red-50 border border-red-100 rounded-xl">
        <p class="text-[11px] text-red-500 font-bold uppercase tracking-wider"><?= $error ?></p>
    </div>
<?php endif; ?>
</div>
<!-- Form Section -->
<form class="space-y-6" method="POST" action="">
<!-- Email Input -->
<div class="space-y-2">
<label class="block text-[11px] uppercase tracking-widest text-luxe-dark/40 font-bold ml-1">Email Address</label>
<div class="relative group">
<div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none text-luxe-dark/20 group-focus-within:text-primary transition-colors">
<span class="material-symbols-outlined text-[22px]">mail</span>
</div>
<input name="email" required class="block w-full pl-14 pr-5 py-4 bg-luxe-beige/30 border border-primary/10 rounded-2xl text-luxe-dark placeholder:text-luxe-dark/20 focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all outline-none text-sm" placeholder="admin@luxe.com" type="email"/>
</div>
</div>
<!-- Password Input -->
<div class="space-y-2">
<div class="flex justify-between items-center ml-1">
<label class="block text-[11px] uppercase tracking-widest text-luxe-dark/40 font-bold">Password</label>
<a class="text-[11px] font-bold text-primary hover:text-primary-hover transition-colors" href="#">FORGOT PASSWORD?</a>
</div>
<div class="relative group">
<div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none text-luxe-dark/20 group-focus-within:text-primary transition-colors">
<span class="material-symbols-outlined text-[22px]">lock</span>
</div>
<input name="password" required class="block w-full pl-14 pr-14 py-4 bg-luxe-beige/30 border border-primary/10 rounded-2xl text-luxe-dark placeholder:text-luxe-dark/20 focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all outline-none text-sm" placeholder="••••••••" type="password"/>
<button class="absolute inset-y-0 right-0 pr-5 flex items-center text-luxe-dark/20 hover:text-primary transition-colors" type="button">
<span class="material-symbols-outlined text-[22px]">visibility</span>
</button>
</div>
</div>
<!-- Utilities Row -->
<div class="flex items-center ml-1">
<label class="relative inline-flex items-center cursor-pointer">
<input class="sr-only peer" id="remember-me" name="remember-me" type="checkbox"/>
<div class="w-9 h-5 bg-luxe-beige rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-primary"></div>
<span class="ml-3 text-xs font-semibold text-luxe-dark/60">Remember me for 30 days</span>
</label>
</div>
<!-- Login Button -->
<button class="w-full flex justify-center py-4 px-4 rounded-2xl shadow-lg shadow-primary/25 text-sm font-black text-white bg-primary hover:bg-primary-hover hover:-translate-y-0.5 transition-all duration-300 focus:outline-none" type="submit">
                    SIGN IN TO DASHBOARD
                </button>
<!-- Footer Link -->
<div class="text-center pt-4">
<p class="text-sm text-slate-500 dark:text-slate-400">
                        Don't have an admin account? 
                        <a class="text-xs font-medium text-primary hover:underline underline-offset-4 transition-colors" href="#">Sign Up</a>
</p>
</div>
</form>
</div>
<!-- Decorative Footer -->
<div class="mt-8 flex flex-col items-center gap-4">
<div class="flex items-center gap-6 text-slate-400 dark:text-slate-500">
<a class="text-xs uppercase tracking-widest hover:text-primary transition-colors" href="#">Support</a>
<span class="h-1 w-1 rounded-full bg-slate-300"></span>
<a class="text-xs uppercase tracking-widest hover:text-primary transition-colors" href="#">Privacy Policy</a>
<span class="h-1 w-1 rounded-full bg-slate-300"></span>
<a class="text-xs uppercase tracking-widest hover:text-primary transition-colors" href="#">Terms</a>
</div>
<p class="text-[10px] uppercase tracking-[0.2em] text-slate-400/60 dark:text-slate-600">© 2024 LUXE Hospitality Group</p>
</div>
</div>
<!-- Background Decoration -->
<div class="fixed top-0 left-0 w-full h-1 bg-primary"></div>
<div class="fixed bottom-0 left-0 w-full h-64 bg-gradient-to-t from-primary/5 to-transparent -z-10"></div>
<div class="fixed top-20 right-20 opacity-10 -z-10 pointer-events-none">
<span class="material-symbols-outlined text-[300px] text-primary">local_hotel</span>
</div>
</body></html>
