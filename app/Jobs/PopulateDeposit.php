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
        ->orderBy('depo.id')
        ->chunk(200, function ($deposits) {
            foreach($deposits as $depo){
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
                }

                if($memberSimple["success"] == true){
                    $member_id = $memberSimple["data"]["memberId"];
                    $play_id = $memberSimple["data"]["playid"];
                    $currency = $memberSimple["data"]["currency"];
                    $branch_name = $memberSimple["data"]["website"];
                    $warning = $memberSimple["data"]["warningStatus"];
                }

                try{
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
                }

                if($categoriesFrom["success"] == true && $categoriesTo["success"] == true){
                    $categoryFrom = $categoriesFrom["data"]["categories"];
                    $categoryTo = $categoriesTo["data"]["categories"];
                }

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
        });
    }
}
