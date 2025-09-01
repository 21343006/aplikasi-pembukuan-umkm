<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Income;
use App\Models\Expenditure;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class FixUserIds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data:fix-user-ids';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assign a user ID to incomes and expenditures that do not have one.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to fix user IDs for incomes and expenditures.');

        // Find the first user
        $user = User::first();

        if (!$user) {
            $this->error('No users found in the database. Please create a user first.');
            return 1;
        }

        $this->info("Found user: {$user->name} (ID: {$user->id}). Using this user to fix the data.");

        // Fix incomes
        $incomesUpdated = DB::table('incomes')->whereNull('user_id')->update(['user_id' => $user->id]);
        $this->info("Updated {$incomesUpdated} income records.");

        // Fix expenditures
        $expendituresUpdated = DB::table('expenditures')->whereNull('user_id')->update(['user_id' => $user->id]);
        $this->info("Updated {$expendituresUpdated} expenditure records.");

        $this->info('Finished fixing user IDs.');

        return 0;
    }
}
