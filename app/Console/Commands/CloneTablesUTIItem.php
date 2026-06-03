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

                    $data = collect($rows)
                        ->map(fn ($row) => (array) $row)
                        ->toArray();

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
