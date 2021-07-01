<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\SummarizeMember;

class Summarize extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'summarize {scanId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Summarize the deposit and withdraw from all member on scanId';

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

        try{
            SummarizeMember::dispatch($scan_id);
        } catch (\Throwable $e) {
            return $this->error("Failed with error: {$e->getMessage()}");
        }
    }
}
