<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Product;
use App\ProductType;
use App\Status;
use App\Tax;
use App\Unit;
use Carbon\Carbon;
use Exception;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Str;

class ProductController extends Controller
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
     * 製品一覧
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
            $search_action = '../ProductIndex';
        } else {
            $search_action = './ProductIndex';
        }

        // 変数の初期化
        $product_search_type = 1;
        $product_search_text = null;
        $product_name        = null;
        $product_code        = null;

        // postできたか、getできたか
        if ($_SERVER["REQUEST_METHOD"] != "POST") { // ページング処理

            $condition_date_from     = $request->session()->get('condition_date_from');
            $condition_date_to       = $request->session()->get('condition_date_to');

            $condition_product_type  = $request->session()->get('condition_product_type');
            $condition_status_id     = $request->session()->get('condition_status_id');

            $condition_search_text   = $request->session()->get('condition_search_text');
            $condition_product_code  = $request->session()->get('condition_product_code');
            $condition_product_id    = $request->session()->get('condition_product_id');
            $condition_product_text  = $request->session()->get('condition_product_text');

        } else { // POST時の処理

            if (isset($_POST['search-btn'])) { // 検索ボタン押された時の処理

                if (isset($request->data['product'])) {
                    $req_product_data = $request->data['product'];
                }

                 // 日付の設定
                $condition_date_from     = isset($req_product_data['date_from']) ? $req_product_data['date_from'] : NULL;
                $condition_date_to       = isset($req_product_data['date_to']) ? $req_product_data['date_to'] : NULL;
                // どちらか片方しか入力されなかった場合は同じ日付を入れる
                if (!empty($condition_date_from) && empty($condition_date_to)) {
                    $condition_date_to = $condition_date_from;
                }
                if (empty($condition_date_from) && !empty($condition_date_to)) {
                    $condition_date_from = $condition_date_to;
                }
                $condition_product_type  = isset($req_product_data['product_type']) ? $req_product_data['product_type'] : NULL;
                $condition_status_id     = isset($req_product_data['status_id']) ? $req_product_data['status_id'] : NULL;
                $condition_search_text   = isset($req_product_data['search_text']) ? $req_product_data['search_text'] : NULL;
                $condition_product_code  = isset($req_product_data['product_code']) ? $req_product_data['product_code'] : NULL;
                $condition_product_id    = isset($req_product_data['product_id']) ? $req_product_data['product_id'] : NULL;
                $condition_product_text  = isset($req_product_data['product_text']) ? $req_product_data['product_text'] : NULL;

                $request->session()->put('condition_date_from', $condition_date_from);
                $request->session()->put('condition_date_to', $condition_date_to);
                $request->session()->put('condition_product_type', $condition_product_type);
                $request->session()->put('condition_status_id', $condition_status_id);
                $request->session()->put('condition_search_text', $condition_search_text);
                $request->session()->put('condition_product_code', $condition_product_code);
                $request->session()->put('condition_product_id', $condition_product_id);
                $request->session()->put('condition_product_text', $condition_product_text);

            } else { // リセットボタンが押された時の処理

                $condition_date_from     = null;
                $condition_date_to       = null;
                $condition_product_type  = null;
                $condition_status_id     = null;
                $condition_search_text   = null;
                $condition_product_code  = null;
                $condition_product_id    = null;
                $condition_product_text  = null;

                $request->session()->forget('condition_date_from');
                $request->session()->forget('condition_date_to');
                $request->session()->forget('condition_product_type');
                $request->session()->forget('condition_status_id');
                $request->session()->forget('condition_search_text');
                $request->session()->forget('condition_product_code');
                $request->session()->forget('condition_product_id');
                $request->session()->forget('condition_product_text');
            }
        }

        // CSV出力種別を設定
        $csv_type_arr = [
            0 => '自動レジ用アップロード',
          //  1 => '通常商品マスタ',
        ];

        try {
            // カテゴリーカテゴリー
            $productTypeList = ProductType::where([
                ['auto_regis_type_flg', 0],
                ['active', 1],
            ])->orderBy('sort', 'asc')->get();

            // 製品状態
            $statusList = Status::where([
                ['active', 1],
            ])->orderBy('sort', 'asc')->get();

            // 税率
            $taxList = Tax::where([
                ['active', 1],
            ])->orderBy('sort', 'asc')->get();

            // 単位
            $unitList = Unit::where([
                ['active', 1],
            ])->orderBy('sort', 'asc')->get();

            // 製品一覧を取得
            $productList = DB::table('products AS Product')
            ->select(
                'Product.id             AS product_id',
                'Product.code           AS product_code',
                'Product.product_type   AS product_type',
                'ProductType.name       AS product_type_name',
                'Status.name            AS status_name',
                'Tax.name               AS tax_name',
                'Product.name           AS product_name',
                'Unit.name              AS unit_name'
            )
            ->leftJoin('product_types AS ProductType', function ($join) {
                $join->on('ProductType.id', '=', 'Product.product_type');
            })
            ->leftJoin('statuses AS Status', function ($join) {
                $join->on('Status.id', '=', 'Product.status_id');
            })
            ->join('taxes AS Tax', function ($join) {
                $join->on('Tax.id', '=', 'Product.tax_id');
            })
            ->join('units AS Unit', function ($join) {
                $join->on('Unit.id', '=', 'Product.unit_id');
            })
            ->if(!empty($condition_date_from) && !empty($condition_date_to), function ($query) use ($condition_date_from, $condition_date_to) {
                return $query->whereBetween('Product.created', [$condition_date_from, $condition_date_to]);
            })
            ->if(!empty($condition_product_type), function ($queryDetail) use ($condition_product_type) {
                return $queryDetail->where('Product.product_type', '=', $condition_product_type);
            })
            ->if(!empty($condition_status_id), function ($queryDetail) use ($condition_status_id) {
                return $queryDetail->where('Product.status_id', '=', $condition_status_id);
            })
            ->if(!empty($condition_search_text), function ($queryDetail) use ($condition_search_text) {
                return $queryDetail->where('Product.name', 'like', '%'.$condition_search_text.'%');
            })
            ->if(!empty($condition_product_id), function ($queryDetail) use ($condition_product_id) {
                return $queryDetail->where('Product.id', '=', $condition_product_id);
            })
            ->where([
                ['Product.new_product_flg', '=', '1'],
                ['Product.active', '=', '1'],
                ['ProductType.auto_regis_type_flg', '=', '0'],
            ])
            ->orderBy('Product.created', 'desc')->paginate(20);

            // 対象日付のチェック
            $product_search_type_name = "";
            $product_search_type_code = "";
            if($product_search_type == 1) $product_search_type_name = "checked";
            else  $product_search_type_code = "checked";


        } catch (\Exception $e) {

            dd($e);

            return view('Product.complete')->with([
                'errorMessage' => $e
            ]);
        }

        return view('Product.index')->with([
            "action"                   => $search_action,
            "condition_date_from"     => $condition_date_from,
            "condition_date_to"        => $condition_date_to,
            "condition_product_type"   => $condition_product_type,
            "condition_status_id"      => $condition_status_id,
            "condition_search_text"   => $condition_search_text,
            "condition_product_code"   => $condition_product_code,
            "condition_product_id"     => $condition_product_id,
            "condition_product_text"   => $condition_product_text,
            "search_action"            => $search_action,
            "productTypeList"          => $productTypeList,
            "statusList"               => $statusList,
            "taxList"                  => $taxList,
            "unitList"                 => $unitList,
            "productList"              => $productList,
            "csv_type_arr"             => $csv_type_arr,
        ]);
    }

    /**
     * 製品情報編集
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function edit(Request $request, $product_id)
    {
        // カテゴリーカテゴリー
        $productTypeList = ProductType::where([
            ['auto_regis_type_flg', 0],
            ['active', 1],
        ])->orderBy('sort', 'asc')->get();

        // 製品状態
        $statusList = Status::where([
            ['active', 1],
        ])->orderBy('sort', 'asc')->get();

        // 税率
        $taxList = Tax::where([
            ['active', 1],
        ])->orderBy('sort', 'asc')->get();

        // 単位
        $unitList = Unit::where([
            ['active', 1],
        ])->orderBy('sort', 'asc')->get();

        // 製品を取得
        $editProduct = DB::table('products AS Product')
        ->select(
            'Product.id                AS product_id',
            'Product.code              AS product_code',
            'Product.product_type      AS product_type',
            'Product.status_id         AS status_id',
            'Product.tax_id            AS tax_id',
            'Product.name              AS product_name',
            'Product.yomi              AS yomi',
            'Product.unit_id           AS unit_id',
            'Product.inventory_unit_id AS inventory_unit_id',
            'Product.display_flg       AS display_flg'
        )
        ->where([
            ['Product.id', '=', $product_id],
            ['Product.active', '=', '1'],
        ])
        ->first();

        // エラーメッセージ取得
        $error_message       = $request->session()->get('error_message');
        $request->session()->forget('error_message');


        return view('Product.edit')->with([
            "productTypeList"  => $productTypeList,
            "statusList"    => $statusList,
            "taxList"       => $taxList,
            "unitList"      => $unitList,
            "editProduct"   => $editProduct,
            "error_message" => $error_message,
        ]);
    }


    /**
     * 製品新規登録画面
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function create(Request $request)
    {
        // カテゴリーカテゴリー
        $productTypeList = ProductType::where([
            ['auto_regis_type_flg', 0],
            ['active', 1],
        ])->orderBy('sort', 'asc')->get();

        // 製品状態
        $statusList = Status::where([
            ['active', 1],
        ])->orderBy('sort', 'asc')->get();

        // 税率
        $taxList = Tax::where([
            ['active', 1],
        ])->orderBy('sort', 'asc')->get();

        // 単位
        $unitList = Unit::where([
            ['active', 1],
        ])->orderBy('sort', 'asc')->get();

        // エラーメッセージ取得
        $error_message       = $request->session()->get('error_message');
        $request->session()->forget('error_message');


        return view('Product.create')->with([
            "productTypeList"           => $productTypeList,
            "statusList"                => $statusList,
            "taxList"                   => $taxList,
            "unitList"                  => $unitList,
            "error_message"             => $error_message,
        ]);
    }

     /**
     * 製品新規追加 確認
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function confirm(Request $request)
    {
        if($request->submit_type == 1){
            $action_url = './ProductComplete';
        } else {
            $action_url = './ProductEditComplete';
        }

        // 商品カテゴリー
        $productTypeList = ProductType::where([
            ['id', $request->data['Product']['product_type']],
        ])->first();

        // 製品状態
        $statusList = Status::where([
            ['id', $request->data['Product']['status_id']],
        ])->first();

        // 税率
        $taxList = Tax::where([
            ['id', $request->data['Product']['tax_id']],
        ])->first();

        // 単位
        $unitList = Unit::where([
            ['id', $request->data['Product']['unit_id']],
        ])->first();

        // 仕入単位
        $inventoryUnitList = Unit::where([
            ['id', $request->data['Product']['inventory_unit_id']],
        ])->first();

        // 各種名称を格納
        $request->product_type_name   = $productTypeList->name;
        $request->status_name         = $statusList->name;
        $request->tax_name            = $taxList->name;
        $request->unit_name           = $unitList->name;
        $request->inventory_unit_name = $inventoryUnitList->name;
        $request->display_flg_name    = $request->data['Product']['display_flg'] === "1" ? '表示' : '非表示';

        return view('Product.confirm')->with([
            "action_url"           => $action_url,
            "request"              => $request,
        ]);
    }

    /**
     * 製品新規追加 修正登録
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function editComplete(Request $request)
    {

        // ユーザー情報の取得
        $user_info    = \Auth::user();
        $user_info_id = $user_info['id'];

        // トランザクション処理
        DB::beginTransaction();

        // エラータイプを初期化
        $exception_type = 0;

        try {

            // リクエストされたコードを格納
            $product_code = $request->data['Product']['code'];

            // codeが存在するかチェック
            $productCodeCheck = DB::table('products AS Product')
            ->select(
                'Product.code AS code'
            )
            ->where([
                ['Product.id', '!=', $request->data['Product']['product_id']],
                ['Product.new_product_flg', '=', '1'],
                ['Product.active', '=', '1'],
                ['Product.code', '=', $product_code],
            ])->orderBy('id', 'desc')->first();

            if (!empty($productCodeCheck)){

                $exception_type = 1;

                throw new Exception();
            }

            //---------------
            // 保存処理を行う
            //---------------

            // 製品テーブルの保存処理
            $Product = \App\Product::find($request->data['Product']['product_id']);
            $Product->code              = $request->data['Product']['code'];                // 製品コード
            $Product->product_type      = $request->data['Product']['product_type'];        // 製品種別(魚orその他)
            $Product->status_id         = $request->data['Product']['status_id'];           // 製品状態
            $Product->tax_id            = $request->data['Product']['tax_id'];              // 税率
            $Product->name              = $request->data['Product']['product_name'];        // 製品名
            $Product->yomi              = $request->data['Product']['yomi'];                // ヨミガナ
            $Product->unit_id           = $request->data['Product']['unit_id'];             // 単位
            $Product->inventory_unit_id = $request->data['Product']['inventory_unit_id'];   // 仕入単位
            $Product->display_flg       = $request->data['Product']['display_flg'];         // サジェスト表示
            $Product->modified_user_id  = $user_info_id;                                    // 更新者ユーザーID
            $Product->modified          = Carbon::now();                                    // 更新時間

            // 保存処理
            $Product->save();

        } catch (\Exception $e) {

            DB::rollback();

            if($exception_type == 1){ // 登録済みのコードを指定の場合

                $errorMsg = "指定のコードは既に登録済みです。";
                $request->session()->put('error_message', $errorMsg);

                return redirect('./ProductEdit/'.$request->data['Product']['product_id']);
            }

            var_dump($e);
            die;

            return view('Product.complete')->with([
                'errorMessage' => $e
            ]);
        }

        // DBの変更を確定
        DB::commit();

        return view('Product.complete');
    }

    /**
     * 製品新規追加 登録
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function complete(Request $request)
    {

        // ユーザー情報の取得
        $user_info    = \Auth::user();
        $user_info_id = $user_info['id'];

        // トランザクション処理
        DB::beginTransaction();

        // エラータイプを初期化
        $exception_type = 0;

        try {

            // codeが入力されていない場合
            if(empty($request->data['Product']['code'])){

                // 空いている部分コードに入れたいので、1からにする。
                $product_code = 1;

                do {

                    $product_code = $product_code + 1;

                    // codeが存在するかチェック
                    $productCodeCheck = DB::table('products AS Product')
                    ->select(
                        'Product.code AS code'
                    )
                    ->where([
                        ['Product.active', '=', '1'],
                        ['Product.new_product_flg', '=', '1'],
                        ['Product.code', '=', $product_code],
                    ])->orderBy('id', 'desc')->first();

                } while (!empty($productCodeCheck));

            } else {

                // リクエストされたコードを格納
                $product_code = $request->data['Product']['code'];

                // codeが存在するかチェック
                $productCodeCheck = DB::table('products AS Product')
                ->select(
                    'Product.code AS code'
                )
                ->where([
                    ['Product.active', '=', '1'],
                    ['Product.new_product_flg', '=', '1'],
                    ['Product.code', '=', $product_code],
                ])->orderBy('id', 'desc')->first();

                if (!empty($productCodeCheck)){

                    $exception_type = 1;

                    throw new Exception();
                }
            }

            //---------------
            // 保存処理を行う
            //---------------
            // 製品テーブルの保存処理
            $Product = new Product;
            $Product->product_type      = $request->data['Product']['product_type'];        // 製品種別(魚orその他)
            $Product->status_id         = $request->data['Product']['status_id'];           // 製品状態
            $Product->tax_id            = $request->data['Product']['tax_id'];              // 税率
            $Product->code              = $product_code;                                    // 製品コード
            $Product->name              = $request->data['Product']['product_name'];        // 製品名
            $Product->yomi              = $request->data['Product']['yomi'];                // ヨミガナ
            $Product->unit_id           = $request->data['Product']['unit_id'];             // 単位
            $Product->inventory_unit_id = $request->data['Product']['inventory_unit_id'];   // 仕入単位
            $Product->display_flg       = $request->data['Product']['display_flg'];         // サジェスト表示
            $Product->sort              = 100;                                              // 表示順
            $Product->created_user_id   = $user_info_id;                                    // 作成者ユーザーID
            $Product->created           = Carbon::now();                                    // 作成時間
            $Product->modified_user_id  = $user_info_id;                                    // 更新者ユーザーID
            $Product->modified          = Carbon::now();                                    // 更新時間

            // 保存処理
            $Product->save();
            DB::connection()->commit();

        } catch (\Exception $e) {

            DB::rollback();

            if($exception_type == 1){ // 登録済みのコードを指定の場合

                $errorMsg = "指定のコードは既に登録済みです。";
                $request->session()->put('error_message', $errorMsg);

                return redirect('./ProductCreate');
            }

            return view('Product.complete')->with([
                'errorMessage' => $e
            ]);
        }

        // DBの変更を確定
        DB::commit();

        return view('Product.complete');
    }

    /**
     * イベント新規追加 登録
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function AjaxAddStandard(Request $request)
    {
        $standard_count = $request->standard_count;

        if(empty($standard_count)) $standard_count = 1;

        $ajaxHtml = '';

        $ajaxHtml .= "<tr id='standart_list_".$standard_count."' class='standard_list'>";
        $ajaxHtml .= "    <td>";
        $ajaxHtml .= "        <input type='text' class='form-control' id='standard_id' name='data[standard][standard_name][".$standard_count."]' value='規格".$standard_count."'>";
        $ajaxHtml .= "    </td>";
        $ajaxHtml .= "    <td>";
        $ajaxHtml .= "        <button id='standard_del_btn' type='button' class='btn standard_del_btn' onclick='javascript:removeStandardList(".$standard_count.")'>削除</button>";
        $ajaxHtml .= "    </td>";
        $ajaxHtml .= "</tr>'";

        $standard_count = intval($standard_count) + 1;

        $returnArray = array($standard_count, $ajaxHtml);


        return $returnArray;
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
                'Product.name AS product_name',
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

        return json_encode($auto_complete_array);
    }

    /**
     * 製品ID更新時のAjax処理
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function csvDownLoad(Request $request)
    {
        $fileName = "product.csv";

        // セッションにある検索条件を取得する

        $condition_date_from     = $request->session()->get('condition_date_from');
        $condition_date_to       = $request->session()->get('condition_date_to');

        $condition_product_type  = $request->session()->get('condition_product_type');
        $condition_status_id     = $request->session()->get('condition_status_id');

        $condition_search_text   = $request->session()->get('condition_search_text');
        $condition_product_code  = $request->session()->get('condition_product_code');
        $condition_product_id    = $request->session()->get('condition_product_id');
        $condition_product_text  = $request->session()->get('condition_product_text');

        try {


            // 製品一覧を取得
            $productList = DB::table('products AS Product')
            ->select(
                'Product.id             AS product_id',
                'Product.code           AS product_code',
                'Product.tax_id         AS tax_id',
                'Product.name           AS product_name',
            )
            ->leftJoin('product_types AS ProductType', function ($join) {
                $join->on('ProductType.id', '=', 'Product.product_type');
            })
            ->leftJoin('statuses AS Status', function ($join) {
                $join->on('Status.id', '=', 'Product.status_id');
            })
            ->join('taxes AS Tax', function ($join) {
                $join->on('Tax.id', '=', 'Product.tax_id');
            })
            ->join('units AS Unit', function ($join) {
                $join->on('Unit.id', '=', 'Product.unit_id');
            })
            ->if(!empty($condition_date_from) && !empty($condition_date_to), function ($query) use ($condition_date_from, $condition_date_to) {
                return $query->whereBetween('Product.created', [$condition_date_from, $condition_date_to]);
            })
            ->if(!empty($condition_product_type), function ($queryDetail) use ($condition_product_type) {
                return $queryDetail->where('Product.product_type', '=', $condition_product_type);
            })
            ->if(!empty($condition_status_id), function ($queryDetail) use ($condition_status_id) {
                return $queryDetail->where('Product.status_id', '=', $condition_status_id);
            })
            ->if(!empty($condition_search_text), function ($queryDetail) use ($condition_search_text) {
                return $queryDetail->where('Product.name', 'like', '%'.$condition_search_text.'%');
            })
            ->if(!empty($condition_product_id), function ($queryDetail) use ($condition_product_id) {
                return $queryDetail->where('Product.id', '=', $condition_product_id);
            })
            ->where([
                ['Product.new_product_flg', '=', '1'],
                ['Product.active', '=', '1'],
                ['ProductType.auto_regis_type_flg', '=', '0'],
            ])->orderByRaw('CAST(Product.code AS SIGNED) ASC') // 文字列を数値としてソート
            ->get();

            // csv配列作成
            $product_data = [];
            foreach ($productList as $product) {

                if($product->tax_id == 1){ // 8%の場合
                    $tax_id = 1;
                } else {
                    $tax_id = 3;
                }

                // 自動レジは30文字MAX
                $product_name = Str::limit($product->product_name, 26);

                 // 商品コードを5桁に変換（先頭に0を付ける）
                 $formatted_product_code = str_pad($product->product_code, 5, '0', STR_PAD_LEFT);

                $product_data[] = [
                    0 => $formatted_product_code,  // 商品コード
                    1 => $product_name,            //商品名称
                    2 => '' ,                      //レシート表示
                    3 => 0 ,                       //単価
                    4 => 0 ,                       //原価
                    5 => '' ,                      //バーコード
                    6 => 1 ,                       //大グループコード
                    7 => 1 ,                       //グループコード
                    8 => 1 ,                       //部門コード
                    9 => '' ,                      //クラスコード
                    10 => $tax_id ,                //課税
                    11 => 0 ,                      //値引・割引許可
                    12 => 0 ,                      //ポイント計算対象
                    13 => 0 ,                      //商品ポイント（任意）
                    14 => 0 ,                      //商品ポイント（利用）
                    15 => '' ,                     //メモ
                    16 => 0 ,                      //在庫管理対象
                    17 => 0 ,                      //原価計算区分
                    18 => 1 ,                      //商品販売区分
                    19 => 0 ,                      //会員単価対象区分
                    20 => 0 ,                      //会員単価
                    21 => 1 ,                      //計算間隔
                    22 => 0 ,                      //最低料金
                    23 => 0 ,                      //最大料金（１日）
                    24 => '' ,                     //延長リンク商品コード
                    25 => '0000-0000-0-0' ,        //時間帯１（開始・終了・料金・間隔）
                    26 => '0000-0000-0-0' ,        //時間帯２（開始・終了・料金・間隔）
                    27 => '0000-0000-0-0' ,        //時間帯３（開始・終了・料金・間隔）
                    28 => '0000-0000-0-0' ,        //時間帯４（開始・終了・料金・間隔）
                    29 => '0000-0000-0-0' ,        //時間帯５（開始・終了・料金・間隔）
                ];

            }

            // レスポンスをストリームで返す
            $response = new StreamedResponse(function () use($product_data) {

                $handle = fopen('php://output', 'w');

                // ヘッダー行を追加
                fputcsv($handle, array_map(function ($value) {
                    return mb_convert_encoding($value, 'SJIS-win', 'UTF-8');
                }, [
                    '商品コード',
                    '商品名称',
                    'レシート表示',
                    '単価',
                    '原価',
                    'バーコード',
                    '大グループコード',
                    'グループコード',
                    '部門コード',
                    'クラスコード',
                    '課税',
                    '値引・割引許可',
                    'ポイント計算対象',
                    '商品ポイント（任意）',
                    '商品ポイント（利用）',
                    'メモ',
                    '在庫管理対象',
                    '原価計算区分',
                    '商品販売区分',
                    '会員単価対象区分',
                    '会員単価',
                    '計算間隔',
                    '最低料金',
                    '最大料金（１日）',
                    '延長リンク商品コード',
                    '時間帯１（開始・終了・料金・間隔）',
                    '時間帯２（開始・終了・料金・間隔）',
                    '時間帯３（開始・終了・料金・間隔）',
                    '時間帯４（開始・終了・料金・間隔）',
                    '時間帯５（開始・終了・料金・間隔）'
                ]));

                // データをCSVに書き込む
                foreach ($product_data as $row) {
                    fputcsv($handle, array_map(function ($value) {
                        return mb_convert_encoding($value, 'SJIS-win', 'UTF-8');
                    }, $row));
                }

                fclose($handle);
            });

            // HTTPヘッダーを設定
            $response->headers->set('Content-Type', 'text/csv; charset=Shift_JIS');
            $response->headers->set('Content-Disposition', 'attachment; filename="' . $fileName . '"');

            return $response;
        } catch (\Exception $e) {

            dd($e);

            return null;
        }
    }
}
