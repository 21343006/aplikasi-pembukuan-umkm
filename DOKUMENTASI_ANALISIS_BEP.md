# 📊 Dokumentasi Fitur Analisis BEP (Break-Even Point)

## 🎯 Overview

Fitur **Analisis BEP (Break-Even Point)** adalah alat analisis keuangan yang membantu pelaku UMKM memahami titik impas bisnis mereka. Fitur ini memungkinkan user untuk menganalisis BEP dalam tiga mode berbeda: Kalkulator BEP, BEP Per Produk, dan BEP Per Periode.

---

## ✨ Fitur Utama

### **1. Mode Kalkulator BEP**
- **Perhitungan Real-time** dengan input manual
- **Auto-load data** dari database berdasarkan periode
- **Target profit analysis** untuk perencanaan laba
- **Margin of safety** untuk analisis risiko
- **Analisis sensitivitas** untuk perubahan parameter

### **2. Mode BEP Per Produk**
- **Manajemen data BEP** per produk (CRUD)
- **Perhitungan otomatis** berdasarkan data yang tersimpan
- **Tracking progress** menuju BEP
- **Perbandingan** dengan penjualan aktual

### **3. Mode BEP Per Periode**
- **Analisis BEP bulanan** berdasarkan data aktual
- **Perhitungan otomatis** dari database
- **Visualisasi status** (di atas/bawah BEP)
- **Margin of safety** untuk periode tertentu

---

## 🔧 Implementasi Teknis

### **Livewire Component:**
```php
app/Livewire/Beps/BepForm.php
```

### **View Template:**
```php
resources/views/livewire/beps/bep-form.blade.php
```

### **Route:**
```php
Route::get('/bep-form', BepForm::class)->name('bep.form');
```

---

## 📊 Formula Perhitungan BEP

### **1. BEP Dasar:**
```php
// Margin Kontribusi per Unit
$contributionMargin = $sellingPrice - $variableCostPerUnit;

// BEP dalam Unit
$bepUnits = ceil($fixedCost / $contributionMargin);

// BEP dalam Rupiah
$bepRupiah = $bepUnits * $sellingPrice;
```

### **2. BEP dengan Target Profit:**
```php
// BEP + Target Profit
$bepWithTarget = ($fixedCost + $targetProfit) / ($contributionMarginRatio / 100);
```

### **3. Margin of Safety:**
```php
// Margin of Safety (%)
$marginOfSafety = (($currentSales - $bep) / $currentSales) * 100;
```

### **4. Rasio Margin Kontribusi:**
```php
// Total Margin Kontribusi
$totalContributionMargin = $totalSales - $totalVariableCost;

// Rasio Margin Kontribusi (%)
$contributionMarginRatio = ($totalContributionMargin / $totalSales) * 100;
```

---

## 🎮 Cara Menggunakan

### **Mode 1: Kalkulator BEP**

#### **Langkah 1: Pilih Periode**
- Pilih bulan dan tahun yang ingin dianalisis
- Data akan otomatis di-load dari database

#### **Langkah 2: Pilih Produk (Opsional)**
- Pilih produk untuk auto-load harga dan unit terjual
- Jika tidak dipilih, input manual diperlukan

#### **Langkah 3: Input Parameter**
- **Harga Jual/Unit:** Harga jual per unit produk
- **Biaya Variabel (Bulan):** Total pengeluaran bulan ini
- **Biaya Tetap (Bulan):** Total biaya tetap bulan ini
- **Target Profit (Opsional):** Target laba yang diinginkan

#### **Langkah 4: Analisis Hasil**
- Lihat tabel hasil perhitungan BEP
- Perhatikan margin of safety
- Gunakan analisis sensitivitas untuk perencanaan

### **Mode 2: BEP Per Produk**

#### **Langkah 1: Tambah Data BEP**
- Klik tombol "Tambah BEP"
- Pilih produk dari dropdown
- Input harga jual, biaya variabel, dan biaya tetap

#### **Langkah 2: Simpan dan Analisis**
- Data akan tersimpan dan dapat diedit
- Lihat perbandingan dengan penjualan aktual
- Track progress menuju BEP

### **Mode 3: BEP Per Periode**

#### **Langkah 1: Pilih Periode**
- Pilih bulan dan tahun yang ingin dianalisis
- Klik tombol "Hitung BEP"

#### **Langkah 2: Analisis Hasil**
- Lihat data periode (biaya tetap, penjualan, biaya variabel)
- Perhatikan hasil BEP dan margin of safety
- Status akan menunjukkan posisi relatif terhadap BEP

---

## 📈 Fitur Analisis Lanjutan

### **1. Analisis Sensitivitas**
- **Perubahan Biaya Tetap:** ±20%, ±10%, 0%, +10%, +20%
- **Perubahan Margin Kontribusi:** ±20%, ±10%, 0%, +10%, +20%
- **Dampak pada BEP:** Menunjukkan seberapa sensitif BEP terhadap perubahan

### **2. Margin of Safety**
- **Persentase keamanan** dari titik impas
- **Indikator risiko** bisnis
- **Target planning** untuk pertumbuhan

### **3. Target Profit Analysis**
- **BEP dengan target laba** tertentu
- **Perencanaan pendapatan** yang diperlukan
- **Goal setting** untuk tim penjualan

---

## 🎨 Tampilan UI

### **Layout Halaman:**
```
┌─────────────────────────────────────────────────────────┐
│                Analisis BEP (Break-Even Point)         │
├─────────────────────────────────────────────────────────┤
│                Mode Selector (3 Mode)                  │
├─────────────────────────────────────────────────────────┤
│                Mode 1: Kalkulator BEP                  │
│  ┌─────────────────┬─────────────────────────────────┐  │
│  │ Filter Periode  │        Input Parameter          │  │
│  └─────────────────┴─────────────────────────────────┘  │
│                Tabel Hasil Perhitungan                 │
│                Analisis Sensitivitas                   │
├─────────────────────────────────────────────────────────┤
│                Mode 2: BEP Per Produk                  │
│                Tabel Data BEP + CRUD                   │
├─────────────────────────────────────────────────────────┤
│                Mode 3: BEP Per Periode                 │
│                Analisis BEP Bulanan                    │
└─────────────────────────────────────────────────────────┘
```

### **Komponen UI:**
- **Mode Selector:** Radio button untuk pilihan mode
- **Filter Periode:** Dropdown bulan dan input tahun
- **Input Parameter:** Form input untuk kalkulasi
- **Tabel Hasil:** Metrik BEP dengan keterangan
- **Analisis Sensitivitas:** Tabel perubahan parameter
- **Modal CRUD:** Form untuk manajemen data BEP

---

## 📊 Metrik yang Ditampilkan

### **1. Metrik Dasar:**
- **Unit Terjual:** Total unit terjual dalam periode
- **Perkiraan Penjualan:** Estimasi pendapatan bulanan
- **Biaya Variabel:** Total pengeluaran bulan ini
- **Biaya Tetap:** Total biaya tetap bulan ini

### **2. Metrik BEP:**
- **BEP (Pendapatan):** Pendapatan minimum untuk impas
- **BEP (Unit):** Unit minimum untuk impas
- **Sisa ke BEP:** Unit yang masih diperlukan
- **Margin of Safety:** Persentase keamanan dari BEP

### **3. Metrik Analisis:**
- **Margin Kontribusi:** Selisih penjualan dan biaya variabel
- **Rasio Margin:** Persentase margin kontribusi
- **Target Profit BEP:** BEP dengan target laba
- **Status BEP:** Posisi relatif terhadap titik impas

---

## 🔍 Validasi dan Error Handling

### **1. Validasi Input:**
```php
protected function rules()
{
    if ($this->mode === 'perProduct') {
        return [
            'selectedProduk' => 'required|string',
            'totalFixedCost' => 'required|numeric|min:0',
            'avgSellingPrice' => 'required|numeric|gt:0',
            'modal_per_barang' => 'required|numeric|min:0|lt:avgSellingPrice',
        ];
    }
    return [];
}
```

### **2. Error Handling:**
- **Division by zero:** Validasi untuk mencegah error perhitungan
- **Data tidak ditemukan:** Pesan error yang informatif
- **Validasi periode:** Range tahun yang valid (2000-2099)

### **3. Type Safety:**
- **Type casting:** Semua input di-cast ke tipe data yang benar
- **Float precision:** Perhitungan menggunakan float untuk akurasi
- **Integer validation:** Unit terjual menggunakan integer

---

## 🚀 Fitur Tambahan yang Bisa Dikembangkan

### **1. Visualisasi Data:**
- **Chart BEP:** Grafik break-even point
- **Trend Analysis:** Analisis tren BEP dari waktu ke waktu
- **Comparison Chart:** Perbandingan antar produk

### **2. Export dan Reporting:**
- **PDF Report:** Laporan BEP dalam format PDF
- **Excel Export:** Data BEP dalam format Excel
- **Scheduled Reports:** Laporan otomatis bulanan

### **3. Advanced Analytics:**
- **Scenario Planning:** Multiple skenario BEP
- **Risk Assessment:** Analisis risiko berdasarkan margin of safety
- **Forecasting:** Prediksi BEP berdasarkan trend

---

## 📋 Checklist Testing

### **✅ Testing Mode Kalkulator:**
- [ ] Filter periode berfungsi dengan benar
- [ ] Auto-load data produk berfungsi
- [ ] Perhitungan real-time akurat
- [ ] Target profit analysis berfungsi
- [ ] Analisis sensitivitas berfungsi

### **✅ Testing Mode Per Produk:**
- [ ] CRUD operasi berfungsi
- [ ] Validasi input berfungsi
- [ ] Preview perhitungan akurat
- [ ] Perbandingan dengan data aktual

### **✅ Testing Mode Per Periode:**
- [ ] Perhitungan BEP bulanan akurat
- [ ] Margin of safety dihitung dengan benar
- [ ] Status BEP ditampilkan dengan benar
- [ ] Error handling berfungsi

---

## 🔧 Troubleshooting

### **Masalah Umum:**

#### **1. BEP tidak dapat dihitung:**
- Pastikan ada data penjualan untuk periode yang dipilih
- Periksa apakah biaya variabel tidak melebihi penjualan
- Pastikan margin kontribusi positif

#### **2. Data tidak ter-load:**
- Periksa koneksi database
- Pastikan periode yang dipilih memiliki data
- Refresh halaman jika diperlukan

#### **3. Perhitungan tidak akurat:**
- Periksa input parameter
- Pastikan semua field terisi dengan benar
- Gunakan tombol reset jika diperlukan

---

## 📚 Referensi

### **File yang Dibuat:**
- `app/Livewire/Beps/BepForm.php`
- `resources/views/livewire/beps/bep-form.blade.php`

### **Route yang Tersedia:**
- `/bep-form` → `BepForm::class`

### **Dependencies:**
- Bootstrap untuk UI components
- Livewire untuk interaktivitas
- Carbon untuk manipulasi tanggal

---

## 🎯 Manfaat Fitur

### **1. Pengambilan Keputusan:**
- **Pricing strategy** berdasarkan analisis BEP
- **Cost control** untuk optimasi biaya
- **Sales target** yang realistis

### **2. Perencanaan Bisnis:**
- **Budget planning** berdasarkan BEP
- **Growth strategy** dengan target profit
- **Risk management** melalui margin of safety

### **3. Analisis Performa:**
- **Performance tracking** terhadap target BEP
- **Product profitability** analysis
- **Periodic comparison** untuk trend analysis

---

*Dokumentasi ini dibuat untuk membantu developer dan user memahami fitur analisis BEP yang sudah diperbaiki dalam aplikasi UMKM.*
