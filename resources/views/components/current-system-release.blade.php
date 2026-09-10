<div>
    @if ($currentSystemRelease)
        @if (Route::has('systemReleasesShow'))
            <a href="{{ route('systemReleasesShow') }}">
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
