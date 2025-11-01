<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('fleet_acquisitions', function (Blueprint $table) {
        $table->json('legal_documents')->nullable()->after('business_permit_number');
    });
}

public function down()
{
    Schema::table('fleet_acquisitions', function (Blueprint $table) {
        $table->dropColumn('legal_documents');
    });
}
};
