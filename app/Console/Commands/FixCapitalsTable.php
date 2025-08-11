<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Log;
use App\Models\Capital;

class FixCapitalsTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:capitals-table 
                           {--force : Force table recreation}
                           {--backup : Create backup before changes}
                           {--check-only : Only check table structure without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix capitals table structure ensuring backward compatibility and all required columns';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Starting capitals table structure check...');

        try {
            // Mode check-only
            if ($this->option('check-only')) {
                return $this->checkTableStructure();
            }

            // Backup data jika diminta
            if ($this->option('backup') && Schema::hasTable('capitals')) {
                $this->createBackup();
            }

            // Jika force flag digunakan, drop dan recreate table
            if ($this->option('force')) {
                return $this->forceRecreateTable();
            }

            // Mode normal: incremental update
            return $this->incrementalUpdate();

        } catch (\Exception $e) {
            $this->error('❌ Error fixing capitals table: ' . $e->getMessage());
            $this->error('📍 File: ' . $e->getFile() . ':' . $e->getLine());
            
            if ($this->option('verbose')) {
                $this->error('🔍 Trace: ' . $e->getTraceAsString());
            }
            
            Log::error('FixCapitalsTable command failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return 1;
        }
    }

    /**
     * Check table structure only
     */
    private function checkTableStructure(): int
    {
        $this->info('📋 Checking table structure (read-only mode)...');

        if (!Schema::hasTable('capitals')) {
            $this->warn('⚠️  Table "capitals" does not exist');
            $this->info('💡 Run without --check-only to create the table');
            return 1;
        }

        $this->info('✅ Table "capitals" exists');
        
        // Check required columns
        $requiredColumns = [
            'id' => 'Primary Key',
            'user_id' => 'Foreign Key to users',
            'tanggal' => 'Date field',
            'nominal' => 'Amount field',
            'created_at' => 'Timestamp',
            'updated_at' => 'Timestamp'
        ];

        $optionalColumns = [
            'keperluan' => 'Purpose field (optional)',
            'keterangan' => 'Description field (optional)',
            'jenis' => 'Transaction type field (optional)'
        ];

        $this->line('');
        $this->info('📊 Required Columns Status:');
        foreach ($requiredColumns as $column => $description) {
            $exists = Schema::hasColumn('capitals', $column);
            $status = $exists ? '✅' : '❌';
            $this->line("  {$status} {$column} - {$description}");
        }

        $this->line('');
        $this->info('📋 Optional Columns Status:');
        foreach ($optionalColumns as $column => $description) {
            $exists = Schema::hasColumn('capitals', $column);
            $status = $exists ? '✅' : '⚪';
            $this->line("  {$status} {$column} - {$description}");
        }

        // Show record count
        $recordCount = DB::table('capitals')->count();
        $this->line('');
        $this->info("📈 Total records: {$recordCount}");

        // Check data integrity
        $this->checkDataIntegrity();

        return 0;
    }

    /**
     * Force recreate table
     */
    private function forceRecreateTable(): int
    {
        if (Schema::hasTable('capitals')) {
            $recordCount = DB::table('capitals')->count();
            
            if ($recordCount > 0) {
                $this->warn("⚠️  This will delete {$recordCount} existing records in capitals table");
                
                if (!$this->confirm('Are you sure you want to continue?')) {
                    $this->info('Operation cancelled.');
                    return 0;
                }

                // Create backup before dropping
                $this->createBackup();
            }

            Schema::dropIfExists('capitals');
            $this->warn('🗑️  Dropped existing capitals table');
        }

        $this->info('📋 Creating new capitals table...');
        $this->createCompleteTable();
        
        $this->info('✅ Capitals table recreated successfully!');
        $this->showTableStructure();
        
        return 0;
    }

    /**
     * Incremental update mode
     */
    private function incrementalUpdate(): int
    {
        if (!Schema::hasTable('capitals')) {
            $this->info('📋 Creating new capitals table...');
            $this->createCompleteTable();
            $this->info('✅ Capitals table created successfully!');
            $this->showTableStructure();
            return 0;
        }

        $this->info('📋 Table exists. Checking for missing columns...');
        $columnsAdded = 0;
        $columnsUpdated = 0;

        // Required columns check
        $requiredColumns = [
            'user_id' => ['type' => 'foreignId', 'constraint' => 'users', 'onDelete' => 'cascade'],
            'tanggal' => ['type' => 'date'],
            'nominal' => ['type' => 'decimal', 'precision' => 15, 'scale' => 2]
        ];

        foreach ($requiredColumns as $columnName => $definition) {
            if (!Schema::hasColumn('capitals', $columnName)) {
                $this->addRequiredColumn($columnName, $definition);
                $columnsAdded++;
            }
        }

        // Optional columns check
        $optionalColumns = [
            'keperluan' => ['type' => 'string', 'length' => 255, 'nullable' => true],
            'keterangan' => ['type' => 'text', 'nullable' => true],
            'jenis' => ['type' => 'enum', 'values' => ['masuk', 'keluar'], 'default' => 'masuk'],
        ];

        foreach ($optionalColumns as $columnName => $definition) {
            if (!Schema::hasColumn('capitals', $columnName)) {
                $this->addOptionalColumn($columnName, $definition);
                $columnsAdded++;
            }
        }

        // Fix nominal column if needed
        if ($this->shouldFixNominalColumn()) {
            $this->fixNominalColumn();
            $columnsUpdated++;
        }

        // Update existing records
        $this->updateExistingRecords();

        // Add missing indexes
        $indexesAdded = $this->addMissingIndexes();

        // Clear model cache
        Capital::clearColumnCache();

        // Summary
        if ($columnsAdded === 0 && $columnsUpdated === 0 && $indexesAdded === 0) {
            $this->info('✅ All required columns and indexes already exist with correct structure.');
        } else {
            if ($columnsAdded > 0) {
                $this->info("✅ Successfully added {$columnsAdded} column(s)");
            }
            if ($columnsUpdated > 0) {
                $this->info("✅ Successfully updated {$columnsUpdated} column(s)");
            }
            if ($indexesAdded > 0) {
                $this->info("✅ Successfully added {$indexesAdded} index(es)");
            }
        }

        $this->showTableStructure();
        return 0;
    }

    /**
     * Create complete table structure
     */
    private function createCompleteTable(): void
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
            
            // Indexes for optimization
            $table->index(['user_id', 'tanggal'], 'capitals_user_tanggal_idx');
            $table->index(['user_id', 'jenis'], 'capitals_user_jenis_idx');
            $table->index(['tanggal', 'jenis'], 'capitals_tanggal_jenis_idx');
            $table->index(['user_id', 'tanggal', 'jenis'], 'capitals_user_tanggal_jenis_idx');
        });
    }

    /**
     * Add required column
     */
    private function addRequiredColumn(string $columnName, array $definition): void
    {
        Schema::table('capitals', function (Blueprint $table) use ($columnName, $definition) {
            switch ($definition['type']) {
                case 'foreignId':
                    $column = $table->foreignId($columnName);
                    if (isset($definition['constraint'])) {
                        $column->constrained($definition['constraint']);
                        if (isset($definition['onDelete'])) {
                            $column->onDelete($definition['onDelete']);
                        }
                    }
                    break;
                    
                case 'date':
                    $table->date($columnName);
                    break;
                    
                case 'decimal':
                    $table->decimal($columnName, $definition['precision'] ?? 15, $definition['scale'] ?? 2);
                    break;
            }
        });
        
        $this->info("✅ Added required column: {$columnName}");
    }

    /**
     * Add optional column
     */
    private function addOptionalColumn(string $columnName, array $definition): void
    {
        Schema::table('capitals', function (Blueprint $table) use ($columnName, $definition) {
            $column = null;
            
            switch ($definition['type']) {
                case 'string':
                    $column = $table->string($columnName, $definition['length'] ?? 255);
                    break;
                    
                case 'text':
                    $column = $table->text($columnName);
                    break;
                    
                case 'enum':
                    $column = $table->enum($columnName, $definition['values']);
                    if (isset($definition['default'])) {
                        $column->default($definition['default']);
                    }
                    break;
            }
            
            if ($column && ($definition['nullable'] ?? false)) {
                $column->nullable();
            }
            
            // Position columns appropriately
            if ($columnName === 'keperluan') {
                $column->after('tanggal');
            } elseif ($columnName === 'keterangan') {
                $column->after('keperluan');
            } elseif ($columnName === 'jenis') {
                $column->after('nominal');
            }
        });
        
        $this->info("✅ Added optional column: {$columnName}");
    }

    /**
     * Check if nominal column needs fixing
     */
    private function shouldFixNominalColumn(): bool
    {
        try {
            $columns = Schema::getConnection()->getDoctrineSchemaManager()->listTableColumns('capitals');
            
            if (isset($columns['nominal'])) {
                $nominalColumn = $columns['nominal'];
                $typeName = $nominalColumn->getType()->getName();
                
                return !in_array($typeName, ['decimal', 'numeric']) || 
                       $nominalColumn->getPrecision() < 15 || 
                       $nominalColumn->getScale() < 2;
            }
            
            return false;
        } catch (\Exception $e) {
            $this->warn("⚠️  Could not check nominal column type: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Fix nominal column type
     */
    private function fixNominalColumn(): void
    {
        try {
            Schema::table('capitals', function (Blueprint $table) {
                $table->decimal('nominal', 15, 2)->change();
            });
            $this->info("✅ Fixed nominal column type to decimal(15,2)");
        } catch (\Exception $e) {
            $this->warn("⚠️  Could not fix nominal column: " . $e->getMessage());
        }
    }

    /**
     * Update existing records
     */
    private function updateExistingRecords(): void
    {
        try {
            // Update records dengan jenis NULL menjadi 'masuk'
            if (Schema::hasColumn('capitals', 'jenis')) {
                $nullJenisCount = DB::table('capitals')->whereNull('jenis')->count();
                if ($nullJenisCount > 0) {
                    DB::table('capitals')->whereNull('jenis')->update(['jenis' => 'masuk']);
                    $this->info("✅ Updated {$nullJenisCount} records with default jenis value");
                }
            }

            // Pastikan semua nominal positif
            $negativeCount = DB::table('capitals')->where('nominal', '<', 0)->count();
            if ($negativeCount > 0) {
                DB::table('capitals')->where('nominal', '<', 0)->update([
                    'nominal' => DB::raw('ABS(nominal)')
                ]);
                $this->info("✅ Fixed {$negativeCount} negative nominal values");
            }

        } catch (\Exception $e) {
            $this->warn("⚠️  Could not update existing records: " . $e->getMessage());
        }
    }

    /**
     * Add missing indexes
     */
    private function addMissingIndexes(): int
    {
        $indexesAdded = 0;
        
        try {
            $existingIndexes = collect(Schema::getConnection()
                ->getDoctrineSchemaManager()
                ->listTableIndexes('capitals'))
                ->keys()
                ->map(fn($key) => strtolower($key))
                ->toArray();

            $indexesToCreate = [
                'capitals_user_tanggal_idx' => ['user_id', 'tanggal'],
                'capitals_user_jenis_idx' => ['user_id', 'jenis'],
                'capitals_tanggal_jenis_idx' => ['tanggal', 'jenis'],
                'capitals_user_tanggal_jenis_idx' => ['user_id', 'tanggal', 'jenis']
            ];

            foreach ($indexesToCreate as $indexName => $columns) {
                if (!in_array(strtolower($indexName), $existingIndexes)) {
                    // Check if all columns exist before creating index
                    $allColumnsExist = true;
                    foreach ($columns as $column) {
                        if (!Schema::hasColumn('capitals', $column)) {
                            $allColumnsExist = false;
                            break;
                        }
                    }

                    if ($allColumnsExist) {
                        Schema::table('capitals', function (Blueprint $table) use ($columns, $indexName) {
                            $table->index($columns, $indexName);
                        });
                        $this->info("✅ Created index: {$indexName}");
                        $indexesAdded++;
                    }
                }
            }
        } catch (\Exception $e) {
            $this->warn('⚠️  Could not create some indexes: ' . $e->getMessage());
        }

        return $indexesAdded;
    }

    /**
     * Create backup of existing data
     */
    private function createBackup(): void
    {
        try {
            $timestamp = now()->format('Y_m_d_H_i_s');
            $backupTable = "capitals_backup_{$timestamp}";
            
            $recordCount = DB::table('capitals')->count();
            
            if ($recordCount > 0) {
                DB::statement("CREATE TABLE {$backupTable} AS SELECT * FROM capitals");
                $this->info("📦 Created backup table: {$backupTable} ({$recordCount} records)");
            } else {
                $this->info("📦 No records to backup");
            }
        } catch (\Exception $e) {
            $this->warn("⚠️  Could not create backup: " . $e->getMessage());
        }
    }

    /**
     * Check data integrity
     */
    private function checkDataIntegrity(): void
    {
        try {
            $this->line('');
            $this->info('🔍 Data Integrity Check:');

            // Check for null user_id
            $nullUserCount = DB::table('capitals')->whereNull('user_id')->count();
            $status = $nullUserCount === 0 ? '✅' : '❌';
            $this->line("  {$status} Records with null user_id: {$nullUserCount}");

            // Check for future dates
            $futureDatesCount = DB::table('capitals')->where('tanggal', '>', now())->count();
            $status = $futureDatesCount === 0 ? '✅' : '⚠️ ';
            $this->line("  {$status} Records with future dates: {$futureDatesCount}");

            // Check for negative amounts
            $negativeCount = DB::table('capitals')->where('nominal', '<', 0)->count();
            $status = $negativeCount === 0 ? '✅' : '⚠️ ';
            $this->line("  {$status} Records with negative amounts: {$negativeCount}");

            // Check for very large amounts (potential data errors)
            $largeAmountCount = DB::table('capitals')->where('nominal', '>', 999999999999)->count();
            $status = $largeAmountCount === 0 ? '✅' : '⚠️ ';
            $this->line("  {$status} Records with very large amounts (>999B): {$largeAmountCount}");

        } catch (\Exception $e) {
            $this->warn("⚠️  Could not perform data integrity check: " . $e->getMessage());
        }
    }

    /**
     * Show current table structure
     */
    private function showTableStructure(): void
    {
        try {
            $this->line('');
            $this->info('📊 Current capitals table structure:');
            
            $columns = Schema::getColumnListing('capitals');
            $columnDetails = [];
            
            try {
                $doctrineColumns = Schema::getConnection()->getDoctrineSchemaManager()->listTableColumns('capitals');
                foreach ($columns as $column) {
                    if (isset($doctrineColumns[$column])) {
                        $doctrineColumn = $doctrineColumns[$column];
                        $type = $doctrineColumn->getType()->getName();
                        $nullable = !$doctrineColumn->getNotnull() ? ' (nullable)' : '';
                        $default = $doctrineColumn->getDefault() ? " [default: {$doctrineColumn->getDefault()}]" : '';
                        $columnDetails[$column] = "{$type}{$nullable}{$default}";
                    } else {
                        $columnDetails[$column] = 'unknown type';
                    }
                }
            } catch (\Exception $e) {
                // Fallback to simple listing
                foreach ($columns as $column) {
                    $columnDetails[$column] = '';
                }
            }

            foreach ($columnDetails as $column => $details) {
                $this->line("  📋 {$column} {$details}");
            }

            // Show indexes
            try {
                $indexes = Schema::getConnection()->getDoctrineSchemaManager()->listTableIndexes('capitals');
                if (!empty($indexes)) {
                    $this->line('');
                    $this->info('🔍 Indexes:');
                    foreach ($indexes as $indexName => $index) {
                        $columns = implode(', ', $index->getColumns());
                        $this->line("  📌 {$indexName}: ({$columns})");
                    }
                }
            } catch (\Exception $e) {
                // Ignore index listing errors
            }

            // Show row count
            $rowCount = DB::table('capitals')->count();
            $this->line('');
            $this->info("📈 Total records: {$rowCount}");
            
        } catch (\Exception $e) {
            $this->warn("⚠️  Could not display complete table structure: " . $e->getMessage());
        }
    }
}