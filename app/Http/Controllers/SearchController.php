<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * Record search behind the top bar's search box (pages are matched in the browser).
 * Each group only appears when the user may open those records.
 */
class SearchController extends Controller
{
    private const LIMIT = 5;

    public function __invoke(Request $request): JsonResponse
    {
        $terms = collect(preg_split('/\s+/', trim((string) $request->query('q'))))->filter()->take(4);

        if (mb_strlen($terms->implode(' ')) < 2) {
            return response()->json(['groups' => []]);
        }

        $canEdit = Gate::allows('manage-school');
        $groups = [];

        if (Gate::allows('teach')) {
            $groups[] = [
                'label' => 'Students',
                'items' => Student::with(['schoolClass:id,name', 'section:id,name'])
                    ->tap(fn (Builder $query) => $this->matchEach($query, $terms, ['first_name', 'last_name', 'admission_id']))
                    ->orderBy('first_name')->limit(self::LIMIT)->get()
                    ->map(fn (Student $student) => [
                        'title' => $student->full_name,
                        'meta' => trim($student->admission_id.' · Class '.$student->schoolClass?->name.' - '.$student->section?->name),
                        'image' => $student->photo_url,
                        'url' => url('student/profile/'.$student->id),
                    ]),
            ];

            $groups[] = [
                'label' => 'Teachers',
                'items' => Teacher::with(['user:id,name,email,avatar', 'department:id,department_name'])
                    ->whereHas('user', fn (Builder $query) => $this->matchEach($query, $terms, ['name', 'email']))
                    ->limit(self::LIMIT)->get()
                    ->map(fn (Teacher $teacher) => [
                        'title' => $teacher->full_name,
                        'meta' => $teacher->department?->department_name ?? 'No department',
                        'image' => $teacher->user?->avatar_url,
                        'url' => $canEdit ? url('teacher/edit/'.$teacher->id) : route('teacher/list/page', ['search' => $teacher->full_name]),
                    ]),
            ];
        }

        if (Gate::allows('manage-users')) {
            $groups[] = [
                'label' => 'Users',
                'items' => User::query()
                    ->tap(fn (Builder $query) => $this->matchEach($query, $terms, ['name', 'email', 'code']))
                    ->orderBy('name')->limit(self::LIMIT)->get()
                    ->map(fn (User $user) => [
                        'title' => $user->name,
                        'meta' => $user->role?->label().' · '.$user->email,
                        'image' => $user->avatar_url,
                        'url' => url('view/user/edit/'.$user->code),
                    ]),
            ];
        }

        return response()->json(['groups' => array_values(array_filter($groups, fn ($group) => $group['items']->isNotEmpty()))]);
    }

    /** Every word must appear in at least one of the columns. */
    private function matchEach(Builder $query, $terms, array $columns): void
    {
        foreach ($terms as $term) {
            $like = '%'.addcslashes($term, '%_\\').'%';
            $query->where(fn (Builder $q) => collect($columns)->each(fn ($column) => $q->orWhere($column, 'like', $like)));
        }
    }
}
