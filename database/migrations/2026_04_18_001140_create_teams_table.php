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
        Schema::create('teams', function (Blueprint $table) {
            $table->id();

            $table->foreignId('owner_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('name');
            $table->string('slug')->unique();
            $table->string('tag', 10)->nullable()->unique();
            $table->text('description')->nullable();

            $table->string('logo_url')->nullable();
            $table->string('cover_url')->nullable();

            $table->date('founded_at')->nullable();
            $table->string('location')->nullable();

            $table->string('website_url')->nullable();
            $table->string('discord_url')->nullable();
            $table->string('social_url')->nullable();

            $table->boolean('is_active')->default(true);
            $table->boolean('is_recruiting')->default(false);
            $table->text('recruitment_message')->nullable();

            $table->string('status')->default('amateur');
            // amateur, semi_pro, pro

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teams');
    }
};