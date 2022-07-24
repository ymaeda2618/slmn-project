@extends('layouts.app') @section('content')
<div class="container">

    <div class="row justify-content-center">

        <div class="top-title">CSVアップロード画面</div>

        <div class='tab-area'>
            <table class="tab-area-table">
                <tr>
                    <td>
                        <ul>
                            <a href="#" id="csv-upload-tab">
                                <li id="csv-upload-li" class="active">CSVアップロード</li>
                            </a>
                            <a href="#" id="upload-history-tab">
                                <li id="upload-history-li">アップロード履歴</li>
                            </a>
                        </ul>
                    </td>
                </tr>
            </table>
        </div>

        @if($tab_index==1)
        <div class='upload-area'>
            {{ csrf_field() }}
            <table class="upload-area-table">
                <tr>
                    <th>データ種別</th>
                    <td>
                        <select class="file-control" name="data_type_val" id="data_type_val">
                            @foreach($data_type_arr as $data_type_key => $data_type_val)
                            <option value="{{$data_type_key}}">{{$data_type_val}}</option>
                            @endforeach
                        </select>
                    </td>
                </tr>
                <tr>
                    <th class="table-th">データファイル</th>
                    <td>
                        <input type="file" class="upload-file-input" name="uploadCsvFile" id="uploadCsvFile" accept=".csv">
                    </td>
                </tr>
            </table>
            <div class="btn-area ">
                <input type='button' class='btn-primary' id="upload-btn" value='アップロード'>
            </div>
        </div>
        @else
        <div class='hisotry-area'>
            <div class="hisotry-attention">※最大直近20件を表示</div>
            <table class="hisotry-area-column-table">
                <tr>
                    <th>日時</th>
                    <th>データ種別</th>
                    <th>ファイル名</th>
                    <th>登録者</th>
                </tr>
            </table>
            @foreach($CsvUploadHistoryList as $CsvUploadHistoryVal)
            <table class="hisotry-area-val-table">
                <tr>
                    <td>{{$CsvUploadHistoryVal->upload_date}}</td>
                    <td>{{$data_type_arr[$CsvUploadHistoryVal->data_type]}}</td>
                    <td>{{$CsvUploadHistoryVal->file_name}}</td>
                    <td>{{$CsvUploadHistoryVal->user_name}}</td>
                </tr>
            </table>
            @endforeach

        </div>
        @endif

        <input type="hidden" id="tab_index" value="{{ $tab_index }}">
    </div>
</div>
<div id="overlay">
    <div class="cv-spinner">
        <span class="spinner"></span>
    </div>
</div>
@endsection
<script src="https://code.jquery.com/jquery-2.2.4.min.js"></script>
<link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
<style>
    .top-title {
        font-size: 1.4em;
        font-weight: bold;
        width: 100%;
        text-align: center;
        padding: 25px 0px;
    }

    .tab-area {
        width: 100%;
        margin: 30px auto;
    }

    .tab-area .tab-area-table {
        width: 100%;
        max-width: 600px;
        margin: auto;
        border-bottom: 1px solid #57595b;
    }

    .tab-area ul {
        list-style-type: none;
    }

    .tab-area li {
        float: left;
        width: 30%;
        font-size: 12px;
        list-style-type: none;
        font-weight: bold;
        text-align: center;
        border-left: 1px solid #57595b;
        border-right: 1px solid #57595b;
        border-top: 1px solid #57595b;
        padding: 2px;
        margin-right: 5px;
        border-radius: 2px;
        color: #57595b;
    }

    .tab-area ul .active {
        background-color: #57595b;
        color: #ffff;
    }
    /*CSVアップロード*/

    .upload-area {
        width: 100%;
    }

    .upload-area .upload-area-table {
        width: 100%;
        font-size: 12px;
        max-width: 600px;
        margin: auto;
    }

    .upload-area-table th {
        width: 30%;
        background-color: #57595b;
        color: white;
        padding: 7px;
        border: 1px solid #ffff;
    }

    .upload-area-table td {
        width: 70%;
        padding: 7px;
        border: 1px solid #bcbcbc;
    }

    .upload-area-table select {
        width: 60%;
    }

    .upload-area .btn-area {
        width: 100%;
        font-size: 12px;
        max-width: 600px;
        text-align: center;
        margin: 30px auto;
    }

    #upload-btn {
        width: 60%;
        max-width: 200px;
        height: 30px;
        border-radius: 10px;
        margin-right: 2%;
    }
    /*アップロード履歴テーブル*/

    .hisotry-area {
        width: 100%;
        font-size: 12px;
        max-width: 600px;
        margin: auto;
    }

    .hisotry-attention {
        width: 100%;
        font-size: 8px;
    }

    .hisotry-area-column-table,
    .hisotry-area-val-table {
        width: 100%;
        letter-spacing: 2px;
    }

    .hisotry-area-column-table th {
        width: 10%;
        padding: 10px;
        padding-left: 10px;
        background-color: #57595b;
        font-weight: bold;
        color: white;
        font-size: 10px;
        letter-spacing: 1px;
        border: 1px solid #bcbcbc;
    }

    .hisotry-area-column-table th:nth-child(1),
    .hisotry-area-val-table td:nth-child(1) {
        width: 25%;
    }

    .hisotry-area-column-table th:nth-child(2),
    .hisotry-area-column-table th:nth-child(3),
    .hisotry-area-val-table td:nth-child(2),
    .hisotry-area-val-table td:nth-child(3) {
        width: 30%;
    }

    .hisotry-area-column-table th:nth-child(4),
    .hisotry-area-val-table td:nth-child(4) {
        width: 15%;
    }

    .hisotry-area-val-table td {
        font-size: 8px;
        padding-left: 20px;
        padding: 8px;
        border: 1px solid #bcbcbc;
        width: 10%;
    }

    #overlay {
        position: fixed;
        top: 0;
        z-index: 100;
        width: 100%;
        height: 100%;
        display: none;
        background: rgba(0, 0, 0, 0.6);
    }

    .cv-spinner {
        height: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .spinner {
        width: 40px;
        height: 40px;
        border: 4px #ddd solid;
        border-top: 4px #2e93e6 solid;
        border-radius: 50%;
        animation: sp-anime 0.8s infinite linear;
    }

    @keyframes sp-anime {
        100% {
            transform: rotate(360deg);
        }
    }

    .is-hide {
        display: none;
    }
</style>
<script type="text/javascript">
    var notax_sub_total_8;
    var tax_total_8;
    var sub_total_8;

    var notax_sub_total_10;
    var tax_total_10;
    var sub_total_10;

    var notax_sub_total;
    var tax_total;
    var sub_total;

    var adjust_price;
    var total;

    (function($) {
        jQuery(window).load(function() {

            //------------------------------------------
            // tab_indexが2の場合は、アップロード履歴のタブをアクティブにする
            //------------------------------------------
            var tab_index = $("#tab_index").val();

            if (tab_index == 2) {
                $("#csv-upload-li").removeClass('active');
                $("#upload-history-li").addClass('active');
            }

            //-----------------------
            // CSVアップロードのタブが押された時
            //-----------------------
            $(document).on("click", "#csv-upload-tab", function() {
                $(this).attr('href', './csvUpload?tab_index=1');
            });

            //-----------------------
            // アップロード履歴のタブが押された時の処理
            //-----------------------
            $(document).on("click", "#upload-history-tab", function() {
                $(this).attr('href', './csvUpload?tab_index=2');
            });

            //-----------------------
            // アップロードボタンが押された時の処理
            //-----------------------
            $(document).on("click", "#upload-btn", function() {

                // ボタンを非活性にして、処理中を出す。
                $("#overlay").fadeIn(300);

                var fd = new FormData();
                fd.append("data_type_val", $("#data_type_val").val());
                fd.append("slip_type_val", $("#slip_type_val").val());
                fd.append("uploadCsvFile", $("#uploadCsvFile").prop('files')[0]);

                // ファイル拡張子を確認し、csv以外は弾くようにする
                var acceptArray = new Array('text/csv');
                var fileAccept = $("#uploadCsvFile").prop('files')[0].type;

                // ファイルがない場合はアラートを飛ばす
                if (!$("#uploadCsvFile").val()) {
                    alert("csvファイルが選択されておりません。");
                    $("#overlay").fadeOut(0);
                    return;
                } else if ($.inArray(fileAccept, acceptArray) === -1) {
                    alert("選択されたファイルがCSVではありません。");
                    $("#overlay").fadeOut(0);
                    return;
                }

                $.ajax({
                        headers: {
                            "X-CSRF-TOKEN": $("[name='_token']").val()
                        },
                        url: "/AjaxUploadCsv",
                        type: "POST",
                        dataType: "JSON",
                        data: fd,
                        processData: false,
                        contentType: false
                    })
                    .done(function(data) {

                        if (data["success"]) {
                            // アップロード履歴に遷移する
                            location.href = '/csvUpload?tab_index=2';
                        } else {
                            // アラートメッセージ
                            alert(data["message"]);
                            // アップロード画面に戻る
                            location.href = './csvUpload?tab_index=1';
                        }
                    });
            });
        });


    })(jQuery);
</script>
