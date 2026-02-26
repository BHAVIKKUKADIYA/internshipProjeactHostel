<?php
require_once '../config/config.php';
require_once '../actions/reservation/reservation_actions.php';

$res_id = $_GET['id'] ?? null;

if (!$res_id) {
    die("Reservation ID is required.");
}

// Fetch reservation details
$stmt = $pdo->prepare("SELECT * FROM reservations WHERE id = ?");
$stmt->execute([$res_id]);
$res = $stmt->fetch();

if (!$res) {
    die("Reservation not found.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservation Receipt #<?= htmlspecialchars($res['id']) ?> | LUXE</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght@700;900&display=swap">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#b76e79',
                        charcoal: '#2b2b2b',
                        ivory: '#fdfbf9',
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
        @media print {
            .no-print { display: none; }
            body { background: white; }
            .receipt-card { border: none; shadow: none; }
        }
    </style>
</head>
<body class="bg-ivory text-charcoal min-h-screen p-4 sm:p-8 font-sans">

    <div class="max-w-2xl mx-auto">
        <!-- Back Link -->
        <div class="mb-8 no-print">
            <a href="table_booking.php" class="inline-flex items-center text-sm font-bold text-primary hover:gap-2 transition-all">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Bookings
            </a>
        </div>

        <!-- Receipt Card -->
        <div class="bg-white rounded-sm border border-neutral-200 shadow-xl overflow-hidden receipt-card">
            <!-- Header -->
            <div class="p-8 border-b border-neutral-100 text-center">
                <h1 class="serif-heading text-4xl mb-2">LUXE</h1>
                <p class="text-[10px] uppercase tracking-[0.3em] font-bold text-primary">Reservation Receipt</p>
            </div>

            <!-- Details -->
            <div class="p-8 space-y-8">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-[10px] uppercase tracking-widest text-neutral-400 mb-1">Guest Name</p>
                        <p class="text-xl font-bold"><?= htmlspecialchars($res['guest_name']) ?></p>
                    </div>
                    <div class="text-right">
                        <p class="text-[10px] uppercase tracking-widest text-neutral-400 mb-1">Date Issued</p>
                        <p class="text-xs font-medium"><?= date('F j, Y') ?></p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-8 py-8 border-y border-neutral-50">
                    <div>
                        <p class="text-[10px] uppercase tracking-widest text-neutral-400 mb-2">Reservation ID</p>
                        <p class="font-bold">#KK-<?= date('Y', strtotime($res['reservation_date'])) ?>-<?= str_pad($res['id'], 4, '0', STR_PAD_LEFT) ?></p>
                    </div>
                    <div>
                        <p class="text-[10px] uppercase tracking-widest text-neutral-400 mb-2">Status</p>
                        <span class="px-3 py-1 bg-green-50 text-green-700 text-[10px] uppercase font-bold tracking-widest rounded-full"><?= htmlspecialchars($res['status']) ?></span>
                    </div>
                    <div>
                        <p class="text-[10px] uppercase tracking-widest text-neutral-400 mb-2">Booking Date</p>
                        <p class="text-sm font-medium"><?= date("l, M j, Y", strtotime($res['reservation_date'])) ?></p>
                    </div>
                    <div>
                        <p class="text-[10px] uppercase tracking-widest text-neutral-400 mb-2">Arrival Time</p>
                        <p class="text-sm font-medium"><?= htmlspecialchars($res['reservation_time']) ?></p>
                    </div>
                    <div>
                        <p class="text-[10px] uppercase tracking-widest text-neutral-400 mb-2">Party Size</p>
                        <p class="text-sm font-medium"><?= htmlspecialchars($res['guest_count']) ?> People</p>
                    </div>
                    <div>
                        <p class="text-[10px] uppercase tracking-widest text-neutral-400 mb-2">Contact Details</p>
                        <p class="text-[11px] font-medium leading-relaxed">
                            <?= htmlspecialchars($res['email']) ?><br>
                            <?= htmlspecialchars($res['phone']) ?>
                        </p>
                    </div>
                </div>

                <!-- Footer Note -->
                <div class="text-center pt-8">
                    <p class="text-xs italic text-neutral-400 mb-8">Thank you for choosing LUXE. We look forward to serving you.</p>
                    
                    <!-- Print Button -->
                    <button onclick="window.print()" class="no-print inline-flex items-center gap-2 px-8 py-3 bg-charcoal text-white rounded-full text-xs font-bold uppercase tracking-widest hover:bg-primary transition-all shadow-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Print Receipt
                    </button>
                </div>
            </div>
        </div>

        <p class="text-center mt-8 text-[10px] uppercase tracking-widest font-black text-neutral-300">LUXE ROSE EDITION • © <?= date('Y') ?></p>
    </div>

</body>
</html>
