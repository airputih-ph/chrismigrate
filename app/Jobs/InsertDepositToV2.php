<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class InsertDepositToV2 implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $deposits = DB::table('deposits_migrations')
            ->where("status_migration", 1)
            ->orderBy("id")->get();
            //->chunk(3000, function ($deposits){
                foreach($deposits as $deposit){
                  if($deposit->status_migration == 1){
                    $array = (array)$deposit;
                    unset($array["status_migration"]);
                    unset($array["id"]);
                    DB::connection('v2')->table('Deposits')->insertOrIgnore($array);
                    DB::table('deposits_migrations')->where('id', $deposit->id)->update(['status_migration' => 2]);
                  }
                }
            //});
    }
}
