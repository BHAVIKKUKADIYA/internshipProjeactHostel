<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title><?php echo $page_title ?? 'KUKI | Exquisite Dining Redefined'; ?></title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: '#B76E79',
                        'primary-hover': '#a55f69',
                        charcoal: '#2b2b2b',
                        'background-ivory': '#FAF9F8',
                        'border-neutral': '#E5E1DA',
                        'soft-grey': '#717171',
                    },
                    fontFamily: {
                        sans: ['Public Sans', 'sans-serif'],
                        serif: ['Playfair Display', 'serif'],
                    }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700;800;900&amp;family=Playfair+Display:ital,wght@0,400;0,700;1,400&amp;display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
    <link rel="stylesheet" href="style.css">
    <?php if(isset($extra_head)) echo $extra_head; ?>
</head>
<body class="text-charcoal body-text selection:bg-primary selection:text-white bg-background-ivory">
<?php include __DIR__ . '/user_navbar.php'; ?>
<main>
