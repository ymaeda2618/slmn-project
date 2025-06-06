<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\OrderSaleUnitPrice;
use App\OrderSaleUnitPriceDetail;
use Carbon\Carbon;

class OrderSaleUnitPriceController extends Controller
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
     * 売上発注単価一覧
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
            $search_action = '../OrderSaleUnitPriceIndex';
        } else {
            $search_action = './OrderSaleUnitPriceIndex';
        }

        // postできたか、getできたか
        if ($_SERVER["REQUEST_METHOD"] != "POST"
        ) { // ページング処理

            $condition_date_from = $request->session()->get('order_sale_condition_date_from');
            $condition_date_to   = $request->session()->get('order_sale_condition_date_to');

            // 空値の場合は初期値を設定
            if(empty($condition_date_from)) $condition_date_from = date('Y-m-d');
            if(empty($condition_date_to)) $condition_date_to     = date('Y-m-d');

            $condition_company_code = $request->session()->get('order_sale_condition_company_code');
            $condition_company_id   = $request->session()->get('order_sale_condition_company_id');
            $condition_company_text = $request->session()->get('order_sale_condition_company_text');
            $condition_product_code = $request->session()->get('order_sale_condition_product_code');
            $condition_product_id   = $request->session()->get('order_sale_condition_product_id');
            $condition_product_text = $request->session()->get('order_sale_condition_product_text');

        } else { // POST時の処理

            if (isset($_POST['search-btn'])) { // 検索ボタン押された時の処理

                $condition_company_code = $request->data['OrderSaleUnitPrice']['sale_company_code'];
                $condition_company_id   = $request->data['OrderSaleUnitPrice']['sale_company_id'];
                $condition_company_text = $request->data['OrderSaleUnitPrice']['sale_company_text'];
                $condition_product_code = $request->data['OrderSaleUnitPrice']['product_code'];
                $condition_product_id   = $request->data['OrderSaleUnitPrice']['product_id'];
                $condition_product_text = $request->data['OrderSaleUnitPrice']['product_text'];

                // 日付の設定
                $condition_date_from = $request->data['OrderSaleUnitPrice']['apply_from'];
                $condition_date_to   = $request->data['OrderSaleUnitPrice']['apply_to'];
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

                $request->session()->put('order_sale_condition_date_from', $condition_date_from);
                $request->session()->put('order_sale_condition_date_to', $condition_date_to);
                $request->session()->put('order_sale_condition_company_code', $condition_company_code);
                $request->session()->put('order_sale_condition_company_id', $condition_company_id);
                $request->session()->put('order_sale_condition_company_text', $condition_company_text);
                $request->session()->put('order_sale_condition_product_code', $condition_product_code);
                $request->session()->put('order_sale_condition_product_id', $condition_product_id);
                $request->session()->put('order_sale_condition_product_text', $condition_product_text);

            } else { // リセットボタンが押された時の処理

                $condition_date_from    = date('Y-m-d');
                $condition_date_to      = date('Y-m-d');
                $condition_company_code = null;
                $condition_company_id   = null;
                $condition_company_text = null;
                $condition_product_code = null;
                $condition_product_id   = null;
                $condition_product_text = null;
                $request->session()->forget('order_sale_condition_date_from');
                $request->session()->forget('order_sale_condition_date_to');
                $request->session()->forget('order_sale_condition_company_code');
                $request->session()->forget('order_sale_condition_company_id');
                $request->session()->forget('order_sale_condition_company_text');
                $request->session()->forget('order_sale_condition_product_code');
                $request->session()->forget('order_sale_condition_product_id');
                $request->session()->forget('order_sale_condition_product_text');
            }
        }

        try {

            // order_sale_unit_price_detailsのサブクエリを作成
            $product_sub_query = null;
            if(!empty($condition_product_id)) {
                $product_sub_query = DB::table('order_sale_unit_price_details as SubTable')
                ->select('SubTable.order_sale_unit_price_id AS sub_order_sale_unit_price_id')
                ->where('SubTable.product_id', '=', $condition_product_id)
                ->groupBy('SubTable.order_sale_unit_price_id');
            }

            // 売上発注単価一覧を取得
            $orderSaleUnitPriceList = DB::table('order_sale_unit_prices AS OrderSaleUnitPrice')
            ->select(
                'OrderSaleUnitPrice.id       AS order_sale_unit_price_id',
                'OrderSaleUnitPrice.modified AS modified',
                'SaleCompany.code            AS sale_company_code',
                'SaleCompany.name            AS sale_company_name'
            )
            ->selectRaw('MIN(OrderSaleUnitPriceDetail.apply_from) AS apply_from')
            ->join('sale_companies AS SaleCompany', function ($join) {
                $join->on('SaleCompany.id', '=', 'OrderSaleUnitPrice.company_id');
            })
            ->if(!empty($condition_product_id), function ($query) use ($product_sub_query) {
                return $query
                       ->join(DB::raw('('. $product_sub_query->toSql() .') as OrderSaleUnitPriceDetail'), 'OrderSaleUnitPriceDetail.sub_order_sale_unit_price_id', '=', 'OrderSaleUnitPrice.id')
                       ->mergeBindings($product_sub_query);
            })
            ->if(!empty($condition_date_from) && !empty($condition_date_to), function ($query) use ($condition_date_from, $condition_date_to) {
                return $query
                        ->join('order_sale_unit_price_details AS OrderSaleUnitPriceDetail', 'OrderSaleUnitPrice.id', '=', 'OrderSaleUnitPriceDetail.order_sale_unit_price_id')
                        ->whereBetween('OrderSaleUnitPriceDetail.apply_from', [$condition_date_from, $condition_date_to]);
            })
            ->if(!empty($condition_company_id), function ($query) use ($condition_company_id) {
                return $query->where('OrderSaleUnitPrice.company_id', '=', $condition_company_id);
            })
            ->where('OrderSaleUnitPrice.active', '=', '1')
            ->groupBy('OrderSaleUnitPrice.id')
            ->orderBy('OrderSaleUnitPrice.id', 'asc')
            ->paginate(10);

            // ===================
            // 売上発注単価詳細を取得
            // ===================
            $orderSaleUnitPriceDetailList = DB::table('order_sale_unit_price_details AS OrderSaleUnitPriceDetail')
            ->select(
                'OrderSaleUnitPrice.id                AS order_sale_unit_price_id',
                'Product.code                         AS product_code',
                'Product.name                         AS product_name',
                'Product.tax_id                       AS product_tax_id',
                'OrderSaleUnitPriceDetail.id          AS order_sale_unit_price_detail_id',
                'OrderSaleUnitPriceDetail.notax_price AS order_sale_unit_price_detail_price',
                'Unit.name                            AS unit_name'
            )
            ->join('order_sale_unit_prices as OrderSaleUnitPrice', function ($join) {
                $join->on('OrderSaleUnitPrice.id', '=', 'OrderSaleUnitPriceDetail.order_sale_unit_price_id')
                ->where('OrderSaleUnitPrice.active', '=', true);
            })
            ->join('products as Product', function ($join) {
                $join->on('Product.id', '=', 'OrderSaleUnitPriceDetail.product_id')
                ->where('Product.active', '=', true);
            })
            ->join('units as Unit', function ($join) {
                $join->on('Unit.id', '=', 'Product.unit_id')
                ->where('Unit.active', '=', true);
            })
            ->leftJoin('sale_companies as SaleCompany', function ($join) {
                $join->on('SaleCompany.id', '=', 'OrderSaleUnitPrice.company_id')
                ->where('SaleCompany.active', '=', true);
            })
            ->get();

            // 各伝票にいくつ明細がついているのかをカウントする配列
            $order_sale_unit_price_detail_arr = array();

            // 伝票詳細で取得したDBをループ
            foreach($orderSaleUnitPriceDetailList as $orderSaleUnitPriceDetails){

                if(!isset($order_sale_unit_price_detail_count_arr[$orderSaleUnitPriceDetails->order_sale_unit_price_id])){
                    $order_sale_unit_price_detail_count_arr[$orderSaleUnitPriceDetails->order_sale_unit_price_id] = 0;
                }

                $order_sale_unit_price_detail_count_arr[$orderSaleUnitPriceDetails->order_sale_unit_price_id] += 1;

                $order_sale_unit_price_detail_arr[$orderSaleUnitPriceDetails->order_sale_unit_price_id][] = [

                    'product_code'                       => $orderSaleUnitPriceDetails->product_code,
                    'product_name'                       => $orderSaleUnitPriceDetails->product_name,
                    'product_tax_id'                     => $orderSaleUnitPriceDetails->product_tax_id,
                    'order_sale_unit_price_detail_id'    => $orderSaleUnitPriceDetails->order_sale_unit_price_detail_id,
                    'order_sale_unit_price_detail_price' => $orderSaleUnitPriceDetails->order_sale_unit_price_detail_price,
                    'unit_name'                          => $orderSaleUnitPriceDetails->unit_name,
                ];
            }

        } catch (\Exception $e) {

            dd($e);

            return view('OrderSaleUnitPrice.index')->with([
                'errorMessage' => $e
            ]);
        }

        return view('OrderSaleUnitPrice.index')->with([
            "search_action"                          => $search_action,
            "condition_date_from"                    => $condition_date_from,
            "condition_date_to"                      => $condition_date_to,
            "condition_company_code"                 => $condition_company_code,
            "condition_company_id"                   => $condition_company_id,
            "condition_company_text"                 => $condition_company_text,
            "condition_product_code"                 => $condition_product_code,
            "condition_product_id"                   => $condition_product_id,
            "condition_product_text"                 => $condition_product_text,
            "orderSaleUnitPriceList"                 => $orderSaleUnitPriceList,
            "orderSaleUnitPriceDetailList"           => $orderSaleUnitPriceDetailList,
            "order_sale_unit_price_detail_arr"       => $order_sale_unit_price_detail_arr,
            "order_sale_unit_price_detail_count_arr" => $order_sale_unit_price_detail_count_arr
        ]);
    }

    /**
     * 売上発注単価登録画面
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function create()
    {
        return view('OrderSaleUnitPrice.create');
    }

    /**
     * 売上発注単価登録処理
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function register(Request $request)
    {
        // ユーザー情報の取得
        $userInfo    = \Auth::user();
        $userInfoId = $userInfo['id'];

        // リクエストパラメータ取得
        $OrderSaleUnitPriceData = $request->data['OrderSaleUnitPrice'];
        $OrderSaleUnitPriceDetailData = $request->data['OrderSaleUnitPriceDetail'];

        // トランザクション開始
        DB::connection()->beginTransaction();

        try {

            // ---------------------------------
            // order_sale_unit_pricesを登録する
            // ---------------------------------
            $OrderSaleUnitPrice = new OrderSaleUnitPrice;
            $OrderSaleUnitPrice->company_id       = $OrderSaleUnitPriceData['sale_company_id']; // 企業ID
            $OrderSaleUnitPrice->remarks          = $OrderSaleUnitPriceData['remarks'];         // 備考
            $OrderSaleUnitPrice->active           = 1;                                          // 有効フラグ
            $OrderSaleUnitPrice->created_user_id  = $userInfoId;                                // 作成者ID
            $OrderSaleUnitPrice->created          = Carbon::now();                              // 作成日
            $OrderSaleUnitPrice->modified_user_id = $userInfoId;                                // 更新者
            $OrderSaleUnitPrice->modified         = Carbon::now();                              // 更新日

            // 登録
            $OrderSaleUnitPrice->save();

            // 作成したIDを取得する
            $orderSaleUnitPriceId = $OrderSaleUnitPrice->id;

            // ----------------------------------------
            // order_sale_unit_price_detailsを登録する
            // ----------------------------------------
            $insertDetailParams = array();

            foreach ($OrderSaleUnitPriceDetailData as $OrderSaleUnitPriceDetail) {

                // 税込計算
                $product_data = DB::table('products')->where('id', '=', $OrderSaleUnitPriceDetail['product_id'])->get();
                $tax_id = $product_data[0]->tax_id;
                $price = 0;
                if ($tax_id == 1) {
                    // 8%
                    $price = round($OrderSaleUnitPriceDetail['order_unit_price'] / 1.08);
                } else {
                    // 10%
                    $price = round($OrderSaleUnitPriceDetail['order_unit_price'] / 1.1);
                }

                $insertDetailParams[] = [
                    'order_sale_unit_price_id' => $orderSaleUnitPriceId,
                    'product_id'               => $OrderSaleUnitPriceDetail['product_id'],
                    'staff_id'                 => $OrderSaleUnitPriceDetail['staff_id'],
                    'apply_from'               => $OrderSaleUnitPriceDetail['apply_from'],
                    'notax_price'              => $OrderSaleUnitPriceDetail['order_unit_price'],
                    'price'                    => $price,
                    'active'                   => 1,
                    'created_user_id'          => $userInfoId,
                    'created'                  => Carbon::now(),
                    'modified_user_id'         => $userInfoId,
                    'modified'                 => Carbon::now(),
                ];

            }

            if(!empty($insertDetailParams)) {
                DB::table('order_sale_unit_price_details')->insert($insertDetailParams);
            }

            // 問題なければコミット
            DB::connection()->commit();

        } catch (\Exception $e) {

            DB::rollback();

            dd($e);

        }

        return redirect('./OrderSaleUnitPriceIndex');
    }

    /**
     * 売上発注単価編集画面
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function edit($order_sale_unit_price_id) {

        // --------------
        // 売上発注単価取得
        // --------------
        $orderSaleUnitPriceList = DB::table('order_sale_unit_prices AS OrderSaleUnitPrice')
        ->select(
            'OrderSaleUnitPrice.id         AS order_sale_unit_price_id',
            'OrderSaleUnitPrice.remarks    AS remarks',
            'SaleCompany.id                AS sale_company_id',
            'SaleCompany.code              AS sale_company_code',
            'SaleCompany.name              AS sale_company_name',
        )
        ->join('sale_companies AS SaleCompany', function ($join) {
            $join->on('SaleCompany.id', '=', 'OrderSaleUnitPrice.company_id');
        })
        ->where('OrderSaleUnitPrice.id', '=', $order_sale_unit_price_id)
        ->first();

        // -----------------
        // 売上発注単価詳細取得
        // -----------------
        $orderSaleUnitPriceDetailList = DB::table('order_sale_unit_price_details AS OrderSaleUnitPriceDetail')
        ->select(
            'OrderSaleUnitPrice.id                AS order_sale_unit_price_id',
            'Product.code                         AS product_code',
            'Product.name                         AS product_name',
            'Product.id                           AS product_id',
            'Staff.code                           AS staff_code',
            'Staff.id                             AS staff_id',
            'OrderSaleUnitPriceDetail.id          AS order_sale_unit_price_detail_id',
            'OrderSaleUnitPriceDetail.notax_price AS order_sale_unit_price_detail_price',
            'OrderSaleUnitPriceDetail.apply_from  AS apply_from',
            'OrderSaleUnitPriceDetail.staff_id    AS staff_id'
        )
        ->selectRaw('CONCAT(Staff.name_sei," ",Staff.name_mei) AS staff_name')
        ->join('order_sale_unit_prices as OrderSaleUnitPrice', function ($join) {
            $join->on('OrderSaleUnitPrice.id', '=', 'OrderSaleUnitPriceDetail.order_sale_unit_price_id')
            ->where('OrderSaleUnitPrice.active', '=', true);
        })
        ->join('products as Product', function ($join) {
            $join->on('Product.id', '=', 'OrderSaleUnitPriceDetail.product_id')
            ->where('Product.active', '=', true);
        })
        ->join('staffs as Staff', function ($join) {
            $join->on('Staff.id', '=', 'OrderSaleUnitPriceDetail.staff_id')
                 ->where('Staff.active', '=', true);
        })
        ->where('OrderSaleUnitPriceDetail.order_sale_unit_price_id', '=', $order_sale_unit_price_id)
        ->get();

        return view('OrderSaleUnitPrice.edit')->with([
            'orderSaleUnitPriceList'       => $orderSaleUnitPriceList,
            'orderSaleUnitPriceDetailList' => $orderSaleUnitPriceDetailList
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
        $OrderSaleUnitPriceDatas       = $request->data['OrderSaleUnitPrice'];
        $OrderSaleUnitPriceDetailDatas = $request->data['OrderSaleUnitPriceDetail'];

        // トランザクション開始
        DB::connection()->beginTransaction();

        try {

            // --------------------------------
            // order_sale_unit_pricesを登録する
            // --------------------------------
            $OrderSaleUnitPrice = \App\OrderSaleUnitPrice::find($OrderSaleUnitPriceDatas['id']);
            $OrderSaleUnitPrice->company_id       = $OrderSaleUnitPriceDatas['sale_company_id'];    // 企業ID
            $OrderSaleUnitPrice->remarks          = $OrderSaleUnitPriceDatas['remarks'];            // 備考
            $OrderSaleUnitPrice->modified_user_id = $user_info_id;                                  // 更新者ユーザーID
            $OrderSaleUnitPrice->modified         = Carbon::now();                                  // 更新時間

            $OrderSaleUnitPrice->save();

            // ----------------------------------------
            // order_sale_unit_price_detailsを登録する
            // ----------------------------------------
            // まずは伝票詳細を削除
            \App\OrderSaleUnitPriceDetail::where('order_sale_unit_price_id', $OrderSaleUnitPriceDatas['id'])->delete();

            // 登録データ格納用配列初期化
            $detail_datas = array();

            foreach($OrderSaleUnitPriceDetailDatas as $OrderSaleUnitPriceDetail){

                // 税込計算
                $product_data = DB::table('products')->where('id', '=', $OrderSaleUnitPriceDetail['product_id'])->get();
                $tax_id = $product_data[0]->tax_id;
                $price = 0;
                if ($tax_id == 1) {
                    // 8%
                    $price = round($OrderSaleUnitPriceDetail['order_unit_price'] * 1.08);
                } else {
                    // 10%
                    $price = round($OrderSaleUnitPriceDetail['order_unit_price'] * 1.1);
                }

                $detail_datas[] = [
                    'order_sale_unit_price_id' => $OrderSaleUnitPriceDatas['id'],
                    'product_id'               => $OrderSaleUnitPriceDetail['product_id'],
                    'staff_id'                 => $OrderSaleUnitPriceDetail['staff_id'],
                    'apply_from'               => $OrderSaleUnitPriceDetail['apply_from'],
                    'notax_price'              => $OrderSaleUnitPriceDetail['order_unit_price'],
                    'price'                    => $price,
                    'active'                   => 1,
                    'created_user_id'          => $user_info_id,
                    'created'                  => Carbon::now(),
                    'modified_user_id'         => $user_info_id,
                    'modified'                 => Carbon::now(),
                ];

            }

            if(!empty($detail_datas)) {
                DB::table('order_sale_unit_price_details')->insert($detail_datas);
            }

            // 問題なければコミット
            DB::connection()->commit();

        } catch (\Exception $e) {

            DB::rollback();

            dd($e);

        }

        return redirect('./OrderSaleUnitPriceIndex');
    }

    /**
     * 製品ID用の行追加処理Ajax
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function AjaxAddSaleProduct(Request $request) {

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
        $ajaxHtml .= "         <input type='hidden' id='product_id_".$product_num."' name='data[OrderSaleUnitPriceDetail][".$product_num."][product_id]'>";
        $ajaxHtml .= "     </td>";
        $ajaxHtml .= "     <td class='width-20'>";
        $ajaxHtml .= "         <input type='text' class='form-control' id='product_text_".$product_num."' name='data[OrderSaleUnitPriceDetail][".$product_num."][product_text]' placeholder='製品欄' readonly>";
        $ajaxHtml .= "     </td>";
        $ajaxHtml .= "     <td class='width-10'>";
        $ajaxHtml .= "         <input type='number' class='form-control' id='order_unit_price_".$product_num."' name='data[OrderSaleUnitPriceDetail][".$product_num."][order_unit_price]' tabindex='".($tabInitialNum + 1)."'>";
        $ajaxHtml .= "     </td>";
        $ajaxHtml .= "     <td class='width-10'>";
        $ajaxHtml .= "         <input type='date' class='form-control' id='apply_from' name='data[OrderSaleUnitPriceDetail][".$product_num."][apply_from]' value='" . $today ."' tabindex='".($tabInitialNum + 2)."'>";
        $ajaxHtml .= "     </td>";
        $ajaxHtml .= "     <td class='width-10' id='staff-code-area-".$product_num."'>";
        $ajaxHtml .= "         <input type='hidden' id='staff_id_".$product_num."' name='data[OrderSaleUnitPriceDetail][".$product_num."][staff_id]' value='9'>";
        $ajaxHtml .= "     </td>";
        $ajaxHtml .= "     <td class='width-20'>";
        $ajaxHtml .= "         <input type='text' class='form-control' id='staff_text_".$product_num."' name='data[OrderSaleUnitPriceDetail][".$product_num."][staff_text]' placeholder='担当者' value='石塚 貞雄' readonly>";
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
        $autoCompleteProduct = "<input type='text' class='form-control product_code_input' id='product_code_".$product_num."' name='data[OrderSaleUnitPrice][".$product_num."][product_code]' tabindex='".$tabInitialNum."''>";

        // 担当者
        $autoCompleteStaff = "<input type='text' class='form-control staff_code_input' id='staff_code_".$product_num."' name='data[OrderSaleUnitPrice][".$product_num."][staff_code]' value='1009' tabindex='".($tabInitialNum + 3)."''>";

        $product_num = intval($product_num) + 1;

        $returnArray = array($product_num, $ajaxHtml, $autoCompleteProduct, $autoCompleteStaff);

        return $returnArray;
    }
}