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
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('title', 150);
            $table->string('slug', 150);
            $table->foreignId('category_id')->constrained('categories');
            $table->string('cover', 100);
            $table->string('thumbnail', 100);
            $table->text('content');
            $table->date('publication_date')->nullable();
            $table->enum('is_draft', [0, 1])->default(1);
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
