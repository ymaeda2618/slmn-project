<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SupplySlip extends Model
{
    /**
     * モデルと関連しているテーブル
     *
     * @var string
     */
    protected $table = 'supply_slips';

    const CREATED_AT = 'created';
    const UPDATED_AT = 'modified';

}
