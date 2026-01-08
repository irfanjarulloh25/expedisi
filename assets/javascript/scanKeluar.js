// Ambil elemen berdasarkan ID yang benar-benar ada di HTML
const supirInput = document.getElementById('supir'); // Dropdown
const btnPilih = document.getElementById('btnPilih'); // Tombol Pilih

// Elemen Input Resi
const resiInput = document.getElementById('resiInput');
const btnScan = document.getElementById('btnScan');
const listResi = document.getElementById('listResi');

// Elemen Hidden Input di dalam Form
const hiddenSupirId = document.getElementById('supir_id_input');
const hiddenResiList = document.getElementById('resi_list_input');
const btnSelesai = document.getElementById('btnSelesai');

let resiArray = [];

// Fungsi saat tombol PILIH diklik
btnPilih.onclick = () => {
    const supirValue = supirInput.value;

    if (!supirValue) {
        alert("Silakan pilih supir terlebih dahulu!");
        beep();
        return;
    }

    // MENGISI VALUE KE HIDDEN INPUT (Inilah yang tadi error)
    hiddenSupirId.value = supirValue;

    // Kunci UI Pemilihan
    supirInput.disabled = true;
    btnPilih.disabled = true;
    btnPilih.classList.add('opacity-50', 'cursor-not-allowed');

    // Buka Area Scan
    resiInput.disabled = false;
    btnScan.disabled = false;
    btnScan.classList.replace('bg-gray-500', 'bg-blue-600');

    // OTOMATIS PINDAH KE KOTAK RESI
    setTimeout(() => {
        resiInput.focus();
    }, 100);
};

// Fungsi Scan (Enter)
resiInput.addEventListener('keypress', e => {
    if (e.key === 'Enter') {
        e.preventDefault();
        addResi();
    }
});

btnScan.onclick = addResi;


function addResi() {
    const resi = resiInput.value.trim().toUpperCase();
    
    if (!resi) return;
    if (resiArray.includes(resi)) {
        beep();
        resiInput.value = '';
        return;
    }

    fetch('../../api/cekResi.php', {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: 'no_resi=' + encodeURIComponent(resi)
    })
    .then(r => r.json())
    .then(d => {
        if (d.valid) {
            resiArray.push(resi);
            
            // Tampilkan di list UI
            const li = document.createElement('li');
            li.className = "p-2 border-b bg-white flex justify-between";
            li.innerHTML = `<span>${resi}</span> <span class="text-green-600">✓</span>`;
            listResi.appendChild(li);
            
            resiInput.value = '';
            btnSelesai.disabled = false; // Aktifkan tombol simpan jika sudah ada resi
            resiInput.focus();
        } else {
            beep();
        }
    })
    .catch(err => console.error("Error:", err));
}

function submitData() {
    if (resiArray.length === 0) {
        alert('Belum ada resi yang discan!');
        return false;
    }
    // Masukkan list resi ke hidden input sebelum form dikirim ke PHP
    hiddenResiList.value = JSON.stringify(resiArray);
    return true;
}

function beep() {
    const audio = document.getElementById('beep');
    if (audio) audio.play();
}
