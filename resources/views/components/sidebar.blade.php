{{-- $menus is provided by the view composer in AppServiceProvider --}}
<div class="flex h-full flex-col">
    <a href="{{ route('home') }}" class="flex h-16 shrink-0 items-center gap-3 border-b border-slate-200 px-6">
        <span class="grid size-9 place-items-center rounded-lg bg-primary-600 text-white">
            <x-icon name="academic-cap" />
        </span>
        <span class="truncate text-sm font-bold text-slate-900">{{ setting('school_name') }}</span>
    </a>

    <nav class="flex-1 overflow-y-auto px-3 py-4">
        <p class="px-3 pb-2 text-xs font-semibold tracking-wider text-slate-400 uppercase">Main Menu</p>
        <ul class="space-y-1">
            @foreach ($menus as $menu)
                @php($active = $menu->isActive())
                @php($children = $menu->children)
                <li x-data="{ open: @js($active) }">
                    @if ($children->isEmpty())
                        <a href="{{ $menu->url() }}" @class([
                            'flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium',
                            'bg-primary-50 text-primary-700' => $active,
                            'text-slate-600 hover:bg-slate-100 hover:text-slate-900' => ! $active,
                        ])>
                            <x-icon :name="$menu->icon" />
                            {{ $menu->title }}
                        </a>
                    @else
                        <button type="button" @click="open = !open" @class([
                            'flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium',
                            'text-primary-700' => $active,
                            'text-slate-600 hover:bg-slate-100 hover:text-slate-900' => ! $active,
                        ])>
                            <x-icon :name="$menu->icon" />
                            <span class="flex-1 text-left">{{ $menu->title }}</span>
                            <x-icon name="chevron-right" @class(['size-4 transition-transform', 'rotate-90' => $active]) ::class="{ 'rotate-90': open }" />
                        </button>
                        <ul x-show="open" x-cloak class="mt-1 space-y-1 border-l border-slate-200 ml-5 pl-3">
                            @foreach ($children as $child)
                                <li>
                                    <a href="{{ $child->url() }}" @class([
                                        'block rounded-md px-3 py-1.5 text-sm',
                                        'bg-primary-50 font-semibold text-primary-700' => $child->isActive(),
                                        'text-slate-500 hover:bg-slate-100 hover:text-slate-900' => ! $child->isActive(),
                                    ])>{{ $child->title }}</a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </li>
            @endforeach
        </ul>
    </nav>
</div>
