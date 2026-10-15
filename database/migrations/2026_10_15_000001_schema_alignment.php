<?php

use Illuminate\Database\Migrations\Migration;

/*
 * Kept intentionally empty. The application models use the original, linked
 * tables: homes, residents, visits, payments and incidents. Older drafts
 * attempted partial table renames, which broke their foreign keys.
 */
return new class extends Migration {
    public function up(): void {}
    public function down(): void {}
};
