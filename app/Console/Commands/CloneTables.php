<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CloneTables extends Command
{
    protected $signature = 'clone:tables_salesAliance';

    protected $description = 'Clone filtered tables';

    public function handle()
    {
        $tables = [
            'mst_customer' => 'id',
            'mst_sales' => 'id',
            'sales_order' => 'id',
            'sales_orderdet' => 'id',
            'delivery_order' => 'id',
            'delivery_orderdet' => 'id',
            'invoice' => 'id',
            'return_sales' => 'id',
            'return_salesdet' => 'id_detail',
            'tmp_return_sales' => 'id',
            'retursales_orderdet_history' => 'id',
            'sentra_mst_sales' => 'id',
            'sentra_sales_order' => 'id',
            'sentra_sales_orderdet' => 'id',
            'sentra_invoice' => 'id',
            'sentra_return_sales' => 'id',
            'sentra_return_salesdet' => 'id_detail',
            'sentra_sales_retursales_orderdet_history' => 'id',
            'sentra_salesorder_tmp' => 'id',
            'sentra_tmp_return_sales' => 'id',
            'sentra_mst_customer' => 'id',
            'sentra_delivery_order' => 'id',
            'sentra_delivery_orderdet' => 'id',
            'consigment' => 'id',
            'consigment_det' => 'id',
            'consigment_det_temp' => 'id',
            'sales_consigment' => 'id',
            'sales_consigment_det' => 'id',
            'delivery_order_consigment' => 'id',
            'delivery_orderdet_consigment' => 'id',
            'data_stokopname_consignment' => 'id',
            'do_tmp_consigment' => 'id',
            'salesorderconsigment_tmp' => 'id',
            'retursales_orderdet_doconsigment_history' => 'id'
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
// php artisan clone:tables
