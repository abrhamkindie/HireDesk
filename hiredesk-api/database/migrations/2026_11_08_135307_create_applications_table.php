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
    $table->string('resume_path', 500);
    $table->enum('status', [
        'new', 'screening', 'shortlisted', 'interview',
        'offer', 'hired', 'rejected', 'withdrawn',
    ])->default('new');
    $table->timestamp('applied_at')->useCurrent();
    $table->timestamp('status_updated_at')->nullable();
    $table->timestamps();

    $table->unique(['vacancy_id', 'candidate_id']);
    $table->index(['vacancy_id', 'status']);
    $table->index(['candidate_id', 'status']);
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
