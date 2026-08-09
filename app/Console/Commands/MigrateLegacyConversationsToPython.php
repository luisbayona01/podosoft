<?php

namespace App\Console\Commands;

use App\Models\ConversacionIA;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class MigrateLegacyConversationsToPython extends Command
{
    protected $signature = 'conversations:migrate-to-python
                            {--dry-run : Show what would change without writing}
                            {--chunk=200 : Process in chunks of this size}';

    protected $description = 'Close and flag all active legacy conversations in MySQL so the Python agent owns state from now on.';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $chunkSize = (int) $this->option('chunk');

        $query = ConversacionIA::where('migrated_to_python', false)
            ->where('estado', '!=', 'cerrada');

        $total = (clone $query)->count();

        $this->info("Found {$total} legacy conversation(s) to migrate" . ($dryRun ? ' (DRY-RUN, no writes)' : '.'));

        if ($total === 0) {
            return self::SUCCESS;
        }

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $migrated = 0;

        $query->orderBy('id')->chunkById($chunkSize, function ($rows) use ($dryRun, &$migrated, $bar) {
            foreach ($rows as $conv) {
                $previousStep = $conv->metadata['conversation_step'] ?? null;

                if (!$dryRun) {
                    $conv->update([
                        'estado' => 'cerrada',
                        'migrated_to_python' => true,
                        'metadata' => null,
                        'ultima_interaccion' => now(),
                    ]);
                }

                Log::info('[MigrateToPython] conversation migrated', [
                    'conversation_id' => $conv->id,
                    'tenant_id' => $conv->tenant_id,
                    'phone' => $conv->telefono,
                    'previous_step' => $previousStep,
                    'dry_run' => $dryRun,
                ]);

                $migrated++;
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine();
        $this->info("Done. {$migrated} conversation(s) " . ($dryRun ? 'would be' : 'were') . ' migrated.');

        return self::SUCCESS;
    }
}
