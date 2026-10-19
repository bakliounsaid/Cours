<?php

namespace App\Settings;

/**
 * Valeurs par défaut des réglages (utilisées par le seeder et en repli).
 */
class DefaultSettings
{
    /**
     * @return array<string, mixed>
     */
    public static function all(): array
    {
        return [
            'payments.count_absences' => false,
            'payments.pre_reminder' => true,
            'payments.lessons_per_cycle' => (int) config('educoeur.payments.default_lessons_covered'),

            'contact.phone' => config('educoeur.contact.phone'),
            'contact.whatsapp' => config('educoeur.contact.whatsapp'),
            'contact.email' => config('educoeur.contact.email'),
            'contact.address' => [
                'fr' => 'Cité Ighil Taourga, Boumerdès',
                'ar' => 'حي إغيل تاورقة، بومرداس',
                'en' => 'Cité Ighil Taourga, Boumerdès',
            ],
            'contact.latitude' => (float) config('educoeur.contact.latitude'),
            'contact.longitude' => (float) config('educoeur.contact.longitude'),

            'home.hero_title' => [
                'fr' => 'Chaque enfant mérite qu’on croie en lui',
                'ar' => 'كل طفل يستحق من يؤمن به',
                'en' => 'Every child deserves someone who believes in them',
            ],
            'home.hero_tagline' => [
                'fr' => 'Des cours de soutien bienveillants et sérieux, en petits groupes, pour que votre enfant reprenne confiance et réussisse à l’école.',
                'ar' => 'دروس دعم جادة وبروح طيبة، في أفواج صغيرة، ليستعيد طفلك ثقته بنفسه وينجح في دراسته.',
                'en' => 'Caring and serious tutoring in small groups, so your child regains confidence and succeeds at school.',
            ],
            'home.story_year' => 2019,
            'home.story' => [
                'fr' => 'Éducœur a été fondée en 2019 par Essaid Baklioun avec une conviction simple : chaque enfant mérite d’être accompagné, quels que soient les moyens de sa famille. L’école est née pour aider les familles modestes du quartier à offrir à leurs enfants un soutien scolaire de qualité, et pour orienter les nouvelles générations vers la réussite, la confiance en soi et un avenir choisi. Depuis, nous enseignons avec la même idée : transmettre le savoir avec cœur.',
                'ar' => 'تأسست مدرسة إديكور سنة 2019 على يد السعيد بكليون، انطلاقاً من قناعة بسيطة: كل طفل يستحق المرافقة مهما كانت إمكانيات عائلته. وُلدت المدرسة لمساعدة العائلات ذات الدخل المحدود في الحي على توفير دعم مدرسي ذي جودة لأبنائها، ولتوجيه الأجيال الجديدة نحو النجاح والثقة بالنفس ومستقبل يختارونه بأنفسهم. ومنذ ذلك الحين نُعلّم بنفس الفكرة: نقل المعرفة بقلب.',
                'en' => 'Éducœur was founded in 2019 by Essaid Baklioun with a simple belief: every child deserves support, whatever their family’s means. The school was created to help modest families in the neighbourhood give their children quality tutoring, and to guide the new generation towards success, self-confidence and a future of their own choosing. Since then, we have taught with the same idea: sharing knowledge from the heart.',
            ],
            'home.story_founder' => [
                'fr' => 'Essaid Baklioun, fondateur',
                'ar' => 'السعيد بكليون، المؤسس',
                'en' => 'Essaid Baklioun, founder',
            ],
            'home.missions' => [
                [
                    'icon' => 'heart',
                    'title' => ['fr' => 'Aider les familles modestes', 'ar' => 'مساعدة العائلات ذات الدخل المحدود', 'en' => 'Helping modest families'],
                    'text' => ['fr' => 'Un soutien scolaire de qualité, à la portée des familles du quartier.', 'ar' => 'دعم مدرسي ذو جودة في متناول عائلات الحي.', 'en' => 'Quality tutoring within reach of local families.'],
                ],
                [
                    'icon' => 'light-bulb',
                    'title' => ['fr' => 'Orienter la nouvelle génération', 'ar' => 'توجيه الجيل الجديد', 'en' => 'Guiding the new generation'],
                    'text' => ['fr' => 'Donner confiance, méthode et ambition pour choisir son avenir.', 'ar' => 'منح الثقة والمنهجية والطموح لاختيار المستقبل.', 'en' => 'Building confidence, method and ambition to choose their future.'],
                ],
                [
                    'icon' => 'user-group',
                    'title' => ['fr' => 'Réussir ensemble', 'ar' => 'النجاح معًا', 'en' => 'Succeeding together'],
                    'text' => ['fr' => 'Élèves, parents et enseignants avancent main dans la main.', 'ar' => 'التلاميذ والأولياء والأساتذة يتقدمون يدًا بيد.', 'en' => 'Students, parents and teachers moving forward hand in hand.'],
                ],
            ],
            'home.features' => [
                [
                    'icon' => 'user-group',
                    'title' => ['fr' => 'Petits groupes', 'ar' => 'أفواج صغيرة', 'en' => 'Small groups'],
                    'text' => ['fr' => '15 élèves maximum par groupe, pour que chacun puisse poser ses questions.', 'ar' => '15 تلميذًا كحد أقصى في كل فوج، ليتمكن كل واحد من طرح أسئلته.', 'en' => '15 students maximum per group, so everyone can ask their questions.'],
                ],
                [
                    'icon' => 'heart',
                    'title' => ['fr' => 'Suivi de chaque élève', 'ar' => 'متابعة كل تلميذ', 'en' => 'Every student followed'],
                    'text' => ['fr' => 'Présences, progrès et échanges réguliers avec les parents.', 'ar' => 'متابعة الحضور والتقدم وتواصل منتظم مع الأولياء.', 'en' => 'Attendance, progress and regular contact with parents.'],
                ],
                [
                    'icon' => 'academic-cap',
                    'title' => ['fr' => 'Enseignants qualifiés', 'ar' => 'أساتذة مؤهلون', 'en' => 'Qualified teachers'],
                    'text' => ['fr' => 'Une équipe expérimentée, patiente et passionnée par la transmission.', 'ar' => 'فريق ذو خبرة، صبور وشغوف بالتعليم.', 'en' => 'An experienced, patient team with a passion for teaching.'],
                ],
                [
                    'icon' => 'user',
                    'title' => ['fr' => 'Cours individuels', 'ar' => 'دروس فردية', 'en' => 'One-to-one lessons'],
                    'text' => ['fr' => 'Des séances sur mesure pour avancer à son rythme.', 'ar' => 'حصص مصممة على المقاس للتقدم حسب الوتيرة الخاصة.', 'en' => 'Tailor-made sessions to progress at their own pace.'],
                ],
            ],
        ];
    }

    public static function get(string $key): mixed
    {
        return self::all()[$key] ?? null;
    }
}
