<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\OrderSupplyUnitPrice;
use App\OrderSupplyUnitPriceDetail;
use Carbon\Carbon;

class OrderSupplyUnitPriceController extends Controller
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
     * 仕入発注単価一覧
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
            $search_action = '../OrderSupplyUnitPriceIndex';
        } else {
            $search_action = './OrderSupplyUnitPriceIndex';
        }

        // postできたか、getできたか
        if ($_SERVER["REQUEST_METHOD"] != "POST"
        ) { // ページング処理

            $condition_date_from = $request->session()->get('order_supply_condition_date_from');
            $condition_date_to   = $request->session()->get('order_supply_condition_date_to');

            // 空値の場合は初期値を設定
            if(empty($condition_date_from)) $condition_date_from = date('Y-m-d');
            if(empty($condition_date_to)) $condition_date_to     = date('Y-m-d');

            $condition_company_code = $request->session()->get('order_supply_condition_company_code');
            $condition_company_id   = $request->session()->get('order_supply_condition_company_id');
            $condition_company_text = $request->session()->get('order_supply_condition_company_text');
            $condition_product_code = $request->session()->get('order_supply_condition_product_code');
            $condition_product_id   = $request->session()->get('order_supply_condition_product_id');
            $condition_product_text = $request->session()->get('order_supply_condition_product_text');

        } else { // POST時の処理

            if (isset($_POST['search-btn'])) { // 検索ボタン押された時の処理

                $condition_company_code = $request->data['OrderSupplyUnitPrice']['supply_company_code'];
                $condition_company_id   = $request->data['OrderSupplyUnitPrice']['supply_company_id'];
                $condition_company_text = $request->data['OrderSupplyUnitPrice']['supply_company_text'];
                $condition_product_code = $request->data['OrderSupplyUnitPrice']['product_code'];
                $condition_product_id   = $request->data['OrderSupplyUnitPrice']['product_id'];
                $condition_product_text = $request->data['OrderSupplyUnitPrice']['product_text'];

                // 日付の設定
                $condition_date_from = $request->data['OrderSupplyUnitPrice']['apply_from'];
                $condition_date_to   = $request->data['OrderSupplyUnitPrice']['apply_to'];
                // どちらか片方しか入力されなかった場合は同じ日付を入れる
                if (!empty($condition_date_from) && empty($condition_date_to)) {
                    $condition_date_to = $condition_date_from;
                }
                if (empty($condition_date_from) && !empty($condition_date_to)) {
                    $condition_date_from = $condition_date_to;
                }
                // どちらも入力されていない場合はどちらも当日を入れる
                if (empty($condition_date_from) && empty($condition_date_to)) {
                    $condition_date_from = date('Y-m-d');
                    $condition_date_to = date('Y-m-d');
                }

                $request->session()->put('order_supply_condition_date_from', $condition_date_from);
                $request->session()->put('order_supply_condition_date_to', $condition_date_to);
                $request->session()->put('order_supply_condition_company_code', $condition_company_code);
                $request->session()->put('order_supply_condition_company_id', $condition_company_id);
                $request->session()->put('order_supply_condition_company_text', $condition_company_text);
                $request->session()->put('order_supply_condition_product_code', $condition_product_code);
                $request->session()->put('order_supply_condition_product_id', $condition_product_id);
                $request->session()->put('order_supply_condition_product_text', $condition_product_text);

            } else { // リセットボタンが押された時の処理

                $condition_date_from    = date('Y-m-d');
                $condition_date_to      = date('Y-m-d');
                $condition_company_code = null;
                $condition_company_id   = null;
                $condition_company_text = null;
                $condition_product_code = null;
                $condition_product_id   = null;
                $condition_product_text = null;
                $request->session()->forget('order_supply_condition_date_from');
                $request->session()->forget('order_supply_condition_date_to');
                $request->session()->forget('order_supply_condition_company_code');
                $request->session()->forget('order_supply_condition_company_id');
                $request->session()->forget('order_supply_condition_company_text');
                $request->session()->forget('order_supply_condition_product_code');
                $request->session()->forget('order_supply_condition_product_id');
                $request->session()->forget('order_supply_condition_product_text');
            }
        }

        try {

            // order_supply_unit_price_detailsのサブクエリを作成
            $product_sub_query = null;
            if(!empty($condition_product_id)) {
                $product_sub_query = DB::table('order_supply_unit_price_details as SubTable')
                ->select('SubTable.order_supply_unit_price_id AS sub_order_supply_unit_price_id')
                ->where('SubTable.product_id', '=', $condition_product_id)
                ->groupBy('SubTable.order_supply_unit_price_id');
            }

            // 仕入発注単価一覧を取得
            $orderSupplyUnitPriceList = DB::table('order_supply_unit_prices AS OrderSupplyUnitPrice')
            ->select(
                'OrderSupplyUnitPrice.id               AS order_supply_unit_price_id',
                'OrderSupplyUnitPrice.modified         AS modified',
                'SupplyCompany.code                    AS supply_company_code',
                'SupplyCompany.name                    AS supply_company_name'
            )
            ->selectRaw('MIN(OrderSupplyUnitPriceDetail.apply_from) AS apply_from')
            ->join('supply_companies AS SupplyCompany', function ($join) {
                $join->on('SupplyCompany.id', '=', 'OrderSupplyUnitPrice.company_id');
            })
            ->if(!empty($condition_product_id), function ($query) use ($product_sub_query) {
                return $query
                       ->join(DB::raw('('. $product_sub_query->toSql() .') as OrderSupplyUnitPriceDetail'), 'OrderSupplyUnitPriceDetail.sub_order_supply_unit_price_id', '=', 'OrderSupplyUnitPrice.id')
                       ->mergeBindings($product_sub_query);
            })
            ->if(!empty($condition_date_from) && !empty($condition_date_to), function ($query) use ($condition_date_from, $condition_date_to) {
                return $query
                        ->join('order_supply_unit_price_details AS OrderSupplyUnitPriceDetail', 'OrderSupplyUnitPrice.id', '=', 'OrderSupplyUnitPriceDetail.order_supply_unit_price_id')
                        ->whereBetween('OrderSupplyUnitPriceDetail.apply_from', [$condition_date_from, $condition_date_to]);
            })
            ->if(!empty($condition_company_id), function ($query) use ($condition_company_id) {
                return $query->where('OrderSupplyUnitPrice.company_id', '=', $condition_company_id);
            })
            ->where('OrderSupplyUnitPrice.active', '=', '1')
            ->groupBy('OrderSupplyUnitPrice.id')
            ->orderBy('OrderSupplyUnitPrice.id', 'asc')
            ->paginate(10);

            // ===================
            // 仕入発注単価詳細を取得
            // ===================
            $orderSupplyUnitPriceDetailList = DB::table('order_supply_unit_price_details AS OrderSupplyUnitPriceDetail')
            ->select(
                'OrderSupplyUnitPrice.id                AS order_supply_unit_price_id',
                'Product.code                           AS product_code',
                'Product.name                           AS product_name',
                'Product.tax_id                         AS product_tax_id',
                'OrderSupplyUnitPriceDetail.id          AS order_supply_unit_price_detail_id',
                'OrderSupplyUnitPriceDetail.notax_price AS order_supply_unit_price_detail_price',
                'Unit.name                              AS unit_name'
            )
            ->join('order_supply_unit_prices as OrderSupplyUnitPrice', function ($join) {
                $join->on('OrderSupplyUnitPrice.id', '=', 'OrderSupplyUnitPriceDetail.order_supply_unit_price_id')
                ->where('OrderSupplyUnitPrice.active', '=', true);
            })
            ->join('products as Product', function ($join) {
                $join->on('Product.id', '=', 'OrderSupplyUnitPriceDetail.product_id')
                ->where('Product.active', '=', true);
            })
            ->join('units as Unit', function ($join) {
                $join->on('Unit.id', '=', 'Product.unit_id')
                ->where('Unit.active', '=', true);
            })
            ->leftJoin('supply_companies as SupplyCompany', function ($join) {
                $join->on('SupplyCompany.id', '=', 'OrderSupplyUnitPrice.company_id')
                ->where('SupplyCompany.active', '=', true);
            })
            ->get();

            // 各伝票にいくつ明細がついているのかをカウントする配列
            $order_supply_unit_price_detail_arr = array();

            // 伝票詳細で取得したDBをループ
            foreach($orderSupplyUnitPriceDetailList as $orderSupplyUnitPriceDetails){

                if(!isset($order_supply_unit_price_detail_count_arr[$orderSupplyUnitPriceDetails->order_supply_unit_price_id])){
                    $order_supply_unit_price_detail_count_arr[$orderSupplyUnitPriceDetails->order_supply_unit_price_id] = 0;
                }

                $order_supply_unit_price_detail_count_arr[$orderSupplyUnitPriceDetails->order_supply_unit_price_id] += 1;

                $order_supply_unit_price_detail_arr[$orderSupplyUnitPriceDetails->order_supply_unit_price_id][] = [

                    'product_code'                         => $orderSupplyUnitPriceDetails->product_code,
                    'product_name'                         => $orderSupplyUnitPriceDetails->product_name,
                    'product_tax_id'                       => $orderSupplyUnitPriceDetails->product_tax_id,
                    'order_supply_unit_price_detail_id'    => $orderSupplyUnitPriceDetails->order_supply_unit_price_detail_id,
                    'order_supply_unit_price_detail_price' => $orderSupplyUnitPriceDetails->order_supply_unit_price_detail_price,
                    'unit_name'                            => $orderSupplyUnitPriceDetails->unit_name,
                ];
            }

        } catch (\Exception $e) {

            dd($e);

            return view('OrderSupplyUnitPrice.index')->with([
                'errorMessage' => $e
            ]);
        }

        return view('OrderSupplyUnitPrice.index')->with([
            "search_action"                            => $search_action,
            "condition_date_from"                      => $condition_date_from,
            "condition_date_to"                        => $condition_date_to,
            "condition_company_code"                   => $condition_company_code,
            "condition_company_id"                     => $condition_company_id,
            "condition_company_text"                   => $condition_company_text,
            "condition_product_code"                   => $condition_product_code,
            "condition_product_id"                     => $condition_product_id,
            "condition_product_text"                   => $condition_product_text,
            "orderSupplyUnitPriceList"                 => $orderSupplyUnitPriceList,
            "orderSupplyUnitPriceDetailList"           => $orderSupplyUnitPriceDetailList,
            "order_supply_unit_price_detail_arr"       => $order_supply_unit_price_detail_arr,
            "order_supply_unit_price_detail_count_arr" => $order_supply_unit_price_detail_count_arr
        ]);
    }

    /**
     * 仕入発注単価登録画面
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function create()
    {
        return view('OrderSupplyUnitPrice.create');
    }

    /**
     * 仕入発注単価登録処理
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function register(Request $request)
    {
        // ユーザー情報の取得
        $userInfo    = \Auth::user();
        $userInfoId = $userInfo['id'];

        // リクエストパラメータ取得
        $OrderSupplyUnitPriceData = $request->data['OrderSupplyUnitPrice'];
        $OrderSupplyUnitPriceDetailData = $request->data['OrderSupplyUnitPriceDetail'];

        // トランザクション開始
        DB::connection()->beginTransaction();

        try {

            // ---------------------------------
            // order_supply_unit_pricesを登録する
            // ---------------------------------
            $OrderSupplyUnitPrice = new OrderSupplyUnitPrice;
            $OrderSupplyUnitPrice->company_id       = $OrderSupplyUnitPriceData['supply_company_id'];   // 企業ID
            $OrderSupplyUnitPrice->remarks          = $OrderSupplyUnitPriceData['remarks'];             // 備考
            $OrderSupplyUnitPrice->active           = 1;                                                // 有効フラグ
            $OrderSupplyUnitPrice->created_user_id  = $userInfoId;                                      // 作成者ID
            $OrderSupplyUnitPrice->created          = Carbon::now();                                    // 作成日
            $OrderSupplyUnitPrice->modified_user_id = $userInfoId;                                      // 更新者
            $OrderSupplyUnitPrice->modified         = Carbon::now();                                    // 更新日

            // 登録
            $OrderSupplyUnitPrice->save();

            // 作成したIDを取得する
            $orderSupplyUnitPriceId = $OrderSupplyUnitPrice->id;

            // ----------------------------------------
            // order_supply_unit_price_detailsを登録する
            // ----------------------------------------
            $insertDetailParams = array();

            foreach ($OrderSupplyUnitPriceDetailData as $OrderSupplyUnitPriceDetail) {

                // 税込計算
                $product_data = DB::table('products')->where('id', '=', $OrderSupplyUnitPriceDetail['product_id'])->get();
                $tax_id = $product_data[0]->tax_id;
                $price = 0;
                if ($tax_id == 1) {
                    // 8%
                    $price = round($OrderSupplyUnitPriceDetail['order_unit_price'] * 1.08);
                } else {
                    // 10%
                    $price = round($OrderSupplyUnitPriceDetail['order_unit_price'] * 1.1);
                }

                $insertDetailParams[] = [
                    'order_supply_unit_price_id' => $orderSupplyUnitPriceId,
                    'product_id'                 => $OrderSupplyUnitPriceDetail['product_id'],
                    'staff_id'                   => $OrderSupplyUnitPriceDetail['staff_id'],
                    'apply_from'                 => $OrderSupplyUnitPriceDetail['apply_from'],
                    'notax_price'                => $OrderSupplyUnitPriceDetail['order_unit_price'],
                    'price'                      => $price,
                    'active'                     => 1,
                    'created_user_id'            => $userInfoId,
                    'created'                    => Carbon::now(),
                    'modified_user_id'           => $userInfoId,
                    'modified'                   => Carbon::now(),
                ];

            }

            if(!empty($insertDetailParams)) {
                DB::table('order_supply_unit_price_details')->insert($insertDetailParams);
            }

            // 問題なければコミット
            DB::connection()->commit();

        } catch (\Exception $e) {

            DB::rollback();

            dd($e);

        }

        return redirect('./OrderSupplyUnitPriceIndex');
    }

    /**
     * 仕入発注単価編集画面
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function edit($order_supply_unit_price_id) {

        // --------------
        // 仕入発注単価取得
        // --------------
        $orderSupplyUnitPriceList = DB::table('order_supply_unit_prices AS OrderSupplyUnitPrice')
        ->select(
            'OrderSupplyUnitPrice.id         AS order_supply_unit_price_id',
            'OrderSupplyUnitPrice.remarks    AS remarks',
            'SupplyCompany.id                AS supply_company_id',
            'SupplyCompany.code              AS supply_company_code',
            'SupplyCompany.name              AS supply_company_name',
        )
        ->join('supply_companies AS SupplyCompany', function ($join) {
            $join->on('SupplyCompany.id', '=', 'OrderSupplyUnitPrice.company_id');
        })
        ->where('OrderSupplyUnitPrice.id', '=', $order_supply_unit_price_id)
        ->first();

        // -----------------
        // 仕入発注単価詳細取得
        // -----------------
        $orderSupplyUnitPriceDetailList = DB::table('order_supply_unit_price_details AS OrderSupplyUnitPriceDetail')
        ->select(
            'OrderSupplyUnitPrice.id                AS order_supply_unit_price_id',
            'Product.code                           AS product_code',
            'Product.name                           AS product_name',
            'Product.id                             AS product_id',
            'Staff.code                             AS staff_code',
            'Staff.id                               AS staff_id',
            'OrderSupplyUnitPriceDetail.id          AS order_supply_unit_price_detail_id',
            'OrderSupplyUnitPriceDetail.notax_price AS order_supply_unit_price_detail_price',
            'OrderSupplyUnitPriceDetail.apply_from  AS apply_from',
            'OrderSupplyUnitPriceDetail.staff_id    AS staff_id',
        )
        ->selectRaw('CONCAT(Staff.name_sei," ",Staff.name_mei) AS staff_name')
        ->join('order_supply_unit_prices as OrderSupplyUnitPrice', function ($join) {
            $join->on('OrderSupplyUnitPrice.id', '=', 'OrderSupplyUnitPriceDetail.order_supply_unit_price_id')
            ->where('OrderSupplyUnitPrice.active', '=', true);
        })
        ->join('products as Product', function ($join) {
            $join->on('Product.id', '=', 'OrderSupplyUnitPriceDetail.product_id')
            ->where('Product.active', '=', true);
        })
        ->join('staffs as Staff', function ($join) {
            $join->on('Staff.id', '=', 'OrderSupplyUnitPriceDetail.staff_id')
                 ->where('Staff.active', '=', true);
        })
        ->where('OrderSupplyUnitPriceDetail.order_supply_unit_price_id', '=', $order_supply_unit_price_id)
        ->get();

        return view('OrderSupplyUnitPrice.edit')->with([
            'orderSupplyUnitPriceList'       => $orderSupplyUnitPriceList,
            'orderSupplyUnitPriceDetailList' => $orderSupplyUnitPriceDetailList
        ]);
    }

    /**
     * 編集画面登録処理
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function editRegister(Request $request) {

        // ユーザー情報の取得
        $user_info    = \Auth::user();
        $user_info_id = $user_info['id'];

        // リクエストパラメータの取得
        $OrderSupplyUnitPriceDatas       = $request->data['OrderSupplyUnitPrice'];
        $OrderSupplyUnitPriceDetailDatas = $request->data['OrderSupplyUnitPriceDetail'];

        // トランザクション開始
        DB::connection()->beginTransaction();

        try {

            // --------------------------------
            // order_supply_unit_pricesを登録する
            // --------------------------------
            $OrderSupplyUnitPrice = \App\OrderSupplyUnitPrice::find($OrderSupplyUnitPriceDatas['id']);
            $OrderSupplyUnitPrice->company_id       = $OrderSupplyUnitPriceDatas['supply_company_id'];  // 企業ID
            $OrderSupplyUnitPrice->remarks          = $OrderSupplyUnitPriceDatas['remarks'];            // 備考
            $OrderSupplyUnitPrice->modified_user_id = $user_info_id;                                    // 更新者ユーザーID
            $OrderSupplyUnitPrice->modified         = Carbon::now();                                    // 更新時間

            $OrderSupplyUnitPrice->save();

            // ----------------------------------------
            // order_supply_unit_price_detailsを登録する
            // ----------------------------------------
            // まずは伝票詳細を削除
            \App\OrderSupplyUnitPriceDetail::where('order_supply_unit_price_id', $OrderSupplyUnitPriceDatas['id'])->delete();

            // 登録データ格納用配列初期化
            $detail_datas = array();

            foreach($OrderSupplyUnitPriceDetailDatas as $OrderSupplyUnitPriceDetail){

                // 税込計算
                $product_data = DB::table('products')->where('id', '=', $OrderSupplyUnitPriceDetail['product_id'])->get();
                $tax_id = $product_data[0]->tax_id;
                $price = 0;
                if ($tax_id == 1) {
                    // 8%
                    $price = round($OrderSupplyUnitPriceDetail['order_unit_price'] * 1.08);
                } else {
                    // 10%
                    $price = round($OrderSupplyUnitPriceDetail['order_unit_price'] * 1.1);
                }

                $detail_datas[] = [
                    'order_supply_unit_price_id' => $OrderSupplyUnitPriceDatas['id'],
                    'product_id'                 => $OrderSupplyUnitPriceDetail['product_id'],
                    'staff_id'                   => $OrderSupplyUnitPriceDetail['staff_id'],
                    'apply_from'                 => $OrderSupplyUnitPriceDetail['apply_from'],
                    'notax_price'                => $OrderSupplyUnitPriceDetail['order_unit_price'],
                    'price'                      => $price,
                    'active'                     => 1,
                    'created_user_id'            => $user_info_id,
                    'created'                    => Carbon::now(),
                    'modified_user_id'           => $user_info_id,
                    'modified'                   => Carbon::now(),
                ];

            }

            if(!empty($detail_datas)) {
                DB::table('order_supply_unit_price_details')->insert($detail_datas);
            }

            // 問題なければコミット
            DB::connection()->commit();

        } catch (\Exception $e) {

            DB::rollback();

            dd($e);

        }

        return redirect('./OrderSupplyUnitPriceIndex');
    }

    /**
     * 製品ID用の行追加処理Ajax
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function AjaxAddProduct(Request $request) {

        // 製品数を取得
        $product_num = $request->product_num;
        if(empty($product_num)) $product_num = 1;

        $tabInitialNum = intval(4*$product_num + 3);

        $today = date('Y-m-d');

        // 追加伝票形成
        $ajaxHtml = '';
        $ajaxHtml .= " <tr id='product-partition-".$product_num."' class='partition-area'>";
        $ajaxHtml .= " </tr>";
        $ajaxHtml .= " <tr id='product-upper-".$product_num."'>";
        $ajaxHtml .= "     <td class='width-10' id='product-code-area-".$product_num."'>";
        $ajaxHtml .= "         <input type='hidden' id='product_id_".$product_num."' name='data[OrderSupplyUnitPriceDetail][".$product_num."][product_id]'>";
        $ajaxHtml .= "     </td>";
        $ajaxHtml .= "     <td class='width-20'>";
        $ajaxHtml .= "         <input type='text' class='form-control' id='product_text_".$product_num."' name='data[OrderSupplyUnitPriceDetail][".$product_num."][product_text]' placeholder='製品欄' readonly>";
        $ajaxHtml .= "     </td>";
        $ajaxHtml .= "     <td class='width-10'>";
        $ajaxHtml .= "         <input type='number' class='form-control' id='order_unit_price_".$product_num."' name='data[OrderSupplyUnitPriceDetail][".$product_num."][order_unit_price]' tabindex='".($tabInitialNum + 1)."'>";
        $ajaxHtml .= "     </td>";
        $ajaxHtml .= "     <td class='width-10'>";
        $ajaxHtml .= "         <input type='date' class='form-control' id='apply_from' name='data[OrderSupplyUnitPriceDetail][".$product_num."][apply_from]' value='" . $today ."' tabindex='".($tabInitialNum + 2)."'>";
        $ajaxHtml .= "     </td>";
        $ajaxHtml .= "     <td class='width-10' id='staff-code-area-".$product_num."'>";
        $ajaxHtml .= "         <input type='hidden' id='staff_id_".$product_num."' name='data[OrderSupplyUnitPriceDetail][".$product_num."][staff_id]' value='9'>";
        $ajaxHtml .= "     </td>";
        $ajaxHtml .= "     <td class='width-20'>";
        $ajaxHtml .= "         <input type='text' class='form-control' id='staff_text_".$product_num."' name='data[OrderSupplyUnitPriceDetail][".$product_num."][staff_text]' value='石塚 貞雄' placeholder='担当者' readonly>";
        $ajaxHtml .= "     </td>";
        $ajaxHtml .= "     <td rowspan='2' class='width-5'>";
        $ajaxHtml .= "         <button id='remove-product-btn' type='button' class='btn remove-product-btn btn-secondary' onclick='javascript:removeProduct(".$product_num.") '>削除</button>";
        $ajaxHtml .= "     </td>";
        $ajaxHtml .= " </tr>";
        $ajaxHtml .= " <hr/>";

        //-------------------------------
        // AutoCompleteの要素は別で形成する
        //-------------------------------
        // 製品ID
        $autoCompleteProduct = "<input type='text' class='form-control product_code_input' id='product_code_".$product_num."' name='data[OrderSupplyUnitPrice][".$product_num."][product_code]' tabindex='".$tabInitialNum."''>";

        // スタッフID
        $autoCompleteStaff = "<input type='text' class='form-control staff_code_input' id='staff_code_".$product_num."' name='data[OrderSupplyUnitPrice][".$product_num."][staff_code]' value='1009' tabindex='".($tabInitialNum + 3)."''>";

        $product_num = intval($product_num) + 1;

        $returnArray = array($product_num, $ajaxHtml, $autoCompleteProduct, $autoCompleteStaff);

        return $returnArray;
    }
}