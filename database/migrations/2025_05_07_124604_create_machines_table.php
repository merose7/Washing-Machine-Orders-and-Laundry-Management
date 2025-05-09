<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMachinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('machines', function (Blueprint $table) {
            $table->id(); // Menambahkan kolom ID
            $table->string('name'); // Nama mesin cuci
            $table->string('status')->default('available'); // Status mesin (available, full, under repair)
            $table->integer('price')->default(0); // Harga mesin cuci, bisa diubah jika perlu
            $table->text('description')->nullable(); // Deskripsi mesin, bisa diisi opsional
            $table->timestamps(); // Kolom created_at dan updated_at
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('machines');
    }
}
