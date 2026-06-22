<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Doppelte Keys ermitteln und alle außer dem ersten Vorkommen auf null setzen
        $duplicates = DB::table('customers')
            ->select('key')
            ->whereNotNull('key')
            ->groupBy('key')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('key');

        foreach ($duplicates as $key) {
            $ids = DB::table('customers')
                ->where('key', $key)
                ->orderBy('id')
                ->pluck('id');

            // Ersten Eintrag behalten, alle weiteren auf null setzen
            $idsToNullify = $ids->slice(1)->values();

            DB::table('customers')
                ->whereIn('id', $idsToNullify)
                ->update(['key' => null]);
        }

        Schema::table('customers', function (Blueprint $table) {
            $table->unique('key');
        });
    }

    public function down()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropUnique(['key']);
        });
    }
};
