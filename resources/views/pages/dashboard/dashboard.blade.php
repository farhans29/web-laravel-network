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
                <p>Integrated Networking Solution</p>
            </div>
        
        </div>

        <!-- Dashboard actions -->
        <div class="sm:flex sm:justify-between sm:items-center mb-8">

            <!-- Left: Avatars -->
            {{-- <x-dashboard.dashboard-avatars /> --}}
            <label class="bg-slate-100"></label>

            <!-- Right: Actions -->
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">

            </div>

        </div>
        
        <!-- Cards -->
        {{-- Old Cards
        <div class="grid grid-cols-12 gap-6">
            @foreach ($dataRouter as $router)
                <div class="flex flex-col col-span-full sm:col-span-6 xl:col-span-4 bg-white shadow-lg rounded-sm border border-slate-200">
                    <header class="px-3 py-4 border-b border-slate-100">
                        <h2 class="font-semibold text-slate-800">{{ $router->name }}</h2>
                        <header class="py-1 text-xs uppercase text-slate-400 bg-slate-50 rounded-sm font-semibold p-0.25"></header>
                        <header class="text-xs uppercase text-slate-400 bg-slate-50 rounded-sm font-semibold p-0.25">
                            Serial #:{{ $router->type }} | Model: {{ $router->idrouter }}
                        </header>
                    </header>
                    <div class="p-3">
                        <div class="grow">
                            <ul class="my-1">
                                <header class="px-3 border-b border-slate-100" style="font-size: 0.9rem">Daily</header>
                                <img id="graphImage1-{{ $router->idrouter }}" 
                                    class="object-cover object-center w-full h-full"
                                    src="http://{{ $router->ip }}:{{ $router->web_port }}/graphs/iface/bridge/daily.gif?{{ time() }}" 
                                    alt="Daily Graph">
                            </ul>
                            <ul class="my-1">
                                <header class="px-3 border-b border-slate-100" style="font-size: 0.9rem">Weekly</header>
                                <img id="graphImage2-{{ $router->idrouter }}" 
                                    class="object-cover object-center w-full h-full"
                                    src="http://{{ $router->ip }}:{{ $router->web_port }}/graphs/iface/bridge/weekly.gif?{{ time() }}" 
                                    alt="Weekly Graph">
                            </ul>
                            <!-- Additional Info Boxes -->
                            <div class="border-t border-slate-200 mt-2 pt-2 flex justify-center space-x-2">
                                <div class="w-1/2 flex items-center justify-center text-center align-middle border border-slate-300 py-2 text-xs text-slate-500">
                                    Connected Client: 100
                                </div>
                                <div class="w-1/2 text-center border border-slate-300 py-1 text-xs text-slate-500 flex flex-col">
                                    <div>Transferred: 100</div>
                                    <div>Received: 50</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div> --}}
        {{-- New Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 w-full">
            @foreach ($dataRouter as $router)
            <div class="w-full flex flex-col bg-white shadow-lg rounded-sm border border-slate-200">
                <header class="px-3 py-4 border-b border-slate-100">
                    <h2 class="font-medium text-white text-lg bg-[#7881EB] px-2 py-1 rounded-md">
                        {{ $router->name }}
                    </h2>
                    <header class="text-xs uppercase text-slate-800 bg-indigo-200 rounded-sm p-1">
                        Serial #: {{ $router->idrouter }} | Model: {{ $router->type }}
                    </header>
                </header>
                
                <div class="p-4 flex flex-col sm:flex-row gap-4">
                    <!-- Graph Container -->
                    <div class="flex flex-col sm:w-3/4 w-full">
                        <header class="text-sm border-b font-semibold text-gray-600">Daily</header>
                        <div class="relative">
                            <img id="graphImage1-{{ $router->idrouter }}" 
                                class="object-cover object-center w-full"
                                src="http://{{ $router->ip }}:{{ $router->web_port }}/graphs/iface/bridge/daily.gif" 
                                alt="Daily Graph">
                        </div>
                        <header class="text-sm border-b font-semibold text-gray-600 mt-2">Weekly</header>
                        <div class="relative">
                            <img id="graphImage2-{{ $router->idrouter }}" 
                                class="object-cover object-center w-full"
                                src="http://{{ $router->ip }}:{{ $router->web_port }}/graphs/iface/bridge/weekly.gif" 
                                alt="Weekly Graph">
                        </div>
                    </div>
                    <!-- Info Boxes (Responsive) -->
                    <div class="flex flex-col sm:w-1/4 w-full">
                        <!-- Connected Clients -->
                        <div class="flex flex-col justify-center items-center border border-slate-600 text-white p-3 rounded-lg bg-gray-800"
                            style="height: calc(100% / 2 - 4px);">
                            <div class="text-2xl font-bold">{{ $router->count }}</div>
                            <div class="text-sm pb-2">Devices</div>
                        </div>
        
                        <!-- Transferred Data -->
                        <div class="flex flex-col justify-center items-center border border-slate-600 text-white p-3 rounded-lg mt-2 bg-gray-800"
                            style="height: calc(100% / 2 - 4px);">
                            <div class="text-2xl font-bold">{{ $router->total }}</div>
                            <div class="text-sm pb-2">Transferred</div>
                        </div>
                    </div>                    
                </div>
            </div>
            @endforeach
        </div>        
        
    </div>

    @section('js-page')
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // Select all images with an ID starting with 'graphImage-'
            document.querySelectorAll("img[id^='graphImage1-']").forEach(img => {
                img.onload = function () {
                    setTimeout(() => {
                        if (this.naturalWidth === 118 && this.naturalHeight === 17) { 
                            // Replace with custom image if MikroTik placeholder is detected
                            this.src = "{{ asset('images/no-image-found.png') }}";

                            // Force custom dimensions
                            this.style.width = "500px";  // Set width
                            this.style.height = "170px"; // Set height
                        }
                    }, 300); // Slight delay to ensure image fully loads
                };
                
                // Force reload to trigger onload event properly
                img.src = img.src + "?nocache=" + new Date().getTime();
            });

            document.querySelectorAll("img[id^='graphImage2-']").forEach(img => {
                img.onload = function () {
                    setTimeout(() => {
                        if (this.naturalWidth === 118 && this.naturalHeight === 17) { 
                            // Replace with custom image if MikroTik placeholder is detected
                            this.src = "{{ asset('images/no-image-found.png') }}";
                            
                            // Force custom dimensions
                            this.style.width = "500px";  // Set width
                            this.style.height = "170px"; // Set height
                        }
                    }, 300); // Slight delay to ensure image fully loads
                };
                
                // Force reload to trigger onload event properly
                img.src = img.src + "?nocache=" + new Date().getTime();
            });
        });
    </script>
    @endsection
</x-app-layout>