<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            Log::info('Starting capitals table migration...');
            
            // Cek apakah tabel sudah ada
            if (Schema::hasTable('capitals')) {
                Log::info('Capitals table already exists, checking structure...');
                
                // Cek struktur tabel yang ada
                $this->upgradeExistingTable();
                
            } else {
                Log::info('Creating new capitals table...');
                
                // Buat tabel baru dengan struktur lengkap
                $this->createNewTable();
            }
            
            // Validasi akhir struktur tabel
            $this->validateTableStructure();
            
            Log::info('Capitals table migration completed successfully');
            
        } catch (\Exception $e) {
            Log::error('Capitals migration failed: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Upgrade existing table structure
     */
    private function upgradeExistingTable(): void
    {
        $columnsToAdd = [];
        $columnsToModify = [];

        // Cek kolom yang hilang
        if (!Schema::hasColumn('capitals', 'keperluan')) {
            $columnsToAdd[] = 'keperluan';
        }

        if (!Schema::hasColumn('capitals', 'keterangan')) {
            $columnsToAdd[] = 'keterangan';
        }

        if (!Schema::hasColumn('capitals', 'jenis')) {
            $columnsToAdd[] = 'jenis';
        }

        // Tambahkan kolom yang hilang
        if (!empty($columnsToAdd)) {
            Schema::table('capitals', function (Blueprint $table) use ($columnsToAdd) {
                if (in_array('keperluan', $columnsToAdd)) {
                    $table->string('keperluan', 255)->nullable()->after('tanggal');
                    Log::info('Added keperluan column');
                }

                if (in_array('keterangan', $columnsToAdd)) {
                    $table->text('keterangan')->nullable()->after('keperluan');
                    Log::info('Added keterangan column');
                }

                if (in_array('jenis', $columnsToAdd)) {
                    $table->enum('jenis', ['masuk', 'keluar'])->default('masuk')->after('nominal');
                    Log::info('Added jenis column');
                }
            });

            // Update existing records untuk kolom jenis
            if (in_array('jenis', $columnsToAdd)) {
                DB::table('capitals')->whereNull('jenis')->update(['jenis' => 'masuk']);
                Log::info('Updated existing records with default jenis value');
            }
        }

        // Pastikan tipe data nominal benar
        $this->ensureNominalColumnType();

        // Tambahkan index jika belum ada
        $this->addMissingIndexes();
    }

    /**
     * Create new table with complete structure
     */
    private function createNewTable(): void
    {
        Schema::create('capitals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('tanggal');
            $table->string('keperluan', 255)->nullable();
            $table->text('keterangan')->nullable();
            $table->decimal('nominal', 15, 2);
            $table->enum('jenis', ['masuk', 'keluar'])->default('masuk');
            $table->timestamps();
            
            // Index untuk optimasi query
            $table->index(['user_id', 'tanggal'], 'capitals_user_tanggal_idx');
            $table->index(['user_id', 'jenis'], 'capitals_user_jenis_idx');
            $table->index(['tanggal', 'jenis'], 'capitals_tanggal_jenis_idx');
            $table->index(['user_id', 'tanggal', 'jenis'], 'capitals_user_tanggal_jenis_idx');
        });

        Log::info('Created new capitals table with complete structure');
    }

    /**
     * Ensure nominal column has correct type
     */
    private function ensureNominalColumnType(): void
    {
        try {
            // Coba modify kolom nominal untuk memastikan tipe yang benar
            Schema::table('capitals', function (Blueprint $table) {
                $table->decimal('nominal', 15, 2)->change();
            });
            Log::info('Updated nominal column type to decimal(15,2)');
        } catch (\Exception $e) {
            // Jika gagal modify, log warning tapi jangan stop migrasi
            Log::warning('Could not update nominal column type: ' . $e->getMessage());
        }
    }

    /**
     * Add missing indexes for optimization
     */
    private function addMissingIndexes(): void
    {
        try {
            $existingIndexes = Schema::getConnection()
                ->getDoctrineSchemaManager()
                ->listTableIndexes('capitals');

            $indexesToCreate = [
                'capitals_user_tanggal_idx' => ['user_id', 'tanggal'],
                'capitals_user_jenis_idx' => ['user_id', 'jenis'],
                'capitals_tanggal_jenis_idx' => ['tanggal', 'jenis'],
                'capitals_user_tanggal_jenis_idx' => ['user_id', 'tanggal', 'jenis']
            ];

            foreach ($indexesToCreate as $indexName => $columns) {
                if (!isset($existingIndexes[$indexName])) {
                    Schema::table('capitals', function (Blueprint $table) use ($columns, $indexName) {
                        $table->index($columns, $indexName);
                    });
                    Log::info("Created index: {$indexName}");
                }
            }
        } catch (\Exception $e) {
            Log::warning('Could not create indexes: ' . $e->getMessage());
        }
    }

    /**
     * Validate final table structure
     */
    private function validateTableStructure(): void
    {
        $requiredColumns = ['id', 'user_id', 'tanggal', 'keperluan', 'keterangan', 'nominal', 'jenis', 'created_at', 'updated_at'];
        
        foreach ($requiredColumns as $column) {
            if (!Schema::hasColumn('capitals', $column)) {
                throw new \Exception("Required column '{$column}' is missing from capitals table");
            }
        }

        // Count records untuk validasi
        $recordCount = DB::table('capitals')->count();
        Log::info("Capitals table validation passed. Total records: {$recordCount}");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            Log::info('Rolling back capitals table migration...');
            
            // Backup data jika ada
            $recordCount = 0;
            if (Schema::hasTable('capitals')) {
                $recordCount = DB::table('capitals')->count();
                if ($recordCount > 0) {
                    Log::warning("Dropping capitals table with {$recordCount} records");
                }
            }

            Schema::dropIfExists('capitals');
            Log::info('Capitals table dropped successfully');
            
        } catch (\Exception $e) {
            Log::error('Rollback failed: ' . $e->getMessage());
            throw $e;
        }
    }
};