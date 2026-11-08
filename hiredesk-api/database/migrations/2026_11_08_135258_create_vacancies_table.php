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
Schema::create('vacancies', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->string('slug')->unique();
    $table->text('description');
    $table->text('requirements')->nullable();
    $table->enum('employment_type', [
        'fulltime', 'parttime', 'contract', 'internship', 'remote',
    ])->default('fulltime');
    $table->string('location')->nullable();
    $table->decimal('salary_min', 12, 2)->nullable();
    $table->decimal('salary_max', 12, 2)->nullable();
    $table->date('deadline')->nullable();
    $table->enum('status', ['draft', 'open', 'closed'])->default('draft');
    $table->foreignId('created_by')
        ->constrained('users')
        ->restrictOnDelete();
    $table->timestamps();

    $table->index(['status', 'deadline']);
    $table->index('employment_type');
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vacancies');
    }
};
