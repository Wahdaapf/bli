<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Sistem Monitoring WiFi & Speedtest</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 30px; background-color: #f9f9f9; color: #333; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h2, h3 { color: #2c3e50; }
        select, input[type="number"], input[type="file"], button { padding: 10px; margin: 10px 0; font-size: 14px; border-radius: 4px; border: 1px solid #ccc; }
        button { background-color: #27ae60; color: white; border: none; cursor: pointer; font-weight: bold; }
        button:hover { background-color: #219150; }
        .grid-foto { display: flex; flex-wrap: wrap; gap: 15px; margin: 15px 0; padding: 10px; background: #eee; border-radius: 6px; min-height: 50px; }
        .item-foto { background: white; padding: 10px; border: 1px solid #ddd; border-radius: 4px; text-align: center; width: 150px; }
        .item-foto p { font-size: 11px; word-wrap: break-word; margin: 5px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 13px; }
        th, td { border: 1px solid #aaa; padding: 8px; text-align: left; }
        th { background-color: #00FF00; color: black; font-weight: bold; }
        .closed-row { background-color: #FFC7CE; color: #9C0006; }
        .badge-sort { background: #2980b9; color: white; padding: 2px 6px; border-radius: 10px; font-size: 11px; }
    </style>
</head>
<body>

<div class="container">
    <h2>Sistem Otomasi Monitoring & Sorting Gambar</h2>
    <hr>

    <div>
        <label style="font-weight: bold;">1. Pilih Shift Kerja: </label>
        <select id="shiftSelect" onchange="renderTabelDanForm()">
            <option value="">-- Silahkan Pilih Shift --</option>
            <option value="pagi">Shift Pagi</option>
            <option value="siang">Shift Siang</option>
            <option value="malam">Shift Malam</option>
        </select>
    </div>

    <div id="workflowArea" style="display: none; margin-top: 25px;">
        
        <div>
            <label style="font-weight: bold;">Nomor Awal Rename Evidence: </label>
            <input type="number" id="startNumber" value="1" min="1" style="width: 80px;">
            <span style="font-size: 12px; color: #7f8c8d;"> (Contoh: dimasukkan 13 -> evidence13.jpg, evidence14.jpg)</span>
        </div>

        <div style="margin-top: 20px;">
            <h3>2. Upload Folder Foto (WiFiman & Speedtest Campur)</h3>
            <input type="file" id="imageFolder" multiple accept="image/*" onchange="handleFolderSelect()">
        </div>

        <div>
            <h3>3. Urutkan File Foto (Klik Sesuai Urutan Yang Diinginkan)</h3>
            <p style="font-size: 12px; color: #e67e22; font-weight: bold;">*Centang checkbox di bawah foto secara berurutan dari yang awal sampai akhir.</p>
            <div class="grid-foto" id="previewGrid"></div>
        </div>

        <button type="button" onclick="prosesDataDanDownload()">Proses Teks & Unduh ZIP Foto</button>

        <div>
            <h3>4. Preview Tabel Struktur Shift</h3>
            <div id="tableContainer"></div>
        </div>
    </div>
</div>

<?php include 'templates.php'; ?>
<script>
// Melempar data PHP templates ke JavaScript agar dinamis di client-side
const templatesData = <?php echo json_encode($templates); ?>;
let selectedFiles = [];
let sortedFileOrder = [];

function renderTabelDanForm() {
    const shift = document.getElementById('shiftSelect').value;
    const workflowArea = document.getElementById('workflowArea');
    const tableContainer = document.getElementById('tableContainer');
    
    if (!shift) {
        workflowArea.style.display = 'none';
        return;
    }
    
    workflowArea.style.display = 'block';
    const dataShift = templatesData[shift];
    
    // Generate Visual Tabel secara dinamis sesuai gambar excel user
    let htmlTable = `<table>
        <thead>
            <tr>
                <th>Location</th>
                <th>Access Point Name</th>
                <th>Mac Address</th>
                <th>Clock</th>
                <th>Channel/Freq</th>
                <th>Connected Device</th>
                <th>Ch. Util(%)</th>
                <th>Load</th>
                <th>Signal (dBm)</th>
                <th>Download(Mbps)</th>
                <th>Upload(Mbps)</th>
                <th>Ping(ms)</th>
                <th>Jitter(ms)</th>
                <th>Status / Note</th>
            </tr>
        </thead>
        <tbody>`;
        
    dataShift.forEach(row => {
        let isClosed = row.note === 'Closed Area' ? 'class="closed-row"' : '';
        htmlTable += `<tr ${isClosed}>
            <td>${row.location}</td>
            <td>${row.ap_name}</td>
            <td>${row.mac_address}</td>
            <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
            <td>${row.note}</td>
        </tr>`;
    });
    
    htmlTable += `</tbody></table>`;
    tableContainer.innerHTML = htmlTable;
    
    // Reset data upload jika ganti shift
    document.getElementById('imageFolder').value = '';
    document.getElementById('previewGrid').innerHTML = '';
    selectedFiles = [];
    sortedFileOrder = [];
}

function handleFolderSelect() {
    const input = document.getElementById('imageFolder');
    const grid = document.getElementById('previewGrid');
    grid.innerHTML = '';
    selectedFiles = Array.from(input.files);
    sortedFileOrder = [];

    if (selectedFiles.length === 0) return;

    selectedFiles.forEach((file, index) => {
        const item = document.createElement('div');
        item.className = 'item-foto';
        
        // Membuat object URL agar gambar bisa tampil di website
        const imgUrl = URL.createObjectURL(file);
        
        item.innerHTML = `
            <img src="${imgUrl}" style="width:100%; height:100px; object-fit:contain; border-bottom:1px solid #eee;">
            <p>${file.name}</p>
            <input type="checkbox" id="chk_${index}" onchange="handleCheckboxChange(this, '${file.name}')">
            <span id="badge_${index}"></span>
        `;
        grid.appendChild(item);
    });
}

function handleCheckboxChange(checkbox, fileName) {
    const index = checkbox.id.split('_')[1];
    const badge = document.getElementById(`badge_${index}`);

    if (checkbox.checked) {
        sortedFileOrder.push(fileName);
        badge.innerHTML = `<span class="badge-sort">#${sortedFileOrder.length}</span>`;
    } else {
        sortedFileOrder = sortedFileOrder.filter(name => name !== fileName);
        badge.innerHTML = '';
        // Re-index susunan angka badge jika ada yang di-uncheck di tengah jalan
        sortedFileOrder.forEach((name, idx) => {
            const currentFileIndex = selectedFiles.findIndex(f => f.name === name);
            if(currentFileIndex !== -1) {
                document.getElementById(`badge_${currentFileIndex}`).innerHTML = `<span class="badge-sort">#${idx + 1}</span>`;
            }
        });
    }
}

function prosesDataDanDownload() {
    if (sortedFileOrder.length === 0) {
        alert("Silahkan pilih dan centang file foto sesuai urutan terlebih dahulu!");
        return;
    }

    alert("Mempersiapkan data dan membuat folder ZIP. Menghubungkan ke engine auto-rename server...");
    
    // Logika frontend mengirim urutan file terpilih ke file proses.php via Form/POST
    const formData = new FormData();
    formData.append('shift', document.getElementById('shiftSelect').value);
    formData.append('start_number', document.getElementById('startNumber').value);
    formData.append('file_order', JSON.stringify(sortedFileOrder));
    
    // Menyisipkan file-file aktual yang dicentang
    sortedFileOrder.forEach((fileName) => {
        const fileObj = selectedFiles.find(f => f.name === fileName);
        if (fileObj) {
            formData.append('images[]', fileObj);
        }
    });

    // Kirim data ke backend proses.php
    fetch('proses.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.blob())
    .then(blob => {
        // Otomatis download file .ZIP hasil olahan penamaan baru dari PHP server
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `Evidence_Hasil_Sorting_${document.getElementById('shiftSelect').value}.zip`;
        document.body.appendChild(a);
        a.click();    
        a.remove();
    })
    .catch(err => alert("Gagal memproses file di server backend: " + err));
}
</script>
</body>
</html>