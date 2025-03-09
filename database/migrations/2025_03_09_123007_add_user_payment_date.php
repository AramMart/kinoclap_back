<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserPaymentDate extends Migration
{
 public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dateTime('payment_date')->nullable()->after('email_verification_token');
            $table->decimal('payment_amount', 8, 2)->nullable()->after('payment_date');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('payment_date');
            $table->dropColumn('payment_amount');
        });
    }
}
