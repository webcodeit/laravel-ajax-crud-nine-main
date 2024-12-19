<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->text('mobile')->nullable();;
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->json('hobbies')->nullable();
            $table->string('file_name');
            $table->string('file_size');
            $table->string('file_last_modified');
            $table->string('file_path');
            $table->enum('type', ['Pending', 'Rejected', 'Approved'])->default('Pending');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->boolean('verify')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('posts');
    }
};
