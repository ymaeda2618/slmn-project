<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SupplySlipDetail extends Model
{
    /**
     * モデルと関連しているテーブル
     *
     * @var string
     */
    protected $table = 'supply_slip_details';

    const CREATED_AT = 'created';
    const UPDATED_AT = 'modified';

}
