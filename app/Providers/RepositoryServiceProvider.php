<?php

namespace App\Providers;

use App\Repositories\Contracts;
use App\Repositories\Eloquent;
use Illuminate\Support\ServiceProvider;

/**
 * Pattern Repository : chaque contrat de lecture est relié à son implémentation Eloquent.
 * Les composants et les services dépendent des contrats, jamais d'Eloquent directement.
 */
class RepositoryServiceProvider extends ServiceProvider
{
    /** @var array<class-string, class-string> */
    public array $singletons = [
        Contracts\AttendanceRepository::class => Eloquent\EloquentAttendanceRepository::class,
        Contracts\GroupRepository::class => Eloquent\EloquentGroupRepository::class,
        Contracts\LessonRepository::class => Eloquent\EloquentLessonRepository::class,
        Contracts\PaymentRepository::class => Eloquent\EloquentPaymentRepository::class,
        Contracts\StudentRepository::class => Eloquent\EloquentStudentRepository::class,
        Contracts\TeacherRepository::class => Eloquent\EloquentTeacherRepository::class,
    ];
}
