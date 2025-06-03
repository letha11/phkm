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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prescription_id')->constrained('prescriptions')->unique()->index();
            $table->string('invoice_number')->unique()->index();
            $table->timestamp('issue_date');
            $table->decimal('subtotal_medicines', 10, 2);
            $table->decimal('consultation_fee', 8, 2);
            $table->decimal('ppn_amount', 8, 2);
            $table->decimal('grand_total', 10, 2);
            $table->string('payment_method');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
