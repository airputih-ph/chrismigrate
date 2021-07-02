<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\InsertDepositToV2;

class InsertDepositToV2Command extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'insert:deposit';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Inserting deposit data to V2 {!PRODUCTION!}';

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
        try{
            InsertDepositToV2::dispatch();
        } catch (\Throwable $e) {
            return $this->error("Failed with error: {$e->getMessage()}");
        }
    }
}
