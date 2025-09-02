<?php

namespace App\Observers;

use App\Models\Capitalearly;
use App\Models\Capital;
use Illuminate\Support\Facades\Log;

class CapitalearlyObserver
{
    /**
     * Handle the Capitalearly "created" event.
     */
    public function created(Capitalearly $capitalearly): void
    {
        $this->syncToCapital($capitalearly, 'created');
    }

    /**
     * Handle the Capitalearly "updated" event.
     */
    public function updated(Capitalearly $capitalearly): void
    {
        $this->syncToCapital($capitalearly, 'updated');
    }

    /**
     * Handle the Capitalearly "deleted" event.
     */
    public function deleted(Capitalearly $capitalearly): void
    {
        // Hapus data modal awal dari tabel Capital
        Capital::where('user_id', $capitalearly->user_id)
            ->where('jenis', 'masuk')
            ->where('keperluan', 'Modal Awal')
            ->delete();
            
        Log::info("Modal awal deleted from Capital table for user {$capitalearly->user_id}");
    }

    /**
     * Handle the Capitalearly "restored" event.
     */
    public function restored(Capitalearly $capitalearly): void
    {
        $this->syncToCapital($capitalearly, 'restored');
    }

    /**
     * Handle the Capitalearly "force deleted" event.
     */
    public function forceDeleted(Capitalearly $capitalearly): void
    {
        // Hapus data modal awal dari tabel Capital
        Capital::where('user_id', $capitalearly->user_id)
            ->where('jenis', 'masuk')
            ->where('keperluan', 'Modal Awal')
            ->forceDelete();
            
        Log::info("Modal awal force deleted from Capital table for user {$capitalearly->user_id}");
    }

    /**
     * Sync modal awal ke tabel Capital
     */
    private function syncToCapital(Capitalearly $capitalearly, string $action): void
    {
        try {
            // Cek apakah sudah ada data modal awal di tabel Capital
            $existingCapital = Capital::where('user_id', $capitalearly->user_id)
                ->where('jenis', 'masuk')
                ->where('keperluan', 'Modal Awal')
                ->first();
            
            if ($existingCapital) {
                // Update data yang sudah ada
                $existingCapital->update([
                    'nominal' => $capitalearly->modal_awal,
                    'tanggal' => $capitalearly->tanggal_input,
                    'keterangan' => 'Modal awal untuk memulai usaha (auto-sync)'
                ]);
                
                Log::info("Modal awal updated in Capital table for user {$capitalearly->user_id}", [
                    'action' => $action,
                    'old_nominal' => $existingCapital->getOriginal('nominal'),
                    'new_nominal' => $capitalearly->modal_awal
                ]);
            } else {
                // Buat data modal awal di tabel Capital
                Capital::create([
                    'user_id' => $capitalearly->user_id,
                    'tanggal' => $capitalearly->tanggal_input,
                    'keperluan' => 'Modal Awal',
                    'keterangan' => 'Modal awal untuk memulai usaha (auto-sync)',
                    'nominal' => $capitalearly->modal_awal,
                    'jenis' => 'masuk'
                ]);
                
                Log::info("Modal awal created in Capital table for user {$capitalearly->user_id}", [
                    'action' => $action,
                    'nominal' => $capitalearly->modal_awal,
                    'tanggal' => $capitalearly->tanggal_input
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Error syncing modal awal to Capital table for user {$capitalearly->user_id}", [
                'action' => $action,
                'error' => $e->getMessage(),
                'modal_awal_id' => $capitalearly->id
            ]);
        }
    }
}
