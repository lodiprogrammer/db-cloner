<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CloneTablesMSAll extends Command
{
    protected $signature = 'clone:CloneTablesMSAll';

    protected $description = 'Clone filtered tables';

    public function handle()
    {
        $tables = [
            'outgoing_item' => 'id',
            'inventory_logs' => 'id',
            'outgoings' => 'id',
            'assembly_item' => 'id',
            'sales_invoices' => 'id',
            'sales_order_item' => 'id',
            'assembly_formula_item' => 'id',
            'sales_invoice_item' => 'id',
            'assemblies' => 'id',
            'sales_orders' => 'id',
            'incoming_item' => 'id',
            'purchase_invoice_item' => 'id',
            'incomings' => 'id',
            'purchase_invoices' => 'id',
            'inventories' => 'id',
            'assemblies_import' => 'id',
            'sales_invoices_bckmtc' => 'id',
            'items' => 'id',
            'items_bck' => 'id',
            'purchase_order_item' => 'id',
            'purchase_orders' => 'id',
            'customers' => 'id',
            'inventories_copy' => 'id',
            'customers_bckmtc' => 'id',
            'outgoings_bck250217' => 'id',
            'assembly_formulas' => 'id',
            'sales_return_item' => 'id',
            'suntik_stok' => 'id',
            'suntik_sales_order' => 'id',
            'sales_returns' => 'id',
            'sessions' => 'id',
            'suntik_stok_glodok' => 'id',
            'incomings_bck250217' => 'id',
            'tmp_outgoingreport' => 'id',
            'account_receivables' => 'id',
            'account_receivables_bckmtc' => 'id',
            'sales_return_lovs' => 'id',
            'account_receivable_sales_invoice' => 'id',
            'account_receivable_sales_invoice_bckmtc' => 'id',
            'purchase_bill_item' => 'id',
            'sales_bill_lovs' => 'id',
            'purchase_bill_lovs' => 'id',
            'stock_opnames' => 'id',
            'inject_return' => 'id',
            'mutation_out_item' => 'id',
            'purchase_return_lovs' => 'id',
            'suntik_purchase_local_2504' => 'id',
            'suntik_purchase' => 'id',
            'suntik_purchase_impor_3' => 'id',
            'sales_bill_item' => 'id',
            'suntik_purchase_import_2504' => 'id',
            'account_payables' => 'id',
            'suppliers' => 'id',
            'purchase_returns' => 'id',
            'purchase_return_item' => 'id',
            'transfers' => 'id',
            'sales_bills' => 'id',
            'debit_notes' => 'id',
            'personal_access_tokens' => 'id',
            'mutation_outs' => 'id',
            'account_payable_purchase_invoice' => 'id',
            'suntik_stok_glodok_old' => 'id',
            'account_receivable_sales_invoice_tmp' => 'id',
            'locations' => 'id',
            'companies' => 'id',
            'supplier_deposits' => 'id',
            'account_payable_purchase_invoice_tmp' => 'id',
            'chart_of_accounts' => 'id',
            'customer_deposits' => 'id',
            'item_interchanges' => 'id',
            'purchase_bills' => 'id',
            'users' => 'id',
            'permissions' => 'id',
            'suppliers_inject' => 'id',
            'supplier_imports' => 'id',
            'billings' => 'id',
            'customers_inject' => 'id',
            'jobs' => 'id',
            'customer_imports' => 'id',
            'mutation_ins' => 'id',
            'failed_jobs' => 'id',
            'item_imports' => 'id',
            'mutation_in_item' => 'id',
            'cache_locks' => 'id',
            'password_reset_tokens' => 'id',
            'billing_lovs' => 'id',
            'migrations' => 'id',
            'list_bank' => 'id',
            'roles' => 'id',
            'location_imports' => 'id',
            'repair_scraps' => 'id',
            'job_batches' => 'id',
            'menus' => 'id',
            'billing_sales_invoice' => 'id',
            'taxes' => 'id',
            'suntik_purchase_impor' => 'id',
            'salesmen' => 'id',
            'duplikat_mutasi' => 'id',
            'wh_besar_duplikat' => 'id',
            'wh_kecil_duplikat' => 'id',
            'inventory_notdelete' => 'id',
            'cari_tidak_sinkron_inventory' => 'id'
        ];


        foreach ($tables as $table => $primaryKey) {

            $this->newLine();
            $this->info("Processing table: {$table}");

            $total = DB::connection('source_MS')
                ->table($table)
                ->count();

            $bar = $this->output->createProgressBar($total);

            $bar->start();

            DB::connection('source_MS')
                ->table($table)
                ->orderBy($primaryKey)
                ->chunk(1000, function ($rows) use ($table, $primaryKey, $bar) {


                    // Cek cache
                    if (!isset($generatedColumnsCache[$table])) {
                        $generatedColumnsCache[$table] = DB::connection('target_MS')
                            ->select("
                                SELECT COLUMN_NAME 
                                FROM INFORMATION_SCHEMA.COLUMNS 
                                WHERE TABLE_SCHEMA = DATABASE() 
                                AND TABLE_NAME = ? 
                                AND EXTRA LIKE '%GENERATED%'
                ", [$table]);
                    }

                    $generatedNames = collect($generatedColumnsCache[$table])->pluck('COLUMN_NAME')->toArray();

                    $data = collect($rows)
                        ->map(fn ($row) => (array) $row)
                        ->map(function ($row) use ($generatedNames) {
                            return array_diff_key($row, array_flip($generatedNames));
                        })
                        ->toArray();

                    DB::connection('target_MS')->statement('SET FOREIGN_KEY_CHECKS=0');
                    DB::connection('target_MS')

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
// php artisan clone:CloneTablesMSAll
