<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Product;
use App\Standard;
use App\Status;
use App\Tax;
use App\Unit;;
use Carbon\Carbon;
use Exception;

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

            $product_search_type = $request->session()->get('product_search_type');
            $product_search_text = $request->session()->get('product_search_text');

            if($product_search_type == 1){ // 製品名検索の場合
                $product_name = $product_search_text;
            } else { // 製品コード検索の場合
                $product_code = $product_search_text;
            }

        } else { // POST時の処理

            if (isset($_POST['search-btn'])) { // 検索ボタン押された時の処理

                $product_search_type = $request->data['Product']['product_search_type'];
                $product_search_text = $request->data['Product']['product_search_text'];

                if ($product_search_type == 1) { // 製品名検索の場合
                    $product_name        = $product_search_text;
                } else { // 製品コード検索の場合
                    $product_code        = $product_search_text;
                }

                $request->session()->put('product_search_type', $product_search_type);
                $request->session()->put('product_search_text', $product_search_text);
                $request->session()->put('product_name', $product_name);
                $request->session()->put('product_code', $product_code);

            } else { // リセットボタンが押された時の処理

                $request->session()->forget('product_search_type');
                $request->session()->forget('product_search_text');
                $request->session()->forget('product_name');
                $request->session()->forget('product_code');
            }
        }

        try {

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
                'Status.name            AS status_name',
                'Tax.name               AS tax_name',
                'Product.name           AS product_name',
                'Unit.name              AS unit_name'
            )
            ->selectRaw(
                'CASE WHEN Product.product_type = 1 THEN "魚" ELSE "その他" END AS product_type_name'
            )
            ->join('statuses AS Status', function ($join) {
                $join->on('Status.id', '=', 'Product.status_id');
            })
            ->join('taxes AS Tax', function ($join) {
                $join->on('Tax.id', '=', 'Product.tax_id');
            })
            ->join('units AS Unit', function ($join) {
                $join->on('Unit.id', '=', 'Product.unit_id');
            })
            ->if(!empty($product_name), function ($query) use ($product_name) {
                return $query->where('Product.name', 'like', '%'.$product_name.'%');
            })
            ->if(!empty($product_code), function ($query) use ($product_code) {
                return $query->where('Product.code', '=', $product_code);
            })
            ->where('Product.active', '=', '1')
            ->orderBy('Product.created', 'asc')->paginate(20);

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
            "product_search_type"      => $product_search_type,
            "product_search_type_name" => $product_search_type_name,
            "product_search_type_code" => $product_search_type_code,
            "product_search_text"      => $product_search_text,
            "search_action"            => $search_action,
            "statusList"               => $statusList,
            "taxList"                  => $taxList,
            "unitList"                 => $unitList,
            "productList"              => $productList,
        ]);
    }

    /**
     * 製品情報編集
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function edit(Request $request, $product_id)
    {
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
            'Product.inventory_unit_id AS inventory_unit_id'
        )
        ->where([
            ['Product.id', '=', $product_id],
            ['Product.active', '=', '1'],
        ])
        ->first();

        // 規格一覧を取得
        $standardList = DB::table('standards AS Standard')
        ->select(
            'Standard.id     AS standard_id',
            'Standard.name   AS standard_name',
            'Standard.sort   AS status_sort'
        )
        ->where([
            ['Standard.product_id', '=', $product_id],
            ['Standard.active', '=', '1'],
        ])
        ->orderBy('Standard.sort', 'asc')->get();

        // エラーメッセージ取得
        $error_message       = $request->session()->get('error_message');
        $request->session()->forget('error_message');


        return view('Product.edit')->with([
            "statusList"    => $statusList,
            "taxList"       => $taxList,
            "unitList"      => $unitList,
            "editProduct"   => $editProduct,
            "standardList"  => $standardList,
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
            "statusList"                => $statusList,
            "taxList"                   => $taxList,
            "unitList"                  => $unitList,
            "error_message"             => $error_message,
        ]);
    }

     /**
     * 製品新規追加　確認
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
        $request->status_name         = $statusList->name;
        $request->tax_name            = $taxList->name;
        $request->unit_name           = $unitList->name;
        $request->inventory_unit_name = $inventoryUnitList->name;

        return view('Product.confirm')->with([
            "action_url"           => $action_url,
            "request"              => $request,
        ]);
    }

    /**
     * 製品新規追加　修正登録
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
            $Product->modified_user_id  = $user_info_id;                                    // 更新者ユーザーID
            $Product->modified          = Carbon::now();                                    // 更新時間

            // 保存処理
            $Product->save();

            // 作成したIDを取得する
            $product_new_id = $Product->id;

            // 規格テーブルの保存処理
            $standardArray = [];
            $sort = 0;

            foreach ($request->data['standard']['standard_name'] as $standardKey => $standards) {

                $Standard = \App\Standard::find($standardKey);
                $Standard->product_id       = $product_new_id;
                $Standard->name             = $standards;
                $Standard->modified_user_id = $user_info_id;
                $Standard->modified         = Carbon::now();

                // 保存処理
                $Standard->save();

            }

        } catch (\Exception $e) {

            DB::rollback();

            if($exception_type == 1){ // 登録済みのコードを指定の場合

                $errorMsg = "指定のコードは既に登録済みです。";
                $request->session()->put('error_message', $errorMsg);

                return redirect('./ProductEdit/'.$request->data['Product']['product_id']);
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
     * 製品新規追加　登録
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

                do {

                    // codeのMAX値を取得
                    $productCode = DB::table('products AS Product')
                    ->select(
                        'Product.code AS code'
                    )
                    ->where([
                        ['Product.active', '=', '1'],
                    ])->orderBy('id', 'desc')->first();

                    $product_code = $productCode->code + 1;

                    // codeが存在するかチェック
                    $productCodeCheck = DB::table('products AS Product')
                    ->select(
                        'Product.code AS code'
                    )
                    ->where([
                        ['Product.active', '=', '1'],
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
            $Product->sort              = 100;                                              // 表示順
            $Product->created_user_id   = $user_info_id;                                    // 作成者ユーザーID
            $Product->created           = Carbon::now();                                    // 作成時間
            $Product->modified_user_id  = $user_info_id;                                    // 更新者ユーザーID
            $Product->modified          = Carbon::now();                                    // 更新時間

            // 保存処理
            $Product->save();

            // 作成したIDを取得する
            $product_new_id = $Product->id;

            // 規格テーブルの保存処理
            $standardArray = [];
            $sort = 0;
            $standard_code = 1;

            foreach ($request->data['standard']['standard_name'] as $standards) {

                $standardArray[] = [
                    'product_id'       => $product_new_id,
                    'name'             => $standards,
                    'code'             => $standard_code,
                    'sort'             => $sort,
                    'created_user_id'  => $user_info_id,
                    'created'          => Carbon::now(),
                    'modified_user_id' => $user_info_id,
                    'modified'         => Carbon::now(),
                ];

                $standard_code++;
                $sort ++;
            }

            DB::table('standards')->insert($standardArray);

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
     * イベント新規追加　登録
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
}
