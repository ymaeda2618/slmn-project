<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Http\Request;
use App\Standard;
use App\Staff;
use App\SupplySlip;
use App\SupplySlipDetail;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Str;

class SupplySlipController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth');
    }

    /**
     * 仕入一覧
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {

        // 現在のURL（このSupplySlipIndex）を取得
        $current_url = $request->url();

        // リファラー（直前のURL）を取得
        $referer = $request->headers->get('referer');

        // リファラーが存在し、かつこの画面以外から遷移してきた場合
        if ($referer && strpos($referer, $current_url) === false) {
            // セッション初期化
            $request->session()->forget([
                'condition_date_type',
                'condition_date_from',
                'condition_date_to',
                'condition_company_code',
                'condition_company_id',
                'condition_company_text',
                'condition_product_code',
                'condition_product_id',
                'condition_product_text',
                'condition_submit_type',
                'condition_display_sort',
                'condition_display_num',
            ]);
        }

        // リクエストパスを取得
        $request_path = $request->path();
        $path_array   = explode('/', $request_path);

        // ページングの番号の有無でindexのaction先を変更
        if(count($path_array) > 1){
            $search_action = '../SupplySlipIndex';
        } else {
            $search_action = './SupplySlipIndex';
        }

        // postできたか、getできたか
        if ($_SERVER["REQUEST_METHOD"] != "POST"
        ) { // ページング処理
            \Log::info('SupplySlip Index 画面初期表示', [
                'user_id' => $this->login_user_id ?? null
            ]);

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
            $condition_product_code  = $request->session()->get('condition_product_code');
            $condition_product_id    = $request->session()->get('condition_product_id');
            $condition_product_text  = $request->session()->get('condition_product_text');
            $condition_submit_type   = $request->session()->get('condition_submit_type');
            $condition_display_sort  = $request->session()->get('condition_display_sort');
            $condition_display_num   = $request->session()->get('condition_display_num');

            // 空値の場合は初期値を設定
            if(empty($condition_display_sort)) $condition_display_sort = 0;
            if(empty($condition_display_num)) $condition_display_num = 20;

        } else { // POST時の処理

            if (isset($_POST['search-btn'])) { // 検索ボタン押された時の処理
                \Log::info('SupplySlip Index 検索実行', [
                    'user_id' => $this->login_user_id ?? null
                ]);

                $req_data = $request->data['SupplySlip'];
                if (isset($request->data['SupplySlipDetail'])) {
                    $req_detail_data = $request->data['SupplySlipDetail'];
                } else {
                    $req_detail_data = null;
                }

                $condition_date_type     = isset($req_data['date_type']) ? $req_data['date_type'] : 1;
                $condition_company_code  = isset($req_data['supply_company_code']) ? $req_data['supply_company_code'] : NULL;
                $condition_company_id    = isset($req_data['supply_company_id']) ? $req_data['supply_company_id'] : NULL;
                $condition_company_text  = isset($req_data['supply_company_text']) ? $req_data['supply_company_text'] : NULL;
                $condition_product_code  = isset($req_detail_data['product_code']) ? $req_detail_data['product_code'] : NULL;
                $condition_product_id    = isset($req_detail_data['product_id']) ? $req_detail_data['product_id'] : NULL;
                $condition_product_text  = isset($req_detail_data['product_text']) ? $req_detail_data['product_text'] : NULL;
                $condition_submit_type   = isset($req_data['supply_submit_type']) ? $req_data['supply_submit_type'] : 0;
                $condition_display_sort  = isset($request->display_sort) ? $request->display_sort : 0;
                $condition_display_num   = isset($request->display_num) ? $request->display_num : 20;

                // 日付の設定
                $condition_date_from     = isset($req_data['supply_date_from']) ? $req_data['supply_date_from'] : NULL;
                $condition_date_to       = isset($req_data['supply_date_to']) ? $req_data['supply_date_to'] : NULL;
                // どちらか片方しか入力されなかった場合は同じ日付を入れる
                if (!empty($condition_date_from) && empty($condition_date_to)) {
                    $condition_date_to = $condition_date_from;
                }
                if (empty($condition_date_from) && !empty($condition_date_to)) {
                    $condition_date_from = $condition_date_to;
                }

                $request->session()->put('condition_date_type', $condition_date_type);
                $request->session()->put('condition_date_from', $condition_date_from);
                $request->session()->put('condition_date_to', $condition_date_to);
                $request->session()->put('condition_company_code', $condition_company_code);
                $request->session()->put('condition_company_id', $condition_company_id);
                $request->session()->put('condition_company_text', $condition_company_text);
                $request->session()->put('condition_product_code', $condition_product_code);
                $request->session()->put('condition_product_id', $condition_product_id);
                $request->session()->put('condition_product_text', $condition_product_text);
                $request->session()->put('condition_submit_type', $condition_submit_type);
                $request->session()->put('condition_display_sort', $condition_display_sort);
                $request->session()->put('condition_display_num', $condition_display_num);

            } else { // リセットボタンが押された時の処理

                \Log::info('SupplySlip Index リセット実行', [
                    'user_id' => $this->login_user_id ?? null
                ]);

                $condition_date_type     = 1;
                $condition_date_from     = date('Y-m-d');
                $condition_date_to       = date('Y-m-d');
                $condition_company_code  = null;
                $condition_company_id    = null;
                $condition_company_text  = null;
                $condition_product_code  = null;
                $condition_product_id    = null;
                $condition_product_text  = null;
                $condition_submit_type   = 0;
                $condition_display_sort  = 0;
                $condition_display_num   = 20;
                $request->session()->forget('condition_date_type');
                $request->session()->forget('condition_date_from');
                $request->session()->forget('condition_date_to');
                $request->session()->forget('condition_company_code');
                $request->session()->forget('condition_company_id');
                $request->session()->forget('condition_company_text');
                $request->session()->forget('condition_product_code');
                $request->session()->forget('condition_product_id');
                $request->session()->forget('condition_product_text');
                $request->session()->forget('condition_submit_type');
                $request->session()->forget('condition_display_sort');
                $request->session()->forget('condition_display_num');
            }
        }

        \Log::info('検索条件', [
            'user_id'           => $this->login_user_id ?? null,
            'date_type'         => $condition_date_type,
            'date_from'         => $condition_date_from,
            'date_to'           => $condition_date_to,
            'supply_company_id' => $condition_company_id,
            'product_id'        => $condition_product_id,
            'submit_type'       => $condition_submit_type,
        ]);

        try {

            // supply_slip_detailsのサブクエリを作成
            $product_sub_query = null;
            if(!empty($condition_product_id)) {

                $product_sub_query = DB::table('supply_slip_details as SubTable')
                ->select('SubTable.supply_slip_id AS supply_slip_id')
                ->where('SubTable.product_id', '=', $condition_product_id)
                ->where('SubTable.active', '=', '1')
                ->groupBy('SubTable.supply_slip_id');
            }

            //---------------------
            // 仕入一覧を取得
            //---------------------
            $supplySlipList = DB::table('supply_slips AS SupplySlip')
            ->select(
                'SupplySlip.id                  AS supply_slip_id',
                'SupplySlip.delivery_price      AS delivery_price',
                'SupplySlip.adjust_price        AS adjust_price',
                'SupplySlip.notax_sub_total     AS notax_sub_total',
                'SupplySlip.supply_submit_type  AS supply_submit_type',
                'SupplyCompany.code             AS supply_company_code',
                'SupplyCompany.name             AS supply_company_name'
            )
            ->selectRaw('DATE_FORMAT(SupplySlip.date, "%Y/%m/%d")                AS supply_slip_date')
            ->selectRaw('DATE_FORMAT(SupplySlip.delivery_date, "%Y/%m/%d") AS supply_slip_delivery_date')
            ->selectRaw('DATE_FORMAT(SupplySlip.modified, "%m-%d %H:%i")      AS supply_slip_modified')
            ->join('supply_companies AS SupplyCompany', function ($join) {
                $join->on('SupplyCompany.id', '=', 'SupplySlip.supply_company_id');
            })
            ->if(!empty($condition_product_id), function ($query) use ($product_sub_query) {
                return $query
                       ->join(DB::raw('('. $product_sub_query->toSql() .') as SupplySlipDetail'), 'SupplySlipDetail.supply_slip_id', '=', 'SupplySlip.id')
                       ->mergeBindings($product_sub_query);
            })
            ->if(!empty($condition_date_from) && !empty($condition_date_to) && $condition_date_type == 1, function ($query) use ($condition_date_from, $condition_date_to) {
                return $query->whereBetween('SupplySlip.date', [$condition_date_from, $condition_date_to]);
            })
            ->if(!empty($condition_date_from) && !empty($condition_date_to) && $condition_date_type == 2, function ($query) use ($condition_date_from, $condition_date_to) {
                return $query->whereBetween('SupplySlip.delivery_date', [$condition_date_from, $condition_date_to]);
            })
            ->if(!empty($condition_company_id), function ($query) use ($condition_company_id) {
                return $query->where('SupplySlip.supply_company_id', '=', $condition_company_id);
            })
            ->if(!empty($condition_submit_type), function ($query) use ($condition_submit_type) {
                return $query->where('SupplySlip.supply_submit_type', '=', $condition_submit_type);
            })
            ->if($condition_display_sort == 0, function ($query) { // 伝票日付:降順
                return $query->orderBy('SupplySlip.date', 'desc');
            })
            ->if($condition_display_sort == 1, function ($query) { // 伝票日付:昇順
                return $query->orderBy('SupplySlip.date', 'asc');
            })
            ->if($condition_display_sort == 2, function ($query) { // 納品日付:降順
                return $query->orderBy('SupplySlip.delivery_date', 'desc');
            })
            ->if($condition_display_sort == 3, function ($query) { // 納品日付:昇順
                return $query->orderBy('SupplySlip.delivery_date', 'asc');
            })
            ->where('SupplySlip.active', '1')
            ->orderBy('SupplySlip.id', 'desc')
            ->paginate($condition_display_num);

            //---------------------
            // 仕入れ一覧の総額集計
            //---------------------
            $supplySlipSumList = DB::table('supply_slips AS SupplySlip')
            ->selectRaw('COUNT(SupplySlip.id) AS supply_slip_num')
            ->selectRaw('SUM(SupplySlip.delivery_price) AS delivery_price_sum')
            ->selectRaw('SUM(SupplySlip.adjust_price) AS adjust_price_sum')
            ->selectRaw('SUM(SupplySlip.notax_sub_total) AS notax_sub_total_sum')

            ->join('supply_companies AS SupplyCompany', function ($join) {
                $join->on('SupplyCompany.id', '=', 'SupplySlip.supply_company_id');
            })
            ->if(!empty($condition_date_from) && !empty($condition_date_to) && $condition_date_type == 1, function ($query) use ($condition_date_from, $condition_date_to) {
                return $query->whereBetween('SupplySlip.date', [$condition_date_from, $condition_date_to]);
            })
            ->if(!empty($condition_date_from) && !empty($condition_date_to) && $condition_date_type == 2, function ($query) use ($condition_date_from, $condition_date_to) {
                return $query->whereBetween('SupplySlip.delivery_date', [$condition_date_from, $condition_date_to]);
            })
            ->if(!empty($condition_company_id), function ($query) use ($condition_company_id) {
                return $query->where('SupplySlip.supply_company_id', '=', $condition_company_id);
            })
            ->if(!empty($condition_product_id), function ($query) use ($product_sub_query) {
                return $query
                       ->join(DB::raw('('. $product_sub_query->toSql() .') as SupplySlipDetail'), 'SupplySlipDetail.supply_slip_id', '=', 'SupplySlip.id')
                       ->mergeBindings($product_sub_query);
            })
            ->if(!empty($condition_submit_type), function ($query) use ($condition_submit_type) {
                return $query->where('SupplySlip.supply_submit_type', '=', $condition_submit_type);
            })
            ->where('SupplySlip.active', '=', '1')
            ->get();

            // 全体で何件伝票があるのかカウント
            $supply_slip_num = 0;
            // 全体の配送金額をカウント
            $delivery_price_amount = 0;
            // 全体の調整額をカウント
            $adjust_price_amount = 0;
            // 全体の税抜小計額をカウント
            $notax_sub_total_amount = 0;
            // 全体の総額をカウント
            $supply_slip_amount = 0;

            if(!empty($supplySlipSumList)) {

                // 最初の要素を取得
                $supplySlipSumVal = current($supplySlipSumList);

                $supply_slip_num        = $supplySlipSumVal[0]->supply_slip_num;
                $delivery_price_amount  = $supplySlipSumVal[0]->delivery_price_sum;
                $adjust_price_amount    = $supplySlipSumVal[0]->adjust_price_sum;
                $notax_sub_total_amount = $supplySlipSumVal[0]->notax_sub_total_sum;
                $supply_slip_amount     = ($delivery_price_amount + $adjust_price_amount + $notax_sub_total_amount);
            }

            //---------------------
            // 伝票詳細を取得
            //---------------------
            $supply_slip_id_arr = array();
            foreach($supplySlipList as $supplySlipVal){
                $supply_slip_id_arr[] = $supplySlipVal->supply_slip_id;
            }

            $SupplySlipDetailList = DB::table('supply_slip_details AS SupplySlipDetail')
            ->select(
                'SupplySlip.id                     AS supply_slip_id',
                'SupplySlip.total                  AS supply_slip_total',
                'SupplySlip.supply_submit_type     AS supply_submit_type',
                'SupplyCompany.code                AS supply_company_code',
                'SupplyCompany.name                AS supply_company_name',
                'Product.code                      AS product_code',
                'Product.name                      AS product_name',
                'Product.tax_id                    AS product_tax_id',
                'Standard.name                     AS standard_name',
                'SupplySlipDetail.id               AS supply_slip_detail_id',
                'SupplySlipDetail.unit_price       AS supply_slip_detail_unit_price',
                'SupplySlipDetail.unit_num         AS supply_slip_detail_unit_num',
                'Unit.name                         AS unit_name',
                'SupplySlipDetail.memo             AS supply_slip_detail_memo',
            )
            ->selectRaw('DATE_FORMAT(SupplySlip.date, "%Y/%m/%d")                AS supply_slip_date')
            ->selectRaw('DATE_FORMAT(SupplySlip.delivery_date, "%Y/%m/%d") AS supply_slip_delivery_date')
            ->selectRaw('DATE_FORMAT(SupplySlip.modified, "%m-%d %H:%i")      AS supply_slip_modified')

            ->selectRaw('CONCAT(Staff.name_sei," ",Staff.name_mei)            AS staff_name')

            ->join('supply_slips as SupplySlip', function ($join) {
                $join->on('SupplySlip.id', '=', 'SupplySlipDetail.supply_slip_id')
                ->where('SupplySlip.active', '=', true);
            })
            ->join('products as Product', function ($join) {
                $join->on('Product.id', '=', 'SupplySlipDetail.product_id')
                ->where('Product.active', '=', true);
            })
            ->leftJoin('standards as Standard', function ($join) {
                $join->on('Standard.id', '=', 'SupplySlipDetail.standard_id')
                ->where('Standard.active', '=', true);
            })
            ->join('units as Unit', function ($join) {
                $join->on('Unit.id', '=', 'SupplySlipDetail.unit_id')
                ->where('Unit.active', '=', true);
            })
            ->leftJoin('staffs as Staff', function ($join) {
                $join->on('Staff.id', '=', 'SupplySlipDetail.staff_id')
                ->where('Staff.active', '=', true);
            })
            ->leftJoin('units as InventoryUnit', function ($join) {
                $join->on('InventoryUnit.id', '=', 'SupplySlipDetail.inventory_unit_id')
                ->where('InventoryUnit.active', '=', true);
            })
            ->leftJoin('supply_companies as SupplyCompany', function ($join) {
                $join->on('SupplyCompany.id', '=', 'SupplySlip.supply_company_id')
                ->where('SupplyCompany.active', '=', true);
            })
            ->if(!empty($condition_date_from) && !empty($condition_date_to) && $condition_date_type == 1, function ($query) use ($condition_date_from, $condition_date_to) {
                return $query->whereBetween('SupplySlip.date', [$condition_date_from, $condition_date_to]);
            })
            ->if(!empty($condition_date_from) && !empty($condition_date_to) && $condition_date_type == 2, function ($query) use ($condition_date_from, $condition_date_to) {
                return $query->whereBetween('SupplySlip.delivery_date', [$condition_date_from, $condition_date_to]);
            })
            ->if(!empty($condition_company_id), function ($query) use ($condition_company_id) {
                return $query->where('SupplySlip.supply_company_id', '=', $condition_company_id);
            })
            ->if(!empty($condition_product_id), function ($queryDetail) use ($condition_product_id) {
                return $queryDetail->where('SupplySlipDetail.product_id', '=', $condition_product_id);
            })
            ->if(!empty($condition_submit_type), function ($query) use ($condition_submit_type) {
                return $query->where('SupplySlip.supply_submit_type', '=', $condition_submit_type);
            })
            ->whereIn('SupplySlip.id', $supply_slip_id_arr)
            ->where('SupplySlip.active', '=', '1')
            ->orderBy('SupplySlip.id', 'desc')
            ->orderBy('SupplySlipDetail.sort', 'asc')
            ->get();

            // 各伝票にいくつ明細がついているのかをカウントする配列
            $supply_slip_condition_num             = 0;       // 条件指定された時の伝票の枚数
            $supply_slip_condition_notax_sub_total = 0;       // 条件指定された伝票詳細の税抜小計
            $supply_slip_detail_count_arr          = array(); // 各伝票が伝票詳細をいくつ持っているか
            $supply_slip_detail_arr                = array(); // 各伝票に紐づく伝票詳細配列


            // 伝票詳細で取得したDBをループ
            foreach($SupplySlipDetailList as $SupplySlipDetails){

                $unit_price  = $SupplySlipDetails->supply_slip_detail_unit_price; // 単価
                $unit_num    = $SupplySlipDetails->supply_slip_detail_unit_num;   // 数量
                $notax_price = $unit_price * $unit_num;                           // 税抜小計
                $supply_slip_condition_notax_sub_total += $notax_price;           // 小計を加えていく

                if(!isset($supply_slip_detail_count_arr[$SupplySlipDetails->supply_slip_id])){
                    $supply_slip_condition_num +=1;
                    $supply_slip_detail_count_arr[$SupplySlipDetails->supply_slip_id] = 0;
                }

                $supply_slip_detail_count_arr[$SupplySlipDetails->supply_slip_id] += 1;

                $supply_slip_detail_arr[$SupplySlipDetails->supply_slip_id][] = [

                    'product_code'                  => $SupplySlipDetails->product_code,
                    'product_name'                  => $SupplySlipDetails->product_name,
                    'product_tax_id'                => $SupplySlipDetails->product_tax_id,
                    'standard_name'                 => $SupplySlipDetails->standard_name,
                    'supply_slip_detail_id'         => $SupplySlipDetails->supply_slip_detail_id,
                    'supply_slip_detail_unit_price' => $unit_price,
                    'supply_slip_detail_unit_num'   => $unit_num,
                    'unit_name'                     => $SupplySlipDetails->unit_name,
                    'staff_name'                    => $SupplySlipDetails->staff_name,
                    'memo'                          => $SupplySlipDetails->supply_slip_detail_memo,
                ];
            }

            \Log::info('SupplySlip 件数/金額', [
                'user_id' => $this->login_user_id ?? null,
                '伝票件数' => $supply_slip_num,
                '配送額合計' => $delivery_price_amount,
                '調整額合計' => $adjust_price_amount,
                '税抜合計' => $notax_sub_total_amount,
                '総合計' => $supply_slip_amount,
                '条件付き伝票数' => $supply_slip_condition_num,
                '条件付き税抜小計' => $supply_slip_condition_notax_sub_total,
                '明細件数' => count($SupplySlipDetailList)
            ]);

            // 対象日付のチェック
            $check_str_slip_date = "";
            $check_str_deliver_date = "";
            if($condition_date_type == 1) $check_str_slip_date = "checked";
            else  $check_str_deliver_date = "checked";

        } catch (\Exception $e) {

            \Log::error('SupplySlip Index エラー', [
                'user_id' => $this->login_user_id ?? null,
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'trace'   => $e->getTraceAsString()
            ]);

            return view('errors.error')->with([
                'errorMessage' => $e->getMessage()
            ]);
        }

        return view('SupplySlip.index')->with([
            "search_action"                => $search_action,
            "check_str_slip_date"          => $check_str_slip_date,
            "check_str_deliver_date"       => $check_str_deliver_date,
            "condition_date_from"          => $condition_date_from,
            "condition_date_to"            => $condition_date_to,
            "condition_company_code"       => $condition_company_code,
            "condition_company_id"         => $condition_company_id,
            "condition_company_text"       => $condition_company_text,
            "condition_product_code"       => $condition_product_code,
            "condition_product_id"         => $condition_product_id,
            "condition_product_text"       => $condition_product_text,
            "condition_submit_type"        => $condition_submit_type,
            "supplySlipList"               => $supplySlipList,
            "SupplySlipDetailList"         => $SupplySlipDetailList,
            "supply_slip_num"              => $supply_slip_num,
            "delivery_price_amount"        => $delivery_price_amount,
            "adjust_price_amount"          => $adjust_price_amount,
            "notax_sub_total_amount"       => $notax_sub_total_amount,
            "supply_slip_amount"           => $supply_slip_amount,
            "supply_slip_condition_num"    => $supply_slip_condition_num,
            "supply_slip_condition_notax_sub_total" => $supply_slip_condition_notax_sub_total,
            "supply_slip_detail_arr"       => $supply_slip_detail_arr,
            "supply_slip_detail_count_arr" => $supply_slip_detail_count_arr,
            "condition_display_sort"       => $condition_display_sort,
            "condition_display_num"        => $condition_display_num
        ]);
    }

    /**
     * 仕入編集
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function edit($supply_slip_id)
    {

        \Log::info('SupplySlipController@edit 開始', [
            'user_id'        => $this->login_user_id ?? null,
            'supply_slip_id' => $supply_slip_id
        ]);

        try{
            // activeの変数を格納
            $this_active = 1;

            // 仕入伝票取得
            $SupplySlipList = DB::table('supply_slips AS SupplySlip')
            ->select(
                'SupplySlip.id                  AS supply_slip_id',
                'SupplySlip.notax_sub_total_8   AS notax_sub_total_8',
                'SupplySlip.notax_sub_total_10  AS notax_sub_total_10',
                'SupplySlip.notax_sub_total     AS notax_sub_total',
                'SupplySlip.tax_total_8         AS tax_total_8',
                'SupplySlip.tax_total_10        AS tax_total_10',
                'SupplySlip.tax_total           AS tax_total',
                'SupplySlip.sub_total_8         AS sub_total_8',
                'SupplySlip.sub_total_10        AS sub_total_10',
                'SupplySlip.sub_total           AS sub_total',
                'SupplySlip.delivery_price      AS delivery_price',
                'SupplySlip.adjust_price        AS adjust_price',
                'SupplySlip.total               AS total',
                'SupplySlip.remarks             AS remarks',
                'SupplySlip.supply_submit_type  AS supply_submit_type',
                'SupplyCompany.code             AS supply_company_code',
                'SupplyCompany.id               AS supply_company_id',
                'SupplyCompany.name             AS supply_company_name',
                'Delivery.code                  AS delivery_code',
                'Delivery.id                    AS delivery_id',
                'Delivery.name                  AS delivery_name'
            )
            ->selectRaw('DATE_FORMAT(SupplySlip.date, "%Y-%m-%d") AS supply_slip_supply_date')
            ->selectRaw('DATE_FORMAT(SupplySlip.delivery_date, "%Y-%m-%d") AS supply_slip_delivery_date')
            ->join('supply_companies as SupplyCompany', function ($join) {
                $join->on('SupplyCompany.id', '=', 'SupplySlip.supply_company_id')
                    ->where('SupplyCompany.active', '=', true);
            })
            ->leftJoin('deliverys as Delivery', function ($join) {
                $join->on('Delivery.id', '=', 'SupplySlip.delivery_id')
                    ->where('Delivery.active', '=', true);
            })
            ->where([
                ['SupplySlip.id', '=', $supply_slip_id],
                ['SupplySlip.active', '=', $this_active],
            ])
            ->first();

            // 仕入伝票詳細取得
            $SupplySlipDetailList = DB::table('supply_slip_details AS SupplySlipDetail')
            ->select(
                'SupplySlipDetail.id                 AS supply_slip_detail_id',
                'SupplySlipDetail.unit_price         AS unit_price',
                'SupplySlipDetail.unit_num           AS unit_num',
                'SupplySlipDetail.notax_price        AS notax_price',
                'SupplySlipDetail.seri_no            AS seri_no',
                'SupplySlipDetail.inventory_unit_num AS inventory_unit_num',
                'SupplySlipDetail.memo               AS memo',
                'SupplySlipDetail.sort               AS sort',
                'Product.code                        AS product_code',
                'Product.id                          AS product_id',
                'Product.name                        AS product_name',
                'Tax.id                              AS tax_id',
                'Tax.name                            AS tax_name',
                'Standard.code                       AS standard_code',
                'Standard.id                         AS standard_id',
                'Standard.name                       AS standard_name',
                'Quality.code                        AS quality_code',
                'Quality.id                          AS quality_id',
                'Quality.name                        AS quality_name',
                'Unit.id                             AS unit_id',
                'Unit.name                           AS unit_name',
                'OriginArea.id                       AS origin_area_id',
                'OriginArea.name                     AS origin_area_name',
                'Staff.code                          AS staff_code',
                'Staff.id                            AS staff_id',
                'InventoryUnit.id                    AS inventory_unit_id',
                'InventoryUnit.name                  AS inventory_unit_name'
            )
            ->selectRaw('CONCAT(Staff.name_sei," ",Staff.name_mei) AS staff_name')
            ->join('products as Product', function ($join) {
                $join->on('Product.id', '=', 'SupplySlipDetail.product_id')
                    ->where('Product.active', '=', true);
            })
            ->join('taxes as Tax', function ($join) {
                $join->on('Tax.id', '=', 'Product.tax_id')
                    ->where('Tax.active', '=', true);
            })
            ->leftJoin('standards as Standard', function ($join) {
                $join->on('Standard.id', '=', 'SupplySlipDetail.standard_id')
                    ->where('Standard.active', '=', true);
            })
            ->leftJoin('qualities as Quality', function ($join) {
                $join->on('Quality.id', '=', 'SupplySlipDetail.quality_id')
                    ->where('Quality.active', '=', true);
            })
            ->join('units as Unit', function ($join) {
                $join->on('Unit.id', '=', 'SupplySlipDetail.unit_id')
                    ->where('Unit.active', '=', true);
            })
            ->leftJoin('origin_areas as OriginArea', function ($join) {
                $join->on('OriginArea.id', '=', 'SupplySlipDetail.origin_area_id')
                    ->where('OriginArea.active', '=', true);
            })
            ->join('staffs as Staff', function ($join) {
                $join->on('Staff.id', '=', 'SupplySlipDetail.staff_id')
                    ->where('Staff.active', '=', true);
            })
            ->leftJoin('units as InventoryUnit', function ($join) {
                $join->on('InventoryUnit.id', '=', 'SupplySlipDetail.inventory_unit_id')
                    ->where('InventoryUnit.active', '=', true);
            })
            ->where([
                ['SupplySlipDetail.supply_slip_id', '=', $supply_slip_id],
                ['SupplySlipDetail.active', '=', $this_active],
            ])
            ->get();

            return view('SupplySlip.edit')->with([
                "SupplySlipList"        => $SupplySlipList,
                "SupplySlipDetailList"  => $SupplySlipDetailList
            ]);
        } catch (\Exception $e) {
            \Log::error('SupplySlipController@edit エラー発生', [
                'user_id'        => $this->login_user_id ?? null,
                'supply_slip_id' => $supply_slip_id,
                'error'          => $e->getMessage(),
                'trace'          => $e->getTraceAsString()
            ]);
            return view('errors.error')->with([
                'errorMessage' => $e->getMessage()
            ]);

        }

    }

    /**
     * 編集登録
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function editRegister(Request $request)
    {

        \Log::info('SupplySlipController@editRegister 開始', [
            'user_id' => $this->login_user_id ?? null,
        ]);

        // トランザクション開始
        DB::connection()->beginTransaction();

        try{

            // ユーザー情報の取得
            $user_info    = \Auth::user();
            $user_info_id = $user_info['id'];

            $SupplySlipData = $request->data['SupplySlip'];
            $SupplySlipDetailData = $request->data['SupplySlipDetail'];

            \Log::info('受信SupplySlipデータ', [
                'user_id'         => $this->login_user_id ?? null,
                'supply_slip_id'  => $SupplySlipData['id'] ?? null,
                'submit_type'     => $SupplySlipData['supply_submit_type'] ?? null,
                '詳細件数'         => count($SupplySlipDetailData)
            ]);

            if ($SupplySlipData['supply_submit_type'] == 3) {

                \Log::info('SupplySlip 論理削除モード', [
                    'supply_slip_id' => $SupplySlipData['id'],
                ]);

                // -----------------
                // 伝票を論理削除させる
                // -----------------
                $SupplySlip = \App\SupplySlip::find($SupplySlipData['id']);
                $SupplySlip->active           = 0;              // アクティブフラグ
                $SupplySlip->modified_user_id = $user_info_id;  // 更新者ユーザーID
                $SupplySlip->modified         = Carbon::now();  // 更新時間

                $SupplySlip->save();

            } else {

                \Log::info('SupplySlip 登録更新モード', [
                    'supply_slip_id' => $SupplySlipData['id'],
                ]);

                // 値がNULLのところを初期化
                if(empty($SupplySlipData['delivery_id'])) $SupplySlipData['delivery_id'] = 0;
                if(empty($SupplySlipData['delivery_price'])) $SupplySlipData['delivery_price'] = 0;
                if(empty($SupplySlipData['adjust_price'])) $SupplySlipData['adjust_price'] = 0;

                // supply_slipsを登録する
                $SupplySlip = \App\SupplySlip::find($SupplySlipData['id']);
                $SupplySlip->date               = $SupplySlipData['supply_date'];          // 日付
                $SupplySlip->delivery_date      = $SupplySlipData['delivery_date'];        // 納品日
                $SupplySlip->supply_company_id  = $SupplySlipData['supply_company_id'];    // 仕入先ID
                $SupplySlip->delivery_id        = $SupplySlipData['delivery_id'];          // 配送ID
                $SupplySlip->notax_sub_total_8  = $SupplySlipData['notax_sub_total_8'];    // 8%課税対象額
                $SupplySlip->notax_sub_total_10 = $SupplySlipData['notax_sub_total_10'];   // 10%課税対象額
                $SupplySlip->notax_sub_total    = $SupplySlipData['notax_sub_total'];      // 税抜合計額
                $SupplySlip->tax_total_8        = $SupplySlipData['tax_total_8'];          // 8%課税対象額
                $SupplySlip->tax_total_10       = $SupplySlipData['tax_total_10'];         // 10%課税対象額
                $SupplySlip->tax_total          = $SupplySlipData['tax_total'];            // 税抜合計額
                $SupplySlip->sub_total_8        = $SupplySlipData['sub_total_8'];          // 8%合計額
                $SupplySlip->sub_total_10       = $SupplySlipData['sub_total_10'];         // 10%合計額
                $SupplySlip->delivery_price     = $SupplySlipData['delivery_price'];       // 合計額
                $SupplySlip->sub_total          = $SupplySlipData['sub_total'];            // 配送額
                $SupplySlip->adjust_price       = $SupplySlipData['adjust_price'];         // 調整額
                $SupplySlip->total              = $SupplySlipData['total'];                // 合計額
                $SupplySlip->remarks            = $SupplySlipData['remarks'];              // 備考
                $SupplySlip->supply_submit_type = $SupplySlipData['supply_submit_type'];   // 登録タイプ
                $SupplySlip->modified_user_id   = $user_info_id;                           // 更新者ユーザーID
                $SupplySlip->modified           = Carbon::now();                           // 更新時間

                $SupplySlip->save();

                // 作成したIDを取得する
                $supply_slip_new_id = $SupplySlip->id;

                \Log::info('SupplySlipDetail 削除開始', [
                    'supply_slip_id' => $SupplySlipData['id'],
                ]);

                // 伝票詳細を削除
                \App\SupplySlipDetail::where('supply_slip_id', $SupplySlipData['id'])->delete();

                $supply_slip_detail = array();
                $sort = 1;

                foreach ($SupplySlipDetailData as $index => $SupplySlipDetail) {
                    if (empty($SupplySlipDetail['product_id'])) {
                        \Log::warning('SupplySlipDetailにproduct_id未設定', [
                            'index' => $index,
                            'データ' => $SupplySlipDetail,
                        ]);
                    }

                    // 値がNULLのところを初期化
                    if (empty($SupplySlipData['inventory_unit_id'])) $SupplySlipData['inventory_unit_id'] = 0;
                    if (empty($SupplySlipData['inventory_unit_num'])) $SupplySlipData['inventory_unit_num'] = 0;
                    if (empty($SupplySlipData['origin_area_id'])) $SupplySlipData['origin_area_id'] = 0;

                    $supply_slip_detail[] = [
                        'supply_slip_id'     => $supply_slip_new_id,
                        'product_id'         => $SupplySlipDetail['product_id'],
                        'unit_price'         => $SupplySlipDetail['unit_price'],
                        'unit_num'           => $SupplySlipDetail['unit_num'],
                        'notax_price'        => $SupplySlipDetail['notax_price'],
                        'unit_id'            => $SupplySlipDetail['unit_id'],
                        'origin_area_id'     => $SupplySlipDetail['origin_area_id'],
                        'staff_id'           => $SupplySlipDetail['staff_id'],
                        'inventory_unit_id'  => $SupplySlipDetail['inventory_unit_id'],
                        'inventory_unit_num' => $SupplySlipDetail['inventory_unit_num'],
                        'memo'               => $SupplySlipDetail['memo'],
                        'sort'               => $sort,
                        'created_user_id'    => $user_info_id,
                        'created'            => Carbon::now(),
                        'modified_user_id'   => $user_info_id,
                        'modified'           => Carbon::now(),
                    ];

                    $sort ++;
                }

                if(!empty($supply_slip_detail)) {

                    DB::table('supply_slip_details')->insert($supply_slip_detail);

                    \Log::info('SupplySlipDetail 登録完了', [
                        '件数'            => count($supply_slip_detail),
                        'supply_slip_id' => $supply_slip_new_id,
                    ]);

                }
            }

            // 問題なければコミット
            DB::connection()->commit();

            \Log::info('SupplySlipController@editRegister 完了', [
                'user_id'        => $user_info_id,
                'supply_slip_id' => $SupplySlipData['id']
            ]);

        } catch (\Exception $e) {

            DB::rollback();

            \Log::error('SupplySlipController@editRegister エラー', [
                'user_id' => $this->login_user_id ?? null,
                'message' => $e->getMessage(),
                'line'    => $e->getLine(),
                'file'    => $e->getFile()
            ]);

            return view('errors.error')->with([
                'error_message' => $e->getMessage()
            ]);
        }

        return redirect('./SupplySlipCreate');

    }

    /**
     * 仕入登録
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function create()
    {
        return view('SupplySlip.create');
    }

    /**
     * 仕入一覧のcsvダウンロード処理
     *
     * @param Request $request
     * @return void
     */
    public function csvDownLoad(Request $request)
    {
        $fileName = "supply_list.csv";

        $supply_submit_name = [
            0 => '全て',
            1 => '登録済み',
            2 => '一時保存',
        ];

        $user_id = $this->login_user_id ?? null;

        \Log::info('SupplySlipController@csvDownLoad 開始', [
            'user_id' => $user_id
        ]);

        // セッションにある検索条件を取得する
        $date_type    = $request->session()->get('condition_date_type');
        $date_from    = $request->session()->get('condition_date_from');
        $date_to      = $request->session()->get('condition_date_to');
        $company_id   = $request->session()->get('condition_company_id');
        $product_id   = $request->session()->get('condition_product_id');
        $submit_type  = $request->session()->get('condition_submit_type');
        $display_sort = $request->session()->get('condition_display_sort');
        $display_num  = $request->session()->get('condition_display_num');

        // sessionにデータない場合
        if (empty($date_type)) $date_type = 1;
        if (empty($date_from)) $date_from = date('Y-m-d');
        if (empty($date_to)) $date_to = date('Y-m-d');

        \Log::info('SupplySlipController@csvDownLoad 検索条件', [
            'user_id'      => $user_id,
            'date_type'    => $date_type,
            'date_from'    => $date_from,
            'date_to'      => $date_to,
            'company_id'   => $company_id,
            'product_id'   => $product_id,
            'submit_type'  => $submit_type,
            'display_sort' => $display_sort,
            'display_num'  => $display_num,
        ]);

        try {

            // ------------------
            // 仕入一覧データを取得
            // ------------------
            $supplySlipList = DB::table('supply_slips AS SupplySlip')
                ->select([
                    'SupplySlip.id AS supply_slip_id',
                    'SupplySlip.delivery_price AS delivery_price',
                    'SupplySlip.adjust_price AS adjust_price',
                    'SupplySlip.notax_sub_total AS notax_sub_total',
                    'SupplySlip.supply_submit_type AS supply_submit_type',
                    'SupplyCompany.code AS supply_company_code',
                    'SupplyCompany.name AS supply_company_name'
                ])
                ->selectRaw('
                    DATE_FORMAT(SupplySlip.date, "%Y/%m/%d") AS supply_slip_date,
                    DATE_FORMAT(SupplySlip.delivery_date, "%Y/%m/%d") AS supply_slip_delivery_date,
                    DATE_FORMAT(SupplySlip.modified, "%m-%d %H:%i") AS supply_slip_modified
                ')
                ->join('supply_companies AS SupplyCompany', 'SupplyCompany.id', '=', 'SupplySlip.supply_company_id')
                ->when(!empty($product_id), function ($query) use ($product_id) {
                    return $query->whereExists(function ($subQuery) use ($product_id) {
                        $subQuery->select(DB::raw(1))
                            ->from('supply_slip_details AS SubTable')
                            ->whereColumn('SubTable.supply_slip_id', 'SupplySlip.id')
                            ->where('SubTable.product_id', '=', $product_id)
                            ->where('SubTable.active', '=', '1');
                    });
                })
                ->when(!empty($date_from) && !empty($date_to) && $date_type == 1, function ($query) use ($date_from, $date_to) {
                    return $query->whereBetween('SupplySlip.date', [$date_from, $date_to]);
                })
                ->when(!empty($date_from) && !empty($date_to) && $date_type == 2, function ($query) use ($date_from, $date_to) {
                    return $query->whereBetween('SupplySlip.delivery_date', [$date_from, $date_to]);
                })
                ->when(!empty($company_id), function ($query) use ($company_id) {
                    return $query->where('SupplySlip.supply_company_id', '=', $company_id);
                })
                ->when(!empty($submit_type), function ($query) use ($submit_type) {
                    return $query->where('SupplySlip.supply_submit_type', '=', $submit_type);
                })
                ->when($display_sort === 0, function ($query) {
                    return $query->orderBy('SupplySlip.date', 'desc');
                })
                ->when($display_sort === 1, function ($query) {
                    return $query->orderBy('SupplySlip.date', 'asc');
                })
                ->when($display_sort === 2, function ($query) {
                    return $query->orderBy('SupplySlip.delivery_date', 'desc');
                })
                ->when($display_sort === 3, function ($query) {
                    return $query->orderBy('SupplySlip.delivery_date', 'asc');
                })
                ->where('SupplySlip.active', 1)
                ->orderBy('SupplySlip.id', 'desc')
                ->get();

            \Log::info('SupplySlip 一覧取得', [
                'user_id' => $user_id,
                '件数' => $supplySlipList->count()
            ]);

            // ---------------
            // 伝票詳細を取得
            // ---------------
            $SupplySlipDetailList = DB::table('supply_slip_details AS SupplySlipDetail')
                ->select([
                    'SupplySlip.id AS supply_slip_id',
                    'SupplySlip.total AS supply_slip_total',
                    'SupplySlip.supply_submit_type AS supply_submit_type',
                    'SupplyCompany.code AS supply_company_code',
                    'SupplyCompany.name AS supply_company_name',
                    'Product.code AS product_code',
                    'Product.name AS product_name',
                    'Product.tax_id AS product_tax_id',
                    'Standard.name AS standard_name',
                    'SupplySlipDetail.id AS supply_slip_detail_id',
                    'SupplySlipDetail.inventory_unit_num AS inventory_unit_num',
                    'InventoryUnit.name AS inventory_unit_name',
                    'SupplySlipDetail.unit_price AS unit_price',
                    'SupplySlipDetail.unit_num AS unit_num',
                    'Unit.name AS unit_name',
                    'SupplySlipDetail.memo AS memo',
                    'Staff.code AS staff_code',
                    'OriginArea.id AS origin_area_id',
                    'OriginArea.name AS origin_area_name',
                ])
                ->selectRaw('
                    DATE_FORMAT(SupplySlip.date, "%Y/%m/%d") AS supply_slip_date,
                    DATE_FORMAT(SupplySlip.delivery_date, "%Y/%m/%d") AS supply_slip_delivery_date,
                    DATE_FORMAT(SupplySlip.modified, "%m-%d %H:%i") AS supply_slip_modified,
                    CONCAT(Staff.name_sei, " ", Staff.name_mei) AS staff_name
                ')
                ->join('supply_slips AS SupplySlip', 'SupplySlip.id', '=', 'SupplySlipDetail.supply_slip_id')
                ->join('products AS Product', 'Product.id', '=', 'SupplySlipDetail.product_id')
                ->leftJoin('standards AS Standard', 'Standard.id', '=', 'SupplySlipDetail.standard_id')
                ->join('units AS Unit', 'Unit.id', '=', 'SupplySlipDetail.unit_id')
                ->leftJoin('units AS InventoryUnit', 'InventoryUnit.id', '=', 'SupplySlipDetail.inventory_unit_id')
                ->leftJoin('staffs AS Staff', 'Staff.id', '=', 'SupplySlipDetail.staff_id')
                ->leftJoin('supply_companies AS SupplyCompany', 'SupplyCompany.id', '=', 'SupplySlip.supply_company_id')
                ->leftJoin('origin_areas AS OriginArea', 'OriginArea.id', '=', 'SupplySlipDetail.origin_area_id')
                ->whereExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('supply_slips AS s')
                        ->whereColumn('s.id', 'SupplySlipDetail.supply_slip_id')
                        ->where('s.active', 1);
                })
                ->when(!empty($date_from) && !empty($date_to) && $date_type == 1, function ($query) use ($date_from, $date_to) {
                    return $query->whereBetween('SupplySlip.date', [$date_from, $date_to]);
                })
                ->when(!empty($date_from) && !empty($date_to) && $date_type == 2, function ($query) use ($date_from, $date_to) {
                    return $query->whereBetween('SupplySlip.delivery_date', [$date_from, $date_to]);
                })
                ->when(!empty($company_id), function ($query) use ($company_id) {
                    return $query->where('SupplySlip.supply_company_id', '=', $company_id);
                })
                ->when(!empty($product_id), function ($query) use ($product_id) {
                    return $query->where('SupplySlipDetail.product_id', '=', $product_id);
                })
                ->when(!empty($submit_type), function ($query) use ($submit_type) {
                    return $query->where('SupplySlip.supply_submit_type', '=', $submit_type);
                })
                ->orderBy('SupplySlip.id', 'desc')
                ->orderBy('SupplySlipDetail.sort', 'asc')
                ->get();

            \Log::info('SupplySlipDetail 一覧取得', [
                'user_id' => $user_id,
                '件数' => $SupplySlipDetailList->count()
            ]);

            $supply_data = [];
            foreach ($supplySlipList as $supplyData) {
                $supply_data[$supplyData->supply_slip_id]['date']                = $supplyData->supply_slip_date;
                $supply_data[$supplyData->supply_slip_id]['delivery_date']       = $supplyData->supply_slip_delivery_date;
                $supply_data[$supplyData->supply_slip_id]['company_code']        = $supplyData->supply_company_code;
                $supply_data[$supplyData->supply_slip_id]['company_name']        = $supplyData->supply_company_name;
            }

            // csv配列作成
            $csv_data = [];
            foreach ($SupplySlipDetailList as $detailData) {

                $supplySlipId = $detailData->supply_slip_id;

                $tax = '8%';
                // 税抜金額
                $notax_total = $detailData->unit_price * $detailData->unit_num;
                // 税込金額
                if ($detailData->product_tax_id == 1) { // 8%の場合
                    $total = floor($notax_total * 1.08);
                } else {
                    $tax = '10%';
                    $total = floor($notax_total * 1.1);
                }

                // 自動レジは30文字MAX
                $product_name = Str::limit($detailData->product_name, 27);

                $csv_data[] = [
                    0  => $supply_data[$supplySlipId]['date'],                  // 仕入日付
                    1  => $supply_data[$supplySlipId]['delivery_date'],         // 納品日付
                    2  => $supply_data[$supplySlipId]['company_code'],          // 仕入企業コード
                    3  => $supply_data[$supplySlipId]['company_name'],          // 仕入企業名
                    4  => $detailData->product_code,                            // 製品コード
                    5  => $product_name,                                        // 製品名
                    6  => $detailData->inventory_unit_num,                      // 個数
                    7  => $detailData->inventory_unit_name,                     // 個数単位
                    8  => $detailData->unit_num,                                // 数量
                    9  => $detailData->unit_name,                               // 数量単位
                    10 => $detailData->unit_price,                              // 単価
                    11 => $tax,                                                 // 税率
                    12 => $notax_total,                                         // 税抜合計金額
                    13 => $total,                                               // 税込合計金額
                    14 => $detailData->origin_area_id,                          // 産地コード
                    15 => $detailData->origin_area_name,                        // 産地名
                    16 => $detailData->staff_code,                              // 担当者コード
                    17 => $detailData->staff_name,                              // 担当者名
                    18 => $detailData->memo,                                    // 摘要
                ];

            }

            \Log::info('CSVデータ生成完了', [
                'user_id' => $user_id,
                'csv行数' => count($csv_data)
            ]);

            // レスポンスをストリームで返す
            $response = new StreamedResponse(function () use($csv_data) {

                $handle = fopen('php://output', 'w');

                // ヘッダー行を追加
                fputcsv($handle, array_map(function ($value) {
                    return mb_convert_encoding($value, 'SJIS-win', 'UTF-8');
                }, [
                    '仕入日付',
                    '納品日付',
                    '仕入企業コード',
                    '仕入企業名',
                    '製品コード',
                    '製品名',
                    '個数',
                    '個数単位',
                    '数量',
                    '数量単位',
                    '単価',
                    '税率',
                    '税抜合計金額',
                    '税込合計金額',
                    '産地コード',
                    '産地名',
                    '担当者コード',
                    '担当者名',
                    '摘要',
                ]));

                // データをCSVに書き込む
                foreach ($csv_data as $row) {
                    fputcsv($handle, array_map(function ($value) {
                        return mb_convert_encoding($value, 'SJIS-win', 'UTF-8');
                    }, $row));
                }

                fclose($handle);
            });

            // HTTPヘッダーを設定
            $response->headers->set('Content-Type', 'text/csv; charset=Shift_JIS');
            $response->headers->set('Content-Disposition', 'attachment; filename="' . $fileName . '"');

            \Log::info('CSVダウンロード完了', [
                'user_id' => $user_id
            ]);

            return $response;

        } catch (\Exception $e) {

            \Log::error('SupplySlipController@csvDownLoad エラー発生', [
                'user_id' => $user_id,
                'message' => $e->getMessage(),
                'line'    => $e->getLine(),
                'file'    => $e->getFile(),
            ]);

            return view('errors.error')->with([
                'error_message' => 'CSV出力時にエラーが発生しました：' . $e->getMessage()
            ]);

        }
    }

    /**
     * 製品ID更新時のAjax処理
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function AjaxChangeProductId(Request $request)
    {
        $user_id = $this->login_user_id ?? null;

        \Log::info('SupplySlipController@AjaxChangeProductId 開始', [
            'user_id'            => $user_id,
            'slip_num'           => $request->slip_num,
            'selected_product_id'=> $request->selected_product_id,
        ]);

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
        $ajaxHtml .= "<select class='form-control' id='standard_id_{$slip_num}' name='data[SupplySlip][standard_id][{$slip_num}]'>";
        $ajaxHtml .= "    <option value='0'>-</option>";
                        if(!empty($StandardList)) {
                           foreach ($StandardList as $Standards){
        $ajaxHtml .= "    <option value='{$Standards->id}'>{$Standards->name}</option>";
                           }
                        }
        $ajaxHtml .= "</select>";

        $returnArray = array($ajaxHtml, $tax_id, $tax_name);

        \Log::info('SupplySlipController@AjaxChangeProductId 正常終了', [
            'user_id'      => $user_id,
            'tax_id'       => $tax_id,
            'tax_name'     => $tax_name,
            '標準件数'     => $StandardList->count(),
        ]);

        return $returnArray;

    }

    /**
     * 仕入企業ID更新時のAjax処理
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function AjaxAutoCompleteSupplyCompany(Request $request)
    {

        $user_id = $this->login_user_id ?? null;

        \Log::info('SupplySlipController@AjaxAutoCompleteSupplyCompany 開始', [
            'user_id'   => $user_id,
            'inputText' => $request->inputText,
        ]);

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

        \Log::info('SupplySlipController@AjaxAutoCompleteSupplyCompany レスポンス', [
            'user_id'     => $user_id,
            'suggestions' => $auto_complete_array,
            '件数'         => count($auto_complete_array),
        ]);

        return json_encode($auto_complete_array);
    }

    /**
     * 仕入先企業更新時のAjax処理
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function AjaxSetSupplyCompany(Request $request)
    {

        $user_id = $this->login_user_id ?? null;

        \Log::info('SupplySlipController@AjaxSetSupplyCompany 開始', [
            'user_id'   => $user_id,
            'inputText' => $request->inputText,
        ]);

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

        \Log::info('SupplySlipController@AjaxSetSupplyCompany レスポンス', [
            'user_id'      => $user_id,
            'output_code'  => $output_code,
            'output_id'    => $output_id,
            'output_name'  => $output_name,
            '件数'         => !empty($output_id) ? 1 : 0,
        ]);

        return json_encode($returnArray);
    }

    /**
     * 製品ID更新時のAjax処理
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function AjaxAutoCompleteProduct(Request $request)
    {

        $user_id = $this->login_user_id ?? null;

        \Log::info('SupplySlipController@AjaxAutoCompleteProduct 開始', [
            'user_id'   => $user_id,
            'inputText' => $request->inputText,
        ]);

        // 入力された値を取得
        $input_text = $request->inputText;

        // 入力候補を初期化
        $auto_complete_array = array();

        if (!empty($input_text)) {

            // 製品DB取得
            $productList = DB::table('products AS Product')
            ->select(
                'Product.name  AS product_name',
                'Product.code AS product_code',
                'Unit.name AS unit_name'
            )->join('units AS Unit', function ($join) {
                $join->on('Unit.id', '=', 'Product.unit_id');
            })->where([
                    ['Product.display_flg', '=', '1'],
                    ['Product.active', '=', '1'],
            ])->where(function($query) use ($input_text){
                $query
                ->orWhere('Product.name', 'like', "%{$input_text}%")
                ->orWhere('Product.yomi', 'like', "%{$input_text}%");
            })
            ->get();

            if (!empty($productList)) {

                foreach ($productList as $product_val) {

                    // サジェスト表示を「コード 商品名 単位」にする
                    $suggest_text = '【' . $product_val->product_code . '】 ' . $product_val->product_name . ' (' . $product_val->unit_name . ')';

                    array_push($auto_complete_array, $suggest_text);
                }
            }
        }

        \Log::info('SupplySlipController@AjaxAutoCompleteProduct 終了', [
            'user_id' => $user_id,
            '件数'    => count($auto_complete_array),
        ]);

        return json_encode($auto_complete_array);
    }

    /**
     * 製品ID更新時のAjax処理
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function AjaxSetProduct(Request $request)
    {

        $user_id = $this->login_user_id ?? null;

        \Log::info('SupplySlipController@AjaxSetProduct 開始', [
            'user_id'   => $user_id,
            'inputText' => $request->inputText,
        ]);

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
            } else {
                $product_code = null;
                $product_name = $input_text;
            }
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

        \Log::info('SupplySlipController@AjaxSetProduct 終了', [
            'user_id'      => $user_id,
            'product_id'   => $output_product_id,
            'product_code' => $output_product_code,
        ]);


        return json_encode($returnArray);
    }

    /**
     * 規格ID更新時のAjax処理
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function AjaxAutoCompleteStandard(Request $request)
    {

        $user_id = $this->login_user_id ?? null;

        \Log::info('SupplySlipController@AjaxAutoCompleteStandard 開始', [
            'user_id'    => $user_id,
            'product_id' => $request->productId,
            'input_text' => $request->inputText,
        ]);

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

        \Log::info('SupplySlipController@AjaxAutoCompleteStandard 終了', [
            'user_id'       => $user_id,
            'suggest_count' => count($auto_complete_array),
        ]);

        return json_encode($auto_complete_array);
    }

    /**
     * 規格ID更新時のAjax処理
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function AjaxSetStandard(Request $request)
    {

        $user_id = $this->login_user_id ?? null;

        \Log::info('SupplySlipController@AjaxSetStandard 開始', [
            'user_id'    => $user_id,
            'product_id' => $request->productId,
            'input_text' => $request->inputText,
        ]);

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

        \Log::info('SupplySlipController@AjaxSetStandard 終了', [
            'user_id'      => $user_id,
            'output_code'  => $output_code,
            'output_id'    => $output_id,
            'output_name'  => $output_name,
        ]);

        return json_encode($returnArray);
    }

    /**
     * 品質ID更新時のAjax処理
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function AjaxAutoCompleteQuality(Request $request)
    {

        $user_id = $this->login_user_id ?? null;

        \Log::info('SupplySlipController@AjaxAutoCompleteQuality 開始', [
            'user_id'    => $user_id,
            'input_text' => $request->inputText,
        ]);

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

        \Log::info('SupplySlipController@AjaxAutoCompleteQuality 終了', [
            'user_id' => $user_id,
            '候補数' => count($auto_complete_array),
        ]);

        return json_encode($auto_complete_array);
    }

    /**
     * 品質ID更新時のAjax処理
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function AjaxSetQuality(Request $request)
    {

        $user_id = $this->login_user_id ?? null;

        \Log::info('SupplySlipController@AjaxSetQuality 開始', [
            'user_id'    => $user_id,
            'input_text' => $request->inputText,
        ]);

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

        \Log::info('SupplySlipController@AjaxSetQuality 終了', [
            'user_id'      => $user_id,
            'output_code'  => $output_code,
            'output_id'    => $output_id,
            'output_name'  => $output_name,
        ]);

        return json_encode($returnArray);
    }

    /**
     * 産地ID更新時のAjax処理
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function AjaxAutoCompleteOriginArea(Request $request)
    {

        $user_id = $this->login_user_id ?? null;

        \Log::info('SupplySlipController@AjaxAutoCompleteOriginArea 開始', [
            'user_id'    => $user_id,
            'input_text' => $request->inputText,
            'line'       => __LINE__,
        ]);

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

        \Log::info('SupplySlipController@AjaxAutoCompleteOriginArea 終了', [
            'user_id' => $user_id,
            'suggestions_count' => count($auto_complete_array),
            'line' => __LINE__,
        ]);

        return json_encode($auto_complete_array);
    }

    /**
     * 産地ID更新時のAjax処理
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function AjaxSetOriginArea(Request $request)
    {

        $user_id = $this->login_user_id ?? null;

        \Log::info('SupplySlipController@AjaxSetOriginArea 開始', [
            'user_id'    => $user_id,
            'input_text' => $request->inputText,
            'line'       => __LINE__,
        ]);

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

        \Log::info('SupplySlipController@AjaxSetOriginArea 終了', [
            'user_id'       => $user_id,
            'output_id'     => $output_id,
            'output_name'   => $output_name,
            'line'          => __LINE__,
        ]);

        return json_encode($returnArray);
    }

    /**
     * 担当者ID更新時のAjax処理
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function AjaxAutoCompleteStaff(Request $request)
    {

        $user_id = $this->login_user_id ?? null;

        \Log::info('SupplySlipController@AjaxAutoCompleteStaff 開始', [
            'user_id'    => $user_id,
            'input_text' => $request->inputText,
            'line'       => __LINE__,
        ]);

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

        \Log::info('SupplySlipController@AjaxAutoCompleteStaff 終了', [
            'user_id'  => $user_id,
            'count'    => count($auto_complete_array),
            'line'     => __LINE__,
        ]);

        return json_encode($auto_complete_array);
    }

    /**
     * 担当者ID更新時のAjax処理
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function AjaxSetStaff(Request $request)
    {

        $user_id = $this->login_user_id ?? null;

        \Log::info('SupplySlipController@AjaxSetStaff 開始', [
            'user_id'    => $user_id,
            'input_text' => $request->inputText,
            'line'       => __LINE__,
        ]);

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

        \Log::info('SupplySlipController@AjaxSetStaff 終了', [
            'user_id'     => $user_id,
            'output_name' => $output_name,
            'line'        => __LINE__,
        ]);

        return json_encode($returnArray);
    }

    /**
     * 配送ID更新時のAjax処理
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function AjaxAutoCompleteDelivery(Request $request)
    {

        $user_id = $this->login_user_id ?? null;

        \Log::info('SupplySlipController@AjaxAutoCompleteDelivery 開始', [
            'user_id'    => $user_id,
            'input_text' => $request->inputText,
            'line'       => __LINE__,
        ]);

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

        \Log::info('SupplySlipController@AjaxAutoCompleteDelivery 終了', [
            'user_id' => $user_id,
            'line'    => __LINE__,
        ]);

        return json_encode($auto_complete_array);
    }

    /**
     * 配送ID更新時のAjax処理
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function AjaxSetDelivery(Request $request)
    {

        $user_id = $this->login_user_id ?? null;

        \Log::info('SupplySlipController@AjaxSetDelivery 開始', [
            'user_id'    => $user_id,
            'input_text' => $request->inputText,
            'line'       => __LINE__,
        ]);

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

            \Log::info('SupplySlipController@AjaxSetDelivery 検索結果', [
                'user_id'     => $user_id,
                'found_id'    => $output_id,
                'found_code'  => $output_code,
                'found_name'  => $output_name,
                'line'        => __LINE__,
            ]);
        }

        \Log::info('SupplySlipController@AjaxSetDelivery 終了', [
            'user_id' => $user_id,
            'line'    => __LINE__,
        ]);

        $returnArray = array($output_code, $output_id, $output_name);

        return json_encode($returnArray);
    }

    /**
     * 仕入伝票の登録処理
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function registerSupplySlips(Request $request)
    {

        $user_info    = \Auth::user();
        $user_info_id = $user_info['id'] ?? null;

        \Log::info('SupplySlipController@registerSupplySlips 開始', [
            'user_id' => $user_info_id,
            'line'    => __LINE__,
        ]);

        // トランザクション開始
        DB::connection()->beginTransaction();

        try{

            $SupplySlipData = $request->data['SupplySlip'];
            $SupplySlipDetailData = $request->data['SupplySlipDetail'];

            // 値がNULLのところを初期化
            if(empty($SupplySlipData['delivery_id'])) $SupplySlipData['delivery_id'] = 0;
            if(empty($SupplySlipData['delivery_price'])) $SupplySlipData['delivery_price'] = 0;
            if(empty($SupplySlipData['adjust_price'])) $SupplySlipData['adjust_price'] = 0;

            // supply_slipsを登録する
            $SupplySlip = new SupplySlip;
            $SupplySlip->date               = $SupplySlipData['supply_date'];          // 日付
            $SupplySlip->delivery_date      = $SupplySlipData['delivery_date'];        // 納品日
            $SupplySlip->supply_company_id  = $SupplySlipData['supply_company_id'];    // 仕入先ID
            $SupplySlip->delivery_id        = $SupplySlipData['delivery_id'];          // 配送ID
            $SupplySlip->notax_sub_total_8  = $SupplySlipData['notax_sub_total_8'];    // 8%課税対象額
            $SupplySlip->notax_sub_total_10 = $SupplySlipData['notax_sub_total_10'];   // 10%課税対象額
            $SupplySlip->notax_sub_total    = $SupplySlipData['notax_sub_total'];      // 税抜合計額
            $SupplySlip->tax_total_8        = $SupplySlipData['tax_total_8'];          // 8%課税対象額
            $SupplySlip->tax_total_10       = $SupplySlipData['tax_total_10'];         // 10%課税対象額
            $SupplySlip->tax_total          = $SupplySlipData['tax_total'];            // 税抜合計額
            $SupplySlip->sub_total_8        = $SupplySlipData['sub_total_8'];          // 8%合計額
            $SupplySlip->sub_total_10       = $SupplySlipData['sub_total_10'];         // 10%合計額
            $SupplySlip->delivery_price     = $SupplySlipData['delivery_price'];       // 合計額
            $SupplySlip->sub_total          = $SupplySlipData['sub_total'];            // 配送額
            $SupplySlip->adjust_price       = $SupplySlipData['adjust_price'];         // 調整額
            $SupplySlip->total              = $SupplySlipData['total'];                // 合計額
            $SupplySlip->remarks            = $SupplySlipData['remarks'];              // 備考
            $SupplySlip->supply_submit_type = $SupplySlipData['supply_submit_type'];   // 登録タイプ
            $SupplySlip->sort               = 100;                                   // ソート
            $SupplySlip->created_user_id    = $user_info_id;                         // 作成者ユーザーID
            $SupplySlip->created            = Carbon::now();                         // 作成時間
            $SupplySlip->modified_user_id   = $user_info_id;                         // 更新者ユーザーID
            $SupplySlip->modified           = Carbon::now();                         // 更新時間

            $SupplySlip->save();

            // 作成したIDを取得する
            $supply_slip_new_id = $SupplySlip->id;

            $supply_slip_detail = array();
            $sort = 1;

            foreach($SupplySlipDetailData as $SupplySlipDetail){

                // 値がNULLのところを初期化
                if (empty($SupplySlipData['inventory_unit_id'])) $SupplySlipData['inventory_unit_id'] = 0;
                if (empty($SupplySlipData['inventory_unit_num'])) $SupplySlipData['inventory_unit_num'] = 0;
                if (empty($SupplySlipData['origin_area_id'])) $SupplySlipData['origin_area_id'] = 0;

                $supply_slip_detail[] = [
                    'supply_slip_id'     => $supply_slip_new_id,
                    'product_id'         => $SupplySlipDetail['product_id'],
                    'unit_price'         => $SupplySlipDetail['unit_price'],
                    'unit_num'           => $SupplySlipDetail['unit_num'],
                    'notax_price'        => $SupplySlipDetail['notax_price'],
                    'unit_id'            => $SupplySlipDetail['unit_id'],
                    'origin_area_id'     => $SupplySlipDetail['origin_area_id'],
                    'staff_id'           => $SupplySlipDetail['staff_id'],
                    'inventory_unit_id'  => $SupplySlipDetail['inventory_unit_id'],
                    'inventory_unit_num' => $SupplySlipDetail['inventory_unit_num'],
                    'memo'               => $SupplySlipDetail['memo'],
                    'sort'               => $sort,
                    'created_user_id'    => $user_info_id,
                    'created'            => Carbon::now(),
                    'modified_user_id'   => $user_info_id,
                    'modified'           => Carbon::now(),
                ];

                $sort ++;
            }

            if(!empty($supply_slip_detail)) {

                DB::table('supply_slip_details')->insert($supply_slip_detail);

                \Log::info('supply_slip_details 登録完了', [
                    'user_id' => $user_info_id,
                    'line' => __LINE__,
                    'count' => count($supply_slip_detail),
                ]);

            }

            // 問題なければコミット
            DB::connection()->commit();

            \Log::info('registerSupplySlips 正常終了', [
                'user_id' => $user_info_id,
                'line' => __LINE__,
            ]);

        } catch (\Exception $e) {

            \Log::error('registerSupplySlips 例外発生', [
                'user_id'  => $user_info_id,
                'line'     => __LINE__,
                'message'  => $e->getMessage(),
                'file'     => $e->getFile(),
                'exception_line' => $e->getLine(),
            ]);

            return view('errors.error', ['error_message' => '仕入スリップ登録処理でエラーが発生しました。']);
        }

        return redirect('./SupplySlipCreate');
    }

    /**
     * 伝票新規追加 登録
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function AjaxAddSlip(Request $request)
    {
        $user_info = \Auth::user();
        $user_info_id = $user_info['id'] ?? null;

        \Log::info('AjaxAddSlip 開始', [
            'user_id' => $user_info_id,
            'line' => __LINE__,
            'slip_num' => $request->slip_num,
        ]);

        // 伝票NOを取得
        $slip_num = $request->slip_num;
        if(empty($slip_num)) $slip_num = 1;

        $tabInitialNum = intval(7*$slip_num + 2);

        // 追加伝票形成
        $ajaxHtml1 = '';
        $ajaxHtml1 .= "<tr id='slip-partition-".$slip_num."' class='partition-area'>";
        $ajaxHtml1 .= "</tr>";
        $ajaxHtml1 .= "<input type='hidden' name='sort' id='sort-".$slip_num."' value='".$slip_num."'>";
        $ajaxHtml1 .= "<input type='hidden' name='data[SupplySlipDetail][".$slip_num."][id]' id='id-".$slip_num."' value=''>";
        $ajaxHtml1 .= '<tr id="slip-upper-' . $slip_num . '">';
        $ajaxHtml1 .= '    <td class="index-td-blue" rowspan="2">' . $slip_num . '</td>';
        $ajaxHtml1 .= '    <td colspan="2" id="product-code-area-' . $slip_num . '">';
        $ajaxHtml1 .= '        <input type="hidden" id="product_id_' . $slip_num . '" name="data[SupplySlipDetail][' . $slip_num . '][product_id]">';
        $ajaxHtml1 .= '        <input type="hidden" id="tax_id_' . $slip_num . '" name="data[SupplySlipDetail][' . $slip_num . '][tax_id]" value="' . $slip_num . '">';
        $ajaxHtml1 .= '    </td>';
        $ajaxHtml1 .= '    <td>';
        $ajaxHtml1 .= '        <input type="number" class="form-control" id="inventory_unit_num_' . $slip_num . '" name="data[SupplySlipDetail][' . $slip_num . '][inventory_unit_num]" tabindex="' . ($tabInitialNum + 2) . '">';
        $ajaxHtml1 .= '   </td>';
        $ajaxHtml1 .= '   <td>';
        $ajaxHtml1 .= '        <input type="number" class="form-control" id="unit_num_' . $slip_num . '" name="data[SupplySlipDetail][' . $slip_num . '][unit_num]" onchange="javascript:priceNumChange(' . $slip_num . ')" tabindex="' . ($tabInitialNum + 3) . '">';
        $ajaxHtml1 .= '   </td>';
        $ajaxHtml1 .= '   <td colspan="2">';
        $ajaxHtml1 .= '        <input type="number" class="form-control" id="unit_price_' . $slip_num . '" name="data[SupplySlipDetail][' . $slip_num . '][unit_price]" onchange="javascript:priceNumChange(' . $slip_num . ')" tabindex="' . ($tabInitialNum + 4) . '">';
        $ajaxHtml1 .= '   </td>';
        $ajaxHtml1 .= '    <td colspan="2" id="origin-code-area-' . $slip_num . '">';
        $ajaxHtml1 .= '        <input type="hidden" id="origin_area_id_' . $slip_num . '" name="data[SupplySlipDetail][' . $slip_num . '][origin_area_id]">';
        $ajaxHtml1 .= '    </td>';
        $ajaxHtml1 .= '   <td id="staff-code-area-'.$slip_num.'">';
        $ajaxHtml1 .= '        <input type="hidden" id="staff_id_' . $slip_num . '" name="data[SupplySlipDetail][' . $slip_num . '][staff_id]" value="9">';
        $ajaxHtml1 .= '   </td>';
        $ajaxHtml1 .= '   <td colspan="2">';
        $ajaxHtml1 .= '        <input type="text" class="form-control" id="staff_text_' . $slip_num . '" name="data[SupplySlipDetail][' . $slip_num . '][staff_text]" placeholder="担当欄" value="石塚 貞雄" readonly>';
        $ajaxHtml1 .= '   </td>';
        $ajaxHtml1 .= '   <td rowspan="2">';
        $ajaxHtml1 .= '        <button id="remove-slip-btn" type="button" class="btn rmv-slip-btn btn-secondary" onclick="javascript:removeSlip(' . $slip_num . ')">削除</button>';
        $ajaxHtml1 .= '   </td>';
        $ajaxHtml1 .= '</tr>';
        $ajaxHtml1 .= '<tr id="slip-lower-' . $slip_num . '">';
        $ajaxHtml1 .= '    <td colspan="2">';
        $ajaxHtml1 .= '        <input type="text" class="form-control" id="product_text_' . $slip_num . '" name="data[SupplySlipDetail][' . $slip_num . '][product_text]" placeholder="製品欄" readonly>';
        $ajaxHtml1 .= '    </td>';
        $ajaxHtml1 .= '    <td>';
        $ajaxHtml1 .= '        <input type="text" class="form-control" id="inventory_unit_text_' . $slip_num . '" name="data[SupplySlipDetail][' . $slip_num . '][inventory_unit_text]" placeholder="個数欄" readonly>';
        $ajaxHtml1 .= '        <input type="hidden" id="inventory_unit_id_' . $slip_num . '" name="data[SupplySlipDetail][' . $slip_num . '][inventory_unit_id]" value="' . $slip_num . '">';
        $ajaxHtml1 .= '    </td>';
        $ajaxHtml1 .= '    <td>';
        $ajaxHtml1 .= '        <input type="text" class="form-control" id="unit_text_' . $slip_num . '" name="data[SupplySlipDetail][' . $slip_num . '][unit_text]" placeholder="数量欄" readonly>';
        $ajaxHtml1 .= '        <input type="hidden" id="unit_id_' . $slip_num . '" name="data[SupplySlipDetail][' . $slip_num . '][unit_id]" value="' . $slip_num . '">';
        $ajaxHtml1 .= '    </td>';
        $ajaxHtml1 .= '    <td colspan="2">';
        $ajaxHtml1 .= '        <input type="text" class="form-control" id="notax_price_' . $slip_num . '" name="data[SupplySlipDetail][' . $slip_num . '][notax_price]" value="0" readonly>';
        $ajaxHtml1 .= '    </td>';
        $ajaxHtml1 .= '    <td colspan="2">';
        $ajaxHtml1 .= '        <input type="text" class="form-control" id="origin_area_text_' . $slip_num . '" name="data[SupplySlipDetail][' . $slip_num . '][origin_area_text]" placeholder="産地欄" readonly>';
        $ajaxHtml1 .= '    </td>';
        $ajaxHtml1 .= '    <td colspan="3">';
        $ajaxHtml1 .= '        <input type="text" class="form-control" id="memo_' . $slip_num . '" name="data[SupplySlipDetail][' . $slip_num . '][memo]" placeholder="摘要欄" tabindex="' . ($tabInitialNum + 7) . '">';
        $ajaxHtml1 .= '    </td>';
        $ajaxHtml1 .= '</tr>';

        //-------------------------------
        // AutoCompleteの要素は別で形成する
        //-------------------------------
        // 製品ID
        $autoCompleteProduct = '<input type="text" class="form-control product_code_input" id="product_code_' . $slip_num . '" name="data[SupplySlipDetail][' . $slip_num . '][product_code]" tabindex="' . ($tabInitialNum + 1) . '">';
        // 産地
        $autoCompleteOrigin  = '<input type="text" class="form-control origin_area_code_input" id="origin_area_code_' . $slip_num . '" name="data[SupplySlipDetail][' . $slip_num . '][origin_area_code]" tabindex="' . ($tabInitialNum + 5) . '">';
        // 担当
        $autoCompleteStaff   = '<input type="text" class="form-control staff_code_input" id="staff_code_' . $slip_num . '" name="data[SupplySlipDetail][' . $slip_num . '][staff_code]" value="1009" tabindex="' . ($tabInitialNum + 6) . '">';

        $slip_num = intval($slip_num) + 1;

        $returnArray = array($slip_num, $ajaxHtml1, $autoCompleteProduct, $autoCompleteOrigin, $autoCompleteStaff);

        \Log::info('AjaxAddSlip 正常終了', [
            'user_id' => $user_info_id,
            'line' => __LINE__,
            'next_slip_num' => $slip_num,
        ]);

        return $returnArray;
    }

    /**
     * SP用 仕入伝票追加処理（Ajax）
     */
    public function AjaxAddSlipSp(Request $request)
    {

        $user_info = \Auth::user();
        $user_info_id = $user_info['id'] ?? null;

        \Log::info('AjaxAddSlipSp 開始', [
            'user_id' => $user_info_id,
            'line' => __LINE__,
            'slip_num' => $request->slip_num,
        ]);

        $slipNum = (int) $request->input('slip_num', 1);
        $tabInitialNum = 7 * $slipNum + 2;

        $html = "";
        $html .= "<tr id='slip-partition-{$slipNum}' class='partition-area'></tr>";
        $html .= "<input type='hidden' name='sort-{$slipNum}' id='sort' value='{$slipNum}'>";
        $html .= "<input type='hidden' name='data[SupplySlipDetail][{$slipNum}][id]' id='id-{$slipNum}' value=''>";
        $html .= "<tr id='slip-upper-{$slipNum}'>";
        $html .= "  <td class='index-td-blue' rowspan='6'>{$slipNum}</td>";
        $html .= "  <td colspan='1'>";
        $html .= "    <input type='text' class='form-control product_code_input' id='product_code_{$slipNum}' name='data[SupplySlipDetail][{$slipNum}][product_code]' placeholder='製品コード' tabindex='" . ($tabInitialNum + 1) . "'>";
        $html .= "    <input type='hidden' id='product_id_{$slipNum}' name='data[SupplySlipDetail][{$slipNum}][product_id]'>";
        $html .= "    <input type='hidden' id='tax_id_{$slipNum}' name='data[SupplySlipDetail][{$slipNum}][tax_id]' value='1'>";
        $html .= "  </td>";
        $html .= "  <td colspan='3'>";
        $html .= "    <input type='text' class='form-control' id='product_text_{$slipNum}' name='data[SupplySlipDetail][{$slipNum}][product_text]' placeholder='製品欄' readonly>";
        $html .= "  </td>";
        $html .= "  <td class='remove-btn-td' rowspan='6'>";
        $html .= "    <button id='remove-slip-btn' type='button' class='btn rmv-slip-btn btn-secondary' onclick='javascript:removeSlip({$slipNum})'>削除</button>";
        $html .= "  </td>";
        $html .= "</tr>";

        $html .= "<tr>";
        $html .= "  <td>";
        $html .= "    <input type='number' class='form-control' id='inventory_unit_num_{$slipNum}' name='data[SupplySlipDetail][{$slipNum}][inventory_unit_num]' placeholder='個数' step='0.01' tabindex='" . ($tabInitialNum + 2) . "'>";
        $html .= "  </td>";
        $html .= "  <td>";
        $html .= "    <input type='text' class='form-control' id='inventory_unit_text_{$slipNum}' name='data[SupplySlipDetail][{$slipNum}][inventory_unit_text]' placeholder='個数単位' readonly>";
        $html .= "    <input type='hidden' id='inventory_unit_id_{$slipNum}' name='data[SupplySlipDetail][{$slipNum}][inventory_unit_id]' value='1'>";
        $html .= "  </td>";
        $html .= "  <td>";
        $html .= "    <input type='number' class='form-control' id='unit_num_{$slipNum}' name='data[SupplySlipDetail][{$slipNum}][unit_num]' placeholder='数量' onchange='javascript:priceNumChange({$slipNum})' step='0.01' tabindex='" . ($tabInitialNum + 3) . "'>";
        $html .= "  </td>";
        $html .= "  <td>";
        $html .= "    <input type='text' class='form-control' id='unit_text_{$slipNum}' name='data[SupplySlipDetail][{$slipNum}][unit_text]' placeholder='数量単位' readonly>";
        $html .= "    <input type='hidden' id='unit_id_{$slipNum}' name='data[SupplySlipDetail][{$slipNum}][unit_id]' value='1'>";
        $html .= "  </td>";
        $html .= "</tr>";

        $html .= "<tr>";
        $html .= "  <td colspan='2'>";
        $html .= "    <input type='number' class='form-control' id='unit_price_{$slipNum}' name='data[SupplySlipDetail][{$slipNum}][unit_price]' placeholder='単価' onchange='javascript:priceNumChange({$slipNum})' step='0.01' tabindex='" . ($tabInitialNum + 4) . "'>";
        $html .= "  </td>";
        $html .= "  <td colspan='2'>";
        $html .= "    <input type='text' class='form-control' id='notax_price_{$slipNum}' name='data[SupplySlipDetail][{$slipNum}][notax_price]' placeholder='金額' value='0' readonly>";
        $html .= "  </td>";
        $html .= "</tr>";

        $html .= "<tr>";
        $html .= "  <td colspan='2'>";
        $html .= "    <input type='text' class='form-control origin_area_code_input' id='origin_area_code_{$slipNum}' name='data[SupplySlipDetail][{$slipNum}][origin_area_code]' placeholder='産地コード' tabindex='" . ($tabInitialNum + 5) . "'>";
        $html .= "    <input type='hidden' id='origin_area_id_{$slipNum}' name='data[SupplySlipDetail][{$slipNum}][origin_area_id]'>";
        $html .= "  </td>";
        $html .= "  <td colspan='2'>";
        $html .= "    <input type='text' class='form-control' id='origin_area_text_{$slipNum}' name='data[SupplySlipDetail][{$slipNum}][origin_area_text]' placeholder='産地名' readonly>";
        $html .= "  </td>";
        $html .= "</tr>";

        $html .= "<tr>";
        $html .= "  <td colspan='2'>";
        $html .= "    <input type='text' class='form-control staff_code_input' id='staff_code_{$slipNum}' name='data[SupplySlipDetail][{$slipNum}][staff_code]' placeholder='担当者コード' value='1009' tabindex='" . ($tabInitialNum + 6) . "'>";
        $html .= "    <input type='hidden' id='staff_id_{$slipNum}' name='data[SupplySlipDetail][{$slipNum}][staff_id]' value='9'>";
        $html .= "  </td>";
        $html .= "  <td colspan='2'>";
        $html .= "    <input type='text' class='form-control' id='staff_text_{$slipNum}' name='data[SupplySlipDetail][{$slipNum}][staff_text]' placeholder='担当名' value='石塚 貞雄' readonly>";
        $html .= "  </td>";
        $html .= "</tr>";

        $html .= "<tr>";
        $html .= "  <td colspan='4'>";
        $html .= "    <input type='text' class='form-control' id='memo_{$slipNum}' name='data[SupplySlipDetail][{$slipNum}][memo]' placeholder='摘要欄' tabindex='" . ($tabInitialNum + 7) . "'>";
        $html .= "  </td>";
        $html .= "</tr>";

        // 仕入伝票格納エリア
        $html2 = "<div id='supply-slip-area-{$slipNum}'></div>";

        // オートコンプリート用DOM（Ajax後にappendする）
        $autoProduct = "<input type='text' class='form-control product_code_input' id='product_code_{$slipNum}' name='data[SupplySlipDetail][{$slipNum}][product_code]' tabindex='" . ($tabInitialNum + 1) . "'>";
        $autoOrigin  = "<input type='text' class='form-control origin_area_code_input' id='origin_area_code_{$slipNum}' name='data[SupplySlipDetail][{$slipNum}][origin_area_code]' tabindex='" . ($tabInitialNum + 5) . "'>";
        $autoStaff   = "<input type='text' class='form-control staff_code_input' id='staff_code_{$slipNum}' name='data[SupplySlipDetail][{$slipNum}][staff_code]' value='1009' tabindex='" . ($tabInitialNum + 6) . "'>";

        \Log::info('AjaxAddSlipSp 正常終了', [
            'user_id' => $user_info_id,
            'line' => __LINE__,
            'next_slip_num' => $slipNum + 1,
        ]);

        return Response::json([
            $slipNum + 1,
            $html,
            $html2,
            $autoProduct,
            $autoOrigin,
            $autoStaff
        ]);
    }

    /**
     * 仕入発注単価の取得
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getOrderSupplyUnitPrice(Request $request)
    {

        $user_info = \Auth::user();
        $user_info_id = $user_info['id'] ?? null;

        // パラメータの取得
        $companyId = $request->company_id;
        $productId = $request->product_id;
        $supplyDate = $request->supply_date;

        \Log::info('getOrderSupplyUnitPrice 開始', [
            'user_id'     => $user_info_id,
            'line'        => __LINE__,
            'company_id'  => $companyId,
            'product_id'  => $productId,
            'supply_date' => $supplyDate,
        ]);

        // データの取得
        $orderSupplyUnitPriceDetails = DB::table('order_supply_unit_price_details AS OrderSupplyUnitPriceDetails')
        ->select(
            'OrderSupplyUnitPriceDetails.notax_price AS notax_price'
        )
        ->join('order_supply_unit_prices AS OrderSupplyUnitPrice', function ($join) {
            $join->on('OrderSupplyUnitPriceDetails.order_supply_unit_price_id', '=', 'OrderSupplyUnitPrice.id');
        })
        ->where([
            ['OrderSupplyUnitPriceDetails.apply_from', '<=', $supplyDate],
            ['OrderSupplyUnitPriceDetails.product_id', '=', $productId],
            ['OrderSupplyUnitPrice.company_id', '=', $companyId],
            ['OrderSupplyUnitPriceDetails.active', '=', '1'],
            ['OrderSupplyUnitPrice.active', '=', '1'],
        ])
        ->orderBy('OrderSupplyUnitPriceDetails.apply_from', 'desc')
        ->limit(1)
        ->get();

        $orderSupplyUnitPrice = 0;
        if (isset($orderSupplyUnitPriceDetails[0]->notax_price))
            $orderSupplyUnitPrice = $orderSupplyUnitPriceDetails[0]->notax_price;

        \Log::info('getOrderSupplyUnitPrice 正常終了', [
            'user_id'     => $user_info_id,
            'line'        => __LINE__,
            'unit_price'  => $orderSupplyUnitPrice,
        ]);

        return $orderSupplyUnitPrice;

    }
}
