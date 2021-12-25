<?php

namespace App\Console\Commands;

use App\Jobs\PopulateFirstTime as JobsPopulateFirstTime;
use Illuminate\Console\Command;

class PopulateFirstTime extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'populate:first {date}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Populate first time member summary';

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
        $date = $this->argument('date');

        try{
            JobsPopulateFirstTime::dispatch($date);
        } catch (\Throwable $e) {
            return $this->error("Failed with error: {$e->getMessage()}");
        }
    }
}
