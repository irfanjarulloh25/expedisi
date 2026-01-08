// ===================== COD toggle =====================
function toggleCOD() {
    const cod = document.getElementById('cod').value;
    document.getElementById('hargaCodBox').style.display = cod === 'ya' ? 'block' : 'none';
}

// ===================== Webcam capture =====================
const startCameraBtn = document.getElementById('startCamera');
const video = document.getElementById('video');
const snapBtn = document.getElementById('snap');
const canvas = document.getElementById('canvas');
const foto_data = document.getElementById('foto_data');
const capturedImg = document.getElementById('capturedImg');

startCameraBtn.addEventListener('click', async () => {
    try {
        const stream = await navigator.mediaDevices.getUserMedia({ video:true });
        video.srcObject = stream;
        video.style.display = 'block';
        snapBtn.style.display = 'inline-block';
        startCameraBtn.style.display = 'none';
        capturedImg.style.display = 'none';
    } catch(err) {
        Swal.fire({
            icon: 'error',
            title: 'Kamera Error',
            text: 'Tidak bisa mengakses kamera: ' + err.message
        });
    }
});

snapBtn.addEventListener('click', () => {
    canvas.getContext('2d').drawImage(video,0,0,canvas.width,canvas.height);
    const dataURL = canvas.toDataURL('image/png');
    foto_data.value = dataURL;
    capturedImg.src = dataURL;
    capturedImg.style.display = 'block';
    video.style.display = 'none';
    snapBtn.style.display = 'none';
});

capturedImg.addEventListener('click', () => {
    const modal = document.getElementById('modalImg');
    document.getElementById('modalContent').src = capturedImg.src;
    modal.style.display = 'flex';
});
document.getElementById('modalImg')?.addEventListener('click', () => {
    document.getElementById('modalImg').style.display = 'none';
});

// ===================== Autocomplete kota =====================
const kotaInput = document.getElementById('kota_penerima');
const kotaList = document.getElementById('kotaList');
let selectedKota = '';

kotaInput.addEventListener('input', async function(){
    const q = this.value.trim();
    kotaList.innerHTML = '';
    selectedKota = '';

    if (!q) return;

    const formData = new FormData();
    formData.append('q', q);

    try {
        const response = await fetch('../../api/getKota.php', { method: 'POST', body: formData });
        const result = await response.json();

        result.forEach(kota => {
            const div = document.createElement('div');
            div.textContent = kota;
            div.className = 'cursor-pointer px-3 py-1 hover:bg-gray-200';
            div.addEventListener('click', () => {
                kotaInput.value = kota;
                selectedKota = kota;
                kotaList.innerHTML = '';
                updateHargaOngkir();
            });
            kotaList.appendChild(div);
        });
    } catch(err) {
        console.error('Error fetch kota:', err);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Gagal memuat daftar kota'
        });
    }
});

document.addEventListener('click', e => {
    if (!kotaList.contains(e.target) && e.target !== kotaInput) {
        kotaList.innerHTML = '';
    }
});

// ===================== Update harga ongkir =====================
async function updateHargaOngkir() {
    const berat = parseFloat(document.getElementById('berat').value);
    const kota = selectedKota;

    if (!berat || !kota) {
        document.getElementById('harga_ongkir').value = '';
        return;
    }

    const formData = new FormData();
    formData.append('berat', berat);
    formData.append('kota', kota);

    try {
        const response = await fetch('../../api/getHarga.php', { method:'POST', body: formData });
        const data = await response.json();
        document.getElementById('harga_ongkir').value = data.harga ?? 0;
    } catch(err) {
        console.error('Error fetch harga ongkir:', err);
        document.getElementById('harga_ongkir').value = 0;
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Gagal menghitung ongkir'
        });
    }
}

document.getElementById('berat').addEventListener('input', updateHargaOngkir);

// ===================== AJAX simpan paket =====================
document.getElementById('btnSimpan').addEventListener('click', async () => {
    const form = document.getElementById('formPaket');

    if (!foto_data.value) {
        Swal.fire({
            icon: 'warning',
            title: 'Foto paket belum diambil!',
            text: 'Harap ambil foto sebelum menyimpan.'
        });
        return;
    }

    const formData = new FormData(form);
    formData.append('ajax','1');

    const result = await Swal.fire({
        title: 'Apakah Anda yakin?',
        text: 'Data paket akan disimpan',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, simpan',
        cancelButtonText: 'Batal'
    });

    if (!result.isConfirmed) return;

    try {
        const response = await fetch(window.location.href, { method:'POST', body:formData });
        const data = await response.json();

        if (data.success) {
            // JANGAN reload dulu, panggil fungsi cetak/tampil resi
            showResultModal(data, formData);
        } else {
            Swal.fire({ icon: 'error', title: 'Gagal', text: data.message });
        }
    } catch(err) {
        Swal.fire({ icon: 'error', title: 'Error', text: err.message });
    }
});

// Fungsi untuk menampilkan Modal Resi & Generate Barcode
function showResultModal(data, formData) {
    // 1. Isi data ke elemen modal
    document.getElementById('res_no_resi').innerText = data.no_resi;
    document.getElementById('res_gudang').innerText = data.nama_gudang;
    document.getElementById('res_penerima').innerText = formData.get('nama_penerima');
    document.getElementById('res_alamat_penerima').innerText = `${formData.get('alamat_penerima')}, ${formData.get('kota_penerima')}`;
    document.getElementById('res_pengirim').innerText = formData.get('nama_pengirim');
    document.getElementById('res_berat').innerText = formData.get('berat');
    document.getElementById('res_qty').innerText = formData.get('qty');
    
    const tipe = formData.get('cod') === 'ya' ? 'COD' : 'NON-COD';
    document.getElementById('res_tipe').innerText = tipe;

    if (formData.get('cod') === 'ya') {
        document.getElementById('res_cod_info').classList.remove('hidden');
        document.getElementById('res_harga_cod').innerText = "Rp " + parseInt(formData.get('harga_cod')).toLocaleString('id-ID');
    } else {
        document.getElementById('res_cod_info').classList.add('hidden');
    }

    // 2. Generate Barcode (Pastikan library JsBarcode sudah di-load di HTML)
    JsBarcode("#barcodeCanvas", data.no_resi, {
        format: "CODE128",
        width: 2,
        height: 50,
        displayValue: false // Kita tampilkan manual di text
    });

    // 3. Tampilkan Modal
    document.getElementById('modalResi').classList.remove('hidden');
}

// Fungsi Cetak (Optimasi untuk Printer Thermal)
function printResi() {
    const printContent = document.getElementById('printableArea').innerHTML;
    const win = window.open('', '', 'height=500,width=800');
    win.document.write('<html><head><title>Cetak Resi</title>');
    win.document.write('<script src="https://cdn.tailwindcss.com"></script>'); // Agar styling terbawa
    win.document.write('</head><body onload="window.print();window.close();">');
    win.document.write(`<div style="width: 78mm; font-family: sans-serif; padding: 10px;">${printContent}</div>`);
    win.document.write('</body></html>');
    win.document.close();
}

function closeAndReload() {
    location.reload();
}
