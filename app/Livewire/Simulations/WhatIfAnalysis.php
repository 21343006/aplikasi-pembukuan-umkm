<?php

namespace App\Livewire\Simulations;

use App\Models\Bep;
use App\Models\Expenditure;
use App\Models\FixedCost;
use App\Models\Income;
use Livewire\Component;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WhatIfAnalysis extends Component
{
    #[Title('Simulasi "Apa Jika?" (What-If Analysis)')]

    // Properti untuk filter periode
    public $selectedYear;
    public $selectedMonth;
    public $monthNames = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];

    // Product-specific properties for Simulasi 1
    public $selectedProduk = null;
    public $produkList = [];
    public $hargaJualAwalProduk = 0;
    public $unitTerjualAwalProduk = 0;
    public $biayaVariabelAwalProduk = 0;
    public $bepUnitAwalProduk = 0;

    // Properti untuk data dasar (Business-Wide)
    public $pendapatanTotal = 0;
    public $biayaVariabelTotal = 0;
    public $biayaTetapTotal = 0;
    public $labaAwal = 0;
    public $marginKontribusiRata = 0; // This might not be needed anymore
    public $bepRupiahAwal = 0;

    // Properti untuk simulasi perubahan harga
    public $simulasiHarga = 0; // Persentase perubahan harga
    public $hargaJualBaru = 0; // This will now represent simulated total revenue or product price
    public $labaBaru = 0;
    public $bepUnitBaru = 0; // This will be used for product-specific BEP Unit
    public $bepRupiahBaru = 0;
    public $persentasePerubahanLaba = 0;

    // Properti untuk simulasi kenaikan biaya bahan
    public $persentaseKenaikanBiaya = 10; // Default 10%
    public $biayaVariabelBaru = 0;
    public $labaSetelahKenaikanBiaya = 0;
    public $masihUntung = true;
    public $persentasePerubahanLabaBiaya = 0;

    // Properti untuk simulasi penambahan karyawan
    public $jumlahKaryawanBaru = 1;
    public $gajiPerKaryawan = 2500000; // Default Rp 2.5 juta
    public $tambahBiayaTetap = 0;
    public $bepBaruDenganKaryawan = 0;
    public $estimasiWaktuBalikModal = 0; // dalam bulan
    public $pendapatanBulanan = 0; // This will be the initial pendapatanTotal

    public function mount()
    {
        $this->selectedYear = now()->year;
        $this->selectedMonth = now()->month;
        $this->loadProdukList(); // Added
        $this->loadDataAwal();
        $this->resetSimulasi();
    }

    public function loadProdukList()
    {
        if (!Auth::check()) return;

        try {
            $this->produkList = Income::where('user_id', Auth::id())
                ->select('produk')
                ->whereNotNull('produk')
                ->where('produk', '!=', '')
                ->distinct()
                ->pluck('produk')
                ->toArray();
        } catch (\Exception $e) {
            Log::error('Error loading product list: ' . $e->getMessage());
            $this->produkList = [];
        }
    }

    public function updatedSelectedProduk()
    {
        $this->loadDataAwal();
        $this->resetSimulasi();
    }

    public function updatedSelectedYear()
    {
        $this->loadDataAwal();
        $this->resetSimulasi();
    }

    public function updatedSelectedMonth()
    {
        $this->loadDataAwal();
        $this->resetSimulasi();
    }

    public function loadDataAwal()
    {
        if (!Auth::check()) return;

        try {
            $userId = Auth::id();
            $year = (int)$this->selectedYear;
            $month = (int)$this->selectedMonth;

            // --- Load Business-Wide Data --- (Always loaded)
            $this->pendapatanTotal = Income::where('user_id', $userId)
                ->whereYear('tanggal', $year)
                ->whereMonth('tanggal', $month)
                ->sum('total_pendapatan');

            $this->biayaVariabelTotal = Income::where('user_id', $userId)
                ->whereYear('tanggal', $year)
                ->whereMonth('tanggal', $month)
                ->get()
                ->sum(function ($income) {
                    return $income->biaya_per_unit * $income->jumlah_terjual;
                });

            $this->biayaTetapTotal = FixedCost::where('user_id', $userId)
                ->whereYear('tanggal', $year)
                ->whereMonth('tanggal', $month)
                ->sum('nominal');

            $this->labaAwal = $this->pendapatanTotal - $this->biayaVariabelTotal - $this->biayaTetapTotal;

            $marginKontribusiTotal = $this->pendapatanTotal - $this->biayaVariabelTotal;
            if ($this->pendapatanTotal > 0) {
                $rasioMarginKontribusi = $marginKontribusiTotal / $this->pendapatanTotal;
                if ($rasioMarginKontribusi > 0) {
                    $this->bepRupiahAwal = $this->biayaTetapTotal / $rasioMarginKontribusi;
                } else {
                    $this->bepRupiahAwal = 0; // No contribution margin
                }
            } else {
                $this->bepRupiahAwal = 0; // No revenue
            }

            // --- Load Product-Specific Data for Simulasi 1 if a product is selected ---
            if (!empty($this->selectedProduk)) {
                $incomeProduct = Income::where('user_id', $userId)
                    ->where('produk', $this->selectedProduk)
                    ->whereYear('tanggal', $year)
                    ->whereMonth('tanggal', $month)
                    ->first();

                if ($incomeProduct) {
                    $this->hargaJualAwalProduk = is_numeric($incomeProduct->harga_satuan) ? (float)$incomeProduct->harga_satuan : 0;
                    $this->unitTerjualAwalProduk = is_numeric($incomeProduct->jumlah_terjual) ? (int)$incomeProduct->jumlah_terjual : 0;
                    $this->biayaVariabelAwalProduk = is_numeric($incomeProduct->biaya_per_unit) ? (float)$incomeProduct->biaya_per_unit : 0;

                    // Calculate product-specific BEP Unit
                    $marginKontribusiProduk = $this->hargaJualAwalProduk - $this->biayaVariabelAwalProduk;
                    if ($marginKontribusiProduk > 0) {
                        // Using business-wide fixed cost for product BEP Unit calculation
                        $this->bepUnitAwalProduk = ceil($this->biayaTetapTotal / $marginKontribusiProduk);
                    } else {
                        $this->bepUnitAwalProduk = 0;
                    }
                } else {
                    // Reset product-specific data if product not found for the period
                    $this->hargaJualAwalProduk = 0;
                    $this->unitTerjualAwalProduk = 0;
                    $this->biayaVariabelAwalProduk = 0;
                    $this->bepUnitAwalProduk = 0;
                }
            } else {
                // Ensure product-specific data is reset if no product is selected
                $this->hargaJualAwalProduk = 0;
                $this->unitTerjualAwalProduk = 0;
                $this->biayaVariabelAwalProduk = 0;
                $this->bepUnitAwalProduk = 0;
            }

            // Set initial values for simulations (these will be overridden in simulasiPerubahanHarga if product selected)
            $this->hargaJualBaru = $this->pendapatanTotal; // Default to business-wide total revenue
            $this->biayaVariabelBaru = $this->biayaVariabelTotal;
            $this->pendapatanBulanan = $this->pendapatanTotal; // Initial total revenue

        } catch (\Exception $e) {
            Log::error('Error loading initial data for What-If Analysis: ' . $e->getMessage());
            $this->resetDataAwal(); // Reset to 0 on error
        }
    }

    public function simulasiPerubahanHarga()
    {
        if (!empty($this->selectedProduk)) {
            // Product-specific simulation
            if ($this->hargaJualAwalProduk <= 0) return;

            try {
                $this->hargaJualBaru = $this->hargaJualAwalProduk * (1 + ($this->simulasiHarga / 100));
                $pendapatanBaruProduk = $this->hargaJualBaru * $this->unitTerjualAwalProduk;
                $biayaVariabelTotalProduk = $this->biayaVariabelAwalProduk * $this->unitTerjualAwalProduk;
                $this->labaBaru = $pendapatanBaruProduk - $biayaVariabelTotalProduk - $this->biayaTetapTotal; // Using business-wide fixed cost

                // Percentage change in profit (product-specific)
                $labaAwalProduk = ($this->hargaJualAwalProduk * $this->unitTerjualAwalProduk) - ($this->biayaVariabelAwalProduk * $this->unitTerjualAwalProduk) - $this->biayaTetapTotal;
                if ($labaAwalProduk != 0) {
                    $this->persentasePerubahanLaba = (($this->labaBaru - $labaAwalProduk) / abs($labaAwalProduk)) * 100;
                } else {
                    $this->persentasePerubahanLaba = $this->labaBaru > 0 ? 100 : 0;
                }

                // BEP Unit Baru (product-specific)
                $marginKontribusiBaruProduk = $this->hargaJualBaru - $this->biayaVariabelAwalProduk;
                if ($marginKontribusiBaruProduk > 0) {
                    $this->bepUnitBaru = ceil($this->biayaTetapTotal / $marginKontribusiBaruProduk);
                    $this->bepRupiahBaru = $this->bepUnitBaru * $this->hargaJualBaru; // BEP Rupiah for product
                } else {
                    $this->bepUnitBaru = 0;
                    $this->bepRupiahBaru = 0;
                }
            } catch (\Exception $e) {
                Log::error('Error in product price change simulation: ' . $e->getMessage());
            }
        } else {
            // Business-wide simulation (existing logic)
            if ($this->pendapatanTotal <= 0) return;

            try {
                $pendapatanBaru = $this->pendapatanTotal * (1 + ($this->simulasiHarga / 100));
                $this->hargaJualBaru = $pendapatanBaru; // Represents simulated total revenue

                $this->labaBaru = $pendapatanBaru - $this->biayaVariabelTotal - $this->biayaTetapTotal;

                if ($this->labaAwal != 0) {
                    $this->persentasePerubahanLaba = (($this->labaBaru - $this->labaAwal) / abs($this->labaAwal)) * 100;
                } else {
                    $this->persentasePerubahanLaba = $this->labaBaru > 0 ? 100 : 0;
                }

                $marginKontribusiBaru = $pendapatanBaru - $this->biayaVariabelTotal;
                if ($pendapatanBaru > 0) {
                    $rasioMarginKontribusiBaru = $marginKontribusiBaru / $pendapatanBaru;
                    if ($rasioMarginKontribusiBaru > 0) {
                        $this->bepRupiahBaru = $this->biayaTetapTotal / $rasioMarginKontribusiBaru;
                    } else {
                        $this->bepRupiahBaru = 0;
                    }
                } else {
                    $this->bepRupiahBaru = 0;
                }
                $this->bepUnitBaru = 0; // Not applicable for business-wide
            } catch (\Exception $e) {
                Log::error('Error in business-wide revenue change simulation: ' . $e->getMessage());
            }
        }
    }

    public function simulasiKenaikanBiaya()
    {
        if ($this->biayaVariabelTotal <= 0) return;

        try {
            // Calculate new total variable costs based on percentage increase
            $this->biayaVariabelBaru = $this->biayaVariabelTotal * (1 + ($this->persentaseKenaikanBiaya / 100));

            // Calculate new profit
            $this->labaSetelahKenaikanBiaya = $this->pendapatanTotal - $this->biayaVariabelBaru - $this->biayaTetapTotal;

            // Check if still profitable
            $this->masihUntung = $this->labaSetelahKenaikanBiaya > 0;

            // Calculate percentage change in profit
            if ($this->labaAwal != 0) {
                $this->persentasePerubahanLabaBiaya = (($this->labaSetelahKenaikanBiaya - $this->labaAwal) / abs($this->labaAwal)) * 100;
            } else {
                $this->persentasePerubahanLabaBiaya = $this->labaSetelahKenaikanBiaya > 0 ? 100 : 0;
            }
        } catch (\Exception $e) {
            Log::error('Error in cost increase simulation: ' . $e->getMessage());
        }
    }

    public function simulasiTambahKaryawan()
    {
        if ($this->biayaTetapTotal <= 0 || $this->pendapatanBulanan <= 0) return;

        try {
            // Calculate additional fixed costs from new employees
            $this->tambahBiayaTetap = $this->jumlahKaryawanBaru * $this->gajiPerKaryawan;
            $biayaTetapBaru = $this->biayaTetapTotal + $this->tambahBiayaTetap;

            // Calculate new BEP in Rupiah with additional employees
            $marginKontribusi = $this->pendapatanTotal - $this->biayaVariabelTotal;
            if ($this->pendapatanTotal > 0) {
                $rasioMarginKontribusi = $marginKontribusi / $this->pendapatanTotal;
                if ($rasioMarginKontribusi > 0) {
                    $this->bepBaruDenganKaryawan = $biayaTetapBaru / $rasioMarginKontribusi;
                } else {
                    $this->bepBaruDenganKaryawan = 0;
                }
            } else {
                $this->bepBaruDenganKaryawan = 0;
            }

            // Calculate estimated payback period (in months)
            $labaBulanan = $this->labaAwal;
            if ($labaBulanan > 0) {
                $this->estimasiWaktuBalikModal = ceil($this->tambahBiayaTetap / $labaBulanan);
            } else {
                $this->estimasiWaktuBalikModal = 0; // Cannot payback if no profit
            }
        } catch (\Exception $e) {
            Log::error('Error in employee addition simulation: ' . $e->getMessage());
        }
    }

    public function resetDataAwal()
    {
        $this->pendapatanTotal = 0;
        $this->biayaVariabelTotal = 0;
        $this->biayaTetapTotal = 0;
        $this->labaAwal = 0;
        $this->bepRupiahAwal = 0;
        $this->pendapatanBulanan = 0;

        // Product-specific resets
        $this->hargaJualAwalProduk = 0;
        $this->unitTerjualAwalProduk = 0;
        $this->biayaVariabelAwalProduk = 0;
        $this->bepUnitAwalProduk = 0;
    }

    public function resetSimulasi()
    {
        // Reset price change simulation
        $this->simulasiHarga = 0;
        // Conditional reset for hargaJualBaru and bepUnitBaru
        if (!empty($this->selectedProduk)) {
            $this->hargaJualBaru = $this->hargaJualAwalProduk; // Reset to product-specific initial price
            $this->bepUnitBaru = $this->bepUnitAwalProduk; // Reset to product-specific BEP Unit
        } else {
            $this->hargaJualBaru = $this->pendapatanTotal; // Reset to business-wide total revenue
            $this->bepUnitBaru = 0; // Not applicable for business-wide
        }
        $this->labaBaru = $this->labaAwal; // This will be the business-wide labaAwal
        $this->bepRupiahBaru = $this->bepRupiahAwal; // This will be the business-wide bepRupiahAwal
        $this->persentasePerubahanLaba = 0;

        // Reset cost increase simulation
        $this->persentaseKenaikanBiaya = 10;
        $this->biayaVariabelBaru = $this->biayaVariabelTotal; // Reset to business-wide total variable costs
        $this->labaSetelahKenaikanBiaya = $this->labaAwal; // This will be the business-wide labaAwal
        $this->masihUntung = $this->labaAwal > 0;
        $this->persentasePerubahanLabaBiaya = 0;

        // Reset employee addition simulation
        $this->jumlahKaryawanBaru = 1;
        $this->gajiPerKaryawan = 2500000;
        $this->tambahBiayaTetap = 0;
        $this->bepBaruDenganKaryawan = 0;
        $this->estimasiWaktuBalikModal = 0;
    }

    public function render()
    {
        return view('livewire.simulations.what-if-analysis');
    }
}