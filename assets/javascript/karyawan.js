// =============================
// PREVIEW FOTO
// =============================
function previewImage(event) {
    const reader = new FileReader();
    reader.onload = function() {
        document.getElementById('previewFoto').src = reader.result;
    }
    reader.readAsDataURL(event.target.files[0]);
}

// =============================
// PASSWORD LOGIC
// =============================
const roleSelect = document.getElementById('role_id');
const passwordInput = document.getElementById('password');
const btnEditPassword = document.getElementById('btnEditPassword');

// Fungsi toggle password sesuai role & mode (edit / tambah)
function togglePassword() {
    if (!roleSelect || !passwordInput) return;

    const isEditMode = !!btnEditPassword;

    if (roleSelect.value == 3) { // SUPIR
        // SUPIR → password disembunyikan & nonaktif
        passwordInput.value = '';
        passwordInput.disabled = true;
        passwordInput.classList.add('hidden');
        passwordInput.removeAttribute('required');

        if (btnEditPassword) btnEditPassword.classList.add('hidden');
    } else {
        // Non-SUPIR
        passwordInput.disabled = false;

        if (isEditMode) {
            // Edit mode → sembunyikan password awal, tombol edit muncul
            passwordInput.classList.add('hidden');
            passwordInput.removeAttribute('required');
            btnEditPassword.classList.remove('hidden');
        } else {
            // Tambah mode → tampilkan password langsung
            passwordInput.classList.remove('hidden');
            passwordInput.setAttribute('required', true);
        }
    }
}

// Jalankan saat load halaman
togglePassword();

// Jalankan saat ganti role
if (roleSelect) roleSelect.addEventListener('change', togglePassword);

// =============================
// BUTTON EDIT PASSWORD (edit mode)
// =============================
if (btnEditPassword && passwordInput) {
    btnEditPassword.addEventListener('click', function() {
        passwordInput.classList.remove('hidden');
        passwordInput.focus();
        passwordInput.setAttribute('required', true);
        btnEditPassword.style.display = 'none';
    });
}

// =============================
// OPTIONAL: Tombol BATAL (jika ada)
// =============================
const btnBatal = document.getElementById('btnBatal'); // tombol batal di HTML
if (btnBatal) {
    btnBatal.addEventListener('click', function() {
        window.location.href = 'karyawan.php'; // kembali ke daftar / mode tambah
    });
}
