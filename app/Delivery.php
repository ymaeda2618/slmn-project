<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Delivery extends Model
{
    /**
     * モデルと関連しているテーブル
     *
     * @var string
     */
    protected $table = 'deliverys';

    const CREATED_AT = 'created';
    const UPDATED_AT = 'modified';

}
