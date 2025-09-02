# 🔮 Dokumentasi Fitur Analisis What If

## 🎯 Overview

Fitur **Analisis What If** adalah alat simulasi keuangan yang memungkinkan pelaku UMKM untuk menganalisis dampak perubahan berbagai parameter bisnis terhadap profitabilitas usaha. Fitur ini membantu dalam pengambilan keputusan strategis dengan memproyeksikan berbagai skenario bisnis.

---

## ✨ Fitur Utama

### **1. Simulasi Parameter Bisnis**
- **Perubahan Harga:** Simulasi kenaikan/penurunan harga jual
- **Perubahan Volume:** Simulasi perubahan jumlah unit terjual
- **Perubahan Biaya Variabel:** Simulasi perubahan biaya produksi
- **Perubahan Biaya Tetap:** Simulasi perubahan biaya operasional

### **2. Skenario Predefined**
- **Skenario Baseline:** Kondisi aktual saat ini
- **Skenario Optimis:** Harga naik 10%, volume naik 20%, biaya turun 5%
- **Skenario Pesimis:** Harga turun 5%, volume turun 15%, biaya naik 10%
- **Optimasi Biaya:** Fokus pada pengurangan biaya variabel dan tetap
- **Strategi Harga & Volume:** Harga naik 5%, volume naik 25%

### **3. Skenario Custom**
- User dapat membuat skenario sendiri
- Nama dan deskripsi skenario yang dapat disesuaikan
- Parameter perubahan yang fleksibel

### **4. Analisis Komprehensif**
- Perbandingan skenario aktual vs what if
- Break-even analysis
- Margin of safety
- Contribution margin analysis
- Visualisasi data dengan chart

---

## 🔧 Implementasi Teknis

### **Livewire Component:**
```php
app/Livewire/Simulations/WhatIfAnalysis.php
```

### **View Template:**
```php
resources/views/livewire/simulations/what-if-analysis.blade.php
```

### **Route:**
```php
Route::get('/what-if-analysis', WhatIfAnalysis::class)->name('what.if.analysis');
```

---

## 📊 Perhitungan What If

### **1. Formula Dasar:**
```php
// Perubahan Harga
$newPrice = $actualPrice * (1 + $priceChangePercent / 100);

// Perubahan Volume
$newVolume = $actualVolume * (1 + $volumeChangePercent / 100);

// Pendapatan Baru
$newRevenue = $newPrice * $newVolume;

// Biaya Variabel Baru
$newVariableCost = $actualVariableCost * (1 + $costChangePercent / 100);

// Biaya Tetap Baru
$newFixedCost = $actualFixedCost * (1 + $fixedCostChangePercent / 100);

// Total Biaya Baru
$newTotalCost = $newVariableCost + $newFixedCost + $capitalOutflow;

// Laba Baru
$newProfit = $newRevenue - $newTotalCost;
```

### **2. Break-even Analysis:**
```php
// Contribution Margin
$contributionMargin = $newRevenue - $newVariableCost;

// Contribution Margin Ratio
$contributionMarginRatio = $contributionMargin / $newRevenue;

// Break-even Revenue
$breakEvenRevenue = $newFixedCost / $contributionMarginRatio;

// Break-even Units
$breakEvenUnits = $breakEvenRevenue / $newPrice;

// Margin of Safety
$marginOfSafety = (($newRevenue - $breakEvenRevenue) / $newRevenue) * 100;
```

### **3. Perubahan Persentase:**
```php
// Perubahan Pendapatan
$revenueChange = $newRevenue - $actualRevenue;
$revenueChangePercent = ($revenueChange / $actualRevenue) * 100;

// Perubahan Laba
$profitChange = $newProfit - $actualProfit;
$profitChangePercent = ($profitChange / abs($actualProfit)) * 100;
```

---

## 📱 Tampilan UI

### **Layout Halaman:**
```
┌─────────────────────────────────────────────────────────┐
│                    Analisis What If                     │
├─────────────────────────────────────────────────────────┤
│                Filter Periode Analisis                  │
├─────────────────────────────────────────────────────────┤
│                Data Aktual Bulan Ini                    │
├─────────────────────────────────────────────────────────┤
│                Skenario What If                         │
├─────────────────────────────────────────────────────────┤
│                Hasil Analisis                           │
│  ┌─────────────────┬─────────────────────────────────┐  │
│  │ Perbandingan    │        Detail Perbandingan      │  │
│  │ Skenario        │                                 │  │
│  └─────────────────┴─────────────────────────────────┘  │
├─────────────────────────────────────────────────────────┤
│                Chart Visualisasi                        │
│  ┌─────────────────┬─────────────────────────────────┐  │
│  │ Pendapatan      │            Laba                 │  │
│  └─────────────────┴─────────────────────────────────┘  │
├─────────────────────────────────────────────────────────┤
│                Breakdown Biaya                          │
├─────────────────────────────────────────────────────────┤
│                Buat Skenario Custom                     │
└─────────────────────────────────────────────────────────┘
```

### **Komponen UI:**
- **Filter Periode:** Dropdown bulan dan tahun
- **Data Aktual:** Card dengan 4 metrik utama
- **Skenario:** Dropdown pilihan skenario + input parameter
- **Hasil Analisis:** Perbandingan visual + tabel detail
- **Chart:** 3 chart untuk visualisasi data
- **Skenario Custom:** Form input untuk skenario baru

---

## 🎯 Skenario Bisnis

### **1. Skenario Optimis:**
- **Harga:** +10% (naik 10%)
- **Volume:** +20% (naik 20%)
- **Biaya Variabel:** -5% (turun 5%)
- **Biaya Tetap:** 0% (tidak berubah)
- **Tujuan:** Meningkatkan profitabilitas dengan optimisme pasar

### **2. Skenario Pesimis:**
- **Harga:** -5% (turun 5%)
- **Volume:** -15% (turun 15%)
- **Biaya Variabel:** +10% (naik 10%)
- **Biaya Tetap:** +5% (naik 5%)
- **Tujuan:** Persiapan menghadapi kondisi pasar yang sulit

### **3. Optimasi Biaya:**
- **Harga:** 0% (tidak berubah)
- **Volume:** 0% (tidak berubah)
- **Biaya Variabel:** -15% (turun 15%)
- **Biaya Tetap:** -10% (turun 10%)
- **Tujuan:** Meningkatkan profit dengan efisiensi biaya

### **4. Strategi Harga & Volume:**
- **Harga:** +5% (naik 5%)
- **Volume:** +25% (naik 25%)
- **Biaya Variabel:** 0% (tidak berubah)
- **Biaya Tetap:** 0% (tidak berubah)
- **Tujuan:** Ekspansi pasar dengan harga yang kompetitif

---

## 📊 Metrik Analisis

### **1. Metrik Keuangan:**
- **Pendapatan:** Total revenue dari penjualan
- **Jumlah Unit:** Total unit yang terjual
- **Harga Rata-rata:** Rata-rata harga per unit
- **Biaya Variabel:** Biaya yang berubah sesuai volume
- **Biaya Tetap:** Biaya yang tidak berubah
- **Total Biaya:** Semua biaya operasional
- **Laba Bersih:** Pendapatan dikurangi total biaya
- **Margin Laba:** Persentase laba terhadap pendapatan

### **2. Metrik Analisis:**
- **Contribution Margin:** Pendapatan dikurangi biaya variabel
- **Contribution Margin Ratio:** Persentase contribution margin
- **Break-even Revenue:** Pendapatan minimal untuk mencapai titik impas
- **Break-even Units:** Unit minimal untuk mencapai titik impas
- **Margin of Safety:** Persentase keamanan dari break-even point

### **3. Metrik Perubahan:**
- **Perubahan Absolut:** Selisih nilai aktual vs what if
- **Perubahan Persentase:** Persentase perubahan relatif

---

## 🔍 Cara Menggunakan

### **1. Akses Fitur:**
- Buka menu **Analisis** → **What If Analysis**
- Atau akses langsung: `/what-if-analysis`

### **2. Pilih Periode:**
- Pilih bulan dan tahun yang ingin dianalisis
- Pastikan ada data transaksi untuk periode tersebut

### **3. Pilih Skenario:**
- Pilih skenario predefined yang tersedia
- Atau buat skenario custom dengan parameter sendiri

### **4. Atur Parameter:**
- **Perubahan Harga:** Masukkan persentase perubahan harga
- **Perubahan Volume:** Masukkan persentase perubahan volume
- **Perubahan Biaya:** Masukkan persentase perubahan biaya
- **Perubahan Biaya Tetap:** Masukkan persentase perubahan biaya tetap

### **5. Analisis Hasil:**
- Lihat perbandingan skenario aktual vs what if
- Perhatikan perubahan pendapatan dan laba
- Analisis break-even point dan margin of safety
- Gunakan chart untuk visualisasi data

### **6. Buat Skenario Custom:**
- Masukkan nama skenario
- Masukkan deskripsi skenario
- Atur parameter sesuai kebutuhan
- Klik "Buat Skenario"

---

## 📈 Contoh Analisis

### **Skenario: Kenaikan Harga 15%**

#### **Data Aktual:**
- Pendapatan: Rp 10.000.000
- Volume: 1.000 unit
- Harga: Rp 10.000/unit
- Biaya Variabel: Rp 6.000.000
- Biaya Tetap: Rp 2.000.000
- Laba: Rp 2.000.000

#### **Skenario What If (Harga +15%):**
- Harga Baru: Rp 11.500/unit
- Pendapatan Baru: Rp 11.500.000
- Perubahan Pendapatan: +Rp 1.500.000 (+15%)
- Laba Baru: Rp 3.500.000
- Perubahan Laba: +Rp 1.500.000 (+75%)

#### **Analisis:**
- **Break-even Revenue:** Rp 5.750.000
- **Margin of Safety:** 50%
- **Contribution Margin Ratio:** 47.8%

---

## 🚀 Fitur Tambahan yang Bisa Dikembangkan

### **1. Sensitivitas Analysis:**
- Grafik sensitivitas parameter
- Analisis parameter mana yang paling berpengaruh
- Range nilai yang aman untuk setiap parameter

### **2. Monte Carlo Simulation:**
- Simulasi dengan distribusi probabilitas
- Analisis risiko dan ketidakpastian
- Confidence interval untuk hasil analisis

### **3. Scenario Comparison:**
- Perbandingan multiple skenario sekaligus
- Ranking skenario berdasarkan profitabilitas
- Export hasil analisis ke Excel/PDF

### **4. Historical Analysis:**
- Trend analisis what if dari waktu ke waktu
- Benchmarking dengan periode sebelumnya
- Seasonal adjustment untuk analisis

### **5. Goal Seeking:**
- Reverse calculation untuk mencapai target laba
- Optimasi parameter untuk target tertentu
- Recommendation engine untuk strategi bisnis

---

## 📋 Checklist Testing

### **✅ Testing Fitur Dasar:**
- [ ] Filter periode berfungsi dengan benar
- [ ] Data aktual dimuat dengan akurat
- [ ] Skenario predefined tersedia
- [ ] Parameter perubahan dapat diinput

### **✅ Testing Perhitungan:**
- [ ] Perhitungan what if akurat
- [ ] Break-even analysis benar
- [ ] Margin of safety akurat
- [ ] Perubahan persentase benar

### **✅ Testing UI:**
- [ ] Chart ditampilkan dengan benar
- [ ] Tabel perbandingan informatif
- [ ] Responsive design berfungsi
- [ ] Alert dan notifikasi muncul

### **✅ Testing Edge Cases:**
- [ ] Periode tanpa data
- [ ] Parameter ekstrem (0%, 100%, -100%)
- [ ] Skenario custom berfungsi
- [ ] Reset parameter berfungsi

---

## 🔧 Troubleshooting

### **Masalah Umum:**

#### **1. Chart tidak muncul:**
- Pastikan Chart.js library ter-load
- Check console browser untuk error
- Pastikan data chart tersedia

#### **2. Perhitungan salah:**
- Verifikasi data aktual dari database
- Periksa formula perhitungan
- Test dengan data dummy sederhana

#### **3. Skenario tidak berubah:**
- Pastikan Livewire event berfungsi
- Check browser console untuk error
- Refresh halaman jika diperlukan

---

## 📚 Referensi

### **File yang Dibuat:**
- `app/Livewire/Simulations/WhatIfAnalysis.php`
- `resources/views/livewire/simulations/what-if-analysis.blade.php`

### **Route yang Ditambahkan:**
- `/what-if-analysis` → `WhatIfAnalysis::class`

### **Dependencies:**
- Chart.js untuk visualisasi
- Bootstrap untuk UI components
- Livewire untuk interaktivitas

---

## 🎯 Manfaat Fitur

### **1. Pengambilan Keputusan:**
- Evaluasi dampak perubahan harga
- Analisis strategi volume penjualan
- Optimasi struktur biaya
- Perencanaan ekspansi bisnis

### **2. Manajemen Risiko:**
- Identifikasi skenario terburuk
- Persiapan menghadapi perubahan pasar
- Analisis sensitivitas bisnis
- Mitigasi risiko keuangan

### **3. Perencanaan Strategis:**
- Target setting yang realistis
- Budget planning yang akurat
- Resource allocation yang optimal
- Competitive positioning

---

*Dokumentasi ini dibuat untuk membantu developer dan user memahami fitur analisis What If yang sudah ada di folder Simulations aplikasi UMKM.*
