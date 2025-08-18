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
        Schema::table('carritos', function (Blueprint $table) {
            // después de delivery_currency; ajusta el "after" si tu esquema es distinto
            if (!Schema::hasColumn('carritos', 'stripe_payment_intent_id')) {
                $table->string('stripe_payment_intent_id')->nullable()->after('delivery_currency');
            }
        });
    }

    public function down(): void
    {
        Schema::table('carritos', function (Blueprint $table) {
            if (Schema::hasColumn('carritos', 'stripe_payment_intent_id')) {
                $table->dropColumn('stripe_payment_intent_id');
            }
        });
    }
};
