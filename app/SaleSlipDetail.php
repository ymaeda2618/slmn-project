<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class SaleSlipDetail extends Model
{
    /**
     * モデルと関連しているテーブル
     *
     * @var string
     */
    protected $table = 'sale_slip_details';

    const CREATED_AT = 'created';
    const UPDATED_AT = 'modified';

    /**
     * 売上伝票詳細の新規登録
     * @param array $SaleSlipDetailData
     *
     * @return array SaleSlipDetailモデル
     */
    public function insertSaleSlipDetail(array $SaleSlipDetailData)
    {
        // 値がNULLのところを初期化
        if (empty($SaleSlipDetailData['standard_id'])) $SaleSlipDetailData['standard_id'] = 0;
        if (empty($SaleSlipDetailData['quality_id'])) $SaleSlipDetailData['quality_id'] = 0;
        if (isset($SaleSlipDetailData['supply_count']) || empty($SaleSlipDetailData['supply_count'])) $SaleSlipDetailData['supply_count'] = 0;
        if (isset($SaleSlipDetailData['supply_unit_num']) || empty($SaleSlipDetailData['supply_unit_num'])) $SaleSlipDetailData['supply_unit_num'] = 0;
        if (isset($SaleSlipDetailData['sort']) || empty($SaleSlipDetailData['sort'])) $SaleSlipDetailData['sort'] = 0;

        // sale_slip_detailsを登録する
        $SaleSlipDetail                   = new SaleSlipDetail;
        $SaleSlipDetail->sale_slip_id     = $SaleSlipDetailData['sale_slip_id'];
        $SaleSlipDetail->product_id       = $SaleSlipDetailData['product_id'];
        $SaleSlipDetail->standard_id      = $SaleSlipDetailData['standard_id'];
        $SaleSlipDetail->quality_id       = $SaleSlipDetailData['quality_id'];
        $SaleSlipDetail->unit_price       = $SaleSlipDetailData['unit_price'];
        $SaleSlipDetail->unit_num         = $SaleSlipDetailData['unit_num'];
        $SaleSlipDetail->notax_price      = $SaleSlipDetailData['notax_price'];
        $SaleSlipDetail->unit_id          = $SaleSlipDetailData['unit_id'];
        $SaleSlipDetail->staff_id         = $SaleSlipDetailData['staff_id'];
        $SaleSlipDetail->memo             = $SaleSlipDetailData['memo'];
        $SaleSlipDetail->supply_count     = $SaleSlipDetailData['supply_count'];
        $SaleSlipDetail->supply_unit_num  = $SaleSlipDetailData['supply_unit_num'];
        $SaleSlipDetail->sort             = $SaleSlipDetailData['sort'];
        $SaleSlipDetail->created_user_id  = $SaleSlipDetailData['user_id'];
        $SaleSlipDetail->created          = Carbon::now();
        $SaleSlipDetail->created_user_id  = $SaleSlipDetailData['user_id'];
        $SaleSlipDetail->modified         = Carbon::now();
        $SaleSlipDetail->save();

        return $SaleSlipDetail;
    }
}
