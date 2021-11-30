<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\PopulateWithdrawTogel as PopulateJob;

class PopulateWithdrawTogel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'populate:withdrawtogel {scanId} {currency}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Populating withdraw data from togel';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $scan_id = $this->argument('scanId');
        $currency = $this->argument('currency');

        try{
            PopulateJob::dispatch($scan_id, $currency);
        } catch (\Throwable $e) {
            return $this->error("Failed with error: {$e->getMessage()}");
        }
    }
}
