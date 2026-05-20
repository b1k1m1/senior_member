<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->foreignId('event_type_id')->constrained('event_types')->onDelete('restrict');
            $table->date('start_date');
            $table->date('end_date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('location', 255)->nullable();
            $table->integer('capacity')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->enum('status', ['ACTIVE', 'SCHEDULED', 'RESCHEDULED', 'CANCELLED', 'COMPLETED'])->default('ACTIVE');
            $table->date('confirmation_deadline')->nullable();
            $table->integer('min_attendees')->default(0);
            $table->enum('attendance_type', ['MEMBERS_ONLY', 'MEMBERS_WITH_GUESTS'])->default('MEMBERS_ONLY');
            $table->integer('max_guests_per_member')->default(0);
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
