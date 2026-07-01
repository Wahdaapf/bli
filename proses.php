<?php
// proses.php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shift = $_POST['shift'] ?? 'pagi';
    $startNumber = intval($_POST['start_number'] ?? 1);

    // Pastikan ada file yang diunggah
    if (!isset($_FILES['images']) || empty($_FILES['images']['name'][0])) {
        die("Tidak ada file gambar yang diunggah atau diurutkan.");
    }

    // Membuat instance ZipArchive bawaan PHP
    $zip = new ZipArchive();
    $zipFileName = tempnam(sys_get_temp_dir(), 'zip');
    
    if ($zip->open($zipFileName, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
        die("Gagal membuat file ZIP di server.");
    }

    $counter = $startNumber;
    $uploadedFiles = $_FILES['images'];
    $fileCount = count($uploadedFiles['name']);

    for ($i = 0; $i < $fileCount; $i++) {
        if ($uploadedFiles['error'][$i] === UPLOAD_ERR_OK) {
            $tmpPath = $uploadedFiles['tmp_name'][$i];
            $originalName = $uploadedFiles['name'][$i];
            
            // Ambil ekstensi asli file (misal: jpg, png)
            $ext = pathinfo($originalName, PATHINFO_EXTENSION);
            if (empty($ext)) {
                $ext = 'png';
            }
            
            // Buat nama baru terurut (misal: evidence13.jpg)
            $newFileName = "evidence" . $counter . "." . $ext;
            
            // Masukkan file ke ZIP
            $zip->addFile($tmpPath, $newFileName);
            $counter++;
        }
    }

    // Tutup ZIP setelah semua file dimasukkan
    $zip->close();

    // Mengirimkan file ZIP kembali ke browser untuk didownload
    header('Content-Type: application/zip');
    header('Content-Length: ' . filesize($zipFileName));
    header('Content-Disposition: attachment; filename="Evidence_Hasil_Sorting_' . $shift . '.zip"');
    readfile($zipFileName);
    
    // Hapus file temporary di sistem server
    unlink($zipFileName);
    exit;
}