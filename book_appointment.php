<?php
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_appointment'])) {
    $user_id = (int) $_SESSION['user_id'];
    $doctor_name = trim($_POST['doctor_name'] ?? '');
    $appointment_date = $_POST['appointment_date'] ?? '';
    $appointment_time = $_POST['appointment_time'] ?? '';
    $notes = trim($_POST['notes'] ?? '');

    if ($doctor_name === '' || $appointment_date === '' || $appointment_time === '') {
        echo "<script>alert('Doctor, date and time are required.'); window.location='book_appointment.php';</script>";
        exit();
    }

    $appointment_date = date('Y-m-d', strtotime($appointment_date));
    $today = date('Y-m-d');
    if ($appointment_date < $today) {
        echo "<script>alert('Appointment date cannot be in the past.'); window.location='book_appointment.php';</script>";
        exit();
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO appointments (user_id, doctor_name, appointment_date, appointment_time, notes, status) VALUES (?, ?, ?, ?, ?, 'Pending')");
        $stmt->execute([$user_id, $doctor_name, $appointment_date, $appointment_time, $notes]);
        echo "<script>alert('Appointment request sent successfully!'); window.location='my_appointments.php';</script>";
        exit();
    } catch (PDOException $e) {
        echo "<script>alert('Error: " . addslashes($e->getMessage()) . "'); window.location='book_appointment.php';</script>";
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Consultation | MediVault</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: linear-gradient(135deg, #ecfeff 0%, #f8fafc 30%, #f0fdf4 100%); }
        .bg-arogga { background-color: #0d9488; }
        .text-arogga { color: #0d9488; }
    </style>
</head>
<body class="min-h-screen">
    <nav class="bg-white shadow-md sticky top-0 z-20">
        <div class="container mx-auto px-4 h-20 flex items-center justify-between">
            <a href="dashboard.php" class="flex items-center gap-3">
                <div class="bg-arogga text-white p-2 rounded-xl"><i class="fas fa-heartbeat"></i></div>
                <span class="text-2xl font-black text-slate-800">Medi<span class="text-arogga">Vault</span></span>
            </a>
            <a href="my_appointments.php" class="text-sm font-bold text-slate-500 hover:text-arogga">
                <i class="fas fa-calendar-check mr-2"></i>My Appointments
            </a>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-12 max-w-3xl">
        <div class="bg-white shadow-xl rounded-[36px] p-8 md:p-12 border border-slate-100">
            <div class="mb-8 text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-teal-100 text-arogga rounded-full text-2xl mb-4">
                    <i class="fas fa-user-md"></i>
                </div>
                <h1 class="text-3xl font-black text-slate-800 uppercase tracking-tight">Book a Consultation</h1>
                <p class="text-sm text-slate-500 mt-2">Schedule a pharmacist or doctor consultation for your treatment needs.</p>
            </div>

            <form method="POST" class="space-y-6">
                <div>
                    <label class="block text-xs font-black uppercase tracking-widest text-slate-500 mb-2">Preferred doctor</label>
                    <input type="text" name="doctor_name" placeholder="e.g. Dr. Rahman" required class="w-full p-4 bg-slate-50 border border-slate-200 rounded-2xl outline-none focus:ring-2 focus:ring-teal-500 font-bold text-slate-700">
                </div>

                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-black uppercase tracking-widest text-slate-500 mb-2">Appointment date</label>
                        <input type="date" name="appointment_date" min="<?= date('Y-m-d') ?>" required class="w-full p-4 bg-slate-50 border border-slate-200 rounded-2xl outline-none focus:ring-2 focus:ring-teal-500 font-bold text-slate-700">
                    </div>
                    <div>
                        <label class="block text-xs font-black uppercase tracking-widest text-slate-500 mb-2">Time</label>
                        <input type="time" name="appointment_time" required class="w-full p-4 bg-slate-50 border border-slate-200 rounded-2xl outline-none focus:ring-2 focus:ring-teal-500 font-bold text-slate-700">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-black uppercase tracking-widest text-slate-500 mb-2">Symptoms / notes</label>
                    <textarea name="notes" rows="4" placeholder="Tell us about your concern, symptoms, or medication question..." class="w-full p-4 bg-slate-50 border border-slate-200 rounded-2xl outline-none focus:ring-2 focus:ring-teal-500 font-medium text-slate-700"></textarea>
                </div>

                <button type="submit" name="book_appointment" class="w-full bg-slate-900 text-white py-4 rounded-2xl font-black uppercase tracking-widest text-xs hover:bg-arogga transition-all shadow-lg">
                    Request Appointment <i class="fas fa-arrow-right ml-2"></i>
                </button>
            </form>
        </div>
    </div>
</body>
</html>
