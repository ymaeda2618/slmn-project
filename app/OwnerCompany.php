<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OwnerCompany extends Model
{
    /**
     * モデルと関連しているテーブル
     *
     * @var string
     */
    protected $table = 'owner_companies';

    const CREATED_AT = 'created';
    const UPDATED_AT = 'modified';

}
