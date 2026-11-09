<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Section;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class TableTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => Role::SuperAdmin]);
        foreach (['Chan', 'Bopha', 'Arun'] as $name) {
            Student::factory()->create(['first_name' => $name, 'last_name' => 'Test']);
        }
    }

    public function test_list_sorts_by_a_column_in_both_directions(): void
    {
        $this->actingAs($this->admin)->get('/student/list?sort=name&direction=asc')
            ->assertOk()->assertSeeInOrder(['Arun Test', 'Bopha Test', 'Chan Test'])
            ->assertSee('aria-sort="ascending"', false);

        $this->actingAs($this->admin)->get('/student/list?sort=name&direction=desc')
            ->assertOk()->assertSeeInOrder(['Chan Test', 'Bopha Test', 'Arun Test'])
            ->assertSee('aria-sort="descending"', false);
    }

    public function test_unknown_sort_falls_back_to_the_default_order(): void
    {
        // default for students is newest first
        $this->actingAs($this->admin)->get('/student/list?sort=password&direction=sideways')
            ->assertOk()->assertSeeInOrder(['Arun Test', 'Bopha Test', 'Chan Test'])
            ->assertDontSee('aria-sort=', false);
    }

    public function test_rows_per_page_is_chosen_from_the_allowed_sizes(): void
    {
        $section = Section::first();
        Student::factory()->count(12)->create(['section_id' => $section->id, 'school_class_id' => $section->school_class_id]);

        $this->actingAs($this->admin)->get('/student/list?per_page=10')
            ->assertOk()->assertSee('Showing <span class="font-semibold text-slate-700">1</span>–<span class="font-semibold text-slate-700">10</span>', false)
            ->assertSee('of <span class="font-semibold text-slate-700">15</span> results', false);

        // a size that isn't offered falls back to the page default (15)
        $this->actingAs($this->admin)->get('/student/list?per_page=9999')
            ->assertOk()->assertSee('–<span class="font-semibold text-slate-700">15</span>', false);
    }

    public function test_pagination_and_sort_links_keep_the_search(): void
    {
        $section = Section::first();
        Student::factory()->count(20)->create(['last_name' => 'Keep', 'section_id' => $section->id, 'school_class_id' => $section->school_class_id]);

        $this->actingAs($this->admin)->get('/student/list?search=Keep&per_page=10')
            ->assertOk()
            ->assertSee('search=Keep&amp;per_page=10&amp;page=2', false)
            ->assertSee('search=Keep&amp;per_page=10&amp;sort=name&amp;direction=asc', false);
    }

    /** every sortable column of every list page, as [uri, sort key] */
    public static function sortableColumns(): array
    {
        $pages = [
            'student/list' => ['admission_id', 'name', 'class', 'date_of_birth', 'newest'],
            'teacher/list/page' => ['code', 'name', 'department', 'qualification', 'experience', 'newest'],
            'list/users' => ['code', 'name', 'role', 'position', 'join_date', 'status'],
            'subject/list/page' => ['code', 'name', 'class'],
            'department/list/page' => ['code', 'name', 'head', 'teachers', 'started', 'students'],
            'books' => ['title', 'category', 'isbn', 'shelf', 'copies'],
            'library/loans?tab=returned' => ['book', 'student', 'issued', 'due', 'returned', 'fine'],
            'fees/balances' => ['student', 'class'],
            'expenses' => ['date', 'title', 'category', 'paid_to', 'method', 'amount'],
            'exams' => ['name', 'class', 'term', 'dates', 'max_marks', 'marks'],
            'account/fees/collections/page' => ['admission_id', 'student', 'fee_type', 'amount', 'paid_date'],
            'invoice/list/page' => ['invoice', 'created', 'customer', 'amount', 'due_date', 'status', 'newest'],
        ];

        $cases = [];
        foreach ($pages as $uri => $keys) {
            foreach ($keys as $key) {
                $cases["{$uri} by {$key}"] = [$uri, $key];
            }
        }

        return $cases;
    }

    #[DataProvider('sortableColumns')]
    public function test_every_sortable_column_works_in_both_directions(string $uri, string $key): void
    {
        foreach (['asc', 'desc'] as $direction) {
            $separator = str_contains($uri, '?') ? '&' : '?';
            $this->actingAs($this->admin)->get("/{$uri}{$separator}sort={$key}&direction={$direction}")->assertOk();
        }
    }
}
