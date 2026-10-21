<?php

use App\Repositories\Contracts;
use App\Repositories\Eloquent;

/**
 * Pattern Repository : les contrats sont reliés aux implémentations Eloquent,
 * et l'espace enseignant ne fait aucune requête lui-même.
 */
it('relie chaque contrat à son implémentation Eloquent', function (string $contract, string $implementation) {
    expect(app($contract))->toBeInstanceOf($implementation)
        ->and(app($contract))->toBe(app($contract));
})->with([
    [Contracts\AttendanceRepository::class, Eloquent\EloquentAttendanceRepository::class],
    [Contracts\GroupRepository::class, Eloquent\EloquentGroupRepository::class],
    [Contracts\LessonRepository::class, Eloquent\EloquentLessonRepository::class],
    [Contracts\PaymentRepository::class, Eloquent\EloquentPaymentRepository::class],
    [Contracts\StudentRepository::class, Eloquent\EloquentStudentRepository::class],
    [Contracts\TeacherRepository::class, Eloquent\EloquentTeacherRepository::class],
]);

it('ne fait aucune requête directe dans l’espace enseignant ni la section élèves de l’admin', function () {
    $files = collect(File::allFiles(app_path('Livewire/Teacher')))
        ->merge(File::allFiles(app_path('Livewire/Admin/Students')))
        ->merge(array_map(fn (string $form) => new SplFileInfo(app_path("Livewire/Forms/{$form}.php")), ['GroupForm', 'StudentForm', 'IndividualLessonForm', 'PaymentForm']));

    foreach ($files as $file) {
        $code = file_get_contents($file->getPathname());

        expect($code)
            ->not->toContain('::query()', $file->getFilename())
            ->not->toContain('DB::', $file->getFilename())
            ->not->toContain('Facades\DB', $file->getFilename());
    }
});
