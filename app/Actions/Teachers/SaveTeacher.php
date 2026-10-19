<?php

namespace App\Actions\Teachers;

use App\Enums\UserRole;
use App\Models\Teacher;
use App\Models\User;
use App\Support\Phone;
use Illuminate\Support\Facades\DB;

/**
 * Crée ou met à jour un enseignant et son compte de connexion (rôle enseignant).
 */
class SaveTeacher
{
    /**
     * @param  array{name: string, email: string, password?: ?string, phone?: ?string, rate_group: int|string, rate_individual: int|string, is_active: bool, subject_ids: list<int|string>}  $data
     */
    public function handle(array $data, ?Teacher $teacher = null): Teacher
    {
        return DB::transaction(function () use ($data, $teacher) {
            $user = $teacher?->user ?? new User(['role' => UserRole::Teacher]);

            $user->fill([
                'name' => trim($data['name']),
                'email' => mb_strtolower(trim($data['email'])),
            ]);

            if (filled($data['password'] ?? null)) {
                $user->password = $data['password'];
            }

            $user->email_verified_at ??= now();
            $user->save();

            $teacher ??= new Teacher(['user_id' => $user->id]);
            $teacher->fill([
                'phone' => filled($data['phone'] ?? null) ? Phone::normalize($data['phone']) : null,
                'rate_group' => (int) $data['rate_group'],
                'rate_individual' => (int) $data['rate_individual'],
                'is_active' => (bool) $data['is_active'],
            ])->save();

            $teacher->subjects()->sync(array_map('intval', $data['subject_ids']));

            return $teacher->load('user', 'subjects');
        });
    }
}
