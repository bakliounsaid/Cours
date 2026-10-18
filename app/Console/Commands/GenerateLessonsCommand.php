<?php

namespace App\Console\Commands;

use App\Actions\Lessons\GenerateGroupLessons;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('lessons:generate {--days= : Nombre de jours à générer à partir d’aujourd’hui}')]
#[Description('Génère les séances des groupes à partir de leurs créneaux récurrents')]
class GenerateLessonsCommand extends Command
{
    public function handle(GenerateGroupLessons $generateGroupLessons): int
    {
        $days = (int) ($this->option('days') ?: config('educoeur.lessons.generate_days_ahead'));

        $report = $generateGroupLessons->handle(today(), today()->addDays($days));

        $this->info("{$report['created']} séance(s) créée(s).");

        foreach ($report['skipped'] as $skipped) {
            $this->warn("Ignorée ({$skipped['reason']}) : {$skipped['group']} le {$skipped['starts_at']}");
        }

        return self::SUCCESS;
    }
}
