<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            // Ajouter visibilité
            $table->string('visibility')->default('public')->after('content');

            // Soft delete (deleted_at)
            $table->softDeletes();

            // Supprimer anciens champs médias
            $table->dropColumn(['image_url', 'video_url']);
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            // Recréer les colonnes supprimées
            $table->string('image_url')->nullable()->after('content');
            $table->string('video_url')->nullable()->after('image_url');

            // Supprimer ce qu'on a ajouté
            $table->dropColumn('visibility');
            $table->dropSoftDeletes();
        });
    }
};