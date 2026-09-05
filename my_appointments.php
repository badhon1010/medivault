<?php
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$user_id = (int) $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_appointment'])) {
    $appointment_id = (int) ($_POST['appointment_id'] ?? 0);
    if ($appointment_id > 0) {
        $stmt = $pdo->prepare("UPDATE appointments SET status = 'Cancelled' WHERE appointment_id = ? AND user_id = ?");
        $stmt->execute([$appointment_id, $user_id]);
        echo "<script>alert('Appointment cancelled successfully.'); window.location='my_appointments.php';</script>";
        exit();
    }
}

$stmt = $pdo->prepare("SELECT * FROM appointments WHERE user_id = ? ORDER BY appointment_date DESC, appointment_time DESC");
$stmt->execute([$user_id]);
$appointments = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Appointments | MediVault</title>
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
        <div class="container mx-auto px-4 h-20 flex justify-between items-center">
            <a href="dashboard.php" class="text-2xl font-black text-slate-800">Medi<span class="text-arogga">Vault</span></a>
            <a href="book_appointment.php" class="bg-arogga text-white px-5 py-3 rounded-xl font-bold text-xs uppercase tracking-wider shadow-md">
                <i class="fas fa-calendar-plus mr-2"></i>Book New
            </a>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-10 max-w-5xl">
        <div class="flex items-end justify-between mb-8 border-b border-gray-200 pb-4">
            <div>
                <h1 class="text-3xl font-black text-slate-800 uppercase tracking-tight">My Consultations</h1>
                <p class="text-sm text-slate-500 mt-1">Track your appointment requests and consultation status.</p>
            </div>
        </div>

        <?php if (empty($appointments)): ?>
            <div class="bg-white rounded-[30px] shadow-sm border border-dashed border-slate-200 p-10 text-center">
                <div class="w-16 h-16 bg-teal-100 text-arogga rounded-full flex items-center justify-center text-2xl mx-auto mb-4">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <h2 class="text-xl font-black text-slate-800">No appointment requests yet</h2>
                <p class="text-sm text-slate-500 mt-2">Book your first pharmacist or doctor consultation to get started.</p>
                <a href="book_appointment.php" class="inline-block mt-6 bg-slate-900 text-white px-6 py-3 rounded-xl font-black uppercase text-xs tracking-wider">Book Consultation</a>
            </div>
        <?php else: ?>
            <div class="space-y-5">
                <?php foreach ($appointments as $appointment): ?>
                    <?php
                        $statusColors = [
                            'Pending' => 'bg-amber-100 text-amber-700',
                            'Approved' => 'bg-blue-100 text-blue-700',
                            'Completed' => 'bg-green-100 text-green-700',
                            'Cancelled' => 'bg-red-100 text-red-700',
                        ];
                    ?>
                    <div class="bg-white rounded-[28px] shadow-sm border border-slate-100 p-6">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                            <div>
                                <div class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">Consultation</div>
                                <h3 class="text-xl font-black text-slate-800"><?= htmlspecialchars($appointment['doctor_name']) ?></h3>
                                <p class="text-sm text-slate-500 mt-1">
                                    <?= date('d M Y', strtotime($appointment['appointment_date'])) ?> at <?= htmlspecialchars($appointment['appointment_time']) ?>
                                </p>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="inline-flex px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider <?= $statusColors[$appointment['status']] ?? 'bg-gray-100 text-gray-600' ?>">
                                    <?= htmlspecialchars($appointment['status']) ?>
                                </span>
                                <?php if ($appointment['status'] === 'Pending' || $appointment['status'] === 'Approved'): ?>
                                    <form method="POST" class="inline-block">
                                        <input type="hidden" name="appointment_id" value="<?= (int) $appointment['appointment_id'] ?>">
                                        <button type="submit" name="cancel_appointment" class="bg-red-50 text-red-600 px-4 py-2 rounded-xl font-bold text-xs hover:bg-red-100 transition-all">
                                            <i class="fas fa-times mr-1"></i> Cancel
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if (!empty($appointment['notes'])): ?>
                            <div class="mt-5 rounded-2xl bg-slate-50 border border-slate-100 p-4">
                                <div class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">Notes</div>
                                <p class="text-sm text-slate-600 leading-relaxed"><?= htmlspecialchars($appointment['notes']) ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
