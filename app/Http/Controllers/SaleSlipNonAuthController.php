<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Standard;
use App\Staff;
use App\SaleSlip;
use App\SaleSlipDetail;
use App\SupplySlipDetail;
use App\InventoryManage;
use Carbon\Carbon;

class SaleSlipNonAuthController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // 他のコントローラーと違い、認証不要のコントローラー
        $this->SaleSlip         = new SaleSlip;
        $this->SaleSlipDetail   = new SaleSlipDetail;
        $this->SupplySlipDetail = new SupplySlipDetail;
    }

    /**
     * 当日納品一覧
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function todayDeliverySlips(Request $request)
    {
        try {

            // 納品日付を受け取る
            $delivery_date = $request->delivery_date;

            if(empty($delivery_date)){
                $delivery_date  = $request->session()->get('delivery_date');
            }

            $request->session()->put('delivery_date', $delivery_date);

            //---------------------
            // 納品日の企業を20社づつ表示する
            //---------------------
            $saleSlipList = DB::table('sale_slips AS SaleSlip')
            ->select(
                'SaleCompany.id             AS sale_company_id'
            )
            ->join('sale_companies AS SaleCompany', function ($join) {
                $join->on('SaleCompany.id', '=', 'SaleSlip.sale_company_id');
            })
            ->where('SaleSlip.delivery_date', '=', $delivery_date)
            ->where('SaleSlip.active', '=', '1')
            ->groupBy('SaleCompany.id')
            ->orderBy('SaleSlip.date', 'desc')
            ->orderBy('SaleSlip.id', 'desc')
            ->paginate(20);

            // 企業配列
            $sale_company_arr = [];
            if(!empty($saleSlipList)){
                foreach($saleSlipList as $saleSlipVal){
                    $sale_company_arr[] =$saleSlipVal->sale_company_id;
                }
            }

            //---------------------
            // 伝票詳細を取得
            //---------------------
            $SaleSlipDetailList = DB::table('sale_slip_details AS SaleSlipDetail')
            ->select(
                'SaleCompany.id                    AS sale_company_id',
                'SaleCompany.name                  AS sale_company_name',
                'Product.name                      AS product_name',
                'SaleSlipDetail.unit_num           AS unit_num',
                'Unit.name                         AS unit_name',
                'SaleSlipDetail.inventory_unit_num AS inventory_unit_num',
                'InventoryUnit.name                AS inventory_unit_name'
            )
            ->join('sale_slips as SaleSlip', function ($join) {
                $join->on('SaleSlip.id', '=', 'SaleSlipDetail.sale_slip_id')
                ->where('SaleSlip.active', '=', true);
            })
            ->join('products as Product', function ($join) {
                $join->on('Product.id', '=', 'SaleSlipDetail.product_id')
                ->where('Product.active', '=', true);
            })
            ->join('units as Unit', function ($join) {
                $join->on('Unit.id', '=', 'SaleSlipDetail.unit_id')
                ->where('Unit.active', '=', true);
            })
            ->leftJoin('units as InventoryUnit', function ($join) {
                $join->on('InventoryUnit.id', '=', 'SaleSlipDetail.inventory_unit_id')
                ->where('InventoryUnit.active', '=', true);
            })
            ->leftJoin('sale_companies as SaleCompany', function ($join) {
                $join->on('SaleCompany.id', '=', 'SaleSlip.sale_company_id')
                ->where('SaleCompany.active', '=', true);
            })
            ->whereIn('SaleCompany.id', $sale_company_arr)
            ->where('SaleSlip.delivery_date', '=', $delivery_date)
            ->orderBy('SaleCompany.id', 'desc')
            ->orderBy('SaleSlip.id', 'desc')
            ->limit(50)
            ->get();

            // 各伝票にいくつ明細がついているのかをカウントする配列
            $sale_slip_detail_arr = array();

            // 各小計が入るファイルをリセット
            $sale_slip_detail_count_arr = array();

            // 伝票詳細で取得したDBをループ
            foreach($SaleSlipDetailList as $SaleSlipDetails){

                if(!isset($sale_slip_detail_count_arr[$SaleSlipDetails->sale_company_id])){
                    $sale_slip_detail_count_arr[$SaleSlipDetails->sale_company_id] =  [
                        'sale_company_id' => $SaleSlipDetails->sale_company_id,
                        'company_name'    => $SaleSlipDetails->sale_company_name,
                        'company_count'   => 0
                    ];
                }

                $sale_slip_detail_count_arr[$SaleSlipDetails->sale_company_id]['company_count'] += 1;

                $sale_slip_detail_arr[$SaleSlipDetails->sale_company_id][] = [
                    'product_name'                => $SaleSlipDetails->product_name,
                    'unit_num'                    => $SaleSlipDetails->unit_num,
                    'unit_name'                   => $SaleSlipDetails->unit_name,
                    'inventory_unit_num'          => $SaleSlipDetails->inventory_unit_num,
                    'inventory_unit_name'         => $SaleSlipDetails->inventory_unit_name,
                ];
            }

        } catch (\Exception $e) {

            var_dump($e);

            //return view('SaleSlip.complete')->with([
            //    'errorMessage' => $e
            //]);
        }

        return view('SaleSlipNonAuth.todayDeliverySlips')->with([
            "saleSlipList"              =>$saleSlipList,
            "delivery_date"              => $delivery_date,
            "sale_slip_detail_arr"       => $sale_slip_detail_arr,
            "sale_slip_detail_count_arr" => $sale_slip_detail_count_arr
        ]);
    }
}
