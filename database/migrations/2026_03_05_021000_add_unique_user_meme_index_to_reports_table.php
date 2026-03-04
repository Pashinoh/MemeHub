<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $duplicates = DB::table('reports')
            ->select('meme_id', 'user_id', DB::raw('MAX(id) as keep_id'))
            ->groupBy('meme_id', 'user_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $duplicate) {
            DB::table('reports')
                ->where('meme_id', $duplicate->meme_id)
                ->where('user_id', $duplicate->user_id)
                ->where('id', '!=', $duplicate->keep_id)
                ->delete();
        }

        Schema::table('reports', function (Blueprint $table) {
            $table->unique(['meme_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropUnique(['meme_id', 'user_id']);
        });
    }
};
