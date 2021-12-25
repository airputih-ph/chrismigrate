<?php

namespace App\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PopulateFirstTime implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $date;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($date)
    {
        $this->date = $date;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $date = $this->date;
        $array = DB::connection('v2')->table('Wallets')->whereRaw('DATE(created_at) = \''.$date.'\'')->where('code', 'MAIN')->get();
        
        foreach($array as $a){
            $branch = (int) substr($a->play_id, -3);
            $check = DB::connection('v2')->table('MemberFirstTimes')->where('member_id', $a->member_id)->first();
            if(!$check){
                $insertFirstTime = [
                    "member_id" => $a->member_id,
                    "play_id" => $a->play_id,
                    "username" => $a->username,
                    "branch_id" => $branch,
                    "currency" => $a->currency,
                    "first_wallet_created_date" => $date,
                    "first_wallet_created_time" => $a->created_at
                ];
                $checkingFirst = DB::connection('v2')->table('MemberFirstTimes')->where('member_id', $a->member_id)->first();
                if(!$checkingFirst){
                    DB::connection('v2')->table('MemberFirstTimes')->insert($insertFirstTime);

                    $summary = DB::connection('v2')->table('MemberFirstTimeSummaries')->where('branch_id', $branch)->where('currency', $a->currency)->where('date', $date)->exists();
                    if($summary){
                        DB::connection('v2')->table('MemberFirstTimeSummaries')->where('branch_id', $branch)->where('currency', $a->currency)->where('date', $date)->update([
                            'new_register_count' => DB::raw('new_register_count + 1'),
                            'last_update' => $a->created_at
                        ]);
                    }else{
                        DB::connection('v2')->table('MemberFirstTimeSummaries')->insert([
                            "branch_id" => $branch,
                            "currency" => $a->currency,
                            "date" => $date,
                            "new_register_count" => 1,
                            "new_deposit_count" => 0,
                            "new_deposit_amount" => 0,
                            "last_update" => $a->created_at
                        ]);
                    }
                }

                $checkDepo = DB::connection('v2')->table('Deposits')->where('member_id', $a->member_id)->where('status', 1)->orderBy('processed_at')->first();
                if($checkDepo){
                    $dateProcessed = Carbon::parse($checkDepo->processed_at)->format('Y-m-d');
                    DB::connection('v2')->table('MemberFirstTimes')->where('member_id', $checkDepo->member_id)->update([
                        'first_deposit_time' => $checkDepo->processed_at,
                        'first_deposit_date' => $dateProcessed,
                        'first_deposit_amount' => $checkDepo->amount_processed,
                        'first_deposit_trx_id' => $checkDepo->transaction_id
                    ]);

                    $depoDate = Carbon::parse($checkDepo->processed_at)->format('Y-m-d');
                    $depoCountCheck = DB::connection('v2')->table('MemberFirstTimeSummaries')->where('branch_id', $branch)->where('currency', $a->currency)->where('date', $depoDate)->exists();
                    if($depoCountCheck){
                        DB::connection('v2')->table('MemberFirstTimeSummaries')->where('branch_id', $branch)->where('currency', $a->currency)->where('date', $depoDate)->update([
                            'new_deposit_count' => DB::raw('new_deposit_count + 1'),
                            'new_deposit_amount' => DB::raw('new_deposit_amount + ' . $checkDepo->amount_processed),
                            'last_update' => $checkDepo->processed_at
                        ]);
                    }else{
                        DB::connection('v2')->table('MemberFirstTimeSummaries')->insert([
                            'branch_id' => $branch,
                            'currency' => $a->currency,
                            'date' => $depoDate,
                            'new_register_count' => 0,
                            'new_deposit_count' => 1,
                            'new_deposit_amount' => $checkDepo->amount_processed,
                            'last_update' => $checkDepo->processed_at
                        ]);
                    }

                }
            }
            
        }
    }
}
