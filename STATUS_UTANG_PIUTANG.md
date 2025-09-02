# Status Utang Piutang - Fitur Baru

## Deskripsi
Sistem status utang piutang telah diperbarui untuk memberikan informasi yang lebih detail dan akurat tentang kondisi pembayaran dan keterlambatan.

## Logika Status

### 1. Status Berdasarkan Jumlah Pembayaran

#### Lunas
- **Kondisi**: `paid_amount >= amount`
- **Tampilan**: Badge hijau dengan teks "Lunas"
- **Keterangan**: Utang/piutang sudah dibayar penuh

#### Dibayar Sebagian
- **Kondisi**: `0 < paid_amount < amount`
- **Tampilan**: Badge biru dengan teks "Dibayar Sebagian"
- **Keterangan**: Sudah ada pembayaran tetapi belum lunas

#### Belum Dibayar
- **Kondisi**: `paid_amount = 0`
- **Tampilan**: Badge kuning dengan teks "Belum Dibayar"
- **Keterangan**: Belum ada pembayaran sama sekali

### 2. Status dengan Keterlambatan

#### Terlambat (Belum Dibayar)
- **Kondisi**: `paid_amount = 0` dan `due_date < now()`
- **Tampilan**: Badge merah dengan teks "Belum Dibayar (Terlambat X hari)"
- **Keterangan**: Belum dibayar dan sudah melewati tanggal jatuh tempo

#### Terlambat (Dibayar Sebagian)
- **Kondisi**: `0 < paid_amount < amount` dan `due_date < now()`
- **Tampilan**: Badge merah dengan teks "Dibayar Sebagian (Terlambat X hari)"
- **Keterangan**: Sudah dibayar sebagian tetapi masih terlambat

## Implementasi Teknis

### Model Attributes Baru

#### Debt dan Receivable Models
```php
// Status detail berdasarkan kondisi pembayaran dan keterlambatan
public function getDetailedStatusAttribute()

// Class CSS untuk badge styling
public function getStatusBadgeClassAttribute()

// Teks status untuk display
public function getStatusTextAttribute()
```

### Warna Badge
- **Hijau** (`bg-success`): Lunas
- **Biru** (`bg-info`): Dibayar Sebagian (dalam waktu)
- **Kuning** (`bg-warning`): Belum Dibayar (dalam waktu)
- **Merah** (`bg-danger`): Terlambat (baik belum dibayar maupun sebagian)

### Contoh Tampilan

#### Dalam Waktu
- ✅ **Lunas** - Utang sudah dibayar penuh
- 🔵 **Dibayar Sebagian** - Sudah ada pembayaran, belum lunas
- 🟡 **Belum Dibayar** - Belum ada pembayaran sama sekali

#### Terlambat
- 🔴 **Belum Dibayar (Terlambat 5 hari)** - Belum dibayar dan sudah terlambat
- 🔴 **Dibayar Sebagian (Terlambat 3 hari)** - Sudah dibayar sebagian tapi terlambat

## Data Dummy

Seeder telah diperbarui untuk menghasilkan data dengan berbagai skenario:

1. **Lunas** - Dibayar tepat waktu
2. **Dibayar Sebagian** - Dibayar sebagian dalam waktu
3. **Belum Dibayar** - Belum dibayar dalam waktu
4. **Terlambat** - Baik belum dibayar maupun sebagian, dengan tanggal jatuh tempo di masa lalu

## Penggunaan

Status akan otomatis diperbarui saat:
- Menambah utang/piutang baru
- Mencatat pembayaran
- Mengedit data utang/piutang

Sistem akan menghitung secara otomatis:
- Jumlah hari keterlambatan
- Status berdasarkan jumlah pembayaran
- Warna badge yang sesuai
