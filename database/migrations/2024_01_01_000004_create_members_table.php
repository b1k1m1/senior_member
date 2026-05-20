<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->string('member_no', 20)->unique();
            $table->string('first_name', 60);
            $table->string('last_name', 60);
            $table->string('spouse_first_name', 60)->nullable();
            $table->string('spouse_last_name', 60)->nullable();
            $table->string('email', 120)->nullable();
            $table->string('phone', 25)->nullable();
            $table->string('address1', 120)->nullable();
            $table->string('address2', 120)->nullable();
            $table->string('city', 60)->nullable();
            $table->string('state', 30)->nullable();
            $table->string('zip', 15)->nullable();
            $table->foreignId('membership_type_id')->constrained('membership_types')->onDelete('restrict');
            $table->date('membership_start_date')->nullable();
            $table->integer('joining_year')->nullable();
            $table->enum('status', ['ACTIVE', 'INACTIVE'])->default('ACTIVE');
            $table->text('notes')->nullable();
            $table->string('photo_path', 255)->nullable();
            $table->string('receipt_no', 30)->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
