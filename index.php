<!DOCTYPE html>
<html lang="id">
    <meta charset="UTF-8">
    <title>Sistem Monitoring WiFi & Speedtest</title>
    <!-- Tesseract.js library for Browser OCR -->
    <script src="https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js"></script>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 30px; background-color: #f0f3f6; color: #2c3e50; }
        .container { max-width: 1400px; margin: 0 auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        h2, h3 { color: #2c3e50; font-weight: 600; margin-top: 0; }
        select, input[type="number"], input[type="file"], button { padding: 10px 15px; margin: 10px 0; font-size: 14px; border-radius: 6px; border: 1px solid #cbd5e1; }
        button { background-color: #27ae60; color: white; border: none; cursor: pointer; font-weight: bold; transition: background-color 0.2s, transform 0.1s; }
        button:hover { background-color: #219150; }
        button:active { transform: scale(0.98); }
        .btn-export { background-color: #3498db; margin-left: 10px; }
        .btn-export:hover { background-color: #2980b9; }
        .grid-foto { display: flex; flex-wrap: wrap; gap: 15px; margin: 15px 0; padding: 15px; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 8px; min-height: 100px; }
        
        /* Premium Card style for uploaded photos */
        .item-foto { 
            position: relative; 
            background: white; 
            padding: 12px; 
            border: 1px solid #e2e8f0; 
            border-radius: 8px; 
            text-align: center; 
            width: 170px; 
            transition: transform 0.2s, box-shadow 0.2s, border-color 0.2s; 
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }
        .item-foto:hover { 
            transform: translateY(-2px); 
            box-shadow: 0 4px 12px rgba(0,0,0,0.08); 
            border-color: #b4c6fc;
        }
        .item-foto p { font-size: 11px; word-wrap: break-word; margin: 5px 0; color: #64748b; font-weight: 500; }
        
        /* OCR Badges and Indicators */
        .ocr-status { font-size: 11px; font-weight: 600; padding: 3px 8px; border-radius: 20px; display: inline-block; margin: 5px 0; text-transform: uppercase; }
        .ocr-pending { background-color: #f1f5f9; color: #64748b; }
        .ocr-running { background-color: #fef3c7; color: #d97706; animation: pulse 1.5s infinite; }
        .ocr-success-wifiman { background-color: #dcfce7; color: #15803d; }
        .ocr-success-speedtest { background-color: #dbeafe; color: #1d4ed8; }
        .ocr-failed { background-color: #fee2e2; color: #b91c1c; }
        
        .ocr-details {
            display: none;
            text-align: left;
            background: #f8fafc;
            padding: 8px;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
            margin-top: 8px;
            font-size: 10px;
            color: #334155;
            white-space: pre-wrap;
            max-height: 120px;
            overflow-y: auto;
        }
        .item-foto:hover .ocr-details { display: block; }
        
        /* Table and editable cells style */
        table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 13px; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.02); }
        th, td { border: 1px solid #e2e8f0; padding: 10px 12px; text-align: left; }
        th { background-color: #10b981; color: white; font-weight: 600; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px; }
        
        td[contenteditable="true"] { 
            cursor: text; 
            transition: background-color 0.15s; 
            outline: none;
        }
        td[contenteditable="true"]:hover { background-color: #f0fdf4; }
        td[contenteditable="true"]:focus { 
            background-color: #dcfce7; 
            box-shadow: inset 0 0 0 2px #10b981; 
        }
        
        .closed-row { background-color: #fee2e2; color: #991b1b; }
        .closed-row td { border-color: #fca5a5; }
        
        .badge-sort { background: #3b82f6; color: white; padding: 2px 8px; border-radius: 20px; font-size: 11px; font-weight: bold; }
        
        /* Highlight updated row briefly */
        @keyframes highlight {
            0% { background-color: #fef08a; }
            100% { background-color: transparent; }
        }
        .row-highlight { animation: highlight 2s ease-out; }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.6; }
        }
        
        /* Count Indicators Styling */
        .count-container {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }
        .count-indicator {
            flex: 1;
            padding: 15px;
            border-radius: 8px;
            font-size: 15px;
            font-weight: bold;
            text-align: center;
            border: 1px solid #cbd5e1;
            transition: all 0.2s;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }
        .count-green {
            background-color: #dcfce7;
            color: #15803d;
            border-color: #bbf7d0;
        }
        .count-red {
            background-color: #fee2e2;
            color: #b91c1c;
            border-color: #fca5a5;
        }

        /* Upload Columns Layout */
        .upload-columns {
            display: flex;
            gap: 20px;
            margin-top: 20px;
            flex-wrap: wrap;
        }
        .upload-col {
            flex: 1;
            min-width: 320px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 20px;
            border-radius: 8px;
        }

        /* Numbered Thumbnail Cards Grid */
        .thumb-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
            gap: 12px;
            margin-top: 15px;
        }
        .thumb-card {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 8px;
            text-align: center;
            position: relative;
            transition: all 0.2s;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }
        .thumb-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.06);
            border-color: #cbd5e1;
        }
        .thumb-badge {
            position: absolute;
            top: -6px;
            left: -6px;
            background: #1e293b;
            color: white;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            font-size: 11px;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.15);
            border: 1px solid #ffffff;
            z-index: 10;
        }
        .thumb-img {
            width: 100%;
            height: 80px;
            object-fit: contain;
            border-radius: 4px;
            border-bottom: 1px solid #f1f5f9;
            margin-bottom: 6px;
        }
        .thumb-filename {
            font-size: 10px;
            color: #64748b;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            margin: 4px 0;
            font-weight: 500;
        }
        .thumb-card .ocr-status {
            font-size: 9px;
            padding: 2px 6px;
            margin: 3px 0;
        }
        .thumb-card .ocr-details {
            font-size: 9px;
            width: 220px;
            max-height: 180px;
            overflow-y: auto;
            padding: 8px;
            display: none;
            position: absolute;
            bottom: 105%;
            left: 50%;
            transform: translateX(-50%);
            z-index: 100;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            background: white;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            text-align: left;
            white-space: normal;
        }
        .thumb-card:hover .ocr-details {
            display: block;
        }

        /* Preview Modal Styles */
        .modal {
            display: none; 
            position: fixed; 
            z-index: 10000; 
            left: 0;
            top: 0;
            width: 100%; 
            height: 100%; 
            overflow: auto; 
            background-color: rgba(15, 23, 42, 0.75);
            backdrop-filter: blur(8px);
            justify-content: center;
            align-items: center;
            transition: opacity 0.3s ease;
        }
        .modal-content {
            position: relative;
            background-color: transparent;
            padding: 0;
            max-width: 90%;
            max-height: 90%;
            display: flex;
            justify-content: center;
            align-items: center;
            animation: zoomIn 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        @keyframes zoomIn {
            from { transform: scale(0.9); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
        .modal-content img {
            max-width: 100%;
            max-height: 80vh;
            border-radius: 12px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            border: 3px solid rgba(255, 255, 255, 0.2);
        }
        .close-modal {
            position: absolute;
            top: -45px;
            right: 0;
            color: #ffffff;
            font-size: 36px;
            font-weight: bold;
            cursor: pointer;
            transition: color 0.15s;
            text-shadow: 0 2px 4px rgba(0,0,0,0.5);
        }
        .close-modal:hover {
            color: #ef4444;
        }
        .btn-preview-thumb {
            background-color: #3b82f6;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 4px 8px;
            font-size: 10px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            margin-top: 5px;
            transition: background-color 0.15s;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        }
        .btn-preview-thumb:hover {
            background-color: #2563eb;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Sistem Otomasi Monitoring & Sorting Gambar <span style="font-size:12px; color:#64748b; font-weight:normal;">(v2.3 - Typo-Tolerant Frequency Parsing)</span></h2>
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

        <div class="count-container" style="margin-top: 20px;">
            <div id="wifimanIndicator" class="count-indicator count-green">WiFiman (0/0)</div>
            <div id="speedtestIndicator" class="count-indicator count-green">Speedtest (0/0)</div>
        </div>

        <div class="upload-columns">
            <div class="upload-col">
                <h3 style="margin-top: 0; color: #15803d; font-size: 16px;">2a. Upload Folder Foto WiFiman</h3>
                <input type="file" id="wifimanFolder" multiple accept="image/*" onchange="handleWifimanSelect()" style="width: 100%; box-sizing: border-box;">
                <div class="thumb-grid" id="wifimanGrid"></div>
            </div>
            
            <div class="upload-col">
                <h3 style="margin-top: 0; color: #1d4ed8; font-size: 16px;">2b. Upload Folder Foto Speedtest</h3>
                <input type="file" id="speedtestFolder" multiple accept="image/*" onchange="handleSpeedtestSelect()" style="width: 100%; box-sizing: border-box;">
                <div class="thumb-grid" id="speedtestGrid"></div>
            </div>
        </div>

        <div style="margin-top: 20px; background: #fffbeb; border: 1px solid #fef3c7; padding: 15px; border-radius: 8px;">
            <p style="font-size: 13px; color: #b45309; font-weight: 600; margin: 0;">
                💡 *Gambar akan otomatis diurutkan di dalam ZIP hasil download sesuai urutan baris tabel AP di bawah (WiFiman lalu Speedtest untuk masing-masing AP). Anda tidak perlu mengurutkan secara manual.
            </p>
        </div>

        <div style="margin-top: 15px;">
            <button type="button" onclick="prosesDataDanDownload()">Proses Teks & Unduh ZIP Foto</button>
            <button type="button" class="btn-export" onclick="copyTableToClipboard()">Salin Tabel ke Clipboard / Excel</button>
        </div>

        <div>
            <h3>3. Preview Tabel Struktur Shift (Nilai tabel dapat diklik dan diedit langsung)</h3>
            <div id="tableContainer"></div>
        </div>
    </div>
</div>

<!-- Modal Preview Gambar -->
<div id="imageModal" class="modal" onclick="closeImageModal(event)">
    <div class="modal-content">
        <span class="close-modal" onclick="closeImageModal(event)">&times;</span>
        <img id="modalImage" src="" alt="Preview Gambar">
    </div>
</div>

<?php include 'templates.php'; ?>
<script>
// Melempar data PHP templates ke JavaScript agar dinamis di client-side
const templatesData = <?php echo json_encode($templates); ?>;
let wifimanFiles = [];
let speedtestFiles = [];
let pairings = []; // array of objects: { pairedMac: '', wifimanOcr: null, speedtestOcr: null }
let currentShiftAps = []; // Simpan daftar AP shift aktif secara global

function renderTabelDanForm() {
    const shift = document.getElementById('shiftSelect').value;
    const workflowArea = document.getElementById('workflowArea');
    const tableContainer = document.getElementById('tableContainer');
    
    if (!shift) {
        workflowArea.style.display = 'none';
        currentShiftAps = [];
        return;
    }
    
    workflowArea.style.display = 'block';
    const dataShift = templatesData[shift];
    currentShiftAps = dataShift; // Simpan daftar AP shift aktif secara global
    
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
        
    dataShift.forEach((row, idx) => {
        let isClosed = row.note === 'Closed Area' ? 'closed-row' : '';
        let normMac = row.mac_address.toLowerCase().trim();
        htmlTable += `<tr class="${isClosed}" data-mac="${normMac}" data-index="${idx}">
            <td>${row.location}</td>
            <td>${row.ap_name}</td>
            <td class="mac-cell">${row.mac_address}</td>
            <td contenteditable="true" data-field="clock"></td>
            <td contenteditable="true" data-field="channel"></td>
            <td contenteditable="true" data-field="clients"></td>
            <td contenteditable="true" data-field="utilization"></td>
            <td contenteditable="true" data-field="load"></td>
            <td contenteditable="true" data-field="signal"></td>
            <td contenteditable="true" data-field="download"></td>
            <td contenteditable="true" data-field="upload"></td>
            <td contenteditable="true" data-field="ping"></td>
            <td contenteditable="true" data-field="jitter"></td>
            <td contenteditable="true" data-field="note">${row.note}</td>
        </tr>`;
    });
    
    htmlTable += `</tbody></table>`;
    tableContainer.innerHTML = htmlTable;
    
    // Reset data upload jika ganti shift
    if (document.getElementById('wifimanFolder')) document.getElementById('wifimanFolder').value = '';
    if (document.getElementById('speedtestFolder')) document.getElementById('speedtestFolder').value = '';
    if (document.getElementById('wifimanGrid')) document.getElementById('wifimanGrid').innerHTML = '';
    if (document.getElementById('speedtestGrid')) document.getElementById('speedtestGrid').innerHTML = '';
    
    wifimanFiles = [];
    speedtestFiles = [];
    pairings = [];
    updateIndicators();
}

function updateIndicators() {
    const totalAps = currentShiftAps.length;
    const wCount = wifimanFiles.length;
    const sCount = speedtestFiles.length;
    
    const wifiEl = document.getElementById('wifimanIndicator');
    const speedEl = document.getElementById('speedtestIndicator');
    
    if (wCount === sCount) {
        wifiEl.className = 'count-indicator count-green';
        wifiEl.innerText = `WiFiman (${wCount}/${totalAps})`;
        
        speedEl.className = 'count-indicator count-green';
        speedEl.innerText = `Speedtest (${sCount}/${totalAps})`;
    } else {
        wifiEl.className = 'count-indicator count-red';
        wifiEl.innerText = `WiFiman (${wCount})`;
        
        speedEl.className = 'count-indicator count-red';
        speedEl.innerText = `Speedtest (${sCount})`;
    }
}

function handleWifimanSelect() {
    const input = document.getElementById('wifimanFolder');
    wifimanFiles = Array.from(input.files);
    
    adjustPairingsLength();
    renderWifimanThumbs();
    updateIndicators();
    
    processWifimanOcr();
}

function handleSpeedtestSelect() {
    const input = document.getElementById('speedtestFolder');
    speedtestFiles = Array.from(input.files);
    
    adjustPairingsLength();
    renderSpeedtestThumbs();
    updateIndicators();
    
    processSpeedtestOcr();
}

function adjustPairingsLength() {
    const maxLen = Math.max(wifimanFiles.length, speedtestFiles.length);
    
    while (pairings.length < maxLen) {
        pairings.push({
            pairedMac: '',
            wifimanOcr: null,
            speedtestOcr: null
        });
    }
    
    if (pairings.length > maxLen) {
        pairings = pairings.slice(0, maxLen);
    }
}

function renderWifimanThumbs() {
    const grid = document.getElementById('wifimanGrid');
    grid.innerHTML = '';
    
    wifimanFiles.forEach((file, index) => {
        const item = document.createElement('div');
        item.className = 'thumb-card';
        const imgUrl = URL.createObjectURL(file);
        
        let selectOptions = `<option value="">-- Pilih AP --</option>`;
        currentShiftAps.forEach(ap => {
            const apMac = ap.mac_address.toLowerCase().trim();
            const isSelected = apMac === pairings[index].pairedMac ? 'selected' : '';
            selectOptions += `<option value="${apMac}" ${isSelected}>${ap.ap_name}</option>`;
        });
        
        item.innerHTML = `
            <div class="thumb-badge">${index + 1}</div>
            <img src="${imgUrl}" class="thumb-img">
            <div class="thumb-filename" title="${file.name}">${file.name}</div>
            <div style="margin-top:4px;">
                <select class="ap-select" style="font-size:10px; padding:3px; width:100%; border-radius:4px; border:1px solid #cbd5e1;" onchange="handleManualPairing(${index}, this.value)">
                    ${selectOptions}
                </select>
            </div>
            <button type="button" class="btn-preview-thumb" onclick="showImageModal('${imgUrl}')">🔍 Lihat Foto</button>
            <div class="ocr-status ocr-pending" id="wifiman_status_${index}">⏳ Pending</div>
            <div class="ocr-details" id="wifiman_details_${index}">Menunggu giliran...</div>
        `;
        grid.appendChild(item);
    });
}

function renderSpeedtestThumbs() {
    const grid = document.getElementById('speedtestGrid');
    grid.innerHTML = '';
    
    speedtestFiles.forEach((file, index) => {
        const item = document.createElement('div');
        item.className = 'thumb-card';
        const imgUrl = URL.createObjectURL(file);
        
        const pairedMac = pairings[index] ? pairings[index].pairedMac : '';
        const pairedAp = currentShiftAps.find(ap => ap.mac_address.toLowerCase().trim() === pairedMac);
        const pairedApName = pairedAp ? pairedAp.ap_name : 'Belum Berpasangan';
        
        item.innerHTML = `
            <div class="thumb-badge">${index + 1}</div>
            <img src="${imgUrl}" class="thumb-img">
            <div class="thumb-filename" title="${file.name}">${file.name}</div>
            <div style="font-size:9px; color:#475569; font-weight:bold; margin-top:4px; background:#f1f5f9; padding:3px; border-radius:4px;" id="speedtest_ap_label_${index}">
                🔗 AP: ${pairedApName}
            </div>
            <button type="button" class="btn-preview-thumb" onclick="showImageModal('${imgUrl}')">🔍 Lihat Foto</button>
            <div class="ocr-status ocr-pending" id="speedtest_status_${index}">⏳ Pending</div>
            <div class="ocr-details" id="speedtest_details_${index}">Menunggu giliran...</div>
        `;
        grid.appendChild(item);
    });
}

function handleManualPairing(index, newMac) {
    if (newMac) {
        const duplicateIdx = pairings.findIndex((p, idx) => idx !== index && p.pairedMac === newMac);
        if (duplicateIdx !== -1) {
            alert(`AP ini sudah dihubungkan ke Pengukuran #${duplicateIdx + 1}!`);
            renderWifimanThumbs();
            return;
        }
    }
    
    pairings[index].pairedMac = newMac;
    
    const labelEl = document.getElementById(`speedtest_ap_label_${index}`);
    if (labelEl) {
        const pairedAp = currentShiftAps.find(ap => ap.mac_address.toLowerCase().trim() === newMac);
        labelEl.innerText = `🔗 AP: ${pairedAp ? pairedAp.ap_name : 'Belum Berpasangan'}`;
    }
    
    updateTableFromPairings();
}

async function processWifimanOcr() {
    const statusDivs = wifimanFiles.map((_, idx) => document.getElementById(`wifiman_status_${idx}`));
    const detailDivs = wifimanFiles.map((_, idx) => document.getElementById(`wifiman_details_${idx}`));
    
    let worker = null;
    try {
        for (let idx = 0; idx < wifimanFiles.length; idx++) {
            statusDivs[idx].className = 'ocr-status ocr-running';
            statusDivs[idx].innerText = '⏳ Init Engine...';
        }
        
        worker = await Tesseract.createWorker('eng', 1, {
            logger: (m) => {
                if (m.status === 'recognizing text' && window.currentWifimanOcrIdx !== undefined) {
                    const percent = Math.round(m.progress * 100);
                    const currentStatus = document.getElementById(`wifiman_status_${window.currentWifimanOcrIdx}`);
                    if (currentStatus) {
                        currentStatus.innerText = `⚡ Scan (${percent}%)`;
                    }
                }
            }
        });
        
        for (let idx = 0; idx < wifimanFiles.length; idx++) {
            window.currentWifimanOcrIdx = idx;
            const file = wifimanFiles[idx];
            const statusDiv = statusDivs[idx];
            const detailDiv = detailDivs[idx];
            
            statusDiv.className = 'ocr-status ocr-running';
            statusDiv.innerText = '⚡ Scan (0%)';
            detailDiv.innerText = 'Membaca gambar...';
            
            try {
                const result = await worker.recognize(file);
                const text = result.data.text;
                const parsed = parseOcrData(text);
                parsed.rawText = text;
                
                pairings[idx].wifimanOcr = parsed;
                
                // Paksa type menjadi wifiman jika diunggah di kolom WiFiman
                if (parsed.type === 'unknown') {
                    parsed.type = 'wifiman';
                }

                if (parsed.type === 'wifiman') {
                    statusDiv.className = 'ocr-status ocr-success-wifiman';
                    statusDiv.innerText = '✅ WiFiman';
                    
                    let detailHtml = `<b>Hasil WiFiman:</b>\n`;
                    if (parsed.mac) detailHtml += `• MAC: ${parsed.mac}\n`;
                    if (parsed.signal) detailHtml += `• Signal: ${parsed.signal}\n`;
                    if (parsed.channel) detailHtml += `• Channel: ${parsed.channel}\n`;
                    if (parsed.utilization) detailHtml += `• Util: ${parsed.utilization}\n`;
                    if (parsed.clients !== null) detailHtml += `• Clients: ${parsed.clients}\n`;
                    detailHtml += `\n---\n<b>Raw OCR:</b>\n${text}`;
                    detailDiv.innerHTML = detailHtml.replace(/\n/g, '<br>');
                    
                    const matchedMac = parsed.mac ? parsed.mac.toLowerCase().trim() : '';
                    if (matchedMac) {
                        const apExists = currentShiftAps.some(ap => ap.mac_address.toLowerCase().trim() === matchedMac);
                        if (apExists) {
                            const duplicateIdx = pairings.findIndex((p, i) => i !== idx && p.pairedMac === matchedMac);
                            if (duplicateIdx === -1) {
                                pairings[idx].pairedMac = matchedMac;
                                
                                const selectEl = document.querySelector(`#wifimanGrid .thumb-card:nth-child(${idx+1}) .ap-select`);
                                if (selectEl) selectEl.value = matchedMac;
                                
                                const labelEl = document.getElementById(`speedtest_ap_label_${idx}`);
                                if (labelEl) {
                                    const pairedAp = currentShiftAps.find(ap => ap.mac_address.toLowerCase().trim() === matchedMac);
                                    labelEl.innerText = `🔗 AP: ${pairedAp ? pairedAp.ap_name : 'Belum Berpasangan'}`;
                                }
                            }
                        }
                    }
                } else {
                    statusDiv.className = 'ocr-status ocr-failed';
                    statusDiv.innerText = '❓ Unknown';
                    let detailHtml = `<span style="color:#ef4444;">Bukan screenshot WiFiman.</span>\n\n---\n<b>Raw OCR:</b>\n${text}`;
                    detailDiv.innerHTML = detailHtml.replace(/\n/g, '<br>');
                }
            } catch (err) {
                console.error("Gagal scan OCR file: " + file.name, err);
                statusDiv.className = 'ocr-status ocr-failed';
                statusDiv.innerText = '❌ Gagal';
                detailDiv.innerText = 'Error: ' + err.message;
            }
        }
        
        updateTableFromPairings();
    } catch (err) {
        console.error("Gagal mengaktifkan Tesseract worker", err);
        alert("Gagal menginisialisasi engine Tesseract OCR: " + err.message);
    } finally {
        if (worker) {
            await worker.terminate();
        }
    }
}

async function processSpeedtestOcr() {
    const statusDivs = speedtestFiles.map((_, idx) => document.getElementById(`speedtest_status_${idx}`));
    const detailDivs = speedtestFiles.map((_, idx) => document.getElementById(`speedtest_details_${idx}`));
    
    let worker = null;
    try {
        for (let idx = 0; idx < speedtestFiles.length; idx++) {
            statusDivs[idx].className = 'ocr-status ocr-running';
            statusDivs[idx].innerText = '⏳ Init Engine...';
        }
        
        worker = await Tesseract.createWorker('eng', 1, {
            logger: (m) => {
                if (m.status === 'recognizing text' && window.currentSpeedtestOcrIdx !== undefined) {
                    const percent = Math.round(m.progress * 100);
                    const currentStatus = document.getElementById(`speedtest_status_${window.currentSpeedtestOcrIdx}`);
                    if (currentStatus) {
                        currentStatus.innerText = `⚡ Scan (${percent}%)`;
                    }
                }
            }
        });
        
        for (let idx = 0; idx < speedtestFiles.length; idx++) {
            window.currentSpeedtestOcrIdx = idx;
            const file = speedtestFiles[idx];
            const statusDiv = statusDivs[idx];
            const detailDiv = detailDivs[idx];
            
            statusDiv.className = 'ocr-status ocr-running';
            statusDiv.innerText = '⚡ Scan (0%)';
            detailDiv.innerText = 'Membaca gambar...';
            
            try {
                const result = await worker.recognize(file);
                const text = result.data.text;
                const parsed = parseOcrData(text);
                parsed.rawText = text;
                
                // === REGIONAL OCR FALLBACK ===
                // Jika DL atau UL tidak terbaca (angka stylized di bg gelap),
                // crop bagian kiri/kanan atas gambar dan OCR ulang dengan inversi warna
                if (!parsed.download || !parsed.upload) {
                    if (!parsed.download) {
                        statusDiv.innerText = '⚡ Scan region DL...';
                        const dlNum = await cropAndOcrRegion(file, worker, 'download');
                        if (dlNum) parsed.download = dlNum;
                    }
                    if (!parsed.upload) {
                        statusDiv.innerText = '⚡ Scan region UL...';
                        const ulNum = await cropAndOcrRegion(file, worker, 'upload');
                        if (ulNum) parsed.upload = ulNum;
                    }
                }
                
                pairings[idx].speedtestOcr = parsed;
                
                // Paksa type menjadi speedtest jika diunggah di kolom Speedtest
                if (parsed.type === 'unknown') {
                    parsed.type = 'speedtest';
                }
                
                if (parsed.type === 'speedtest') {
                    statusDiv.className = 'ocr-status ocr-success-speedtest';
                    statusDiv.innerText = '✅ Speedtest';
                    
                    let detailHtml = `<b>Hasil Speedtest:</b>\n`;
                    if (parsed.download) detailHtml += `• DL: ${parsed.download} Mbps\n`;
                    if (parsed.upload) detailHtml += `• UL: ${parsed.upload} Mbps\n`;
                    if (parsed.ping) detailHtml += `• Ping: ${parsed.ping} ms\n`;
                    if (parsed.jitter) detailHtml += `• Jitter: ${parsed.jitter} ms\n`;
                    detailHtml += `\n---\n<b>Raw OCR:</b>\n${text}`;
                    detailDiv.innerHTML = detailHtml.replace(/\n/g, '<br>');
                    
                    if (!pairings[idx].pairedMac) {
                        const predictedMac = currentShiftAps[idx] ? currentShiftAps[idx].mac_address.toLowerCase().trim() : '';
                        if (predictedMac) {
                            const duplicateIdx = pairings.findIndex((p, i) => i !== idx && p.pairedMac === predictedMac);
                            if (duplicateIdx === -1) {
                                pairings[idx].pairedMac = predictedMac;
                                
                                const selectEl = document.querySelector(`#wifimanGrid .thumb-card:nth-child(${idx+1}) .ap-select`);
                                if (selectEl) selectEl.value = predictedMac;
                                
                                const labelEl = document.getElementById(`speedtest_ap_label_${idx}`);
                                if (labelEl) {
                                    const pairedAp = currentShiftAps.find(ap => ap.mac_address.toLowerCase().trim() === predictedMac);
                                    labelEl.innerText = `🔗 AP: ${pairedAp ? pairedAp.ap_name : 'Belum Berpasangan'}`;
                                }
                            }
                        }
                    }
                } else {
                    statusDiv.className = 'ocr-status ocr-failed';
                    statusDiv.innerText = '❓ Unknown';
                    let detailHtml = `<span style="color:#ef4444;">Bukan screenshot Speedtest.</span>\n\n---\n<b>Raw OCR:</b>\n${text}`;
                    detailDiv.innerHTML = detailHtml.replace(/\n/g, '<br>');
                }
            } catch (err) {
                console.error("Gagal scan OCR file: " + file.name, err);
                statusDiv.className = 'ocr-status ocr-failed';
                statusDiv.innerText = '❌ Gagal';
                detailDiv.innerText = 'Error: ' + err.message;
            }
        }
        
        updateTableFromPairings();
    } catch (err) {
        console.error("Gagal mengaktifkan Tesseract worker", err);
        alert("Gagal menginisialisasi engine Tesseract OCR: " + err.message);
    } finally {
        if (worker) {
            await worker.terminate();
        }
    }
}

function updateTableFromPairings() {
    const rows = document.querySelectorAll('#tableContainer tbody tr');
    rows.forEach(row => {
        row.querySelector('[data-field="clock"]').innerText = '';
        row.querySelector('[data-field="channel"]').innerText = '';
        row.querySelector('[data-field="clients"]').innerText = '';
        row.querySelector('[data-field="utilization"]').innerText = '';
        row.querySelector('[data-field="load"]').innerText = '';
        row.querySelector('[data-field="signal"]').innerText = '';
        row.querySelector('[data-field="download"]').innerText = '';
        row.querySelector('[data-field="upload"]').innerText = '';
        row.querySelector('[data-field="ping"]').innerText = '';
        row.querySelector('[data-field="jitter"]').innerText = '';
    });
    
    pairings.forEach(p => {
        if (!p.pairedMac) return;
        
        const row = findRowByMac(p.pairedMac);
        if (!row) return;
        
        row.classList.remove('row-highlight');
        void row.offsetWidth;
        row.classList.add('row-highlight');
        
        if (p.wifimanOcr && p.wifimanOcr.type === 'wifiman') {
            const parsed = p.wifimanOcr;
            if (parsed.channel) row.querySelector('[data-field="channel"]').innerText = parsed.channel;
            if (parsed.clients !== null) row.querySelector('[data-field="clients"]').innerText = parsed.clients;
            if (parsed.utilization) {
                row.querySelector('[data-field="utilization"]').innerText = parsed.utilization;
            }
            if (parsed.signal) row.querySelector('[data-field="signal"]').innerText = parsed.signal;
        }
        
        if (p.speedtestOcr && p.speedtestOcr.type === 'speedtest') {
            const parsed = p.speedtestOcr;
            if (parsed.download) row.querySelector('[data-field="download"]').innerText = parsed.download;
            if (parsed.upload) row.querySelector('[data-field="upload"]').innerText = parsed.upload;
            if (parsed.ping) row.querySelector('[data-field="ping"]').innerText = parsed.ping;
            if (parsed.jitter) row.querySelector('[data-field="jitter"]').innerText = parsed.jitter;
        }
    });
}

// =====================================================
// OCR REGIONAL: Crop gambar ke area DL/UL, lalu OCR ulang
// Digunakan sebagai fallback saat full-image OCR gagal baca angka stylized
// =====================================================
async function cropAndOcrRegion(imgFile, worker, region) {
    return new Promise((resolve) => {
        const img = new Image();
        const url = URL.createObjectURL(imgFile);
        img.onload = async () => {
            try {
                const w = img.naturalWidth;
                const h = img.naturalHeight;

                // Area kotak Download/Upload berada di ~8%-38% dari atas layar
                const cropY = Math.round(h * 0.08);
                const cropH = Math.round(h * 0.30);
                let cropX, cropW;

                if (region === 'download') {
                    cropX = 0;
                    cropW = Math.round(w * 0.50);
                } else {
                    cropX = Math.round(w * 0.50);
                    cropW = Math.round(w * 0.50);
                }

                // Gambar ke canvas, perbesar 2x agar OCR lebih akurat
                const canvas = document.createElement('canvas');
                canvas.width = cropW * 2;
                canvas.height = cropH * 2;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, cropX, cropY, cropW, cropH, 0, 0, cropW * 2, cropH * 2);

                // Inversi warna: angka putih di bg gelap → angka gelap di bg terang
                // Tesseract lebih akurat dengan teks gelap di bg terang
                const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
                const d = imageData.data;
                for (let j = 0; j < d.length; j += 4) {
                    d[j]     = 255 - d[j];     // R
                    d[j + 1] = 255 - d[j + 1]; // G
                    d[j + 2] = 255 - d[j + 2]; // B
                    // alpha d[j+3] dibiarkan
                }
                ctx.putImageData(imageData, 0, 0);

                const result = await worker.recognize(canvas);
                const text = result.data.text;

                // Cari angka pertama yang muncul (bisa 133, 15,4, 1,56, dll)
                const match = text.match(/\b\d+(?:[.,]\d+)?\b/);
                resolve(match ? match[0] : null);
            } catch (e) {
                resolve(null);
            } finally {
                URL.revokeObjectURL(url);
            }
        };
        img.onerror = () => { URL.revokeObjectURL(url); resolve(null); };
        img.src = url;
    });
}

function parseOcrData(text) {
    const textLower = text.toLowerCase();
    const lines = text.split('\n');
    
    const hasSpeedtestWord = textLower.includes('speedtest') || /spe+d\s*te?st/i.test(textLower);
    const hasDlWord = /down\s*[l1ioa]+d/i.test(textLower);
    const hasUlWord = /up\s*[l1ioa]+d/i.test(textLower);
    
    let isWifiman = false;
    let isSpeedtest = false;
    let mac = null;
    
    if (hasSpeedtestWord) {
        isSpeedtest = true;
    } else {
        const macRegex = /\b([0-9a-fA-F]{2}[:\-.;\s]){5}[0-9a-fA-F]{2}\b/g;
        const macMatches = text.match(macRegex);
        if (macMatches) {
            mac = macMatches[0].replace(/[\-.;\s]/g, ':').toLowerCase().trim();
            isWifiman = true;
        } else {
            const cleanHexRegex = /\b[0-9a-fA-F]{12}\b/g;
            const cleanText = text.replace(/[:\-.;\s]/g, '');
            const cleanMatches = cleanText.match(cleanHexRegex);
            if (cleanMatches) {
                const clean = cleanMatches[0].toLowerCase();
                mac = clean.match(/.{1,2}/g).join(':');
                isWifiman = true;
            }
        }
        
        if (textLower.includes('access point') || textLower.includes('dbm')) {
            isWifiman = true;
        }
        
        if (!isWifiman && (hasDlWord || hasUlWord || textLower.includes('ping') || textLower.includes('jitter'))) {
            isSpeedtest = true;
        }
    }
    
    let type = 'unknown';
    let signal = null;
    let channel = null;
    let utilization = null;
    let clients = null;
    let download = null;
    let upload = null;
    let ping = null;
    let jitter = null;
    
    if (isWifiman) {
        type = 'wifiman';
        
        // === SIGNAL: Jangan ambil jika ada "No Signal", dan jangan ambil dari baris Transmit Power ===
        const hasNoSignal = /no\s*signal/i.test(text);
        if (!hasNoSignal) {
            for (let line of lines) {
                const lineLower = line.toLowerCase();
                // Lewati baris Transmit Power — bukan signal strength
                if (lineLower.includes('transmit') || lineLower.includes('power') || lineLower.includes('tx')) continue;
                // Cari baris yang ada dBm, khusus baris yang relevan dengan signal strength
                const sigMatch = line.match(/(-\d+)\s*dBm/i); // Signal selalu negatif
                if (sigMatch) {
                    signal = sigMatch[1] + " dBm";
                    break;
                }
            }
        }
        
        // === CHANNEL: 3 tahap dari paling bersih ke paling agresif ===
        // Nomor channel WiFi valid: 1-196 (2.4 GHz: 1-14, 5 GHz: 36-165, 6 GHz hingga 196)
        
        // Tahap 1: Header bar WiFiMan — "Channel: 153" (paling bersih)
        const chHeaderMatch = text.match(/Channel\s*[:\s·•]+(\d+)(?!\d*[+\-=])/i);
        if (chHeaderMatch) {
            const cand = parseInt(chHeaderMatch[1], 10);
            if (cand >= 1 && cand <= 196) channel = chHeaderMatch[1];
        }
        
        // Tahap 2: Baris detail "CH 153 ·" dengan spasi eksplisit
        if (!channel) {
            for (let line of lines) {
                if (/width|wide/i.test(line)) continue;
                const m = line.match(/\bCH\s+(\d{1,3})\b/i);
                if (m) {
                    const cand = parseInt(m[1], 10);
                    if (cand >= 1 && cand <= 196) { channel = m[1]; break; }
                }
            }
        }
        
        // Tahap 3: OCR garbled — "CH1531" (middot terbaca jadi "1") 
        // → ambil prefix 3 → 2 → 1 digit yang valid sebagai nomor channel
        if (!channel) {
            const chRaw = text.match(/\bCH\s*(\d+)/i);
            if (chRaw) {
                const numStr = chRaw[1];
                for (let len = Math.min(3, numStr.length); len >= 1; len--) {
                    const cand = parseInt(numStr.substring(0, len), 10);
                    if (cand >= 1 && cand <= 196) {
                        channel = numStr.substring(0, len);
                        break;
                    }
                }
            }
        }
        
        // === FREKUENSI: Ambil frekuensi pertama dari baris channel ===
        // Cari angka 4-digit yang merupakan frekuensi WiFi (5GHz: 5xxx, 2.4GHz: 24xx, 6GHz: 6xxx)
        if (channel) {
            let freq = null;
            for (let line of lines) {
                if (/width|wide/i.test(line)) continue;
                if (!/ch/i.test(line) && !/channel/i.test(line)) continue;
                
                // Cari angka 4-digit yang diawali dengan 5 (5GHz), 24 (2.4GHz), atau 6 (6GHz)
                const freqMatch = line.match(/(5\d{3}|24\d{2}|6\d{3})/);
                if (freqMatch) {
                    freq = freqMatch[1];
                    break;
                }
            }
            // Format akhir: "CH 153 5755"
            channel = freq ? `CH ${channel} ${freq}` : `CH ${channel}`;
        }
        

        for (let i = 0; i < lines.length; i++) {
            const line = lines[i];
            if (/utili/i.test(line) || /util/i.test(line)) {
                const pctMatch = line.match(/(\d+)\s*%/);
                if (pctMatch) {
                    utilization = pctMatch[1] + " %";
                    break;
                }
                if (i + 1 < lines.length) {
                    const nextPct = lines[i+1].match(/(\d+)\s*%/);
                    if (nextPct) {
                        utilization = nextPct[1] + " %";
                        break;
                    }
                }
            }
        }
        if (!utilization) {
            const fallbackPct = text.match(/(\d+)\s*%/);
            if (fallbackPct) {
                utilization = fallbackPct[1] + " %";
            }
        }
        
        for (let i = 0; i < lines.length; i++) {
            const line = lines[i];
            if (/client/i.test(line)) {
                const idx = line.toLowerCase().indexOf('client');
                const afterClients = line.substring(idx + 6);
                const numMatch = afterClients.match(/\b\d+\b/);
                if (numMatch) {
                    clients = parseInt(numMatch[0]);
                    break;
                }
                if (i + 1 < lines.length) {
                    const nextNum = lines[i+1].match(/^\s*(\d+)\s*$/);
                    if (nextNum) {
                        clients = parseInt(nextNum[1]);
                        break;
                    }
                }
            }
        }
    } else if (isSpeedtest) {
        type = 'speedtest';
        
        for (let i = 0; i < lines.length; i++) {
            const line = lines[i].toLowerCase();
            
            // 1. Download & Upload (Abaikan jika baris ini berisi data Ping/Jitter)
            const isPingLine = line.includes('ping') || line.includes('latency') || line.includes('jitter');
            const hasDownload = !isPingLine && /down\s*[l1ioa]+d/i.test(line);
            const hasUpload = !isPingLine && /up\s*[l1ioa]+d/i.test(line);
            
            if (hasDownload && hasUpload) {
                const dlIdx = line.search(/down\s*[l1ioa]+d/i);
                const ulIdx = line.search(/up\s*[l1ioa]+d/i);
                
                let dlZone = "";
                let ulZone = "";
                if (dlIdx < ulIdx) {
                    dlZone = line.substring(dlIdx, ulIdx);
                    ulZone = line.substring(ulIdx);
                } else {
                    ulZone = line.substring(ulIdx, dlIdx);
                    dlZone = line.substring(dlIdx);
                }
                
                const dlMatch = dlZone.match(/\b\d+(?:[.,]\d+)?\b/);
                const ulMatch = ulZone.match(/\b\d+(?:[.,]\d+)?\b/);
                
                if (dlMatch) download = dlMatch[0];
                if (ulMatch) upload = ulMatch[0];
                
                if (!download && !upload) {
                    let foundNumbers = [];
                    for (let offset = 1; offset <= 6; offset++) {
                        const nextLine = lines[i + offset];
                        if (!nextLine) break;
                        // Lewati baris yang mengandung Ping/Jitter agar tidak terambil sebagai DL/UL
                        const nextLineLower = nextLine.toLowerCase();
                        if (nextLineLower.includes('ping') || nextLineLower.includes('jitter') || nextLineLower.includes('latency')) continue;
                        const lineNums = nextLine.match(/\b\d+(?:[.,]\d+)?\b/g);
                        if (lineNums) {
                            foundNumbers.push(...lineNums);
                        }
                        if (foundNumbers.length >= 2) break;
                    }
                    if (foundNumbers.length >= 2) {
                        download = foundNumbers[0];
                        upload = foundNumbers[1];
                    }
                }
            } else if (hasDownload && !download) {
                const cleanLine = line.replace(/down\s*[l1ioa]+d/gi, '');
                const match = cleanLine.match(/\b\d+(?:[.,]\d+)?\b/);
                if (match) {
                    download = match[0];
                } else if (i + 1 < lines.length) {
                    const nextMatch = lines[i+1].match(/\b\d+(?:[.,]\d+)?\b/);
                    if (nextMatch) download = nextMatch[0];
                }
            } else if (hasUpload && !upload) {
                const cleanLine = line.replace(/up\s*[l1ioa]+d/gi, '');
                const match = cleanLine.match(/\b\d+(?:[.,]\d+)?\b/);
                if (match) {
                    upload = match[0];
                } else if (i + 1 < lines.length) {
                    const nextMatch = lines[i+1].match(/\b\d+(?:[.,]\d+)?\b/);
                    if (nextMatch) upload = nextMatch[0];
                }
            }
            
            // 2. Ping & Jitter
            const hasPing = line.includes('ping') || line.includes('latency');
            const hasJitter = line.includes('jitter');
            
            if (hasPing && hasJitter) {
                const pingIdx = line.indexOf('ping') !== -1 ? line.indexOf('ping') : line.indexOf('latency');
                const jitterIdx = line.indexOf('jitter');
                
                let pingZone = "";
                let jitterZone = "";
                if (pingIdx < jitterIdx) {
                    pingZone = line.substring(pingIdx, jitterIdx);
                    jitterZone = line.substring(jitterIdx);
                } else {
                    jitterZone = line.substring(jitterIdx, pingIdx);
                    pingZone = line.substring(pingIdx);
                }
                
                const pMatch = pingZone.match(/\b\d+\b/);
                const jMatch = jitterZone.match(/\b\d+\b/);
                
                if (pMatch) ping = pMatch[0];
                if (jMatch) jitter = jMatch[0];
                
                if (!ping && !jitter) {
                    let foundNumbers = [];
                    for (let offset = 1; offset <= 4; offset++) {
                        const nextLine = lines[i + offset];
                        if (nextLine) {
                            const lineNums = nextLine.match(/\b\d+\b/g);
                            if (lineNums) {
                                foundNumbers.push(...lineNums);
                            }
                        }
                        if (foundNumbers.length >= 2) break;
                    }
                    if (foundNumbers.length >= 2) {
                        ping = foundNumbers[0];
                        jitter = foundNumbers[1];
                    }
                }
            } else if (hasPing && !ping) {
                const cleanLine = line.replace(/(?:ping|latency)/g, '');
                const match = cleanLine.match(/\b\d+\b/);
                if (match) {
                    ping = match[0];
                } else if (i + 1 < lines.length) {
                    const nextMatch = lines[i+1].match(/\b\d+\b/);
                    if (nextMatch) ping = nextMatch[0];
                }
            } else if (hasJitter && !jitter) {
                const cleanLine = line.replace(/jitter/g, '');
                const match = cleanLine.match(/\b\d+\b/);
                if (match) {
                    jitter = match[0];
                } else if (i + 1 < lines.length) {
                    const nextMatch = lines[i+1].match(/\b\d+\b/);
                    if (nextMatch) jitter = nextMatch[0];
                }
            }
        }
        
        // Fallback global scans if still not detected
        if (!download) {
            const dlMatch = text.match(/down\s*[l1ioa]+d\s*[:\-]*\s*(\d+(?:[.,]\d+)?)/i);
            if (dlMatch) download = dlMatch[1];
        }
        if (!upload) {
            const ulMatch = text.match(/up\s*[l1ioa]+d\s*[:\-]*\s*(\d+(?:[.,]\d+)?)/i);
            if (ulMatch) upload = ulMatch[1];
        }
        if (!ping) {
            const pingMatch = text.match(/ping\s*[:\-]*\s*(\d+)/i);
            if (pingMatch) ping = pingMatch[1];
        }
        if (!jitter) {
            const jitterMatch = text.match(/jitter\s*[:\-]*\s*(\d+)/i);
            if (jitterMatch) jitter = jitterMatch[1];
        }
    }
    
    return {
        type,
        mac,
        signal,
        channel,
        utilization,
        clients,
        download,
        upload,
        ping,
        jitter
    };
}

function findRowByMac(mac) {
    if (!mac) return null;
    const cleanMac = mac.replace(/[:.-]/g, '').toLowerCase().trim();
    const rows = document.querySelectorAll('#tableContainer tbody tr');
    
    for (let row of rows) {
        const rowMac = row.getAttribute('data-mac');
        if (rowMac) {
            const cleanRowMac = rowMac.replace(/[:.-]/g, '').toLowerCase().trim();
            if (cleanRowMac === cleanMac) return row;
        }
    }
    
    let bestRow = null;
    let maxMatches = 0;
    
    for (let row of rows) {
        const rowMac = row.getAttribute('data-mac');
        if (rowMac) {
            const cleanRowMac = rowMac.replace(/[:.-]/g, '').toLowerCase().trim();
            let matches = 0;
            for (let i = 0; i < Math.min(cleanRowMac.length, cleanMac.length); i++) {
                if (cleanRowMac[i] === cleanMac[i]) matches++;
            }
            if (matches > maxMatches && matches >= 9) {
                maxMatches = matches;
                bestRow = row;
            }
        }
    }
    
    return bestRow;
}

function copyTableToClipboard() {
    const table = document.querySelector("#tableContainer table");
    if (!table) {
        alert("Tabel kosong atau belum dibuat!");
        return;
    }
    
    const clone = table.cloneNode(true);
    clone.querySelectorAll('[contenteditable]').forEach(el => {
        el.removeAttribute('contenteditable');
        el.style.backgroundColor = '';
    });
    clone.querySelectorAll('.row-highlight').forEach(el => {
        el.classList.remove('row-highlight');
    });
    
    const container = document.createElement('div');
    container.style.position = 'absolute';
    container.style.left = '-9999px';
    container.appendChild(clone);
    document.body.appendChild(container);
    
    const range = document.createRange();
    range.selectNode(clone);
    const selection = window.getSelection();
    selection.removeAllRanges();
    selection.addRange(range);
    
    try {
        document.execCommand('copy');
        alert("Tabel berhasil disalin ke clipboard! Silakan paste (Ctrl+V) langsung ke Microsoft Excel.");
    } catch (err) {
        alert("Gagal menyalin tabel: " + err);
    }
    
    document.body.removeChild(container);
    selection.removeAllRanges();
}

function prosesDataDanDownload() {
    const shift = document.getElementById('shiftSelect').value;
    const startNumber = document.getElementById('startNumber').value;
    
    const filesToUpload = [];
    
    currentShiftAps.forEach(ap => {
        const mac = ap.mac_address.toLowerCase().trim();
        
        const pair = pairings.find(p => p.pairedMac === mac);
        if (pair) {
            const pairIdx = pairings.indexOf(pair);
            
            const wifimanFile = wifimanFiles[pairIdx];
            if (wifimanFile) {
                filesToUpload.push(wifimanFile);
            }
            
            const speedtestFile = speedtestFiles[pairIdx];
            if (speedtestFile) {
                filesToUpload.push(speedtestFile);
            }
        }
    });
    
    if (filesToUpload.length === 0) {
        alert("Tidak ada file foto yang terasosiasi dengan AP di tabel shift saat ini!");
        return;
    }
    
    alert(`Mempersiapkan data dan membuat folder ZIP. Mengunggah ${filesToUpload.length} gambar terurut ke backend...`);
    
    const formData = new FormData();
    formData.append('shift', shift);
    formData.append('start_number', startNumber);
    
    const fileOrder = filesToUpload.map(f => f.name);
    formData.append('file_order', JSON.stringify(fileOrder));
    
    filesToUpload.forEach(fileObj => {
        formData.append('images[]', fileObj);
    });

    // Kirim data ke backend proses.php
    fetch('proses.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.blob())
    .then(blob => {
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `Evidence_Hasil_Sorting_${shift}.zip`;
        document.body.appendChild(a);
        a.click();    
        a.remove();
    })
    .catch(err => alert("Gagal memproses file di server backend: " + err));
}

function showImageModal(src) {
    const modal = document.getElementById('imageModal');
    const modalImg = document.getElementById('modalImage');
    modalImg.src = src;
    modal.style.display = 'flex';
}

function closeImageModal(e) {
    const modal = document.getElementById('imageModal');
    if (e.target.id === 'imageModal' || e.target.classList.contains('close-modal')) {
        modal.style.display = 'none';
    }
}
</script>
</body>
</html>