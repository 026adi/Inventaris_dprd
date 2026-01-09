/* assets/js/script.js */

document.addEventListener("DOMContentLoaded", function() {

    // 1. CEK NOTIFIKASI DARI URL (Query Param)
    const urlParams = new URLSearchParams(window.location.search);
    const pesan = urlParams.get('pesan');

    if (pesan) {
        let title = '';
        let text = '';
        let icon = 'success';

        // --- Logika Pesan Inventaris & Mobil ---
        if (pesan === 'sukses' || pesan === 'simpan' || pesan === 'berhasil_pinjam') {
            title = 'Berhasil!';
            text = 'Data berhasil disimpan ke database.';
        } 
        else if (pesan === 'update') {
            title = 'Terupdate!';
            text = 'Data berhasil diperbarui.';
        } 
        else if (pesan === 'hapus' || pesan === 'hapus_riwayat') {
            title = 'Terhapus!';
            text = 'Data telah dihapus dari sistem.';
        }
        else if (pesan === 'mobil_kembali') {
            title = 'Selesai!';
            text = 'Status mobil berhasil diperbarui menjadi Tersedia.';
        }
        else if (pesan === 'dibatalkan') {
            title = 'Dibatalkan!';
            text = 'Transaksi dibatalkan, stok dikembalikan.';
        }
        // --- Pesan Error ---
        else if (pesan === 'gagal' || pesan === 'gagal_db') {
            title = 'Gagal!';
            text = 'Terjadi kesalahan sistem database.';
            icon = 'error';
        }
        else if (pesan === 'gagal_upload') {
            title = 'Upload Gagal!';
            text = 'Format file salah atau ukuran terlalu besar.';
            icon = 'error';
        }
        else if (pesan === 'stok_kurang') {
            title = 'Stok Tidak Cukup!';
            text = 'Jumlah barang yang diminta melebihi stok tersedia.';
            icon = 'warning';
        }
        else if (pesan === 'tidak_tersedia') {
            title = 'Mobil Tidak Tersedia!';
            text = 'Mobil sedang dipinjam oleh unit lain.';
            icon = 'warning';
        }
        else if (pesan === 'sudah_kembali') {
            title = 'Info';
            text = 'Mobil ini sudah dikembalikan sebelumnya.';
            icon = 'info';
        }

        // Tampilkan SweetAlert
        Swal.fire({
            icon: icon,
            title: title,
            text: text,
            timer: 2500, // Durasi sedikit diperlama
            showConfirmButton: false
        });
        
        // Bersihkan URL agar notif tidak muncul lagi saat refresh
        window.history.replaceState({}, document.title, window.location.pathname);
    }

    // 2. INTERCEPT TOMBOL HAPUS (Konfirmasi SweetAlert)
    // Berlaku untuk tombol hapus & tombol pengembalian mobil
    const tombolAksi = document.querySelectorAll('.btn-danger, .btn-outline-danger, .btn-success');
    
    tombolAksi.forEach(btn => {
        // Cek apakah tombol ini tombol hapus atau kembalikan mobil
        const href = btn.getAttribute('href');
        const onclick = btn.getAttribute('onclick'); // Cek apakah ada onclick native

        if (href && (href.includes('hapus') || (href.includes('kembali') && !onclick))) {
            
            btn.addEventListener('click', function(e) {
                e.preventDefault(); // Stop link asli

                let title = 'Apakah Anda yakin?';
                let text = "Data yang dihapus tidak dapat dikembalikan!";
                let confirmBtn = 'Ya, Hapus!';
                let confirmColor = '#d33';

                // Jika tombol kembali mobil
                if (href.includes('kembali')) {
                    title = 'Konfirmasi Pengembalian';
                    text = "Pastikan mobil sudah dicek fisiknya.";
                    confirmBtn = 'Ya, Mobil Kembali';
                    confirmColor = '#198754'; // Hijau
                }

                Swal.fire({
                    title: title,
                    text: text,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: confirmColor,
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: confirmBtn,
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = href; 
                    }
                });
            });
        }
    });

    // 3. TOMBOL LOGOUT
    const btnLogout = document.querySelector('a[href*="logout.php"]');
    if (btnLogout) {
        btnLogout.addEventListener('click', function(e) {
            e.preventDefault();
            const href = this.getAttribute('href');

            Swal.fire({
                title: 'Keluar Sistem?',
                text: "Anda harus login kembali untuk mengakses halaman ini.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Logout'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = href;
                }
            });
        });
    }

});