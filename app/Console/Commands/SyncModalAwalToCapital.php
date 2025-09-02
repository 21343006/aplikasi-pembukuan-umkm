<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Capitalearly;
use App\Models\Capital;
use Illuminate\Support\Facades\Log;

class SyncModalAwalToCapital extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'modal:sync-awal {--user-id= : ID user yang akan disinkronkan}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sinkronisasi modal awal dari tabel Capitalearly ke tabel Capital';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memulai sinkronisasi modal awal...');

        $userId = $this->option('user-id');
        
        if ($userId) {
            $this->syncForUser($userId);
        } else {
            // Sync untuk semua user
            $users = Capitalearly::select('user_id')->distinct()->get();
            
            if ($users->isEmpty()) {
                $this->warn('Tidak ada data modal awal yang ditemukan.');
                return;
            }

            $this->info("Menemukan {$users->count()} user dengan data modal awal.");
            
            foreach ($users as $user) {
                $this->syncForUser($user->user_id);
            }
        }

        $this->info('Sinkronisasi modal awal selesai!');
    }

    private function syncForUser($userId)
    {
        $this->info("Sinkronisasi untuk user ID: {$userId}");

        // Ambil data modal awal
        $modalAwal = Capitalearly::where('user_id', $userId)->first();
        
        if (!$modalAwal) {
            $this->warn("Tidak ada data modal awal untuk user ID: {$userId}");
            return;
        }

        // Cek apakah sudah ada data modal awal di tabel Capital
        $existingCapital = Capital::where('user_id', $userId)
            ->where('jenis', 'masuk')
            ->where('keperluan', 'Modal Awal')
            ->first();
        
        if ($existingCapital) {
            // Update data yang sudah ada
            $existingCapital->update([
                'nominal' => $modalAwal->modal_awal,
                'tanggal' => $modalAwal->tanggal_input,
                'keterangan' => 'Modal awal untuk memulai usaha (updated)'
            ]);
            
            $this->info("Data modal awal berhasil diupdate untuk user ID: {$userId}");
            Log::info("Modal awal updated for user {$userId}", [
                'old_nominal' => $existingCapital->getOriginal('nominal'),
                'new_nominal' => $modalAwal->modal_awal
            ]);
        } else {
            // Buat data modal awal di tabel Capital
            Capital::create([
                'user_id' => $userId,
                'nama' => 'Modal Awal',
                'tanggal' => $modalAwal->tanggal_input,
                'keperluan' => 'Modal Awal',
                'keterangan' => 'Modal awal untuk memulai usaha',
                'nominal' => $modalAwal->modal_awal,
                'jenis' => 'masuk'
            ]);
            
            $this->info("Data modal awal berhasil dibuat untuk user ID: {$userId}");
            Log::info("Modal awal created for user {$userId}", [
                'nominal' => $modalAwal->modal_awal,
                'tanggal' => $modalAwal->tanggal_input
            ]);
        }
    }
}
