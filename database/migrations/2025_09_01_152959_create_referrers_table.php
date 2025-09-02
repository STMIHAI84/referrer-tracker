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
        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            $table->string('referrer_url', 2048)->nullable();   // URL complet din Referer
            $table->string('referrer_host', 255)->nullable();   // doar domeniul (ex: facebook.com)
            $table->string('landing_path', 255);                // ex: /landing
            $table->string('ip', 45)->nullable();               // IPv4/IPv6
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referrers');
    }
};
