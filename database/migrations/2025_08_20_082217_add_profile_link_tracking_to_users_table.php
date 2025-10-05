<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('has_edited_profile_link')->default(false)->after('merchant_code');
            $table->timestamp('profile_link_edited_at')->nullable()->after('has_edited_profile_link');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['has_edited_profile_link', 'profile_link_edited_at']);
        });
    }
};