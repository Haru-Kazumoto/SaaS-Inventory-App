# Gambaran Produk

Dokumen ini menjelaskan **apa** aplikasinya, **untuk siapa**, dan **fitur apa saja** (yang sudah ada dan yang direncanakan). Untuk **bagaimana** kodenya disusun, lihat [`ARCHITECTURE.md`](./ARCHITECTURE.md).

---

## 1. Ringkasan

**Aplikasi inventaris & peminjaman barang** untuk organisasi apa pun yang punya barang bersama dan perlu tahu: *barangnya ada berapa, di mana, sedang dipinjam siapa, dan kapan kembali.*

Awalnya dibuat untuk satu sekolah (per jurusan). Arah ke depan:
1. **Generik**: tidak terikat sektor sekolah; istilah menyesuaikan organisasi.
2. **SaaS**: banyak organisasi dalam satu aplikasi, data terisolasi per organisasi.

**Satu konteks, tidak melebar:** inti produk tetap *inventaris + peminjaman*. Akuntansi/penyusutan aset, penjualan (POS), dan sewa berbayar ber-invoice **di luar cakupan** saat ini.

---

## 2. Untuk siapa

| Segmen | Contoh | Unit-nya disebut | Kebutuhan utama |
|---|---|---|---|
| Sekolah | SMK, SMA | Jurusan | Alat praktik per jurusan, peminjaman siswa/guru, pengadaan tahunan |
| Kampus | Fakultas, lab | Fakultas / Lab | Peralatan lab, peminjaman mahasiswa |
| Kantor | Perusahaan, instansi | Divisi / Departemen | Aset kantor (laptop, proyektor), peminjaman antar divisi |
| Komunitas / organisasi kecil | Komunitas, gereja, karang taruna | (disembunyikan, 1 unit) | Catat barang & siapa yang pinjam, tanpa ribet |
| Rental sederhana | Persewaan alat | Cabang | Stok & status pinjam (tanpa invoice) |

---

## 3. Konsep & istilah

```
Platform (pemilik SaaS)
 └── Organisasi (tenant)          SMK Yatindo · PT Maju Jaya · Komunitas Y
      ├── Pengaturan              nama, logo, warna, zona waktu, terminologi
      ├── Unit                    Jurusan TJKT · Divisi IT · Cabang Bandung   (boleh hanya 1)
      │    ├── Anggota            user + role di unit ini
      │    ├── Gudang / Lokasi    (atau gudang bersama milik organisasi)
      │    └── Barang
      │         └── Stok          per lokasi × kondisi (dihitung dari riwayat pergerakan)
      ├── Kontak peminjam         master peminjam (NIS/NIP/ID karyawan/NIK)
      └── Data master             kategori, satuan
```

| Istilah produk | Arti | Catatan |
|---|---|---|
| **Organisasi** | Satu pelanggan SaaS | Semua data terisolasi per organisasi |
| **Unit** | Bagian di dalam organisasi pemilik barang | Label bisa diganti: Jurusan, Divisi, Departemen, Cabang, Fakultas |
| **Gudang / Lokasi** | Tempat barang disimpan | Milik satu unit atau bersama (organisasi) |
| **Barang** | Item yang dicatat | Tipe: *tidak habis pakai* (dipinjam & kembali) atau *habis pakai* (terpakai) |
| **Stok** | Jumlah per lokasi & kondisi | Kondisi: Bagus, Rusak, Hilang, Dihapuskan |
| **Peminjaman** | Barang keluar untuk dipinjam/dipakai | Status: Dipinjam, Kembali Sebagian, Selesai |
| **Kontak peminjam** | Orang yang meminjam | Bisa internal (anggota) atau eksternal |
| **Penanggung jawab** | Orang yang menjamin peminjaman | Sebelumnya "Pengawas ruangan" |
| **Lokasi pemakaian** | Di mana barang dipakai | Sebelumnya "Ruangan" |
| **Pengadaan** | Permintaan pembelian barang baru | Ditinjau admin organisasi, direkap per tahun |
| **Barang dihapuskan** | Barang rusak/pensiun yang dikeluarkan dari gudang | Tidak lagi dihitung stok; tercatat untuk audit |

### Terminologi per organisasi

Label di UI diambil dari pengaturan organisasi (`useTerms()` di frontend), bukan ditulis langsung. Template saat daftar:

| Template | Unit | Kategori awal | Label identitas peminjam |
|---|---|---|---|
| Sekolah | Jurusan | Alat praktik, Elektronik, ATK | NIS / NIP |
| Kampus | Fakultas | Lab, Elektronik | NIM / NIDN |
| Kantor | Divisi | Elektronik, Furnitur, Kendaraan | ID Karyawan |
| Komunitas / Rental | — (1 unit, disembunyikan) | Bebas | NIK / No. HP |

---

## 4. Peran pengguna

| Peran | Lingkup | Bisa |
|---|---|---|
| **Admin platform** | Seluruh SaaS | Kelola organisasi, paket, pemakaian (sebelumnya role `DEVELOPER`) |
| **Pemilik organisasi** | Organisasi | Semua milik admin organisasi + billing & hapus organisasi |
| **Admin organisasi** | Organisasi | Kelola unit, anggota, pengaturan, data master; lihat & proses semua unit; tinjau pengadaan (sebelumnya `SUPERADMIN`) |
| **Admin unit** | Unit tertentu | Kelola barang, stok, peminjaman, pengadaan di unitnya (sebelumnya `ADMIN_<JURUSAN>`) |
| **Staf unit** | Unit tertentu | Catat peminjaman & pengembalian, lihat stok |
| **Viewer** | Unit tertentu | Hanya melihat |

Satu user bisa menjadi anggota **beberapa unit** dengan peran berbeda di tiap unit.

---

## 5. Fitur

Legenda: ✅ sudah ada · 🔄 sudah ada, perlu digeneralisasi/diperbaiki · 🆕 rencana

### 5.1 Dashboard
| Fitur | Status | Catatan |
|---|---|---|
| Ringkasan angka (dalam gudang, dipinjam, dll.) | ✅ | |
| Grafik pergerakan per periode | ✅ | |
| Peminjam hari ini | ✅ | |
| Aksi cepat (tambah barang, buat peminjaman, catat barang masuk) | 🔄 | Tambah "Catat Barang Masuk" |
| Pengingat peminjaman jatuh tempo / terlambat | 🆕 | Butuh tanggal jatuh tempo |

### 5.2 Barang (Inventaris)
| Fitur | Status | Catatan |
|---|---|---|
| Daftar barang: cari, filter tipe, urutkan, status stok (Habis/Menipis/Cukup/Aman) | ✅ | |
| Tambah/ubah barang (kode, kategori, satuan, harga, tanggal pelabelan, gambar, stok awal per gudang) | ✅ | |
| Detail barang: stok per gudang & kondisi | ✅ | |
| Restock dari detail barang | ✅ | |
| **Catat Barang Masuk** dari halaman Barang Masuk (multi-barang sekaligus) | 🆕 | Endpoint batch baru |
| **Hapuskan Barang** dari halaman Barang Dihapuskan + **alasan** (rusak/hilang/pensiun) | 🆕 | Butuh kolom alasan |
| Export Excel | ✅ | Pindah ke queue untuk SaaS |
| Kode barang unik **per organisasi** | 🔄 | Sekarang unik global |
| Import barang dari Excel | 🆕 | |
| Label/QR code barang | 🆕 | Cetak & scan saat pinjam/kembali |

### 5.3 Peminjaman & Pengembalian
| Fitur | Status | Catatan |
|---|---|---|
| Buat peminjaman multi-barang | ✅ | |
| Detail peminjaman + status terhitung (Dipinjam/Kembali Sebagian/Selesai) | ✅ | |
| Pengembalian bertahap per barang + kondisi (bagus/rusak) | ✅ | |
| Field peminjam generik: kontak, penanggung jawab, lokasi pemakaian | 🔄 | Sebelumnya ruangan & pengawas ruangan |
| Master kontak peminjam (tidak ketik ulang) | 🆕 | |
| Tanggal jatuh tempo & tanda terlambat | 🆕 | |
| Bukti peminjaman (PDF/cetak) | 🆕 | |

### 5.4 Pergerakan Barang
| Fitur | Status |
|---|---|
| Barang keluar, tidak habis pakai (masih dipinjam) | ✅ |
| Barang keluar, habis pakai (pemakaian) | ✅ |
| Barang masuk (riwayat restock) | ✅ |
| Barang dihapuskan (riwayat + export) | ✅ |
| Filter peminjam, kode, gudang, tanggal | ✅ |

### 5.5 Pengadaan
| Fitur | Status | Catatan |
|---|---|---|
| Ajukan barang (jumlah, satuan, alasan, link toko, gambar) | ✅ | |
| Tinjau & setujui/tolak massal | ✅ | Oleh admin organisasi |
| Rekap & export per tahun dan status | ✅ | |
| Notifikasi status ke pengaju | 🆕 | Email / in-app |

### 5.6 Data Master
| Fitur | Status | Catatan |
|---|---|---|
| Gudang / lokasi | ✅ | + opsi gudang bersama organisasi 🆕 |
| Kategori (sub kategori) | ✅ | |
| Satuan | ✅ | Di kode diganti nama menjadi `uom` |
| **Unit** (jurusan/divisi/cabang) dinamis | 🆕 | Sekarang 6 jurusan di-hardcode |

### 5.7 Pengguna & Akses
| Fitur | Status | Catatan |
|---|---|---|
| Kelola pengguna (foto, kontak, role, unit, password acak) | ✅ | |
| Role generik + keanggotaan multi-unit | 🆕 | Ganti role `ADMIN_<JURUSAN>` |
| Undang anggota lewat email | 🆕 | |
| Otorisasi per role & cek kepemilikan data | 🔄 | **Celah saat ini**: belum ada policy, rawan IDOR |
| Lupa password, verifikasi email | 🔄 | Ada dari Breeze, belum dipakai penuh |

### 5.8 Organisasi & SaaS
| Fitur | Status |
|---|---|
| Daftar organisasi + pilih template (Sekolah/Kampus/Kantor/Komunitas) | 🆕 |
| Pengaturan organisasi: nama, logo, warna, zona waktu, terminologi | 🆕 |
| Area Platform (role DEVELOPER): dashboard MRR/pendapatan/tunggakan, kelola organisasi (profil, status, paket), semua pengguna (reset password), paket, tagihan (buat, cetak, batalkan), pembayaran | ✅ |
| Paket & masa coba; batas pemakaian **ditampilkan** | ✅ (penegakan batas 🆕) |
| Pembayaran manual (transfer, VA, QRIS, tunai) & pembayaran sebagian | ✅ |
| Pembayaran otomatis via gateway (Midtrans/Xendit webhook) | 🆕 |
| Tagihan perpanjangan otomatis & status menunggak otomatis (scheduler) | 🆕 |
| Email tagihan / pengingat jatuh tempo | 🆕 |
| Audit log (siapa mengubah apa) | 🆕 |
| Export seluruh data organisasi & hapus akun (UU PDP) | 🆕 |

---

## 6. Alur utama

**Peminjaman barang**
1. Staf/admin unit memilih barang & gudang asal → isi kontak peminjam, penanggung jawab, lokasi pemakaian, (jatuh tempo).
2. Stok kondisi *Bagus* di gudang asal berkurang.
3. Pengembalian bisa bertahap; tiap barang kembali dengan kondisi *Bagus* atau *Rusak*.
4. Status otomatis: **Dipinjam → Kembali Sebagian → Selesai**. Barang habis pakai langsung *Selesai*.

**Barang masuk (restock)**: pilih barang → gudang → jumlah & kondisi → stok bertambah, tercatat di *Barang Masuk*.

**Penghapusan barang**: pilih barang → gudang → jumlah → (alasan) → stok aktif berkurang, tercatat di *Barang Dihapuskan*.

**Pengadaan**: admin unit mengajukan → admin organisasi menyetujui/menolak (bisa massal) → masuk rekap tahunan.

---

## 7. Prinsip UX

- **Responsive first**: tabel di desktop, daftar kartu di HP; tombol aksi utama selalu terlihat (sticky di HP).
- **Aksi ditaruh di tempat hasilnya tercatat** (mis. "Catat Barang Masuk" di halaman Barang Masuk), dengan jalan pintas dari detail barang.
- **Istilah mengikuti organisasi** (terminologi), bukan istilah teknis.
- **Unit tersembunyi bila hanya satu**: organisasi kecil tidak dibebani konsep yang tidak mereka butuhkan.
- **Tombol berlabel** (bukan ikon saja) untuk aksi penting; konfirmasi untuk aksi yang tidak bisa dibatalkan.
