<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CsvUploadHistories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('csv_upload_histories', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("ID");
            $table->integer('data_type')->comment("データタイプ");
            $table->string('file_name', 255)->comment("登録ファイル名");
            $table->bigInteger('created_user_id')->comment("作成ユーザーID");
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('csv_upload_histories');
    }
}
