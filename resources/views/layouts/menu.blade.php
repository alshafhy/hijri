{{-- Dynamic menu from SystemComponent via MenuComposer ($menuItems) --}}
@foreach ($menuItems ?? [] as $item)
    @php
        $hasChildren = $item->children->isNotEmpty();
        $isOpen = $hasChildren && $item->children->contains(fn ($child) => $child->isCurrentRoute());
        $isActive = $item->isCurrentRoute();
        $icon = $item->icon_name ?: 'circle';
    @endphp

    @if ($hasChildren)
        <li class="nav-item has-sub {{ $isOpen ? 'open' : '' }}">
            <a href="javascript:void(0)" class="d-flex align-items-center">
                <i data-feather="{{ $icon }}"></i>
                <span class="menu-title text-truncate">{{ $item->comp_ar_label }}</span>
            </a>
            <ul class="menu-content">
                @foreach ($item->children as $child)
                    <li class="{{ $child->isCurrentRoute() ? 'active' : '' }}">
                        <a href="{{ $child->getRouteUrl() }}" class="d-flex align-items-center">
                            <i data-feather="{{ $child->icon_name ?: 'circle' }}"></i>
                            <span class="menu-item text-truncate">{{ $child->comp_ar_label }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </li>
    @else
        <li class="nav-item {{ $isActive ? 'active' : '' }}">
            <a href="{{ $item->getRouteUrl() }}" class="d-flex align-items-center">
                <i data-feather="{{ $icon }}"></i>
                <span class="menu-title text-truncate">{{ $item->comp_ar_label }}</span>
            </a>
        </li>
    @endif
@endforeach
