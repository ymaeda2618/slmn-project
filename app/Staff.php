<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    /**
     * モデルと関連しているテーブル
     *
     * @var string
     */
    protected $table = 'staffs';

    const CREATED_AT = 'created';
    const UPDATED_AT = 'modified';

}
