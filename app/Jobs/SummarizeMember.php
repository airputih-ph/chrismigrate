<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Models\SummariesMember;

class SummarizeMember implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $scan_id;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($scan_id)
    {
        $this->scan_id = $scan_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        DB::table('deposits_migrations')
            ->select("id", "member_id", "amount_processed", "status", "processed_at")
            ->where('branch_code', $this->scan_id)
            ->where("status_migration", 0)
            ->orderBy("id")
            ->chunk(500, function ($deposits){
                foreach($deposits as $d){
                    if($d->status == 1){
                        $summary = SummariesMember::firstOrCreate(["member_id" => $d->member_id]);
            
                        $summary->count_approved_depo += 1;
                        $summary->sum_approved_depo += $d->amount_processed;
                        $summary->last_deposit = $d->processed_at;
            
                        $summary->save();
                        
                        DB::table('deposits_migrations')->where('id', $d->id)->update(['status_migration' => 1]);
                    }elseif($d->status == 2){
                        $summary = SummariesMember::firstOrCreate(["member_id" => $d->member_id]);
            
                        $summary->count_rejected_depo += 1;
                        $summary->sum_rejected_depo += $d->amount_processed;
                        $summary->last_deposit = $d->processed_at;                
            
                        $summary->save();
                        
                        DB::table('deposits_migrations')->where('id', $d->id)->update(['status_migration' => 1]);
                    }
                }
            });

        DB::table('withdraws_migrations')
            ->select("id", "member_id", "amount_processed", "status", "processed_at")
            ->where('branch_code', $this->scan_id)
            ->where("status_migration", 0)
            ->orderBy("id")
            ->chunk(500, function ($withdraws){
                foreach($withdraws as $w){
                    if($w->status == 1){
                        $summary = SummariesMember::firstOrCreate(["member_id" => $w->member_id]);
            
                        $summary->count_approved_wd += 1;
                        $summary->sum_approved_wd += $w->amount_processed;
                        $summary->last_withdraw = $w->processed_at;
            
                        $summary->save();
                        
                        DB::table('withdraws_migrations')->where('id', $w->id)->update(['status_migration' => 1]);
                    }elseif($w->status == 2){
                        $summary = SummariesMember::firstOrCreate(["member_id" => $w->member_id]);
            
                        $summary->count_rejected_wd += 1;
                        $summary->sum_rejected_wd += $w->amount_processed;
                        $summary->last_withdraw = $w->processed_at;                
            
                        $summary->save();
                        
                        DB::table('withdraws_migrations')->where('id', $w->id)->update(['status_migration' => 1]);
                    }
                }
            });
    }
}
