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

class PopulateDeposit implements ShouldQueue
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
        DB::connection('v1')->table('depo')
        ->select('depo.id as id', 'depo.scan_id as scan_id', 'depo.bank as bank', 'depo.name as name', 'depo.rekening as rekening', 'category_type', 'username', 'jumlah', 'status', 'useradmin', 'date', 'approved_at', 'depo.lastUpdate as lastUpdate', 'remark', 'warning_status', 'img_url', 'depo.rate as rate', 'rekening.bank as toBank', 'rekening.name as toName', 'rekening.rekening as toNumber')
        ->where('depo.scan_id', $this->scan_id)
        ->join('rekening', 'depo.rekening_tujuan', '=', 'rekening.id')
        ->orderBy('depo.id', 'DESC')
        ->chunk(2000, function ($deposits) {
            foreach($deposits as $depo){
		if(DB::table('deposits_migrations')->where('transaction_id', $depo->id)->exists()){
			Log::debug('ada '.$depo->id); 
		}
		else{
                $member_url = "http://172.31.39.201";
                $member_token = "eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9.eyJhdWQiOiJteS1wcm9qZWN0IiwiZXhwIjoxNTA5NjUwODAxLCJpYXQiOjE1MDk2NTQ0MDF9.F4iKO0R0wvHkpCcQoyrYttdGxE5FLAgDhbJOSLEHIBPsbL2WkLxXB9IGbDESn9rE7oxn89PJFRtcLn7kJwvdQkQcsPxn2RQorvDAnvAi1w3k8gpxYWo2DYJlnsi7mxXDqSUCNm1UCLRCW68ssYJxYLSg7B1xGMgDADGyYPaIx1EdN4dDbh-WeDyLLa7a8iWVBXdbmy1H3fEuiAyxiZpk2ll7DcQ6ryyMrU2XadwEr9PDqbLe1SrlaJsQbFi8RIdlQJSo_DZGOoAlA5bYTDYXb-skm7qvoaH5uMtOUb0rjijYuuxhNZvZDaBerEaxgmmlO0nQgtn12KVKjmKlisG79Q";

                $payment_url = "http://172.31.36.238";
                $payment_token = "eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9.eyJzZWMiOiI0YjJkYTM4MGFiY2UzYjY3ZTY1NTUzNzcxMmMyOGY3MyJ9.BQu84ytkA4q3YHNoKik7fYf-pnUxicpxBryGlJOBgPWByHH5oOOIXKZIPBMNSQVgW6NesZH60PL4bXgOqIAiUSBC6y7R_wbYtNnVbHYQBFyddc2cdFx1hwbTzyMbILQHTScxus-oiuPU2_VFSqiy8vSyrlE3UO85xozkVmMHBUsE9LSVl5gV4sVW2Fh8kGiT0zjZUDcPYzvYNiYSgaBDtynH52EGY7wrXnJzaEXlF04_hGOgBc6fB7SRVNOi1ui443GL9qKnSQu0KQt_58hCydofV5g3_uwJSDClpPrF-f4V6HnmrP-3Y6oPJmw2TYTrglzdNE_mQt9n2K-ulAPdsA";

                $username = substr($depo->username, 3);

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
			Log::warning($username);
			continue;
                }

                if($memberSimple["success"] == true){
                    $member_id = $memberSimple["data"]["memberId"];
                    $play_id = $memberSimple["data"]["playid"];
                    $currency = $memberSimple["data"]["currency"];
                    $branch_name = $memberSimple["data"]["website"];
                    $warning = $memberSimple["data"]["warningStatus"];
                }

if($depo->toBank == "CIMB" || $depo->toBank == "cimbniaga"){
$depo->toBank = "cimb";
}

if($depo->toBank == "otherbank"){
$depo->toBank = "banklain";
}

if($depo->bank == "otherbank"){
$depo->bank = "banklain";
}

if($depo->bank == "CIMB" || $depo->bank == "cimbniaga"){
$depo->bank = "cimb";
}

if($depo->bank == "BCA" || $depo->bank == "qr-bankbca"){
$depo->bank = "bca";
}

if($depo->bank == "BNI"){
$depo->bank = "bni";
}

if($depo->bank == "BRI"){
$depo->bank = "bri";
}

if($depo->bank == "Danamon" || $depo->bank == "bankdanamon"){
$depo->bank = "danamon";
}

if($depo->bank == "Mandiri"){
$depo->bank = "mandiri";
}

if($depo->bank == "mandiri_online" || $depo->bank == "mandiri2"){
$depo->bank = "mandiri";
}

if($depo->toBank == "BCA" || $depo->toBank == "bca2" || $depo->toBank == "bca3" || $depo->toBank == "qr-bankbca"){
$depo->toBank = "bca";
}

if($depo->toBank == "BNI"){
$depo->toBank = "bni";
}

if($depo->toBank == "BRI" || $depo->toBank == "bri2"){
$depo->toBank = "bri";
}

if($depo->toBank == "Danamon" || $depo->toBank == "bankdanamon"){
$depo->toBank = "danamon";
}

if($depo->toBank == "dana1" || $depo->toBank == "dana2" || $depo->toBank == "dana3" || $depo->toBank == "dana4" || $depo->toBank == "dana5" || $depo->toBank == "dana6" || $depo->toBank == "dana7" || $depo->toBank == "dana8" || $depo->toBank == "dana9" || $depo->toBank == "dana10" || $depo->toBank == "dana11" || $depo->toBank == "dana12" || $depo->toBank == "dana13" || $depo->toBank == "dana14" || $depo->toBank == "dana15" || $depo->toBank == "dana16" || $depo->toBank == "dana17" || $depo->toBank == "dana18" || $depo->toBank == "dana19" || $depo->toBank == "dana20" || $depo->toBank == "dana21" || $depo->toBank == "dana22" || $depo->toBank == "dana23" || $depo->toBank == "qr-dana"){
    $depo->toBank = "dana";
}

if($depo->toBank == "ovo2" || $depo->toBank == "ovo3" || $depo->toBank == "ovo4" || $depo->toBank == "qr-ovo"){
    $depo->toBank = "ovo";
}

if($depo->bank == "ovo2" || $depo->bank == "ovo3" || $depo->bank == "ovo4"){
$depo->bank = "ovo";
}

if($depo->toBank == "Mandiri" || $depo->toBank == "mandiri_online" || $depo->toBank == "mandiri2"){
$depo->toBank = "mandiri";
}

if($depo->toBank == "qr-dana"){
$depo->toBank = "dana";
}

if($depo->toBank == "qr-gopay"){
$depo->toBank = "gopay";
}

if($depo->toBank == "qr-linkaja"){
$depo->toBank = "linkaja";
}

if($depo->toBank == "qr-sakuku"){
$depo->toBank = "sakuku";
}

if($depo->toBank == "qr-shopeepay"){
$depo->toBank = "shopeepay";
}

if($depo->toBank == "jeniusbtpn"){
$depo->toBank = "jenius";
}

if($depo->bank == "jeniusbtpn"){
$depo->bank = "jenius";
}

if($depo->toBank == "bankpapua"){
$depo->toBank = "papua";
}

$xl = ['xlatauaxis', 
'xlatauaxis11', 
'xlatauaxis12', 
'xlatauaxis13', 
'xlatauaxis14', 
'xlatauaxis15', 
'xlatauaxis16', 
'xlatauaxis17', 
'xlatauaxis18', 
'xlatauaxis19', 
'xlatauaxis20',
'xl/axis11',
'xl/axis12',
'xl/axis13',
'xl/axis14',
'xl/axis15',
'xl/axis16',
'xl/axis17',
'xl/axis18',
'xl/axis20',
'axis',
'axis/xl',
'xl/axis',
'xl-all-operator'];

if(in_array($depo->bank, $xl)){
    $depo->bank = 'xl';
}

if(in_array($depo->toBank, $xl)){
    $depo->toBank = 'xl';
}

$telkomsel = ['telkomsel',
'telkomsel1',
'telkomsel2',
'telkomsel3',
'telkomsel4',
'telkomsel40',
'telkomsel41',
'telkomsel42',
'telkomsel44',
'telkomsel45',
'telkomsel46',
'telkomsel47',
'telkomsel48',
'telkomsel49',
'telkomsel5',
'telkomsel50',
'telkomsel51',
'telkomsel52',
'telkomsel53',
'telkomsel54',
'telkomsel55',
'telkomsel56',
'telkomsel57',
'telkomsel58',
'telkomsel59',
'telkomsel60',
'telkomsel61',
'telkomsel62',
'telkomsel63',
'telkomsel64',
'telkomsel65',
'telkomsel66',
'telkomsel67',
'telkomsel68',
'telkomsel70',
'telkomsel71',
'telkomsel72',
'telkomsel73',
'telkomsel74'];

if(in_array($depo->bank, $telkomsel)){
    $depo->bank = 'telkomsel';
}

if(in_array($depo->toBank, $telkomsel)){
    $depo->toBank = 'telkomsel';
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

$categoryFrom = $categories[$depo->bank];
$categoryTo = $categories[$depo->toBank];

                /*try{
                    $categoriesFrom = Http::withToken($payment_token)
                    ->timeout(5)
                    ->retry(3, 100)
                    ->post("{$payment_url}/v1/payment/categories-by-code", [
                        "bankCode" => $depo->bank
                    ])->json();

                    $categoriesTo = Http::withToken($payment_token)
                    ->timeout(5)
                    ->retry(3, 100)
                    ->post("{$payment_url}/v1/payment/categories-by-code", [
                        "bankCode" => $depo->toBank
                    ])->json();
                }catch(Exception $e){
                    Log::warning($e);
			Log::warning($depo->bank);
			Log::warning($depo->toBank);

                }

if(!isset($categoriesFrom["data"]["categories"]) || !isset($categoriesTo["data"]["categories"])){
Log::warning($depo->bank);
Log::warning($depo->toBank);
}

                if($categoriesFrom["success"] == true && $categoriesTo["success"] == true){
                    $categoryFrom = $categoriesFrom["data"]["categories"];
                    $categoryTo = $categoriesTo["data"]["categories"];
                } */

                $amountSubmitted = ($depo->jumlah / 1000) / ($depo->rate / 100);
                $amountProcessed = $currency == "IDR" ? $depo->jumlah / 1000 : $depo->jumlah;

                if($depo->category_type != "MEMBER"){
                    $remark = "MANUAL DEPOSIT";
                }else{
                    $remark = strpos($depo->remark, 'base64') !== false ? base64_decode(substr($depo->remark, 6)) : $depo->remark;
                }

                if($depo->status == "Approved"){
                    $status = 1;
                }elseif($depo->status == "Rejected"){
                    $status = 2;
                }else{
                    $status = 2;
                    Log::warning($depo->id . ' error, no status detected');
                }

                if($depo->approved_at == null){
                    $processed = $depo->lastUpdate;
                }else{
                    $processed = $depo->approved_at;
                }

                $inserted = [
                    "transaction_id" => $depo->id,
                    "branch_code" => $this->scan_id,
                    "branch_name" => $branch_name,
                    "currency_code" => $currency,
                    "member_id" => $member_id,
                    "play_id" => $play_id,
                    "username" => $username,
                    "from_payment_type_name" => $categoryFrom,
                    "from_payment_name" => $depo->bank,
                    "from_account_name" => $depo->name,
                    "from_account_number" => $depo->rekening,
                    "to_payment_type_name" => $categoryTo,
                    "to_payment_name" => $depo->toBank,
                    "to_account_name" => $depo->toName,
                    "to_account_number" => $depo->toNumber,
                    "rate" => $depo->rate,
                    "amount_submitted" => $amountSubmitted,
                    "amount_processed" => $amountProcessed,
                    "notes" => $remark,
                    "proof" => $depo->img_url,
                    "remark" => "",
                    "status" => $status,
                    "warning" => $warning,
                    "created_at" => $depo->date,
                    "processed_by" => $depo->useradmin,
                    "processed_at" => $processed,
                    "updated_at" => $depo->lastUpdate,
                    "updated_by" => "",
                    "status_migration" => 0
                ];

                DB::table('deposits_migrations')->insertOrIgnore($inserted);
            }
	}
        });
    }
}
