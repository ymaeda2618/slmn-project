<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SupplyCompany extends Model
{
    /**
     * モデルと関連しているテーブル
     *
     * @var string
     */
    protected $table = 'supply_companies';

    const CREATED_AT = 'created';
    const UPDATED_AT = 'modified';

}
