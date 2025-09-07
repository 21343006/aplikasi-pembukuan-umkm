# Aplikasi Pembukuan UMKM

Aplikasi pembukuan sederhana untuk Usaha Mikro, Kecil, dan Menengah (UMKM) yang dibangun dengan Laravel dan Livewire.

## Fitur Utama

### 1. Manajemen Keuangan Dasar
- **Pemasukan**: Pencatatan pendapatan usaha
- **Pengeluaran**: Pencatatan biaya operasional
- **Modal**: Pengelolaan modal awal dan tambahan
- **Biaya Tetap**: Pencatatan biaya tetap bulanan

### 2. Buku Utang & Piutang
- **Pencatatan Utang**: Mencatat utang usaha kepada pemasok atau pihak lain
  - Nama kreditur/pemasok
  - Deskripsi utang
  - Jumlah utang
  - Tanggal jatuh tempo
  - Status pembayaran (belum bayar, sebagian, lunas)
  - Pencatatan pembayaran bertahap
  - Notifikasi utang jatuh tempo

- **Pencatatan Piutang**: Mencatat tagihan kepada pelanggan yang belum dibayar
  - Nama debitur/pelanggan
  - Deskripsi piutang
  - Jumlah piutang
  - Tanggal jatuh tempo
  - Status pembayaran (belum bayar, sebagian, lunas)
  - Pencatatan penerimaan bertahap
  - Notifikasi piutang jatuh tempo

- **Dashboard Utang & Piutang**:
  - Ringkasan total utang dan piutang
  - Posisi net (piutang - utang)
  - Jumlah item jatuh tempo
  - Statistik pembayaran

### 3. Analisis Keuangan
- **BEP Calculator**: Kalkulator Break Even Point real-time dari data aktual
- **IRR Analysis**: Analisis tingkat pengembalian internal
- **What-If Analysis**: Analisis skenario bisnis

### 4. Laporan Keuangan
- **Laporan Bulanan**: Laporan keuangan per bulan
- **Laporan Tahunan**: Laporan keuangan per tahun
- **Laba Rugi**: Analisis profitabilitas

### 5. Manajemen Stok
- **Stok Barang**: Pencatatan dan monitoring stok produk

## Teknologi yang Digunakan

- **Backend**: Laravel 11
- **Frontend**: Livewire 3, Bootstrap 5
- **Database**: SQLite (development), MySQL/PostgreSQL (production)
- **Authentication**: Laravel Breeze

## Instalasi

1. Clone repository
```bash
git clone <repository-url>
cd aplikasi-umkm
```

2. Install dependencies
```bash
composer install
npm install
```

3. Setup environment
```bash
cp .env.example .env
php artisan key:generate
```

4. Setup database
```bash
php artisan migrate
php artisan db:seed --class=DummyDataSeeder
```

5. Build assets
```bash
npm run build
```

6. Serve aplikasi
```bash
php artisan serve
```

## Struktur Database

### Tabel Utang (debts)
- `id` - Primary key
- `user_id` - Foreign key ke users
- `creditor_name` - Nama kreditur/pemasok
- `description` - Deskripsi utang
- `amount` - Jumlah utang
- `due_date` - Tanggal jatuh tempo
- `status` - Status pembayaran (unpaid/partial/paid)
- `paid_amount` - Jumlah yang sudah dibayar
- `paid_date` - Tanggal pembayaran
- `notes` - Catatan tambahan
- `created_at`, `updated_at` - Timestamps

### Tabel Piutang (receivables)
- `id` - Primary key
- `user_id` - Foreign key ke users
- `debtor_name` - Nama debitur/pelanggan
- `description` - Deskripsi piutang
- `amount` - Jumlah piutang
- `due_date` - Tanggal jatuh tempo
- `status` - Status pembayaran (unpaid/partial/paid)
- `paid_amount` - Jumlah yang sudah diterima
- `paid_date` - Tanggal penerimaan
- `notes` - Catatan tambahan
- `created_at`, `updated_at` - Timestamps

## Penggunaan Fitur Buku Utang & Piutang

### 1. Menambah Utang Baru
1. Buka menu "Buku Utang & Piutang"
2. Pilih tab "Buku Utang"
3. Klik tombol "Tambah Utang"
4. Isi form dengan data:
   - Nama kreditur
   - Deskripsi utang
   - Jumlah utang
   - Tanggal jatuh tempo
   - Catatan (opsional)
5. Klik "Simpan"

### 2. Mencatat Pembayaran Utang
1. Pada tabel utang, klik ikon pembayaran (💰)
2. Masukkan jumlah pembayaran
3. Pilih tanggal pembayaran
4. Klik "Catat Pembayaran"

### 3. Menambah Piutang Baru
1. Pilih tab "Buku Piutang"
2. Klik tombol "Tambah Piutang"
3. Isi form dengan data:
   - Nama debitur
   - Deskripsi piutang
   - Jumlah piutang
   - Tanggal jatuh tempo
   - Catatan (opsional)
4. Klik "Simpan"

### 4. Mencatat Penerimaan Piutang
1. Pada tabel piutang, klik ikon penerimaan (💰)
2. Masukkan jumlah penerimaan
3. Pilih tanggal penerimaan
4. Klik "Catat Penerimaan"

### 5. Monitoring Dashboard
- Lihat ringkasan total utang dan piutang
- Monitor posisi net (piutang - utang)
- Perhatikan item yang jatuh tempo
- Pantau status pembayaran

## Fitur Keamanan

- Autentikasi user
- Isolasi data per user
- Validasi input
- Sanitasi data

## Kontribusi

Silakan berkontribusi dengan membuat pull request atau melaporkan bug melalui issues.

## Lisensi

Proyek ini dilisensikan di bawah MIT License.