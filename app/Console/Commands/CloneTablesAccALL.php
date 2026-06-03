<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CloneTablesAccALL extends Command
{
    protected $signature = 'clone:CloneTablesAccALL';

    protected $description = 'Clone filtered tables';

    public function handle()
    {
        $tables = [
            'sales_invoices' => 'id',
            'sales_invoice_items' => 'id',
            'purchase_invoice_items' => 'id',
            'journal_transactions' => 'id',
            'purchase_invoices' => 'id',
            'account_receivables' => 'id',
            'account_receivable_details' => 'id',
            'purchase_payment_items' => 'id',
            'suppliers' => 'id',
            'customers' => 'id',
            'sessions' => 'id',
            'account_receivable_numberings' => 'id',
            'sales_orders' => 'id',
            'cb_expenses' => 'id',
            'chart_of_account' => 'id',
            'journal_entries' => 'id',
            'outstanding_receivables' => 'id',
            'journal_entry_details' => 'id',
            'sales_return_items' => 'id',
            'sales_returns' => 'id',
            'account_payables' => 'id',
            'cb_expense_details' => 'id',
            'purchase_payments' => 'id',
            'purchase_orders' => 'id',
            'cb_receipts' => 'id',
            'inject_return' => 'id',
            'journal_entry_numbering' => 'id',
            'cb_expense_numberings' => 'id',
            'debit_notes' => 'id',
            'audits2' => 'id',
            'personal_access_tokens' => 'id',
            'billing_items' => 'id',
            'account_payable_numberings' => 'id',
            'purchase_payment_lovs' => 'id',
            'account_payable_details' => 'id',
            'account_mutations' => 'id',
            'users' => 'id',
            'users_bck' => 'id',
            'purchase_returns' => 'id',
            'account_receivable_lovs' => 'id',
            'customer_deposits' => 'id',
            'supplier_deposits' => 'id',
            'companies' => 'id',
            'outstanding_payables' => 'id',
            'purchase_return_items' => 'id',
            'billings' => 'id',
            'jobs' => 'id',
            'failed_jobs' => 'id',
            'account_settings' => 'id',
            'sales_return_lovs' => 'id',
            'cb_receipt_details' => 'id',
            'billing_lovs' => 'id',
            'purchase_order_items' => 'id',
            'company_profile' => 'id',
            'journal_transaction_archives' => 'id',
            'chart_of_account_balances' => 'id',
            'job_batches' => 'id',
            'purchase_return_lovs' => 'id',
            'account_mutation_details' => 'id',
            'account_payable_lovs' => 'id',
            'manageable_assets' => 'id',
            'year_end_closings' => 'id',
            'menus' => 'id',
            'cb_receipt_numberings' => 'id',
            'period_closings' => 'id',
            'sales_order_items' => 'id',
            'sales_return_item' => 'id',
            'journal_transaction_archive_summaries' => 'id',
            'chart_of_account_archives' => 'id'
        ];


        foreach ($tables as $table => $primaryKey) {

            $this->newLine();
            $this->info("Processing table: {$table}");

            $total = DB::connection('source_ACC')
                ->table($table)
                ->count();

            $bar = $this->output->createProgressBar($total);

            $bar->start();

            DB::connection('source_ACC')
                ->table($table)
                ->orderBy($primaryKey)
                ->chunk(1000, function ($rows) use ($table, $primaryKey, $bar) {


                    // Cek cache
                    if (!isset($generatedColumnsCache[$table])) {
                        $generatedColumnsCache[$table] = DB::connection('target_ACC')
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
                        DB::connection('target_ACC')->table($table)->truncate();
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
                    DB::connection('target_ACC')->statement('SET FOREIGN_KEY_CHECKS=0');
                    DB::connection('target_ACC')
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
// php artisan clone:CloneTablesAccALL
