<?php

namespace App\Console\Commands;

use App\Jobs\InsertWithdrawToV2;
use Illuminate\Console\Command;

class InsertWithdrawToV2Command extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'insert:withdraw';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Inserting withdraw data to V2 {!PRODUCTION!}';

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
            InsertWithdrawToV2::dispatch();
        } catch (\Throwable $e) {
            return $this->error("Failed with error: {$e->getMessage()}");
        }
    }
}
