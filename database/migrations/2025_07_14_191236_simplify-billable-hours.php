<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('report_items', function (Blueprint $table) {
            $table->boolean('is_remote_interaction')->after('report_item_category_id')->default(false);
        });

        Schema::table('work_entries', function (Blueprint $table) {
            $table->boolean('is_remote_interaction')->after('report_item_category_id')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
