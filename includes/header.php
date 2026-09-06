<?php
$page_title = $page_title ?? 'MediVault';
$extra_head = $extra_head ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f3f4f6; overflow-x: hidden; }
        .bg-arogga { background-color: #0d9488; } 
        .text-arogga { color: #0d9488; }
        .border-arogga { border-color: #0d9488; }
        .hover-arogga:hover { background-color: #0f766e; }
        
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        .med-card { animation: fadeInUp 0.5s ease backwards; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .med-card:hover { transform: translateY(-5px); box-shadow: 0 15px 30px -5px rgba(0,0,0,0.15); }

        #cartDrawer { transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1); z-index: 100; }
        .drawer-open { transform: translateX(0) !important; }
        .drawer-overlay { transition: opacity 0.3s ease; }

        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-thumb { background: #0d9488; border-radius: 10px; }

        .fade-in { animation: fadeIn 0.5s ease-in-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    </style>
    <?= $extra_head ?>
</head>
<body class="<?= htmlspecialchars($body_class ?? 'bg-gray-50 flex flex-col min-h-screen w-full') ?>">
