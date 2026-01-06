/* assets/js/script.js */

document.addEventListener("DOMContentLoaded", function() {

    // 1. CEK NOTIFIKASI DARI URL (Query Param)
    const urlParams = new URLSearchParams(window.location.search);
    const pesan = urlParams.get('pesan');

    if (pesan) {
        if (pesan === 'sukses' || pesan === 'simpan') {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Data berhasil disimpan ke database.',
                timer: 2000,
                showConfirmButton: false
            });
        } else if (pesan === 'update') {
            Swal.fire({
                icon: 'success',
                title: 'Terupdate!',
                text: 'Data berhasil diperbarui.',
                timer: 2000,
                showConfirmButton: false
            });
        } else if (pesan === 'hapus') {
            Swal.fire({
                icon: 'success',
                title: 'Terhapus!',
                text: 'Data telah dihapus dari sistem.',
                timer: 2000,
                showConfirmButton: false
            });
        } else if (pesan === 'gagal_upload') {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Gagal mengupload file. Pastikan format & ukuran sesuai.',
            });
        }
        
        // Bersihkan URL agar notif tidak muncul lagi saat refresh
        window.history.replaceState({}, document.title, window.location.pathname);
    }

    // 2. INTERCEPT TOMBOL HAPUS (Konfirmasi SweetAlert)
    const tombolHapus = document.querySelectorAll('.btn-danger, .btn-outline-danger');
    
    tombolHapus.forEach(btn => {
        // Hanya berlaku jika tombol punya link href (bukan button form biasa)
        if (btn.getAttribute('href') && btn.getAttribute('href').includes('hapus')) {
            btn.addEventListener('click', function(e) {
                e.preventDefault(); // Stop link asli
                const href = this.getAttribute('href');

                Swal.fire({
                    title: 'Apakah Anda yakin?',
                    text: "Data yang dihapus tidak dapat dikembalikan!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = href; // Lanjut ke proses.php
                    }
                });
            });
        }
    });

    // 3. PREVIEW FOTO UPLOAD (Otomatis jalan di form tambah/edit)
    const inputFoto = document.querySelector('input[name="foto"]');
    
    if (inputFoto) {
        // Buat elemen img preview jika belum ada
        const previewContainer = document.createElement('div');
        previewContainer.className = 'mt-3 text-center';
        previewContainer.style.display = 'none'; // Sembunyikan dulu
        
        const imgPreview = document.createElement('img');
        imgPreview.style.maxWidth = '200px';
        imgPreview.style.maxHeight = '200px';
        imgPreview.className = 'img-thumbnail shadow-sm rounded';
        
        previewContainer.appendChild(imgPreview);
        inputFoto.parentNode.appendChild(previewContainer);

        inputFoto.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    imgPreview.src = e.target.result;
                    previewContainer.style.display = 'block';
                }
                reader.readAsDataURL(file);
            } else {
                previewContainer.style.display = 'none';
            }
        });
    }

});