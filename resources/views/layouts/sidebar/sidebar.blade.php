<div class="sidebar d-flex flex-column flex-shrink-0" id="mainSidebar">
    <div class="sidebar-toggle" id="sidebarToggle"><span class="sidebar-toggle-icon"></span></div>
    @include('gingerminds-core::layouts.header.partials.logo')
    <hr>
    <div class="sidebar-menu">
        @include('gingerminds-core::layouts.sidebar.partials.dashboard_menu_items')
    </div>
    <hr>
    <div class="dropdown">
        <a href="#"
           class="d-flex align-items-center text-white text-decoration-none dropdown-toggle sidebar-profile-toggle"
           data-bs-toggle="dropdown" aria-expanded="false">
            @php($user = Auth::user())
            @if($user && $user->contributor && $user->contributor->avatar)
                <img src="{{ $user->contributor->avatar }}"
                     alt="" width="32" height="32"
                     class="rounded-circle me-2">
            @else
                <div class="rounded-circle me-2 sidebar-avatar d-flex align-items-center justify-content-center text-white fw-bold" style="width: 32px; height: 32px; font-size: 12px;">
                    {{ $user ? strtoupper(substr($user->email, 0, 1)) : '?' }}
                </div>
            @endif
            <span class="profile-info">
                <strong class="d-block">{{ $user ? ($user->contributor ? $user->contributor->firstname . ' ' . $user->contributor->lastname : $user->email) : 'Guest' }}</strong>
                @if($user && $user->getRoleNames()->isNotEmpty())
                    <small class="profile-role d-block">{{ $user->getRoleNames()->first() }}</small>
                @endif
            </span>
        </a>
        <ul class="dropdown-menu dropdown-menu-dark text-small shadow">
            <li><a class="dropdown-item" href="{{ route('gingerminds-core.profile.edit-profile') }}">@lang('gingerminds-core::translation.profile.settings')</a></li>
            <li>
                <hr class="dropdown-divider">
            </li>
            <li>
                <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="dropdown-item">
                    @lang('gingerminds-core::translation.action.sign_out')
                </a>
                <form id="logout-form" method="POST" action="{{ route('gingerminds-core.logout') }}" style="display:none;">
                    @csrf
                </form>
        </ul>
    </div>
</div>
