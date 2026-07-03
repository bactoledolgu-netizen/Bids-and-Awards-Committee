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
        Schema::table('users', function (Blueprint $table) {
            $table->string('employee_id')->nullable()->unique()->after('email');
            $table->string('position_title')->nullable()->after('employee_id');
            $table->string('office')->nullable()->after('position_title');
            $table->string('username')->unique()->after('office');
            $table->enum('account_status', ['active', 'inactive', 'locked'])->default('active')->after('username');
            $table->unsignedTinyInteger('failed_login_attempts')->default(0)->after('account_status');
            $table->timestamp('locked_until')->nullable()->after('failed_login_attempts');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['employee_id']);
            $table->dropColumn(['employee_id', 'position_title', 'office']);
            $table->dropUnique(['username']);
            $table->dropColumn(['username', 'account_status', 'failed_login_attempts', 'locked_until']);
        });
    }
};
