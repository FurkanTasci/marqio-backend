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
        Schema::create('rss_source_catalog', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rss_source_id')->constrained()->onDelete('cascade');

            $table->string('country')->nullable();
            $table->string('category')->nullable();

            $table->unsignedInteger('rank')->default(0); 
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rss_source_catalog');
    }
};
