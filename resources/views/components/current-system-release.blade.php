<div>
    @if ($currentSystemRelease)
        @if (Route::has('dashboard.releases'))
            <a href="{{ route('dashboard.releases') }}">
                الاصدار الحالى للنظام
                ({{ $currentSystemRelease }})
            </a>
        @else
            <span class="navbar-text">
                الاصدار الحالى للنظام
                ({{ $currentSystemRelease }})
            </span>
        @endif
    @endif
</div>
