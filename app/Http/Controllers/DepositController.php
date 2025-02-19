<?php

namespace App\Http\Controllers;

use App\CompanySetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DepositController extends Controller
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
     * 請求一覧
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
            $search_action = '../DepositIndex';
        } else {
            $search_action = './DepositIndex';
        }

        // postできたか、getできたか
        if ($_SERVER["REQUEST_METHOD"] != "POST"
        ) { // ページング処理

            $condition_id             = $request->session()->get('deposit_condition_id');
            $condition_date_type      = $request->session()->get('deposit_condition_date_from');
            $condition_date_from      = $request->session()->get('deposit_condition_date_from');
            $condition_date_to        = $request->session()->get('deposit_condition_date_to');
            $condition_company_code   = $request->session()->get('deposit_condition_company_code');
            $condition_company_id     = $request->session()->get('deposit_condition_company_id');
            $condition_company_text   = $request->session()->get('deposit_condition_company_text');

        } else { // POST時の処理

            if (isset($_POST['search-btn'])) { // 検索ボタン押された時の処理

                $condition_id           = $request->data['Deposit']['id'];
                $condition_date_type    = $request->data['Deposit']['date_type'];
                $condition_company_code = $request->data['Deposit']['deposit_company_code'];
                $condition_company_id   = $request->data['Deposit']['deposit_company_id'];
                $condition_company_text = $request->data['Deposit']['deposit_company_text'];

                // 日付の設定
                $condition_date_from = $request->data['Deposit']['deposit_date_from'];
                $condition_date_to   = $request->data['Deposit']['deposit_date_to'];
                // どちらか片方しか入力されなかった場合は同じ日付を入れる
                if (!empty($condition_date_from) && empty($condition_date_to)) {
                    $condition_date_to = $condition_date_from;
                }
                if (empty($condition_date_from) && !empty($condition_date_to)) {
                    $condition_date_from = $condition_date_to;
                }

                $request->session()->put('deposit_condition_id', $condition_id);
                $request->session()->put('deposit_condition_date_type', $condition_date_type);
                $request->session()->put('deposit_condition_date_from', $condition_date_from);
                $request->session()->put('deposit_condition_date_to', $condition_date_to);
                $request->session()->put('deposit_condition_company_code', $condition_company_code);
                $request->session()->put('deposit_condition_company_id', $condition_company_id);
                $request->session()->put('deposit_condition_company_text', $condition_company_text);

            } else { // リセットボタンが押された時の処理

                $condition_id             = null;
                $condition_date_type      = null;
                $condition_date_from      = null;
                $condition_date_to        = null;
                $condition_sale_date_from = null;
                $condition_sale_date_to   = null;
                $condition_company_code   = null;
                $condition_company_id     = null;
                $condition_company_text   = null;
                $request->session()->forget('deposit_condition_id');
                $request->session()->forget('deposit_condition_date_type');
                $request->session()->forget('deposit_condition_date_from');
                $request->session()->forget('deposit_condition_date_to');
                $request->session()->forget('deposit_condition_sale_date_from');
                $request->session()->forget('deposit_condition_sale_date_to');
                $request->session()->forget('deposit_condition_company_code');
                $request->session()->forget('deposit_condition_company_id');
                $request->session()->forget('deposit_condition_company_text');

            }
        }

        try {

            // 請求一覧を取得
            $depositList = DB::table('deposits AS Deposit')
            ->select(
                'Deposit.id                  AS deposit_id',
                'Deposit.date                AS deposit_date',
                'Deposit.sale_from_date      AS sale_from_date',
                'Deposit.sale_to_date        AS sale_to_date',
                'Deposit.amount              AS amount',
                'Deposit.deposit_submit_type AS deposit_submit_type',
                'SaleCompany.name            AS sale_company_name'
            )
            ->join('sale_companies AS SaleCompany', function ($join) {
                $join->on('SaleCompany.id', '=', 'Deposit.sale_company_id');
            })
            ->if(!empty($condition_date_from) && !empty($condition_date_to) && $condition_date_type == 1, function ($query) use ($condition_date_from, $condition_date_to) {
                return $query->whereBetween('Deposit.date', [$condition_date_from, $condition_date_to]);
            })
            ->if(!empty($condition_date_from) && $condition_date_type == 2, function ($query) use ($condition_date_from) {
                return $query->where('Deposit.sale_from_date', '>=', $condition_date_from);
            })
            ->if(!empty($condition_date_to) && $condition_date_type == 2, function ($query) use ($condition_date_to) {
                return $query->where('Deposit.sale_to_date', '<=', $condition_date_to);
            })
            ->if(!empty($condition_company_id), function ($query) use ($condition_company_id) {
                return $query->where('Deposit.sale_company_id', '=', $condition_company_id);
            })
            ->if(!empty($condition_id), function ($query) use ($condition_id) {
                return $query->where('Deposit.id', '=', $condition_id);
            })
            ->where('Deposit.active', '=', '1')
            ->orderBy('Deposit.date', 'desc')
            ->orderBy('Deposit.id', 'desc')
            ->paginate(20);

        } catch (\Exception $e) {

            dd($e);

            return view('Deposit.index')->with([
                'errorMessage' => $e
            ]);
        }

        // 対象日付のチェック
        $check_str_slip_date = "";
        $check_str_deposit_date = "";
        if($condition_date_type == 2) $check_str_deposit_date = "checked";
        else  $check_str_slip_date = "checked";

        return view('Deposit.index')->with([
            "search_action"            => $search_action,
            "check_str_slip_date"      => $check_str_slip_date,
            "check_str_deposit_date"   => $check_str_deposit_date,
            "condition_id"             => $condition_id,
            "condition_date_from"      => $condition_date_from,
            "condition_date_to"        => $condition_date_to,
            "condition_company_code"   => $condition_company_code,
            "condition_company_id"     => $condition_company_id,
            "condition_company_text"   => $condition_company_text,
            "depositList"              => $depositList
        ]);
    }

    /**
     * 請求登録画面
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function create(Request $request)
    {
        return view('Deposit.create');
    }

    /**
     * 請求登録処理
     *
     */
    public function registerDeposit(Request $request) {

        // トランザクション開始
        DB::connection()->beginTransaction();

        try {

            // ユーザー情報の取得
            $user_info    = \Auth::user();
            $user_info_id = $user_info['id'];

            // リクエストパラメータ取得
            $depositDatas       = $request->data['Deposit'];
            $depositDetailDatas = $request->data['DepositDetail'];

            // 入力値を配列に格納する
            $insertParams = array(
                'sale_company_id'     => $depositDatas['deposit_company_id'],
                'sale_shop_id'        => $depositDatas['deposit_shop_id'],
                //'date'                => $depositDatas['deposit_date'],
                'date'                => $depositDatas['payment_date'],// 入金日付には支払期限を入れる
                'sale_from_date'      => $depositDatas['sales_from_date'],
                'sale_to_date'        => $depositDatas['sales_to_date'],
                'payment_date'        => $depositDatas['payment_date'],
                'staff_id'            => $depositDatas['staff_id'],
                'sub_total'           => $depositDatas['price'],
                'adjustment_amount'   => $depositDatas['adjustment_price'],
                'amount'              => $depositDatas['total_price'],
                'deposit_method_id'   => $depositDatas['deposit_method_id'],
                'remarks'             => $depositDatas['memo'],
                'deposit_submit_type' => $depositDatas['deposit_submit_type'],
                'created_user_id'     => $user_info_id,
                'created'             => Carbon::now(),
                'modified_user_id'    => $user_info_id,
                'modified'            => Carbon::now()
            );

            $depositNewId = DB::table('deposits')->insertGetId($insertParams);

            // --------------------------------------
            // 詳細テーブルに登録する(deposit_withdrawal_details)
            // --------------------------------------
            // sale_slip_idの数ループさせる
            foreach ($depositDetailDatas['sale_slip_ids'] as $saleSlipId) {

                // 仕入伝票データ取得
                $saleSlipDate    = $depositDetailDatas[$saleSlipId]['date'];
                $notaxSubTotal8  = $depositDetailDatas[$saleSlipId]['notax_subTotal_8'];
                $notaxSubTotal10 = $depositDetailDatas[$saleSlipId]['notax_subTotal_10'];
                $subTotal        = $depositDetailDatas[$saleSlipId]['subTotal'];
                $deliveryPrice   = $depositDetailDatas[$saleSlipId]['delivery_price'];
                $adjustPrice     = $depositDetailDatas[$saleSlipId]['adjust_price'];
                $total           = $depositDetailDatas[$saleSlipId]['total'];

                // 登録データ格納
                $insertDetailParams[] = array(
                    'deposit_withdrawal_id'   => $depositNewId,
                    'supply_sale_slip_id'     => $saleSlipId,
                    //'deposit_withdrawal_date' => $depositDatas['deposit_date'],
                    'deposit_withdrawal_date' => $depositDatas['payment_date'], // 入金日付には支払期限を入れる
                    'supply_sale_slip_date'   => $saleSlipDate,
                    'type'                    => 2,
                    'notax_sub_total_8'       => $notaxSubTotal8,
                    'notax_sub_total_10'      => $notaxSubTotal10,
                    'sub_total'               => $subTotal,
                    'delivery_price'          => $deliveryPrice,
                    'adjust_price'            => $adjustPrice,
                    'total'                   => $total,
                    'active'                  => 1,
                    'created_user_id'         => $user_info_id,
                    'created'                 => Carbon::now(),
                    'modified_user_id'        => $user_info_id,
                    'modified'                => Carbon::now(),
                );
            }

            if (!empty($insertDetailParams)) {
                DB::table('deposit_withdrawal_details')->insert($insertDetailParams);
            }

            // -----------------------------
            // 売上データのフラグを売上済みにする
            // -----------------------------
            DB::table('sale_slips')
            ->whereIn('id', $depositDetailDatas['sale_slip_ids'])
            ->update(array('sale_flg' => 1));

            // 問題なければコミット
            DB::connection()->commit();

        } catch (\Exception $e) {

            DB::rollback();

            dd($e);
        }

        return redirect('./DepositIndex');
    }

    /**
     * 入金編集
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function edit($deposit_id)
    {
        // 出金データ取得
        $depositDatas = DB::table('deposits AS Deposit')
        ->select(
            'Deposit.id                  AS deposit_id',
            'Deposit.date                AS deposit_date',
            'Deposit.sale_from_date      AS sale_from_date',
            'Deposit.sale_to_date        AS sale_to_date',
            'Deposit.payment_date        AS payment_date',
            'Deposit.sub_total           AS sub_total',
            'Deposit.adjustment_amount   AS adjustment_amount',
            'Deposit.amount              AS amount',
            'Deposit.deposit_method_id   AS deposit_method_id',
            'Deposit.staff_id            AS staff_id',
            'Deposit.remarks             AS remarks',
            'Deposit.deposit_submit_type AS deposit_submit_type',
            'Deposit.sale_company_id     AS sale_company_id',
            'Deposit.sale_shop_id        AS sale_shop_id',
            'SaleCompany.name            AS sale_company_name',
            'SaleCompany.code            AS sale_company_code',
            'SaleCompany.tax_calc_type   AS sale_company_tax_calc_type',
            'SaleShop.name               AS sale_shop_name',
            'SaleShop.code               AS sale_shop_code',
            'Staff.code                  AS staff_code'
        )
        ->selectRaw('CONCAT(Staff.name_sei," ",Staff.name_mei) AS staff_name')
        ->join('sale_companies AS SaleCompany', function ($join) {
            $join->on('SaleCompany.id', '=', 'Deposit.sale_company_id');
        })
        ->leftJoin('sale_shops as SaleShop', function ($join) {
            $join->on('SaleShop.id', '=', 'Deposit.sale_shop_id')
                 ->where('SaleShop.active', '=', true);
        })
        ->join('staffs as Staff', function ($join) {
            $join->on('Staff.id', '=', 'Deposit.staff_id')
                 ->where('Staff.active', '=', true);
        })
        ->where([
            ['Deposit.id', '=', $deposit_id],
            ['Deposit.active', '=', '1']
        ])
        ->first();

        // 仕入データ取得
        $saleSlipList = DB::table('sale_slips AS SaleSlip')
        ->select(
            'SaleSlip.id As id',
            'SaleSlip.date As date',
            'SaleSlip.notax_sub_total_8 As notax_sub_total_8',
            'SaleSlip.notax_sub_total_10 As notax_sub_total_10',
            'SaleSlip.delivery_price As delivery_price',
            'SaleSlip.adjust_price As adjust_price',
            'SaleSlip.sale_flg As sale_flg'
        )
        ->whereBetween('SaleSlip.date', [$depositDatas->sale_from_date, $depositDatas->sale_to_date])
        ->where([
            ['SaleSlip.sale_company_id', '=', $depositDatas->sale_company_id],
            ['SaleSlip.active', '=', '1']
        ])
        ->get();

        // 出金詳細データ取得
        $depositDetailDatas = DB::table('deposit_withdrawal_details AS DepositWithdrawalDetail')
        ->select(
            'DepositWithdrawalDetail.supply_sale_slip_id As sale_slip_id'
        )
        ->where([
            ['DepositWithdrawalDetail.deposit_withdrawal_id', '=', $deposit_id],
            ['DepositWithdrawalDetail.type', '=', '2'],
            ['DepositWithdrawalDetail.active', '=', '1']
        ])
        ->get();

        return view('Deposit.edit')->with([
            'depositDatas'       => $depositDatas,
            'saleSlipDatas'      => $saleSlipList,
            'depositDetailDatas' => $depositDetailDatas,
        ]);
    }

    /**
     * 入金編集処理
     *
     */
    public function editRegisterDeposit(Request $request) {

        // トランザクション開始
        DB::connection()->beginTransaction();

        try {

            // ユーザー情報の取得
            $user_info    = \Auth::user();
            $user_info_id = $user_info['id'];

            // リクエストパラメータ取得
            $depositDatas       = $request->data['Deposit'];
            $depositDetailDatas = $request->data['DepositDetail'];

            if ($depositDatas['deposit_submit_type'] == 3) {

                // ------------------------------------
                // 売上伝票を再度選択できるようにデータを戻す
                // ------------------------------------
                // 対象のデータを取得
                $saleSlips = DB::table('sale_slips AS SaleSlip')
                ->select(
                    'SaleSlip.id AS sale_slip_id'
                )
                ->join('deposit_withdrawal_details AS DepositWithdrawalDetail', function ($join) {
                    $join->on('DepositWithdrawalDetail.supply_sale_slip_id', '=', 'SaleSlip.id')
                            ->where([
                                ['DepositWithdrawalDetail.type', '=', '2'],
                                ['DepositWithdrawalDetail.active', '=', '1']
                            ]);
                })
                ->join('deposits AS Deposit', function ($join) {
                    $join->on('Deposit.id', '=', 'DepositWithdrawalDetail.deposit_withdrawal_id')
                            ->where('Deposit.active', '=', '1');
                })
                ->where([
                    ['Deposit.id', '=', $depositDatas['id']],
                    ['SaleSlip.active', '=', '1'],
                ])
                ->get();

                // ----------------------
                // 売上伝票のフラグを元に戻す
                // ----------------------
                foreach ($saleSlips as $saleSlip) {
                    $saleSlipUpParams = array(
                        'sale_flg'         => 0,                // 支払フラグ
                        'modified_user_id' => $user_info_id,    // 更新者ユーザーID
                        'modified'         => Carbon::now()     // 更新時間
                    );

                    // 更新処理
                    DB::table('sale_slips')
                    ->where('id', '=', $saleSlip->sale_slip_id)
                    ->update($saleSlipUpParams);
                }

                // -----------------
                // 伝票を論理削除させる
                // -----------------
                $updateParams = array(
                    'active'           => 0,                // アクティブフラグ
                    'modified_user_id' => $user_info_id,    // 更新者ユーザーID
                    'modified'         => Carbon::now()     // 更新時間
                );

                // 更新処理
                DB::table('deposits')
                ->where('id', '=', $depositDatas['id'])
                ->update($updateParams);

            } else {

                // 入力値を配列に格納する
                $updateParams = array(
                    'sale_company_id'     => $depositDatas['deposit_company_id'],
                    'sale_shop_id'        => $depositDatas['deposit_shop_id'],
                    //'date'                => $depositDatas['deposit_date'],
                    'date'                => $depositDatas['payment_date'], // 入金日付には支払期限を入れる
                    'sale_from_date'      => $depositDatas['sale_from_date'],
                    'sale_to_date'        => $depositDatas['sale_to_date'],
                    'payment_date'        => $depositDatas['payment_date'],
                    'staff_id'            => $depositDatas['staff_id'],
                    'sub_total'           => $depositDatas['price'],
                    'adjustment_amount'   => $depositDatas['adjustment_price'],
                    'amount'              => $depositDatas['total_price'],
                    'deposit_submit_type' => $depositDatas['deposit_submit_type'],
                    'deposit_method_id'   => $depositDatas['deposit_method_id'],
                    'remarks'             => $depositDatas['memo'],
                    'modified_user_id'    => $user_info_id,
                    'modified'            => Carbon::now()
                );

                // 更新処理
                DB::table('deposits')
                ->where('id', '=', $depositDatas['id'])
                ->update($updateParams);

                // -------------------------------------
                // 入出金詳細テーブルのデータを削除して新規登録
                // -------------------------------------
                // データ削除前に支払フラグ戻すように仕入IDを取得しておく
                $delBeforeSaleSlipIds = DB::table('deposit_withdrawal_details AS DepositWithdrawalDetail')
                ->select(
                    'DepositWithdrawalDetail.supply_sale_slip_id As sale_slip_id'
                )
                ->where([
                    ['DepositWithdrawalDetail.deposit_withdrawal_id', '=', $depositDatas['id']],
                    ['DepositWithdrawalDetail.type', '=', '2'],
                    ['DepositWithdrawalDetail.active', '=', '1']
                ])
                ->get();

                // データ削除
                \App\DepositWithdrawalDetail::where('deposit_withdrawal_id', $depositDatas['id'])->delete();

                // -------
                // 新規登録
                // -------
                // sale_slip_idの数ループさせる
                if (isset($depositDetailDatas['sale_slip_ids'])) {
                    foreach ($depositDetailDatas['sale_slip_ids'] as $saleSlipId) {
                        // 仕入伝票データ取得
                        $saleSlipDate    = $depositDetailDatas[$saleSlipId]['date'];
                        $notaxSubTotal8  = $depositDetailDatas[$saleSlipId]['notax_subTotal_8'];
                        $notaxSubTotal10 = $depositDetailDatas[$saleSlipId]['notax_subTotal_10'];
                        $subTotal        = $depositDetailDatas[$saleSlipId]['subTotal'];
                        $deliveryPrice   = $depositDetailDatas[$saleSlipId]['delivery_price'];
                        $adjustPrice     = $depositDetailDatas[$saleSlipId]['adjust_price'];
                        $total           = $depositDetailDatas[$saleSlipId]['total'];

                        // 登録データ格納
                        $insertDetailParams[] = array(
                            'deposit_withdrawal_id'   => $depositDatas['id'],
                            'supply_sale_slip_id'     => $saleSlipId,
                            // 'deposit_withdrawal_date' => $depositDatas['deposit_date'],
                            'deposit_withdrawal_date' => $depositDatas['payment_date'], // 入金日付には支払期限を入れる
                            'supply_sale_slip_date'   => $saleSlipDate,
                            'type'                    => 2,
                            'notax_sub_total_8'       => $notaxSubTotal8,
                            'notax_sub_total_10'      => $notaxSubTotal10,
                            'sub_total'               => $subTotal,
                            'delivery_price'          => $deliveryPrice,
                            'adjust_price'            => $adjustPrice,
                            'total'                   => $total,
                            'active'                  => 1,
                            'created_user_id'         => $user_info_id,
                            'created'                 => Carbon::now(),
                            'modified_user_id'        => $user_info_id,
                            'modified'                => Carbon::now(),
                        );
                    }
                }

                if (!empty($insertDetailParams)) {
                    DB::table('deposit_withdrawal_details')->insert($insertDetailParams);
                }

                // -----------------------------
                // 支払データのフラグを支払済みにする
                // -----------------------------
                if (!empty($delBeforeSaleSlipIds)) {
                    // 一旦対象データの支払フラグを未払いに戻す
                    foreach ($delBeforeSaleSlipIds as $delBeforeSaleSlipId) {
                        $saleSlipIds[] = $delBeforeSaleSlipId->sale_slip_id;
                    }

                    if (isset($saleSlipIds) && !empty($saleSlipIds)) {
                        DB::table('sale_slips')
                        ->whereIn('id', $saleSlipIds)
                        ->update(array('sale_flg' => 0));
                    }

                    if (isset($depositDetailDatas['sale_slip_ids']) && !empty($depositDetailDatas['sale_slip_ids'])) {
                        // その後支払済みに更新する
                        DB::table('sale_slips')
                            ->whereIn('id', $depositDetailDatas['sale_slip_ids'])
                            ->update(array('sale_flg' => 1));
                    }
                }

                // --------------------------------------------
                // 入金詳細テーブルに重複しているIDが存在しているか確認
                // --------------------------------------------
                $delTargetDatas = null;
                if (isset($depositDetailDatas['sale_slip_ids'])) {
                    foreach ($depositDetailDatas['sale_slip_ids'] as $saleSlipId) {
                        // 編集対象のID以外のものを抽出
                        $delTargetDatas = DB::table('deposit_withdrawal_details AS DepositWithdrawalDetail')
                        ->select(
                            'DepositWithdrawalDetail.id As id',
                            'DepositWithdrawalDetail.deposit_withdrawal_id As deposit_withdrawal_id'
                        )
                        ->join('sale_slips As SaleSlip', function ($join) {
                            $join->on('DepositWithdrawalDetail.supply_sale_slip_id', '=', 'SaleSlip.id');
                        })
                        ->where([
                            ['DepositWithdrawalDetail.deposit_withdrawal_id', '<>', $depositDatas['id']],
                            ['DepositWithdrawalDetail.supply_sale_slip_id', '=', $saleSlipId],
                            ['DepositWithdrawalDetail.active', '=', '1'],
                            ['SaleSlip.active', '=', '1']
                        ])
                        ->first();
                    }
                }

                // ----------------------------------------
                // 存在していれば編集対象以外のデータを削除、再計算
                // ----------------------------------------
                if (!empty($delTargetDatas)) {

                    // 削除対象データを削除
                    \App\DepositWithdrawalDetail::where('id', $delTargetDatas->id)->delete();

                    // ------------------------
                    // 削除された出金IDの再計算する
                    // ------------------------
                    // まずは計算データ取得
                    $depositDetailCalcDatas = DB::table('deposit_withdrawal_details AS DepositWithdrawalDetail')
                        ->select(
                            'DepositWithdrawalDetail.notax_sub_total_8 As notax_sub_total_8',
                            'DepositWithdrawalDetail.notax_sub_total_10 As notax_sub_total_10',
                            'DepositWithdrawalDetail.delivery_price As delivery_price',
                            'DepositWithdrawalDetail.adjust_price As adjust_price'
                        )
                        ->where([
                            ['DepositWithdrawalDetail.deposit_withdrawal_id', '=', $delTargetDatas->deposit_withdrawal_id],
                            ['DepositWithdrawalDetail.active', '=', '1']
                        ])
                        ->get();

                    $depositCalcDatas = DB::table('deposits AS Deposit')
                        ->select(
                            'Deposit.adjustment_amount As adjustment_amount'
                        )
                        ->where([
                            ['Deposit.id', '=', $delTargetDatas->deposit_withdrawal_id],
                            ['Deposit.active', '=', '1']
                        ])
                        ->get();

                    // ---------
                    // データ計算
                    // ---------
                    $notaxSubTotal8   = 0;
                    $notaxSubTotal10  = 0;
                    $subTotal8        = 0;
                    $subTotal10       = 0;
                    $deliveryPrice    = 0;
                    $adjustTotalPrice = 0;
                    $adjustPrice      = 0;
                    $subTotal         = 0;
                    $total            = 0;
                    foreach ($depositDetailCalcDatas as $depositDetailCalcData) {

                        // 税抜8％額
                        $notaxSubTotal8 += $depositDetailCalcData->notax_sub_total_8;

                        // 税抜10％額
                        $notaxSubTotal10 += $depositDetailCalcData->notax_sub_total_10;

                        // 配送額
                        $deliveryPrice += $depositDetailCalcData->delivery_price;

                        // 調整額
                        $adjustTotalPrice += $depositDetailCalcData->adjust_price;
                    }

                    // 税込8%
                    $subTotal8 = floor($notaxSubTotal8 * 1.08);

                    // 税込10%
                    $subTotal10 = floor($notaxSubTotal10 * 1.1);

                    // 小計(調整額含まない)
                    $subTotal = $subTotal8 + $subTotal10 + $deliveryPrice + $adjustTotalPrice;

                    // 総合計
                    if (!empty($depositCalcDatas[0]->adjustment_amount)) $adjustPrice = $depositCalcDatas[0]->adjustment_amount;
                    $total = $subTotal + $adjustPrice;

                    // -----------------------------
                    // 計算結果を出金テーブルに更新させる
                    // -----------------------------
                    DB::table('deposits')
                    ->where('id', $delTargetDatas->deposit_withdrawal_id)
                    ->update(array(
                        'sub_total' => $subTotal,
                        'amount'    => $total
                    ));
                }
            }

            // 問題なければコミット
            DB::connection()->commit();

        } catch (\Exception $e) {

            DB::rollback();

            dd($e);
        }

        return redirect('./DepositIndex');
    }

    /**
     * ajax処理
     * 指定された範囲の請求伝票一覧を返す
     *
     */
    public function AjaxSearchSaleSlips(Request $request) {

        // 入力された値を取得
        $saleFromDate  = $request->sales_from_date;
        $saleToDate    = $request->sales_to_date;
        $saleCompany   = $request->sales_company;
        $searchDateVal = $request->search_date_val;
        $action        = $request->action;

        // 入力された値のチェック
        if (empty($saleFromDate) && empty($saleToDate)) {
            return false;
        }

        if (empty($saleCompany)) {
            return false;
        }

        // どちらか片方にしか日付が入っていない場合は入っている方の日付と同じ日付を設定する
        if (!empty($saleFromDate) && empty($saleToDate)) {
            $saleToDate = $saleFromDate;
        }

        if (empty($saleFromDate) && !empty($saleToDate)) {
            $saleFromDate = $saleToDate;
        }

        if ($action == 'edit') {
            $whereArray = [
                ['SaleSlip.sale_company_id', '=', $saleCompany],
                ['SaleSlip.deposit_flg', '=', '0'],
                ['SaleSlip.active', '=', '1']
            ];
        } else {
            $whereArray = [
                ['SaleSlip.sale_company_id', '=', $saleCompany],
                //['SaleSlip.sale_flg', '=', '0'], 現状出力した明細も出すようにする
                ['SaleSlip.deposit_flg', '=', '0'],
                ['SaleSlip.active', '=', '1']
            ];
        }

        // 対象日付から売上伝票を取得
        $saleSlipList = DB::table('sale_slips AS SaleSlip')
        ->select(
            'SaleSlip.id As id',
            'SaleSlip.date As date',
            'SaleSlip.notax_sub_total_8 As notax_sub_total_8',
            'SaleSlip.notax_sub_total_10 As notax_sub_total_10',
            'SaleSlip.delivery_price As delivery_price',
            'SaleSlip.adjust_price As adjust_price'
        )
        ->if($searchDateVal == 1, function ($query) use ($saleFromDate, $saleToDate){
            return $query->whereBetween('SaleSlip.date', [$saleFromDate, $saleToDate]);
        })
        ->if($searchDateVal == 2, function ($query) use ($saleFromDate, $saleToDate){
            return $query->whereBetween('SaleSlip.delivery_date', [$saleFromDate, $saleToDate]);
        })
        ->where($whereArray)
        ->get();

        // 取得してきたデータを計算してHTMLを形成
        // HTML格納変数初期化
        $ajaxHtml = '';
        if (!$saleSlipList->isEmpty()) {

            $notaxSubTotal8  = 0;
            $notaxSubTotal10 = 0;
            $tax8            = 0;
            $tax10           = 0;
            $subTotal        = 0;
            $delivery_price  = 0;
            $adjust_price    = 0;
            $total           = 0;

            $ajaxHtml .= '<table class="result-table" onchange="javascript:changeCalcFlg()">';
            $ajaxHtml .= '    <tr>';
            $ajaxHtml .= '        <th class="width-5">選択</th>';
            $ajaxHtml .= '        <th>伝票日付</th>';
            $ajaxHtml .= '        <th>8%税抜合計</th>';
            $ajaxHtml .= '        <th>外税8%</th>';
            $ajaxHtml .= '        <th>10%税抜合計</th>';
            $ajaxHtml .= '        <th>外税10%</th>';
            $ajaxHtml .= '        <th>小計</th>';
            $ajaxHtml .= '        <th>配送額</th>';
            $ajaxHtml .= '        <th>調整額</th>';
            $ajaxHtml .= '        <th>調整後金額</th>';
            $ajaxHtml .= '        <th class="width-5">明細</th>';
            $ajaxHtml .= '    </tr>';

            foreach ($saleSlipList as $saleSlipDatas) {
                // 金額計算
                $notaxSubTotal8  = $saleSlipDatas->notax_sub_total_8;                     // 税抜8%金額
                $tax8            = floor($saleSlipDatas->notax_sub_total_8 * 8 / 100);    // 8%消費税
                $notaxSubTotal10 = $saleSlipDatas->notax_sub_total_10;                    // 税抜10%金額
                $tax10           = floor($saleSlipDatas->notax_sub_total_10 * 10 / 100);  // 8%消費税
                $subTotal        = $notaxSubTotal8 + $tax8 + $notaxSubTotal10 + $tax10;   // 小計
                $delivery_price  = $saleSlipDatas->delivery_price;                        // 配送額
                $adjust_price    = $saleSlipDatas->adjust_price;                          // 調整額
                $total           = $subTotal + $delivery_price + $adjust_price;           // 調整後総合計額

                $ajaxHtml .= '        <tr>';
                $ajaxHtml .= '            <td><input type="checkbox" class="checkbox_list" id="sale-slip-id-' . $saleSlipDatas->id . '" name="data[DepositDetail][' . $saleSlipDatas->id . '][id]" value="' . $saleSlipDatas->id . '"></td>';
                $ajaxHtml .= '            <td>' . $saleSlipDatas->date;
                $ajaxHtml .= '                <input type="hidden" name="data[DepositDetail][' . $saleSlipDatas->id . '][date]" value="' . $saleSlipDatas->date . '">';
                $ajaxHtml .= '            </td>';
                $ajaxHtml .= '            <td>' . number_format($notaxSubTotal8);
                $ajaxHtml .= '                <input type="hidden" id="sale-slip-subTotal8-' . $saleSlipDatas->id . '" name="data[DepositDetail][' . $saleSlipDatas->id . '][notax_subTotal_8]" value="' . $notaxSubTotal8 . '">';
                $ajaxHtml .= '            </td>';
                $ajaxHtml .= '            <td>' . number_format($tax8);
                $ajaxHtml .= '                <input type="hidden" id="sale-slip-tax8-' . $saleSlipDatas->id . '"name="data[DepositDetail][' . $saleSlipDatas->id . '][tax8]" value="' . $tax8 . '">';
                $ajaxHtml .= '            </td>';
                $ajaxHtml .= '            <td>' . number_format($notaxSubTotal10);
                $ajaxHtml .= '                <input type="hidden" id="sale-slip-subTotal10-' . $saleSlipDatas->id . '"name="data[DepositDetail][' . $saleSlipDatas->id . '][notax_subTotal_10]" value="' . $notaxSubTotal10 . '">';
                $ajaxHtml .= '            </td>';
                $ajaxHtml .= '            <td>' . number_format($tax10);
                $ajaxHtml .= '                <input type="hidden" id="sale-slip-tax10-' . $saleSlipDatas->id . '"name="data[DepositDetail][' . $saleSlipDatas->id . '][tax10]" value="' . $tax10 . '">';
                $ajaxHtml .= '            </td>';
                $ajaxHtml .= '            <td>' . number_format($subTotal);
                $ajaxHtml .= '                <input type="hidden" name="data[DepositDetail][' . $saleSlipDatas->id . '][subTotal]" value="' . $subTotal . '">';
                $ajaxHtml .= '            </td>';
                $ajaxHtml .= '            <td>' . number_format($delivery_price);
                $ajaxHtml .= '                <input type="hidden" id="sale-slip-deliveryPrice-' . $saleSlipDatas->id . '" name="data[DepositDetail][' . $saleSlipDatas->id . '][delivery_price]" value="' . $delivery_price . '">';
                $ajaxHtml .= '            </td>';
                $ajaxHtml .= '            <td>' . number_format($adjust_price);
                $ajaxHtml .= '                <input type="hidden" id="sale-slip-adjustPrice-' . $saleSlipDatas->id . '" name="data[DepositDetail][' . $saleSlipDatas->id . '][adjust_price]" value="' . $adjust_price . '">';
                $ajaxHtml .= '            </td>';
                $ajaxHtml .= '            <td id="sale-slip-total' . $saleSlipDatas->id . '" data-value="' . $total . '">' . number_format($total);
                $ajaxHtml .= '                <input type="hidden" name="data[DepositDetail][' . $saleSlipDatas->id . '][total]" value="' . $total . '">';
                $ajaxHtml .= '            </td>';
                $ajaxHtml .= '            <td>';
                $ajaxHtml .= '                <a target="_blank" href="./SaleSlipEdit/' . $saleSlipDatas->id . '">明細</a>';
                $ajaxHtml .= '            </td>';
                $ajaxHtml .= '        </tr>';

            }

            $ajaxHtml .= '</table>';

        }

        $returnArray = array($ajaxHtml);
        return $returnArray;

    }

    /**
     * 請求書印刷
     */
    public function invoiceOutput($depositId) {

        // 請求情報を取得
        $depositList = DB::table('deposits AS Deposit')
        ->select(
            'Deposit.id                                  AS deposit_id',
            'Deposit.payment_date                        AS payment_date',
            'Deposit.adjustment_amount                   AS deposit_adjust_price',
            'Deposit.remarks                             AS remarks',
            'Deposit.sale_from_date                      AS sale_from_date',
            'Deposit.sale_to_date                        AS sale_to_date',
            'DepositWithdrawalDetail.supply_sale_slip_id AS sale_slip_id',
            'DepositWithdrawalDetail.delivery_price      AS delivery_price',
            'DepositWithdrawalDetail.adjust_price        AS sale_adjust_price',
            'SaleCompany.id                              AS company_id',
            'SaleCompany.name                            AS company_name',
            'SaleCompany.postal_code                     AS company_postal_code',
            'SaleCompany.address                         AS company_address',
            'SaleCompany.tax_calc_type                   AS company_tax_calc_type',
            'SaleCompany.invoice_display_name            AS company_invoice_display_name',
            'SaleCompany.invoice_display_address         AS company_invoice_display_address',
            'SaleCompany.invoice_display_postal_code     AS company_invoice_display_postal_code',
            'SaleCompany.invoice_display_flg             AS company_invoice_display_flg',
            'SaleSlipDetail.inventory_unit_num           AS inventory_unit_num',
            'SaleSlipDetail.unit_price                   AS unit_price',
            'SaleSlipDetail.unit_num                     AS unit_num',
            'SaleSlipDetail.notax_price                  AS notax_price',
            'SaleSlipDetail.memo                         AS memo',
            'Product.name                                AS product_name',
            'Product.tax_id                              AS tax_id',
            'Unit.name                                   AS unit_name',
            'OriginArea.name                             AS origin_name',
        )
        ->selectRaw('DATE_FORMAT(SaleSlip.delivery_date, "%m/%d") AS sale_slip_delivery_date')
        ->join('sale_companies AS SaleCompany', function ($join) {
            $join->on('SaleCompany.id', '=', 'Deposit.sale_company_id');
        })
        ->join('deposit_withdrawal_details AS DepositWithdrawalDetail', function ($join) {
            $join->on('DepositWithdrawalDetail.deposit_withdrawal_id', '=', 'Deposit.id')
                 ->where('DepositWithdrawalDetail.type', '=', '2'); // 入出金タイプ 1:出金, 2:入金
        })
        ->join('sale_slips AS SaleSlip', function ($join) {
            $join->on('DepositWithdrawalDetail.supply_sale_slip_id', '=', 'SaleSlip.id');
        })
        ->join('sale_slip_details AS SaleSlipDetail', function ($join) {
            $join->on('SaleSlipDetail.sale_slip_id', '=', 'SaleSlip.id')
                 ->where('DepositWithdrawalDetail.type', '=', '2');
        })
        ->join('products AS Product', function ($join) {
            $join->on('SaleSlipDetail.product_id', '=', 'Product.id');
        })
        ->join('units AS Unit', function ($join) {
            $join->on('Product.unit_id', '=', 'Unit.id');
        })
        ->leftJoin('origin_areas as OriginArea', function ($join) {
            $join->on('OriginArea.id', '=', 'SaleSlipDetail.origin_area_id')
                 ->where('OriginArea.active', '=', true);
        })
        ->where([
            ['Deposit.id', '=', $depositId],
            ['Deposit.active', '=', '1']
        ])
        ->orderBy('SaleSlip.delivery_date', 'asc')
        ->orderBy('SaleSlip.id', 'asc')
        ->orderBy('SaleSlipDetail.sort', 'asc')
        ->get();

        // ------------------------
        // 企業情報を取得する
        // ------------------------
        $companyDatas = CompanySetting::getCompanyData();

        // ------------------------
        // 取得してきたデータを整形する
        // ------------------------

        // 請求元企業情報の整形
        // 初期化
        $companyInfo = array();
        $bank_type = array(
            1 => '普通',
            2 => '当座',
            3 => 'その他',
        );

        $companyInfo['name']            = empty($companyDatas[0]->name)            ? '' : $companyDatas[0]->name;
        $companyInfo['address']         = empty($companyDatas[0]->address)         ? '' : $companyDatas[0]->address;
        $companyInfo['office_tel']      = empty($companyDatas[0]->office_tel)      ? '' : $companyDatas[0]->office_tel;
        $companyInfo['office_fax']      = empty($companyDatas[0]->office_fax)      ? '' : $companyDatas[0]->office_fax;
        $companyInfo['shop_tel']        = empty($companyDatas[0]->shop_tel)        ? '' : $companyDatas[0]->shop_tel;
        $companyInfo['shop_fax']        = empty($companyDatas[0]->shop_fax)        ? '' : $companyDatas[0]->shop_fax;
        $companyInfo['invoice_form_id'] = empty($companyDatas[0]->invoice_form_id) ? '' : $companyDatas[0]->invoice_form_id;
        $companyInfo['bank_name']       = empty($companyDatas[0]->bank_name)       ? '' : $companyDatas[0]->bank_name;
        $companyInfo['branch_name']     = empty($companyDatas[0]->branch_name)     ? '' : $companyDatas[0]->branch_name;
        $companyInfo['bank_type']       = empty($companyDatas[0]->bank_type)       ? '' : $bank_type[$companyDatas[0]->bank_type];
        $companyInfo['bank_account']    = empty($companyDatas[0]->bank_account)    ? '' : $companyDatas[0]->bank_account;
        $companyInfo['company_image']   = empty($companyDatas[0]->company_image)   ? '' : $companyDatas[0]->company_image;

        // 郵便番号は間にハイフンを入れる
        if (!empty($companyDatas[0]->postal_code)) {
            $codeBefore = substr($companyDatas[0]->postal_code, 0, 3);
            $codeAfter  = substr($companyDatas[0]->postal_code, 3, 4);
            $companyInfo['postal_code'] = $codeBefore . '-' . $codeAfter;
        }

        // 初期化処理
        $calcDepositList = array();
        $companyTaxCalcType = 0;
        $prev_sale_slip_id = 0;
        $tax8PerSaleSlip = 0; // 伝票ごとの消費税を格納する変数
        $tax10PerSaleSlip = 0; // 伝票ごとの消費税を格納する変数
        $tax8  = 0; // 請求書の消費税合計を格納する変数
        $tax10 = 0; // 請求書の消費税合計を格納する変数
        $notaxSubTotal8Amount = 0;
        $notaxSubTotal10Amount = 0;
        $thedate_subtotal = 0;  // 日々の小計
        $prev_thedate = "";     // 日々の小計に利用する前レコードの日付
        foreach ($depositList as $depositDatas) {

            // 前レコードと日付が変わっている場合は日々の小計データを入れる
            if (!empty($prev_thedate) && $prev_thedate != $depositDatas->sale_slip_delivery_date) {
                $calcDepositList['detail'][] = array(
                    'date'                => $prev_thedate,
                    'name'                => "小計",
                    'origin_name'         => "",
                    'inventory_unit_num'  => "",
                    'unit_price'          => "",
                    'unit_num'            => "",
                    'unit_name'           => "",
                    'notax_price'         => $thedate_subtotal,
                    'memo'                => "",
                );

                // 初期化
                $thedate_subtotal = 0;
            }

            // 日付を格納
            $prev_thedate = $depositDatas->sale_slip_delivery_date;
            $thedate_subtotal += $depositDatas->notax_price;

             // 税計算種別が0:伝票ごとの場合
            if (
                $companyTaxCalcType == 0 &&
                !empty($prev_sale_slip_id) &&
                $prev_sale_slip_id != $depositDatas->sale_slip_id
            ) {
                $tax8  += floor($tax8PerSaleSlip * 0.08);
                $tax10 += floor($tax10PerSaleSlip * 0.1);
                $tax8PerSaleSlip = 0;
                $tax10PerSaleSlip = 0;
            }
            // 消費税計算するために前売上伝票IDを取得
            $prev_sale_slip_id = $depositDatas->sale_slip_id;

            // -------
            // 会社情報
            // -------
            // 企業情報格納
            if (!isset($calcDepositList['company_info'])) {

                if (isset($depositDatas->shop_name) && !empty($depositDatas->shop_name)) { // 店舗情報がる場合はこちらを入れる

                    $companyId = $depositDatas->company_id; // ブラウザ名になるので店舗の場合でもこちらを入れる
                    $calcDepositList['company_info']['name']    = $depositDatas->shop_name;
                    $company_name                               = $depositDatas->shop_name;
                    $calcDepositList['company_info']['address'] = $depositDatas->shop_address;
                    // 郵便番号は間にハイフンを入れる
                    $calcDepositList['company_info']['code'] = '';
                    if (!empty($depositDatas->shop_postal_code)) {
                        $codeBefore = substr($depositDatas->shop_postal_code, 0, 3);
                        $codeAfter  = substr($depositDatas->shop_postal_code, 3, 4);
                        $calcDepositList['company_info']['code'] = '〒' . $codeBefore . '-' . $codeAfter;
                    }

                } else {
                    $companyId = $depositDatas->company_id;
                    $calcDepositList['company_info']['name'] = $depositDatas->company_name;
                    $calcDepositList['company_info']['address'] = $depositDatas->company_address;
                    // 郵便番号は間にハイフンを入れる
                    $calcDepositList['company_info']['code'] = '';
                    if (!empty($depositDatas->company_postal_code)) {
                        $codeBefore = substr($depositDatas->company_postal_code, 0, 3);
                        $codeAfter  = substr($depositDatas->company_postal_code, 3, 4);
                        $calcDepositList['company_info']['code'] = '〒' . $codeBefore . '-' . $codeAfter;
                    }
                    // 請求書用フラグが有効の場合は請求書用の名前、郵便番号、住所を使用する
                    if ($depositDatas->company_invoice_display_flg) {
                        $calcDepositList['company_info']['name'] = $depositDatas->company_invoice_display_name;
                        $calcDepositList['company_info']['address'] = $depositDatas->company_invoice_display_address;
                        // 郵便番号は間にハイフンを入れる
                        if (!empty($depositDatas->company_invoice_display_postal_code)) {
                            $codeBefore = substr($depositDatas->company_invoice_display_postal_code, 0, 3);
                            $codeAfter  = substr($depositDatas->company_invoice_display_postal_code, 3, 4);
                            $calcDepositList['company_info']['code'] = '〒' . $codeBefore . '-' . $codeAfter;
                        }
                    }
                }

                // 請求期間
                $calcDepositList['company_info']['sale_from_to_date'] = date('Y年m月d日', strtotime($depositDatas->sale_from_date)) . '～' . date('Y年m月d日', strtotime($depositDatas->sale_to_date));

                // 支払期日もここで入れる
                $calcDepositList['company_info']['payment_date'] = date('Y年m月d日', strtotime($depositDatas->payment_date));

                // 備考情報もここで入れる
                $calcDepositList['company_info']['remarks'] = $depositDatas->remarks;

                // 税計算種別(0:伝票ごと 1:請求書ごと)
                $companyTaxCalcType = $depositDatas->company_tax_calc_type;
            }

            // -------------------
            // 8%, 10%ごとの金額計算
            // -------------------

            $product_name = "";
            if ($depositDatas->tax_id == 1) {// 税率が8%の場合
                $product_name = $depositDatas->product_name . " *"; // 軽減税率対象商品がわかるようにする
                $notaxSubTotal8Amount += $depositDatas->notax_price;
                if ($companyTaxCalcType == 0) { //　伝票ごとに消費税算出する計算方式の場合
                    $tax8PerSaleSlip += $depositDatas->notax_price;
                }
            } else {// 税率が10%の場合
                $product_name = $depositDatas->product_name;
                $notaxSubTotal10Amount += $depositDatas->notax_price;
                if ($companyTaxCalcType == 0) { //　伝票ごとに消費税算出する計算方式の場合
                    $tax10PerSaleSlip += $depositDatas->notax_price;
                }
            }

            // 初期化
            $calcDepositList['detail'][] = array(
                'date'                => $depositDatas->sale_slip_delivery_date,
                'name'                => $product_name,
                'origin_name'         => $depositDatas->origin_name,
                'inventory_unit_num'  => $depositDatas->inventory_unit_num,
                'unit_price'          => $depositDatas->unit_price,
                'unit_num'            => $depositDatas->unit_num,
                'unit_name'           => $depositDatas->unit_name,
                'notax_price'         => $depositDatas->notax_price,
                'memo'                => $depositDatas->memo,
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
        }

        // レコードの最後に小計データを入れる
        $calcDepositList['detail'][] = array(
            'date'                => $prev_thedate,
            'name'                => "小計",
            'origin_name'         => "",
            'inventory_unit_num'  => "",
            'unit_price'          => "",
            'unit_num'            =>"",
            'unit_name'           => "",
            'notax_price'         => $thedate_subtotal,
            'memo'                => "",
        );

        // 税金計算
        if ($companyTaxCalcType == 0) {
            $tax8  += floor($tax8PerSaleSlip * 0.08);
            $tax10 += floor($tax10PerSaleSlip * 0.1);
            $tax8PerSaleSlip = 0;
            $tax10PerSaleSlip = 0;
        } else if ($companyTaxCalcType == 1) { //　請求書ごとに消費税算出する計算方式の場合
            $tax8  = floor($notaxSubTotal8Amount * 0.08);
            $tax10 = floor($notaxSubTotal10Amount * 0.1);
        }

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
        foreach ($depositList as $depositDatas) {
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

            // 入金伝票ごとに
            if (!in_array($depositDatas->deposit_id, $calcDepositIds)) {
                $calcDepositList['detail']['adjust_price']['notax_price'] += $depositDatas->deposit_adjust_price;
                $calcDepositList['total']['total'] += $depositDatas->deposit_adjust_price;
                $calcDepositIds[] = $depositDatas->deposit_id;
            }
            // 売上伝票ごとに
            if (!in_array($depositDatas->sale_slip_id, $calcSaleSlipIds)) {
                $calcDepositList['detail']['adjust_price']['notax_price'] += $depositDatas->sale_adjust_price;
                $calcDepositList['detail']['delivery_price']['notax_price'] += $depositDatas->delivery_price;
                $calcDepositList['total']['total'] += $depositDatas->sale_adjust_price + $depositDatas->delivery_price;
                $calcSaleSlipIds[] = $depositDatas->sale_slip_id;
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

        // テスト用
        //return view('pdf.pdf_tamplate')->with(['depositList'=> $calcDepositList]);

        $pdf = \PDF::view('pdf.pdf_tamplate', [
            'depositList' => $calcDepositList,
            'companyInfo' => $companyInfo
        ])
        ->setOption('encoding', 'utf-8')
        ->setOption('margin-bottom', 8)
        ->setOption('footer-center', '[page] ページ')
        ->setOption('footer-font-size', 8)
        ->setOption('footer-html', view('pdf.pdfFooter', [
            'company_name' => $calcDepositList['company_info']['name']
        ]));
        return $pdf->inline('invoice_paymentDate' . '_' . $companyId .'.pdf');  //ブラウザ上で開ける
        // return $pdf->download('thisis.pdf'); //こっちにすると直接ダウンロード
    }

}
