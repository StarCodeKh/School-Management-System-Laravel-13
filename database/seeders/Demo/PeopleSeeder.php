<?php

namespace Database\Seeders\Demo;

use App\Enums\BloodGroup;
use App\Enums\Gender;
use App\Enums\GuardianRelationship;
use App\Enums\Religion;
use App\Enums\Role;
use App\Enums\UserStatus;
use App\Models\Department;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Classes and sections, departments, teachers, office staff, students with their parents,
 * and invoice customers. Also creates one easy-to-remember login per role (see LOGINS).
 */
class PeopleSeeder extends Seeder
{
    /** Fixed demo logins, printed by `school:demo`. */
    public const LOGINS = [
        'teacher@school.test' => Role::Teacher,
        'staff@school.test' => Role::Staff,
        'student@school.test' => Role::Student,
        'parent@school.test' => Role::Parent,
        'client@school.test' => Role::Client,
    ];

    /** department => qualifications its teachers hold (names match AcademicsSeeder::SUBJECTS) */
    public const DEPARTMENTS = [
        'Mathematics' => ['MSc Mathematics', 'BSc Mathematics'],
        'Sciences' => ['MSc Physics', 'BSc Chemistry', 'BSc Biology'],
        'Languages' => ['BA Khmer Literature', 'BA English (TESOL)', 'MA Linguistics'],
        'Social Studies' => ['BA History', 'BA Geography'],
        'Information Technology' => ['BSc Computer Science', 'BSc Information Technology'],
        'Sports & Arts' => ['BEd Physical Education', 'BA Fine Arts'],
    ];

    private string $password;

    /** family name => [parent account, relationship], so brothers and sisters can share one */
    private array $families = [];

    private bool $firstParent = true;

    public function run(): void
    {
        $this->password = Hash::make(config('school.demo.password'));

        $this->classes();
        $teacherUsers = $this->teachers();
        $this->staff();
        $this->students();
        $this->clients();

        // the first teacher heads each department
        Department::all()->each(fn (Department $department) => $department->update([
            'head_user_id' => $teacherUsers->first(fn (User $user) => $user->teacher?->department_id === $department->id)?->id,
        ]));
    }

    private function classes(): void
    {
        foreach (config('school.demo.classes') as $order => $name) {
            // numbered grades sort by their number, so 7 comes before 10
            $class = SchoolClass::updateOrCreate(['name' => (string) $name], ['sort_order' => is_numeric($name) ? (int) $name : $order + 1]);
            foreach (config('school.demo.sections') as $section) {
                $class->sections()->firstOrCreate(['name' => $section]);
            }
        }
    }

    private function teachers()
    {
        $faker = DemoData::faker();
        $departments = collect(self::DEPARTMENTS)->keys()->mapWithKeys(fn (string $name, int $i) => [$name => Department::firstOrCreate(
            ['department_name' => $name],
            ['department_start_date' => today()->subYears(10 - $i)->startOfYear()],
        )]);

        $users = collect();
        foreach ($this->teacherDepartments() as $i => $departmentName) {
            $gender = DemoData::gender();
            [$given, $family] = DemoData::name($gender);
            $user = $this->user(Role::Teacher, "{$given} {$family}", $i === 0 ? 'teacher@school.test' : DemoData::email($given, $family), [
                'position' => $departmentName.' Teacher',
                'join_date' => today()->subDays($faker->numberBetween(120, 3000)),
            ]);
            $place = DemoData::address();
            Teacher::create([
                'user_id' => $user->id,
                'department_id' => $departments[$departmentName]->id,
                'gender' => $gender,
                'date_of_birth' => DemoData::dateBetween('-55 years', '-24 years'),
                'qualification' => $faker->randomElement(self::DEPARTMENTS[$departmentName]),
                'experience' => $faker->numberBetween(1, 25),
                'phone_number' => DemoData::phone(),
                'address' => $place['address'],
                'city' => $place['city'],
                'state' => $place['state'],
                'zip_code' => $place['zip'],
                'country' => 'Cambodia',
            ]);
            $users->push($user->load('teacher'));
        }

        return $users;
    }

    /**
     * The department of each teacher to create. Departments get teachers in proportion to their
     * weekly lessons (3 per subject per section, about 18 per teacher, which leaves room to avoid clashes), interleaved so a smaller
     * --teachers count still covers every department.
     */
    private function teacherDepartments(): array
    {
        $sections = Section::count();
        $need = collect(AcademicsSeeder::SUBJECTS)->countBy()
            ->map(fn (int $subjects) => max(1, (int) ceil($sections * 3 * $subjects / 18)));

        $order = [];
        for ($round = 0; $round < $need->max(); $round++) {
            foreach ($need as $department => $count) {
                if ($round < $count) {
                    $order[] = $department;
                }
            }
        }

        $total = config('school.demo.teachers') ?: count($order);

        return array_map(fn (int $i) => $order[$i % count($order)], range(0, $total - 1));
    }

    private function staff(): void
    {
        $positions = ['Accountant', 'Librarian', 'Registrar', 'IT Support', 'Nurse'];
        foreach ($positions as $i => $position) {
            [$given, $family] = DemoData::name(DemoData::gender());
            $this->user(Role::Staff, "{$given} {$family}", $i === 0 ? 'staff@school.test' : DemoData::email($given, $family), [
                'position' => $position,
                'join_date' => today()->subDays(DemoData::faker()->numberBetween(200, 2500)),
            ]);
        }
        [$given, $family] = DemoData::name(DemoData::gender());
        $this->user(Role::Admin, "{$given} {$family}", DemoData::email($given, $family), ['position' => 'Vice Principal']);
    }

    private function students(): void
    {
        $faker = DemoData::faker();
        $perSection = config('school.demo.students_per_section');
        $admission = (int) (now()->format('y').'000');
        $firstStudent = true;

        foreach (SchoolClass::with('sections')->orderBy('sort_order')->get() as $class) {
            // age follows the grade: grade 7 is about 13 years old
            $age = 6 + (int) $class->name;
            foreach ($class->sections as $section) {
                for ($roll = 1; $roll <= $perSection; $roll++) {
                    $gender = DemoData::gender();
                    [$given, $family] = DemoData::name($gender);

                    // the first student of every section has a login, so the student dashboard has data
                    $account = $roll === 1
                        ? $this->user(Role::Student, "{$given} {$family}", $firstStudent ? 'student@school.test' : DemoData::email($given, $family, 'student.school.test'))
                        : null;
                    $firstStudent = false;

                    $student = Student::create([
                        'user_id' => $account?->id,
                        'admission_id' => 'ADM-'.++$admission,
                        'first_name' => $given,
                        'last_name' => $family,
                        'gender' => $gender,
                        'date_of_birth' => DemoData::dateBetween("-{$age} years -11 months", "-{$age} years"),
                        'blood_group' => $faker->randomElement(BloodGroup::cases()),
                        'religion' => DemoData::weighted([Religion::Buddhism->value => 90, Religion::Islam->value => 5, Religion::Christianity->value => 5]),
                        'email' => DemoData::email($given, $family, 'student.school.test'),
                        'phone_number' => DemoData::phone(),
                        'school_class_id' => $class->id,
                        'section_id' => $section->id,
                        'roll' => (string) $roll,
                    ]);
                    $this->parents($student, $family);
                }
            }
        }
    }

    /** Most students get a parent account; brothers and sisters share one. */
    private function parents(Student $student, string $family): void
    {
        $faker = DemoData::faker();

        // about one in six students has a sibling already in the school
        if (isset($this->families[$family]) && $faker->boolean(17)) {
            [$parent, $relationship] = $this->families[$family];
            $parent->children()->attach($student->id, ['relationship' => $relationship->value]);

            return;
        }
        if (! $faker->boolean(75)) {
            return;
        }

        $relationship = $faker->randomElement([GuardianRelationship::Mother, GuardianRelationship::Father]);
        [$given] = DemoData::name($relationship === GuardianRelationship::Mother ? Gender::Female : Gender::Male);
        $parent = $this->user(Role::Parent, "{$given} {$family}", $this->firstParent ? 'parent@school.test' : DemoData::email($given, $family, 'parent.school.test'));
        $this->firstParent = false;
        $parent->children()->attach($student->id, ['relationship' => $relationship->value]);
        $this->families[$family] = [$parent, $relationship];
    }

    private function clients(): void
    {
        $clients = ['Sunrise Uniform Co.', 'Mekong Book Supply', 'Angkor Catering', 'Golden Bus Services'];
        foreach ($clients as $i => $name) {
            $this->user(Role::Client, $name, $i === 0 ? 'client@school.test' : Str::slug($name).'@client.school.test');
        }
    }

    private function user(Role $role, string $name, string $email, array $extra = []): User
    {
        return User::create($extra + [
            'name' => $name,
            'email' => $email,
            'role' => $role,
            'status' => UserStatus::Active,
            'phone_number' => DemoData::phone(),
            'join_date' => today()->subDays(DemoData::faker()->numberBetween(30, 900)),
            'email_verified_at' => now(),
            'password' => $this->password,
        ]);
    }
}
