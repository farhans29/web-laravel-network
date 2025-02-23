<div>
    <!-- Sidebar backdrop (mobile only) -->
    <div class="fixed inset-0 bg-slate-900 bg-opacity-30 z-40 lg:hidden lg:z-auto transition-opacity duration-200"
        :class="sidebarOpen ? 'opacity-100' : 'opacity-0 pointer-events-none'" aria-hidden="true" x-cloak></div>

    <!-- Sidebar -->
    <div id="sidebar"
        class="flex flex-col absolute z-40 left-0 top-0 lg:static lg:left-auto lg:top-auto lg:translate-x-0 h-screen overflow-y-scroll lg:overflow-y-auto no-scrollbar w-64 lg:w-20 lg:sidebar-expanded:!w-64 2xl:!w-64 shrink-0 bg-slate-800 px-2 py-3 transition-all duration-200 ease-in-out"
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-64'" @click.outside="sidebarOpen = false"
        @keydown.escape.window="sidebarOpen = false" x-cloak="lg">

        <!-- Sidebar header -->
        <div class="flex justify-between mb-10 pr-3 sm:px-2">
            <!-- Close button -->
            <button class="lg:hidden text-slate-500 hover:text-slate-400" @click.stop="sidebarOpen = !sidebarOpen"
                aria-controls="sidebar" :aria-expanded="sidebarOpen">
                <span class="sr-only">Close sidebar</span>
                <svg class="w-6 h-6 fill-current" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M10.7 18.7l1.4-1.4L7.8 13H20v-2H7.8l4.3-4.3-1.4-1.4L4 12z" />
                </svg>
            </button>
            <!-- Logo -->
            <a class="flex flex-row gap-3 items-center" href="{{ route('dashboard') }}">
                <img src="/images/Logo.png" alt="" class='w-10 h-10'>
                <p class="text-white text-center">{{ $CRM_ISS->nilai }}</p>
            </a>
        </div>

        <!-- Links -->
        <div class="space-y-8">
            <!-- Pages group -->
            <div>
                <h3 class="text-xs uppercase text-slate-500 font-semibold pl-3">
                    <span class="hidden lg:block lg:sidebar-expanded:hidden 2xl:hidden text-center w-6"
                        aria-hidden="true">•••</span>
                    <span class="lg:hidden lg:sidebar-expanded:block 2xl:block">Pages</span>
                </h3>
                <ul class="mt-3">
                    <!-- Dashboard -->
                    <li
                        class="px-3 py-2 rounded-sm mb-0.5 last:mb-0 @if(in_array(Request::segment(1), ['dashboard'])){{ 'bg-slate-900' }}@endif">
                        <a class="block text-slate-200 hover:text-white truncate transition duration-150 @if(in_array(Request::segment(1), ['dashboard'])){{ 'hover:text-slate-200' }}@endif"
                            href="{{ route('dashboard') }}">
                            <div class="flex items-center">
                                <svg class="shrink-0 h-6 w-6" viewBox="0 0 24 24">
                                    <path
                                        class="fill-current @if(in_array(Request::segment(1), ['dashboard'])){{ 'text-indigo-500' }}@else{{ 'text-slate-400' }}@endif"
                                        d="M12 0C5.383 0 0 5.383 0 12s5.383 12 12 12 12-5.383 12-12S18.617 0 12 0z" />
                                    <path
                                        class="fill-current @if(in_array(Request::segment(1), ['dashboard'])){{ 'text-indigo-600' }}@else{{ 'text-slate-600' }}@endif"
                                        d="M12 3c-4.963 0-9 4.037-9 9s4.037 9 9 9 9-4.037 9-9-4.037-9-9-9z" />
                                    <path
                                        class="fill-current @if(in_array(Request::segment(1), ['dashboard'])){{ 'text-indigo-200' }}@else{{ 'text-slate-400' }}@endif"
                                        d="M12 15c-1.654 0-3-1.346-3-3 0-.462.113-.894.3-1.285L6 6l4.714 3.301A2.973 2.973 0 0112 9c1.654 0 3 1.346 3 3s-1.346 3-3 3z" />
                                </svg>
                                <span
                                    class="text-sm font-medium ml-3 lg:opacity-0 lg:sidebar-expanded:opacity-100 2xl:opacity-100 duration-200">Dashboard</span>
                            </div>
                        </a>
                    </li>
                    @foreach ($dataRouters as $router)
                        <li class="px-3 py-2 rounded-sm mb-0.5 last:mb-0 
                            @if (Request::segment(1) === 'mikrotik' && request()->route('routerId') == $router->idrouter) bg-slate-900 @endif"
                            x-data="{ open: {{ (Request::segment(1) === 'mikrotik' && request()->route('routerId') == $router->idrouter) ? 1 : 0 }} }">
                            
                            <!-- Router Name -->
                            <a class="block text-slate-200 hover:text-white truncate transition duration-150"
                                {{-- href="#"
                                @click.prevent="open = !open"> --}}
                                href="{{ route('mikrotik.interfaces', ['routerId' => $router->idrouter]) }}"
                                @click="sidebarExpanded ? open = !open : sidebarExpanded = true">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <svg class="shrink-0 h-6 w-6" viewBox="0 0 24 24">
                                            {{-- @if ($router->is_online) --}}
                                                <!-- Better checkmark icon -->
                                                <svg class="shrink-0 h-6 w-6 text-green-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                            {{-- @else
                                                <!-- Red disconnected icon -->
                                                <svg class="shrink-0 h-6 w-6 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                            @endif --}}
                                        </svg>
                                        <span class="text-sm font-medium ml-2 duration-200">
                                            {{ $router->name }}
                                        </span>
                                    </div>
                                    <!-- Expand Icon -->
                                    <svg class="w-3 h-3 shrink-0 ml-2 fill-current text-slate-400"
                                        :class="open ? 'rotate-180' : 'rotate-0'" viewBox="0 0 12 12">
                                        <path d="M5.9 11.4L.5 6l1.4-1.4 4 4 4-4L11.3 6z" />
                                    </svg>
                                </div>
                            </a>

                            <!-- Submenu (List & New) -->
                            <ul class="pl-9 mt-1 hidden" :class="open ? '!block' : 'hidden'">
                                <li class="mb-1 last:mb-0">
                                    <a class="block text-slate-400 hover:text-slate-200 transition duration-150 truncate 
                                        @if (Route::is('mikrotik.devices') && request()->route('routerId') == $router->idrouter) !text-indigo-500 @endif"
                                        href="{{ route('mikrotik.devices', ['routerId' => $router->idrouter]) }}">
                                        <span class="text-sm font-medium duration-200">Connected Devices</span>
                                    </a>
                                </li>
                                <li class="mb-1 last:mb-0">
                                    <a class="block text-slate-400 hover:text-slate-200 transition duration-150 truncate 
                                        @if (Route::is('mikrotik.usage-stats') && request()->route('routerId') == $router->idrouter) !text-indigo-500 @endif"
                                        href="{{ route('mikrotik.usage-stats', ['routerId' => $router->idrouter]) }}">
                                        <span class="text-sm font-medium duration-200">Usage Statistics</span>  
                                    </a>
                                </li>
                                <li class="mb-1 last:mb-0">
                                    <a class="block text-slate-400 hover:text-slate-200 transition duration-150 truncate 
                                        @if (Route::is('mikrotik.l2tp') && request()->route('routerId') == $router->idrouter) !text-indigo-500 @endif"
                                        href="{{ route('mikrotik.l2tp', ['routerId' => $router->idrouter]) }}">
                                        <span class="text-sm font-medium duration-200">VPN Connected Devices</span>  
                                    </a>
                                </li>
                                <li class="mb-1 last:mb-0">
                                    <a class="block text-slate-400 hover:text-slate-200 transition duration-150 truncate 
                                        @if (Route::is('mikrotik.ppp') && request()->route('routerId') == $router->idrouter) !text-indigo-500 @endif"
                                        href="{{ route('mikrotik.ppp', ['routerId' => $router->idrouter]) }}">
                                        <span class="text-sm font-medium duration-200">VPN Register</span>  
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @endforeach
                    <!-- Support section -->
                    <li class="px-3 py-2 rounded-sm mb-0.5 last:mb-0 @if(in_array(Request::segment(1), ['support'])){{ 'bg-slate-900' }}@endif" 
                        x-data="{ open: {{ in_array(Request::segment(1), ['support']) ? 'true' : 'false' }} }">
                        <a class="block text-slate-200 hover:text-white truncate transition duration-150 @if(in_array(Request::segment(1), ['support'])){{ 'hover:text-slate-200' }}@endif"
                            href="#" 
                            @click.prevent="sidebarExpanded ? open = !open : sidebarExpanded = true">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <svg class="shrink-0 h-6 w-6" viewBox="0 0 24 24">
                                        <path class="fill-current text-slate-700" d="M4.418 19.612A9.092 9.092 0 0 1 2.59 17.03L.475 19.14c-.848.85-.536 2.395.743 3.673a4.413 4.413 0 0 0 1.677 1.082c.253.086.519.131.787.135.45.011.886-.16 1.208-.474L7 21.44a8.962 8.962 0 0 1-2.582-1.828Z"></path>
                                        <path class="fill-current text-slate-600" d="M10.034 13.997a11.011 11.011 0 0 1-2.551-3.862L4.595 13.02a2.513 2.513 0 0 0-.4 2.645 6.668 6.668 0 0 0 1.64 2.532 5.525 5.525 0 0 0 3.643 1.824 2.1 2.1 0 0 0 1.534-.587l2.883-2.882a11.156 11.156 0 0 1-3.861-2.556Z"></path>
                                        <path class="fill-current text-slate-400" d="M21.554 2.471A8.958 8.958 0 0 0 18.167.276a3.105 3.105 0 0 0-3.295.467L9.715 5.888c-1.41 1.408-.665 4.275 1.733 6.668a8.958 8.958 0 0 0 3.387 2.196c.459.157.94.24 1.425.246a2.559 2.559 0 0 0 1.87-.715l5.156-5.146c1.415-1.406.666-4.273-1.732-6.666Zm.318 5.257c-.148.147-.594.2-1.256-.018A7.037 7.037 0 0 1 18.016 6c-1.73-1.728-2.104-3.475-1.73-3.845a.671.671 0 0 1 .465-.129c.27.008.536.057.79.146a7.07 7.07 0 0 1 2.6 1.711c1.73 1.73 2.105 3.472 1.73 3.846Z"></path>
                                    </svg>
                                    <span class="text-sm font-medium ml-3 lg:opacity-0 lg:sidebar-expanded:opacity-100 2xl:opacity-100 duration-200">Support Tickets</span>
                                </div>
                                <div class="flex shrink-0 ml-2 lg:opacity-0 lg:sidebar-expanded:opacity-100 2xl:opacity-100 duration-200">
                                    <svg class="w-3 h-3 shrink-0 ml-1 fill-current text-slate-400"
                                        :class="open ? 'rotate-180' : 'rotate-0'" viewBox="0 0 12 12">
                                        <path d="M5.9 11.4L.5 6l1.4-1.4 4 4 4-4L11.3 6z" />
                                    </svg>
                                </div>
                            </div>
                        </a>
                        <div class="lg:hidden lg:sidebar-expanded:block 2xl:block">
                            <ul class="pl-9 mt-1" 
                                x-show="open" 
                                x-transition:enter="transition-all ease-in-out duration-300"
                                x-transition:enter-start="opacity-0 max-h-0" 
                                x-transition:enter-end="opacity-100 max-h-xl"
                                x-transition:leave="transition-all ease-in-out duration-300"
                                x-transition:leave-start="opacity-100 max-h-xl" 
                                x-transition:leave-end="opacity-0 max-h-0">
                                @php
                                    $userRoleId = auth()->user()->role;
                                    $isAdmin = in_array($userRoleId, [ 100, 101, 999, 888]);
                                @endphp
                                
                                @if($isAdmin)
                                <li class="mb-1 last:mb-0">
                                    <a class="block text-slate-400 hover:text-slate-200 transition duration-150 truncate @if(Route::is('support.tickets.list')){{ '!text-indigo-500' }}@endif"
                                        href="{{ route('support.tickets.list') }}">
                                        <span class="text-sm font-medium lg:opacity-0 lg:sidebar-expanded:opacity-100 2xl:opacity-100 duration-200">All Tickets</span>
                                    </a>
                                </li>
                                @endif
                                
                                <li class="mb-1 last:mb-0">
                                    <a class="block text-slate-400 hover:text-slate-200 transition duration-150 truncate @if(Route::is('support.tickets.my')){{ '!text-indigo-500' }}@endif"
                                        href="{{ route('support.tickets.my') }}">
                                        <span class="text-sm font-medium lg:opacity-0 lg:sidebar-expanded:opacity-100 2xl:opacity-100 duration-200">My Tickets</span>
                                    </a>
                                </li>
                                <li class="mb-1 last:mb-0">
                                    <a class="block text-slate-400 hover:text-slate-200 transition duration-150 truncate @if(Route::is('support.tickets.create')){{ '!text-indigo-500' }}@endif"
                                        href="{{ route('support.tickets.create') }}">
                                        <span class="text-sm font-medium lg:opacity-0 lg:sidebar-expanded:opacity-100 2xl:opacity-100 duration-200">Create Ticket</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Expand / collapse button -->
        <div class="pt-3 hidden lg:inline-flex 2xl:hidden justify-end mt-auto">
            <div class="px-3 py-2">
                <button @click="sidebarExpanded = !sidebarExpanded">
                    <span class="sr-only">Expand / collapse sidebar</span>
                    <svg class="w-6 h-6 fill-current sidebar-expanded:rotate-180" viewBox="0 0 24 24">
                        <path class="text-slate-400"
                            d="M19.586 11l-5-5L16 4.586 23.414 12 16 19.414 14.586 18l5-5H7v-2z" />
                        <path class="text-slate-600" d="M3 23H1V1h2z" />
                    </svg>
                </button>
            </div>
        </div>

    </div>
</div>