<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('availabilities', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->time('hour');
            $table->boolean('is_available')->default(true);
            $table->timestamps();

            $table->unique(['date', 'hour']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('availabilities');
    }
};
