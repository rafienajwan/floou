<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('recipient_name')->nullable()->after('user_id');
            $table->string('recipient_phone')->nullable()->after('recipient_name');
            $table->string('shipping_method')->default('standard')->after('shipping_address'); // standard, express
            $table->decimal('shipping_cost', 10, 2)->default(0)->after('shipping_method');
            $table->string('invoice_number')->unique()->nullable()->after('id');
            $table->timestamp('completed_at')->nullable()->after('status');
            $table->timestamp('canceled_at')->nullable()->after('completed_at');
            $table->text('cancel_reason')->nullable()->after('canceled_at');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'recipient_name',
                'recipient_phone',
                'shipping_method',
                'shipping_cost',
                'invoice_number',
                'completed_at',
                'canceled_at',
                'cancel_reason'
            ]);
        });
    }
};
