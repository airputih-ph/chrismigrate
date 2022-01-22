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

class PopulateWithdrawByUsername implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $scan_id;
    protected $currency;
    protected $username;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($username, $scan_id, $currency)
    {
        $this->scan_id = $scan_id;
        $this->currency = $currency;
        $this->username = $scan_id . $username;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        DB::connection('v1')->table('withdraw')
        ->select('withdraw.id as id', 'withdraw.scan_id as scan_id', 'withdraw.bank as bank', 'withdraw.name as name', 'withdraw.rekening as rekening', 'category_type', 'username', 'jumlah', 'status', 'useradmin', 'date', 'approved_at', 'withdraw.lastUpdate as lastUpdate', 'remark', 'warning_status')
        ->where('withdraw.scan_id', $this->scan_id)
        ->where('withdraw.username', $this->username)
        ->orderBy('withdraw.id')
        ->chunk(500, function ($withdraws) {
            foreach($withdraws as $wd){
                $member_url = "http://172.31.39.201";
                $member_token = "eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9.eyJhdWQiOiJteS1wcm9qZWN0IiwiZXhwIjoxNTA5NjUwODAxLCJpYXQiOjE1MDk2NTQ0MDF9.F4iKO0R0wvHkpCcQoyrYttdGxE5FLAgDhbJOSLEHIBPsbL2WkLxXB9IGbDESn9rE7oxn89PJFRtcLn7kJwvdQkQcsPxn2RQorvDAnvAi1w3k8gpxYWo2DYJlnsi7mxXDqSUCNm1UCLRCW68ssYJxYLSg7B1xGMgDADGyYPaIx1EdN4dDbh-WeDyLLa7a8iWVBXdbmy1H3fEuiAyxiZpk2ll7DcQ6ryyMrU2XadwEr9PDqbLe1SrlaJsQbFi8RIdlQJSo_DZGOoAlA5bYTDYXb-skm7qvoaH5uMtOUb0rjijYuuxhNZvZDaBerEaxgmmlO0nQgtn12KVKjmKlisG79Q";

                $payment_url = "http://172.31.36.238";
                $payment_token = "eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9.eyJzZWMiOiI0YjJkYTM4MGFiY2UzYjY3ZTY1NTUzNzcxMmMyOGY3MyJ9.BQu84ytkA4q3YHNoKik7fYf-pnUxicpxBryGlJOBgPWByHH5oOOIXKZIPBMNSQVgW6NesZH60PL4bXgOqIAiUSBC6y7R_wbYtNnVbHYQBFyddc2cdFx1hwbTzyMbILQHTScxus-oiuPU2_VFSqiy8vSyrlE3UO85xozkVmMHBUsE9LSVl5gV4sVW2Fh8kGiT0zjZUDcPYzvYNiYSgaBDtynH52EGY7wrXnJzaEXlF04_hGOgBc6fB7SRVNOi1ui443GL9qKnSQu0KQt_58hCydofV5g3_uwJSDClpPrF-f4V6HnmrP-3Y6oPJmw2TYTrglzdNE_mQt9n2K-ulAPdsA";

                $username = substr($wd->username, 3);

                try{
                    $memberSimple = Http::withToken($member_token)
                    ->timeout(5)
                    ->retry(3, 100)
                    ->post("{$member_url}/member_production/api/v2/member/get_simple", [
                        "branchCode" => $this->scan_id,
                        "username" => $username,
                        "currency" => $this->currency
                    ])->json();
                }catch(Exception $e){
                    Log::warning($e);
                }

                if($memberSimple["success"] == true){
                    $member_id = $memberSimple["data"]["memberId"];
                    $play_id = $memberSimple["data"]["playid"];
                    $currency = $memberSimple["data"]["currency"];
                    $branch_name = $memberSimple["data"]["website"];
                    $warning = $memberSimple["data"]["warningStatus"];
                }

                $bca = [
                    'bca', 'bca2', 'bca3', 'BCA'
                ];

                if(in_array($wd->bank, $bca)){
                    $wd->bank = 'bca';
                }

                $ovo = [
                    'ovo2', 'ovo3', 'ovo4', 'OVO'
                ];

                if(in_array($wd->bank, $ovo)){
                    $wd->bank = 'ovo';
                }

                $mandiri = [
                    'mandiri_online', 'mandiri2', 'MANDIRI'
                ];

                if(in_array($wd->bank, $mandiri)){
                    $wd->bank = 'mandiri';
                }

                $telkomsel = [
                    'telkomsel2', 'telkomsel3', 'telkomsel4'
                ];

                if(in_array($wd->bank, $telkomsel)){
                    $wd->bank = 'telkomsel';
                }

                $ocbc = [
                    'ocbcnisp'
                ];

                if(in_array($wd->bank, $ocbc)){
                    $wd->bank = 'ocbc';
                }


                $bri = [
                    'bri2', 'BRI'
                ];

                if(in_array($wd->bank, $bri)){
                    $wd->bank = 'bri';
                }

                $cimb = [
                    'cimbniaga', 'CIMB'
                ];

                if(in_array($wd->bank, $cimb)){
                    $wd->bank = 'cimb';
                }
                
                $categories = [
                    'bca' => 'bank',
                    'bri' => 'bank',
                    'bni' => 'bank',
                    'mandiri' => 'bank',
                    'cimb' => 'bank',
                    'banklain' => 'bank',
                    'danamon' => 'bank',
                    'btpn' => 'bank',
                    'maybank' => 'bank',
                    'permata' => 'bank',
		    'ocbc' => 'bank',
                    'shopeepay' => 'epayment',
                    'sakuku' => 'epayment',
                    'dana' => 'epayment',
                    'ovo' => 'epayment',
                    'gopay' => 'epayment',
                    'qris' => 'epayment',
                    'linkaja' => 'epayment',
                    'jenius' => 'epayment',
                    'doku' => 'epayment',
                    'telkomsel' => 'phonecredit',
                    'xl' => 'phonecredit',
                    'tri' => 'phonecredit'
                ];
                
                $categoryTo = $categories[$wd->bank];

                $amountSubmitted = $currency == "IDR" ? $wd->jumlah / 1000 : $wd->jumlah;
                $amountProcessed = $currency == "IDR" ? $wd->jumlah / 1000 : $wd->jumlah;

                $remark = strpos($wd->remark, 'base64') !== false ? base64_decode(substr($wd->remark, 6)) : $wd->remark;

                if($wd->status == "Approved"){
                    $status = 1;
                }elseif($wd->status == "Rejected"){
                    $status = 2;
                }else{
                    $status = 2;
                    Log::warning($wd->id . ' error, no status detected');
                }

                if($wd->approved_at == null){
                    $processed = $wd->lastUpdate;
                }else{
                    $processed = $wd->approved_at;
                }

                $inserted = [
                    "transaction_id" => $wd->id,
                    "branch_code" => $this->scan_id,
                    "branch_name" => $branch_name,
                    "currency_code" => $currency,
                    "member_id" => $member_id,
                    "play_id" => $play_id,
                    "username" => $username,
                    "from_payment_type_name" => "-",
                    "from_payment_name" => "-",
                    "from_account_name" => "-",
                    "from_account_number" => "-",
                    "to_payment_type_name" => $categoryTo,
                    "to_payment_name" => $wd->bank,
                    "to_account_name" => $wd->name,
                    "to_account_number" => $wd->rekening,
                    "amount_submitted" => $amountSubmitted,
                    "amount_processed" => $amountProcessed,
                    "notes" => "",
                    "remark" => $remark,
                    "status" => $status,
                    "warning" => $warning,
                    "created_at" => $wd->date,
                    "processed_by" => $wd->useradmin,
                    "processed_at" => $processed,
                    "updated_at" => $wd->lastUpdate,
                    "updated_by" => "",
                    "status_migration" => 0
                ];

                DB::table('withdraws_migrations')->insertOrIgnore($inserted);
            }
        });
    }
}
