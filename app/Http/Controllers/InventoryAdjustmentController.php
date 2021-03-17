<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\SupplySlip;
use App\SupplySlipDetail;

class InventoryAdjustmentController extends Controller
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
     * 在庫一覧
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
            $search_action = '../InventoryAdjustmentIndex';
        } else {
            $search_action = './InventoryAdjustmentIndex';
        }

        // postできたか、getできたか
        if ($_SERVER["REQUEST_METHOD"] != "POST"
        ) { // ページング処理

            $condition_date_type    = $request->session()->get('inventory_condition_date_type');
            $condition_date_from    = $request->session()->get('inventory_condition_date_from');
            $condition_date_to      = $request->session()->get('inventory_condition_date_to');
            $condition_company_code = $request->session()->get('inventory_condition_company_code');
            $condition_company_id   = $request->session()->get('inventory_condition_company_id');
            $condition_company_text = $request->session()->get('inventory_condition_company_text');
            $condition_shop_code    = $request->session()->get('inventory_condition_shop_code');
            $condition_shop_id      = $request->session()->get('inventory_condition_shop_id');
            $condition_shop_text    = $request->session()->get('inventory_condition_shop_text');
            $condition_product_code = $request->session()->get('inventory_condition_product_code');
            $condition_product_id   = $request->session()->get('inventory_condition_product_id');
            $condition_product_text = $request->session()->get('inventory_condition_product_text');
            $condition_staff_code   = $request->session()->get('inventory_condition_staff_code');
            $condition_staff_id     = $request->session()->get('inventory_condition_staff_id');
            $condition_staff_text   = $request->session()->get('inventory_condition_staff_text');

        } else { // POST時の処理

            if (isset($_POST['search-btn'])) { // 検索ボタン押された時の処理

                $condition_date_type    = $request->data['InventoryAdjustment']['date_type'];
                $condition_company_code = $request->data['InventoryAdjustment']['supply_company_code'];
                $condition_company_id   = $request->data['InventoryAdjustment']['supply_company_id'];
                $condition_company_text = $request->data['InventoryAdjustment']['supply_company_text'];
                $condition_shop_code    = $request->data['InventoryAdjustment']['supply_shop_code'];
                $condition_shop_id      = $request->data['InventoryAdjustment']['supply_shop_id'];
                $condition_shop_text    = $request->data['InventoryAdjustment']['supply_shop_text'];
                $condition_product_code = $request->data['InventoryAdjustment']['product_code'];
                $condition_product_id   = $request->data['InventoryAdjustment']['product_id'];
                $condition_product_text = $request->data['InventoryAdjustment']['product_text'];
                $condition_staff_code   = $request->data['InventoryAdjustment']['staff_code'];
                $condition_staff_id     = $request->data['InventoryAdjustment']['staff_id'];
                $condition_staff_text   = $request->data['InventoryAdjustment']['staff_text'];

                // 日付の設定
                $condition_date_from    = $request->data['InventoryAdjustment']['supply_date_from'];
                $condition_date_to      = $request->data['InventoryAdjustment']['supply_date_to'];
                // どちらか片方しか入力されなかった場合は同じ日付を入れる
                if (!empty($condition_date_from) && empty($condition_date_to)) {
                    $condition_date_to = $condition_date_from;
                }
                if (empty($condition_date_from) && !empty($condition_date_to)) {
                    $condition_date_from = $condition_date_to;
                }

                $request->session()->put('inventory_condition_date_type', $condition_date_type);
                $request->session()->put('inventory_condition_date_from', $condition_date_from);
                $request->session()->put('inventory_condition_date_to', $condition_date_to);
                $request->session()->put('inventory_condition_company_code', $condition_company_code);
                $request->session()->put('inventory_condition_company_id', $condition_company_id);
                $request->session()->put('inventory_condition_company_text', $condition_company_text);
                $request->session()->put('inventory_condition_shop_code', $condition_shop_code);
                $request->session()->put('inventory_condition_shop_id', $condition_shop_id);
                $request->session()->put('inventory_condition_shop_text', $condition_shop_text);
                $request->session()->put('inventory_condition_product_code', $condition_product_code);
                $request->session()->put('inventory_condition_product_id', $condition_product_id);
                $request->session()->put('inventory_condition_product_text', $condition_product_text);
                $request->session()->put('inventory_condition_staff_code', $condition_staff_code);
                $request->session()->put('inventory_condition_staff_id', $condition_staff_id);
                $request->session()->put('inventory_condition_staff_text', $condition_staff_text);

            } else { // リセットボタンが押された時の処理

                $condition_date_type    = null;
                $condition_date_from    = null;
                $condition_date_to      = null;
                $condition_company_code = null;
                $condition_company_id   = null;
                $condition_company_text = null;
                $condition_shop_code    = null;
                $condition_shop_id      = null;
                $condition_shop_text    = null;
                $condition_product_code = null;
                $condition_product_id   = null;
                $condition_product_text = null;
                $condition_staff_code   = null;
                $condition_staff_id     = null;
                $condition_staff_text   = null;
                $request->session()->forget('inventory_condition_date_type');
                $request->session()->forget('inventory_condition_date_from');
                $request->session()->forget('inventory_condition_date_to');
                $request->session()->forget('inventory_condition_company_code');
                $request->session()->forget('inventory_condition_company_id');
                $request->session()->forget('inventory_condition_company_text');
                $request->session()->forget('inventory_condition_shop_code');
                $request->session()->forget('inventory_condition_shop_id');
                $request->session()->forget('inventory_condition_shop_text');
                $request->session()->forget('inventory_condition_product_code');
                $request->session()->forget('inventory_condition_product_id');
                $request->session()->forget('inventory_condition_product_text');
                $request->session()->forget('inventory_condition_staff_code');
                $request->session()->forget('inventory_condition_staff_id');
                $request->session()->forget('inventory_condition_staff_text');
            }
        }

        // ------------------------
        // 検索条件を元に在庫一覧を取得
        // ------------------------
        try {

            // --------------
            // 在庫一覧抽出SQL
            // --------------
            $supplySlipList = DB::table('supply_slips AS SupplySlip')
            ->select(
                'Product.id    AS product_id',
                'Product.code  AS product_code',
                'Product.name  AS product_name',
                'Standard.id   AS standard_id',
                'Standard.name AS standard_name',
                'Unit.name     AS unit_name'
            )
            ->selectRaw('MIN(SupplySlip.delivery_date) AS oldest_date')
            ->selectRaw('SUM(SupplySlipDetail.unit_num - SupplySlipDetail.consumption) AS remaining_quantity')
            ->selectRaw('SUM((SupplySlipDetail.unit_num - SupplySlipDetail.consumption) * SupplySlipDetail.unit_price) AS balanced_amount')
            ->join('supply_slip_details AS SupplySlipDetail', function ($join){
                $join->on('SupplySlipDetail.supply_slip_id', '=', 'SupplySlip.id');
            })
            ->join('supply_companies AS SupplyCompany', function ($join){
                $join->on('SupplyCompany.id', '=', 'SupplySlip.supply_company_id');
            })
            ->leftJoin('supply_shops AS SupplyShop', function ($join) {
                $join->on('SupplyShop.id', '=', 'SupplySlip.supply_shop_id')
                    ->on('SupplyShop.supply_company_id', '=', 'SupplyCompany.id');
            })
            ->join('products as Product', function ($join) {
                $join->on('Product.id', '=', 'SupplySlipDetail.product_id');
            })
            ->leftjoin('standards as Standard', function ($join) {
                $join->on('Standard.id', '=', 'SupplySlipDetail.standard_id')
                    ->on('Standard.product_id', '=', 'Product.id');
            })
            ->join('units as Unit', function ($join) {
                $join->on('Unit.id', '=', 'SupplySlipDetail.unit_id');
            })
            ->if(!empty($condition_date_from) && !empty($condition_date_to) && $condition_date_type == 1, function ($query) use ($condition_date_from, $condition_date_to) {
                return $query->whereBetween('SupplySlip.date', [$condition_date_from, $condition_date_to]);
            })->if(!empty($condition_date_from) && !empty($condition_date_to) && $condition_date_type == 2, function ($query) use ($condition_date_from, $condition_date_to) {
                return $query->whereBetween('SupplySlip.delivery_date', [$condition_date_from, $condition_date_to]);
            })
            ->if(!empty($condition_company_id), function ($query) use ($condition_company_id) {
                return $query->where('SupplySlip.supply_company_id', '=', $condition_company_id);
            })
            ->if(!empty($condition_shop_id), function ($query) use ($condition_shop_id) {
                return $query->where('SupplySlip.supply_shop_id', '=', $condition_shop_id);
            })
            ->if(!empty($condition_product_id), function ($query) use ($condition_product_id) {
                return $query->where('SupplySlipDetail.product_id', '=', $condition_product_id);
            })
            ->where([
                ['SupplySlip.active', '=', '1'],
            ])
            ->groupBy(
                'SupplySlipDetail.product_id',
                'SupplySlipDetail.standard_id',
                'SupplySlipDetail.unit_id'
            )
            ->havingRaw('SUM(SupplySlipDetail.unit_num - SupplySlipDetail.consumption) > ?', [0])
            ->paginate(5);

            // ---------------------
            // 最新の売上日付を取得する
            // ---------------------
            $conditionArray = array(
                'condition_date_from'  => $condition_date_from,
                'condition_date_to'    => $condition_date_to,
                'condition_date_type'  => $condition_date_type,
                'condition_company_id' => $condition_company_id,
                'condition_shop_id'    => $condition_shop_id,
                'condition_product_id' => $condition_product_id
            );
            $latestDates = $this->getLatestDate($conditionArray);

            // 最新の売上日付を格納していく
            foreach ($supplySlipList as $supplySlipDatas) {
                foreach ($latestDates as $latestDateDatas) {
                    if (($supplySlipDatas->product_id == $latestDateDatas->product_id) &&
                        ($supplySlipDatas->standard_id == $latestDateDatas->standard_id)) {
                            $supplySlipDatas->latest_date = $latestDateDatas->latest_date;
                    }
                }
            }

// error_log(print_r($supplySlipList, true), '3', '/home/gfproject/mizucho.com/public_html/laravel/storage/logs/error.log');
            // 対象日付のチェック
            $check_str_slip_date = "";
            $check_str_deliver_date = "";
            if($condition_date_type == 2) $check_str_deliver_date = "checked";
            else  $check_str_slip_date = "checked";

        } catch (\Exception $e) {

            dd($e);

            return view('InventoryAdjustment.index')->with([
                'errorMessage' => $e
            ]);

        }

        return view('InventoryAdjustment.index')->with([
            'search_action'          => $search_action,
            'check_str_slip_date'    => $check_str_slip_date,
            'check_str_deliver_date' => $check_str_deliver_date,
            'condition_date_from'    => $condition_date_from,
            'condition_date_to'      => $condition_date_to,
            'condition_company_code' => $condition_company_code,
            'condition_company_id'   => $condition_company_id,
            'condition_company_text' => $condition_company_text,
            'condition_shop_code'    => $condition_shop_code,
            'condition_shop_id'      => $condition_shop_id,
            'condition_shop_text'    => $condition_shop_text,
            'condition_product_code' => $condition_product_code,
            'condition_product_id'   => $condition_product_id,
            'condition_product_text' => $condition_product_text,
            'condition_staff_code'   => $condition_staff_code,
            'condition_staff_id'     => $condition_staff_id,
            'condition_staff_text'   => $condition_staff_text,
            'supplySlipList'         => $supplySlipList
        ]);
    }

    /**
     * 在庫詳細画面
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function detail($link_id)
    {
        // リクエストパラメータの分解
        $params = explode('_', $link_id);
        $productId  = $params[0];   // 製品ID
        $standardId = $params[1];   // 規格ID
        $companyId  = $params[2];   // 企業ID
        $shopId     = $params[3];   // 店舗ID
        $staffId    = $params[4];   // スタッフID
        $dateType   = $params[5];   // 日付タイプ
        $fromDate   = $params[6];   // 開始日付
        $toDate     = $params[7];   // 終了日付

        // ------------------------
        // 画面表示用の詳細データの取得
        // ------------------------
        try {

            $supplySlipList = DB::table('supply_slips AS SupplySlip')
            ->select(
                'SupplySlipDetail.id         AS supply_slip_detail_id',
                'SupplySlip.id               AS supply_slip_id',
                'SupplySlip.date             AS supply_slip_date',
                'SupplySlip.delivery_date    AS delivery_date',
                'SupplyCompany.code          AS company_code',
                'SupplyCompany.name          AS company_name',
                'SupplyShop.code             AS shop_code',
                'SupplyShop.name             AS shop_name',
                'SupplySlipDetail.unit_price AS unit_price',
                'Product.id                  AS product_id',
                'Product.code                AS product_code',
                'Product.name                AS product_name',
                'Standard.id                 AS standard_id',
                'Standard.code               AS standard_code',
                'Standard.name               AS standard_name',
                'Unit.name                   AS unit_name'
            )
            ->selectRaw('(SupplySlipDetail.unit_num - SupplySlipDetail.consumption) AS remaining_quantity')
            ->selectRaw('SUM((SupplySlipDetail.unit_num - SupplySlipDetail.consumption) * SupplySlipDetail.unit_price) AS balanced_amount')
            ->selectRaw('CONCAT(Staff.name_sei," ",Staff.name_mei) AS staff_name')
            ->join('supply_slip_details AS SupplySlipDetail', function ($join){
                $join->on('SupplySlipDetail.supply_slip_id', '=', 'SupplySlip.id');
            })
            ->join('supply_companies AS SupplyCompany', function ($join){
                $join->on('SupplyCompany.id', '=', 'SupplySlip.supply_company_id');
            })
            ->leftJoin('supply_shops AS SupplyShop', function ($join) {
                $join->on('SupplyShop.id', '=', 'SupplySlip.supply_shop_id')
                    ->on('SupplyShop.supply_company_id', '=', 'SupplyCompany.id');
            })
            ->join('products as Product', function ($join) {
                $join->on('Product.id', '=', 'SupplySlipDetail.product_id');
            })
            ->leftjoin('standards as Standard', function ($join) {
                $join->on('Standard.id', '=', 'SupplySlipDetail.standard_id')
                    ->on('Standard.product_id', '=', 'Product.id');
            })
            ->join('units as Unit', function ($join) {
                $join->on('Unit.id', '=', 'SupplySlipDetail.unit_id');
            })
            ->leftJoin('inventory_manages as InventoryManage', function ($join) {
                $join->on('InventoryManage.supply_detail_slip_id', '=', 'SupplySlipDetail.id');
            })
            ->leftJoin('sale_slip_details as SaleSlipDetail', function ($join) {
                $join->on('InventoryManage.sale_detail_slip_id', '=', 'SaleSlipDetail.id');
            })
            ->leftJoin('sale_slips as SaleSlip', function ($join) {
                $join->on('SaleSlipDetail.sale_slip_id', '=', 'SaleSlip.id');
            })
            ->join('staffs as Staff', function ($join) {
                $join->on('Staff.id', '=', 'SupplySlipDetail.staff_id');
            })
            ->if(!empty($fromDate) && !empty($toDate) && $dateType == 1, function ($query) use ($fromDate, $toDate) {
                return $query->whereBetween('SupplySlip.date', [$fromDate, $toDate]);
            })->if(!empty($fromDate) && !empty($toDate) && $dateType == 2, function ($query) use ($fromDate, $toDate) {
                return $query->whereBetween('SupplySlip.delivery_date', [$fromDate, $toDate]);
            })
            ->if(!empty($companyId), function ($query) use ($companyId) {
                return $query->where('SupplySlip.supply_company_id', '=', $companyId);
            })
            ->if(!empty($shopId), function ($query) use ($shopId) {
                return $query->where('SupplySlip.supply_shop_id', '=', $shopId);
            })
            ->if(!empty($productId), function ($query) use ($productId) {
                return $query->where('SupplySlipDetail.product_id', '=', $productId);
            })
            ->if($standardId != '', function ($query) use ($standardId) {
                if ($standardId != 0) {
                    return $query->where('SupplySlipDetail.standard_id', '=', $standardId);
                } else {
                    return $query->whereNull('SupplySlipDetail.standard_id');
                }
            })
            ->if(!empty($staffId), function ($query) use ($staffId) {
                return $query->where('SupplySlipDetail.staff_id', '=', $staffId);
            })
            ->where([
                ['SupplySlip.active', '=', '1'],
            ])
            ->groupBy(
                'SupplySlipDetail.id'
            )
            ->orderBy('SupplySlip.id')
            ->get();

            // error_log(print_r($supplySlipList, true), '3', '/home/gfproject/mizucho.com/public_html/laravel/storage/logs/error.log');

            // 履歴データを取得するために詳細テーブルIDをまとめる
            $supplySlipDetailIds = array();
            foreach ($supplySlipList as $supplySlipDatas) {
                if ($supplySlipDatas->remaining_quantity > 0) {
                    $supplySlipDetailIds[] = $supplySlipDatas->supply_slip_detail_id;
                }
            }

            // ------------------
            // 在庫履歴データの取得
            // ------------------
            $inventoryManageList = DB::table('inventory_manages AS InventoryManage')
            ->select(
                'SaleSlip.id                    AS sale_slip_id',
                'SaleSlip.date                  AS sale_slip_date',
                'SaleSlip.delivery_date         AS sale_delivery_date',
                'SaleSlipDetail.unit_price      AS sale_unit_price',
                'SupplySlip.id                  AS supply_slip_id',
                'SupplySlip.date                AS supply_slip_date',
                'SupplySlip.delivery_date       AS supply_delivery_date',
                'SupplySlipDetail.unit_price    AS supply_unit_price',
                'SaleCompany.code               AS sale_company_code',
                'SaleCompany.name               AS sale_company_name',
                'SupplyCompany.code             AS supply_company_code',
                'SupplyCompany.name             AS supply_company_name',
                'SaleUnit.name                  AS sale_unit_name',
                'SupplyUnit.name                AS supply_unit_name',
                'Product.code                   AS product_code',
                'Product.name                   AS product_name',
                'Standard.code                  AS standard_code',
                'Standard.name                  AS standard_name',
                'InventoryManage.unit_num       AS inventory_unit_num',
                'InventoryManage.inventory_type AS inventory_type'
            )
            ->selectRaw('SUM(SupplySlipDetail.unit_num - SupplySlipDetail.consumption) AS remaining_quantity')
            ->selectRaw('SUM((SupplySlipDetail.unit_num - SupplySlipDetail.consumption) * SupplySlipDetail.unit_price) AS balanced_amount')
            ->selectRaw('CONCAT(SaleStaff.name_sei," ",SaleStaff.name_mei) AS sale_staff_name')
            ->selectRaw('CONCAT(SupplyStaff.name_sei," ",SupplyStaff.name_mei) AS supply_staff_name')
            ->join('supply_slip_details AS SupplySlipDetail', function ($join){
                $join->on('SupplySlipDetail.id', '=', 'InventoryManage.supply_detail_slip_id');
            })
            ->join('supply_slips AS SupplySlip', function ($join){
                $join->on('SupplySlip.id', '=', 'SupplySlipDetail.supply_slip_id');
            })
            ->join('supply_companies AS SupplyCompany', function ($join){
                $join->on('SupplyCompany.id', '=', 'SupplySlip.supply_company_id');
            })
            ->leftJoin('sale_slip_details AS SaleSlipDetail', function ($join){
                $join->on('SaleSlipDetail.id', '=', 'InventoryManage.sale_detail_slip_id');
            })
            ->leftJoin('sale_slips AS SaleSlip', function ($join){
                $join->on('SaleSlip.id', '=', 'SaleSlipDetail.sale_slip_id');
            })
            ->leftJoin('sale_companies AS SaleCompany', function ($join){
                $join->on('SaleCompany.id', '=', 'SaleSlip.sale_company_id');
            })
            ->join('products as Product', function ($join) {
                $join->on('Product.id', '=', 'SupplySlipDetail.product_id');
            })
            ->leftjoin('standards as Standard', function ($join) {
                $join->on('Standard.id', '=', 'SupplySlipDetail.standard_id')
                    ->on('Standard.product_id', '=', 'Product.id');
            })
            ->leftJoin('staffs AS SaleStaff', function ($join){
                $join->on('SaleStaff.id', '=', 'SaleSlipDetail.staff_id');
            })
            ->join('staffs AS SupplyStaff', function ($join){
                $join->on('SupplyStaff.id', '=', 'SupplySlipDetail.staff_id');
            })
            ->leftJoin('units AS SaleUnit', function ($join) {
                $join->on('SaleUnit.id', '=', 'SaleSlipDetail.unit_id');
            })
            ->leftJoin('units AS SupplyUnit', function ($join) {
                $join->on('SupplyUnit.id', '=', 'SupplySlipDetail.unit_id');
            })
            ->whereIn('SupplySlipDetail.id', $supplySlipDetailIds)
            ->groupBy(
                'InventoryManage.id'
            )
            ->orderBy('InventoryManage.id')
            ->get();

            // 在庫数の計算
            $totalInventoryNum = 0;
            foreach ($inventoryManageList as $key => $inventoryManageDatas) {
                // 仕入、売上、在庫調整で計算分ける
                if ($inventoryManageDatas->inventory_type == 1) {
                    // 仕入数
                    $totalInventoryNum += $inventoryManageDatas->inventory_unit_num;
                } elseif ($inventoryManageDatas->inventory_type == 2) {
                    // 売上数
                    $totalInventoryNum -= $inventoryManageDatas->inventory_unit_num;
                } else {
                    // 在庫調整数
                    $totalInventoryNum -= $inventoryManageDatas->inventory_unit_num;
                }
                // 残金計算
                $totalInventoryPrice = $totalInventoryNum * $inventoryManageDatas->supply_unit_price;
                // 値を入れていく
                $inventoryManageList[$key]->total_inventory_num   = $totalInventoryNum;
                $inventoryManageList[$key]->total_inventory_price = $totalInventoryPrice;
            }

            // 配列型に入れ替え
            $inventoryManageArray = array();
            foreach ($inventoryManageList as $key => $inventoryManageDatas) {
                $inventoryManageArray[$key] = $inventoryManageDatas;
            }

            // 降順に並び替え
            krsort($inventoryManageArray);

            // 100件分取得
            $sliceInventoryManageList = array_slice($inventoryManageArray, 0, 100);


// error_log(print_r($inventoryManageList, true), '3', '/home/gfproject/mizucho.com/public_html/laravel/storage/logs/error.log');
        } catch (\Exception $e) {

            dd($e);

            return view('InventoryAdjustment.index')->with([
                'errorMessage' => $e
            ]);

        }

        return view('InventoryAdjustment.detail')->with([
            'supplySlipList'      => $supplySlipList,
            'inventoryManageList' => $sliceInventoryManageList,
        ]);

    }

    /**
     * 在庫調整画面
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function edit(Request $request)
    {

        // -------------------
        // 仕入詳細のデータを取得
        // -------------------
        $supplySlipDetailIds = array();
        foreach ($request->data['SupplySlipDetail'] as $key => $supplySlipDatas) {
            $supplySlipDetailIds[] = $key;
        }

        try {

            $supplySlipList = DB::table('supply_slips AS SupplySlip')
            ->select(
                'SupplySlipDetail.id         AS supply_slip_detail_id',
                'SupplySlip.id               AS supply_slip_id',
                'SupplySlip.date             AS supply_slip_date',
                'SupplySlip.delivery_date    AS delivery_date',
                'SupplyCompany.code          AS company_code',
                'SupplyCompany.name          AS company_name',
                'SupplyShop.code             AS shop_code',
                'SupplyShop.name             AS shop_name',
                'SupplySlipDetail.unit_price AS unit_price',
                'Product.id                  AS product_id',
                'Product.code                AS product_code',
                'Product.name                AS product_name',
                'Standard.id                 AS standard_id',
                'Standard.code               AS standard_code',
                'Standard.name               AS standard_name',
                'Unit.name                   AS unit_name'
            )
            ->selectRaw('SUM(SupplySlipDetail.unit_num - SupplySlipDetail.consumption) AS remaining_quantity')
            ->selectRaw('SUM((SupplySlipDetail.unit_num - SupplySlipDetail.consumption) * SupplySlipDetail.unit_price) AS balanced_amount')
            ->selectRaw('CONCAT(Staff.name_sei," ",Staff.name_mei) AS staff_name')
            ->join('supply_slip_details AS SupplySlipDetail', function ($join){
                $join->on('SupplySlipDetail.supply_slip_id', '=', 'SupplySlip.id');
            })
            ->join('supply_companies AS SupplyCompany', function ($join){
                $join->on('SupplyCompany.id', '=', 'SupplySlip.supply_company_id');
            })
            ->leftJoin('supply_shops AS SupplyShop', function ($join) {
                $join->on('SupplyShop.id', '=', 'SupplySlip.supply_shop_id')
                    ->on('SupplyShop.supply_company_id', '=', 'SupplyCompany.id');
            })
            ->join('products as Product', function ($join) {
                $join->on('Product.id', '=', 'SupplySlipDetail.product_id');
            })
            ->leftjoin('standards as Standard', function ($join) {
                $join->on('Standard.id', '=', 'SupplySlipDetail.standard_id')
                    ->on('Standard.product_id', '=', 'Product.id');
            })
            ->join('units as Unit', function ($join) {
                $join->on('Unit.id', '=', 'SupplySlipDetail.unit_id');
            })
            ->leftJoin('inventory_manages as InventoryManage', function ($join) {
                $join->on('InventoryManage.supply_detail_slip_id', '=', 'SupplySlipDetail.id');
            })
            ->leftJoin('sale_slip_details as SaleSlipDetail', function ($join) {
                $join->on('InventoryManage.sale_detail_slip_id', '=', 'SaleSlipDetail.id');
            })
            ->leftJoin('sale_slips as SaleSlip', function ($join) {
                $join->on('SaleSlipDetail.sale_slip_id', '=', 'SaleSlip.id');
            })
            ->join('staffs as Staff', function ($join) {
                $join->on('Staff.id', '=', 'SupplySlipDetail.staff_id');
            })
            ->whereIn('SupplySlipDetail.id', $supplySlipDetailIds)
            ->groupBy(
                'SupplySlipDetail.id'
            )
            ->orderBy('SupplySlip.id')
            ->get();

            // error_log(print_r($supplySlipList, true), '3', '/home/gfproject/mizucho.com/public_html/laravel/storage/logs/error.log');

        } catch (\Exception $e) {

            dd($e);

            return view('InventoryAdjustment.index')->with([
                'errorMessage' => $e
            ]);

        }

        // error_log(print_r($supplySlipDetailIds, true), '3', '/home/gfproject/mizucho.com/public_html/laravel/storage/logs/error.log');

        return view('InventoryAdjustment.edit')->with([
            'supplySlipList' => $supplySlipList,
        ]);
    }

    /**
     * 在庫調整確認画面
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function confirm(Request $request)
    {

        // チェックされているものだけを確認画面に表示させる
        $inventoryManageDatas = $request->data['InventoryManage'];

        $staffCode = $inventoryManageDatas['staff_code'];
        $staffId   = $inventoryManageDatas['staff_id'];
        $staffText = $inventoryManageDatas['staff_text'];
        $reason    = $inventoryManageDatas['reason'];
        $memo      = $inventoryManageDatas['memo'];

        foreach ($inventoryManageDatas as $key => $inventoryManageData) {
            if (!isset($inventoryManageData['check'])) {
                unset($inventoryManageDatas[$key]);
            }
        }
// error_log(print_r($inventoryManageDatas, true), '3', '/home/gfproject/mizucho.com/public_html/laravel/storage/logs/error.log');

        return view('InventoryAdjustment.confirm')->with([
            'inventoryManageDatas' => $inventoryManageDatas,
            'staffCode'            => $staffCode,
            'staffId'              => $staffId,
            'staffText'            => $staffText,
            'reason'               => $reason,
            'memo'                 => $memo
        ]);
    }

    /**
     * 在庫調整登録処理
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function editInventoryAdjustment(Request $request) {

        // リクエストパラメータの取得
        $inventoryManageDatas  = $request->data["InventoryManage"];
        $inventoryManageCommon = $request->data["InventoryManageCommon"];

        // ユーザー情報の取得
        $user_info    = \Auth::user();
        $user_info_id = $user_info['id'];

        // トランザクション開始
        DB::connection()->beginTransaction();

        try {

            // ---------------------
            // 登録するパラメータの作成
            // ---------------------
            $insertParams = array();
            foreach ($inventoryManageDatas as $supplySlipDetailId => $inventoryManageData) {
                $insertParams[] = array(
                    'supply_detail_slip_id' => $supplySlipDetailId,
                    'inventory_type'        => 3,
                    'unit_num'              => $inventoryManageData['unit_num'],
                    'remarks'               => $inventoryManageCommon['memo'],
                    'sort'                  => 0,
                    'created_user_id'       => $user_info_id,
                    'created'               => Carbon::now(),
                    'modified_user_id'      => $user_info_id,
                    'modified'              => Carbon::now()
                );
            }

            // 在庫テーブルにインサート
            if (!empty($insertParams)) {
                DB::table('inventory_manages')->insert($insertParams);
            }

            // ----------------------
            // 仕入詳細伝票の消費数を更新
            // ----------------------
            foreach ($inventoryManageDatas as $supplySlipDetailId => $inventoryManageData) {

                // 対象の仕入伝票を取得
                $supplySlipDetailList = DB::table('supply_slip_details AS SupplySlipDetail')
                ->select(
                    'SupplySlipDetail.id           AS supply_slip_detail_id',
                    'SupplySlipDetail.unit_num     AS unit_num',
                    'SupplySlipDetail.consumption  AS consumption'
                )
                ->where([
                        ['SupplySlipDetail.id', '=', $supplySlipDetailId],
                        ['SupplySlipDetail.active', '=', 1],
                ])->first();

                // 消費数を追加
                $consumption = floatval($supplySlipDetailList->consumption) + $inventoryManageData['unit_num'];

                // 仕入伝票登録を登録する
                $SupplySlipDetail = \App\SupplySlipDetail::find($supplySlipDetailId);
                $SupplySlipDetail->consumption      = $consumption;
                $SupplySlipDetail->modified_user_id = $user_info_id;
                $SupplySlipDetail->modified         = Carbon::now();
                $SupplySlipDetail->save();

            }

            // 問題なければコミット
            DB::connection()->commit();

        } catch (\Exception $e) {

            DB::rollback();

            dd($e);

        }

        return redirect('./InventoryAdjustmentIndex');

    }

    /**
     * 在庫一覧の最新売上日を取得する
     * 
     * @param array conditionArray
     */
    private function getLatestDate($conditionArray) {

        $condition_date_from  = $conditionArray['condition_date_from'];
        $condition_date_to    = $conditionArray['condition_date_to'];
        $condition_date_type  = $conditionArray['condition_date_type'];
        $condition_company_id = $conditionArray['condition_company_id'];
        $condition_shop_id    = $conditionArray['condition_shop_id'];
        $condition_product_id = $conditionArray['condition_product_id'];

        $supplySlipList = DB::table('supply_slips AS SupplySlip')
            ->select(
                'Product.id    AS product_id',
                'Standard.id   AS standard_id'
            )
            ->selectRaw('MAX(SaleSlep.date) AS latest_date')
            ->join('supply_slip_details AS SupplySlipDetail', function ($join){
                $join->on('SupplySlipDetail.supply_slip_id', '=', 'SupplySlip.id');
            })
            ->join('supply_companies AS SupplyCompany', function ($join){
                $join->on('SupplyCompany.id', '=', 'SupplySlip.supply_company_id');
            })
            ->leftJoin('supply_shops AS SupplyShop', function ($join) {
                $join->on('SupplyShop.id', '=', 'SupplySlip.supply_shop_id')
                    ->on('SupplyShop.supply_company_id', '=', 'SupplyCompany.id');
            })
            ->join('products as Product', function ($join) {
                $join->on('Product.id', '=', 'SupplySlipDetail.product_id');
            })
            ->leftjoin('standards as Standard', function ($join) {
                $join->on('Standard.id', '=', 'SupplySlipDetail.standard_id')
                    ->on('Standard.product_id', '=', 'Product.id');
            })
            ->join('units as Unit', function ($join) {
                $join->on('Unit.id', '=', 'SupplySlipDetail.unit_id');
            })
            ->leftJoin('inventory_manages as InventoryManage', function ($join) {
                $join->on('InventoryManage.supply_detail_slip_id', '=', 'SupplySlipDetail.id');
            })
            ->leftJoin('sale_slip_details as SaleSlipDetail', function ($join) {
                $join->on('InventoryManage.sale_detail_slip_id', '=', 'SaleSlipDetail.id');
            })
            ->leftJoin('sale_slips as SaleSlep', function ($join) {
                $join->on('SaleSlipDetail.sale_slip_id', '=', 'SaleSlep.id');
            })
            ->if(!empty($condition_date_from) && !empty($condition_date_to) && $condition_date_type == 1, function ($query) use ($condition_date_from, $condition_date_to) {
                return $query->whereBetween('SupplySlip.date', [$condition_date_from, $condition_date_to]);
            })->if(!empty($condition_date_from) && !empty($condition_date_to) && $condition_date_type == 2, function ($query) use ($condition_date_from, $condition_date_to) {
                return $query->whereBetween('SupplySlip.delivery_date', [$condition_date_from, $condition_date_to]);
            })
            ->if(!empty($condition_company_id), function ($query) use ($condition_company_id) {
                return $query->where('SupplySlip.supply_company_id', '=', $condition_company_id);
            })
            ->if(!empty($condition_shop_id), function ($query) use ($condition_shop_id) {
                return $query->where('SupplySlip.supply_shop_id', '=', $condition_shop_id);
            })
            ->if(!empty($condition_product_id), function ($query) use ($condition_product_id) {
                return $query->where('SupplySlipDetail.product_id', '=', $condition_product_id);
            })
            ->where([
                ['SupplySlip.active', '=', '1'],
            ])
            ->groupBy(
                'SupplySlipDetail.product_id',
                'SupplySlipDetail.standard_id',
                'SupplySlipDetail.unit_id'
            )
            ->havingRaw('SUM(SupplySlipDetail.unit_num - SupplySlipDetail.consumption) > ?', [0])
            ->get();

            return $supplySlipList;

    }
}