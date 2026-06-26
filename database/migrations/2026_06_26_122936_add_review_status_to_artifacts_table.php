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
        Schema::table('artifacts', function (Blueprint $table) {
            $table->string('review_status')->default('pending')->after('processing_status');
            $table->timestamp('reviewed_at')->nullable()->after('review_status');

            $table->index(['project_id', 'review_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('artifacts', function (Blueprint $table) {
            $table->dropIndex(['project_id', 'review_status']);
            $table->dropColumn(['review_status', 'reviewed_at']);
        });
    }
};
