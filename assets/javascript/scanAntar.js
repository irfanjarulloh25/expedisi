// Inisialisasi Element
const kurirDropdown = document.getElementById("kurir");
const resiInput = document.getElementById("resiInput");
const btnPilih = document.getElementById("btnPilih");
const btnScan = document.getElementById("btnScan");
const listResi = document.getElementById("listResi");
const btnSelesai = document.getElementById("btnSelesai");

// Elemen Hidden untuk Form POST
const hiddenKurirId = document.getElementById("kurir_id_input");
const hiddenResiList = document.getElementById("resi_list_input");

let resiArray = [];

// 1. Logika Pilih Kurir
btnPilih.onclick = () => {
  const kurirVal = kurirDropdown.value;
  if (!kurirVal) {
    beep();
    alert("Silakan pilih kurir terlebih dahulu!");
    return;
  }

  // Set value ke hidden input untuk form submit
  hiddenKurirId.value = kurirVal;

  // Lock dropdown agar tidak berubah di tengah jalan
  kurirDropdown.disabled = true;
  btnPilih.disabled = true;
  btnPilih.classList.replace("bg-blue-600", "bg-gray-400");

  // Aktifkan input scan
  resiInput.disabled = false;
  btnScan.disabled = false;
  btnScan.classList.replace("bg-gray-500", "bg-blue-600");
  resiInput.focus();
};

// 2. Logika Scan / Tambah Resi
btnScan.onclick = addResi;
resiInput.addEventListener("keydown", (e) => {
  if (e.key === "Enter") {
    e.preventDefault();
    addResi();
  }
});

function addResi() {
  const resi = resiInput.value.trim().toUpperCase();

  if (!resi) return;
  if (resiArray.includes(resi)) {
    beep();
    resiInput.value = "";
    return;
  }

  fetch("../../api/resiAntar.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: "no_resi=" + encodeURIComponent(resi),
  })
    .then((r) => r.json())
    .then((d) => {
      if (d.valid) {
        resiArray.push(resi);

        // Tampilkan di list UI
        const li = document.createElement("li");
        li.className = "p-2 border-b bg-white flex justify-between";
        li.innerHTML = `<span>${resi}</span> <span class="text-green-600">✓</span>`;
        listResi.appendChild(li);

        resiInput.value = "";
        btnSelesai.disabled = false; // Aktifkan tombol simpan jika sudah ada resi
        resiInput.focus();
      } else {
        beep();
      }
    })
    .catch((err) => console.error("Error:", err));
}

// 3. Fungsi Hapus Resi dari Daftar
function hapusResi(resi, element) {
  // Hapus dari array
  resiArray = resiArray.filter((item) => item !== resi);
  // Hapus dari UI
  element.closest("li").remove();
  // Nonaktifkan tombol selesai jika daftar kosong
  if (resiArray.length === 0) {
    btnSelesai.disabled = true;
  }
}

// 4. Validasi Final sebelum Submit Form
function submitData() {
  if (resiArray.length === 0) {
    alert("Belum ada resi yang di-scan!");
    return false;
  }
  // Konversi array ke JSON string untuk dikirim ke PHP utama
  hiddenResiList.value = JSON.stringify(resiArray);
  return true;
}

function beep() {
  const snd = document.getElementById("beep");
  if (snd) snd.play();
}
