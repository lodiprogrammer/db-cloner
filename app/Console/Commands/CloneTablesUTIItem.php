<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CloneTablesUTIItem extends Command
{
    protected $signature = 'clone:CloneTablesUTIItem';

    protected $description = 'Clone filtered tables';

    public function handle()
    {
        $tables = [
            'item' => 'id',
            'item_PL' => 'id',
            'item_pl_99' => 'id',
            'mst_item' => 'id',
            'inventory_balance' => 'id'
        ];


        foreach ($tables as $table => $primaryKey) {

            $this->newLine();
            $this->info("Processing table: {$table}");

            $total = DB::connection('source')
                ->table($table)
                ->count();

            $bar = $this->output->createProgressBar($total);

            $bar->start();

            DB::connection('source')
                ->table($table)
                ->orderBy($primaryKey)
                ->chunk(1000, function ($rows) use ($table, $primaryKey, $bar) {


                    // Cek cache
                    if (!isset($generatedColumnsCache[$table])) {
                        $generatedColumnsCache[$table] = DB::connection('target')
                            ->select("
                                SELECT COLUMN_NAME 
                                FROM INFORMATION_SCHEMA.COLUMNS 
                                WHERE TABLE_SCHEMA = DATABASE() 
                                AND TABLE_NAME = ? 
                                AND EXTRA LIKE '%GENERATED%'
                ", [$table]);
                    }

                    // Truncate table target sebelum insert (hanya sekali per tabel)
                    static $truncated = [];
                    if (!isset($truncated[$table])) {
                        DB::connection('target')->table($table)->truncate();
                        $truncated[$table] = true;
                        $this->info("Truncated table: {$table}");
                    }

                    $generatedNames = collect($generatedColumnsCache[$table])->pluck('COLUMN_NAME')->toArray();

                    $data = collect($rows)
                        ->map(fn ($row) => (array) $row)
                        ->map(function ($row) use ($generatedNames) {
                            return array_diff_key($row, array_flip($generatedNames));
                        })
                        ->toArray();

                    DB::connection('target')->statement('SET FOREIGN_KEY_CHECKS=0');
                    DB::connection('target')
                        ->table($table)
                        ->upsert($data, [$primaryKey]);

                    $bar->advance(count($data));
                });

            $bar->finish();
            $this->newLine();
            $this->info("Finished table: {$table}");

        }

        $this->info("Done All");
    }
}


// jalankan di bash
// php artisan clone:CloneTablesUTIItem
