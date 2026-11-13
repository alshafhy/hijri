<li class="nav-item dropdown dropdown-user">
  <a class="nav-link dropdown-toggle dropdown-user-link" id="dropdown-user" href="javascript:void(0);"
    data-bs-toggle="dropdown" aria-haspopup="true">
    <div class="user-nav d-sm-flex d-none">
      <span class="user-name fw-bolder">
        @if (Auth::check())
          {{ Auth::user()->name }}
        @else
          John Doe
        @endif
      </span>
      <span class="user-status">
        @if (Auth::user()?->can_approve_final_request)
          <span class="badge bg-light-info" id="final-approval-badge">{{ __('Delegated for final approval') }}</span>
        @else
          {{ session('current_department_name') ?? ''}}
        @endif
      </span>
    </div>
    <span class="avatar">
      @if (Auth::user()?->avatar_path)
        <img class="round" src="{{ route('dashboard.profile.images.show', [Auth::user(), 'avatar']) }}" alt="avatar" height="40" width="40">
      @else
        <img class="round" src="{{  asset('images/portrait/small/avatar-2.png') }}" alt="avatar" height="40" width="40">
      @endif
      <span class="avatar-status-online"></span>
    </span>
  </a>
  <div class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdown-user">
    <h6 class="dropdown-header">{{__("Manage Profile")}}</h6>
    <div class="dropdown-divider"></div>
    @if (Auth::check())
      <a class="dropdown-item" href="{{ route('dashboard.profile.edit') }}">
        <i class="me-50" data-feather="user"></i> {{ __('My profile') }}
      </a>
    @endif
    <a class="dropdown-item"
      href="{{ Route::has('dashboard.users.change-password') ? route('dashboard.users.change-password') : 'javascript:void(0)' }}">
      <i class="me-50" data-feather="lock"></i> {{__("change-password")}}
    </a>
    @if (Route::has('systemReleasesShow'))
      <a class="dropdown-item" href="{{ route('systemReleasesShow') }}">
        <i class="me-50" data-feather="settings"></i> {{__("system-releases")}}
      </a>
    @endif

    @if (Auth::check())
      <a class="dropdown-item" href="{{ route('logout') }}"
        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
        <i class="me-50" data-feather="power"></i> {{__("Logout")}}
      </a>
      <form method="POST" id="logout-form" action="{{ route('logout') }}">
        @csrf
      </form>
    @else
      <a class="dropdown-item" href="{{ Route::has('login') ? route('login') : 'javascript:void(0)' }}">
        <i class="me-50" data-feather="log-in"></i> Login
      </a>
    @endif
  </div>
</li>
