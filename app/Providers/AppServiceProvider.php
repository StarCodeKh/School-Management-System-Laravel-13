<?php

namespace App\Providers;

use App\Enums\Role;
use App\Models\Menu;
use App\Models\Notice;
use App\Models\User;
use App\Support\TableQuery;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Gate::define('manage-users', fn (User $user) => $user->isAdmin());
        Gate::define('manage-settings', fn (User $user) => $user->isAdmin());
        // academics: admins plan exams and timetables; teachers record attendance and marks
        Gate::define('manage-academics', fn (User $user) => $user->isAdmin());
        Gate::define('teach', fn (User $user) => $user->isAdmin() || $user->hasRole(Role::Teacher));
        Gate::define('manage-finance', fn (User $user) => $user->isAdmin());
        Gate::define('manage-school', fn (User $user) => $user->isAdmin());
        Gate::define('study', fn (User $user) => $user->hasRole(Role::Student));
        Gate::define('guard-children', fn (User $user) => $user->hasRole(Role::Parent));
        Gate::define('manage-communication', fn (User $user) => $user->isAdmin());
        Gate::define('manage-operations', fn (User $user) => $user->isAdmin());

        // only the sidebar needs the menu tree
        View::composer('components.sidebar', fn ($view) => $view->with('menus', Menu::sidebar()));
        View::composer('components.topbar', fn ($view) => $view->with([
            // every page the user can open, for the search box
            'pages' => Menu::sidebar()->flatMap(fn (Menu $menu) => $menu->children->isEmpty()
                ? [['title' => $menu->title, 'section' => null, 'icon' => $menu->icon, 'url' => $menu->url()]]
                : $menu->children->map(fn (Menu $child) => ['title' => $child->title, 'section' => $menu->title, 'icon' => $menu->icon, 'url' => $child->url()]))->values(),
            'notices' => auth()->check()
                ? Notice::visibleTo(auth()->user())->orderByDesc('is_pinned')->latest('published_at')->limit(5)->get(['id', 'title', 'body', 'published_at', 'is_pinned'])
                : collect(),
        ]));

        // ->sortable() and ->paginateFromRequest() for list pages, and the shared pagination design
        TableQuery::register();

        // outside production, fail loudly on N+1 queries (a relation loaded inside a loop)
        Model::preventLazyLoading(! $this->app->isProduction());
        Paginator::defaultView('pagination.table');
    }
}
