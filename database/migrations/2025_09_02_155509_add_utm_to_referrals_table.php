<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('referrals', function (Blueprint $table) {
            $table->string('source')->nullable()->after('referrer_host');
            $table->string('utm_source')->nullable()->after('source');
            $table->string('utm_medium')->nullable()->after('utm_source');
            $table->string('utm_campaign')->nullable()->after('utm_medium');
            $table->string('referral_code')->nullable()->after('utm_campaign');
            $table->text('full_url')->nullable()->after('referral_code');

            $table->index('source');
            $table->index('utm_source');
            $table->index('created_at');
            $table->index('referrer_host');

        });
    }

    public function down()
    {
        Schema::table('referrals', function (Blueprint $table) {
            $table->dropColumn(['source', 'utm_source', 'utm_medium', 'utm_campaign', 'referral_code', 'full_url']);
        });
    }
};
