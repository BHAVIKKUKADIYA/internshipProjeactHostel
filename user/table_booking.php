<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/config.php';
require_once '../includes/functions.php';
require_once '../actions/reservation/reservation_actions.php';

// Check if we are viewing a specific reservation summary or editing one
$res_id = $_GET['id'] ?? $_GET['edit_id'] ?? null;
$edit_mode = isset($_GET['edit_id']);
$view_summary = isset($_GET['id']) && !$edit_mode;
$reservation_data = null;

if ($res_id) {
    $reservation_data = get_reservation_by_id($pdo, $res_id);
}

// Contact details for history
$email = $_SESSION['user_email'] ?? '';
$phone = $_SESSION['user_phone'] ?? '';

// Variables for summary display
$success = false;
$summary_name = '';
$summary_email = '';
$summary_phone = '';
$guests = '';
$date = '';
$time = '';
$reservation_id = '';

if ($view_summary && $reservation_data) {
    $success = true;
    $summary_name = $reservation_data['guest_name'];
    $summary_email = $reservation_data['email'];
    $summary_phone = $reservation_data['phone'];
    
    // Sync session if not set or different
    if (empty($_SESSION['user_email'])) $_SESSION['user_email'] = $summary_email;
    if (empty($_SESSION['user_phone'])) $_SESSION['user_phone'] = $summary_phone;
    
    // Use these for history section too if not already set
    if (empty($email)) $email = $summary_email;
    if (empty($phone)) $phone = $summary_phone;
    
    $guests = $reservation_data['guest_count'];
    $date = date("F j, Y", strtotime($reservation_data['reservation_date']));
    $time = date("h:i A", strtotime($reservation_data['reservation_time']));
    $reservation_id = "KK-" . date("Y", strtotime($reservation_data['reservation_date'])) . "-" . str_pad($reservation_data['id'], 4, '0', STR_PAD_LEFT);
}

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $is_update = !empty($_POST['reservation_id']);
    
    // Collect form data
    $formData = [
        'guest_name' => $_POST['name'] ?? 'Guest',
        'email' => $_POST['email'] ?? '',
        'phone' => $_POST['phone'] ?? '',
        'guest_count' => (int)(str_replace(' People', '', str_replace(' Person', '', $_POST['guests'] ?? '2'))),
        'reservation_date' => $_POST['date'] ?? date('Y-m-d'),
        'reservation_time' => $_POST['time'] ?? '18:00',
        'special_requests' => $_POST['requests'] ?? ''
    ];

    if ($is_update) {
        $formData['id'] = $_POST['reservation_id'];
        
        // Check if current status allows modification
        $current_res = get_reservation_by_id($pdo, $formData['id']);
        if ($current_res && $current_res['status'] === 'Confirmed') {
            die("Confirmed reservations cannot be modified.");
        }
        
        $success = update_reservation($pdo, $formData);
        if ($success) {
            // Update session so history shows correctly
            $_SESSION['user_email'] = $formData['email'];
            $_SESSION['user_phone'] = $formData['phone'];
            header("Location: table_booking.php?status=updated#user-reservations");
            exit;
        }
    } else {
        $formData['status'] = 'Confirmed';
        $success = add_reservation($pdo, $formData);
        $new_id = $pdo->lastInsertId();
        $res_id = $new_id;
    }

    if ($success && !$is_update) {
        $summary_name = $formData['guest_name'];
        $summary_email = $formData['email'];
        $summary_phone = $formData['phone'];
        $_SESSION['user_email'] = $summary_email;
        $_SESSION['user_phone'] = $summary_phone;
        $email = $summary_email;
        $phone = $summary_phone;
        $guests = $formData['guest_count'];
        $date = date("F j, Y", strtotime($formData['reservation_date']));
        $time = date("h:i A", strtotime($formData['reservation_time']));
        $reservation_id = "KK-" . date("Y") . "-" . str_pad($new_id, 4, '0', STR_PAD_LEFT);
    }

    $page_title = "Reservation Confirmed | KUKI";
    include '../includes/user_header.php';
?>

<!-- BEGIN: Hero Section -->
<section class="pt-20 pb-16 text-center" data-purpose="hero-section">
<div class="max-w-3xl mx-auto px-4">
<span class="text-primary font-medium tracking-[0.3em] text-xs uppercase mb-4 block">Reservations</span>
<h1 class="font-serif text-5xl md:text-6xl mb-6 relative inline-block">
          Book Your Table
          <span class="absolute bottom-[-15px] left-1/2 -translate-x-1/2 w-20 h-[1.5px] bg-primary"></span>
</h1>
<p class="text-soft-grey text-lg max-w-xl mx-auto mt-8 font-light leading-relaxed">
          Reserve your table for an unforgettable fine dining experience. We look forward to welcoming you to KUKI.
        </p>
</div>
</section>
<!-- END: Hero Section -->

<!-- BEGIN: Reservation Confirmation Section -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-32">
<div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-start">
<!-- Confirmation Details Column -->
<div class="bg-white p-8 md:p-12 shadow-[0_4px_20px_rgba(0,0,0,0.03)] border border-border-neutral rounded-sm" data-purpose="reservation-success-container">
<?php if ($success || $view_summary): ?>
<div class="flex flex-col items-center text-center py-6">
<!-- Checkmark Icon -->
<div class="w-16 h-16 rounded-full border border-primary flex items-center justify-center mb-8">
<span class="material-symbols-outlined text-3xl text-primary">check</span>
</div>
<!-- Success Message -->
<h2 class="serif-heading text-4xl mb-2 text-charcoal">Thank you!</h2>
<p class="text-[10px] uppercase tracking-[0.3em] font-bold text-primary mb-10">
    <?= isset($_GET['status']) && $_GET['status'] == 'updated' ? 'RESERVATION UPDATED' : 'RESERVATION CONFIRMED' ?>
</p>
<div class="max-w-md mx-auto space-y-4 mb-12">
<p class="text-soft-grey text-lg leading-relaxed">Your table has been successfully reserved. We are preparing for your arrival.</p>
<p class="text-soft-grey text-sm italic font-light">A confirmation email has been sent to <?php echo htmlspecialchars($summary_email); ?> with all the details.</p>
</div>
<?php else: ?>
<div class="flex flex-col items-center text-center py-6">
<!-- Error Icon -->
<div class="w-16 h-16 rounded-full border border-red-500 flex items-center justify-center mb-8">
<span class="material-symbols-outlined text-3xl text-red-500">error</span>
</div>
<!-- Error Message -->
<h2 class="serif-heading text-4xl mb-2 text-charcoal">Oops!</h2>
<p class="text-[10px] uppercase tracking-[0.3em] font-bold text-red-500 mb-10">RESERVATION FAILED</p>
<div class="max-w-md mx-auto space-y-4 mb-12">
<p class="text-soft-grey text-lg leading-relaxed">We encountered an issue while processing your reservation. Please try again later.</p>
</div>
<?php endif; ?>

<!-- Reservation Summary Section -->
<div class="w-full mb-12 text-left">
<div class="mb-6">
<h3 class="serif-heading text-xl text-charcoal mb-2">Reservation Summary</h3>
<div class="w-12 h-[1px] bg-primary"></div>
</div>
<div class="bg-background-ivory/50 p-6 rounded-sm border border-border-neutral space-y-4">
<div class="flex justify-between items-center text-sm border-b border-border-neutral/30 pb-3">
<span class="text-soft-grey uppercase tracking-wider text-[10px]">Reservation ID</span>
<span class="font-bold text-charcoal"><?php echo $reservation_id; ?></span>
</div>
<div class="flex justify-between items-center text-sm border-b border-border-neutral/30 pb-3">
<span class="text-soft-grey uppercase tracking-wider text-[10px]">Full Name</span>
<span class="font-bold text-charcoal"><?php echo htmlspecialchars($summary_name); ?></span>
</div>
<div class="flex justify-between items-center text-sm border-b border-border-neutral/30 pb-3">
<span class="text-soft-grey uppercase tracking-wider text-[10px]">Date</span>
<span class="font-bold text-charcoal"><?php echo $date; ?></span>
</div>
<div class="flex justify-between items-center text-sm border-b border-border-neutral/30 pb-3">
<span class="text-soft-grey uppercase tracking-wider text-[10px]">Time</span>
<span class="font-bold text-charcoal"><?php echo htmlspecialchars($time); ?></span>
</div>
<div class="flex justify-between items-center text-sm border-b border-border-neutral/30 pb-3">
<span class="text-soft-grey uppercase tracking-wider text-[10px]">Number of Guests</span>
<span class="font-bold text-charcoal"><?php echo htmlspecialchars($guests); ?></span>
</div>
<div class="flex justify-between items-center text-sm border-b border-border-neutral/30 pb-3">
<span class="text-soft-grey uppercase tracking-wider text-[10px]">Email Address</span>
<span class="font-bold text-charcoal"><?php echo htmlspecialchars($summary_email); ?></span>
</div>
<div class="flex justify-between items-center text-sm">
<span class="text-soft-grey uppercase tracking-wider text-[10px]">Contact Number</span>
<span class="font-bold text-charcoal"><?php echo htmlspecialchars($summary_phone); ?></span>
</div>
</div>
</div>
<div class="w-full h-[1px] bg-border-neutral mb-12"></div>
<!-- Need to make changes section -->
<div class="flex justify-center">
    <a href="#user-reservations" class="px-10 py-3 bg-charcoal text-white text-[10px] uppercase font-bold tracking-[0.2em] transition-soft hover:bg-primary rounded-sm text-center">
      VIEW HISTORY
    </a>
</div>
</div>
</div>
<!-- Image Column -->
<div class="h-full" data-purpose="reservation-visual-context">
<div class="sticky top-32">
<img alt="Luxury restaurant dining area" class="w-full h-auto object-cover rounded-sm shadow-xl aspect-[4/5] img-hover-scale transition-soft" src="https://lh3.googleusercontent.com/aida-public/AB6AXuCVKHtcw5X1cptWKGmP-u9YY1DYDvKCjkNvsVPMjc9uNMnCN6M9tywezTdIdPPIbRdLNgrGj73VwNuMSSkHQslIRfKEU6iJggG9mYV_w1Ls3MaXMLYqdn2npmhq4Ve63-Ae4hMoAT02kB4h_7jJCOvCnnp9rJuCQx3uHi7YhmFAeCOgP5QUmjsqyNuJnLwfcLVuBgzbzDQOj1fAGzPWayKSf5b0Uh3CbOeyRka_zmBN7IXQZKpjWyf2xaF15dU0qdGwLcyj9L5RrqM"/>
<!-- Subtle decorative element -->
<div class="mt-8 border-l-2 border-primary pl-6 bg-white/50 p-6 rounded-r-sm" style="border-color: #B76E79;">
<p class="font-serif italic text-xl text-charcoal">"A dining experience that transcends the ordinary, where every detail is meticulously crafted for your pleasure."</p>
<p class="mt-2 text-xs uppercase tracking-widest text-primary font-bold">— Executive Chef</p>
</div>
</div>
</div>
</div>
</section>
<!-- END: Reservation Confirmation Section -->
<?php
} else {
    // If not a POST request, show the form
    $page_title = "Book Your Table | KUKI";
    include '../includes/user_header.php';
    $active_slots = get_active_slots($pdo);
    $today = date('Y-m-d');
?>

<!-- BEGIN: Hero Section -->
<section class="pt-20 pb-16 text-center" data-purpose="hero-section">
<div class="max-w-3xl mx-auto px-4">
<span class="text-primary font-medium tracking-[0.3em] text-xs uppercase mb-4 block">Reservations</span>
<h1 class="font-serif text-5xl md:text-6xl mb-6 relative inline-block">
          Book Your Table
          <span class="absolute bottom-[-15px] left-1/2 -translate-x-1/2 w-20 h-[1.5px] bg-primary"></span>
</h1>
<p class="text-soft-grey text-lg max-w-xl mx-auto mt-8 font-light leading-relaxed">
          Reserve your table for an unforgettable fine dining experience. We look forward to welcoming you to KUKI.
        </p>
</div>
</section>
<!-- END: Hero Section -->
<!-- BEGIN: Reservation Main Section -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-32">
<div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-start">
<!-- Reservation Form Column -->
<div class="bg-white p-8 md:p-12 shadow-[0_4px_20px_rgba(0,0,0,0.03)] border border-border-neutral rounded-sm" data-purpose="reservation-form-container">
<form action="table_booking.php" method="POST" class="space-y-6">
    <?php if ($edit_mode): ?>
        <input type="hidden" name="reservation_id" value="<?= $res_id ?>">
    <?php endif; ?>
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
<!-- Full Name -->
<div class="flex flex-col">
<label class="text-xs uppercase tracking-widest text-charcoal font-semibold mb-2" for="name">Full Name</label>
<input class="border-border-neutral rounded-sm p-3 text-sm transition-soft gold-focus bg-background-ivory/30" id="name" name="name" placeholder="John Doe" required="" type="text" value="<?= htmlspecialchars($reservation_data['guest_name'] ?? '') ?>"/>
</div>
<!-- Email Address -->
<div class="flex flex-col">
<label class="text-xs uppercase tracking-widest text-charcoal font-semibold mb-2" for="email">Email Address</label>
<input class="border-border-neutral rounded-sm p-3 text-sm transition-soft gold-focus bg-background-ivory/30" id="email" name="email" placeholder="john@example.com" required="" type="email" value="<?= htmlspecialchars($reservation_data['email'] ?? '') ?>"/>
</div>
</div>
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
<!-- Phone Number -->
<div class="flex flex-col">
<label class="text-xs uppercase tracking-widest text-charcoal font-semibold mb-2" for="phone">Phone Number</label>
<input class="border-border-neutral rounded-sm p-3 text-sm transition-soft gold-focus bg-background-ivory/30" id="phone" name="phone" placeholder="+1 (234) 567-890" required="" type="tel" value="<?= htmlspecialchars($reservation_data['phone'] ?? '') ?>"/>
</div>
<!-- Number of Guests -->
<div class="flex flex-col">
<label class="text-xs uppercase tracking-widest text-charcoal font-semibold mb-2" for="date">Preferred Date</label>
<input class="border-border-neutral rounded-sm p-3 text-sm transition-soft gold-focus bg-background-ivory/30" id="date" name="date" required="" type="date" min="<?= $today ?>" value="<?= $reservation_data['reservation_date'] ?? $today ?>" onchange="checkAvailability()"/>
</div>
<div class="flex flex-col">
<label class="text-xs uppercase tracking-widest text-charcoal font-semibold mb-2" for="guests">Guests</label>
<select class="border-border-neutral rounded-sm p-3 text-sm transition-soft gold-focus bg-background-ivory/30" id="guests" name="guests" onchange="checkAvailability()">
    <?php 
    $current_guests = (int)($reservation_data['guest_count'] ?? 2);
    $options = [1 => '1 Person', 2 => '2 People', 3 => '3 People', 4 => '4 People', 5 => '5+ People'];
    foreach ($options as $val => $label): ?>
        <option value="<?= $label ?>" <?= $current_guests == $val || ($val == 5 && $current_guests >= 5) ? 'selected' : '' ?>><?= $label ?></option>
    <?php endforeach; ?>
</select>
</div>
<!-- Time Section (GSRTC Style) -->
<div class="flex flex-col md:col-span-2">
    <label class="text-xs uppercase tracking-widest text-charcoal font-semibold mb-4 flex items-center justify-between">
        Preferred Time
        <span class="text-[10px] text-primary bg-primary/5 px-2 py-0.5 rounded-full font-bold">Select a Slot</span>
    </label>
    
    <!-- Hidden input for time selection -->
    <input type="hidden" id="time" name="time" value="<?= htmlspecialchars($reservation_data['reservation_time'] ?? '') ?>" required>
    
    <!-- Slot Grid Container -->
    <div id="slot-grid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
        <!-- Slots will be injected here via JS -->
        <div class="col-span-full py-8 text-center bg-background-ivory/20 rounded-sm border border-dashed border-border-neutral">
            <div class="animate-pulse flex flex-col items-center">
                <div class="h-2 w-24 bg-border-neutral rounded mb-2"></div>
                <div class="h-2 w-32 bg-border-neutral rounded"></div>
            </div>
            <p class="text-[10px] uppercase tracking-widest text-soft-grey mt-4">Loading available times...</p>
        </div>
    </div>
    
    <div id="availability-feedback" class="mt-4"></div>
</div>
</div>
<!-- Special Requests -->
<div class="flex flex-col">
<label class="text-xs uppercase tracking-widest text-charcoal font-semibold mb-2" for="requests">Special Requests (Optional)</label>
<textarea class="border-border-neutral rounded-sm p-3 text-sm transition-soft gold-focus resize-none bg-background-ivory/30" id="requests" name="requests" placeholder="Allergies, anniversaries, or special seating preferences..." rows="4"><?= htmlspecialchars($reservation_data['special_requests'] ?? '') ?></textarea>
</div>
<!-- Submit Button -->
<div class="pt-4">
<button id="submit-reservation" class="w-full bg-primary hover:bg-primary-hover text-white py-4 px-8 rounded-sm text-sm font-bold uppercase tracking-[0.2em] transition-soft btn-hover-lift shadow-md" type="submit">
                <?= $edit_mode ? 'Update Reservation' : 'Reserve Now' ?>
              </button>
</div>
</form>
</div>
<!-- Image Column -->
<div class="h-full" data-purpose="reservation-visual-context">
<div class="sticky top-32">
<img alt="Luxury restaurant dining area" class="w-full h-auto object-cover rounded-sm shadow-xl aspect-[4/5] img-hover-scale transition-soft" src="https://lh3.googleusercontent.com/aida-public/AB6AXuCVKHtcw5X1cptWKGmP-u9YY1DYDvKCjkNvsVPMjc9uNMnCN6M9tywezTdIdPPIbRdLNgrGj73VwNuMSSkHQslIRfKEU6iJggG9mYV_w1Ls3MaXMLYqdn2npmhq4Ve63-Ae4hMoAT02kB4h_7jJCOvCnnp9rJuCQx3uHi7YhmFAeCOgP5QUmjsqyNuJnLwfcLVuBgzbzDQOj1fAGzPWayKSf5b0Uh3CbOeyRka_zmBN7IXQZKpjWyf2xaF15dU0qdGwLcyj9L5RrqM"/>
<!-- Subtle decorative element -->
<div class="mt-8 border-l-2 border-primary pl-6 bg-white/50 p-6 rounded-r-sm" style="border-color: #B76E79;">
<p class="font-serif italic text-xl text-charcoal">"A dining experience that transcends the ordinary, where every detail is meticulously crafted for your pleasure."</p>
<p class="mt-2 text-xs uppercase tracking-widest text-primary font-bold">— Executive Chef</p>
</div>
</div>
</div>
</div>
</section>
<!-- END: Reservation Main Section -->

<script>
    async function checkAvailability() {
        const dateInput = document.getElementById('date');
        const guestsInput = document.getElementById('guests');
        const timeInput = document.getElementById('time');
        const slotGrid = document.getElementById('slot-grid');
        const feedback = document.getElementById('availability-feedback');
        const submitBtn = document.getElementById('submit-reservation');
        const resId = new URLSearchParams(window.location.search).get('edit_id') || '';

        if (!dateInput.value) return;
        
        // Extract numeric guest count
        const guestsText = guestsInput.value || '2 People';
        const numGuests = parseInt(guestsText) || 2;

        try {
            const response = await fetch(`../actions/reservation/get_all_availabilities.php?date=${dateInput.value}&guests=${numGuests}&exclude_id=${resId}`);
            const result = await response.json();

            if (result.success) {
                renderSlots(result.slots);
            }
        } catch (error) {
            console.error('Error fetching availabilities:', error);
            slotGrid.innerHTML = `<p class='text-red-500 text-[10px] col-span-full py-4 uppercase font-bold text-center'>Failed to load time slots.</p>`;
        }
    }

    function renderSlots(slots) {
        const slotGrid = document.getElementById('slot-grid');
        const timeInput = document.getElementById('time');
        const selectedTime = timeInput.value;
        const feedback = document.getElementById('availability-feedback');
        const submitBtn = document.getElementById('submit-reservation');
        const selectedDate = document.getElementById('date').value;

        // Current time for past-slot check
        const now = new Date();
        const today = now.toISOString().split('T')[0];
        const currentHour = now.getHours();
        const currentMinute = now.getMinutes();

        if (slots.length === 0) {
            slotGrid.innerHTML = `<p class='text-soft-grey text-[10px] col-span-full py-8 uppercase font-bold text-center border border-dashed rounded-sm'>No slots available for this date.</p>`;
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
            return;
        }

        slotGrid.innerHTML = '';

        slots.forEach(slot => {
            const isFull = !slot.assignment_possible || slot.remaining <= 0;
            
            // Check if slot is in the past
            let isPast = false;
            if (selectedDate === today) {
                // Parse slot time (e.g., "05:00 PM")
                const [time, modifier] = slot.time.split(' ');
                let [hours, minutes] = time.split(':').map(Number);
                if (modifier === 'PM' && hours < 12) hours += 12;
                if (modifier === 'AM' && hours === 12) hours = 0;
                
                if (hours < currentHour || (hours === currentHour && minutes <= currentMinute)) {
                    isPast = true;
                }
            }

            const isUnavailable = isFull || isPast;
            const isSelected = selectedTime === slot.time && !isUnavailable;
            
            // Availability Label Logic
            let badgeClass = 'text-green-600';
            let label = `${slot.remaining} Tables Available`;
            
            if (isPast) {
                badgeClass = 'text-slate-400';
                label = 'UNAVAILABLE';
            } else if (isFull) {
                badgeClass = 'text-red-500';
                label = 'FULL';
            } else if (slot.percent > 85) {
                badgeClass = 'text-amber-600';
                label = 'ALMOST FULL';
            }

            const slotEl = document.createElement('div');
            slotEl.className = `
                relative p-4 rounded-sm border text-center cursor-pointer transition-all duration-300 group
                ${isUnavailable ? 'opacity-50 grayscale cursor-not-allowed bg-slate-50 border-slate-200' : 'hover:border-primary hover:-translate-y-1'}
                ${isSelected ? 'border-primary bg-primary/5 shadow-md ring-1 ring-primary/20' : 'border-border-neutral bg-white'}
            `;
            
            slotEl.innerHTML = `
                <p class="text-sm font-bold text-charcoal mb-1 ${isSelected ? 'text-primary' : ''}">${slot.time}</p>
                <span class="text-[8px] font-black uppercase tracking-widest ${isSelected ? 'text-primary' : badgeClass}">${label}</span>
                ${slot.is_peak ? '<span class="absolute -top-1.5 -right-1.5 material-symbols-outlined text-[14px] text-primary" style="font-variation-settings:\'FILL\' 1">bolt</span>' : ''}
            `;

            if (!isUnavailable) {
                slotEl.onclick = () => {
                    timeInput.value = slot.time;
                    checkAvailability(); // Re-render to update selection
                    feedback.innerHTML = `<span class='text-green-600 flex items-center gap-1 text-[10px] font-bold uppercase tracking-widest'><span class='material-symbols-outlined text-sm'>check_circle</span> Slot Selected: ${slot.time}</span>`;
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                };
            } else if (isSelected) {
                // Deselect if it became unavailable
                timeInput.value = '';
            }

            slotGrid.appendChild(slotEl);
        });

        // Validation for the overall form
        if (!timeInput.value) {
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
            if (selectedTime) {
                const wasUnavailable = slots.find(s => s.time === selectedTime && (!s.assignment_possible || s.remaining <= 0));
                if (wasUnavailable) {
                    feedback.innerHTML = `<span class='text-red-500 flex items-center gap-1 text-[10px] font-bold uppercase tracking-widest'><span class='material-symbols-outlined text-sm'>error</span> The previously selected slot is now fully booked or unavailable.</span>`;
                } else {
                    feedback.innerHTML = `<span class='text-amber-600 flex items-center gap-1 text-[10px] font-bold uppercase tracking-widest'><span class='material-symbols-outlined text-sm'>info</span> Please select an available time slot above.</span>`;
                }
            } else {
                feedback.innerHTML = `<span class='text-amber-600 flex items-center gap-1 text-[10px] font-bold uppercase tracking-widest'><span class='material-symbols-outlined text-sm'>info</span> Please select an available time slot above.</span>`;
            }
        }
    }
    
    // Initial check
    window.addEventListener('DOMContentLoaded', checkAvailability);
</script>

<?php } ?>

<!-- BEGIN: Your Reservations Section -->
<?php
// Fetch all reservations for this email or phone to show user history
$user_reservations = get_reservations_by_contact($pdo, $email, $phone);
$upcoming_res = [];
$past_res = [];
$cur_date = date('Y-m-d');
$cur_time = date('H:i:s');

foreach ($user_reservations as $res) {
    $res_date = $res['reservation_date'];
    $res_time = $res['reservation_time'];
    
    // Convert to 24h for comparison if needed
    if (preg_match('/(0[1-9]|1[0-2]):[0-5][0-9]\s?(AM|PM)/i', $res_time)) {
        $res_time = date("H:i:s", strtotime($res_time));
    }

    if ($res_date > $cur_date || ($res_date == $cur_date && $res_time >= $cur_time)) {
        $upcoming_res[] = $res;
    } else {
        $past_res[] = $res;
    }
}
?>
<section id="user-reservations" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-32 scroll-mt-32">
<?php if (isset($_GET['status']) && $_GET['status'] == 'updated'): ?>
    <div class="mb-8 p-4 bg-green-50 border border-green-100 rounded-sm text-green-700 text-sm flex items-center gap-3 shadow-sm animate-fade-in">
        <span class="material-symbols-outlined text-green-500">check_circle</span>
        <span class="font-medium">Reservation updated successfully.</span>
    </div>
<?php endif; ?>
<div class="mb-12 text-center">
<h2 class="serif-heading text-4xl mb-2 text-charcoal">Your Reservations</h2>
<div class="w-16 h-[1px] bg-primary mx-auto"></div>
</div>
<div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
<!-- Upcoming Reservations -->
<div>
<h3 class="serif-heading text-2xl mb-8 text-charcoal flex items-center gap-3">
        Upcoming Reservations
        <span class="text-[10px] bg-primary/10 text-primary px-2 py-0.5 rounded-full font-sans font-bold uppercase tracking-wider"><?= count($upcoming_res) ?> Active</span>
</h3>
<div class="space-y-6">
<?php if (empty($upcoming_res)): ?>
    <p class="text-soft-grey text-sm italic">No upcoming reservations found.</p>
<?php else: ?>
    <?php foreach ($upcoming_res as $res): ?>
    <div class="bg-white p-6 rounded-sm border border-border-neutral shadow-sm">
        <div class="flex justify-between items-start mb-6">
            <div>
                <p class="font-bold text-charcoal text-lg mb-1 leading-tight"><?= htmlspecialchars($res['guest_name']) ?></p>
                <div class="flex flex-col gap-0.5">
                    <p class="text-[10px] uppercase tracking-[0.2em] text-soft-grey"><?= htmlspecialchars($res['email']) ?></p>
                    <p class="text-[10px] uppercase tracking-[0.2em] text-soft-grey"><?= htmlspecialchars($res['phone']) ?></p>
                </div>
            </div>
            <span class="px-3 py-1 border border-primary text-primary text-[10px] uppercase font-bold tracking-widest rounded-full"><?= htmlspecialchars($res['status']) ?></span>
        </div>
        <div class="grid grid-cols-3 gap-4 mb-6">
            <div>
                <p class="text-[10px] uppercase tracking-widest text-soft-grey">Date</p>
                <p class="text-sm font-medium"><?= date("M j, Y", strtotime($res['reservation_date'])) ?></p>
            </div>
            <div>
                <p class="text-[10px] uppercase tracking-widest text-soft-grey">Time</p>
                <p class="text-sm font-medium"><?= htmlspecialchars($res['reservation_time']) ?></p>
            </div>
            <div>
                <p class="text-[10px] uppercase tracking-widest text-soft-grey">Guests</p>
                <p class="text-sm font-medium"><?= htmlspecialchars($res['guest_count']) ?> People</p>
            </div>
        </div>
        <?php if ($res['status'] === 'Confirmed'): ?>
            <div class="w-full py-3 bg-slate-50 border border-slate-200 text-slate-400 text-[10px] uppercase font-bold tracking-widest rounded-full text-center italic">
                Confirmed reservations cannot be modified.
            </div>
        <?php else: ?>
            <a href="table_booking.php?edit_id=<?= $res['id'] ?>" class="block w-full py-3 bg-white border-2 border-primary text-primary text-xs uppercase font-bold tracking-widest rounded-full transition-all duration-300 hover:bg-primary hover:text-white hover:shadow-lg hover:-translate-y-1 text-center">Modify Reservation</a>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
<?php endif; ?>
</div>
</div>
<!-- Past Reservations -->
<?php if (!empty($past_res)): ?>
<div>
<h3 class="serif-heading text-2xl mb-8 text-charcoal">Past Reservations</h3>
<div class="space-y-6">
    <?php foreach ($past_res as $res): 
        $is_cancelled = strtolower($res['status']) === 'cancelled';
    ?>
    <div class="bg-white/60 p-6 rounded-sm border border-border-neutral">
        <div class="flex justify-between items-start mb-6">
            <div>
                <p class="font-bold text-charcoal text-lg mb-1 leading-tight <?= $is_cancelled ? 'text-opacity-50' : '' ?>"><?= htmlspecialchars($res['guest_name']) ?></p>
                <div class="flex flex-col gap-0.5">
                    <p class="text-[10px] uppercase tracking-[0.2em] text-soft-grey"><?= htmlspecialchars($res['email']) ?></p>
                    <p class="text-[10px] uppercase tracking-[0.2em] text-soft-grey"><?= htmlspecialchars($res['phone']) ?></p>
                </div>
            </div>
            <span class="px-3 py-1 <?= $is_cancelled ? 'bg-red-50 text-red-400' : 'bg-green-100 text-green-700' ?> text-[10px] uppercase font-bold tracking-widest rounded-full">
                <?= $is_cancelled ? 'Cancelled' : 'Completed' ?>
            </span>
        </div>
        <div class="grid grid-cols-3 gap-4 mb-6 <?= $is_cancelled ? 'text-opacity-50' : '' ?>">
            <div>
                <p class="text-[10px] uppercase tracking-widest text-soft-grey">Date</p>
                <p class="text-sm font-medium"><?= date("M j, Y", strtotime($res['reservation_date'])) ?></p>
            </div>
            <div>
                <p class="text-[10px] uppercase tracking-widest text-soft-grey">Time</p>
                <p class="text-sm font-medium"><?= htmlspecialchars($res['reservation_time']) ?></p>
            </div>
            <div>
                <p class="text-[10px] uppercase tracking-widest text-soft-grey">Guests</p>
                <p class="text-sm font-medium"><?= htmlspecialchars($res['guest_count']) ?> People</p>
            </div>
        </div>
        <?php if ($is_cancelled): ?>
            <button class="w-full py-2 border border-charcoal/10 text-charcoal/40 text-[10px] uppercase font-bold tracking-widest" disabled>Cancelled</button>
        <?php else: ?>
            <a href="receipt.php?id=<?= $res['id'] ?>" target="_blank" class="block w-full py-2 border border-charcoal/20 text-charcoal/60 text-[10px] uppercase font-bold tracking-widest transition-soft hover:bg-charcoal hover:text-white text-center">View Receipt</a>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>
</div>
<?php endif; ?>
</div>
</section>
<!-- END: Your Reservations Section -->

<?php include '../includes/user_footer.php'; ?>
