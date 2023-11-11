<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
use PDO;
use Strage;

class LineNoticeDeliveryDate extends Command
{
    // 発注LINE用トークン
    const LINE_GROUP_TOKEN = "q8iNq2e6HZHzp4C4A2QwhFdjs8gTok3EHsfaxRffouc";

    const LINE_API_URL = "https://notify-api.line.me/api/notify";
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:getLineNoticeDeliveryDate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '当日の納品データをチェックし、対象の注文を通知する';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try{

            // タイムゾーンを日本に設定
            date_default_timezone_set('Asia/Tokyo');

            // ログ用の情報
            $start = time();
            $this->comment("START Time=" . date("Y-m-d H:i:s"));
            \Log::channel()->info("GET_LINE_NOTICE_DELIVERY_DATE START");

            // 納品データを収集して、本日日付の納品データをすべて抽出する

            // 本日日付取得
            $today = date("Y-m-d");

            //---------------------
            // 売上一覧を取得
            //---------------------
            $saleSlipList = DB::table('sale_slips AS SaleSlip')
            ->select(
                'SaleSlip.id                  AS sale_slip_id',
                'SaleSlip.delivery_price      AS delivery_price',
                'SaleSlip.adjust_price        AS adjust_price',
                'SaleSlip.notax_sub_total     AS notax_sub_total',
                'SaleSlip.total               AS sale_slip_total',
                'SaleSlip.sale_submit_type    AS sale_submit_type',
                'SaleCompany.code             AS sale_company_code',
                'SaleCompany.name             AS sale_company_name',
                'SaleShop.name                AS sale_shop_name'
            )
            ->selectRaw('DATE_FORMAT(SaleSlip.date, "%Y/%m/%d")          AS sale_slip_date')
            ->selectRaw('DATE_FORMAT(SaleSlip.delivery_date, "%Y/%m/%d") AS sale_slip_delivery_date')
            ->selectRaw('DATE_FORMAT(SaleSlip.modified, "%m-%d %H:%i")   AS sale_slip_modified')
            ->join('sale_companies AS SaleCompany', function ($join) {
                $join->on('SaleCompany.id', '=', 'SaleSlip.sale_company_id');
            })
            ->leftJoin('sale_shops AS SaleShop', function ($join) {
                $join->on('SaleShop.id', '=', 'SaleSlip.sale_shop_id');
            })
            ->where('SaleSlip.delivery_date', '=', $today)
            ->where('SaleSlip.active', '=', '1')
            ->orderBy('SaleSlip.date', 'desc')
            ->orderBy('SaleSlip.id', 'desc')
            ->paginate(10);




            $message = "通知ﾃｽﾄです。";

            $data = http_build_query( [ 'message' => $message ], '', '&');

            $options = [
                    'http' => [
                        'method' => 'POST',
                        'header' => 'Authorization: Bearer ' . self::LINE_GROUP_TOKEN . "\r\n"
                            . "Content-Type: application/x-www-form-urlencoded\r\n"
                            . 'Content-Length: ' . strlen($data)  . "\r\n",
                        'content' => $data,
                    ]
                ];

            $context = stream_context_create($options);
            $resultJson = file_get_contents(self::LINE_API_URL, false, $context);
            $resultArray = json_decode($resultJson, true);


            // ログ用の情報
            $end = time();
            $this->comment("SUCCESS:END TIME Time=" . date("Y-m-d H:i:s") . "Run time=" . ($end - $start) . "sec");
            \Log::channel()->info("GET_LINE_NOTICE_DELIVERY_DATE END");

        } catch (Exception $e) {
            DB::rollback();
        }
    }
}
