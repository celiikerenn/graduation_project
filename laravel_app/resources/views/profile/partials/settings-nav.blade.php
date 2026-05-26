<nav class="settings-subnav" aria-label="Settings sections">
    <a href="{{ route('profile.show') }}"
       class="settings-subnav__link {{ request()->routeIs('profile.show') ? 'settings-subnav__link--active' : '' }}">
        Account
    </a>
    <a href="{{ route('profile.preferences') }}"
       class="settings-subnav__link {{ request()->routeIs('profile.preferences') ? 'settings-subnav__link--active' : '' }}">
        Budget &amp; preferences
    </a>
</nav>
