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
        Schema::create('favorite_rss_items', function (Blueprint $table) {
            $table->id();
            // $table->foreignId('rss_source_id')->constrained('rss_sources');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); 
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('item_url')->unique()->nullable(); // Klarer Name für die URL des RSS-Items
            $table->dateTime('published_at')->nullable()->index(); // Index für schnelle Abfragen
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('favorite_rss_items');
    }
};
