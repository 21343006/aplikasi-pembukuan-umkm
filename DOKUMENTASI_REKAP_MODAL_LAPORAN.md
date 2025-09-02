# 📊 Dokumentasi Fitur Rekap Modal dalam Laporan Bulanan

## 🎯 Overview

Fitur **Rekap Modal** telah ditambahkan ke dalam laporan bulanan aplikasi UMKM untuk memberikan gambaran yang lebih lengkap dan detail mengenai modal awal, modal keluar, dan biaya tetap dalam periode laporan tertentu.

---

## ✨ Fitur yang Ditambahkan

### **1. Section Rekap Modal & Keuangan**
Section baru yang ditampilkan setelah ringkasan saldo dan sebelum tabel rekap harian, berisi:

#### **Modal Awal Bulan Ini**
- **Lokasi:** Card hijau dengan border success
- **Informasi:** Total modal awal yang dimasukkan dalam bulan laporan
- **Detail:** Tabel dengan tanggal input dan jumlah modal
- **Warna:** Hijau (success) untuk modal masuk

#### **Modal Keluar Bulan Ini**
- **Lokasi:** Card merah dengan border danger
- **Informasi:** Total modal yang dikeluarkan dalam bulan laporan
- **Detail:** Tabel dengan tanggal, keperluan, keterangan, dan jumlah
- **Warna:** Merah (danger) untuk modal keluar

#### **Biaya Tetap Bulan Ini**
- **Lokasi:** Card kuning dengan border warning
- **Informasi:** Total biaya tetap yang dikeluarkan dalam bulan laporan
- **Detail:** Tabel dengan tanggal, keperluan, dan jumlah
- **Warna:** Kuning (warning) untuk biaya tetap

#### **Ringkasan Modal**
- **Lokasi:** Alert info di bagian bawah section
- **Informasi:** Perbandingan visual modal masuk, keluar, dan biaya tetap
- **Fitur:** Perhitungan Net Modal (Modal awal - Modal keluar - Biaya tetap)

### **2. Informasi Modal dalam Ringkasan Saldo**
- **Lokasi:** Di dalam ringkasan saldo bulan ini
- **Informasi:** Breakdown detail modal awal dan modal keluar
- **Format:** Ditampilkan dengan warna hijau (+) dan merah (-)

### **3. Informasi Tambahan yang Diperluas**
- **Lokasi:** Alert info di bagian bawah laporan
- **Informasi:** Penjelasan rumus saldo kumulatif dan net modal
- **Tujuan:** Memudahkan user memahami perhitungan

---

## 🔧 Implementasi Teknis

### **Method yang Digunakan:**
```php
public function getModalBreakdown(): array
{
    // Mengambil data modal awal, modal keluar, dan biaya tetap
    // untuk bulan dan tahun yang dipilih
}
```

### **Data yang Dihitung:**
- **Modal Awal:** Dari tabel `capitalearlys`
- **Modal Keluar:** Dari tabel `capitals` dengan `jenis = 'keluar'`
- **Biaya Tetap:** Dari tabel `fixed_costs`

### **Perhitungan Net Modal:**
```php
$netModal = $modalAwalBulan - $modalKeluarBulan - $totalModalTetap;
```

---

## 📱 Tampilan UI

### **Layout:**
```
┌─────────────────────────────────────────────────────────┐
│                    Rekap Modal & Keuangan               │
├─────────────────┬───────────────────────────────────────┤
│  Modal Awal     │           Modal Keluar               │
│  (Card Hijau)   │           (Card Merah)               │
├─────────────────┴───────────────────────────────────────┤
│                    Biaya Tetap                          │
│                   (Card Kuning)                         │
├─────────────────────────────────────────────────────────┤
│                 Ringkasan Modal                         │
│              (Alert Info dengan Chart)                  │
└─────────────────────────────────────────────────────────┘
```

### **Warna dan Ikon:**
- **Modal Awal:** 🟢 Hijau + ikon `bi-plus-circle`
- **Modal Keluar:** 🔴 Merah + ikon `bi-dash-circle`
- **Biaya Tetap:** 🟡 Kuning + ikon `bi-calendar-check`
- **Ringkasan:** 🔵 Biru + ikon `bi-lightbulb`

---

## 📊 Data yang Ditampilkan

### **Modal Awal:**
- Tanggal input modal
- Jumlah modal awal
- Total modal awal bulan

### **Modal Keluar:**
- Tanggal pengeluaran
- Keperluan pengeluaran
- Keterangan detail
- Jumlah pengeluaran
- Total modal keluar bulan

### **Biaya Tetap:**
- Tanggal biaya
- Jenis keperluan
- Jumlah biaya
- Total biaya tetap bulan

### **Ringkasan Modal:**
- Modal Masuk (hijau)
- Modal Keluar (merah)
- Biaya Tetap (kuning)
- Net Modal (hijau/merah sesuai nilai)

---

## 🎯 Manfaat Fitur

### **1. Transparansi Keuangan:**
- User dapat melihat dengan jelas sumber modal
- Tracking pengeluaran modal yang terstruktur
- Monitoring biaya tetap bulanan

### **2. Analisis Modal:**
- Perbandingan modal masuk vs keluar
- Perhitungan net modal yang akurat
- Identifikasi penggunaan modal

### **3. Pelaporan Lengkap:**
- Laporan keuangan yang komprehensif
- Breakdown detail setiap komponen modal
- Visualisasi data yang mudah dipahami

### **4. Pengambilan Keputusan:**
- Evaluasi efektivitas penggunaan modal
- Perencanaan modal masa depan
- Analisis tren modal bulanan

---

## 🔍 Cara Menggunakan

### **1. Akses Laporan Bulanan:**
- Buka menu **Laporan** → **Laporan Bulanan**
- Pilih bulan dan tahun yang diinginkan

### **2. Lihat Rekap Modal:**
- Scroll ke section **Rekap Modal & Keuangan**
- Perhatikan card hijau (modal awal), merah (modal keluar), kuning (biaya tetap)

### **3. Analisis Data:**
- Bandingkan modal masuk vs keluar
- Lihat net modal (hijau = positif, merah = negatif)
- Periksa detail setiap komponen

### **4. Gunakan Informasi:**
- Evaluasi penggunaan modal
- Perencanaan modal masa depan
- Analisis tren keuangan

---

## 📝 Contoh Penggunaan

### **Skenario 1: Bulan dengan Modal Awal**
```
Modal Awal: Rp 5.000.000 (hijau)
Modal Keluar: Rp 2.000.000 (merah)
Biaya Tetap: Rp 1.500.000 (kuning)
Net Modal: Rp 1.500.000 (hijau - positif)
```

### **Skenario 2: Bulan tanpa Modal Awal**
```
Modal Awal: Rp 0 (tidak ada)
Modal Keluar: Rp 500.000 (merah)
Biaya Tetap: Rp 1.200.000 (kuning)
Net Modal: -Rp 1.700.000 (merah - negatif)
```

### **Skenario 3: Bulan dengan Ekspansi**
```
Modal Awal: Rp 10.000.000 (hijau)
Modal Keluar: Rp 8.000.000 (merah)
Biaya Tetap: Rp 2.000.000 (kuning)
Net Modal: Rp 0 (netral)
```

---

## 🚀 Fitur Tambahan yang Bisa Dikembangkan

### **1. Grafik Modal:**
- Chart pie untuk distribusi modal
- Chart line untuk tren modal bulanan
- Chart bar untuk perbandingan modal

### **2. Export Data:**
- Export ke Excel/PDF
- Laporan modal tahunan
- Analisis modal per kategori

### **3. Notifikasi:**
- Alert ketika modal keluar melebihi modal masuk
- Reminder biaya tetap bulanan
- Warning modal menipis

### **4. Analisis Lanjutan:**
- ROI modal
- Break-even analysis
- Cash flow projection

---

## 📋 Checklist Testing

### **✅ Testing Fitur Dasar:**
- [ ] Modal awal ditampilkan dengan benar
- [ ] Modal keluar ditampilkan dengan benar
- [ ] Biaya tetap ditampilkan dengan benar
- [ ] Net modal dihitung dengan akurat

### **✅ Testing Data:**
- [ ] Data modal awal sesuai database
- [ ] Data modal keluar sesuai database
- [ ] Data biaya tetap sesuai database
- [ ] Perhitungan total akurat

### **✅ Testing UI:**
- [ ] Warna dan ikon sesuai design
- [ ] Layout responsive
- [ ] Tabel data terformat dengan baik
- [ ] Alert info informatif

### **✅ Testing Edge Cases:**
- [ ] Bulan tanpa modal awal
- [ ] Bulan tanpa modal keluar
- [ ] Bulan tanpa biaya tetap
- [ ] Data kosong ditampilkan dengan baik

---

## 🔧 Troubleshooting

### **Masalah Umum:**

#### **1. Modal tidak muncul:**
- Periksa data di database
- Pastikan method `getModalBreakdown()` berfungsi
- Check error log Laravel

#### **2. Perhitungan salah:**
- Verifikasi data di database
- Periksa logic perhitungan
- Test dengan data dummy

#### **3. UI tidak responsive:**
- Periksa CSS Bootstrap
- Test di berbagai ukuran layar
- Check console browser

---

## 📚 Referensi

### **File yang Dimodifikasi:**
- `resources/views/livewire/reports/reportbulanan-list.blade.php`

### **Method yang Digunakan:**
- `getModalBreakdown()` di `ReportbulananList.php`

### **Model yang Terlibat:**
- `Capitalearly` (modal awal)
- `Capital` (modal keluar)
- `FixedCost` (biaya tetap)

---

*Dokumentasi ini dibuat untuk membantu developer dan user memahami fitur rekap modal yang baru ditambahkan ke dalam laporan bulanan aplikasi UMKM.*
