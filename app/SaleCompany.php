<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SaleCompany extends Model
{
    /**
     * モデルと関連しているテーブル
     *
     * @var string
     */
    protected $table = 'sale_companies';

    const CREATED_AT = 'created';
    const UPDATED_AT = 'modified';

}
