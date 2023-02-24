<nav class="mt-2">
    <ul class="nav nav-pills nav-sidebar flex-column nav-flat" data-widget="treeview" role="menu" data-accordion="false">
        @foreach ($sidebarMenu as $menu)
            @switch($menu['type'])
                @case('dropdown')
                    <x-sidebar.dropdown :hasPermissions="$menu['hasAnyPermissions']" :isActive="in_array($current, Arr::flatten($menu['items']), true)" :icon="$menu['icon']" :name="$menu['name']">
                        @foreach ($menu['items'] as $submenu)
                            <x-sidebar.link :hasPermissions="$submenu['hasAnyPermissions']" :current="$current" :url="$submenu['url']" :icon="$submenu['icon']" :name="$submenu['name']" />
                        @endforeach
                    </x-sidebar.dropdown>
                @break

                @case('link')
                    <x-sidebar.link :hasPermissions="$menu['hasAnyPermissions']" :current="$current" :url="$menu['url']" :icon="$menu['icon']" :name="$menu['name']" />
                @break
            @endswitch
        @endforeach
    </ul>
</nav>
