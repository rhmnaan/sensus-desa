# Catatan Aplikasi Sensus Ekonomi Desa

Cara pakai: tandai yang selesai dengan mengubah `[ ]` menjadi `[x]`.
Di VS Code dengan ekstensi Markdown All in One, tekan `Alt+C` di baris itu.

Legenda: `[x]` selesai, `[ ]` belum. Tulis catatan singkat di belakang tanda `-` kalau ada kendala.

## Info cepat (jangan tulis password di sini)

- Laptop 1 (Publisher/Distributor): LAPTOP-SDTHFIOF
- Laptop 2 (Subscriber): LAPTOP-RC62Q4BQ
- Database: sensus_desa (18 tabel)
- Replikasi: Snapshot (master), Transactional (periode dan skor), Merge (input petugas)
- Akun contoh di tabel users: admin, petugas1, petugas2, pimpinan

### Akun yang dibuat

| Username | Role |
|---|---|
| `admin` | admin123 |
| `petugas1` | petugas123 |
| `petugas2` | petugas123 |
| `pimpinan` | pimpinan123 |

Password akun sudah diatur di database. Jangan simpan password plaintext di catatan atau repositori.

## Keputusan yang masih perlu diambil

- [ ] Rumus skor kesejahteraan (bobot dan ambang) disetujui
- [ ] Status "Draf" pendataan: dihapus (disarankan) atau tambah kolom status
- [ ] Petugas dibatasi per wilayah? Kalau ya, tambah kolom wilayah di users SEBELUM publikasi Merge dibuat
- [ ] Dosen: Windows 11 langsung dan VM boleh? Merge wajib atau cukup teori?

## 1. Fondasi aplikasi

- [x] Skema database 18 tabel (sensus_desa.sql)
- [x] Akun contoh di tabel users
- [x] Layout dengan sidebar per peran, CSS menyatu (tanpa CDN)
- [x] Login dan logout dari tabel users
- [x] Pembatas akses per peran (PeranMiddleware)
- [x] Model: User, Keluarga, Pendataan, PeriodeSensus
- [ ] Driver sqlsrv aktif di PHP (php -m menampilkan pdo_sqlsrv dan sqlsrv) - Laptop 1
- [ ] php artisan db:show berhasil, login berhasil
- [ ] Model lain: Dusun, Rw, Rt, AnggotaKeluarga, DataEkonomi, KondisiRumah, AsetKeluarga, BantuanSosial, JenisAset, JenisBantuan, Pekerjaan, JenisUsaha, KategoriKesejahteraan, PenilaianKesejahteraan
- [ ] Pesan sukses dan gagal (flash message) di layout
- [ ] Ganti password dan profil

## 2. Petugas

- [x] Dashboard (periode aktif, 4 kartu statistik, progres, 5 keluarga menunggu, pintasan)
- [x] Tampilan Daftar Keluarga
- [x] Tampilan Tambah Keluarga
- [x] Tampilan Form Pendataan (4 langkah; menu sidebar membuka pemilihan keluarga)
- [x] Tampilan Status Sinkron (ringkasan, riwayat asli jika tersedia, tombol sinkron, arti konflik)
- [x] Daftar Keluarga membaca database (pencarian, filter RT/status, pagination 8 baris)
- [x] Tambah Keluarga menyimpan ke database (keluarga dan anggota)
- [x] Validasi: No. KK 16 digit unik, NIK 16 digit unik, satu kepala keluarga, minimal satu anggota
- [x] Ubah Keluarga
- [x] Detail Keluarga (anggota, riwayat pendataan)
- [x] Form Pendataan menyimpan (data_ekonomi, kondisi_rumah, aset_keluarga, bantuan_sosial)
- [x] Form Pendataan terhubung ke keluarga yang dipilih (tersedia dari pemilih keluarga maupun daftar keluarga)
- [x] Pendataan hanya saat periode berjalan, satu keluarga sekali per periode
- [x] Dropdown (RT, pekerjaan, jenis usaha, aset, bantuan) membaca tabel master
- [ ] Status Sinkron: riwayat job asli; tombol menjalankan SQL Server Agent lewat REPLICATION_MERGE_SYNC_JOB. Jumlah antrean perubahan tidak diklaim dari riwayat job; uji job dan metadata konflik di Laptop 2.

## 3. Admin

- [x] Dashboard (data asli)
- [x] Pengguna (tambah, ubah, nonaktifkan, reset password; cegah nonaktifkan diri sendiri/satu-satunya admin)
- [x] Wilayah (dusun, RW, RT; cegah hapus wilayah yang masih digunakan)
- [x] Periode Sensus (draft, berjalan, selesai; hanya satu berjalan)
- [x] Data Master: Pekerjaan
- [x] Data Master: Jenis Usaha
- [x] Data Master: Jenis Aset
- [x] Data Master: Jenis Bantuan
- [x] Data Master: Kategori Kesejahteraan
- [ ] Penilaian: rumus skor, hitung ulang, daftar hasil
- [ ] Konflik Sinkronisasi (pilih versi pusat atau lapangan)
- [ ] Monitor Replikasi (jumlah baris dua server, status publikasi)
- [ ] (Opsional) Data Keluarga: lihat, ubah, hapus semua

## 4. Pimpinan

- [x] Dashboard (pilih periode, kartu angka, grafik kategori dan progres antar dusun; memakai hasil penilaian tersimpan)
- [x] Statistik (penghasilan, pekerjaan, pendidikan, kondisi rumah, aset, bantuan)
- [x] Layak Bantuan (daftar, filter dusun/pencarian, detail baca saja dari skor dan kategori tersimpan)
- [x] Laporan rekap per dusun, RW, RT
- [x] Ekspor rekap CSV yang dapat dibuka di Excel, cetak/simpan PDF dari browser (belum berkas .xlsx atau PDF langsung)

## 5. Replikasi (Laptop 1)

- [x] SQL Server dan SQL Server Agent berjalan di kedua laptop
- [x] Akun repl_user, folder snapshot (ReplData) dibagi
- [x] Configure Distribution
- [x] Publikasi Pub_Snapshot_Master dan subscription push dibuat
- [ ] Tabel master terisi di Laptop 2 (dicek dengan SELECT)
- [ ] Tes Snapshot: data baru muncul setelah snapshot baru
- [ ] Publikasi Pub_Transactional_Skor + subscription push
- [ ] Tes Transactional: perubahan muncul dalam hitungan detik
- [ ] Publikasi Pub_Merge_Sensus
- [ ] Pull subscription Merge dari Laptop 2
- [ ] Tes Merge saat offline (matikan Wi-Fi, isi data, hidupkan lagi, sinkron)
- [ ] Tes konflik (ubah baris sama di dua laptop) dan tes No. KK kembar
- [ ] Atasi error sertifikat/enkripsi agent kalau muncul (SQL Server 2025)

## 6. Laptop 2 (website offline)

- [ ] PHP, driver sqlsrv, ODBC Driver 18, Composer, Git terpasang
- [ ] Project di folder di luar OneDrive (mis. C:\proyek\sensus-desa), composer install
- [ ] .env mengarah ke database lokal (DB_HOST=127.0.0.1)
- [ ] SQL Server dan Agent startup Automatic
- [ ] mulai.bat membuka website
- [ ] Uji tanpa jaringan: login dan simpan data berhasil

## 7. Laporan (Template_Basdat.docx)

- [ ] Cover
- [ ] Pendahuluan: latar belakang dan permasalahan
- [ ] Metodologi: gambar ERD (dari dbdiagram.io)
- [ ] Metodologi: kapan memakai Snapshot, Transactional, Merge dan alasannya
- [ ] Implementasi: screenshot Configure Distribution
- [ ] Implementasi: screenshot Snapshot (publikasi, subscription, hasil)
- [ ] Implementasi: screenshot Transactional
- [ ] Implementasi: screenshot Merge (offline dan konflik)
- [ ] Implementasi: screenshot website berjalan di Laptop 2 tanpa jaringan
- [ ] Jawab pertanyaan catatan dosen di template

## Catatan harian

Tulis apa yang dikerjakan dan kendalanya.

- 2026-11-09:
- 2026-11-16: Admin dapat mengelola pengguna, wilayah, periode sensus, dan lima kelompok data master. Penilaian otomatis belum dibuat karena rumus/bobot/ambang belum disetujui; resolusi konflik dan monitor dua server memerlukan keputusan sumber pemenang serta konfigurasi server kedua.
- 2026-11-17: Pimpinan mendapat dashboard per periode, statistik, daftar layak bantuan hanya dari penilaian tersimpan, serta rekap dusun/RW/RT dengan ekspor CSV dan cetak PDF. Rumus skor otomatis tetap belum dibuat karena bobot/ambang belum disepakati.