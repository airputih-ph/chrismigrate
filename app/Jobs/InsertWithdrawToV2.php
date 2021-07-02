<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class InsertWithdrawToV2 implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        DB::table('withdraws_migrations')
            ->where("status_migration", 1)
            ->orderBy("id")            
            ->chunk(3000, function ($withdraws){
                foreach($withdraws as $wd){
                    $array = (array)$wd;
                    unset($array["status_migration"]);
                    unset($array["id"]);
                    DB::connection('v2')->table('Withdraws')->insertOrIgnore($array);
                    DB::table('withdraws_migrations')->where('id', $wd->id)->update(['status_migration' => 2]);
                }
            });
    }
}
