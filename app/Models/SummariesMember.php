<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SummariesMember extends Model
{
    use HasFactory;

    protected $table = 'summaries_member';
    protected $primaryKey = 'member_id';

    protected $fillable = ["member_id", "sum_approved_depo", "count_approved_depo", "count_rejected_depo", "sum_rejected_depo", "count_approved_wd", "sum_approved_wd", "count_rejected_wd", "sum_rejected_wd", "last_deposit", "last_withdraw"];
}
