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

        // postできたか、getできたか
        if ($_SERVER["REQUEST_METHOD"] != "POST"
        ) { // ページング処理

            $product_type = $request->session()->get('product_type');
            $status_id    = $request->session()->get('status_id');
            $tax_id       = $request->session()->get('tax_id');
            $product_name = $request->session()->get('product_name');
            $unit_id      = $request->session()->get('unit_id');

        } else { // POST時の処理

            if (isset($_POST['search-btn'])) { // 検索ボタン押された時の処理
                $product_type = $request->data['Product']['product_type'];
                $status_id    = $request->data['Product']['status_id'];
                $tax_id       = $request->data['Product']['tax_id'];
                $product_name = $request->data['Product']['product_name'];
                $unit_id      = $request->data['Product']['unit_id'];

                $request->session()->put('product_type', $product_type);
                $request->session()->put('status_id', $status_id);
                $request->session()->put('tax_id', $tax_id);
                $request->session()->put('product_name', $product_name);
                $request->session()->put('unit_id', $unit_id);
            } else { // リセットボタンが押された時の処理

                $product_type = 0;
                $status_id    = 0;
                $tax_id       = 0;
                $product_name = null;
                $unit_id      = 0;

                $request->session()->forget('product_type');
                $request->session()->forget('status_id');
                $request->session()->forget('tax_id');
                $request->session()->forget('product_name');
                $request->session()->forget('unit_id');
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
            ->if(!empty($product_type), function ($query) use ($product_type) {
                return $query->where('Product.product_type', '=', $product_type);
            })
            ->if(!empty($status_id), function ($query) use ($status_id) {
                return $query->where('Product.status_id', '=', $status_id);
            })
            ->if(!empty($tax_id), function ($query) use ($tax_id) {
                return $query->where('Product.tax_id', '=', $tax_id);
            })
            ->if(!empty($product_name), function ($query) use ($product_name) {
                return $query->where('Product.name', 'like', '%'.$product_name.'%');
            })
            ->if(!empty($unit_id), function ($query) use ($unit_id) {
                return $query->where('Product.unit_id', '=', $unit_id);
            })
            ->where('Product.active', '=', '1')
            ->orderBy('Product.created', 'asc')->paginate(20);


        } catch (\Exception $e) {

            dd($e);

            return view('Product.complete')->with([
                'errorMessage' => $e
            ]);
        }

        return view('Product.index')->with([
            "action"             => $search_action,
            "product_type"       => $product_type,
            "status_id"          => $status_id,
            "tax_id"             => $tax_id,
            "product_name"       => $product_name,
            "unit_id"            => $unit_id,
            "search_action"      => $search_action,
            "statusList"         => $statusList,
            "taxList"            => $taxList,
            "unitList"           => $unitList,
            "productList"        => $productList,
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

}
