@if (isset($menuData['0']))
    @php
        $sidebarMenu = $menuData['0'];

    @endphp
    @foreach ($sidebarMenu as $menu)
        @php
            $url = isset($menu->submenu) ? 'javascript:;' : $menu->url;
        @endphp
        <li>
            <a href="{{ $url }}" class="{{ isset($menu->submenu) ? 'has-arrow' : '' }}" aria-expanded="false">
                <div class="parent-icon"><i class='bx bx-{{ $menu->icon }}'></i>
                </div>
                <div class="menu-title">{{ $menu->name }}</div>
            </a>
            @if (isset($menu->submenu))
                <ul>
                    @foreach ($menu->submenu as $sMenu)
                        <li> <a href="{{ $sMenu->url }}"><i class="bx bx-right-arrow-alt"></i>{{ $sMenu->name }}</a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </li>
    @endforeach


@endif
