<x-app-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">
        
        <!-- Welcome banner -->
        {{-- <x-dashboard.welcome-banner /> --}}

        <div class="relative bg-indigo-200 p-4 sm:p-6 rounded-sm overflow-hidden mb-8">

            <!-- Background illustration -->
            <div class="absolute right-0 top-0 -mt-4 mr-16 pointer-events-none hidden xl:block" aria-hidden="true">
                <svg width="319" height="198" xmlns:xlink="http://www.w3.org/1999/xlink">
                    <defs>
                        <path id="welcome-a" d="M64 0l64 128-64-20-64 20z" />
                        <path id="welcome-e" d="M40 0l40 80-40-12.5L0 80z" />
                        <path id="welcome-g" d="M40 0l40 80-40-12.5L0 80z" />
                        <linearGradient x1="50%" y1="0%" x2="50%" y2="100%" id="welcome-b">
                            <stop stop-color="#A5B4FC" offset="0%" />
                            <stop stop-color="#818CF8" offset="100%" />
                        </linearGradient>
                        <linearGradient x1="50%" y1="24.537%" x2="50%" y2="100%" id="welcome-c">
                            <stop stop-color="#4338CA" offset="0%" />
                            <stop stop-color="#6366F1" stop-opacity="0" offset="100%" />
                        </linearGradient>
                    </defs>
                    <g fill="none" fill-rule="evenodd">
                        <g transform="rotate(64 36.592 105.604)">
                            <mask id="welcome-d" fill="#fff">
                                <use xlink:href="#welcome-a" />
                            </mask>
                            <use fill="url(#welcome-b)" xlink:href="#welcome-a" />
                            <path fill="url(#welcome-c)" mask="url(#welcome-d)" d="M64-24h80v152H64z" />
                        </g>
                        <g transform="rotate(-51 91.324 -105.372)">
                            <mask id="welcome-f" fill="#fff">
                                <use xlink:href="#welcome-e" />
                            </mask>
                            <use fill="url(#welcome-b)" xlink:href="#welcome-e" />
                            <path fill="url(#welcome-c)" mask="url(#welcome-f)" d="M40.333-15.147h50v95h-50z" />
                        </g>
                        <g transform="rotate(44 61.546 392.623)">
                            <mask id="welcome-h" fill="#fff">
                                <use xlink:href="#welcome-g" />
                            </mask>
                            <use fill="url(#welcome-b)" xlink:href="#welcome-g" />
                            <path fill="url(#welcome-c)" mask="url(#welcome-h)" d="M40.333-15.147h50v95h-50z" />
                        </g>
                    </g>
                </svg>
            </div>
        
            <!-- Content -->
            <div class="relative">
                <h1 class="text-2xl md:text-3xl text-slate-800 font-bold mb-1">Welcome to {{ $CRM_ISS->nilai }}, {{ Auth::user()->username }} </h1>
                <p>Responsibility to build Manpower</p>
            </div>
        
        </div>

        <!-- Dashboard actions -->
        <div class="sm:flex sm:justify-between sm:items-center mb-8">

            <!-- Left: Avatars -->
            {{-- <x-dashboard.dashboard-avatars /> --}}
            <label class="bg-slate-100"></label>

            <!-- Right: Actions -->
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">

                <!-- Filter button -->
                {{-- <x-dropdown-filter align="left" /> --}}

                <!-- Datepicker built with flatpickr -->
                {{-- <x-datepicker /> --}}
                {{-- <form class="flex items-center mb-3" id="form-filter">
                    <label class="block text-sm font-medium text-lg mb-1" for="form-search">Select Year For Data Chart :</label>
                    <div class="relative ml-2 w-3/4 md:w-1/4">
                        <select id="form-search" name = "year" class="form-input w-60">
                            <option value="2020" {{ date('Y') == '2020' ? 'selected' : '' }}>2020</option>
                            <option value="2021" {{ date('Y') == '2021' ? 'selected' : '' }}>2021</option>
                            <option value="2022" {{ date('Y') == '2022' ? 'selected' : '' }}>2022</option>
                            <option value="2023" {{ date('Y') == '2023' ? 'selected' : '' }}>2023</option>
                            <option value="2024" {{ date('Y') == '2024' ? 'selected' : '' }}>2024</option>
                            <option value="2025" {{ date('Y') == '2025' ? 'selected' : '' }}>2025</option>
                            <option value="2026" {{ date('Y') == '2026' ? 'selected' : '' }}>2026</option>
                            <option value="2027" {{ date('Y') == '2027' ? 'selected' : '' }}>2027</option>
                            <option value="2028" {{ date('Y') == '2028' ? 'selected' : '' }}>2028</option>
                            <option value="2029" {{ date('Y') == '2029' ? 'selected' : '' }}>2029</option>
                            <option value="2030" {{ date('Y') == '2030' ? 'selected' : '' }}>2030</option>
                            <option value="2031" {{ date('Y') == '2031' ? 'selected' : '' }}>2031</option>
                            <option value="2032" {{ date('Y') == '2032' ? 'selected' : '' }}>2032</option>
                            <option value="2033" {{ date('Y') == '2033' ? 'selected' : '' }}>2033</option>
                            <option value="2034" {{ date('Y') == '2034' ? 'selected' : '' }}>2034</option>
                            <option value="2035" {{ date('Y') == '2035' ? 'selected' : '' }}>2035</option>
                        </select>
                    </div>
                </form> --}}
                

                <!-- Add view button -->
                {{-- <button class="btn bg-indigo-500 hover:bg-indigo-600 text-white">
                    <svg class="w-4 h-4 fill-current opacity-50 shrink-0" viewBox="0 0 16 16">
                        <path d="M15 7H9V1c0-.6-.4-1-1-1S7 .4 7 1v6H1c-.6 0-1 .4-1 1s.4 1 1 1h6v6c0 .6.4 1 1 1s1-.4 1-1V9h6c.6 0 1-.4 1-1s-.4-1-1-1z" />
                    </svg>
                    <span class="hidden xs:block ml-2">Add View</span>
                </button> --}}
                
            </div>

        </div>
        
        <!-- Cards -->
        <div class="grid grid-cols-12 gap-6">
            @foreach ($dataRouter as $router)
                <div class="flex flex-col col-span-full sm:col-span-6 xl:col-span-4 bg-white shadow-lg rounded-sm border border-slate-200">
                    <header class="px-5 py-4 border-b border-slate-100">
                        <h2 class="font-semibold text-slate-800">{{ $router->name }}</h2>
                    </header>
                    <div class="p-3">
                        <div class="grow">
                            <header class="text-xs uppercase text-slate-400 bg-slate-50 rounded-sm font-semibold p-2">
                                {{ $router->idrouter }} - {{ $router->type }}
                            </header>
                            <ul class="my-1">
                                <img class="object-cover object-center w-full h-full"
                                    src="http://{{ $router->ip }}:{{ $router->web_port }}/graphs/iface/bridge/daily.gif?{{ time() }}" 
                                    alt="Daily Graph">
                            </ul>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

    </div>
    @section('js-page')
    <script>
    
    </script>
    @endsection
</x-app-layout>