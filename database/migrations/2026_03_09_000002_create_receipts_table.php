<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('receipts', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_no', 30)->unique();
            $table->foreignId('receipt_type_id')->constrained('receipt_types')->onDelete('restrict');
            $table->string('received_from');
            $table->string('address1')->nullable();
            $table->string('address2')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('zip')->nullable();
            $table->string('county')->nullable();
            $table->string('bank_name')->nullable();
            $table->date('check_date')->nullable();
            $table->string('check_number')->nullable();
            $table->enum('payment_mode', ['CASH', 'CHECK', 'CREDIT_CARD'])->default('CASH');
            $table->decimal('amount', 15, 2);
            $table->text('remarks')->nullable();
            
            // For membership receipt
            $table->foreignId('member_id')->nullable()->constrained('members')->onDelete('set null');
            $table->foreignId('membership_type_id')->nullable()->constrained('membership_types')->onDelete('set null');
            $table->boolean('has_spouse')->default(false);
            
            // For event receipt
            $table->foreignId('event_id')->nullable()->constrained('events')->onDelete('set null');
            
            // For donation
            $table->string('donor_name')->nullable();
            
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receipts');
    }
};
