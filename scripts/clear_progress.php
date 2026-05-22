<?php
// scripts/clear_progress.php
// This script clears all player progression records.

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// Truncate the progression_enigmes table (player progress)
DB::table('progression_enigmes')->truncate();

echo "All player progressions have been cleared.\n";
?>
