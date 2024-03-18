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

class SaleSlipController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->SaleSlip         = new SaleSlip;
        $this->SaleSlipDetail   = new SaleSlipDetail;
        $this->SupplySlipDetail = new SupplySlipDetail;
    }

    /**
     * 売上一覧
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        // リクエストパスを取得
        $request_path = $request->path();
        $path_array   = explode('/', $request_path);

        // ページングの番号の有無でindexのaction先を変更
        if(count($path_array) > 1){
            $search_action = '../SaleSlipIndex';
        } else {
            $search_action = './SaleSlipIndex';
        }

        // postできたか、getできたか
        if ($_SERVER["REQUEST_METHOD"] != "POST"
        ) { // ページング処理

            $condition_date_type     = $request->session()->get('condition_date_type');
            $condition_date_from     = $request->session()->get('condition_date_from');
            $condition_date_to       = $request->session()->get('condition_date_to');

            // 空値の場合は初期値を設定
            if(empty($condition_date_type)) $condition_date_type = 1;
            if(empty($condition_date_from)) $condition_date_from = date('Y-m-d');
            if(empty($condition_date_to)) $condition_date_to     = date('Y-m-d');

            $condition_company_code  = $request->session()->get('condition_company_code');
            $condition_company_id    = $request->session()->get('condition_company_id');
            $condition_company_text  = $request->session()->get('condition_company_text');
            //$condition_shop_id       = $request->session()->get('condition_shop_id');
            //$condition_shop_code     = $request->session()->get('condition_shop_code');
            //$condition_shop_text     = $request->session()->get('condition_shop_text');
            $condition_payment_method_type      = $request->session()->get('condition_payment_method_type');
            $condition_payment_method_type_text = $request->session()->get('condition_payment_method_type_text');
            $condition_product_code  = $request->session()->get('condition_product_code');
            $condition_product_id    = $request->session()->get('condition_product_id');
            $condition_product_text  = $request->session()->get('condition_product_text');
            // トップページから遷移してくる場合があるので、条件判定
            $request_submit_type = $request->input('sale_submit_type');
            if (!empty($request_submit_type)) {
                $condition_submit_type = $request_submit_type;
            } else {
                $condition_submit_type = $request->session()->get('condition_submit_type');
            }

            $condition_display_sort  = $request->session()->get('condition_display_sort');
            $condition_display_num   = $request->session()->get('condition_display_num');
            $condition_no_display    = $request->session()->get('condition_no_display');

            // 空値の場合は初期値を設定
            if(empty($condition_display_sort)) $condition_display_sort = 0;
            if(empty($condition_display_num)) $condition_display_num = 20;
            if(empty($condition_no_display)) $condition_no_display = 0;

        } else { // POST時の処理

            if (isset($_POST['search-btn'])) { // 検索ボタン押された時の処理

                $condition_date_type     = $request->data['SaleSlip']['date_type'];
                $condition_company_code  = $request->data['SaleSlip']['sale_company_code'];
                $condition_company_id    = $request->data['SaleSlip']['sale_company_id'];
                $condition_company_text  = $request->data['SaleSlip']['sale_company_text'];
                //$condition_shop_code     = $request->data['SaleSlip']['sale_shop_code'];
                //$condition_shop_id       = $request->data['SaleSlip']['sale_shop_id'];
                //$condition_shop_text     = $request->data['SaleSlip']['sale_shop_text'];
                $condition_payment_method_type      = $request->data['SaleSlip']['payment_method_type'];
                if($condition_payment_method_type == "") {
                    $condition_payment_method_type_text = "";
                } else {
                    $condition_payment_method_type_text = $request->data['SaleSlip']['payment_method_type_text'];
                }
                $condition_product_code  = $request->data['SaleSlipDetail']['product_code'];
                $condition_product_id    = $request->data['SaleSlipDetail']['product_id'];
                $condition_product_text  = $request->data['SaleSlipDetail']['product_text'];
                $condition_submit_type   = isset($request->data['SaleSlip']['sale_submit_type']) ? $request->data['SaleSlip']['sale_submit_type'] : 0;
                $condition_display_sort  = isset($request->display_sort) ? $request->display_sort : 0;
                $condition_display_num   = isset($request->display_num) ? $request->display_num : 20;
                $condition_no_display    = isset($request->no_display) ? $request->no_display : 0;

                // 日付の設定
                $condition_date_from     = $request->data['SaleSlip']['sale_date_from'];
                $condition_date_to       = $request->data['SaleSlip']['sale_date_to'];
                // どちらか片方しか入力されなかった場合は同じ日付を入れる
                if (!empty($condition_date_from) && empty($condition_date_to)) {
                    $condition_date_to = $condition_date_from;
                }
                if (empty($condition_date_from) && !empty($condition_date_to)) {
                    $condition_date_from = $condition_date_to;
                }

                $request->session()->put('condition_date_from', $condition_date_from);
                $request->session()->put('condition_date_to', $condition_date_to);
                $request->session()->put('condition_company_code', $condition_company_code);
                $request->session()->put('condition_company_id', $condition_company_id);
                $request->session()->put('condition_company_text', $condition_company_text);
                //$$request->session()->put('condition_shop_code', $condition_shop_code);
                //$request->session()->put('condition_shop_id', $condition_shop_id);
                //$$request->session()->put('condition_shop_text', $condition_shop_text);
                $request->session()->put('condition_payment_method_type', $condition_payment_method_type);
                $request->session()->put('condition_payment_method_type_text', $condition_payment_method_type_text);
                $request->session()->put('condition_product_code', $condition_product_code);
                $request->session()->put('condition_product_id', $condition_product_id);
                $request->session()->put('condition_product_text', $condition_product_text);
                $request->session()->put('condition_submit_type', $condition_submit_type);
                $request->session()->put('condition_display_sort', $condition_display_sort);
                $request->session()->put('condition_display_num', $condition_display_num);
                $request->session()->put('condition_no_display', $condition_no_display);

            } else { // リセットボタンが押された時の処理

                $condition_date_type     = 1;
                $condition_date_from     = date('Y-m-d');
                $condition_date_to       = date('Y-m-d');
                $condition_company_code  = null;
                $condition_company_id    = null;
                $condition_company_text  = null;
                //$condition_shop_code     = null;
                //$condition_shop_id       = null;
                //$condition_shop_text     = null;
                $condition_payment_method_type = null;
                $condition_payment_method_type_text = null;
                $condition_product_code  = null;
                $condition_product_id    = null;
                $condition_product_text  = null;
                $condition_submit_type   = 0;
                $condition_display_sort  = 0;
                $condition_display_num   = 20;
                $condition_no_display    = 0;
                $request->session()->forget('condition_date_from');
                $request->session()->forget('condition_date_to');
                $request->session()->forget('condition_company_code');
                $request->session()->forget('condition_company_id');
                $request->session()->forget('condition_company_text');
                //$request->session()->forget('condition_shop_code');
                //$request->session()->forget('condition_shop_id');
                //$request->session()->forget('condition_shop_text');
                $request->session()->forget('condition_payment_method_type');
                $request->session()->forget('condition_payment_method_type_text');
                $request->session()->forget('condition_product_code');
                $request->session()->forget('condition_product_id');
                $request->session()->forget('condition_product_text');
                $request->session()->forget('condition_submit_type');
                $request->session()->forget('condition_display_sort');
                $request->session()->forget('condition_display_num');
                $request->session()->forget('condition_no_display');
            }
        }

        try {

            // sale_slip_detailsのサブクエリを作成
            $product_sub_query = null;
            if(!empty($condition_product_id)) {

                $product_sub_query = DB::table('sale_slip_details as SubTable')
                ->select('SubTable.sale_slip_id AS sale_slip_id')
                ->where('SubTable.product_id', '=', $condition_product_id)
                ->groupBy('SubTable.sale_slip_id');
            }

            //---------------------
            // 売上一覧を取得
            //---------------------
            $saleSlipList = DB::table('sale_slips AS SaleSlip')
            ->select(
                'SaleSlip.id                  AS sale_slip_id',
                'SaleSlip.payment_method_type AS payment_method_type',
                'SaleSlip.delivery_price      AS delivery_price',
                'SaleSlip.adjust_price        AS adjust_price',
                'SaleSlip.notax_sub_total     AS notax_sub_total',
                'SaleSlip.total               AS sale_slip_total',
                'SaleSlip.sale_submit_type    AS sale_submit_type',
                'SaleCompany.code             AS sale_company_code',
                'SaleCompany.name             AS sale_company_name',
                //'SaleShop.name                AS sale_shop_name'
            )
            ->selectRaw('DATE_FORMAT(SaleSlip.date, "%Y/%m/%d")          AS sale_slip_date')
            ->selectRaw('DATE_FORMAT(SaleSlip.delivery_date, "%Y/%m/%d") AS sale_slip_delivery_date')
            ->selectRaw('DATE_FORMAT(SaleSlip.modified, "%m-%d %H:%i")   AS sale_slip_modified')
            ->selectRaw('
                CASE
                WHEN SaleSlip.payment_method_type = 0 THEN "掛け売り"
                WHEN SaleSlip.payment_method_type = 1 THEN "現金売り"
                ELSE "存在しません"
                END AS payment_method_type_text'
            )
            ->join('sale_companies AS SaleCompany', function ($join) {
                $join->on('SaleCompany.id', '=', 'SaleSlip.sale_company_id');
            })
            /*->leftJoin('sale_shops AS SaleShop', function ($join) {
                $join->on('SaleShop.id', '=', 'SaleSlip.sale_shop_id');
            })*/
            ->if(!empty($condition_product_id), function ($query) use ($product_sub_query) {
                return $query
                       ->join(DB::raw('('. $product_sub_query->toSql() .') as SaleSlipDetail'), 'SaleSlipDetail.sale_slip_id', '=', 'SaleSlip.id')
                       ->mergeBindings($product_sub_query);
            })
            ->if(!empty($condition_date_from) && !empty($condition_date_to) && $condition_date_type == 1, function ($query) use ($condition_date_from, $condition_date_to) {
                return $query->whereBetween('SaleSlip.date', [$condition_date_from, $condition_date_to]);
            })
            ->if(!empty($condition_date_from) && !empty($condition_date_to) && $condition_date_type == 2, function ($query) use ($condition_date_from, $condition_date_to) {
                return $query->whereBetween('SaleSlip.delivery_date', [$condition_date_from, $condition_date_to]);
            })
            ->if(!empty($condition_company_id), function ($query) use ($condition_company_id) {
                return $query->where('SaleSlip.sale_company_id', '=', $condition_company_id);
            })
            /*->if(!empty($condition_shop_id), function ($query) use ($condition_shop_id) {
                return $query->where('SaleSlip.sale_shop_id', '=', $condition_shop_id);
            })*/
            ->if(!empty($condition_payment_method_type), function ($query) use ($condition_payment_method_type) {
                return $query->where('SaleSlip.payment_method_type', '=', $condition_payment_method_type);
            })
            ->if(!empty($condition_submit_type), function ($query) use ($condition_submit_type) {
                return $query->where('SaleSlip.sale_submit_type', '=', $condition_submit_type);
            })
            ->if(!empty($condition_no_display), function ($query) {
                return $query->where('SaleSlip.external_slip_no', '=', 0);
            })
            ->where('SaleSlip.active', '=', '1')
            //->orderBy('SaleSlip.date', 'desc')
            ->if($condition_display_sort == 0, function ($query) { // 伝票日付:降順
                return $query->orderBy('SaleSlip.date', 'desc');
            })
            ->if($condition_display_sort == 1, function ($query) { // 伝票日付:昇順
                return $query->orderBy('SaleSlip.date', 'asc');
            })
            ->if($condition_display_sort == 2, function ($query) { // 納品日付:降順
                return $query->orderBy('SaleSlip.delivery_date', 'desc');
            })
            ->if($condition_display_sort == 3, function ($query) { // 納品日付:昇順
                return $query->orderBy('SaleSlip.delivery_date', 'asc');
            })
            ->orderBy('SaleSlip.id', 'desc')
            ->paginate($condition_display_num);

            //---------------------
            // 売上一覧を総額集計
            //---------------------
            $saleSlipSumList = DB::table('sale_slips AS SaleSlip')

            ->selectRaw('COUNT(SaleSlip.id) AS sale_slip_num')
            ->selectRaw('SUM(SaleSlip.delivery_price) AS delivery_price_sum')
            ->selectRaw('SUM(SaleSlip.adjust_price) AS adjust_price_sum')
            ->selectRaw('SUM(SaleSlip.notax_sub_total) AS notax_sub_total_sum')
            ->selectRaw('SUM(SaleSlip.sub_total) AS sub_total_sum')

            ->join('sale_companies AS SaleCompany', function ($join) {
                $join->on('SaleCompany.id', '=', 'SaleSlip.sale_company_id');
            })
            /*->leftJoin('sale_shops AS SaleShop', function ($join) {
                $join->on('SaleShop.id', '=', 'SaleSlip.sale_shop_id');
            })*/
            ->if(!empty($condition_date_from) && !empty($condition_date_to) && $condition_date_type == 1, function ($query) use ($condition_date_from, $condition_date_to) {
                return $query->whereBetween('SaleSlip.date', [$condition_date_from, $condition_date_to]);
            })
            ->if(!empty($condition_date_from) && !empty($condition_date_to) && $condition_date_type == 2, function ($query) use ($condition_date_from, $condition_date_to) {
                return $query->whereBetween('SaleSlip.delivery_date', [$condition_date_from, $condition_date_to]);
            })
            ->if(!empty($condition_company_id), function ($query) use ($condition_company_id) {
                return $query->where('SaleSlip.sale_company_id', '=', $condition_company_id);
            })
            /*->if(!empty($condition_shop_id), function ($query) use ($condition_shop_id) {
                return $query->where('SaleSlip.sale_shop_id', '=', $condition_shop_id);
            })*/
            ->if(!empty($condition_payment_method_type), function ($query) use ($condition_payment_method_type) {
                return $query->where('SaleSlip.payment_method_type', '=', $condition_payment_method_type);
            })
            ->if(!empty($condition_product_id), function ($query) use ($product_sub_query) {
                return $query
                       ->join(DB::raw('('. $product_sub_query->toSql() .') as SaleSlipDetail'), 'SaleSlipDetail.sale_slip_id', '=', 'SaleSlip.id')
                       ->mergeBindings($product_sub_query);
            })
            ->if(!empty($condition_submit_type), function ($query) use ($condition_submit_type) {
                return $query->where('SaleSlip.sale_submit_type', '=', $condition_submit_type);
            })
            ->if(!empty($condition_no_display), function ($query) {
                return $query->where('SaleSlip.external_slip_no', '=', 0);
            })
            ->where('SaleSlip.active', '=', '1')
            ->get();

            // 全体で何件伝票があるのかカウント
            $sale_slip_num = 0;
            // 全体の配送金額をカウント
            $delivery_price_amount = 0;
            // 全体の調整額をカウント
            $adjust_price_amount = 0;
            // 全体の税抜小計額をカウント
            $notax_sub_total_amount = 0;
            // 全体の税込小計額をカウント
            $notax_sub_total_amount = 0;
            // 全体の総額をカウント
            $sale_slip_amount = 0;
            // 全体の税込総額をカウント
            $sale_slip_tax_amount = 0;

            if(!empty($saleSlipSumList)) {

                // 最初の要素を取得
                $saleSlipSumVal = current($saleSlipSumList);

                $sale_slip_num          = $saleSlipSumVal[0]->sale_slip_num;
                $delivery_price_amount  = $saleSlipSumVal[0]->delivery_price_sum;
                $adjust_price_amount    = $saleSlipSumVal[0]->adjust_price_sum;
                $notax_sub_total_amount = $saleSlipSumVal[0]->notax_sub_total_sum;
                $sub_total_amount       = $saleSlipSumVal[0]->sub_total_sum;
                $sale_slip_amount       = ($delivery_price_amount + $adjust_price_amount + $notax_sub_total_amount);
                $sale_slip_tax_amount   = ($delivery_price_amount + $adjust_price_amount + $sub_total_amount);
            }

            //---------------------
            // 伝票詳細を取得
            //---------------------
            $sale_slip_id_arr = array();
            foreach($saleSlipList as $saleSlipVal){
                $sale_slip_id_arr[] = $saleSlipVal->sale_slip_id;
            }

            $SaleSlipDetailList = DB::table('sale_slip_details AS SaleSlipDetail')
            ->select(
                'SaleSlip.id                  AS sale_slip_id',
                'SaleSlip.total               AS sale_slip_total',
                'SaleSlip.sale_submit_type    AS sale_submit_type',
                'SaleCompany.code             AS sale_company_code',
                'SaleCompany.name             AS sale_company_name',
                'Product.code                 AS product_code',
                'Product.name                 AS product_name',
                'Product.tax_id               AS product_tax_id',
                'Standard.name                AS standard_name',
                'SaleSlipDetail.id            AS sale_slip_detail_id',
                'SaleSlipDetail.unit_price    AS sale_slip_detail_unit_price',
                'SaleSlipDetail.unit_num      AS sale_slip_detail_unit_num',
                'Unit.name                    AS unit_name',
                'SaleSlipDetail.memo          AS sale_slip_detail_memo',
            )
            ->selectRaw('DATE_FORMAT(SaleSlip.date, "%Y/%m/%d")                AS sale_slip_date')
            ->selectRaw('DATE_FORMAT(SaleSlip.delivery_date, "%Y/%m/%d") AS sale_slip_delivery_date')
            ->selectRaw('DATE_FORMAT(SaleSlip.modified, "%m-%d %H:%i")      AS sale_slip_modified')

            ->selectRaw('CONCAT(Staff.name_sei," ",Staff.name_mei)            AS staff_name')

            ->join('sale_slips as SaleSlip', function ($join) {
                $join->on('SaleSlip.id', '=', 'SaleSlipDetail.sale_slip_id')
                ->where('SaleSlip.active', '=', true);
            })
            ->join('products as Product', function ($join) {
                $join->on('Product.id', '=', 'SaleSlipDetail.product_id')
                ->where('Product.active', '=', true);
            })
            ->leftJoin('standards as Standard', function ($join) {
                $join->on('Standard.id', '=', 'SaleSlipDetail.standard_id')
                ->where('Standard.active', '=', true);
            })
            ->join('units as Unit', function ($join) {
                $join->on('Unit.id', '=', 'SaleSlipDetail.unit_id')
                ->where('Unit.active', '=', true);
            })
            ->leftJoin('staffs as Staff', function ($join) {
                $join->on('Staff.id', '=', 'SaleSlipDetail.staff_id')
                ->where('Staff.active', '=', true);
            })
            ->leftJoin('units as InventoryUnit', function ($join) {
                $join->on('InventoryUnit.id', '=', 'SaleSlipDetail.inventory_unit_id')
                ->where('InventoryUnit.active', '=', true);
            })
            ->leftJoin('sale_companies as SaleCompany', function ($join) {
                $join->on('SaleCompany.id', '=', 'SaleSlip.sale_company_id')
                ->where('SaleCompany.active', '=', true);
            })
            ->if(!empty($condition_date_from) && !empty($condition_date_to) && $condition_date_type == 1, function ($queryDetail) use ($condition_date_from, $condition_date_to) {
                return $queryDetail->whereBetween('SaleSlip.date', [$condition_date_from, $condition_date_to]);
            })
            ->if(!empty($condition_date_from) && !empty($condition_date_to) && $condition_date_type == 2, function ($queryDetail) use ($condition_date_from, $condition_date_to) {
                return $queryDetail->whereBetween('SaleSlip.delivery_date', [$condition_date_from, $condition_date_to]);
            })
            ->if(!empty($condition_company_id), function ($queryDetail) use ($condition_company_id) {
                return $queryDetail->where('SaleSlip.sale_company_id', '=', $condition_company_id);
            })
            /*->if(!empty($condition_shop_id), function ($queryDetail) use ($condition_shop_id) {
                return $queryDetail->where('SaleSlip.sale_shop_id', '=', $condition_shop_id);
            })*/
            ->if(!empty($condition_payment_method_type), function ($query) use ($condition_payment_method_type) {
                return $query->where('SaleSlip.payment_method_type', '=', $condition_payment_method_type);
            })
            ->if(!empty($condition_product_id), function ($queryDetail) use ($condition_product_id) {
                return $queryDetail->where('SaleSlipDetail.product_id', '=', $condition_product_id);
            })
            ->if(!empty($condition_submit_type), function ($queryDetail) use ($condition_submit_type) {
                return $queryDetail->where('SaleSlip.sale_submit_type', '=', $condition_submit_type);
            })
            ->if(!empty($condition_no_display), function ($query) {
                return $query->where('SaleSlip.external_slip_no', '=', 0);
            })
            ->whereIn('SaleSlip.id', $sale_slip_id_arr)
            ->orderBy('SaleSlip.id', 'desc')
            ->orderBy('SaleSlipDetail.sort', 'asc')
            ->get();


            $sale_slip_condition_num               = 0;       // 条件指定された時の伝票の枚数
            $sale_slip_condition_notax_sub_total   = 0;       // 条件指定された伝票詳細の税抜小計
            $sale_slip_detail_arr                  = array(); // 各伝票にいくつ明細がついているのかをカウントする配列
            $sale_slip_detail_count_arr            = array(); // 各小計が入るファイルをリセット

            // 伝票詳細で取得したDBをループ
            foreach($SaleSlipDetailList as $SaleSlipDetails){

                $unit_price  = $SaleSlipDetails->sale_slip_detail_unit_price; // 単価
                $unit_num    = $SaleSlipDetails->sale_slip_detail_unit_num;   // 数量
                $notax_price = $unit_price * $unit_num;                       // 税抜小計
                $sale_slip_condition_notax_sub_total += $notax_price;         // 小計を加えていく

                if(!isset($sale_slip_detail_count_arr[$SaleSlipDetails->sale_slip_id])){
                    $sale_slip_condition_num +=1;
                    $sale_slip_detail_count_arr[$SaleSlipDetails->sale_slip_id] = 0;
                }

                $sale_slip_detail_count_arr[$SaleSlipDetails->sale_slip_id] += 1;

                $sale_slip_detail_arr[$SaleSlipDetails->sale_slip_id][] = [

                    'product_code'                => $SaleSlipDetails->product_code,
                    'product_name'                => $SaleSlipDetails->product_name,
                    'product_tax_id'              => $SaleSlipDetails->product_tax_id,
                    'standard_name'               => $SaleSlipDetails->standard_name,
                    'sale_slip_detail_id'         => $SaleSlipDetails->sale_slip_detail_id,
                    'sale_slip_detail_unit_price' => $SaleSlipDetails->sale_slip_detail_unit_price,
                    'sale_slip_detail_unit_num'   => $SaleSlipDetails->sale_slip_detail_unit_num,
                    'unit_name'                   => $SaleSlipDetails->unit_name,
                    'staff_name'                  => $SaleSlipDetails->staff_name,
                    'memo'                        => $SaleSlipDetails->sale_slip_detail_memo,
                ];
            }

            // 対象日付のチェック
            $check_str_slip_date = "";
            $check_str_deliver_date = "";
            if($condition_date_type == 1) $check_str_slip_date = "checked";
            else  $check_str_deliver_date = "checked";

        } catch (\Exception $e) {

            dd($e);

            return view('SaleSlip.complete')->with([
                'errorMessage' => $e
            ]);
        }

        return view('SaleSlip.index')->with([
            "search_action"              => $search_action,
            "check_str_slip_date"        => $check_str_slip_date,
            "check_str_deliver_date"     => $check_str_deliver_date,
            "condition_date_from"        => $condition_date_from,
            "condition_date_to"          => $condition_date_to,
            "condition_company_code"     => $condition_company_code,
            "condition_company_id"       => $condition_company_id,
            "condition_company_text"     => $condition_company_text,
            //"condition_shop_code"        => $condition_shop_code,
            //"condition_shop_id"          => $condition_shop_id,
            //"condition_shop_text"        => $condition_shop_text,
            "condition_payment_method_type" => $condition_payment_method_type,
            "condition_payment_method_type_text" => $condition_payment_method_type_text,
            "condition_product_code"     => $condition_product_code,
            "condition_product_id"       => $condition_product_id,
            "condition_product_text"     => $condition_product_text,
            "condition_submit_type"      => $condition_submit_type,
            "saleSlipList"               => $saleSlipList,
            "SaleSlipDetailList"         => $SaleSlipDetailList,
            "sale_slip_num"              => $sale_slip_num,
            "delivery_price_amount"      => $delivery_price_amount,
            "adjust_price_amount"        => $adjust_price_amount,
            "notax_sub_total_amount"     => $notax_sub_total_amount,
            "sale_slip_amount"           => $sale_slip_amount,
            "sale_slip_tax_amount"       => $sale_slip_tax_amount,
            "sale_slip_condition_num"    => $sale_slip_condition_num,
            "sale_slip_condition_notax_sub_total" => $sale_slip_condition_notax_sub_total,
            "sale_slip_detail_arr"       => $sale_slip_detail_arr,
            "sale_slip_detail_count_arr" => $sale_slip_detail_count_arr,
            "condition_display_sort"     => $condition_display_sort,
            "condition_display_num"      => $condition_display_num,
            "condition_no_display"       => $condition_no_display
        ]);
    }

    /**
     * 売上編集
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function edit($sale_slip_id)
    {
        // activeの変数を格納
        $this_active = 1;

        //------------------
        // 売上伝票取得
        //------------------
        $SaleSlipList = DB::table('sale_slips AS SaleSlip')
        ->select(
            'SaleSlip.id                  AS sale_slip_id',
            'SaleSlip.payment_method_type AS payment_method_type',
            'SaleSlip.notax_sub_total_8   AS notax_sub_total_8',
            'SaleSlip.notax_sub_total_10  AS notax_sub_total_10',
            'SaleSlip.notax_sub_total     AS notax_sub_total',
            'SaleSlip.tax_total_8         AS tax_total_8',
            'SaleSlip.tax_total_10        AS tax_total_10',
            'SaleSlip.tax_total           AS tax_total',
            'SaleSlip.sub_total_8         AS sub_total_8',
            'SaleSlip.sub_total_10        AS sub_total_10',
            'SaleSlip.sub_total           AS sub_total',
            'SaleSlip.delivery_price      AS delivery_price',
            'SaleSlip.adjust_price        AS adjust_price',
            'SaleSlip.total               AS total',
            'SaleSlip.remarks             AS remarks',
            'SaleSlip.sale_submit_type  AS sale_submit_type',
            'SaleCompany.code             AS sale_company_code',
            'SaleCompany.id               AS sale_company_id',
            'SaleCompany.name             AS sale_company_name',
            //'SaleShop.code                AS sale_shop_code',
            //'SaleShop.id                  AS sale_shop_id',
            //'SaleShop.name                AS sale_shop_name',
            'Delivery.code                AS delivery_code',
            'Delivery.id                  AS delivery_id',
            'Delivery.name                AS delivery_name'
        )
        ->selectRaw('DATE_FORMAT(SaleSlip.date, "%Y-%m-%d") AS sale_slip_sale_date')
        ->selectRaw('
            CASE
            WHEN SaleSlip.payment_method_type = 0 THEN "掛け売り"
            WHEN SaleSlip.payment_method_type = 1 THEN "現金売り"
            ELSE "存在しません"
            END AS payment_method_type_text'
        )
        ->selectRaw('DATE_FORMAT(SaleSlip.delivery_date, "%Y-%m-%d") AS sale_slip_delivery_date')
        ->join('sale_companies as SaleCompany', function ($join) {
            $join->on('SaleCompany.id', '=', 'SaleSlip.sale_company_id')
                 ->where('SaleCompany.active', '=', true);
        })
        /*->leftJoin('sale_shops as SaleShop', function ($join) {
            $join->on('SaleShop.id', '=', 'SaleSlip.sale_shop_id')
                 ->where('SaleShop.active', '=', true);
        })*/
        ->leftJoin('deliverys as Delivery', function ($join) {
            $join->on('Delivery.id', '=', 'SaleSlip.delivery_id')
                 ->where('Delivery.active', '=', true);
        })
        ->where([
            ['SaleSlip.id', '=', $sale_slip_id],
            ['SaleSlip.active', '=', $this_active],
        ])
        ->first();

        //------------------
        // 売上伝票詳細取得
        //------------------

        // inventory_managesのサブクエリを作成
        $inventory_sub_query = DB::table('inventory_manages as SubInventoryManage')
            ->select(
                'SubInventoryManage.sale_detail_slip_id   AS sale_detail_slip_id'
            )
            ->selectRaw('count(SubInventoryManage.unit_num) AS unit_num_count')
            ->selectRaw('sum(SubInventoryManage.unit_num) AS unit_num_sum')
            ->join('sale_slip_details as SubSaleSlipDetail', function ($join) {
                $join->on('SubSaleSlipDetail.id', '=', 'SubInventoryManage.sale_detail_slip_id');
            })
            ->whereRaw('SubSaleSlipDetail.sale_slip_id =' . $sale_slip_id)
            ->groupBy('SubInventoryManage.sale_detail_slip_id');

        // メインSQL
        $SaleSlipDetailList = DB::table('sale_slip_details AS SaleSlipDetail')
        ->select(
            'SaleSlipDetail.id                 AS sale_slip_detail_id',
            'SaleSlipDetail.unit_price         AS unit_price',
            'SaleSlipDetail.unit_num           AS unit_num',
            'SaleSlipDetail.notax_price        AS notax_price',
            'SaleSlipDetail.seri_no            AS seri_no',
            'SaleSlipDetail.inventory_unit_num AS inventory_unit_num',
            'SaleSlipDetail.memo               AS memo',
            'SaleSlipDetail.sort               AS sort',
            'Product.code                      AS product_code',
            'Product.id                        AS product_id',
            'Product.name                      AS product_name',
            'Tax.id                            AS tax_id',
            'Tax.name                          AS tax_name',
            'Standard.code                     AS standard_code',
            'Standard.id                       AS standard_id',
            'Standard.name                     AS standard_name',
            'Quality.code                      AS quality_code',
            'Quality.id                        AS quality_id',
            'Quality.name                      AS quality_name',
            'Unit.id                           AS unit_id',
            'Unit.name                         AS unit_name',
            'OriginArea.id                     AS origin_area_id',
            'OriginArea.name                   AS origin_area_name',
            'Staff.code                        AS staff_code',
            'Staff.id                          AS staff_id',
            'InventoryManage.unit_num_count    AS unit_num_count',
            'InventoryManage.unit_num_sum      AS unit_num_sum',
            'InventoryUnit.id                  AS inventory_unit_id',
            'InventoryUnit.name                AS inventory_unit_name'
        )
        ->selectRaw('CONCAT(Staff.name_sei," ",Staff.name_mei) AS staff_name')
        ->join('products as Product', function ($join) {
            $join->on('Product.id', '=', 'SaleSlipDetail.product_id')
                 ->where('Product.active', '=', true);
        })
        ->join('taxes as Tax', function ($join) {
            $join->on('Tax.id', '=', 'Product.tax_id')
                 ->where('Tax.active', '=', true);
        })
        ->leftJoin('standards as Standard', function ($join) {
            $join->on('Standard.id', '=', 'SaleSlipDetail.standard_id')
                 ->where('Standard.active', '=', true);
        })
        ->leftJoin('qualities as Quality', function ($join) {
            $join->on('Quality.id', '=', 'SaleSlipDetail.quality_id')
                 ->where('Quality.active', '=', true);
        })
        ->join('units as Unit', function ($join) {
            $join->on('Unit.id', '=', 'SaleSlipDetail.unit_id')
                 ->where('Unit.active', '=', true);
        })
        ->leftJoin('units as InventoryUnit', function ($join) {
            $join->on('InventoryUnit.id', '=', 'SaleSlipDetail.inventory_unit_id')
                 ->where('InventoryUnit.active', '=', true);
        })
        ->leftJoin('origin_areas as OriginArea', function ($join) {
            $join->on('OriginArea.id', '=', 'SaleSlipDetail.origin_area_id')
                 ->where('OriginArea.active', '=', true);
        })
        ->join('staffs as Staff', function ($join) {
            $join->on('Staff.id', '=', 'SaleSlipDetail.staff_id')
                 ->where('Staff.active', '=', true);
        })
        ->leftjoin(DB::raw('('. $inventory_sub_query->toSql() .') as InventoryManage'), function ($join) {
            $join->on('InventoryManage.sale_detail_slip_id', '=', 'SaleSlipDetail.id');
        })
        ->where([
            ['SaleSlipDetail.sale_slip_id','=', $sale_slip_id],
            ['SaleSlipDetail.active','=', $this_active]
        ])
        ->get();

        //------------------
        // 在庫管理
        //------------------
        $inventoryManageList = DB::table('sale_slip_details as SaleSlipDetail')
            ->select(
                'SaleSlipDetail.id                      AS sale_detail_slip_id',
                'SaleSlipDetail.sort                    AS sale_slip_detail_sort',
                'InventoryManage.supply_detail_slip_id  AS supply_detail_slip_id',
                'InventoryManage.unit_num               AS unit_num'
            )
            ->selectRaw(
                'COALESCE(InventoryManage.sort, 0) AS inventory_manage_sort'
            )
            ->leftJoin('inventory_manages as InventoryManage', function ($join) {
                $join->on('InventoryManage.sale_detail_slip_id', '=', 'SaleSlipDetail.id')
                     ->where('InventoryManage.active', '=', true);
            })
            ->where('SaleSlipDetail.sale_slip_id', '=', $sale_slip_id)
            ->orderByRaw("SaleSlipDetail.sort asc, InventoryManage.sort asc")
            ->get();

        // 在庫管理しやすいようにする
        $inventoryManageArr = array();
        foreach($inventoryManageList as $inventoryManageVal) {

            $sale_slip_detail_sort = $inventoryManageVal->sale_slip_detail_sort;
            $inventory_manage_sort = $inventoryManageVal->inventory_manage_sort;

            $inventoryManageArr[$sale_slip_detail_sort][$inventory_manage_sort] = [
                'sale_detail_slip_id'   => $inventoryManageVal->sale_detail_slip_id,
                'supply_detail_slip_id' => $inventoryManageVal->supply_detail_slip_id,
                'unit_num'              => $inventoryManageVal->unit_num,
            ];
        }

        // 入金済みの伝票かチェック
        $depositList = DB::table('deposits as Deposit')
        ->select('Deposit.deposit_submit_type AS deposit_flg')
        ->join('deposit_withdrawal_details as DepositWithdrawalDetail', function ($join) {
            $join->on('Deposit.id', '=', 'DepositWithdrawalDetail.deposit_withdrawal_id');
        })
        ->where([
            ['DepositWithdrawalDetail.supply_sale_slip_id', '=', $sale_slip_id],
            ['DepositWithdrawalDetail.type', '=', '2'],
            ['DepositWithdrawalDetail.active', '=', true],
            ['Deposit.active', '=', true]
        ])
        ->get();
        $depositFlg = 99;
        if (isset($depositList[0])) {
            $depositFlg = $depositList[0]->deposit_flg;
        }

        return view('SaleSlip.edit')->with([
            "sale_slip_id"        => $sale_slip_id,
            "SaleSlipList"        => $SaleSlipList,
            "SaleSlipDetailList"  => $SaleSlipDetailList,
            "inventoryManageArr"  => $inventoryManageArr,
            "depositFlg"          => $depositFlg,
        ]);
    }

    /**
     * 編集登録
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function editRegister(Request $request)
    {

        // トランザクション開始
        DB::connection()->beginTransaction();

        try{

            // ユーザー情報の取得
            $user_info    = \Auth::user();
            $user_info_id = $user_info['id'];

            $SaleSlipData = $request->data['SaleSlip'];
            $SaleSlipDetailData = $request->data['SaleSlipDetail'];

            if ($SaleSlipData['sale_submit_type'] == 3) {

                // -----------------
                // 伝票を論理削除させる
                // -----------------
                $SaleSlip = \App\SaleSlip::find($SaleSlipData['id']);
                $SaleSlip->active           = 0;              // アクティブフラグ
                $SaleSlip->modified_user_id = $user_info_id;  // 更新者ユーザーID
                $SaleSlip->modified         = Carbon::now();  // 更新時間

                $SaleSlip->save();

            } else {

                // 値がNULLのところを初期化
                //if(empty($SaleSlipData['sale_shop_id'])) $SaleSlipData['sale_shop_id'] = 0;
                if(empty($SaleSlipData['delivery_id'])) $SaleSlipData['delivery_id'] = 0;
                if(empty($SaleSlipData['delivery_price'])) $SaleSlipData['delivery_price'] = 0;
                if(empty($SaleSlipData['adjust_price'])) $SaleSlipData['adjust_price'] = 0;

                // sale_slipsを登録する
                $SaleSlip = \App\SaleSlip::find($SaleSlipData['id']);
                $SaleSlip->date               = $SaleSlipData['sale_date'];            // 日付
                $SaleSlip->delivery_date      = $SaleSlipData['delivery_date'];        // 納品日
                $SaleSlip->sale_company_id    = $SaleSlipData['sale_company_id'];      // 売上先ID
                //$SaleSlip->sale_shop_id       = $SaleSlipData['sale_shop_id'];         // 売上先店舗ID
                $SaleSlip->payment_method_type = $SaleSlipData['payment_method_type']; // 支払い方法
                $SaleSlip->delivery_id        = $SaleSlipData['delivery_id'];          // 配送ID
                $SaleSlip->notax_sub_total_8  = $SaleSlipData['notax_sub_total_8'];    // 8%課税対象額
                $SaleSlip->notax_sub_total_10 = $SaleSlipData['notax_sub_total_10'];   // 10%課税対象額
                $SaleSlip->notax_sub_total    = $SaleSlipData['notax_sub_total'];      // 税抜合計額
                $SaleSlip->tax_total_8        = $SaleSlipData['tax_total_8'];          // 8%課税対象額
                $SaleSlip->tax_total_10       = $SaleSlipData['tax_total_10'];         // 10%課税対象額
                $SaleSlip->tax_total          = $SaleSlipData['tax_total'];            // 税抜合計額
                $SaleSlip->sub_total_8        = $SaleSlipData['sub_total_8'];          // 8%合計額
                $SaleSlip->sub_total_10       = $SaleSlipData['sub_total_10'];         // 10%合計額
                $SaleSlip->delivery_price     = $SaleSlipData['delivery_price'];       // 合計額
                $SaleSlip->sub_total          = $SaleSlipData['sub_total'];            // 配送額
                $SaleSlip->adjust_price       = $SaleSlipData['adjust_price'];         // 調整額
                $SaleSlip->total              = $SaleSlipData['total'];                // 合計額
                $SaleSlip->remarks            = $SaleSlipData['remarks'];              // 備考
                $SaleSlip->sale_submit_type   = $SaleSlipData['sale_submit_type'];     // 登録タイプ
                $SaleSlip->modified_user_id   = $user_info_id;                         // 更新者ユーザーID
                $SaleSlip->modified           = Carbon::now();                         // 更新時間

                $SaleSlip->save();

                // 作成したIDを取得する
                $sale_slip_new_id = $SaleSlip->id;

                // 伝票詳細を削除
                \App\SaleSlipDetail::where('sale_slip_id', $SaleSlipData['id'])->delete();

                $sale_slip_detail = array();
                $sort = 0;
                $staffId = '';

                $saleSlipDetailIds = array();
                foreach($SaleSlipDetailData as $SaleSlipDetail){

                    // 値がNULLのところを初期化
                    if (empty($SaleSlipDetail['standard_id'])) $SaleSlipDetail['standard_id'] = 0;
                    if (empty($SaleSlipDetail['quality_id'])) $SaleSlipDetail['quality_id'] = 0;
                    if (empty($SaleSlipDetail['origin_area_id'])) $SaleSlipDetail['origin_area_id'] = 0;
                    if (empty($SaleSlipDetail['seri_no'])) $SaleSlipDetail['seri_no'] = 0;

                    $sale_slip_detail[] = [
                        'sale_slip_id'       => $sale_slip_new_id,
                        'product_id'         => $SaleSlipDetail['product_id'],
                        'standard_id'        => $SaleSlipDetail['standard_id'],
                        'quality_id'         => $SaleSlipDetail['quality_id'],
                        'unit_price'         => $SaleSlipDetail['unit_price'],
                        'unit_num'           => $SaleSlipDetail['unit_num'],
                        'notax_price'        => $SaleSlipDetail['notax_price'],
                        'unit_id'            => $SaleSlipDetail['unit_id'],
                        'origin_area_id'     => $SaleSlipDetail['origin_area_id'],
                        'staff_id'           => $SaleSlipDetail['staff_id'],
                        'seri_no'            => $SaleSlipDetail['seri_no'],
                        'inventory_unit_id'  => $SaleSlipDetail['inventory_unit_id'],
                        'inventory_unit_num' => $SaleSlipDetail['inventory_unit_num'],
                        'memo'               => $SaleSlipDetail['memo'],
                        'sort'               => $sort,
                        'created_user_id'    => $user_info_id,
                        'created'            => Carbon::now(),
                        'modified_user_id'   => $user_info_id,
                        'modified'           => Carbon::now(),
                    ];

                    $sort ++;

                    if (empty($staffId)) $staffId = $SaleSlipDetail['staff_id'];
                }

                if(!empty($sale_slip_detail)) {

                    DB::table('sale_slip_details')->insert($sale_slip_detail);
                    $saleSlipDetailIds[] = DB::getPdo()->lastInsertId();
                }

                // inventory_managesも物理削除して新規登録する

                // -----------------------------
                // 登録されている対象売上データの削除
                // -----------------------------

                if (isset($SaleSlipDetailData[0]) && !empty($SaleSlipDetailData[0])) {
                    \App\InventoryManage::where('sale_detail_slip_id', $SaleSlipDetailData[0]['id'])->delete();
                }

                // -------
                // 新規登録
                // -------
                // 登録データ用格納配列初期化
                $inventoryManage = array();

                if (isset($request->data['InventoryManage']) && !empty($request->data['InventoryManage'])) {
                    foreach ($request->data['InventoryManage'] as $key => $requestInventoryManageDatas) {
                        foreach ($requestInventoryManageDatas['supply_slip_id'] as $supplySlipIdKey => $supplySlipId) {

                            // 利用仕入数取得
                            $unitNum = floatval($requestInventoryManageDatas['use_num'][$supplySlipIdKey]);

                            // 登録データ格納
                            $inventoryManage[] = [
                                "sale_detail_slip_id"       => $saleSlipDetailIds[$key],
                                "supply_detail_slip_id"     => $supplySlipId,
                                "inventory_type"            => 2,
                                "unit_num"                  => $unitNum,
                                "sort"                      => $supplySlipIdKey,
                                "created_user_id"           => $user_info_id,
                                "created"                   => Carbon::now(),
                                "modified_user_id"          => $user_info_id,
                                "modified"                  => Carbon::now()
                            ];

                            // 対象の仕入伝票を取得
                            $SupplySlipDetailList = DB::table('supply_slip_details AS SupplySlipDetail')
                            ->select(
                                'SupplySlipDetail.id           AS supply_slip_detail_id',
                                'SupplySlipDetail.unit_num     AS unit_num',
                                'SupplySlipDetail.consumption  AS consumption'
                            )
                                ->where([
                                    ['SupplySlipDetail.id', '=', $supplySlipId],
                                    ['SupplySlipDetail.active', '=', 1],
                                ])->first();

                            // もし伝票が存在しない場合はエラーを飛ばす
                            if (empty($SupplySlipDetailList)) {
                                throw new \Exception("存在しない仕入伝票が選択されています。");
                            }

                            $sale_slip_detail_unit_num    = floatval($SupplySlipDetailList->unit_num);
                            $sale_slip_detail_consumption = floatval($SupplySlipDetailList->consumption);
                            $sale_slip_detail_remain      = $sale_slip_detail_unit_num - $sale_slip_detail_consumption;

                            if ($sale_slip_detail_remain < $unitNum) {
                                throw new \Exception("在庫数より多くの仕入数が入ってきています。");
                            }

                            // 今回の利用数を含めて登録
                            $sale_slip_detail_unit_num_new = $sale_slip_detail_consumption + $unitNum;

                            // 仕入伝票登録を登録する
                            $SupplySlipDetail = \App\SupplySlipDetail::find($SupplySlipDetailList->supply_slip_detail_id);
                            $SupplySlipDetail->consumption        = $sale_slip_detail_unit_num_new;
                            $SupplySlipDetail->modified_user_id   = $user_info_id;
                            $SupplySlipDetail->modified           = Carbon::now();
                            $SupplySlipDetail->save();

                            if (!empty($inventoryManage)) {

                                DB::table('inventory_manages')->insert($inventoryManage);
                            }
                        }
                    }
                }
            }

            // ------------------------------------
            // 編集した売上伝票に紐づく入金伝票を削除する
            // ------------------------------------
            // 入金伝票IDを取得
            $depositDatas = DB::table('deposits AS Deposit')
            ->select('Deposit.id AS deposit_id')
            ->join('deposit_withdrawal_details AS DepositWithdrawalDetail', function ($join) {
                $join->on('Deposit.id', '=', 'DepositWithdrawalDetail.deposit_withdrawal_id');
            })
            ->where([
                ['DepositWithdrawalDetail.supply_sale_slip_id', '=', $SaleSlipData['id']],
                ['DepositWithdrawalDetail.type', '=', '2'],
                ['DepositWithdrawalDetail.active', '=', true],
                ['Deposit.active', '=', true]
            ])
            ->get();

            // 取得してきた伝票IDを削除する
            if (isset($depositDatas[0])) {
                $updateParams = array(
                    'active'           => 0,                // アクティブフラグ
                    'modified_user_id' => $user_info_id,    // 更新者ユーザーID
                    'modified'         => Carbon::now()     // 更新時間
                );

                // 更新処理
                DB::table('deposits')
                ->where('id', '=', $depositDatas[0]->deposit_id)
                ->update($updateParams);

                // 対象の売上データのsale_flgを0に戻す
                $SaleSlip = \App\SaleSlip::find($SaleSlipData['id']);
                $SaleSlip->sale_flg         = 0;              // 売上フラグ
                $SaleSlip->modified_user_id = $user_info_id;  // 更新者ユーザーID
                $SaleSlip->modified         = Carbon::now();  // 更新時間

                $SaleSlip->save();
            }

            // 問題なければコミット
            DB::connection()->commit();

            // 登録のタイプが請求書印刷の場合(sale_submit_type:4)
            if ($SaleSlipData['sale_submit_type'] == 4) {

                // 請求登録をして請求一覧画面に遷移させる
                $this->registerSaleDeposit($SaleSlip->id, $staffId);

                return redirect('./DepositIndex');

            }

        } catch (\Exception $e) {

            DB::rollback();

            dd($e);
        }

        return redirect('./SaleSlipCreate');
    }

    /**
     * 売上登録
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function create()
    {
        return view('SaleSlip.create');
    }

    /**
     * 製品ID更新時のAjax処理
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function AjaxChangeProductId(Request $request)
    {
        // 伝票番号を取得
        $slip_num = $request->slip_num;

        // 製品IDを取得
        $product_id = $request->selected_product_id;

        if (!empty($product_id)) {

            // 規格情報取得
            $StandardList = Standard::where([
                ['product_id', $product_id],
                ['active', 1],
            ])->orderBy('sort', 'asc')->get();

            // 製品の税率情報を取得
            $productList = DB::table('products AS Product')
            ->select(
                'Product.tax_id  AS tax_id',
                'Tax.name        AS tax_name'
            )
                ->join('taxes AS Tax', function ($join) {
                    $join->on('Tax.id', '=', 'Product.tax_id');
                })
                ->where([
                    ['Product.id', '=', $product_id],
                    ['Product.active', '=', '1'],
                ])->first();

            $tax_id   = $productList->tax_id;
            $tax_name = $productList->tax_name;

        } else {
            $tax_id   = 0;
            $tax_name = '';
        }

        // 規格のSELECTを形成
        $ajaxHtml = '';
        $ajaxHtml .= "<select class='form-control' id='standard_id_{$slip_num}' name='data[SaleSlip][standard_id][{$slip_num}]'>";
        $ajaxHtml .= "    <option value='0'>-</option>";
                        if(!empty($StandardList)) {
                           foreach ($StandardList as $Standards){
        $ajaxHtml .= "    <option value='{$Standards->id}'>{$Standards->name}</option>";
                           }
                        }
        $ajaxHtml .= "</select>";

        $returnArray = array($ajaxHtml, $tax_id, $tax_name);
        return $returnArray;

    }

    /**
     * 売上企業ID更新時のAjax処理
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function AjaxAutoCompleteSaleCompany(Request $request)
    {
        // 入力された値を取得
        $input_text = $request->inputText;

        // 入力候補を初期化
        $auto_complete_array = array();

        if (!empty($input_text)) {

            // 製品DB取得
            $saleCompanyList = DB::table('sale_companies AS SaleCompany')
            ->select(
                'SaleCompany.name  AS sale_company_name'
            )->where([
                    ['SaleCompany.active', '=', '1'],
            ])->where(function($query) use ($input_text){
                $query
                ->orWhere('SaleCompany.name', 'like', "%{$input_text}%")
                ->orWhere('SaleCompany.yomi', 'like', "%{$input_text}%");
            })
            ->get();

            if (!empty($saleCompanyList)) {

                foreach ($saleCompanyList as $sale_company_val) {

                    array_push($auto_complete_array, $sale_company_val->sale_company_name);
                }
            }
        }

        return json_encode($auto_complete_array);
    }

    /**
     * 売上先企業更新時のAjax処理
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function AjaxSetSaleCompany(Request $request)
    {
        // 入力された値を取得
        $input_text = $request->inputText;

        // すべて数字かどうかチェック
        if (is_numeric($input_text)) {
            $input_code = $input_text;
            $input_name = null;
        } else {
            $input_code = null;
            $input_name = $input_text;
        }

        // 初期化
        $output_code = null;
        $output_id   = null;
        $output_name = null;

        if (!empty($input_text)) {

            // 製品DB取得
            // 製品一覧を取得
            $saleCompanyList = DB::table('sale_companies AS SaleCompany')
            ->select(
                'SaleCompany.code  AS code',
                'SaleCompany.id    AS id',
                'SaleCompany.name  AS name',
                'SaleCompany.tax_calc_type  AS tax_calc_type',
            )
            ->if(!empty($input_code), function ($query) use ($input_code) {
                return $query->where('SaleCompany.code', '=', $input_code);
            })
            ->if(!empty($input_name), function ($query) use ($input_name) {
                return $query->where('SaleCompany.name', 'like', $input_name);
            })
            ->first();

            if (!empty($saleCompanyList)) {
                $output_code = $saleCompanyList->code;
                $output_id   = $saleCompanyList->id;
                $output_name = $saleCompanyList->name;
                $output_tax_calc_type = $saleCompanyList->tax_calc_type;
            }
        }

        $returnArray = array($output_code, $output_id, $output_name, $output_tax_calc_type);

        return json_encode($returnArray);
    }

    /**
     * 売上店舗ID更新時のAjax処理
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function AjaxAutoCompleteSaleShop(Request $request)
    {
        // 入力された値を取得
        $input_text = $request->inputText;

        // 入力候補を初期化
        $auto_complete_array = array();

        if (!empty($input_text)) {

            // 製品DB取得
            $saleShopList = DB::table('sale_shops AS SaleShop')
            ->select(
                'SaleShop.name  AS sale_shop_name'
            )->where([
                    ['SaleShop.active', '=', '1'],
            ])->where(function($query) use ($input_text){
                $query
                ->orWhere('SaleShop.name', 'like', "%{$input_text}%")
                ->orWhere('SaleShop.yomi', 'like', "%{$input_text}%");
            })
            ->get();

            if (!empty($saleShopList)) {

                foreach ($saleShopList as $sale_shop_val) {

                    array_push($auto_complete_array, $sale_shop_val->sale_shop_name);
                }
            }
        }

        return json_encode($auto_complete_array);
    }

    /**
     * 売上先店舗更新時のAjax処理
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function AjaxSetSaleShop(Request $request)
    {
        // 入力された値を取得
        $input_text = $request->inputText;

        // すべて数字かどうかチェック
        if (is_numeric($input_text)) {
            $input_code = $input_text;
            $input_name = null;
        } else {
            $input_code = null;
            $input_name = $input_text;
        }

        // 初期化
        $output_code = null;
        $output_id   = null;
        $output_name = null;

        if (!empty($input_text)) {

            // 製品DB取得
            // 製品一覧を取得
            $saleShopList = DB::table('sale_shops AS SaleShop')
            ->select(
                'SaleShop.code  AS code',
                'SaleShop.id    AS id',
                'SaleShop.name  AS name'
            )
            ->if(!empty($input_code), function ($query) use ($input_code) {
                return $query->where('SaleShop.code', '=', $input_code);
            })
            ->if(!empty($input_name), function ($query) use ($input_name) {
                return $query->where('SaleShop.name', 'like', $input_name);
            })
            ->first();

            if (!empty($saleShopList)) {
                $output_code = $saleShopList->code;
                $output_id   = $saleShopList->id;
                $output_name = $saleShopList->name;
            }
        }

        $returnArray = array($output_code, $output_id, $output_name);

        return json_encode($returnArray);
    }

    /**
     * 売上先店舗更新時のAjax処理
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function AjaxSetPaymentMethodType(Request $request)
    {
        // 初期化
        $output_name = null;

        // 入力された値を取得
        $input_text = $request->inputText;

        if($input_text == 0){
            $output_name = "掛け売り";
        } else if ($input_text == 1){
            $output_name = "現金売り";
        } else {
            $output_name = "存在しないコードです";
        }

        $returnArray = array($output_name);

        return json_encode($returnArray);
    }

    /**
     * 製品ID更新時のAjax処理
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function AjaxAutoCompleteProduct(Request $request)
    {
        // 入力された値を取得
        $input_text = $request->inputText;

        // 入力候補を初期化
        $auto_complete_array = array();

        if (!empty($input_text)) {

            // 製品DB取得
            $productList = DB::table('products AS Product')
            ->select(
                'Product.name  AS product_name'
            )->where([
                    ['Product.active', '=', '1'],
            ])->where(function($query) use ($input_text){
                $query
                ->orWhere('Product.name', 'like', "%{$input_text}%")
                ->orWhere('Product.yomi', 'like', "%{$input_text}%");
            })
            ->get();

            if (!empty($productList)) {

                foreach ($productList as $product_val) {

                    array_push($auto_complete_array, $product_val->product_name);
                }
            }
        }

        return json_encode($auto_complete_array);
    }

    /**
     * 製品ID更新時のAjax処理
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function AjaxSetProduct(Request $request)
    {
        // 入力された値を取得
        $input_text = $request->inputText;

        // すべて数字かどうかチェック
        if (is_numeric($input_text)) {
            $product_code = $input_text;
            $product_name = null;
        } else {
            // 先頭2文字がis001などの場合
            $replace_result = substr_replace($input_text, '', 0, 2);
            if(is_numeric($replace_result)){
                $product_code = $input_text;
                $product_name = null;
            }

            $product_code = null;
            $product_name = $input_text;
        }

        // 初期化
        $output_product_code        = null;
        $output_product_id          = null;
        $output_product_name        = null;
        $output_tax_id              = null;
        $output_tax_name            = null;
        $output_unit_id             = null;
        $output_unit_name           = null;
        $output_inventory_unit_id   = null;
        $output_inventory_unit_name = null;

        if (!empty($input_text)) {

            // 製品DB取得
            // 製品一覧を取得
            $productList = DB::table('products AS Product')
            ->select(
                'Product.code  AS code',
                'Product.id    AS id',
                'Product.name  AS product_name',
                'Tax.id        AS tax_id',
                'Tax.name      AS tax_name',
                'Unit.id       AS unit_id',
                'Unit.name     AS unit_name',
                'InventoryUnit.id   AS inventory_unit_id',
                'InventoryUnit.name AS inventory_unit_name'
            )->join('taxes AS Tax', function ($join) {
                $join->on('Tax.id', '=', 'Product.tax_id');
            }
            )->join('units AS Unit', function ($join) {
                $join->on('Unit.id', '=', 'Product.unit_id');
            }
            )->leftJoin('units AS InventoryUnit', function ($join) {
                $join->on('InventoryUnit.id', '=', 'Product.inventory_unit_id');
            })
            ->if(!empty($product_code), function ($query) use ($product_code) {
                return $query->where('Product.code', '=', $product_code);
            })
            ->if(!empty($product_name), function ($query) use ($product_name) {
                return $query->where('Product.name', 'like', $product_name);
            })
            ->first();

            if (!empty($productList)) {
                $output_product_code        = $productList->code;
                $output_product_id          = $productList->id;
                $output_product_name        = $productList->product_name;
                $output_tax_id              = $productList->tax_id;
                $output_tax_name            = $productList->tax_name;
                $output_unit_id             = $productList->unit_id;
                $output_unit_name           = $productList->unit_name;
                $output_inventory_unit_id   = $productList->inventory_unit_id;
                $output_inventory_unit_name = $productList->inventory_unit_name;
            }
        }

        $returnArray = array(
            $output_product_code,
            $output_product_id,
            $output_product_name,
            $output_tax_id,
            $output_tax_name,
            $output_unit_id,
            $output_unit_name,
            $output_inventory_unit_id,
            $output_inventory_unit_name
        );

        return json_encode($returnArray);
    }

    /**
     * 規格ID更新時のAjax処理
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function AjaxAutoCompleteStandard(Request $request)
    {
        // 入力された値を取得
        $productId  = $request->productId;
        $input_text = $request->inputText;

        // 入力候補を初期化
        $auto_complete_array = array();

        if (!empty($input_text)) {

            // 製品DB取得
            $standardList = DB::table('standards AS Standard')
            ->select(
                'Standard.name  AS name'
            )->where([
                    ['Standard.product_id', '=', $productId],
                    ['Standard.active', '=', '1'],
            ])->where(function($query) use ($input_text){
                $query
                ->orWhere('Standard.name', 'like', "%{$input_text}%")
                ->orWhere('Standard.yomi', 'like', "%{$input_text}%");
            })
            ->get();

            if (!empty($standardList)) {

                foreach ($standardList as $standard_val) {

                    array_push($auto_complete_array, $standard_val->name);
                }
            }
        }

        return json_encode($auto_complete_array);
    }

    /**
     * 規格ID更新時のAjax処理
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function AjaxSetStandard(Request $request)
    {
        // 入力された値を取得
        $productId  = $request->productId;
        $input_text = $request->inputText;

        // すべて数字かどうかチェック
        if (is_numeric($input_text)) {
            $input_code = $input_text;
            $input_name = null;
        } else {
            $input_code = null;
            $input_name = $input_text;
        }

        // 初期化
        $output_code = null;
        $output_id = null;
        $output_name = null;

        if (!empty($input_text)) {

            // 製品DB取得
            // 製品一覧を取得
            $productList = DB::table('standards AS Standard')
            ->select(
                'Standard.code  AS code',
                'Standard.id    AS id',
                'Standard.name  AS name'
            )
            ->if(!empty($input_code), function ($query) use ($input_code) {
                return $query->where('Standard.code', '=', $input_code);
            })
            ->if(!empty($input_name), function ($query) use ($input_name) {
                return $query->where('Standard.name', 'like', $input_name);
            })->where([
                ['Standard.product_id', '=', $productId],
                ['Standard.active', '=', '1'],
            ])->first();

            if (!empty($productList)) {
                $output_code = $productList->code;
                $output_id   = $productList->id;
                $output_name = $productList->name;
            }
        }

        $returnArray = array($output_code, $output_id, $output_name);

        return json_encode($returnArray);
    }

    /**
     * 品質ID更新時のAjax処理
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function AjaxAutoCompleteQuality(Request $request)
    {
        // 入力された値を取得
        $input_text = $request->inputText;

        // 入力候補を初期化
        $auto_complete_array = array();

        if (!empty($input_text)) {

            // 製品DB取得
            $qualityList = DB::table('qualities AS Quality')
            ->select(
                'Quality.name  AS name'
            )->where([
                    ['Quality.active', '=', '1'],
            ])->where(function($query) use ($input_text){
                $query
                ->orWhere('Quality.name', 'like', "%{$input_text}%")
                ->orWhere('Quality.yomi', 'like', "%{$input_text}%");
            })
            ->get();

            if (!empty($qualityList)) {

                foreach ($qualityList as $quality_val) {

                    array_push($auto_complete_array, $quality_val->name);
                }
            }
        }

        return json_encode($auto_complete_array);
    }

    /**
     * 品質ID更新時のAjax処理
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function AjaxSetQuality(Request $request)
    {
        // 入力された値を取得
        $input_text = $request->inputText;

        // すべて数字かどうかチェック
        if (is_numeric($input_text)) {
            $input_code = $input_text;
            $input_name = null;
        } else {
            $input_code = null;
            $input_name = $input_text;
        }

        // 初期化
        $output_code = null;
        $output_id = null;
        $output_name = null;

        if (!empty($input_text)) {

            // 製品DB取得
            // 製品一覧を取得
            $qualityList = DB::table('qualities AS Quality')
            ->select(
                'Quality.code  AS code',
                'Quality.id    AS id',
                'Quality.name  AS name'
            )
            ->if(!empty($input_code), function ($query) use ($input_code) {
                return $query->where('Quality.code', '=', $input_code);
            })
            ->if(!empty($input_name), function ($query) use ($input_name) {
                return $query->where('Quality.name', 'like', $input_name);
            })
            ->first();

            if (!empty($qualityList)) {
                $output_code = $qualityList->code;
                $output_id   = $qualityList->id;
                $output_name = $qualityList->name;
            }
        }

        $returnArray = array($output_code, $output_id, $output_name);

        return json_encode($returnArray);
    }

    /**
     * 産地ID更新時のAjax処理
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function AjaxAutoCompleteOriginArea(Request $request)
    {
        // 入力された値を取得
        $input_text = $request->inputText;

        // 入力候補を初期化
        $auto_complete_array = array();

        if (!empty($input_text)) {

            // 製品DB取得
            $originAreaList = DB::table('origin_areas AS OriginArea')
            ->select(
                'OriginArea.name  AS name'
            )->where([
                    ['OriginArea.active', '=', '1'],
            ])->where(function($query) use ($input_text){
                $query
                ->orWhere('OriginArea.name', 'like', "%{$input_text}%")
                ->orWhere('OriginArea.yomi', 'like', "%{$input_text}%");
            })
            ->get();

            if (!empty($originAreaList)) {

                foreach ($originAreaList as $origin_area_val) {

                    array_push($auto_complete_array, $origin_area_val->name);
                }
            }
        }

        return json_encode($auto_complete_array);
    }

    /**
     * 産地ID更新時のAjax処理
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function AjaxSetOriginArea(Request $request)
    {
        // 入力された値を取得
        $input_text = $request->inputText;

        // すべて数字かどうかチェック
        if (is_numeric($input_text)) {
            $input_code = $input_text;
            $input_name = null;
        } else {
            $input_code = null;
            $input_name = $input_text;
        }

        // 初期化
        $output_code = null;
        $output_id = null;
        $output_name = null;

        if (!empty($input_text)) {

            // 製品DB取得
            // 製品一覧を取得
            $originAreaList = DB::table('origin_areas AS OriginArea')
            ->select(
                'OriginArea.id    AS id',
                'OriginArea.name  AS name'
            )
            ->if(!empty($input_code), function ($query) use ($input_code) {
                return $query->where('OriginArea.id', '=', $input_code);
            })
            ->if(!empty($input_name), function ($query) use ($input_name) {
                return $query->where('OriginArea.name', 'like', $input_name);
            })
            ->first();

            if (!empty($originAreaList)) {
                $output_code = $originAreaList->id;
                $output_id   = $originAreaList->id;
                $output_name = $originAreaList->name;
            }
        }

        $returnArray = array($output_code, $output_id, $output_name);

        return json_encode($returnArray);
    }

    /**
     * 担当者ID更新時のAjax処理
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function AjaxAutoCompleteStaff(Request $request)
    {
        // 入力された値を取得
        $input_text = str_replace(' ','', $request->inputText);

        // 入力候補を初期化
        $auto_complete_array = array();

        if (!empty($input_text)) {

            // 製品DB取得
            $staffList = DB::table('staffs AS Staff')
            ->selectRaw('CONCAT(Staff.name_sei," ",Staff.name_mei) AS name')
            ->where([
                    ['Staff.active', '=', '1'],
            ])->where(function($query) use ($input_text){
                $query
                ->orWhereRaw('CONCAT(Staff.name_sei,Staff.name_mei) like "%'.$input_text.'%"')
                ->orWhereRaw('CONCAT(Staff.yomi_sei,Staff.yomi_mei) like "%'.$input_text.'%"');
            })
            ->get();

            if (!empty($staffList)) {

                foreach ($staffList as $staff_val) {

                    array_push($auto_complete_array, $staff_val->name);
                }
            }
        }

        return json_encode($auto_complete_array);
    }

    /**
     * 担当者ID更新時のAjax処理
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function AjaxSetStaff(Request $request)
    {
        // 入力された値を取得
        $input_text = str_replace(' ','', $request->inputText);

        // すべて数字かどうかチェック
        if (is_numeric($input_text)) {
            $input_code = $input_text;
            $input_name = null;
        } else {
            $input_code = null;
            $input_name = $input_text;
        }

        // 初期化
        $output_code = null;
        $output_id = null;
        $output_name = null;

        if (!empty($input_text)) {

            // 担当者TBL取得
            $staffList = DB::table('staffs AS Staff')
            ->select(
                'Staff.code  AS code',
                'Staff.id    AS id'
            )
            ->selectRaw('CONCAT(Staff.name_sei," ",Staff.name_mei) AS name')
            ->if(!empty($input_code), function ($query) use ($input_code) {
                return $query->where('Staff.code', '=', $input_code);
            })
            ->if(!empty($input_name), function ($query) use ($input_name) {
                return $query->whereRaw('CONCAT(Staff.name_sei,Staff.name_mei) like "'.$input_name.'"');
            })
            ->first();

            if (!empty($staffList)) {
                $output_code = $staffList->code;
                $output_id   = $staffList->id;
                $output_name = $staffList->name;
            }
        }

        $returnArray = array($output_code, $output_id, $output_name);

        return json_encode($returnArray);
    }

    /**
     * 配送ID更新時のAjax処理
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function AjaxAutoCompleteDelivery(Request $request)
    {
        // 入力された値を取得
        $input_text = $request->inputText;

        // 入力候補を初期化
        $auto_complete_array = array();

        if (!empty($input_text)) {

            // 製品DB取得
            $deliveryList = DB::table('deliverys AS Delivery')
            ->select(
                'Delivery.name  AS name'
            )->where([
                    ['Delivery.active', '=', '1'],
            ])->where(function($query) use ($input_text){
                $query
                ->orWhere('Delivery.name', 'like', "%{$input_text}%")
                ->orWhere('Delivery.yomi', 'like', "%{$input_text}%");
            })
            ->get();

            if (!empty($deliveryList)) {

                foreach ($deliveryList as $delivery_val) {

                    array_push($auto_complete_array, $delivery_val->name);
                }
            }
        }

        return json_encode($auto_complete_array);
    }

    /**
     * 配送ID更新時のAjax処理
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function AjaxSetDelivery(Request $request)
    {
        // 入力された値を取得
        $input_text = str_replace(' ','', $request->inputText);

        // すべて数字かどうかチェック
        if (is_numeric($input_text)) {
            $input_code = $input_text;
            $input_name = null;
        } else {
            $input_code = null;
            $input_name = $input_text;
        }

        // 初期化
        $output_code = null;
        $output_id = null;
        $output_name = null;

        if (!empty($input_text)) {

            // 製品 DB取得
            $deliveryList = DB::table('deliverys AS Delivery')
            ->select(
                'Delivery.id    AS id',
                'Delivery.code  AS code',
                'Delivery.name  AS name'
            )
            ->if(!empty($input_code), function ($query) use ($input_code) {
                return $query->where('Delivery.code', '=', $input_code);
            })
            ->if(!empty($input_name), function ($query) use ($input_name) {
                return $query->where('Delivery.name', 'like', $input_name);
            })
            ->first();

            if (!empty($deliveryList)) {
                $output_code = $deliveryList->code;
                $output_id   = $deliveryList->id;
                $output_name = $deliveryList->name;
            }
        }

        $returnArray = array($output_code, $output_id, $output_name);

        return json_encode($returnArray);
    }

    /**
     * 売上伝票の登録処理
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function registerSaleSlips(Request $request)
    {

        $error_message = "";

        DB::connection()->beginTransaction();

        try{

            // ユーザー情報の取得
            $user_info    = \Auth::user();
            $user_info_id = $user_info['id'];

            $SaleSlipData = $request->data['SaleSlip'];
            $SaleSlipDetailData = $request->data['SaleSlipDetail'];

            //-----------------------
            // SaleSlipモデル 新規作成
            //-----------------------
            $SaleSlipData['user_id'] = $user_info_id;
            $SaleSlip = $this->SaleSlip->insertSaleSlip($SaleSlipData);

            $sort = 0;
            $staffId = '';

            foreach ($SaleSlipDetailData as $SaleSlipDetailKey => $SaleSlipDetailVal) {

                //-----------------------
                // SaleSlipDetailモデル 新規作成
                //-----------------------
                $SaleSlipDetailVal['sale_slip_id'] = $SaleSlip->id;
                $SaleSlipDetailVal['user_id']      = $user_info_id;
                $SaleSlipDetailVal['sort']         = $sort;
                //$SaleSlipDetail = $this->SaleSlipDetail->insertSaleSlipDetail($SaleSlipDetailVal);

                // 値がNULLのところを初期化
                if (empty($SaleSlipDetailVal['standard_id'])) $SaleSlipDetailVal['standard_id'] = 0;
                if (empty($SaleSlipDetailVal['quality_id'])) $SaleSlipDetailVal['quality_id'] = 0;
                if (isset($SaleSlipDetailVal['supply_count']) || empty($SaleSlipDetailVal['supply_count'])) $SaleSlipDetailVal['supply_count'] = 0;
                if (isset($SaleSlipDetailVal['supply_unit_num']) || empty($SaleSlipDetailVal['supply_unit_num'])) $SaleSlipDetailVal['supply_unit_num'] = 0;
                if (isset($SaleSlipDetailVal['sort']) || empty($SaleSlipDetailVal['sort'])) $SaleSlipDetailVal['sort'] = 0;

                // sale_slip_detailsを登録する
                $SaleSlipDetail                     = new SaleSlipDetail;
                $SaleSlipDetail->sale_slip_id       = $SaleSlipDetailVal['sale_slip_id'];
                $SaleSlipDetail->product_id         = $SaleSlipDetailVal['product_id'];
                $SaleSlipDetail->standard_id        = $SaleSlipDetailVal['standard_id'];
                $SaleSlipDetail->quality_id         = $SaleSlipDetailVal['quality_id'];
                $SaleSlipDetail->unit_price         = $SaleSlipDetailVal['unit_price'];
                $SaleSlipDetail->unit_num           = $SaleSlipDetailVal['unit_num'];
                $SaleSlipDetail->notax_price        = $SaleSlipDetailVal['notax_price'];
                $SaleSlipDetail->origin_area_id     = $SaleSlipDetailVal['origin_area_id'];
                $SaleSlipDetail->unit_id            = $SaleSlipDetailVal['unit_id'];
                $SaleSlipDetail->inventory_unit_id  = $SaleSlipDetailVal['inventory_unit_id'];
                $SaleSlipDetail->inventory_unit_num = $SaleSlipDetailVal['inventory_unit_num'];
                $SaleSlipDetail->staff_id           = $SaleSlipDetailVal['staff_id'];
                $SaleSlipDetail->memo               = $SaleSlipDetailVal['memo'];
                $SaleSlipDetail->supply_count       = $SaleSlipDetailVal['supply_count'];
                $SaleSlipDetail->supply_unit_num    = $SaleSlipDetailVal['supply_unit_num'];
                $SaleSlipDetail->sort               = $sort;
                $SaleSlipDetail->created_user_id    = $SaleSlipDetailVal['user_id'];
                $SaleSlipDetail->created            = Carbon::now();
                $SaleSlipDetail->created_user_id    = $SaleSlipDetailVal['user_id'];
                $SaleSlipDetail->modified           = Carbon::now();
                $SaleSlipDetail->save();

                $sort++;

                // 作成したIDを取得する
                $sale_slip_detail_new_id = $SaleSlipDetail->id;

                // 在庫管理配列を取得
                if (isset($request->data['InventoryManage'][$SaleSlipDetailKey])) {

                    // 在庫管理配列を取得
                    $inventoryManageData = $request->data['InventoryManage'][$SaleSlipDetailKey];

                    // inventory_managesを登録する
                    $inventoryManage = array();

                    foreach ($inventoryManageData['supply_slip_id'] as $supply_slip_key => $supply_slip_val) {

                        // 利用仕入数取得
                        $unit_num = floatval($inventoryManageData['use_num'][$supply_slip_key]);

                        // 在庫管理テーブルの配列作成
                        $inventoryManage[] = [
                            "sale_detail_slip_id"       => $sale_slip_detail_new_id,
                            "supply_detail_slip_id"     => $supply_slip_val,
                            "inventory_type"            => 2,
                            "unit_num"                  => $unit_num,
                            "sort"                      => $supply_slip_key,
                            "created_user_id"           => $user_info_id,
                            "created"                   => Carbon::now(),
                            "modified_user_id"          => $user_info_id,
                            "modified"                  => Carbon::now()
                        ];

                        // 対象の仕入伝票を取得
                        $SupplySlipDetailList = DB::table('supply_slip_details AS SupplySlipDetail')
                        ->select(
                            'SupplySlipDetail.id           AS supply_slip_detail_id',
                            'SupplySlipDetail.unit_num     AS unit_num',
                            'SupplySlipDetail.consumption  AS consumption'
                        )
                        ->where([
                                ['SupplySlipDetail.id', '=', $supply_slip_val],
                                ['SupplySlipDetail.active', '=', 1],
                        ])->first();

                        // もし伝票が存在しない場合はエラーを飛ばす
                        if (empty($SupplySlipDetailList)) {
                            throw new \Exception("存在しない仕入伝票が選択されています。");
                        }

                        $sale_slip_detail_unit_num    = floatval($SupplySlipDetailList->unit_num);
                        $sale_slip_detail_consumption = floatval($SupplySlipDetailList->consumption);
                        $sale_slip_detail_remain      = $sale_slip_detail_unit_num - $sale_slip_detail_consumption;

                        if ($sale_slip_detail_remain < $unit_num) {
                            throw new \Exception("在庫数より多くの仕入数が入ってきています。");
                        }

                        // 今回の利用数を含めて登録
                        $sale_slip_detail_unit_num_new = $sale_slip_detail_consumption + $unit_num;

                        // 仕入伝票登録を登録する
                        $SupplySlipDetail = \App\SupplySlipDetail::find($SupplySlipDetailList->supply_slip_detail_id);
                        $SupplySlipDetail->consumption        = $sale_slip_detail_unit_num_new;
                        $SupplySlipDetail->modified_user_id   = $user_info_id;
                        $SupplySlipDetail->modified           = Carbon::now();
                        $SupplySlipDetail->save();
                    }

                    if (!empty($inventoryManage)) {

                        DB::table('inventory_manages')->insert($inventoryManage);
                    }
                }

                if (empty($staffId)) $staffId = $SaleSlipDetailVal['staff_id'];
            }

            DB::connection()->commit();

            // 登録のタイプが請求書印刷の場合(sale_submit_type:4)
            if ($SaleSlipData['sale_submit_type'] == 4) {
                // 請求登録をして請求一覧画面に遷移させる
                $this->registerSaleDeposit($SaleSlip->id, $staffId);

                return redirect('./DepositIndex');
            }

        } catch (\Exception $e) {

            DB::rollback();
            dd($e);
        }

        return redirect('./SaleSlipCreate');
    }

    /**
     * 伝票新規追加　登録
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function AjaxAddSlip(Request $request)
    {
        // 伝票NOを取得
        $slip_num = $request->slip_num;
        if(empty($slip_num)) $slip_num = 1;

        $tabInitialNum = intval(7*$slip_num + 2);

        // 追加伝票形成
        $ajaxHtml1 = '';
        $ajaxHtml1 .= "<tr id='slip-partition-".$slip_num."' class='partition-area'>";
        $ajaxHtml1 .= "</tr>";
        $ajaxHtml1 .= "<input type='hidden' name='sort' id='sort-".$slip_num."' value='".$slip_num."'>";
        $ajaxHtml1 .= "<input type='hidden' name='data[SaleSlipDetail][".$slip_num."][id]' id='id-".$slip_num."' value=''>";
        $ajaxHtml1 .= '<tr id="slip-upper-' . $slip_num . '">';
        $ajaxHtml1 .= '    <td class="index-td" rowspan="2">' . $slip_num . '</td>';
        $ajaxHtml1 .= '    <td colspan="2" id="product-code-area-' . $slip_num . '">';
        $ajaxHtml1 .= '        <input type="hidden" id="product_id_' . $slip_num . '" name="data[SaleSlipDetail][' . $slip_num . '][product_id]">';
        $ajaxHtml1 .= '        <input type="hidden" id="tax_id_' . $slip_num . '" name="data[SaleSlipDetail][' . $slip_num . '][tax_id]" value="' . $slip_num . '">';
        $ajaxHtml1 .= '    </td>';
        $ajaxHtml1 .= '    <td>';
        $ajaxHtml1 .= '        <input type="number" class="form-control" id="inventory_unit_num_' . $slip_num . '" name="data[SaleSlipDetail][' . $slip_num . '][inventory_unit_num]" tabindex="' . ($tabInitialNum + 2) . '">';
        $ajaxHtml1 .= '   </td>';
        $ajaxHtml1 .= '   <td>';
        $ajaxHtml1 .= '        <input type="number" class="form-control" id="unit_num_' . $slip_num . '" name="data[SaleSlipDetail][' . $slip_num . '][unit_num]" onchange="javascript:priceNumChange(' . $slip_num . ')" tabindex="' . ($tabInitialNum + 3) . '">';
        $ajaxHtml1 .= '   </td>';
        $ajaxHtml1 .= '   <td colspan="2">';
        $ajaxHtml1 .= '        <input type="number" class="form-control" id="unit_price_' . $slip_num . '" name="data[SaleSlipDetail][' . $slip_num . '][unit_price]" onchange="javascript:priceNumChange(' . $slip_num . ')" tabindex="' . ($tabInitialNum + 4) . '">';
        $ajaxHtml1 .= '   </td>';
        $ajaxHtml1 .= '    <td colspan="2" id="origin-code-area-' . $slip_num . '">';
        $ajaxHtml1 .= '        <input type="hidden" id="origin_area_id_' . $slip_num . '" name="data[SaleSlipDetail][' . $slip_num . '][origin_area_id]">';
        $ajaxHtml1 .= '    </td>';
        $ajaxHtml1 .= '   <td id="staff-code-area-'.$slip_num.'">';
        $ajaxHtml1 .= '        <input type="hidden" id="staff_id_' . $slip_num . '" name="data[SaleSlipDetail][' . $slip_num . '][staff_id]" value="9">';
        $ajaxHtml1 .= '   </td>';
        $ajaxHtml1 .= '   <td colspan="2">';
        $ajaxHtml1 .= '        <input type="text" class="form-control" id="staff_text_' . $slip_num . '" name="data[SaleSlipDetail][' . $slip_num . '][staff_text]" placeholder="担当欄" value="石塚 貞雄" readonly>';
        $ajaxHtml1 .= '   </td>';
        $ajaxHtml1 .= '   <td rowspan="2">';
        $ajaxHtml1 .= '        <button id="remove-slip-btn" type="button" class="btn rmv-slip-btn btn-secondary" onclick="javascript:removeSlip(' . $slip_num . ')">削除</button>';
        $ajaxHtml1 .= '   </td>';
        $ajaxHtml1 .= '</tr>';
        $ajaxHtml1 .= '<tr id="slip-lower-' . $slip_num . '">';
        $ajaxHtml1 .= '    <td colspan="2">';
        $ajaxHtml1 .= '        <input type="text" class="form-control" id="product_text_' . $slip_num . '" name="data[SaleSlipDetail][' . $slip_num . '][product_text]" placeholder="製品欄" readonly>';
        $ajaxHtml1 .= '    </td>';
        $ajaxHtml1 .= '    <td>';
        $ajaxHtml1 .= '        <input type="text" class="form-control" id="inventory_unit_text_' . $slip_num . '" name="data[SaleSlipDetail][' . $slip_num . '][inventory_unit_text]" placeholder="個数欄" readonly>';
        $ajaxHtml1 .= '        <input type="hidden" id="inventory_unit_id_' . $slip_num . '" name="data[SaleSlipDetail][' . $slip_num . '][inventory_unit_id]" value="' . $slip_num . '">';
        $ajaxHtml1 .= '    </td>';
        $ajaxHtml1 .= '    <td>';
        $ajaxHtml1 .= '        <input type="text" class="form-control" id="unit_text_' . $slip_num . '" name="data[SaleSlipDetail][' . $slip_num . '][unit_text]" placeholder="数量欄" readonly>';
        $ajaxHtml1 .= '        <input type="hidden" id="unit_id_' . $slip_num . '" name="data[SaleSlipDetail][' . $slip_num . '][unit_id]" value="' . $slip_num . '">';
        $ajaxHtml1 .= '    </td>';
        $ajaxHtml1 .= '    <td colspan="2">';
        $ajaxHtml1 .= '        <input type="text" class="form-control" id="notax_price_' . $slip_num . '" name="data[SaleSlipDetail][' . $slip_num . '][notax_price]" value="' . $slip_num . '" readonly>';
        $ajaxHtml1 .= '    </td>';
        $ajaxHtml1 .= '    <td colspan="2">';
        $ajaxHtml1 .= '        <input type="text" class="form-control" id="origin_area_text_' . $slip_num . '" name="data[SaleSlipDetail][' . $slip_num . '][origin_area_text]" placeholder="産地欄" readonly>';
        $ajaxHtml1 .= '    </td>';
        $ajaxHtml1 .= '    <td colspan="3">';
        $ajaxHtml1 .= '        <input type="text" class="form-control" id="memo_' . $slip_num . '" name="data[SaleSlipDetail][' . $slip_num . '][memo]" placeholder="摘要欄" tabindex="' . ($tabInitialNum + 7) . '">';
        $ajaxHtml1 .= '    </td>';
        $ajaxHtml1 .= '</tr>';


        // 仕入伝票リスト格納エリア
        $ajaxHtml2 = " <div id='slip-slip-area-".$slip_num."'></div>";

        //-------------------------------
        // AutoCompleteの要素は別で形成する
        //-------------------------------
        // 製品ID
        $autoCompleteProduct = '<input type="text" class="form-control product_code_input" id="product_code_' . $slip_num . '" name="data[SaleSlipDetail][' . $slip_num . '][product_code]" tabindex="' . ($tabInitialNum + 1) . '">';
        // 産地
        $autoCompleteOrigin  = '<input type="text" class="form-control origin_area_code_input" id="origin_area_code_' . $slip_num . '" name="data[SaleSlipDetail][' . $slip_num . '][origin_area_code]" tabindex="' . ($tabInitialNum + 5) . '">';
        // 担当
        $autoCompleteStaff   = '<input type="text" class="form-control staff_code_input" id="staff_code_' . $slip_num . '" name="data[SaleSlipDetail][' . $slip_num . '][staff_code]" value="1009" tabindex="' . ($tabInitialNum + 6) . '">';

        $slip_num = intval($slip_num) + 1;

        $returnArray = array($slip_num, $ajaxHtml1, $ajaxHtml2, $autoCompleteProduct, $autoCompleteOrigin, $autoCompleteStaff);


        return $returnArray;
    }

    /**
     * 売上対象製品取得
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function AjaxShowSupplySlip(Request $request)
    {

        // 初期化
        $ajaxHtml = "";

        // 製品IDを取得
        $product_id = $request->product_id;
        if(empty($product_id)) {

            $returnArray = array($ajaxHtml);
            return $returnArray;
        }

        // use_num_arr取得
        if (!empty($request->use_num_arr)) {
            $use_num_arr = explode(",", $request->use_num_arr);
        } else {
            $use_num_arr = null;
        }

        // supply_slip_id_arr取得
        if (!empty($request->supply_slip_id_arr)) {
            $supply_slip_id_arr = explode(",", $request->supply_slip_id_arr);
        } else {
            $supply_slip_id_arr = null;
        }

        // sale_slip_id取得
        if (isset($request->sale_slip_id) && !empty($request->sale_slip_id)) {
            $sale_slip_id = $request->sale_slip_id;
        } else {
            $sale_slip_id = null;
        }

        try {
            // 新規作成か編集かでwhere句の条件を分岐させる
            $action = $request->action;
            if ($action == 'create') {
                $where = 'CAST(SupplySlipDetail.unit_num AS SIGNED) > CAST(SupplySlipDetail.consumption AS SIGNED)';
            } else {
                $where = 'CAST(SupplySlipDetail.unit_num AS SIGNED) > (CAST(SupplySlipDetail.consumption AS SIGNED) - CAST(InventoryManage.unit_num AS SIGNED))';
            }

            // inventory_managesのサブクエリを作成
            $inventory_sub_query = null;
            if ($action == 'edit') {
                $inventory_sub_query = DB::table('inventory_manages as SubInventoryManage')
                ->select(
                    'SubInventoryManage.supply_detail_slip_id AS supply_detail_slip_id'
                )
                ->selectRaw('sum(SubInventoryManage.unit_num) AS unit_num')
                ->leftJoin('supply_slip_details as SubSupplySlipDetail', function ($join) {
                    $join->on('SubSupplySlipDetail.id', '=', 'SubInventoryManage.supply_detail_slip_id');
                })
                ->groupBy('supply_detail_slip_id');
            }

            // 製品名取得
            $productList = DB::table('products AS Product')
            ->select(
                'Product.name           AS product_name',
                'Unit.name              AS unit_name'
            )->join('units AS Unit', function ($join) {
                $join->on('Unit.id', '=', 'Product.unit_id');
            })->where([
                ['Product.id', '=', $product_id],
                ['Product.active', '=', 1],
            ])->first();

            // 売上対象の製品をDBから取得
            $supplySlipDetailList = DB::table('supply_slip_details AS SupplySlipDetail')
            ->select(
                'SupplySlipDetail.id           AS supply_slip_id',
                'SupplyCompany.name            AS supply_company_name',
                'SupplyShop.name               AS supply_shop_name',
                'Product.name                  AS product_name',
                'Standard.name                 AS standard_name',
                'Quality.name                  AS quality_name',
                'SupplySlipDetail.unit_price   AS unit_price',
                'SupplySlipDetail.unit_num     AS unit_num',
                'Unit.name                     AS unit_name',
                'SupplySlipDetail.notax_price  AS notax_price',
                'OriginArea.name               AS originArea_name'
            )
            ->selectRaw('DATE_FORMAT(SupplySlip.date, "%m/%d") AS supply_slip_supply_date')
            ->selectRaw('(CAST(SupplySlipDetail.unit_num AS SIGNED) - CAST(SupplySlipDetail.consumption AS SIGNED)) AS remain_num')
            ->selectRaw('CONCAT(Staff.name_sei," ",Staff.name_mei) AS staff_name')
            ->join('products AS Product', function ($join) {
                $join->on('Product.id', '=', 'SupplySlipDetail.product_id');
            })
            ->leftJoin('standards AS Standard', function ($join) {
                $join->on('Standard.id', '=', 'SupplySlipDetail.standard_id');
            })
            ->leftJoin('qualities AS Quality', function ($join) {
                $join->on('Quality.id', '=', 'SupplySlipDetail.quality_id');
            })
            ->join('units AS Unit', function ($join) {
                $join->on('Unit.id', '=', 'SupplySlipDetail.unit_id');
            })
            ->leftJoin('origin_areas AS OriginArea', function ($join) {
                $join->on('OriginArea.id', '=', 'SupplySlipDetail.origin_area_id');
            })
            ->join('staffs AS Staff', function ($join) {
                $join->on('Staff.id', '=', 'SupplySlipDetail.staff_id');
            })
            ->join('supply_slips AS SupplySlip', function ($join) {
                $join->on('SupplySlip.id', '=', 'SupplySlipDetail.supply_slip_id');
            })
            ->join('supply_companies AS SupplyCompany', function ($join) {
                $join->on('SupplyCompany.id', '=', 'SupplySlip.supply_company_id');
            })
            ->leftJoin('supply_shops AS SupplyShop', function ($join) {
                $join->on('SupplyShop.id', '=', 'SupplySlip.supply_shop_id');
            })
            ->when($action == 'edit', function($query) use ($inventory_sub_query) {
                $query
                ->leftJoin(DB::raw('('. $inventory_sub_query->toSql() .') as InventoryManage'), 'InventoryManage.supply_detail_slip_id', '=', 'SupplySlipDetail.id');
            })
            ->where([
                ['SupplySlipDetail.product_id', '=', $product_id],
                ['SupplySlipDetail.unit_num', '>', 0],
                ['SupplySlipDetail.active', '=', 1],
            ])
            ->whereRaw($where)
            ->orderBy('SupplySlip.date', 'asc')->get();

            // HTMLを形成
            $ajaxHtml .= "<div class='product-area' id='modal_product_area'>".$productList->product_name."</div>";
            $ajaxHtml .= "<table class='modal-table' id='modal_table_area'>";
            $ajaxHtml .= "  <tr>";
            $ajaxHtml .= "    <th>選択</th>";
            $ajaxHtml .= "    <th>利用数</th>";
            $ajaxHtml .= "    <th>仕入日</th>";
            $ajaxHtml .= "    <th>企業名</th>";
            $ajaxHtml .= "    <th>店舗名</th>";
            $ajaxHtml .= "    <th>規格</th>";
            $ajaxHtml .= "    <th>品質</th>";
            $ajaxHtml .= "    <th>単価</th>";
            $ajaxHtml .= "    <th>在庫数</th>";
            $ajaxHtml .= "    <th>産地</th>";
            $ajaxHtml .= "    <th>スタッフ</th>";
            $ajaxHtml .= "  </tr>";

            // 初期化処理
            $use_num_count = 0; // 件数
            $use_num_total = 0; // 単位合計

            if (!empty($supplySlipDetailList)) {

                foreach ($supplySlipDetailList as $supplySlipDetails) {

                    $detail_id = $supplySlipDetails->supply_slip_id;
                    $checked   = "";
                    $remain_num = $supplySlipDetails->remain_num;

                    if (!empty($supply_slip_id_arr)) {

                        foreach ($supply_slip_id_arr as $key => $supply_slip_id_val) {

                            if ($detail_id == $supply_slip_id_val) {
                                $checked   = "checked";
                                $remain_num = $use_num_arr[$key];
                                $use_num_count += 1;
                                $use_num_total += intval($remain_num);
                            }
                        }
                    }

                    $ajaxHtml .= "  <tr>";
                    $ajaxHtml .= "    <td><input type='checkbox' class='modal-checkbox' id='checkbox_".$detail_id."' $checked></td>";
                    $ajaxHtml .= "    <td><input type='tel' class='modal-sale-num' id='use_num_".$detail_id."' value='".$remain_num."'></td>";
                    $ajaxHtml .= "    <td><div id='date_".$detail_id."'>".$supplySlipDetails->supply_slip_supply_date."</div></td>";
                    $ajaxHtml .= "    <td><div id='company_".$detail_id."'>".$supplySlipDetails->supply_company_name."</div></td>";
                    $ajaxHtml .= "    <td><div id='shop_".$detail_id."'>".$supplySlipDetails->supply_shop_name."</div></td>";
                    $ajaxHtml .= "    <td><div id='standard_".$detail_id."'>".$supplySlipDetails->standard_name."</div></td>";
                    $ajaxHtml .= "    <td><div id='quality_".$detail_id."'>".$supplySlipDetails->quality_name."</div></td>";
                    $ajaxHtml .= "    <td><div id='unit_price_".$detail_id."'>".$supplySlipDetails->unit_price."</div></td>";
                    $ajaxHtml .= "    <td>";
                    $ajaxHtml .= "        <div id='remain_num_unit_".$detail_id."'>".$supplySlipDetails->remain_num.$supplySlipDetails->unit_name."</div>";
                    $ajaxHtml .= "        <input type='hidden' id='remain_num_".$detail_id."' value='".$supplySlipDetails->remain_num."'>";
                    $ajaxHtml .= "    </td>";
                    $ajaxHtml .= "    <td><div id='origin_area_".$detail_id."'>".$supplySlipDetails->originArea_name."</div></td>";
                    $ajaxHtml .= "    <td><div id='staff_".$detail_id."'>".$supplySlipDetails->staff_name."</div></td>";
                    $ajaxHtml .= "  </tr>";
                }
            }

            $ajaxHtml .= "</table>";
            $ajaxHtml .= "<div id='sum-area'>";
            $ajaxHtml .= "    <div id='sum_count_area'>".$use_num_count."</div>";
            $ajaxHtml .= "    <div id='sum_count_name_area'>件</div>";
            $ajaxHtml .= "    <div id='sum_unit_num_area'>".$use_num_total."</div>";
            $ajaxHtml .= "    <div id='sum_unit_name_area'>".$productList->unit_name."</div>";
            $ajaxHtml .= "    <input type='hidden' id='sale_slip_no' value=''>";
            $ajaxHtml .= "<div>";

            $returnArray = array($ajaxHtml);

        } catch (\Exception $e) {

            DB::rollback();

            dd($e);
        }

        return $returnArray;
    }

    /**
     * 売上発注単価の取得
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getOrderSaleUnitPrice(Request $request) {

        // パラメータの取得
        $companyId = $request->company_id;
        $productId = $request->product_id;
        $saleDate = $request->sale_date;

        // データの取得
        $orderSaleUnitPriceDetails = DB::table('order_sale_unit_price_details AS OrderSaleUnitPriceDetails')
        ->select(
            'OrderSaleUnitPriceDetails.notax_price AS notax_price'
        )
        ->join('order_sale_unit_prices AS OrderSaleUnitPrice', function ($join) {
            $join->on('OrderSaleUnitPriceDetails.order_sale_unit_price_id', '=', 'OrderSaleUnitPrice.id');
        })
        ->where([
            ['OrderSaleUnitPriceDetails.apply_from', '<=', $saleDate],
            ['OrderSaleUnitPriceDetails.product_id', '=', $productId],
            ['OrderSaleUnitPrice.company_id', '=', $companyId],
            ['OrderSaleUnitPriceDetails.active', '=', '1'],
            ['OrderSaleUnitPrice.active', '=', '1'],
        ])
        ->orderBy('OrderSaleUnitPriceDetails.apply_from', 'desc')
        ->limit(1)
        ->get();

        $orderSaleUnitPirce = 0;
        if (isset($orderSaleUnitPriceDetails[0]->notax_price))
            $orderSaleUnitPirce = $orderSaleUnitPriceDetails[0]->notax_price;

        return $orderSaleUnitPirce;

    }

    /**
     * 売上登録、請求登録して請求書印刷する
     *
     * @param int $saleSlipId
     * @param int $staffId
     */
    private function registerSaleDeposit($saleSlipId, $staffId) {

        // ユーザー情報の取得
        $userInfo   = \Auth::user();
        $userInfoId = $userInfo['id'];

        // トランザクション開始
        DB::connection()->beginTransaction();

        try {
            // --------------------------
            // パラメータから売上データを取得
            // --------------------------
            $saleSlipLists = DB::table('sale_slips AS SaleSlip')
            ->select(
                'SaleSlip.date               AS sale_date',
                'SaleSlip.sale_company_id    AS company_id',
              //  'SaleSlip.sale_shop_id       AS shop_id',
                'SaleSlip.notax_sub_total_8  AS notax_subtotal_8',
                'SaleSlip.notax_sub_total_10 AS notax_subtotal_10',
                'SaleSlip.notax_sub_total    AS notax_subtotal',
                'SaleSlip.tax_total_8        AS tax_total_8',
                'SaleSlip.tax_total_10       AS tax_total_10',
                'SaleSlip.tax_total          AS tax_total',
                'SaleSlip.sub_total_8        AS subtotal_8',
                'SaleSlip.sub_total_10       AS subtotal_10',
                'SaleSlip.sub_total          AS subtotal',
                'SaleSlip.delivery_price     AS delivery_price',
                'SaleSlip.adjust_price       AS adjust_price',
                'SaleSlip.total              AS total'
            )->where([
                    ['SaleSlip.id', '=', $saleSlipId]
            ])
            ->get();

            $saleSlipDatas = $saleSlipLists[0];

            // deposit_idが存在するか確認
            $depositList = DB::table('deposit_withdrawal_details AS DepositWithdrawalDetail')
            ->select(
                'DepositWithdrawalDetail.deposit_withdrawal_id AS deposit_id'
            )
            ->join('deposits as Deposit', function ($join) {
                $join->on('DepositWithdrawalDetail.deposit_withdrawal_id', '=', 'Deposit.id');
            })
            ->where([
                ['DepositWithdrawalDetail.supply_sale_slip_id', '=', $saleSlipId],
                ['DepositWithdrawalDetail.type', '=', '2'],
                ['DepositWithdrawalDetail.active', '=', '1'],
                ['Deposit.active', '=', '1']
            ])
            ->get();

            if (isset($depositList[0]->deposit_id)) {
                // 入出金詳細テーブル(deposit_withdrawal_details)に存在したら更新
                $depositId = $depositList[0]->deposit_id;

                // -------------------
                // 入出金詳細テーブル更新
                // -------------------
                $updateDetailParams = array(
                    'deposit_withdrawal_date' => $saleSlipDatas->sale_date,
                    'supply_sale_slip_date'   => $saleSlipDatas->sale_date,
                    'notax_sub_total_8'       => $saleSlipDatas->notax_subtotal_8,
                    'notax_sub_total_10'      => $saleSlipDatas->notax_subtotal_10,
                    'sub_total'               => $saleSlipDatas->subtotal,
                    'delivery_price'          => $saleSlipDatas->delivery_price,
                    'adjust_price'            => $saleSlipDatas->adjust_price,
                    'total'                   => $saleSlipDatas->total,
                    'modified_user_id'        => $userInfoId,
                    'modified'                => Carbon::now(),
                );
                // 更新処理
                DB::table('deposit_withdrawal_details')
                ->where([
                    ['deposit_withdrawal_id', '=', $depositId],
                    ['supply_sale_slip_id', '=', $saleSlipId],
                    ['type', '=', '2'],
                    ['active', '=', '1']
                ])
                ->update($updateDetailParams);

                // ------------------------
                // 詳細テーブルからデータを取得
                // ------------------------
                $depositDetailList = DB::table('deposit_withdrawal_details AS DepositWithdrawalDetail')
                ->select(
                    'DepositWithdrawalDetail.sub_total    AS subtotal',
                    'DepositWithdrawalDetail.adjust_price AS adjust_price',
                    'DepositWithdrawalDetail.total        AS total'
                )
                ->where([
                    ['deposit_withdrawal_id', '=', $depositId],
                    ['type', '=', '2'],
                    ['active', '=', '1']
                ])
                ->get();

                // ---------------------
                // 取得してきたデータを計算
                // ---------------------
                // 初期化
                $subtotal    = 0;
                $adjustPrice = 0;
                $total       = 0;
                foreach ($depositDetailList as $depositDetailDatas) {
                    $subtotal    += $depositDetailDatas->subtotal;
                    $adjustPrice += $depositDetailDatas->adjust_price;
                    $total       += $depositDetailDatas->total;
                }

                // ---------------------
                // 入金テーブルのデータ更新
                // ---------------------
                $updateParams = array(
                    'sale_company_id'     => $saleSlipDatas->company_id,
                  //  'sale_shop_id'        => $saleSlipDatas->shop_id,
                    'date'                => $saleSlipDatas->sale_date,
                    'staff_id'            => $staffId,
                    'sub_total'           => $subtotal,
                    'adjustment_amount'   => $adjustPrice,
                    'amount'              => $total,
                    'deposit_method_id'   => 6,
                    'deposit_submit_type' => 0,
                    'modified_user_id'    => $userInfoId,
                    'modified'            => Carbon::now()
                );

                // 更新処理
                DB::table('deposits')
                ->where('id', '=', $depositId)
                ->update($updateParams);

                $sessionDepositId = $depositId;

            } else {

                // 入出金詳細テーブル(deposit_withdrawal_details)に存在しなかったら新規登録
                // ----------------
                // 入金テーブルに登録
                // ----------------
                // 配列に値を格納
                $insertDepositColumns = array(
                    'sale_company_id'     => $saleSlipDatas->company_id,
                  //  'sale_shop_id'        => $saleSlipDatas->shop_id,
                    'date'                => $saleSlipDatas->sale_date,
                    'sale_from_date'      => $saleSlipDatas->sale_date,
                    'sale_to_date'        => $saleSlipDatas->sale_date,
                    'payment_date'        => $saleSlipDatas->sale_date,
                    'staff_id'            => $staffId,
                    'sub_total'           => $saleSlipDatas->subtotal,
                    'adjustment_amount'   => $saleSlipDatas->adjust_price,
                    'amount'              => $saleSlipDatas->total,
                    'deposit_method_id'   => 6,
                    'deposit_submit_type' => 0,
                    'active'              => 1,
                    'created_user_id'     => $userInfoId,
                    'created'             => Carbon::now(),
                    'modified_user_id'    => $userInfoId,
                    'modified'            => Carbon::now()
                );

                // データインサート
                $depositNewId = DB::table('deposits')->insertGetId($insertDepositColumns);

                // -----------------
                // 入出金テーブルに登録
                // -----------------
                // 配列に値格納
                $insertDepositDetails = array(
                    'deposit_withdrawal_id'   => $depositNewId,
                    'supply_sale_slip_id'     => $saleSlipId,
                    'deposit_withdrawal_date' => $saleSlipDatas->sale_date,
                    'supply_sale_slip_date'   => $saleSlipDatas->sale_date,
                    'type'                    => 2,
                    'notax_sub_total_8'       => $saleSlipDatas->notax_subtotal_8,
                    'notax_sub_total_10'      => $saleSlipDatas->notax_subtotal_10,
                    'sub_total'               => $saleSlipDatas->subtotal,
                    'delivery_price'          => $saleSlipDatas->delivery_price,
                    'adjust_price'            => $saleSlipDatas->adjust_price,
                    'total'                   => $saleSlipDatas->total,
                    'active'                  => 1,
                    'created_user_id'         => $userInfoId,
                    'created'                 => Carbon::now(),
                    'modified_user_id'        => $userInfoId,
                    'modified'                => Carbon::now(),
                );

                // データインサート
                DB::table('deposit_withdrawal_details')->insert($insertDepositDetails);

                $sessionDepositId = $depositNewId;

            }

            // -----------------------------
            // 売上データのフラグを売上済みにする
            // -----------------------------
            DB::table('sale_slips')
            ->where('id', '=', $saleSlipId)
            ->update(array('sale_flg' => 1));

            // 問題なければコミット
            DB::connection()->commit();

        } catch (\Exception $e) {

            DB::rollback();

            dd($e);

        }

        // セッションにデータ書き込み
        session()->regenerate();
        session(['deposit_condition_id' => $sessionDepositId]);

        return;
    }


    /**
     * 納品書印刷
     */
    public function deliverySlipOutput($sale_slip_id) {

        // activeの変数を格納
        $this_active = 1;

        //------------------
        // 売上伝票詳細取得
        //------------------
        $SaleSlipDetailList = DB::table('sale_slip_details AS SaleSlipDetail')
        ->select(
            'SaleSlip.id                       AS sale_slip_id',
            'SaleSlip.delivery_price           AS delivery_price',
            'SaleSlip.adjust_price             AS adjust_price',
            'SaleSlip.remarks                  AS remarks',
            'SaleSlipDetail.id                 AS sale_slip_detail_id',
            'SaleSlipDetail.unit_price         AS unit_price',
            'SaleSlipDetail.unit_num           AS unit_num',
            'SaleSlipDetail.notax_price        AS notax_price',
            'SaleSlipDetail.seri_no            AS seri_no',
            'SaleSlipDetail.inventory_unit_num AS inventory_unit_num',
            'SaleSlipDetail.memo               AS memo',
            'SaleCompany.id                    AS company_id',
            'SaleCompany.name                  AS company_name',
            'SaleCompany.postal_code           AS company_postal_code',
            'SaleCompany.address               AS company_address',
            'Product.name                      AS product_name',
            'Product.tax_id                    AS tax_id',
            'Unit.name                         AS unit_name',
            'OriginArea.name                   AS origin_name',
        )
        ->selectRaw('DATE_FORMAT(SaleSlip.delivery_date, "%m/%d") AS sale_slip_delivery_date')
        ->join('sale_slips AS SaleSlip', function ($join) {
            $join->on('SaleSlipDetail.sale_slip_id', '=', 'SaleSlip.id');
        })
        ->join('sale_companies AS SaleCompany', function ($join) {
            $join->on('SaleCompany.id', '=', 'SaleSlip.sale_company_id');
        })
        ->join('products as Product', function ($join) {
            $join->on('Product.id', '=', 'SaleSlipDetail.product_id')
                 ->where('Product.active', '=', true);
        })
        ->join('units as Unit', function ($join) {
            $join->on('Unit.id', '=', 'SaleSlipDetail.unit_id')
                 ->where('Unit.active', '=', true);
        })
        ->leftJoin('origin_areas as OriginArea', function ($join) {
            $join->on('OriginArea.id', '=', 'SaleSlipDetail.origin_area_id')
                 ->where('OriginArea.active', '=', true);
        })
        ->where([
            ['SaleSlipDetail.sale_slip_id','=', $sale_slip_id],
            ['SaleSlipDetail.active','=', $this_active]
        ])
        ->get();

        // ------------------------
        // 取得してきたデータを整形する
        // ------------------------

        // 初期化処理
        $calcDepositList = array();
        $notaxSubTotal8Amount = 0;
        $notaxSubTotal10Amount = 0;
        foreach ($SaleSlipDetailList as $SaleSlipDetailDatas) {
            // -------
            // 会社情報
            // -------
            // 企業情報格納
            if (!isset($calcDepositList['company_info'])) {
                $companyId = $SaleSlipDetailDatas->company_id;
                $calcDepositList['company_info']['name']    = $SaleSlipDetailDatas->company_name;
                $calcDepositList['company_info']['address'] = $SaleSlipDetailDatas->company_address;
                // 郵便番号は間にハイフンを入れる
                $calcDepositList['company_info']['code'] = '';
                if (!empty($SaleSlipDetailDatas->company_postal_code)) {
                    $codeBefore = substr($SaleSlipDetailDatas->company_postal_code, 0, 3);
                    $codeAfter  = substr($SaleSlipDetailDatas->company_postal_code, 3, 4);
                    $calcDepositList['company_info']['code'] = '〒' . $codeBefore . '-' . $codeAfter;
                }

                // 納品日
                $calcDepositList['company_info']['sale_slip_delivery_date'] = date('Y年m月d日', strtotime($SaleSlipDetailDatas->sale_slip_delivery_date));

                // 備考情報もここで入れる
                $calcDepositList['company_info']['remarks'] = $SaleSlipDetailDatas->remarks;
            }

            // -------------------
            // 8%, 10%ごとの金額計算
            // -------------------
            // 初期化
            $calcDepositList['detail'][] = array(
                'date'                => $SaleSlipDetailDatas->sale_slip_delivery_date,
                'name'                => $SaleSlipDetailDatas->product_name,
                'origin_name'         => $SaleSlipDetailDatas->origin_name,
                'inventory_unit_num'  => $SaleSlipDetailDatas->inventory_unit_num,
                'unit_price'          => $SaleSlipDetailDatas->unit_price,
                'unit_num'            => $SaleSlipDetailDatas->unit_num,
                'unit_name'           => $SaleSlipDetailDatas->unit_name,
                'notax_price'         => $SaleSlipDetailDatas->notax_price,
                'memo'                => $SaleSlipDetailDatas->memo,
            );

            if (!isset($calcDepositList['total'])) {
                $calcDepositList['total'] = array(
                    'notax_subtotal_8'  => 0,
                    'notax_subtotal_10' => 0,
                    'tax_8'             => 0,
                    'tax_10'            => 0,
                    'total'             => 0
                );
            }

            if ($SaleSlipDetailDatas->tax_id == 1) {
                // 8%の計算
                // 計算
                $notaxSubTotal8Amount += $SaleSlipDetailDatas->notax_price;

            } else {
                // 10%の計算
                // 計算
                $notaxSubTotal10Amount += $SaleSlipDetailDatas->notax_price;
            }
        }

        // 税金計算
        $tax8  = floor($notaxSubTotal8Amount * 0.08);
        $tax10 = floor($notaxSubTotal10Amount * 0.1);

        // 税込小計
        $subTotal8Amount  = $notaxSubTotal8Amount  + $tax8;
        $subTotal10Amount = $notaxSubTotal10Amount + $tax10;

        // データ格納
        $calcDepositList['total']['notax_subtotal_8']  = $notaxSubTotal8Amount;
        $calcDepositList['total']['tax_8']             = $tax8 ;
        $calcDepositList['total']['notax_subtotal_10'] = $notaxSubTotal10Amount;
        $calcDepositList['total']['tax_10']            = $tax10;
        $calcDepositList['total']['total']             = $subTotal8Amount + $subTotal10Amount;

        // -----------------
        // 調整額と配送額の計算
        // -----------------
        $calcDepositIds  = array();
        $calcSaleSlipIds = array();
        foreach ($SaleSlipDetailList as $SaleSlipDetailDatas) {
            // 初期化
            if (!isset($calcDepositList['detail']['adjust_price'])) {
                $calcDepositList['detail']['adjust_price'] = array(
                    'date'               => '',
                    'name'                => '調整額',
                    'origin_name'         => '',
                    'inventory_unit_num'  => '',
                    'unit_price'         => '',
                    'unit_num'           => '',
                    'unit_name'          => '',
                    'notax_price'        => 0,
                    'memo'               => '',
                );
            }

            if (!isset($calcDepositList['detail']['delivery_price'])) {
                $calcDepositList['detail']['delivery_price'] = array(
                    'date'                => '',
                    'name'                => '配送額',
                    'origin_name'         => '',
                    'inventory_unit_num'  => '',
                    'unit_price'          => '',
                    'unit_num'            => '',
                    'unit_name'           => '',
                    'notax_price'         => 0,
                    'memo'                => '',
                );
            }
            // 売上伝票ごとに
            if (!in_array($SaleSlipDetailDatas->sale_slip_id, $calcSaleSlipIds)) {
                $calcDepositList['detail']['adjust_price']['notax_price'] += $SaleSlipDetailDatas->adjust_price;
                $calcDepositList['detail']['delivery_price']['notax_price'] += $SaleSlipDetailDatas->delivery_price;
                $calcDepositList['total']['total'] += $SaleSlipDetailDatas->adjust_price + $SaleSlipDetailDatas->delivery_price;
                $calcSaleSlipIds[] = $SaleSlipDetailDatas->sale_slip_id;
            }
        }

        // 調整額が0円の場合は配列から除去する
        if(empty($calcDepositList['detail']['adjust_price']['notax_price'])){
            unset($calcDepositList['detail']['adjust_price']);
        }

        // 配送額が0円の場合は配列から除去する
        if(empty($calcDepositList['detail']['delivery_price']['notax_price'])){
            unset($calcDepositList['detail']['delivery_price']);
        }

        // 明細の数が10件未満なら10件まで空データを入れる
        $detailCnt = count($calcDepositList['detail']);
        if ($detailCnt < 15) {
            $addLine = 15 - $detailCnt;
            for ($i=1;$i<=$addLine;$i++) {
                $calcDepositList['detail'][] = array(
                    'date'                => '',
                    'name'                => '',
                    'origin_name'         => '',
                    'inventory_unit_num'  => '',
                    'unit_price'          => '',
                    'unit_num'            => '',
                    'unit_name'           => '',
                    'notax_price'         => '',
                    'memo'                => ''
                );
            }
        }

        $pdf = \PDF::view('pdf.pdfDeliverySlip', [
            'depositList' => $calcDepositList
        ])
        ->setOption('encoding', 'utf-8')
        ->setOption('margin-bottom', 8)
        ->setOption('footer-center', '[page] ページ')
        ->setOption('footer-font-size', 8)
        ->setOption('footer-html', view('pdf.pdfFooter', [
            'company_name' => $calcDepositList['company_info']['name']
        ]));

        return $pdf->inline('delivery_slip' . '_' . $companyId .'.pdf');  //ブラウザ上で開ける
        // return $pdf->download('thisis.pdf'); //こっちにすると直接ダウンロード
    }
}
