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

class PopulateWithdraw implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $scan_id;
    protected $currency;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($scan_id, $currency)
    {
        $this->scan_id = $scan_id;
        $this->currency = $currency;
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
	->where('withdraw.date', '>', '2021-06-20 00:00:00')
	->where('withdraw.date', '<', '2022-01-19 00:00:00')
        ->orderBy('withdraw.id', 'ASC')
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
                    Log::warning('[WITHDRAW] error on member : '.$username.' branch code : '.$this->scan_id);
		    continue;
                }

                if($memberSimple["success"] == true){
                    $member_id = $memberSimple["data"]["memberId"];
                    $play_id = $memberSimple["data"]["playid"];
                    $currency = $memberSimple["data"]["currency"];
                    $branch_name = $memberSimple["data"]["website"];
                    $warning = $memberSimple["data"]["warningStatus"];
                }

if($wd->bank == "BCA"){
$wd->bank = "bca";
}

if($wd->bank == "otherbank"){
$wd->bank = "banklain";
}

if($wd->bank == "BNI"){
$wd->bank = "bni";
}

if($wd->bank == "BRI"){
$wd->bank = "bri";
}

if($wd->bank == "banksyariahindonesia"){
$wd->bank = "bsi";
}

if($wd->bank == "Danamon"){
$wd->bank = "danamon";
}

if($wd->bank == "ovo2" || $wd->bank == "ovo3" || $wd->bank == "ovo4"){
$wd->bank = "ovo";
}

if($wd->bank == "ocbcnisp"){
$wd->bank = "ocbc";
}

if($wd->bank == "Mandiri" || $wd->bank == "mandiri_online" || $wd->bank == "mandiri2"){
$wd->bank = "mandiri";
}

if($wd->bank == "cimbniaga" || $wd->bank == "cimbniaga" || $wd->bank == "CIMB"){
$wd->bank = "cimb";
}

if($wd->bank == "lain"){
$wd->bank = "banklain";
}

if($wd->bank == "jeniusbtpn"){
$wd->bank = "jenius";
}

if($wd->bank == "bankdanamon"){
$wd->bank = "danamon";
}

if($wd->bank == "sakukubca"){
$wd->bank = "sakuku";
}

if($wd->bank == "dana1" || $wd->bank == "dana2"){
$wd->bank = "dana";
}

if($wd->bank == "go-pay"){
$wd->bank = "gopay";
}

if($wd->bank == "xlaxiata" || $wd->bank == "xl2"){
$wd->bank = "xl";
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
        'jago' => 'bank',
        'mega' => 'bank',
        'bsi' => 'bank',
        'btn' => 'bank',
        'ocbc' => 'bank',
        'panin' => 'bank',
	'papua' => 'bank',
        'bankaceh' => 'bank',
        'bsb' => 'bank',
        'bcasyariah' => 'bank',
	'mandirisyariah' => 'bank',
        'jatim' => 'bank',
        'bjb' => 'bank',
        'sinarmas' => 'bank',
        'scbbank' => 'bank',
        'kasikornbank' => 'bank',
        'bangkokbank' => 'bank',
        'krungthaibank' => 'bank',
        'shopeepay' => 'epayment',
        'sakuku' => 'epayment',
        'dana' => 'epayment',
        'ovo' => 'epayment',
        'gopay' => 'epayment',
        'qris' => 'epayment',
        'linkaja' => 'epayment',
        'jenius' => 'epayment',
        'doku' => 'epayment',
	'grabpay' => 'epayment',
        'telkomsel' => 'phonecredit',
        'xl' => 'phonecredit',
        'tri' => 'phonecredit'
];

$categoryTo = $categories[$wd->bank];

                /*try{
                    $categoriesTo = Http::withToken($payment_token)
                    ->timeout(5)
                    ->retry(3, 100)
                    ->post("{$payment_url}/v1/payment/categories-by-code", [
                        "bankCode" => $wd->bank
                    ])->json();
                }catch(Exception $e){
                    Log::warning($e);
                }

if(!isset($categoriesTo["data"]["categories"])){
Log::warning($wd->bank);
}


                if($categoriesTo["success"] == true){
                    $categoryTo = $categoriesTo["data"]["categories"];
                }*/

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
