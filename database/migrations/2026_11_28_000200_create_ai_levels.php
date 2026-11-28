<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The AI path: Level 1 → Level 5, each described in Dari, Pashto and English (edited in Admin → AI levels).
        Schema::create('ai_levels', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('number')->unique();
            $table->json('content'); // {prs|ps|en: {title, tagline, who, learn (one per line), after, duration}}
            $table->foreignId('course_id')->nullable()->constrained()->nullOnDelete(); // the course students take for this level
            $table->unsignedInteger('price_afn')->nullable(); // shown when no course is linked; 0 = free; empty = not shown
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // A first draft of the five levels, so the home page has them from day one.
        $draft = json_decode(file_get_contents(database_path('data/ai_levels.json')), true);
        foreach ($draft as $number => $content) {
            DB::table('ai_levels')->insert([
                'number' => (int) $number, 'content' => json_encode($content, JSON_UNESCAPED_UNICODE),
                'price_afn' => (int) $number === 1 ? 0 : null, 'is_active' => true,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_levels');
    }
};
