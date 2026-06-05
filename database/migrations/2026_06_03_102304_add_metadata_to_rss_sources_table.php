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
        Schema::table('rss_sources', function (Blueprint $table) {
            $table->string('title')->nullable();
            $table->string('site_url')->nullable();

            $table->string('country_code', 2)->nullable();
            $table->string('language', 5)->nullable();

            $table->string('category')->nullable();

            $table->boolean('is_featured')->default(false);
            $table->boolean('is_public')->default(true);

            $table->unsignedInteger('subscriber_count')->default(0);

            $table->index('country_code');
            $table->index('is_featured');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rss_sources', function (Blueprint $table) {
            //
        });
    }
};
