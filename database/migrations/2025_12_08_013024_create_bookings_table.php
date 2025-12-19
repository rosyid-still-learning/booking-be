<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
{
    Schema::create('bookings', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->foreignId('room_id')->constrained()->onDelete('cascade');
        $table->string('title');
        $table->datetime('start_time');
        $table->datetime('end_time');
        $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending');
        $table->timestamps();
    });
}


    public function down()
    {
        Schema::dropIfExists('bookings');
    }
};