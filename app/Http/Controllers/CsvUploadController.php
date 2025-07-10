<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Exception;
use App\SaleSlip;
use App\SaleSlipDetail;
use App\SupplySlip;
use App\SupplySlipDetail;
use App\Product;
use App\Unit;
use App\SaleCompany;
use App\SupplyCompany;
use App\CsvUploadHistroy;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class CsvUploadController extends Controller
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
     * csvファイルアップロード画面
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function create(Request $request)
    {
        // tabindexを取得
        if (isset($request->tab_index)) {
            $tab_index = $request->tab_index;
        }

        // 設定されていない場合は1(CSVアップロードタブ)に設定する
        if(empty($tab_index)) {
            $tab_index = 1;
        }

        // 履歴テーブル配列
        $CsvUploadHistoryList = array();

        // データ種別配列
        $data_type_arr = [
            0 => "売上登録",
            1 => "マリネット仕入登録",
            2 => "自動レジ登録",
        ];

        // tab_index=2の場合、履歴テーブルを取得
        if ($tab_index == 2) {

            $CsvUploadHistoryList = DB::table('csv_upload_histories AS CsvUploadHistory')
            ->select(
                'CsvUploadHistory.data_type      AS data_type',
                'CsvUploadHistory.file_name      AS file_name',
                'User.name                       AS user_name',
            )
            ->selectRaw('DATE_FORMAT(CsvUploadHistory.updated_at, "%Y/%m/%d %H:%i") AS upload_date')
            ->leftJoin('users AS User', function ($join) {
                $join->on('User.id', '=', 'CsvUploadHistory.created_user_id');
            })
            ->orderBy('CsvUploadHistory.id', 'desc')
            ->limit(20)
            ->get();
        }

        // todo履歴を取得
        return view('CsvUpload.create')->with([
            "tab_index"            => $tab_index,
            "CsvUploadHistoryList" => $CsvUploadHistoryList,
            "data_type_arr"        => $data_type_arr

        ]);
    }

    /**
     * ajax Csvデータ登録
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function AjaxUploadCsv(Request $request)
    {

        try {

            // レスポンスパラメーターを作成する
            $response = [
                "success" => true,
                "message" => ""
            ];

            // ユーザー情報の取得
            $user_info    = \Auth::user();
            $user_info_id = $user_info['id'];

            DB::beginTransaction();

            // データタイプ取得
            $data_type = $request->data_type_val;

            // ----------------
            // CSVファイルの処理
            // ----------------
            // バリデーション
            $request->validate([
                'uploadCsvFiles.*' => 'required|file|mimes:csv,txt|max:5120',
            ]);

            $upload_csv_files = $request->file('uploadCsvFiles');

            // 取得してきたファイル分ループ処理
            foreach ($upload_csv_files as $upload_csv_file) {
                $original_file_name = $upload_csv_file->getClientOriginalName();
                $orgName = date('YmdHis') . "_" . $original_file_name;
                $spath = storage_path('app');
                $path = $spath . '/' . $upload_csv_file->storeAs('', $orgName);

                $file = new \SplFileObject($path);

                $file->setFlags(
                    \SplFileObject::READ_CSV |      // CSV 列として行を読み込む
                    \SplFileObject::READ_AHEAD |    // 先読み/巻き戻しで読み出す。
                    \SplFileObject::SKIP_EMPTY |    // 空行は読み飛ばす
                    \SplFileObject::DROP_NEW_LINE   // 行末の改行を読み飛ばす
                );

                if ($data_type == 1) {

                    // マリネットデータ登録
                    $this->registerMarinetData($file, $user_info_id);

                } elseif ($data_type == 2) {

                    // 自動レジデータ登録
                    $this->registerSmartOroshiData($file, $user_info_id);

                } else {


                    $lines = [];
                    $csv_slip_details = [];

                    $slip_no = 0;
                    $prev_slip_no = 0;

                    $notax_sub_total_8  = 0; // 8%課税対象額
                    $notax_sub_total_10 = 0; // 10%課税対象額
                    $notax_sub_total    = 0; // 課税対象額
                    $tax_total_8        = 0; // 8%税額
                    $tax_total_10       = 0; // 10%税額
                    $tax_total          = 0; // 税額合計
                    $sub_total_8        = 0; // 8%税込額
                    $sub_total_10       = 0; // 10%税込額
                    $sub_total          = 0; // 税込小計
                    $total              = 0; // 調整後税込額

                    foreach ($file as $key => $line) {
                        // ヘッター行を飛ばす
                        if ($key == 0) {
                            continue;
                        }

                        // 想定するカラム数じゃない場合はエラーを飛ばす
                        if (count($line) != 18) {
                            throw new Exception("カラム数が想定と異なります。");
                        }

                        // 文字コード変換
                        $lines = mb_convert_encoding($line, 'UTF-8', 'ASCII, JIS, UTF-8, SJIS-win');

                        // 前伝票番号を格納
                        $prev_slip_no = $slip_no;

                        // 伝票番号
                        $slip_no = str_replace("'", "", $lines[2]);

                        // 対象の伝票番号の配列の有無を確認
                        if (!isset($csv_slip_details[$slip_no])) {
                            if (!empty($prev_slip_no)) { // 前伝票番号が存在する場合はこちらの処理を行う
                                $notax_sub_total    = $notax_sub_total_8 + $notax_sub_total_10; // 課税対象額
                                $tax_total          = $tax_total_8 + $tax_total_10;             // 税額合計
                                $sub_total          = $sub_total_8 + $sub_total_10;             // 税込小計
                                $total              = $sub_total_8 + $sub_total_10;             // 調整後税込額 ※インフォマートでは調整額がないために同額

                                // 前伝票番号の配列に計算データを入れる
                                $csv_slip_details[$prev_slip_no]["notax_sub_total_8"]   = $notax_sub_total_8;   // 8%課税対象額
                                $csv_slip_details[$prev_slip_no]["notax_sub_total_10"]  = $notax_sub_total_10;  // 10%課税対象額
                                $csv_slip_details[$prev_slip_no]["notax_sub_total"]     = $notax_sub_total;     // 課税対象額
                                $csv_slip_details[$prev_slip_no]["tax_total_8"]         = $tax_total_8;         // 8%税額
                                $csv_slip_details[$prev_slip_no]["tax_total_10"]        = $tax_total_10;        // 10%税額
                                $csv_slip_details[$prev_slip_no]["tax_total"]           = $tax_total;           // 税額合計
                                $csv_slip_details[$prev_slip_no]["sub_total_8"]         = $sub_total_8;         // 8%税込額
                                $csv_slip_details[$prev_slip_no]["sub_total_10"]        = $sub_total_10;        // 10%税込額
                                $csv_slip_details[$prev_slip_no]["sub_total"]           = $sub_total;           // 税込小計
                                $csv_slip_details[$prev_slip_no]["total"]               = $total;               // 調整後税込額
                            }

                            // 伝票番号の配列を新規作成
                            $csv_slip_details[$slip_no] = [
                                "slip_date"           => str_replace("'", "", $lines[0]),   // 伝票日付
                                "delivery_date"       => str_replace("'", "", $lines[1]),   // 納品日
                                "slip_no"             => $slip_no,                          // 伝票番号
                                "company_id"          => str_replace("'", "", $lines[4]),   // 企業ID
                                "sale_company_name"   => str_replace("'", "", $lines[5]),   // 企業名
                                "staus_code"          => str_replace("'", "", $lines[16]),  // 取引状態コード 60:受領 91:赤伝受領
                                "slip_detail"         => [],                                // 伝票詳細配列
                            ];

                            // 各変数を初期化
                            $notax_sub_total_8  = 0; // 8%課税対象額
                            $notax_sub_total_10 = 0; // 10%課税対象額
                            $notax_sub_total    = 0; // 課税対象額
                            $tax_total_8        = 0; // 8%税額
                            $tax_total_10       = 0; // 10%税額
                            $tax_total          = 0; // 税額合計
                            $sub_total_8        = 0; // 8%税込額
                            $sub_total_10       = 0; // 10%税込額
                            $sub_total          = 0; // 税込小計
                            $total              = 0; // 調整後税込額
                        }

                        // 税率コード取得 1:10% 2:8%
                        $tax_id      = str_replace("'", "", $lines[15]);
                        $notax_price = str_replace("'", "", $lines[13]);

                        // 伝票詳細配列を作成する
                        $csv_slip_details[$slip_no]["slip_detail"][] = [
                            "slip_detail_no"      => str_replace("'", "", $lines[3]),
                            "product_code"        => str_replace("'", "", $lines[6]),
                            "product_name"        => str_replace("'", "", $lines[7]),
                            "inventory_unit_num"  => str_replace("'", "", $lines[8]),
                            "inventory_unit_name" => str_replace("'", "", $lines[9]),
                            "unit_price"          => str_replace("'", "", $lines[10]),
                            "unit_num"            => str_replace("'", "", $lines[11]),
                            "unit_name"           => str_replace("'", "", $lines[12]),
                            "notax_price"         => str_replace("'", "", $lines[13]),
                            "tax_rate"            => str_replace("'", "", $lines[14]),
                            "tax_id"              => str_replace("'", "", $lines[15])
                        ];

                        // 各金額を格納
                        if ($tax_id == 1) { // 8%対象商品の場合
                            $tax_price             = round($notax_price * 0.08);
                            $slip_detail_sub_total = $notax_price + $tax_price;

                            $notax_sub_total_8  += $notax_price;           // 8%課税対象額
                            $tax_total_8        += $tax_price;             // 8%税額
                            $sub_total_8        += $slip_detail_sub_total; // 8%税込額
                        } else { // 10%対象商品の場合
                            $tax_price             = round($notax_price * 0.1);
                            $slip_detail_sub_total = $notax_price + $tax_price;

                            $notax_sub_total_10 += $notax_price;           // 10%課税対象額
                            $tax_total_10       += $tax_price;             // 10%税額
                            $sub_total_10       += $slip_detail_sub_total; // 10%税込額
                        }
                    }

                    // 配列ループの最後の値を入れる
                    if (!empty($slip_no)) { // 前伝票番号が存在する場合はこちらの処理を行う
                        $notax_sub_total    = $notax_sub_total_8 + $notax_sub_total_10; // 課税対象額
                        $tax_total          = $tax_total_8 + $tax_total_10;             // 税額合計
                        $sub_total          = $sub_total_8 + $sub_total_10;             // 税込小計
                        $total              = $sub_total_8 + $sub_total_10;             // 調整後税込額 ※インフォマートでは調整額がないために同額

                        // 前伝票番号の配列に計算データを入れる
                        $csv_slip_details[$slip_no]["notax_sub_total_8"]   = $notax_sub_total_8;   // 8%課税対象額
                        $csv_slip_details[$slip_no]["notax_sub_total_10"]  = $notax_sub_total_10;  // 10%課税対象額
                        $csv_slip_details[$slip_no]["notax_sub_total"]     = $notax_sub_total;     // 課税対象額
                        $csv_slip_details[$slip_no]["tax_total_8"]         = $tax_total_8;         // 8%税額
                        $csv_slip_details[$slip_no]["tax_total_10"]        = $tax_total_10;        // 10%税額
                        $csv_slip_details[$slip_no]["tax_total"]           = $tax_total;           // 税額合計
                        $csv_slip_details[$slip_no]["sub_total_8"]         = $sub_total_8;         // 8%税込額
                        $csv_slip_details[$slip_no]["sub_total_10"]        = $sub_total_10;        // 10%税込額
                        $csv_slip_details[$slip_no]["sub_total"]           = $sub_total;           // 税込小計
                        $csv_slip_details[$slip_no]["total"]               = $total;               // 調整後税込額
                    }

                    ksort($csv_slip_details);

                    // 作成した伝票配列を登録する
                    foreach ($csv_slip_details as $key => $csv_slip_detail_val) {
                        // 配列から変数取得
                        $slip_date           = $csv_slip_detail_val["slip_date"];           // 伝票日付
                        $delivery_date       = $csv_slip_detail_val["delivery_date"];       // 納品日
                        $slip_no             = $csv_slip_detail_val["slip_no"];             // 伝票番号
                        $company_id          = $csv_slip_detail_val["company_id"];          // 企業ID
                        $sale_company_name   = $csv_slip_detail_val["sale_company_name"];   // 企業名
                        $staus_code          = $csv_slip_detail_val["staus_code"];          // 取引状態コード 80:受領 91:赤伝受領
                        $notax_sub_total_8   = $csv_slip_detail_val["notax_sub_total_8"];   // 8%課税対象額
                        $notax_sub_total_10  = $csv_slip_detail_val["notax_sub_total_10"];  // 10%課税対象額
                        $notax_sub_total     = $csv_slip_detail_val["notax_sub_total"];     // 課税対象額
                        $tax_total_8         = $csv_slip_detail_val["tax_total_8"];         // 8%税額
                        $tax_total_10        = $csv_slip_detail_val["tax_total_10"];        // 10%税額
                        $tax_total           = $csv_slip_detail_val["tax_total"];           // 税額合計
                        $sub_total_8         = $csv_slip_detail_val["sub_total_8"];         // 8%税込額
                        $sub_total_10        = $csv_slip_detail_val["sub_total_10"];        // 10%税込額
                        $sub_total           = $csv_slip_detail_val["sub_total"];           // 税込小計
                        $total               = $csv_slip_detail_val["total"];               // 調整後税込額

                        // 登録状況
                        $sale_submit_type = 2;
                        if ($staus_code == 80 || $staus_code == 91) { // 受領情報の場合
                            $sale_submit_type = 1;
                        }

                        // 取引先コードが存在するかチェックし、存在しない場合は暫定で新規登録する
                        $sale_company_check = DB::table('sale_companies AS SaleCompany')
                        ->where([
                            ['SaleCompany.active', '=', '1'],
                            ['SaleCompany.code', '=', $company_id],
                        ])->first();

                        if (empty($sale_company_check)) {
                            // 対象企業がない場合は新規作成
                            $SaleCompany = new SaleCompany();
                            $SaleCompany->code              = $company_id;
                            $SaleCompany->name              = $sale_company_name;
                            $SaleCompany->closing_date      = 99;
                            $SaleCompany->sort              = 100;
                            $SaleCompany->active            = 1;
                            $SaleCompany->created_user_id   = $user_info_id;               // 作成者ユーザーID
                            $SaleCompany->created           = Carbon::now();               // 作成時間
                            $SaleCompany->modified_user_id  = $user_info_id;               // 更新者ユーザーID
                            $SaleCompany->modified          = Carbon::now();               // 更新時間
                            $SaleCompany->save();
                            $sale_company_id = $SaleCompany->id;
                        } else {
                            $sale_company_id = $sale_company_check->id;
                        }

                        // 伝票を登録
                        $sale_slip_check = DB::table('sale_slips AS SaleSlip')
                        ->where([
                            ['SaleSlip.active', '=', '1'],
                            ['SaleSlip.info_mart_slip_no', '=', $slip_no],
                        ])->first();

                        // 対象の伝票が存在する場合
                        if (!empty($sale_slip_check)) {
                            // sale_slipsのIDを取得
                            $sale_slip_id = $sale_slip_check->id;

                            // sale_slipsと紐づくsale_slip_detailsを更新
                            SaleSlip::where('id', '=', $sale_slip_id)->update([
                                'active' => 0
                            ]);
                            SaleSlipDetail::where('sale_slip_id', '=', $sale_slip_id)->update([
                                'active' => 0
                            ]);
                        }

                        $SaleSlip = new SaleSlip();
                        $SaleSlip->info_mart_slip_no  = $slip_no;              // インフォマート伝票番号
                        $SaleSlip->date               = $slip_date;            // 伝票日付
                        $SaleSlip->delivery_date      = $delivery_date;        // 納品日
                        $SaleSlip->sale_company_id    = $sale_company_id;      // 売上先ID
                        $SaleSlip->notax_sub_total_8  = $notax_sub_total_8;    // 8%課税対象額
                        $SaleSlip->notax_sub_total_10 = $notax_sub_total_10;   // 10%課税対象額
                        $SaleSlip->notax_sub_total    = $notax_sub_total;      // 税抜合計額
                        $SaleSlip->tax_total_8        = $tax_total_8;          // 8%課税対象額
                        $SaleSlip->tax_total_10       = $tax_total_10;         // 10%課税対象額
                        $SaleSlip->tax_total          = $tax_total;            // 税抜合計額
                        $SaleSlip->sub_total_8        = $sub_total_8;          // 8%合計額
                        $SaleSlip->sub_total_10       = $sub_total_10;         // 10%合計額
                        $SaleSlip->sub_total          = $sub_total;            // 配送額
                        $SaleSlip->delivery_price     = 0;                     // 配送額
                        $SaleSlip->adjust_price       = 0;                     // 調整額
                        $SaleSlip->total              = $total;                // 合計額
                        $SaleSlip->sale_submit_type   = $sale_submit_type;     // 登録タイプ
                        $SaleSlip->sort               = 100;                   // ソート
                        $SaleSlip->created_user_id    = $user_info_id;         // 作成者ユーザーID
                        $SaleSlip->created            = Carbon::now();         // 作成時間
                        $SaleSlip->modified_user_id   = $user_info_id;         // 更新者ユーザーID
                        $SaleSlip->modified           = Carbon::now();         // 更新時間
                        $SaleSlip->save();
                        $sale_slip_id = $SaleSlip->id;


                        // 伝票詳細の登録処理
                        foreach ($csv_slip_detail_val["slip_detail"] as $slip_detail) {
                            $slip_detail_no       = $slip_detail["slip_detail_no"];       // 伝票詳細NO
                            $product_code         = $slip_detail["product_code"];           // 製品ID
                            $product_name         = $slip_detail["product_name"];         // 製品名
                            $inventory_unit_num   = $slip_detail["inventory_unit_num"];   // 入数
                            $inventory_unit_name  = $slip_detail["inventory_unit_name"];  // 入数単位
                            $unit_price           = $slip_detail["unit_price"];           // 単価
                            $unit_num             = $slip_detail["unit_num"];             // 数量
                            $unit_name            = $slip_detail["unit_name"];            // 単位名
                            $notax_price          = $slip_detail["notax_price"];          // 金額
                            $tax_id               = $slip_detail["tax_id"];               // 税率ID


                            // システムの税率ID(1:8% 2:10%) ※インフォマートと逆
                            $system_tax_id = 1;
                            if ($tax_id == 1) { // 10%消費税対象の場合
                                $system_tax_id = 2;
                            }

                            // 単位名が存在するチェックし、存在しない場合は新規登録する
                            $unit_check = DB::table('units AS Unit')
                            ->where([
                                ['Unit.active', '=', '1'],
                                [
                                    'Unit.name', 'like', $unit_name
                                ],
                            ])->first();

                            if (empty($unit_check)) {
                                $Unit                    = new Unit();
                                $Unit->name              = $unit_name;
                                $Unit->save();
                                $unit_id = $Unit->id;
                            } else {
                                $unit_id = $unit_check->id;
                            }

                            if (!empty($inventory_unit_name) && $inventory_unit_name != "未指定") { // 入り数単位が無い場合は単位をそのまま入れる
                                // 入数単位名が存在するチェックし、存在しない場合は新規登録する
                                $inventory_unit_check = DB::table('units AS Unit')
                                ->where([
                                    ['Unit.active', '=', '1'],
                                    ['Unit.name', 'like', $inventory_unit_name],
                                ])->first();

                                if (empty($inventory_unit_check)) {
                                    $Unit                    = new Unit();
                                    $Unit->name              = $inventory_unit_name;
                                    $Unit->save();
                                    $inventory_unit_id = $Unit->id;
                                } else {
                                    $inventory_unit_id = $inventory_unit_check->id;
                                }
                            } else {
                                $inventory_unit_id = $unit_id;
                            }

                            // 商品コードが存在するチェックし、存在しない場合は暫定で新規登録する
                            $product_check = DB::table('products AS Product')
                            ->where([
                                ['Product.active', '=', '1'],
                                ['Product.code', '=', $product_code],
                            ])->first();

                            if (empty($product_check)) { // 対象商品がない場合は新規作成


                                do {

                                    // codeのMAX値を取得
                                    $productCode = DB::table('products AS Product')
                                    ->select(
                                        'Product.code AS code'
                                    )
                                    ->where([
                                        ['Product.active', '=', '1'],
                                    ])
                                    ->whereRaw("code < 9000")
                                    ->orderByRaw("cast(code as SIGNED) desc")->first();

                                    $product_code = $productCode->code + 1;

                                    // codeが存在するかチェック
                                    $productCodeCheck = DB::table('products AS Product')
                                    ->select(
                                        'Product.code AS code'
                                    )
                                    ->where([
                                        ['Product.active', '=', '1'],
                                        ['Product.code', '=', $product_code],
                                    ])
                                    ->whereRaw("code < 9000")
                                    ->orderByRaw("cast(code as SIGNED) desc")->first();

                                } while (!empty($productCodeCheck));

                                $Product = new Product();
                                $Product->tax_id            = $system_tax_id;       // 税率
                                $Product->code              = $product_code;        // 製品コード
                                $Product->name              = $product_name;        // 製品名
                                $Product->unit_id           = $unit_id;             // 単位
                                $Product->inventory_unit_id = $inventory_unit_id;   // 仕入単位
                                $Product->sort              = 100;                  // 表示順
                                $Product->created_user_id   = $user_info_id;        // 作成者ユーザーID
                                $Product->created           = Carbon::now();        // 作成時間
                                $Product->modified_user_id  = $user_info_id;        // 更新者ユーザーID
                                $Product->modified          = Carbon::now();        // 更新時間
                                $Product->save();
                                $product_id = $Product->id;
                            } else {
                                $product_id = $product_check->id;
                            }

                            // sale_slip_detailsを登録する
                            $SaleSlipDetail                     = new SaleSlipDetail();
                            $SaleSlipDetail->sale_slip_id       = $sale_slip_id;
                            $SaleSlipDetail->product_id         = $product_id;
                            $SaleSlipDetail->unit_price         = $unit_price;
                            $SaleSlipDetail->unit_num           = $unit_num;
                            $SaleSlipDetail->notax_price        = $notax_price;
                            $SaleSlipDetail->unit_id            = $unit_id;
                            $SaleSlipDetail->inventory_unit_id  = $inventory_unit_id;
                            $SaleSlipDetail->inventory_unit_num = $inventory_unit_num;
                            $SaleSlipDetail->staff_id           = 9;
                            $SaleSlipDetail->sort               = $slip_detail_no;
                            $SaleSlipDetail->created_user_id    = $user_info_id;
                            $SaleSlipDetail->created            = Carbon::now();
                            $SaleSlipDetail->modified_user_id   = $user_info_id;
                            $SaleSlipDetail->modified           = Carbon::now();
                            $SaleSlipDetail->save();
                        }
                    }


                }

                // 履歴テーブルに格納
                $CsvUploadHistroy                   = new CsvUploadHistroy();
                $CsvUploadHistroy->data_type        = $data_type;
                $CsvUploadHistroy->file_name        = $original_file_name;
                $CsvUploadHistroy->created_user_id  = $user_info_id;
                $CsvUploadHistroy->save();

                // strageに保存したファイルを削除
                Storage::delete($orgName);

            }

            // 全ファイル処理したらcommit
            DB::commit();

            return $response;

        } catch (Exception $e) {

            DB::rollBack();

            $response = [
                "success" => false,
                "message" => $e->getMessage()
            ];

            return $response;
        }
    }

    /**
     * マリネットデータ登録
     *
     * @ file
     * @ user_info_id
     *
     * CSV利用項目
     * 7   日付
     * 8   伝票番号
     * 14  仕入先コード
     * 16  仕入先名
     * 19  売上先コード※水長水産のコード
     * 21  売上先名 ※水長
     * 54  商品コード
     * 62  産地
     * 86  個数
     * 89  数量
     * 90  単位名
     * 91  単価
     * 92  金額(単価×数量)
     * 96  商品名
     * 101 税率名
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function registerMarinetData($file, $user_info_id){

        $lines = [];
        $csv_slip_detail = []; // 伝票詳細配列を作成

        // 共通項目
        $supply_date        = null;// 日付
        $company_code       = 0;   // 仕入先コード
        $company_name       = 0;   // 仕入先名

        $notax_sub_total_8  = 0; // 8%課税対象額
        $notax_sub_total_10 = 0; // 10%課税対象額
        $notax_sub_total    = 0; // 課税対象額
        $tax_total_8        = 0; // 8%税額
        $tax_total_10       = 0; // 10%税額
        $tax_total          = 0; // 税額合計
        $sub_total_8        = 0; // 8%税込額
        $sub_total_10       = 0; // 10%税込額
        $sub_total          = 0; // 税込小計
        $total              = 0; // 調整後税込額

        foreach ($file as $key => $line) {

            // 想定するカラム数じゃない場合はエラーを飛ばす ※列数は105までだが最後にカンマがついているので106になる
            if (count($line) != 106) {
                throw new Exception("カラム数が想定と異なります。");
            }

            // 文字コード変換
            $lines = mb_convert_encoding($line, 'UTF-8', 'ASCII, JIS, UTF-8, SJIS-win');

            if ($key == 0) { // 1行名の場合は共通情報を入れる
                $supply_date        = date('Y/m/d', strtotime($lines[7]));  // 日付
                $company_code       = $lines[14]; // 仕入先コード
                $company_name       = $lines[16]; // 仕入先名
            }

            $seri_no            = $lines[32];// セリNO
            $origin_area        = $lines[62];// 産地
            $inventory_unit_num = $lines[86];// 個数
            if(empty($lines[89])){ // もし数量が空欄の場合は個数の方を入れる
                $unit_num           = $lines[86];// 数量
            } else {
                $unit_num           = $lines[89];// 数量
            }
            $unit_name          = $lines[90];// 単位名
            $unit_price         = $lines[91];// 単価
            $notax_price        = $lines[92];// 税抜金額
            $product_name       = $lines[96];// 商品名
            $tax_rate           = $lines[101];//税率名

            // 見本やサンプルの場合空白が入ってくるので、0を入れる
            if(empty($unit_num)){
                $unit_num = 0;
            }
            if(empty($unit_price)){
                $unit_price = 0;
            }
            if(empty($notax_price)){
                $notax_price = 0;
            }

            // 初期化
            $product_code = "";

            // 税率取得
            if($tax_rate == 10){ // 消費税が10％の場合
                $tax_id = 2;
            } else { // 消費税が8％の場合
                $tax_id = 1;
            }

            // 産地を取得
            $origin_area_check = DB::table('origin_areas AS OriginArea')
                ->where([
                    ['OriginArea.active', '=', '1'],
                    [
                        'OriginArea.name', 'like', "%{$origin_area}%"
                    ],
                ])->first();

            if (!empty($origin_area_check)) {
                $origin_area_id = $origin_area_check->id;
            } else {
                $origin_area_id = 0;
            }

            // 商品コードが存在するチェックし、存在しない場合は暫定で新規登録する
            $product_result = DB::table('products AS Product')
            ->where([
                ['Product.new_product_flg', '=', '1'],
                ['Product.active', '=', '1'],
                ['Product.name', '=', $product_name],
            ])->first();

            if (empty($product_result)) { // 対象商品がない場合は新規作成

                // 単位名をマリネット特有のものからシステムのものに変換する
                switch($unit_name){
                    case "ｷﾛ":
                        $unit_name = "kg";
                        break;
                    case "ｺ":
                        $unit_name = "個";
                        break;
                    case "ｹｰｽ":
                        $unit_name = "C/S";
                        break;
                    case "ﾊﾟｯｸ":
                        $unit_name = "PC";
                        break;
                }

                // unit_idを取得 ※
                // 単位名が存在するチェックし、存在しない場合は新規登録する
                $unit_check = DB::table('units AS Unit')
                    ->where([
                        ['Unit.active', '=', '1'],
                        [
                            'Unit.name', 'like', $unit_name
                        ],
                    ])->first();

                if (empty($unit_check)) {
                    $Unit                    = new Unit();
                    $Unit->name              = $unit_name;
                    $Unit->save();
                    $unit_id = $Unit->id;

                } else {
                    $unit_id = $unit_check->id;
                }

                // マリネットのデータは1万からにする
                $product_code = 10000;

                do {
                    $product_code += 1;

                    // codeが存在するかチェック
                    $productCodeCheck = DB::table('products AS Product')
                    ->select(
                        'Product.code AS code'
                    )
                    ->where([
                        ['Product.new_product_flg', '=', '1'],
                        ['Product.active', '=', '1'],
                        ['Product.code', '=', $product_code],
                    ])->first();

                } while (!empty($productCodeCheck));

                $Product = new Product();
                $Product->product_type      = 100;                  // マリネット商品種別
                $Product->tax_id            = $tax_id;              // 税率
                $Product->code              = $product_code;        // コード
                $Product->name              = $product_name;        // 製品名
                $Product->unit_id           = $unit_id;             // 単位
                $Product->inventory_unit_id = $unit_id;             // 仕入単位
                $Product->sort              = 100;                  // 表示順
                $Product->created_user_id   = $user_info_id;        // 作成者ユーザーID
                $Product->created           = Carbon::now();        // 作成時間
                $Product->modified_user_id  = $user_info_id;        // 更新者ユーザーID
                $Product->modified          = Carbon::now();        // 更新時間
                $Product->save();
                $product_id = $Product->id;

            } else {
                $product_id   = $product_result->id;
                $unit_id      = $product_result->unit_id;
            }

            // 伝票詳細配列を作成する
            $csv_slip_detail[] = [
                "product_id"          => $product_id,
                "inventory_unit_id"   => 6, // マリネットの単位はすべて「個」
                "inventory_unit_num" => $inventory_unit_num,
                "unit_price"          => $unit_price,
                "unit_num"            => $unit_num,
                "unit_id"             => $unit_id,
                "notax_price"         => $notax_price,
                "origin_area_id"      => $origin_area_id,
                "staff_id"            => 1,
                "seri_no"             => $seri_no
            ];

            // 各金額を格納
            if ($tax_id == 1) { // 8%対象商品の場合
                $tax_price             = round($notax_price * 0.08);
                $slip_detail_sub_total = $notax_price + $tax_price;

                $notax_sub_total_8  += $notax_price;           // 8%課税対象額
                $tax_total_8        += $tax_price;             // 8%税額
                $sub_total_8        += $slip_detail_sub_total; // 8%税込額
            } else { // 10%対象商品の場合
                $tax_price             = round($notax_price * 0.1);
                $slip_detail_sub_total = $notax_price + $tax_price;

                $notax_sub_total_10 += $notax_price;           // 10%課税対象額
                $tax_total_10       += $tax_price;             // 10%税額
                $sub_total_10       += $slip_detail_sub_total; // 10%税込額
            }
        }

        // 仕入先名が存在するかチェック
        // 取引先コードが存在するかチェックし、存在しない場合は暫定で新規登録する
        $supply_company_check = DB::table('supply_companies AS SupplyCompany')
        ->where([
            ['SupplyCompany.active', '=', '1'],
            ['SupplyCompany.code', '=', $company_code],
        ])->first();

        if (empty($supply_company_check)) {
            // 対象企業がない場合は新規作成
            $SupplyCompany = new SupplyCompany();
            $SupplyCompany->code              = $company_code;
            $SupplyCompany->name              = $company_name;
            $SupplyCompany->closing_date      = 99;
            $SupplyCompany->sort              = 100;
            $SupplyCompany->active            = 1;
            $SupplyCompany->created_user_id   = $user_info_id;               // 作成者ユーザーID
            $SupplyCompany->created           = Carbon::now();               // 作成時間
            $SupplyCompany->modified_user_id  = $user_info_id;               // 更新者ユーザーID
            $SupplyCompany->modified          = Carbon::now();               // 更新時間
            $SupplyCompany->save();
            $supply_company_id = $SupplyCompany->id;
        } else {
            $supply_company_id = $supply_company_check->id;
        }

        // 伝票が存在するかチェック ※日付と仕入先コードで検索
        // 伝票を登録
        $supply_slip_check = DB::table('supply_slips AS SupplySlip')
        ->where([
            ['SupplySlip.active', '=', '1'],
            ['SupplySlip.delivery_date',   '=', $supply_date],
            ['SupplySlip.supply_company_id',   '=', $supply_company_id],
        ])->first();

        // 対象の伝票が存在する場合
        if (!empty($supply_slip_check)) {

            // sale_slipsのIDを取得
            $supply_slip_id = $supply_slip_check->id;

            // sale_slipsと紐づくsale_slip_detailsをactive=0にして更新
            SupplySlip::where('id', '=', $supply_slip_id)->update([
                'active' => 0
            ]);
            SupplySlipDetail::where('supply_slip_id', '=', $supply_slip_id)->update([
                'active' => 0
            ]);
        }

        // 各種計算
        $notax_sub_total    = $notax_sub_total_8 + $notax_sub_total_10; // 課税対象額
        $tax_total          = $tax_total_8 + $tax_total_10;             // 税額合計
        $sub_total          = $sub_total_8 + $sub_total_10;             // 税込小計
        $total              = $sub_total_8 + $sub_total_10;             // 調整後税込額 ※インフォマートでは調整額がないために同額

        // supply_slipsを登録する
        $SupplySlip = new SupplySlip;
        $SupplySlip->date               = $supply_date;          // 日付
        $SupplySlip->delivery_date      = $supply_date;          // 納品日
        $SupplySlip->supply_company_id  = $supply_company_id;    // 仕入先ID
        $SupplySlip->notax_sub_total_8  = $notax_sub_total_8;    // 8%課税対象額
        $SupplySlip->notax_sub_total_10 = $notax_sub_total_10;   // 10%課税対象額
        $SupplySlip->notax_sub_total    = $notax_sub_total;      // 税抜合計額
        $SupplySlip->tax_total_8        = $tax_total_8;          // 8%課税対象額
        $SupplySlip->tax_total_10       = $tax_total_10;         // 10%課税対象額
        $SupplySlip->tax_total          = $tax_total;            // 税抜合計額
        $SupplySlip->sub_total_8        = $sub_total_8;          // 8%合計額
        $SupplySlip->sub_total_10       = $sub_total_10;         // 10%合計額
        $SupplySlip->sub_total          = $sub_total;            // 合計額
        $SupplySlip->delivery_price     = $total;                // 配送額 ※配送額はないので合計額と同一
        $SupplySlip->adjust_price       = $total;                // 調整額 ※調整額はないので合計額と同一
        $SupplySlip->total              = $total;                // 合計額
        $SupplySlip->supply_submit_type = 1;                     // 登録タイプ
        $SupplySlip->sort               = 100;                   // ソート
        $SupplySlip->created_user_id    = $user_info_id;         // 作成者ユーザーID
        $SupplySlip->created            = Carbon::now();         // 作成時間
        $SupplySlip->modified_user_id   = $user_info_id;         // 更新者ユーザーID
        $SupplySlip->modified           = Carbon::now();         // 更新時間
        $SupplySlip->save();

        // 作成したIDを取得する
        $supply_slip_new_id = $SupplySlip->id;

        $supply_slip_detail = array();
        $sort = 0;

        // 作成した伝票配列を登録する
        foreach ($csv_slip_detail as $key => $csv_slip_detail_val) {

            $supply_slip_detail[] = [
                'supply_slip_id'     => $supply_slip_new_id,
                'product_id'         => $csv_slip_detail_val['product_id'],
                'inventory_unit_id'  => $csv_slip_detail_val['inventory_unit_id'],
                'inventory_unit_num' => $csv_slip_detail_val['inventory_unit_num'],
                'unit_price'         => $csv_slip_detail_val['unit_price'],
                'unit_num'           => $csv_slip_detail_val['unit_num'],
                'unit_id'            => $csv_slip_detail_val['unit_id'],
                'notax_price'        => $csv_slip_detail_val['notax_price'],
                'origin_area_id'     => $csv_slip_detail_val['origin_area_id'],
                'staff_id'           => $csv_slip_detail_val['staff_id'],
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
        }

        return true;

    }

    /**
     * 自動レジデータ登録
     *
     * @ file
     * @ user_info_id
     *
     * CSV利用項目
     * 3   日付
     * 5   伝票番号
     * 8  売上先コード
     *   売上先名
     *   売上先コード※水長水産のコード
     *   売上先名 ※水長
     * 24  商品コード
     * 30  個数
     * 29  数量
     *   単位名
     * 28  単価
     * 35  金額(単価×数量)
     * 25  商品名
     * 27  税率名
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function registerSmartOroshiData($file, $user_info_id) {

        // システム作成ID
        $system_user_id = 99;

        $lines = [];
        $csv_slip_details = []; // 伝票詳細配列を作成

        $slip_no = 0;
        $prev_slip_no = 0;

        $notax_sub_total_8  = 0; // 8%課税対象額
        $notax_sub_total_10 = 0; // 10%課税対象額
        $notax_sub_total    = 0; // 課税対象額
        $tax_total_8        = 0; // 8%税額
        $tax_total_10       = 0; // 10%税額
        $tax_total          = 0; // 税額合計
        $sub_total_8        = 0; // 8%税込額
        $sub_total_10       = 0; // 10%税込額
        $sub_total          = 0; // 税込小計
        $total              = 0; // 調整後税込額

        // 支払方法
        $payment_method_type_0 = 0; // 掛け売り
        $payment_method_type_1 = 0; // 現金売り
        $payment_method_type_2 = 0; // クレジット
        $payment_method_type_3 = 0; // PAYPAY


        foreach ($file as $key => $line) {

            // ヘッダー行を飛ばす
            if ($key == 0) {
                continue;
            }

            // 想定するカラム数じゃない場合はエラーを飛ばす
            if (count($line) != 36) {
                throw new Exception("カラム数が想定と異なります。");
            }

            // 文字コード変換
            $lines = mb_convert_encoding($line, 'UTF-8', 'ASCII, JIS, UTF-8, SJIS-win');

            // =============================
            // 登録対象のデータか確認する start
            // =============================
            // ------------------------------------
            // 対象の企業がマスタに存在しているかチェック
            // ------------------------------------
            $sale_company_code = $lines[8];
            $sale_company_result = DB::table('sale_companies AS SaleCompany')
                ->where([
                    ['SaleCompany.active', '=', '1'],
                    ['SaleCompany.code', '=', $sale_company_code],
                ])->first();

            // 存在しない場合はデータ登録しない
            if (empty($sale_company_result)) {
                throw new Exception("対象の売上企業が存在しません。");
            }

            // ------------------------------------
            // 対象の商品がマスタに存在しているかチェック
            // ------------------------------------
            $product_code = $lines[24];
            $product_code = (int) ltrim($product_code, '0'); // 自動レジのマスタは5桁で先頭に0⃣がついているのでそれを削る

            $product_result = DB::table('products AS Product')
            ->where([
                ['Product.active', '=', '1'],
                ['Product.code', '=', $product_code],
            ])->first();

            // 存在しない場合はデータ登録しない
            if (empty($product_result)) {
                throw new Exception("対象の商品が存在しません。");
            }

            // ------------------------
            // 税率コード取得 1:8% 2:10%
            // ------------------------
            if ($lines[27] == 1) {
                $tax_id = 1;
            } elseif ($lines[27] == 3) {
                $tax_id = 2;
            } else {
                throw new Exception("税率が設定されていません。");
            }
            // =============================
            // 登録対象のデータか確認する end
            // =============================

            // 企業と商品関連情報を格納する
            $sale_company_id   = $sale_company_result->id;
            $product_id        = $product_result->id;
            $product_name      = $product_result->name;
            $unit_id           = $product_result->unit_id;
            $inventory_unit_id = $product_result->inventory_unit_id;

            // 前伝票番号を格納
            $prev_slip_no = $slip_no;

            // 伝票番号
            $slip_no = $lines[5];

            // 対象の伝票番号の配列の有無を確認
            if (!isset($csv_slip_details[$slip_no])) {
                if (!empty($prev_slip_no)) { // 前伝票番号が存在する場合はこちらの処理を行う
                    $notax_sub_total    = $notax_sub_total_8 + $notax_sub_total_10; // 課税対象額
                    $tax_total          = $tax_total_8 + $tax_total_10;             // 税額合計
                    $sub_total          = $sub_total_8 + $sub_total_10;             // 税込小計
                    $total              = $sub_total_8 + $sub_total_10;             // 調整後税込額 ※インフォマートでは調整額がないために同額

                    // 前伝票番号の配列に計算データを入れる
                    $csv_slip_details[$prev_slip_no]["notax_sub_total_8"]   = $notax_sub_total_8;   // 8%課税対象額
                    $csv_slip_details[$prev_slip_no]["notax_sub_total_10"]  = $notax_sub_total_10;  // 10%課税対象額
                    $csv_slip_details[$prev_slip_no]["notax_sub_total"]     = $notax_sub_total;     // 課税対象額
                    $csv_slip_details[$prev_slip_no]["tax_total_8"]         = $tax_total_8;         // 8%税額
                    $csv_slip_details[$prev_slip_no]["tax_total_10"]        = $tax_total_10;        // 10%税額
                    $csv_slip_details[$prev_slip_no]["tax_total"]           = $tax_total;           // 税額合計
                    $csv_slip_details[$prev_slip_no]["sub_total_8"]         = $sub_total_8;         // 8%税込額
                    $csv_slip_details[$prev_slip_no]["sub_total_10"]        = $sub_total_10;        // 10%税込額
                    $csv_slip_details[$prev_slip_no]["sub_total"]           = $sub_total;           // 税込小計
                    $csv_slip_details[$prev_slip_no]["total"]               = $total;               // 調整後税込額

                    $csv_slip_details[$prev_slip_no]["payment_method_type_0"]  = $payment_method_type_0;  // 掛け売り
                    $csv_slip_details[$prev_slip_no]["payment_method_type_1"]  = $payment_method_type_1;  // 現金売り
                    $csv_slip_details[$prev_slip_no]["payment_method_type_2"]  = $payment_method_type_2;  // クレジット
                    $csv_slip_details[$prev_slip_no]["payment_method_type_3"]  = $payment_method_type_3;  // PAYPAY
                }

                // 伝票番号の配列を新規作成
                $csv_slip_details[$slip_no] = [
                    "slip_date"           => $lines[3],             // 伝票日付
                    "delivery_date"       => $lines[3],             // 納品日
                    "slip_no"             => $slip_no,              // 伝票番号
                    "company_id"          => $sale_company_id,      // 企業ID
                    "staus_code"          => $lines[6],             // 取引種別
                    "slip_detail"         => [],                    // 伝票詳細配列
                ];

                // 各変数を初期化
                $notax_sub_total_8  = 0; // 8%課税対象額
                $notax_sub_total_10 = 0; // 10%課税対象額
                $notax_sub_total    = 0; // 課税対象額
                $tax_total_8        = 0; // 8%税額
                $tax_total_10       = 0; // 10%税額
                $tax_total          = 0; // 税額合計
                $sub_total_8        = 0; // 8%税込額
                $sub_total_10       = 0; // 10%税込額
                $sub_total          = 0; // 税込小計
                $total              = 0; // 調整後税込額

                // 支払方法
                $payment_method_type_0   = 0; // 掛け売り
                $payment_method_type_1   = 0; // 現金売り
                $payment_method_type_2   = 0; // クレジット
                $payment_method_type_3   = 0; // PAYPAY
            }

            $notax_price = $lines[35];

            // 伝票詳細配列を作成する
            $csv_slip_details[$slip_no]["slip_detail"][] = [
                "product_id"          => $product_id,
                "product_code"        => $product_code,
                "product_name"        => $product_name,
                "inventory_unit_id"   => $inventory_unit_id,
                "inventory_unit_num"  => $lines[29],
                "unit_id"             => $unit_id,
                "unit_price"          => $lines[31],
                "unit_num"            => $lines[30],
                "notax_price"         => $notax_price,
            ];

            // 各金額を格納
            if ($tax_id == 1) { // 8%対象商品の場合
                $tax_price             = round($notax_price * 0.08);
                $slip_detail_sub_total = $notax_price + $tax_price;

                $notax_sub_total_8  += $notax_price;           // 8%課税対象額
                $tax_total_8        += $tax_price;             // 8%税額
                $sub_total_8        += $slip_detail_sub_total; // 8%税込額
            } else { // 10%対象商品の場合
                $tax_price             = round($notax_price * 0.1);
                $slip_detail_sub_total = $notax_price + $tax_price;

                $notax_sub_total_10 += $notax_price;           // 10%課税対象額
                $tax_total_10       += $tax_price;             // 10%税額
                $sub_total_10       += $slip_detail_sub_total; // 10%税込額
            }

            // 支払方法
            $payment_method_type_0   = $lines[15]; // 掛け売り
            $payment_method_type_1   = $lines[11]; // 現金売り
            $payment_method_type_2   = $lines[17]; // クレジット
            $payment_method_type_3   = $lines[16]; // PAYPAY

        }

        // 配列ループの最後の値を入れる
        if (!empty($slip_no)) { // 前伝票番号が存在する場合はこちらの処理を行う
            $notax_sub_total    = $notax_sub_total_8 + $notax_sub_total_10; // 課税対象額
            $tax_total          = $tax_total_8 + $tax_total_10;             // 税額合計
            $sub_total          = $sub_total_8 + $sub_total_10;             // 税込小計
            $total              = $sub_total_8 + $sub_total_10;             // 調整後税込額 ※インフォマートでは調整額がないために同額

            // 前伝票番号の配列に計算データを入れる
            $csv_slip_details[$slip_no]["notax_sub_total_8"]   = $notax_sub_total_8;   // 8%課税対象額
            $csv_slip_details[$slip_no]["notax_sub_total_10"]  = $notax_sub_total_10;  // 10%課税対象額
            $csv_slip_details[$slip_no]["notax_sub_total"]     = $notax_sub_total;     // 課税対象額
            $csv_slip_details[$slip_no]["tax_total_8"]         = $tax_total_8;         // 8%税額
            $csv_slip_details[$slip_no]["tax_total_10"]        = $tax_total_10;        // 10%税額
            $csv_slip_details[$slip_no]["tax_total"]           = $tax_total;           // 税額合計
            $csv_slip_details[$slip_no]["sub_total_8"]         = $sub_total_8;         // 8%税込額
            $csv_slip_details[$slip_no]["sub_total_10"]        = $sub_total_10;        // 10%税込額
            $csv_slip_details[$slip_no]["sub_total"]           = $sub_total;           // 税込小計
            $csv_slip_details[$slip_no]["total"]               = $total;               // 調整後税込額

            // 支払方法
            $csv_slip_details[$slip_no]["payment_method_type_0"] = $payment_method_type_0;
            $csv_slip_details[$slip_no]["payment_method_type_1"] = $payment_method_type_1;
            $csv_slip_details[$slip_no]["payment_method_type_2"] = $payment_method_type_2;
            $csv_slip_details[$slip_no]["payment_method_type_3"] = $payment_method_type_3;
        }

        ksort($csv_slip_details);

        // 作成した伝票配列を登録する
        foreach ($csv_slip_details as $key => $csv_slip_detail_val) {
            // 配列から変数取得
            $slip_date           = $csv_slip_detail_val["slip_date"];           // 伝票日付
            $delivery_date       = $csv_slip_detail_val["delivery_date"];       // 納品日
            $slip_no             = $csv_slip_detail_val["slip_no"];             // 伝票番号
            $sale_company_id     = $csv_slip_detail_val["company_id"];          // 売上企業ID
            $notax_sub_total_8   = $csv_slip_detail_val["notax_sub_total_8"];   // 8%課税対象額
            $notax_sub_total_10  = $csv_slip_detail_val["notax_sub_total_10"];  // 10%課税対象額
            $notax_sub_total     = $csv_slip_detail_val["notax_sub_total"];     // 課税対象額
            $tax_total_8         = $csv_slip_detail_val["tax_total_8"];         // 8%税額
            $tax_total_10        = $csv_slip_detail_val["tax_total_10"];        // 10%税額
            $tax_total           = $csv_slip_detail_val["tax_total"];           // 税額合計
            $sub_total_8         = $csv_slip_detail_val["sub_total_8"];         // 8%税込額
            $sub_total_10        = $csv_slip_detail_val["sub_total_10"];        // 10%税込額
            $sub_total           = $csv_slip_detail_val["sub_total"];           // 税込小計
            $total               = $csv_slip_detail_val["total"];               // 調整後税込額

            // 支払方法
            $payment_method_type     = null;
            $payment_method_type_0   = $csv_slip_detail_val["payment_method_type_0"]; // 掛け売り
            $payment_method_type_1   = $csv_slip_detail_val["payment_method_type_1"]; // 現金売り
            $payment_method_type_2   = $csv_slip_detail_val["payment_method_type_2"]; // クレジット
            $payment_method_type_3   = $csv_slip_detail_val["payment_method_type_3"]; // PAYPAY

            if (!empty($payment_method_type_0)) {
                $payment_method_type  = 0;
            } else if (!empty($payment_method_type_1)) {
                $payment_method_type  = 1;
            } else if (!empty($payment_method_type_2)) {
                $payment_method_type  = 2;
            } else if (!empty($payment_method_type_3)) {
                $payment_method_type  = 3;
            }

            // 伝票を登録
            $sale_slip_check = DB::table('sale_slips AS SaleSlip')
            ->where([
                ['SaleSlip.active', '=', '1'],
                ['SaleSlip.info_mart_slip_no', '=', $slip_no],
            ])->first();

            // 対象の伝票が存在する場合
            if (!empty($sale_slip_check)) {
                // sale_slipsのIDを取得
                $sale_slip_id = $sale_slip_check->id;

                // sale_slipsと紐づくsale_slip_detailsを更新
                SaleSlip::where('id', '=', $sale_slip_id)->update([
                    'active' => 0
                ]);
                SaleSlipDetail::where('sale_slip_id', '=', $sale_slip_id)->update([
                    'active' => 0
                ]);
            }

            $SaleSlip = new SaleSlip();
            $SaleSlip->info_mart_slip_no  = 0;                     // インフォマートではないので0固定
            $SaleSlip->date               = $slip_date;            // 伝票日付
            $SaleSlip->delivery_date      = $delivery_date;        // 納品日
            $SaleSlip->sale_company_id    = $sale_company_id;      // 売上先ID
            $SaleSlip->payment_method_type = $payment_method_type; // 支払方法
            $SaleSlip->notax_sub_total_8  = $notax_sub_total_8;    // 8%課税対象額
            $SaleSlip->notax_sub_total_10 = $notax_sub_total_10;   // 10%課税対象額
            $SaleSlip->notax_sub_total    = $notax_sub_total;      // 税抜合計額
            $SaleSlip->tax_total_8        = $tax_total_8;          // 8%課税対象額
            $SaleSlip->tax_total_10       = $tax_total_10;         // 10%課税対象額
            $SaleSlip->tax_total          = $tax_total;            // 税抜合計額
            $SaleSlip->sub_total_8        = $sub_total_8;          // 8%合計額
            $SaleSlip->sub_total_10       = $sub_total_10;         // 10%合計額
            $SaleSlip->sub_total          = $sub_total;            // 配送額
            $SaleSlip->delivery_price     = 0;                     // 配送額
            $SaleSlip->adjust_price       = 0;                     // 調整額
            $SaleSlip->total              = $total;                // 合計額
            $SaleSlip->sale_submit_type   = 1;                     // 登録タイプ
            $SaleSlip->sort               = 100;                   // ソート
            $SaleSlip->created_user_id    = $system_user_id;       // 作成者ユーザーID
            $SaleSlip->created            = Carbon::now();         // 作成時間
            $SaleSlip->modified_user_id   = $user_info_id;         // 更新者ユーザーID
            $SaleSlip->modified           = Carbon::now();         // 更新時間
            $SaleSlip->save();
            $sale_slip_id = $SaleSlip->id;


            // 伝票詳細の登録処理
            $slip_detail_no = 1;
            foreach ($csv_slip_detail_val["slip_detail"] as $slip_detail) {
                $product_id           = $slip_detail["product_id"];           // 製品ID
                $product_code         = $slip_detail["product_code"];         // 製品code
                $product_name         = $slip_detail["product_name"];         // 製品名
                $inventory_unit_id    = $slip_detail["inventory_unit_id"];    // 入数ID
                $inventory_unit_num   = $slip_detail["inventory_unit_num"];   // 入数
                $unit_id              = $slip_detail["unit_id"];              // 単位ID
                $unit_price           = $slip_detail["unit_price"];           // 単価
                $unit_num             = $slip_detail["unit_num"];             // 数量
                $notax_price          = $slip_detail["notax_price"];          // 金額

                // sale_slip_detailsを登録する
                $SaleSlipDetail                     = new SaleSlipDetail();
                $SaleSlipDetail->sale_slip_id       = $sale_slip_id;
                $SaleSlipDetail->product_id         = $product_id;
                $SaleSlipDetail->unit_price         = $unit_price;
                $SaleSlipDetail->unit_num           = $unit_num;
                $SaleSlipDetail->notax_price        = $notax_price;
                $SaleSlipDetail->unit_id            = $unit_id;
                $SaleSlipDetail->inventory_unit_id  = $inventory_unit_id;
                $SaleSlipDetail->inventory_unit_num = $inventory_unit_num;
                $SaleSlipDetail->staff_id           = 9;
                $SaleSlipDetail->sort               = $slip_detail_no;
                $SaleSlipDetail->created_user_id    = $user_info_id;
                $SaleSlipDetail->created            = Carbon::now();
                $SaleSlipDetail->modified_user_id   = $user_info_id;
                $SaleSlipDetail->modified           = Carbon::now();
                $SaleSlipDetail->save();

                $slip_detail_no++;
            }
        }

    }
}
