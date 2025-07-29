<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model
{
    protected $fillable = [
        'company_setting_id',
        'bank_code',
        'bank_name',
        'branch_code',
        'branch_name',
        'account_type',
        'account_number',
    ];

    public function companySetting()
    {
        return $this->belongsTo(CompanySetting::class);
    }
}
