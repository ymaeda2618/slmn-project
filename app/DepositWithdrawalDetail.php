<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DepositWithdrawalDetail extends Model
{
    /**
     * モデルと関連しているテーブル
     *
     * @var string
     */
    protected $table = 'deposit_withdrawal_details';

    const CREATED_AT = 'created';
    const UPDATED_AT = 'modified';

}