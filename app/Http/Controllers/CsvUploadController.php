<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Exception;
use App\SaleSlip;
use App\SaleSlipDetail;
use App\Product;
use App\Unit;
use App\SaleCompany;
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

            // CSVファイルの処理
            $original_file_name = $request->file('uploadCsvFile')->getClientOriginalName();
            $orgName = date('YmdHis') . "_" . $original_file_name;
            $spath = storage_path('app');
            $path = $spath . '/' . $request->file('uploadCsvFile')->storeAs('', $orgName);

            $file = new \SplFileObject($path);

            $file->setFlags(
                \SplFileObject::READ_CSV |      // CSV 列として行を読み込む
                \SplFileObject::READ_AHEAD |    // 先読み/巻き戻しで読み出す。
                \SplFileObject::SKIP_EMPTY |    // 空行は読み飛ばす
                \SplFileObject::DROP_NEW_LINE   // 行末の改行を読み飛ばす
            );

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
                if ($key == 0) continue;

                // 想定するカラム数じゃない場合はエラーを飛ばす
                if(count($line) != 18) throw new Exception("カラム数が想定と異なります。");

                // 文字コード変換
                $lines =mb_convert_encoding($line, 'UTF-8', 'ASCII, JIS, UTF-8, SJIS-win');

                // 前伝票番号を格納
                $prev_slip_no = $slip_no;

                // 伝票番号
                $slip_no = str_replace("'","",$lines[2]);

                // 対象の伝票番号の配列の有無を確認
                if(!isset($csv_slip_details[$slip_no])){

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
                        "slip_date"           => str_replace("'","",$lines[0]),   // 伝票日付
                        "delivery_date"       => str_replace("'","",$lines[1]),   // 納品日
                        "slip_no"             => $slip_no,                        // 伝票番号
                        "company_id"          => str_replace("'","",$lines[4]),   // 企業ID
                        "sale_company_name"   => str_replace("'","",$lines[5]),   // 企業名
                        "staus_code"          => str_replace("'","",$lines[16]),  // 取引状態コード 60:受領 91:赤伝受領
                        "slip_detail"         => [],                              // 伝票詳細配列
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
                $tax_id      = str_replace("'","",$lines[15]);
                $notax_price = str_replace("'","",$lines[13]);

                // 伝票詳細配列を作成する
                $csv_slip_details[$slip_no]["slip_detail"][] = [
                    "slip_detail_no"      => str_replace("'","",$lines[3]),
                    "product_code"        => str_replace("'","",$lines[6]),
                    "product_name"        => str_replace("'","",$lines[7]),
                    "inventory_unit_num"  => str_replace("'","",$lines[8]),
                    "inventory_unit_name" => str_replace("'","",$lines[9]),
                    "unit_price"          => str_replace("'","",$lines[10]),
                    "unit_num"            => str_replace("'","",$lines[11]),
                    "unit_name"           => str_replace("'","",$lines[12]),
                    "notax_price"         => str_replace("'","",$lines[13]),
                    "tax_rate"            => str_replace("'","",$lines[14]),
                    "tax_id"              => str_replace("'","",$lines[15])
                ];

                // 各金額を格納
                if($tax_id == 2){ // 8%対象商品の場合
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
                    $SaleCompany = new SaleCompany;
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

                $SaleSlip = new SaleSlip;
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
                        ['Unit.name', 'like', $unit_name
                        ],
                    ])->first();

                    if (empty($unit_check)) {
                        $Unit                    = new Unit;
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
                            $Unit                    = new Unit;
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

                        $Product = new Product;
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
                    $SaleSlipDetail                     = new SaleSlipDetail;
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

            // 履歴テーブルに格納
            $CsvUploadHistroy                   = new CsvUploadHistroy;
            $CsvUploadHistroy->data_type        = 0; // 売上登録 決めうちで:0
            $CsvUploadHistroy->file_name        = $original_file_name;
            $CsvUploadHistroy->created_user_id  = $user_info_id;
            $CsvUploadHistroy->save();

            // strageに保存したファイルを削除
            Storage::delete($orgName);

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
}
