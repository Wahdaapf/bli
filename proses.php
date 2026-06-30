<?php
// proses.php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shift = $_POST['shift'] ?? 'pagi';
    $startNumber = intval($_POST['start_number'] ?? 1);

    // 1. Ambil data gambar Base64 dari input hidden hasil copy-paste frontend
    $rawWifiman   = $_POST['image_wifiman_base64'] ?? '';
    $rawSpeedtest = $_POST['image_speedtest_base64'] ?? '';

    // Validasi dasar: Minimal salah satu area harus ada gambarnya
    if (empty($rawWifiman) && empty($rawSpeedtest)) {
        die("Tidak ada data gambar WiFiman atau Speedtest yang ditempel (paste).");
    }

    // 2. Membuat instance ZipArchive bawaan PHP
    $zip = new ZipArchive();
    $zipFileName = tempnam(sys_get_temp_dir(), 'zip');
    
    if ($zip->open($zipFileName, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
        die("Gagal membuat file ZIP di server.");
    }

    $counter = $startNumber;

    // ==========================================================
    // PROSES GAMBAR 1: WIFIMAN
    // ==========================================================
    if (!empty($rawWifiman)) {
        // Membersihkan format string data URL Base64
        $wifimanClean = str_replace(['data:image/png;base64,', 'data:image/jpeg;base64,', ' '], ['', '', '+'], $rawWifiman);
        $wifimanBinary = base64_decode($wifimanClean);

        if ($wifimanBinary !== false) {
            // Penamaan berurutan (misal: evidence1.png)
            $newFileNameWifiman = "evidence" . $counter . ".png";
            
            // Masukkan data binary langsung dari memori ke dalam ZIP tanpa membuat file fisik sementara
            $zip->addFromString($newFileNameWifiman, $wifimanBinary);

            /* ------------------------------------------------------
               TEMPAT UNTUK MENEMBAK GEMINI API (OCR WIFIMAN) NANTINYA:
               
               // Karena sudah berwujud string murni, hilangkan header jika mau dioper ke API
               $base64ForGemini = $wifimanClean; 
               // hitGeminiAPIWifiman($base64ForGemini);
            ------------------------------------------------------ */

            $counter++;
        }
    }

    // ==========================================================
    // PROSES GAMBAR 2: SPEEDTEST
    // ==========================================================
    if (!empty($rawSpeedtest)) {
        // Membersihkan format string data URL Base64
        $speedtestClean = str_replace(['data:image/png;base64,', 'data:image/jpeg;base64,', ' '], ['', '', '+'], $rawSpeedtest);
        $speedtestBinary = base64_decode($speedtestClean);

        if ($speedtestBinary !== false) {
            // Penamaan berurutan lanjutannya (misal: evidence2.png)
            $newFileNameSpeedtest = "evidence" . $counter . ".png";
            
            // Masukkan data binary langsung dari memori ke dalam ZIP
            $zip->addFromString($newFileNameSpeedtest, $speedtestBinary);

            /* ------------------------------------------------------
               TEMPAT UNTUK MENEMBAK GEMINI API (OCR SPEEDTEST) NANTINYA:
               
               $base64ForGeminiSpeedtest = $speedtestClean;
               // hitGeminiAPISpeedtest($base64ForGeminiSpeedtest);
            ------------------------------------------------------ */

            $counter++;
        }
    }

    // 3. Tutup dan kunci file ZIP setelah semua file dimasukkan
    $zip->close();

    // 4. Mengirimkan file ZIP kembali ke browser untuk otomatis diunduh mandiri oleh user
    header('Content-Type: application/zip');
    header('Content-Length: ' . filesize($zipFileName));
    header('Content-Disposition: attachment; filename="Evidence_Sorted_Paste.zip"');
    readfile($zipFileName);
    
    // Hapus file temporary di sistem server agar hemat penyimpanan
    unlink($zipFileName);
    exit;
}