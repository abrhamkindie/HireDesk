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
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vacancy_id')->constrained()->cascadeOnDelete();
            $table->foreignId('candidate_id')->constrained()->cascadeOnDelete();
            $table->text('cover_letter')->nullable();
            $table->string('resume_path', 500)->nullable();
            $table->string('resume_disk', 30)->nullable();
            $table->string('status', 20)->default('new');
            $table->timestamp('status_updated_at')->useCurrent();
            $table->timestamps();

            $table->unique(['vacancy_id', 'candidate_id']);
            $table->index(['vacancy_id', 'status']);
            $table->index(['candidate_id', 'status']);
            $table->index(['status', 'status_updated_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
