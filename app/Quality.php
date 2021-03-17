<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Quality extends Model
{
    /**
     * モデルと関連しているテーブル
     *
     * @var string
     */
    protected $table = 'qualities';

    const CREATED_AT = 'created';
    const UPDATED_AT = 'modified';

}
