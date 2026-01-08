const resiInput = document.getElementById('resiInput');
const btnScan = document.getElementById('btnScan');
const listResi = document.getElementById('listResi');
const resiListInput = document.getElementById('resi_list_input');
const beepAudio = document.getElementById('beep');
const userId = document.getElementById('user_id').value;

let resiArray = [];

function beep() {
    beepAudio.currentTime = 0;
    beepAudio.play().catch(e => console.log("Audio play blocked by browser. Interact with page first."));
}

function addResi() {
    const resi = resiInput.value.trim();
    if (!resi) return;

    // JIKA DUPLIKAT -> HANYA SUARA
    if (resiArray.includes(resi)) {
        resiInput.value = '';
        beep(); 
        return;
    }

    fetch('../../api/resiMasuk.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'no_resi=' + encodeURIComponent(resi) + '&karyawan_id=' + encodeURIComponent(userId)
    })
    .then(r => r.json())
    .then(d => {
        if (d.valid) {
            resiArray.push(resi);
            const li = document.createElement('li');
            li.className = 'px-4 py-2 border-b last:border-b-0 flex justify-between bg-white';
            li.innerHTML = `<span>${resi}</span> <span class="text-blue-500 font-mono text-sm">Terdata</span>`;
            listResi.appendChild(li);
            resiInput.value = '';
            resiInput.focus();
        } else {
            // JIKA RESI SALAH/TIDAK VALID -> HANYA SUARA
            resiInput.value = '';
            beep();
        }
    })
    .catch(err => {
        console.error(err);
        beep();
    });
}

btnScan.addEventListener('click', addResi);
resiInput.addEventListener('keypress', e => { if (e.key === 'Enter') { e.preventDefault(); addResi(); } });

window.submitData = function() {
    if (resiArray.length === 0) {
        beep();
        Swal.fire({ icon: 'error', title: 'Kosong', text: 'Belum ada resi yang di-scan!' });
        return false;
    }
    resiListInput.value = JSON.stringify(resiArray);
    return true;
};