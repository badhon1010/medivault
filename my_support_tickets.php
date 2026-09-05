<?php
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$user_id = (int) $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM support_tickets WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$tickets = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Support Tickets | MediVault</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f3f4f6; }
        .bg-arogga { background-color: #0d9488; }
        .text-arogga { color: #0d9488; }
    </style>
</head>
<body class="min-h-screen">
    <nav class="bg-white shadow-md sticky top-0 z-50">
        <div class="container mx-auto px-4 h-20 flex items-center justify-between">
            <a href="dashboard.php" class="text-2xl font-black text-slate-800">Medi<span class="text-arogga">Vault</span></a>
            <a href="support_ticket.php" class="bg-arogga text-white px-5 py-3 rounded-xl font-bold text-xs uppercase tracking-wider shadow-md">
                <i class="fas fa-plus mr-2"></i>New Ticket
            </a>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-10 max-w-5xl">
        <div class="flex items-end justify-between mb-8 border-b border-gray-200 pb-4">
            <div>
                <h1 class="text-3xl font-black text-slate-800 uppercase tracking-tight">My Support Tickets</h1>
                <p class="text-sm text-slate-500 mt-1">Monitor the status of issues you reported to our team.</p>
            </div>
        </div>

        <?php if (empty($tickets)): ?>
            <div class="bg-white rounded-[30px] shadow-sm border border-dashed border-slate-200 p-10 text-center">
                <div class="w-16 h-16 bg-violet-100 text-violet-600 rounded-full flex items-center justify-center text-2xl mx-auto mb-4">
                    <i class="fas fa-headset"></i>
                </div>
                <h2 class="text-xl font-black text-slate-800">No tickets yet</h2>
                <p class="text-sm text-slate-500 mt-2">Raise a support request and we will follow up as soon as possible.</p>
                <a href="support_ticket.php" class="inline-block mt-6 bg-slate-900 text-white px-6 py-3 rounded-xl font-black uppercase text-xs tracking-wider">Create Ticket</a>
            </div>
        <?php else: ?>
            <div class="space-y-5">
                <?php foreach ($tickets as $ticket): ?>
                    <?php
                        $statusColors = [
                            'Open' => 'bg-sky-100 text-sky-700',
                            'In Progress' => 'bg-blue-100 text-blue-700',
                            'Resolved' => 'bg-green-100 text-green-700',
                            'Closed' => 'bg-gray-100 text-gray-700',
                        ];
                        $priorityColors = [
                            'Low' => 'bg-green-100 text-green-700',
                            'Medium' => 'bg-amber-100 text-amber-700',
                            'High' => 'bg-red-100 text-red-700',
                        ];
                    ?>
                    <div class="bg-white rounded-[28px] shadow-sm border border-slate-100 p-6">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                            <div>
                                <div class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">Ticket #<?= (int) $ticket['ticket_id'] ?></div>
                                <h3 class="text-xl font-black text-slate-800"><?= htmlspecialchars($ticket['subject']) ?></h3>
                                <p class="text-sm text-slate-500 mt-1"><?= htmlspecialchars($ticket['category']) ?> • <?= date('d M Y', strtotime($ticket['created_at'])) ?></p>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="inline-flex px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider <?= $priorityColors[$ticket['priority']] ?? 'bg-gray-100 text-gray-600' ?>">
                                    <?= htmlspecialchars($ticket['priority']) ?>
                                </span>
                                <span class="inline-flex px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider <?= $statusColors[$ticket['status']] ?? 'bg-gray-100 text-gray-600' ?>">
                                    <?= htmlspecialchars($ticket['status']) ?>
                                </span>
                            </div>
                        </div>

                        <div class="mt-5 rounded-2xl bg-slate-50 border border-slate-100 p-4">
                            <div class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">Message</div>
                            <p class="text-sm text-slate-600 leading-relaxed"><?= nl2br(htmlspecialchars($ticket['message'])) ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
