<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'config.php';

if (!isset($_SESSION['user_id'])) {
    die("Please login first.");
}

if (isset($_POST['upload_presc'])) {
    $user_id = $_SESSION['user_id'];
    $doctor = trim($_POST['doctor_name']);
    
    $targetDir = "assets/prescriptions/";

    if (!is_dir($targetDir)) {
        if (!mkdir($targetDir, 0777, true)) {
            die("Error: Cannot create directory assets/prescriptions/");
        }
    }

    $img_name = basename($_FILES['prescription_image']['name']);
    $unique_name = time() . "_" . preg_replace("/[^a-zA-Z0-9.]/", "_", $img_name);
    $folder = $targetDir . $unique_name;
    $tmp_name = $_FILES['prescription_image']['tmp_name'];

    if (move_uploaded_file($tmp_name, $folder)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO prescriptions (user_id, doctor_name, image_path) VALUES (?, ?, ?)");
            $stmt->execute([$user_id, $doctor, $folder]);
            echo "<script>alert('Uploaded successfully!'); window.location='dashboard.php';</script>";
            exit();
        } catch (Exception $e) {
            echo "Database Error: " . $e->getMessage();
        }
    } else {
        echo "Failed to move uploaded file.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Prescription | MediVault</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f3f4f6; }
        .bg-arogga { background-color: #0d9488; }
        .text-arogga { color: #0d9488; }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen p-4">

    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <a href="dashboard.php" class="inline-flex items-center gap-2 group mb-4">
                <div class="bg-arogga text-white p-2 rounded-lg">
                    <i class="fas fa-heartbeat text-xl"></i>
                </div>
                <span class="text-2xl font-bold text-slate-800 tracking-tight">Medi<span class="text-arogga">Vault</span></span>
            </a>
            <h1 class="text-2xl font-black text-slate-800 uppercase tracking-tight">Upload Prescription</h1>
            <p class="text-slate-400 text-xs font-bold mt-1">Upload a clear image of your prescription</p>
        </div>

        <div class="bg-white p-8 rounded-[35px] shadow-xl border border-white relative overflow-hidden">
            <form method="POST" enctype="multipart/form-data" class="space-y-6">
                
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-2 ml-1">Doctor's Name</label>
                    <div class="relative">
                        <i class="fas fa-user-md absolute left-4 top-4 text-gray-300"></i>
                        <input type="text" name="doctor_name" required placeholder="e.g. Dr. John Doe" 
                            class="w-full pl-10 pr-4 py-3.5 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-teal-500 font-bold text-sm text-slate-700 outline-none transition-all">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-2 ml-1">Prescription Image</label>
                    <div class="relative w-full h-40 border-2 border-dashed border-gray-200 rounded-2xl bg-gray-50 hover:bg-gray-100 hover:border-teal-400 transition-all flex flex-col items-center justify-center text-center group">
                        <input type="file" name="prescription_image" id="fileInput" required accept="image/*" 
                            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" onchange="previewFile()">
                        
                        <div id="uploadPlaceholder" class="transition-all duration-300">
                            <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center shadow-sm mb-3 mx-auto text-gray-300 group-hover:text-arogga transition-colors">
                                <i class="fas fa-camera text-xl"></i>
                            </div>
                            <p class="text-xs font-bold text-gray-400 group-hover:text-arogga">Tap to Upload Image</p>
                            <p class="text-[9px] text-gray-300 mt-1">JPG, PNG supported</p>
                        </div>
                        
                        <div id="filePreview" class="hidden flex-col items-center z-0 w-full h-full p-2">
                            <p id="fileName" class="text-xs font-bold text-slate-700 truncate max-w-[200px] bg-white px-3 py-1 rounded-full shadow-sm mb-2"></p>
                            <div class="text-green-500 font-bold text-[10px] uppercase tracking-widest bg-green-50 px-2 py-1 rounded">Image Selected</div>
                        </div>
                    </div>
                </div>

                <button type="submit" name="upload_presc" class="w-full bg-slate-900 text-white py-4 rounded-2xl font-bold uppercase tracking-widest text-xs hover:bg-arogga transition-all shadow-lg active:scale-95">
                    Upload Now <i class="fas fa-arrow-right ml-2"></i>
                </button>

                <div class="text-center pt-2">
                    <a href="dashboard.php" class="text-xs font-bold text-gray-400 hover:text-arogga transition-colors">
                        Cancel & Return
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        function previewFile() {
            const input = document.getElementById('fileInput');
            const placeholder = document.getElementById('uploadPlaceholder');
            const preview = document.getElementById('filePreview');
            const nameDisplay = document.getElementById('fileName');

            if (input.files && input.files[0]) {
                placeholder.classList.add('hidden');
                preview.classList.remove('hidden');
                preview.classList.add('flex');
                nameDisplay.innerText = input.files[0].name;
            }
        }
    </script>
</body>
</html>