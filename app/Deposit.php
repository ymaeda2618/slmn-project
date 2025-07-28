<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Deposit extends Model
{
    // テーブル名（省略可能: Laravelの命名規則通りの場合）
    protected $table = 'deposits';

    /**
     * 一括代入可能な属性
     *
     * @var array
     */
    protected $fillable = [
        'owner_company_id',
        'sale_company_id',
        'sale_shop_id',
        'date',
        'sale_from_date',
        'sale_to_date',
        'payment_date',
        'staff_id',
        'sub_total',
        'adjustment_amount',
        'amount',
        'deposit_method_id',
        'remarks',
        'deposit_submit_type',
        'sort',
        'active',
        'created_user_id',
        'created',
        'modified_user_id',
        'modified',
        'bank_account_id', // 銀行口座との関連
    ];

    /**
     * 銀行口座とのリレーション（1件の振込は1口座に紐づく）
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class);
    }
}
