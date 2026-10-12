<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('book_member', function (Blueprint $table) {
            $table->dateTime('due_at')->nullable()->after('borrowed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('book_member', function (Blueprint $table) {
            $table->dropColumn('due_at');
        });
    }
};
