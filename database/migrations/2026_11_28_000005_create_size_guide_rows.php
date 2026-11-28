<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Size guide for abaya lengths: which length (inches) fits which body
 * height. Starts with the usual abaya chart; the shop edits it in the admin.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('size_guide_rows', function (Blueprint $table) {
            $table->id();
            $table->string('size', 10);
            $table->unsignedSmallInteger('height_min');   // cm
            $table->unsignedSmallInteger('height_max');   // cm
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();
        });

        $now = now();
        $rows = [['50', 147, 152], ['52', 152, 157], ['54', 157, 163], ['56', 163, 168], ['58', 168, 173], ['60', 173, 178], ['62', 178, 183]];
        DB::table('size_guide_rows')->insert(array_map(fn ($row, $i) => [
            'size' => $row[0], 'height_min' => $row[1], 'height_max' => $row[2], 'position' => $i,
            'created_at' => $now, 'updated_at' => $now,
        ], $rows, array_keys($rows)));
    }

    public function down(): void
    {
        Schema::dropIfExists('size_guide_rows');
    }
};
