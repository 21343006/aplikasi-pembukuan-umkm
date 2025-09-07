# 🍜 **SEEDER DATA DUMMY USAHA BAKSO & MIE AYAM**

## 📋 **Daftar Seeder**

### 1. **BaksoMieAyamSeeder** - Seeder Utama
Seeder utama yang membuat data dasar untuk usaha bakso dan mie ayam.

**Fitur:**
- ✅ User dengan profil lengkap
- ✅ Modal awal 50 juta
- ✅ 5 produk utama (Bakso Sapi, Mie Ayam, Bakso Ikan, Es Teh, Es Jeruk)
- ✅ Biaya tetap bulanan dan tahunan
- ✅ Data penjualan bulanan September 2025 - Agustus 2026
- ✅ Data pengeluaran bahan baku dan operasional
- ✅ Data modal masuk dan keluar
- ✅ Data utang ke supplier dan piutang dari pelanggan

### 2. **DetailedBaksoSeeder** - Data Detail
Seeder untuk data yang lebih detail dan spesifik.

**Fitur:**
- ✅ Data penjualan harian yang detail
- ✅ Data pengeluaran harian yang spesifik
- ✅ Data stok masuk untuk restock
- ✅ Biaya tetap tambahan (asuransi, sertifikasi, dll)

### 3. **RealisticBaksoSeeder** - Data Realistis
Seeder untuk data yang sangat realistis dengan pola musiman.

**Fitur:**
- ✅ Pola penjualan musiman (hujan, panas, liburan)
- ✅ Boost weekend dan hari kerja
- ✅ Event khusus (Natal, Tahun Baru, Hari Raya, dll)
- ✅ Fluktuasi stok yang realistis
- ✅ Variasi pengeluaran berdasarkan musim

## 🚀 **Cara Menjalankan Seeder**

### **Jalankan Semua Seeder:**
```bash
php artisan db:seed
```

### **Jalankan Seeder Tertentu:**
```bash
# Seeder utama
php artisan db:seed --class=BaksoMieAyamSeeder

# Seeder detail
php artisan db:seed --class=DetailedBaksoSeeder

# Seeder realistis
php artisan db:seed --class=RealisticBaksoSeeder
```

### **Reset Database + Seed:**
```bash
php artisan migrate:fresh --seed
```

## 👤 **Data User yang Dibuat**

**Email:** `pakhaji@bakso.com`  
**Password:** `password`  
**Nama:** Pak Haji Ahmad  
**Usaha:** Bakso & Mie Ayam Pak Haji  
**Alamat:** Jl. Raya Bakso No. 123, Jakarta Selatan  
**Modal Awal:** Rp 50.000.000  

## 🏪 **Data Usaha yang Dibuat**

### **Produk:**
1. **Bakso Sapi** - Rp 25.000/porsi (Cost: Rp 15.000)
2. **Mie Ayam** - Rp 20.000/porsi (Cost: Rp 12.000)
3. **Bakso Ikan** - Rp 18.000/porsi (Cost: Rp 10.000)
4. **Es Teh Manis** - Rp 5.000/gelas (Cost: Rp 2.000)
5. **Es Jeruk** - Rp 7.000/gelas (Cost: Rp 3.000)

### **Biaya Tetap Bulanan:**
- Sewa Tempat: Rp 8.000.000
- Listrik: Rp 1.500.000
- Air: Rp 500.000
- Internet: Rp 300.000
- Gaji Karyawan: Rp 12.000.000
- BPJS Karyawan: Rp 800.000
- Pajak UMKM: Rp 500.000
- Maintenance: Rp 1.000.000

### **Biaya Tetap Tahunan:**
- Izin Usaha: Rp 1.000.000
- Asuransi Usaha: Rp 2.000.000
- Sertifikasi Halal: Rp 1.500.000
- Pelatihan Karyawan: Rp 3.000.000
- Konsultan Keuangan: Rp 1.000.000
- Biaya Legal: Rp 800.000

## 📊 **Pola Data yang Dibuat**

### **Penjualan Musiman:**
- **September 2025:** Awal musim (80% dari normal)
- **Oktober 2025:** Musim normal (100%)
- **November 2025:** Musim hujan (120% - naik)
- **Desember 2025:** Liburan (150% - tinggi)
- **Januari 2026:** Awal tahun (100%)
- **Februari 2026:** Musim normal (90%)
- **Maret 2026:** Musim semi (110% - naik)
- **April 2026:** Musim normal (100%)
- **Mei 2026:** Musim panas (130% - tinggi)
- **Juni 2026:** Puncak musim panas (140%)
- **Juli 2026:** Musim panas (130% - tinggi)
- **Agustus 2026:** Akhir musim panas (120%)

### **Boost Khusus:**
- **Weekend:** +30% sampai +80% dari hari kerja
- **Event Khusus:** +20% sampai +100% dari normal
- **Liburan:** +50% sampai +100% dari normal

### **Variasi Harian:**
- **Variasi:** ±20% dari rata-rata
- **Pola:** Lebih tinggi di akhir pekan dan hari libur

## 💰 **Data Keuangan**

### **Modal:**
- **Modal Awal:** Rp 50.000.000
- **Investasi Peralatan:** Rp 15.000.000
- **Renovasi Tempat:** Rp 10.000.000

### **Utang:**
- **PT Sukses Makmur Jaya:** Rp 15.000.000 (bahan baku)
- **CV Bumbu Nusantara:** Rp 5.000.000 (bumbu)

### **Piutang:**
- **Kantor PT Maju Bersama:** Rp 8.000.000 (catering)
- **SDN Harapan Bangsa:** Rp 3.000.000 (makan siang)

## 📈 **Data Stok**

### **Stok Awal:**
- Bakso Sapi: 100 porsi
- Mie Ayam: 80 porsi
- Bakso Ikan: 60 porsi
- Es Teh Manis: 200 gelas
- Es Jeruk: 150 gelas

### **Pola Restock:**
- **Mingguan:** 15-30 unit
- **Bulanan:** 40-80 unit
- **Event Khusus:** 60-100 unit

### **Threshold Stok:**
- Bakso Sapi: 20 porsi
- Mie Ayam: 15 porsi
- Bakso Ikan: 10 porsi
- Es Teh Manis: 30 gelas
- Es Jeruk: 25 gelas

## 🎯 **Kegunaan Data**

### **Untuk Testing:**
- ✅ Semua fitur aplikasi dapat ditest
- ✅ Data yang cukup untuk analisis
- ✅ Pola yang realistis untuk demo

### **Untuk Demo:**
- ✅ Menunjukkan kemampuan aplikasi
- ✅ Data yang menarik untuk presentasi
- ✅ Pola bisnis yang masuk akal

### **Untuk Development:**
- ✅ Testing fitur baru
- ✅ Validasi perhitungan
- ✅ Testing performa dengan data besar

## 🔧 **Customization**

### **Mengubah Data:**
1. Edit file seeder yang sesuai
2. Ubah nilai yang diinginkan
3. Jalankan `php artisan migrate:fresh --seed`

### **Menambah Data:**
1. Buat method baru di seeder
2. Panggil di method `run()`
3. Jalankan seeder

### **Mengubah Periode:**
1. Edit tahun dan bulan di seeder
2. Sesuaikan pola musiman
3. Jalankan ulang seeder

## ⚠️ **Catatan Penting**

- **Data dimulai dari September 2025** untuk menghindari konflik dengan data existing
- **Semua data terhubung** dengan user yang sama
- **Stok otomatis terupdate** saat ada penjualan
- **History stok lengkap** untuk tracking
- **Data dapat dihapus** dengan `php artisan migrate:fresh`

## 🎉 **Hasil Akhir**

Setelah menjalankan semua seeder, Anda akan mendapatkan:
- **1 user** dengan profil lengkap
- **5 produk** dengan stok dan history
- **Data penjualan** 16 bulan (Sept 2025 - Aug 2026)
- **Data pengeluaran** yang realistis
- **Data keuangan** yang komprehensif
- **Data stok** dengan fluktuasi realistis
- **Data utang & piutang** untuk testing

Data ini siap digunakan untuk testing semua fitur aplikasi UMKM! 🚀
