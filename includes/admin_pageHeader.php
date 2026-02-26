<?php if (isset($breadcrumb) && isset($title)): ?>
<p class="text-[10px] font-bold text-primary uppercase tracking-[0.2em] mb-2"><?php echo htmlspecialchars($breadcrumb); ?></p>
<h2 class="font-serif text-3xl font-bold tracking-tight text-[#2b2b2b]"><?php echo htmlspecialchars($title); ?></h2>
<?php endif; ?>
