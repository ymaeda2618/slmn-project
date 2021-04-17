<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Exception;

class PeriodPerformanceController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * 期間実績一覧
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        // postできたか、getできたか
        if ($_SERVER["REQUEST_METHOD"] != "POST"
        ) { // ページング処理

            $pp_date_type     = $request->session()->get('pp_date_type');
            $pp_date_from     = $request->session()->get('pp_date_from');
            $pp_date_to       = $request->session()->get('pp_date_to');

            // 空値の場合は初期値を設定
            if(empty($pp_date_type))  $pp_date_type = 1;
            if(empty($pp_date_from))  $pp_date_from = date('Y-m-d');
            if(empty($pp_date_to))    $pp_date_to   = date('Y-m-d');

            $pp_supply_company_code  = $request->session()->get('pp_supply_company_code');
            $pp_supply_company_id    = $request->session()->get('pp_supply_company_id');
            $pp_supply_company_text  = $request->session()->get('pp_supply_company_text');
            $pp_supply_shop_code     = $request->session()->get('pp_supply_shop_code');
            $pp_supply_shop_id       = $request->session()->get('pp_supply_shop_id');
            $pp_supply_shop_text     = $request->session()->get('pp_supply_shop_text');

            $pp_sale_company_code  = $request->session()->get('pp_sale_company_code');
            $pp_sale_company_id    = $request->session()->get('pp_sale_company_id');
            $pp_sale_company_text  = $request->session()->get('pp_sale_company_text');
            $pp_sale_shop_code     = $request->session()->get('pp_sale_shop_code');
            $pp_sale_shop_id       = $request->session()->get('pp_sale_shop_id');
            $pp_sale_shop_text     = $request->session()->get('pp_sale_shop_text');


            $pp_product_code  = $request->session()->get('pp_product_code');
            $pp_product_id    = $request->session()->get('pp_product_id');
            $pp_product_text  = $request->session()->get('pp_product_text');


            $pp_staff_code  = $request->session()->get('pp_staff_code');
            $pp_staff_id    = $request->session()->get('pp_staff_id');
            $pp_staff_text  = $request->session()->get('pp_staff_text');

        } else { // POST時の処理

            if (isset($_POST['search-btn'])) { // 検索ボタン押された時の処理

                $pp_date_type     = $request->data['PeriodPerformance']['date_type'];

                $pp_supply_company_code  = $request->data['PeriodPerformance']['supply_company_code'];
                $pp_supply_company_id    = $request->data['PeriodPerformance']['supply_company_id'];
                $pp_supply_company_text  = $request->data['PeriodPerformance']['supply_company_text'];
                $pp_supply_shop_code     = $request->data['PeriodPerformance']['supply_shop_code'];
                $pp_supply_shop_id       = $request->data['PeriodPerformance']['supply_shop_id'];
                $pp_supply_shop_text     = $request->data['PeriodPerformance']['supply_shop_text'];

                $pp_sale_company_code  = $request->data['PeriodPerformance']['sale_company_code'];
                $pp_sale_company_id    = $request->data['PeriodPerformance']['sale_company_id'];
                $pp_sale_company_text  = $request->data['PeriodPerformance']['sale_company_text'];
                $pp_sale_shop_code     = $request->data['PeriodPerformance']['sale_shop_code'];
                $pp_sale_shop_id       = $request->data['PeriodPerformance']['sale_shop_id'];
                $pp_sale_shop_text     = $request->data['PeriodPerformance']['sale_shop_text'];

                $pp_product_code       = $request->data['PeriodPerformance']['product_code'];
                $pp_product_id         = $request->data['PeriodPerformance']['product_id'];
                $pp_product_text       = $request->data['PeriodPerformance']['product_text'];

                $pp_staff_code         = $request->data['PeriodPerformance']['staff_code'];
                $pp_staff_id           = $request->data['PeriodPerformance']['staff_id'];
                $pp_staff_text         = $request->data['PeriodPerformance']['staff_text'];

                // 日付の設定
                $pp_date_from     = $request->data['PeriodPerformance']['date_from'];
                $pp_date_to       = $request->data['PeriodPerformance']['date_to'];

                $request->session()->put('pp_date_type', $pp_date_type);
                $request->session()->put('pp_date_from', $pp_date_from);
                $request->session()->put('pp_date_to', $pp_date_to);

                $request->session()->put('pp_supply_company_code', $pp_supply_company_code);
                $request->session()->put('pp_supply_company_id', $pp_supply_company_id);
                $request->session()->put('pp_supply_company_text', $pp_supply_company_text);
                $request->session()->put('pp_supply_shop_code', $pp_supply_shop_code);
                $request->session()->put('pp_supply_shop_id', $pp_supply_shop_id);
                $request->session()->put('pp_supply_shop_text', $pp_supply_shop_text);

                $request->session()->put('pp_sale_company_code', $pp_sale_company_code);
                $request->session()->put('pp_sale_company_id', $pp_sale_company_id);
                $request->session()->put('pp_sale_company_text', $pp_sale_company_text);
                $request->session()->put('pp_sale_shop_code', $pp_sale_shop_code);
                $request->session()->put('pp_sale_shop_id', $pp_sale_shop_id);
                $request->session()->put('pp_sale_shop_text', $pp_sale_shop_text);

                $request->session()->put('pp_product_code', $pp_product_code);
                $request->session()->put('pp_product_id', $pp_product_id);
                $request->session()->put('pp_product_text', $pp_product_text);

                $request->session()->put('pp_staff_code', $pp_staff_code);
                $request->session()->put('pp_staff_id', $pp_staff_id);
                $request->session()->put('pp_staff_text', $pp_staff_text);

            } else { // リセットボタンが押された時の処理

                $pp_date_type     = 1;
                $pp_date_from     = date('Y');
                $pp_date_to    = date('m');

                $pp_supply_company_code  = null;
                $pp_supply_company_id    = null;
                $pp_supply_company_text  = null;
                $pp_supply_shop_code     = null;
                $pp_supply_shop_id       = null;
                $pp_supply_shop_text     = null;

                $pp_sale_company_code  = null;
                $pp_sale_company_id    = null;
                $pp_sale_company_text  = null;
                $pp_sale_shop_code     = null;
                $pp_sale_shop_id       = null;
                $pp_sale_shop_text     = null;

                $pp_product_code       = null;
                $pp_product_id         = null;
                $pp_product_text       = null;

                $pp_staff_code         = null;
                $pp_staff_id           = null;
                $pp_staff_text         = null;

                $request->session()->forget('pp_date_type');
                $request->session()->forget('pp_date_from');
                $request->session()->forget('pp_date_to');

                $request->session()->forget('pp_supply_company_code');
                $request->session()->forget('pp_supply_company_id');
                $request->session()->forget('pp_supply_company_text');
                $request->session()->forget('pp_supply_shop_code');
                $request->session()->forget('pp_supply_shop_id');
                $request->session()->forget('pp_supply_shop_text');

                $request->session()->forget('pp_sale_company_code');
                $request->session()->forget('pp_sale_company_id');
                $request->session()->forget('pp_sale_company_text');
                $request->session()->forget('pp_sale_shop_code');
                $request->session()->forget('pp_sale_shop_id');
                $request->session()->forget('pp_sale_shop_text');

                $request->session()->forget('pp_product_code');
                $request->session()->forget('pp_product_id');
                $request->session()->forget('pp_product_text');

                $request->session()->forget('pp_staff_code');
                $request->session()->forget('pp_staff_id');
                $request->session()->forget('pp_staff_text');
            }
        }

        try {

            // 対象日付のチェック
            $pp_check_str_slip_date = "";
            $pp_check_str_deliver_date = "";
            if($pp_date_type == 1) $pp_check_str_slip_date = "checked";
            else  $pp_check_str_deliver_date = "checked";

            //---------------------
            // 仕入額を取得
            //---------------------
            $supplySlipDetailList = DB::table('supply_slip_details AS SupplySlipDetail')
            ->select(
                'SupplySlipDetail.product_id  AS product_id',
                'Product.code                 AS product_code',
                'Product.name                 AS product_name',
            )
            ->selectRaw(
                'CASE
                   WHEN Product.tax_id = 1 THEN SUM(COALESCE(SupplySlipDetail.notax_price,0))*1.08
                   WHEN Product.tax_id = 2 THEN SUM(COALESCE(SupplySlipDetail.notax_price,0))*1.10
                 END AS supply_product_amount'
            )
            ->join('products AS Product', function ($join) {
                    $join->on('Product.id', '=', 'SupplySlipDetail.product_id');
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
            ->if(!empty($pp_date_from) && !empty($pp_date_to) && $pp_date_type == 1, function ($query) use ($pp_date_from, $pp_date_to) {
                return $query->whereBetween('SupplySlip.date', [$pp_date_from, $pp_date_to]);
            })
            ->if(!empty($pp_date_from) && !empty($pp_date_to) && $pp_date_type == 2, function ($query) use ($pp_date_from, $pp_date_to) {
                return $query->whereBetween('SupplySlip.delivery_date', [$pp_date_from, $pp_date_to]);
            })
            ->if(!empty($pp_supply_company_id), function ($query) use ($pp_supply_company_id) {
                return $query->where('SupplySlip.supply_company_id', '=', $pp_supply_company_id);
            })
            ->if(!empty($pp_supply_shop_id), function ($query) use ($pp_supply_shop_id) {
                return $query->where('SupplySlip.supply_shop_id', '=', $pp_supply_shop_id);
            })
            ->if(!empty($pp_product_id), function ($query) use ($pp_product_id) {
                return $query->where('SupplySlipDetail.product_id', '=', $pp_product_id);
            })
            ->if(!empty($pp_staff_id), function ($query) use ($pp_staff_id) {
                return $query->where('SupplySlipDetail.staff_id', '=', $pp_staff_id);
            })
            ->where('SupplySlipDetail.active', '=', '1')
            ->groupBy('SupplySlipDetail.product_id')
            ->get();

            //---------------------
            // 売上額を取得
            //---------------------
            $saleSlipDetailList = DB::table('sale_slip_details AS SaleSlipDetail')
            ->select(
                'SaleSlipDetail.product_id    AS product_id',
                'Product.code                 AS product_code',
                'Product.name                 AS product_name',
            )
            ->selectRaw(
                'CASE
                   WHEN Product.tax_id = 1 THEN SUM(COALESCE(SaleSlipDetail.notax_price,0))*1.08
                   WHEN Product.tax_id = 2 THEN SUM(COALESCE(SaleSlipDetail.notax_price,0))*1.10
                 END AS sale_product_amount'
            )
            ->join('products AS Product', function ($join) {
                $join->on('Product.id', '=', 'SaleSlipDetail.product_id');
            })
            ->join('sale_slips AS SaleSlip', function ($join) {
                $join->on('SaleSlip.id', '=', 'SaleSlipDetail.sale_slip_id');
            })
            ->join('sale_companies AS SaleCompany', function ($join) {
                $join->on('SaleCompany.id', '=', 'SaleSlip.sale_company_id');
            })
            ->leftJoin('sale_shops AS SaleShop', function ($join) {
                $join->on('SaleShop.id', '=', 'SaleSlip.sale_shop_id');
            })
            ->if(!empty($pp_date_from) && !empty($pp_date_to) && $pp_date_type == 1, function ($query) use ($pp_date_from, $pp_date_to) {
                return $query->whereBetween('SaleSlip.date', [$pp_date_from, $pp_date_to]);
            })
            ->if(!empty($pp_date_from) && !empty($pp_date_to) && $pp_date_type == 2, function ($query) use ($pp_date_from, $pp_date_to) {
                return $query->whereBetween('SaleSlip.delivery_date', [$pp_date_from, $pp_date_to]);
            })
            ->if(!empty($pp_sale_company_id), function ($query) use ($pp_sale_company_id) {
                return $query->where('SaleSlip.sale_company_id', '=', $pp_sale_company_id);
            })
            ->if(!empty($pp_sale_shop_id), function ($query) use ($pp_sale_shop_id) {
                return $query->where('SaleSlip.sale_shop_id', '=', $pp_sale_shop_id);
            })
            ->if(!empty($pp_product_id), function ($query) use ($pp_product_id) {
                return $query->where('SaleSlipDetail.product_id', '=', $pp_product_id);
            })
            ->if(!empty($pp_staff_id), function ($query) use ($pp_staff_id) {
                return $query->where('SaleSlipDetail.staff_id', '=', $pp_staff_id);
            })
            ->where('SaleSlip.active', '=', '1')
            ->groupBy('SaleSlipDetail.product_id')
            ->get();

            //---------------------
            // 日別仕入売上額配列を取得
            //---------------------

            $period_performance_arr = array();
            $supply_total_amount   = 0;
            $sale_total_amount     = 0;

            // 仕入実績を抽出
            foreach ($supplySlipDetailList as $supplySlipDetailVal) {

                $product_id            = $supplySlipDetailVal['product_id'];
                $supply_product_amount = $supplySlipDetailVal['supply_product_amount'];
                $profit                = $supply_product_amount * -1;

                $period_performance_arr[$product_id] =[
                    'code'                  => $supplySlipDetailVal['product_code'],
                    'name'                  => $supplySlipDetailVal['product_name'],
                    'supply_product_amount' => $supply_product_amount,
                    'sale_product_amount'   => 0,
                    'profit'                => $profit
                ];

                $supply_total_amount   += $supply_product_amount;
            }

            // 売上実績を抽出
            foreach ($saleSlipDetailList as $saleSlipDetailVal) {

                $product_id            = $saleSlipDetailVal['product_id'];
                $sale_product_amount   = $saleSlipDetailVal['sale_product_amount'];
                $profit                = $sale_product_amount;

                if(!isset($period_performance_arr[$product_id])) {

                    $period_performance_arr[$product_id] =[
                        'code'                  => $saleSlipDetailVal['product_code'],
                        'name'                  => $saleSlipDetailVal['product_name'],
                        'supply_product_amount' => 0,
                        'sale_product_amount'   => $sale_product_amount,
                        'profit'                => $profit
                    ];
                } else {

                    // 売上額を格納
                    $period_performance_arr[$product_id]['sale_product_amount'] = $sale_product_amount;

                    // 利益額を格納
                    $period_performance_arr[$product_id]['profit']  += $profit;

                }

                $sale_total_amount   += $sale_product_amount;
            }

        } catch (\Exception $e) {

            dd($e);

            return view('PeriodPerformance.index')->with([
                'errorMessage' => $e
            ]);
        }

        return view('PeriodPerformance.index')->with([

            "pp_check_str_slip_date"            => $pp_check_str_slip_date,
            "pp_check_str_deliver_date"         => $pp_check_str_deliver_date,
            "pp_date_from"                      => $pp_date_from,
            "pp_date_to"                        => $pp_date_to,

            "pp_supply_company_code"            => $pp_supply_company_code,
            "pp_supply_company_id"              => $pp_supply_company_id,
            "pp_supply_company_text"            => $pp_supply_company_text,
            "pp_supply_shop_code"               => $pp_supply_shop_code,
            "pp_supply_shop_id"                 => $pp_supply_shop_id,
            "pp_supply_shop_text"               => $pp_supply_shop_text,

            "pp_sale_company_code"              => $pp_sale_company_code,
            "pp_sale_company_id"                => $pp_sale_company_id,
            "pp_sale_company_text"              => $pp_sale_company_text,
            "pp_sale_shop_code"                 => $pp_sale_shop_code,
            "pp_sale_shop_id"                   => $pp_sale_shop_id,
            "pp_sale_shop_text"                 => $pp_sale_shop_text,

            "pp_product_code"                   => $pp_product_code,
            "pp_product_id"                     => $pp_product_id,
            "pp_product_text"                   => $pp_product_text,

            "pp_staff_code"                     => $pp_staff_code,
            "pp_staff_id"                       => $pp_staff_id,
            "pp_staff_text"                     => $pp_staff_text,

            "supply_total_amount"               => $supply_total_amount,
            "sale_total_amount"                 => $sale_total_amount,

            "period_performance_arr"           => $period_performance_arr,
        ]);
    }

    /**
     * 仕入企業ID更新時のAjax処理
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function AjaxAutoCompleteSupplyCompany(Request $request)
    {
        // 入力された値を取得
        $input_text = $request->inputText;

        // 入力候補を初期化
        $auto_complete_array = array();

        if (!empty($input_text)) {

            // 製品DB取得
            $supplyCompanyList = DB::table('supply_companies AS SupplyCompany')
            ->select(
                'SupplyCompany.name  AS supply_company_name'
            )->where([
                    ['SupplyCompany.active', '=', '1']
            ])->where(function($query) use ($input_text){
                $query
                ->orWhere('SupplyCompany.name', 'like', "%{$input_text}%")
                ->orWhere('SupplyCompany.yomi', 'like', "%{$input_text}%");
            })
            ->get();

            if (!empty($supplyCompanyList)) {

                foreach ($supplyCompanyList as $supply_company_val) {

                    array_push($auto_complete_array, $supply_company_val->supply_company_name);
                }
            }
        }

        return json_encode($auto_complete_array);
    }

    /**
     * 仕入先企業更新時のAjax処理
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function AjaxSetSupplyCompany(Request $request)
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
            $supplyCompanyList = DB::table('supply_companies AS SupplyCompany')
            ->select(
                'SupplyCompany.code  AS code',
                'SupplyCompany.id    AS id',
                'SupplyCompany.name  AS name'
            )
            ->if(!empty($input_code), function ($query) use ($input_code) {
                return $query->where('SupplyCompany.code', '=', $input_code);
            })
            ->if(!empty($input_name), function ($query) use ($input_name) {
                return $query->where('SupplyCompany.name', 'like', $input_name);
            })
            ->first();

            if (!empty($supplyCompanyList)) {
                $output_code = $supplyCompanyList->code;
                $output_id   = $supplyCompanyList->id;
                $output_name = $supplyCompanyList->name;
            }
        }

        $returnArray = array($output_code, $output_id, $output_name);

        return json_encode($returnArray);
    }

    /**
     * 仕入店舗ID更新時のAjax処理
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function AjaxAutoCompleteSupplyShop(Request $request)
    {
        // 入力された値を取得
        $input_text = $request->inputText;

        // 入力候補を初期化
        $auto_complete_array = array();

        if (!empty($input_text)) {

            // 製品DB取得
            $supplyShopList = DB::table('supply_shops AS SupplyShop')
            ->select(
                'SupplyShop.name  AS supply_shop_name'
            )->where([
                    ['SupplyShop.active', '=', '1']
            ])->where(function($query) use ($input_text){
                $query
                ->orWhere('SupplyShop.name', 'like', "%{$input_text}%")
                ->orWhere('SupplyShop.yomi', 'like', "%{$input_text}%");
            })
            ->get();

            if (!empty($supplyShopList)) {

                foreach ($supplyShopList as $supply_shop_val) {

                    array_push($auto_complete_array, $supply_shop_val->supply_shop_name);
                }
            }
        }

        return json_encode($auto_complete_array);
    }

    /**
     * 仕入先店舗更新時のAjax処理
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function AjaxSetSupplyShop(Request $request)
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
            $supplyShopList = DB::table('supply_shops AS SupplyShop')
            ->select(
                'SupplyShop.code  AS code',
                'SupplyShop.id    AS id',
                'SupplyShop.name  AS name'
            )
            ->if(!empty($input_code), function ($query) use ($input_code) {
                return $query->where('SupplyShop.code', '=', $input_code);
            })
            ->if(!empty($input_name), function ($query) use ($input_name) {
                return $query->where('SupplyShop.name', 'like', $input_name);
            })
            ->first();

            if (!empty($supplyShopList)) {
                $output_code = $supplyShopList->code;
                $output_id   = $supplyShopList->id;
                $output_name = $supplyShopList->name;
            }
        }

        $returnArray = array($output_code, $output_id, $output_name);

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
                'Product.code       AS code',
                'Product.id         AS id',
                'Product.name       AS product_name',
                'Tax.id             AS tax_id',
                'Tax.name           AS tax_name',
                'Unit.id            AS unit_id',
                'Unit.name          AS unit_name',
                'InventoryUnit.id   AS inventory_unit_id',
                'InventoryUnit.name AS inventory_unit_name'
            )->join('taxes AS Tax', function ($join) {
                $join->on('Tax.id', '=', 'Product.tax_id');
            }
            )->join('units AS Unit', function ($join) {
                $join->on('Unit.id', '=', 'Product.unit_id');
            }
            )->join('units AS InventoryUnit', function ($join) {
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
     * 担当者ID更新時のAjax処理
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function AjaxAutoCompleteStaff(Request $request)
    {
        // 入力された値を取得
        $input_text = $request->inputText;

        // 入力候補を初期化
        $auto_complete_array = array();

        if (!empty($input_text)) {

            // 担当者DB取得
            $staffList = DB::table('staffs AS Staff')
            ->select(
                'Staff.name  AS staff_name'
            )->where([
                    ['Staff.active', '=', '1'],
            ])->where(function($query) use ($input_text){
                $query
                ->orWhere('Staff.name_sei', 'like', "%{$input_text}%")
                ->orWhere('Staff.name_mei', 'like', "%{$input_text}%")
                ->orWhere('Staff.yomi_sei', 'like', "%{$input_text}%")
                ->orWhere('Staff.yomi_mei', 'like', "%{$input_text}%");
            })
            ->get();

            if (!empty($staffList)) {

                foreach ($staffList as $staff_val) {

                    array_push($auto_complete_array, $staff_val->staff_name);
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
        $input_text = $request->inputText;

        // すべて数字かどうかチェック
        if (is_numeric($input_text)) {
            $staff_code = $input_text;
            $staff_name = null;
        } else {
            $staff_code = null;
            $staff_name = $input_text;
        }

        // 初期化
        $output_staff_code          = null;
        $output_staff_id            = null;
        $output_staff_name          = null;

        if (!empty($input_text)) {

            // 担当者DB取得
            // 担当者一覧を取得
            $staffList = DB::table('staffs AS Staff')
            ->select(
                'Staff.code       AS code',
                'Staff.id         AS id'
            ) ->selectRaw('CONCAT(Staff.name_sei," ",Staff.name_mei) AS staff_name'
            )->if(!empty($staff_code), function ($query) use ($staff_code) {
                return $query->where('Staff.code', '=', $staff_code);
            })
            ->if(!empty($staff_name), function ($query) use ($staff_name) {
                return $query->whereRaw('CONCAT(Staff.name_sei,Staff.name_mei) like "%'.$staff_name.'%"');
            })->first();

            if (!empty($staffList)) {
                $output_staff_code        = $staffList->code;
                $output_staff_id          = $staffList->id;
                $output_staff_name        = $staffList->staff_name;
            }
        }

        $returnArray = array(
            $output_staff_code,
            $output_staff_id,
            $output_staff_name
        );

        return json_encode($returnArray);
    }
}
