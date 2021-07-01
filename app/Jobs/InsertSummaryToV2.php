<?php

namespace App\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class InsertSummaryToV2 implements ShouldQueue
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
        $summaryData = DB::table('summaries_member')
            ->select('member_id', 'count_approved_depo', 'sum_approved_depo', 'count_rejected_depo', 'sum_rejected_depo', 'count_approved_wd', 'sum_approved_wd', 'count_rejected_wd', 'sum_rejected_wd', 'created_at', 'updated_at', 'last_deposit', 'last_withdraw')
            ->where('status_migration', 0)->get();

        foreach($summaryData as $summary){
            DB::connection('v2')->table('Summaries')->insertOrIgnore((array)$summary);
            DB::table('summaries_member')->where('member_id', $summary->member_id)->update(['status_migration' => 1]);
        }
    }
}
