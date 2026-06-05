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
        Schema::table('bookmark_tag', function (Blueprint $table) {
            $table->unique(['bookmark_id', 'tag_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookmark_tag', function (Blueprint $table) {
            $table->dropUnique(['bookmark_tag_bookmark_id_tag_id_unique']);
        });
    }
};
