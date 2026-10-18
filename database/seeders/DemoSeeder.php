<?php

namespace Database\Seeders;

use App\Actions\Lessons\GenerateGroupLessons;
use App\Enums\LessonStatus;
use App\Enums\Locale;
use App\Enums\UserRole;
use App\Models\Group;
use App\Models\Lesson;
use App\Models\Level;
use App\Models\Room;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TimeSlot;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Données de démonstration : comptes de test, enseignants, groupes et élèves.
 */
class DemoSeeder extends Seeder
{
    public const ADMIN_EMAIL = 'admin@educoeur.dz';

    public const TEACHER_EMAIL = 'enseignant@educoeur.dz';

    public const PASSWORD = 'password';

    private const FIRST_NAMES = ['Yanis', 'Amine', 'Rayan', 'Adam', 'Ilyes', 'Wassim', 'Anis', 'Mehdi', 'Sofiane', 'Nassim', 'Lina', 'Sara', 'Meriem', 'Yasmine', 'Ines', 'Aya', 'Rania', 'Nour', 'Malak', 'Hiba', 'Imene', 'Khadidja', 'Selma', 'Amel', 'Riad', 'Walid', 'Fares', 'Islam', 'Zakaria', 'Abdelkader'];

    private const LAST_NAMES = ['Benali', 'Boudiaf', 'Cherif', 'Haddad', 'Mansouri', 'Kaci', 'Belkacem', 'Saidi', 'Hamidi', 'Ouali', 'Brahimi', 'Mebarki', 'Ait Ahmed', 'Larbi', 'Zerrouki', 'Bouzid', 'Rahmani', 'Djebbar', 'Amrani', 'Toumi'];

    public function run(): void
    {
        User::query()->updateOrCreate(['email' => self::ADMIN_EMAIL], [
            'name' => 'Direction Éducœur',
            'password' => Hash::make(self::PASSWORD),
            'role' => UserRole::Admin,
            'locale' => Locale::Fr,
            'email_verified_at' => now(),
        ]);

        $teachers = [
            'karim' => $this->teacher('Karim Benali', self::TEACHER_EMAIL, '0555 12 34 56', 2000, ['maths', 'physique']),
            'amina' => $this->teacher('Amina Haddad', 'amina@educoeur.dz', '0661 23 45 67', 1800, ['arabe'], Locale::Ar),
            'sarah' => $this->teacher('Sarah Messaoudi', 'sarah@educoeur.dz', '0770 34 56 78', 1800, ['francais']),
            'yacine' => $this->teacher('Yacine Boudiaf', 'yacine@educoeur.dz', '0550 45 67 89', 1800, ['anglais']),
            'nadia' => $this->teacher('Nadia Cherif', 'nadia@educoeur.dz', '0698 56 78 90', 2000, ['maths', 'physique']),
        ];

        $rooms = Room::query()->pluck('id', 'name');
        $subjects = Subject::query()->pluck('id', 'slug');
        $levels = Level::query()->get()->keyBy(fn (Level $level) => $level->cycle->value.$level->year);

        // [nom, matière, niveau, enseignant, salle, créneaux [jour, heure]]
        $groups = [
            ['Maths 4AM', 'maths', 'middle4', 'karim', 'Portail', [[0, '17:00'], [2, '17:00']]],
            ['Physique 4AM', 'physique', 'middle4', 'karim', 'Portail', [[1, '17:00']]],
            ['Arabe 5AP', 'arabe', 'primary5', 'amina', 'Mur', [[0, '17:00'], [6, '10:00']]],
            ['Français 1AM', 'francais', 'middle1', 'sarah', 'Mur', [[1, '17:00'], [3, '17:00']]],
            ['Anglais 3AM', 'anglais', 'middle3', 'yacine', 'Portail', [[3, '17:00'], [6, '12:30']]],
            ['Maths 2AM', 'maths', 'middle2', 'nadia', 'Mur', [[2, '17:00'], [6, '08:00']]],
            ['Français 5AP', 'francais', 'primary5', 'sarah', 'Portail', [[5, '08:00']]],
            ['Maths 5AP', 'maths', 'primary5', 'nadia', 'Portail', [[6, '08:00']]],
        ];

        $nameIndex = 0;

        foreach ($groups as [$name, $subject, $level, $teacher, $room, $slots]) {
            $group = Group::query()->updateOrCreate(['name' => $name], [
                'subject_id' => $subjects[$subject],
                'level_id' => $levels[$level]->id,
                'teacher_id' => $teachers[$teacher]->id,
                'room_id' => $rooms[$room],
                'starts_on' => now()->subMonth()->startOfMonth()->toDateString(),
                'is_active' => true,
            ]);

            $group->timeSlots()->sync(collect($slots)->map(
                fn (array $slot) => TimeSlot::query()->where('day_of_week', $slot[0])->where('starts_at', $slot[1].':00')->value('id')
            ));

            if ($group->students()->exists()) {
                continue;
            }

            // Les élèves du même niveau suivent plusieurs matières : on réutilise ceux qui existent.
            $students = Student::query()->where('level_id', $levels[$level]->id)->take(8)->get();

            while ($students->count() < 8 + ($nameIndex % 4)) {
                $students->push($this->student($levels[$level], $nameIndex++));
            }

            foreach ($students as $student) {
                $group->students()->attach($student->id, ['joined_at' => $group->starts_on]);
                $student->subjects()->syncWithoutDetaching([$subjects[$subject]]);
            }
        }

        $this->lessons();
    }

    /**
     * Séances : 4 semaines d'historique (faites et pointées) et 2 semaines à venir.
     */
    private function lessons(): void
    {
        app(GenerateGroupLessons::class)->handle(today()->subWeeks(4), today()->addDays(14));

        Lesson::query()
            ->with('teacher')
            ->where('ends_at', '<', now())
            ->where('status', LessonStatus::Scheduled)
            ->whereDoesntHave('checkin')
            ->each(function (Lesson $lesson) {
                $lesson->update(['status' => LessonStatus::Done]);
                $lesson->checkin()->create([
                    'teacher_id' => $lesson->teacher_id,
                    'checked_at' => $lesson->starts_at->copy()->addMinutes(5),
                    'rate_amount' => $lesson->teacher->rate_per_lesson,
                ]);
            });
    }

    /**
     * @param  list<string>  $subjectSlugs
     */
    private function teacher(string $name, string $email, string $phone, int $rate, array $subjectSlugs, Locale $locale = Locale::Fr): Teacher
    {
        $user = User::query()->updateOrCreate(['email' => $email], [
            'name' => $name,
            'password' => Hash::make(self::PASSWORD),
            'role' => UserRole::Teacher,
            'locale' => $locale,
            'email_verified_at' => now(),
        ]);

        $teacher = Teacher::query()->updateOrCreate(['user_id' => $user->id], [
            'phone' => $phone,
            'rate_per_lesson' => $rate,
            'is_active' => true,
        ]);

        $teacher->subjects()->sync(Subject::query()->whereIn('slug', $subjectSlugs)->pluck('id'));

        return $teacher;
    }

    private function student(Level $level, int $index): Student
    {
        return Student::query()->create([
            'first_name' => self::FIRST_NAMES[$index % count(self::FIRST_NAMES)],
            'last_name' => self::LAST_NAMES[($index * 7) % count(self::LAST_NAMES)],
            'level_id' => $level->id,
            'parent_phone' => sprintf('0%d%02d %02d %02d %02d', [5, 6, 7][$index % 3], 50 + $index, ($index * 13) % 100, ($index * 29) % 100, ($index * 41) % 100),
            'parent_locale' => $index % 3 === 0 ? Locale::Ar : Locale::Fr,
            'enrolled_at' => now()->subMonths(2)->addDays($index % 20)->toDateString(),
            'is_active' => true,
        ]);
    }
}
