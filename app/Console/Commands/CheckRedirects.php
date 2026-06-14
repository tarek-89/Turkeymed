<?php

namespace App\Console\Commands;

use App\Models\Redirect;
use Illuminate\Console\Command;

class CheckRedirects extends Command
{
    protected $signature = 'redirects:check';

    protected $description = 'Detect redirect chains and loops among active redirects (a 301 whose target is itself redirected).';

    public function handle(): int
    {
        $active = Redirect::query()->where('is_active', true)->get();

        /** @var array<string, true> $sources */
        $sources = $active->mapWithKeys(fn (Redirect $r): array => [Redirect::normalizePath($r->from_path) => true])->all();

        $problems = [];

        foreach ($active as $redirect) {
            $target = Redirect::normalizePath((string) parse_url($redirect->to_path, PHP_URL_PATH));

            if ($target === '') {
                continue;
            }

            if ($target === Redirect::normalizePath($redirect->from_path)) {
                $problems[] = "LOOP: /{$redirect->from_path} points at itself";
            } elseif (isset($sources[$target])) {
                $problems[] = "CHAIN: /{$redirect->from_path} → {$redirect->to_path}, which is itself an active redirect";
            }
        }

        if ($problems === []) {
            $this->info('No redirect chains or loops found among '.$active->count().' active redirect(s).');

            return self::SUCCESS;
        }

        $this->warn(count($problems).' issue(s) found — flatten these so every old URL points directly at its final destination:');
        foreach ($problems as $problem) {
            $this->line('  • '.$problem);
        }

        return self::FAILURE;
    }
}
