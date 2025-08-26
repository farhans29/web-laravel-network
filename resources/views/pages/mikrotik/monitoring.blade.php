<x-app-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">
        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-8">
            <div class="mb-4 sm:mb-0">
                <h1 class="text-2xl md:text-3xl text-slate-800 font-bold">
                    Traffic Monitor
                </h1>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Router 1 -->
            <div class="flex flex-col space-y-4">
                <!-- Row 1: Live Chart -->
                <div class="bg-white p-4 rounded shadow">
                    <h2 class="text-lg font-semibold mb-2">Router {{ $router1['name'] }}</h2>
                    <canvas id="trafficChart1" height="100"></canvas>
                </div>

                <!-- Row 2: Daily Graph -->
                <div class="bg-white p-4 rounded shadow">
                    <header class="text-sm border-b font-semibold text-gray-600 mb-2">Daily</header>
                    <div class="relative">
                        <img id="graphImage1-{{ $router1['routerId'] }}" 
                            class="object-cover object-center w-full"
                            src="{{ url('mikrotik/proxy-image/' . $router1['routerId'] . '/daily') }}"
                            alt="Daily Graph">
                    </div>
                </div>

                <!-- Row 3: Weekly Graph -->
                <div class="bg-white p-4 rounded shadow">
                    <header class="text-sm border-b font-semibold text-gray-600 mb-2">Weekly</header>
                    <div class="relative">
                        <img id="graphImage2-{{ $router1['routerId'] }}" 
                            class="object-cover object-center w-full"
                            src="{{ url('mikrotik/proxy-image/' . $router1['routerId'] . '/weekly') }}"
                            alt="Weekly Graph">
                    </div>
                </div>
            </div>

            <!-- Router 2 -->
            <div class="flex flex-col space-y-4">
                <!-- Row 1: Live Chart -->
                <div class="bg-white p-4 rounded shadow">
                    <h2 class="text-lg font-semibold mb-2">Router {{ $router2['name'] }}</h2>
                    <canvas id="trafficChart2" height="100"></canvas>
                </div>

                <!-- Row 2: Daily Graph -->
                <div class="bg-white p-4 rounded shadow">
                    <header class="text-sm border-b font-semibold text-gray-600 mb-2">Daily</header>
                    <div class="relative">
                        <img id="graphImage1-{{ $router2['routerId'] }}" 
                            class="object-cover object-center w-full"
                            src="{{ url('mikrotik/proxy-image/' . $router2['routerId'] . '/daily') }}"
                            alt="Daily Graph">
                    </div>
                </div>

                <!-- Row 3: Weekly Graph -->
                <div class="bg-white p-4 rounded shadow">
                    <header class="text-sm border-b font-semibold text-gray-600 mb-2">Weekly</header>
                    <div class="relative">
                        <img id="graphImage2-{{ $router2['routerId'] }}" 
                            class="object-cover object-center w-full"
                            src="{{ url('mikrotik/proxy-image/' . $router2['routerId'] . '/weekly') }}"
                            alt="Weekly Graph">
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const routerId1 = @json($router1['routerId']);
        const routerId2 = @json($router2['routerId']);
        const interface = @json($interface);

        // Function to create a chart instance
        function createTrafficChart(canvasId) {
            const ctx = document.getElementById(canvasId).getContext('2d');
            return new Chart(ctx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [
                        { label: 'RX (bps)', data: [], borderColor: 'blue', fill: false },
                        { label: 'TX (bps)', data: [], borderColor: 'red', fill: false }
                    ]
                },
                options: {
                    animation: false,
                    responsive: true,
                    scales: { y: { beginAtZero: true } }
                }
            });
        }

        const chart1 = createTrafficChart('trafficChart1');
        const chart2 = createTrafficChart('trafficChart2');

        async function fetchTraffic(chart, router, iface) {
            try {
                const res = await fetch(`/mikrotik/monitor/traffic-data/${encodeURIComponent(router)}/${encodeURIComponent(iface)}`);
                if (!res.ok) throw new Error(`HTTP ${res.status}`);
                const data = await res.json();

                if (!data.error) {
                    chart.data.labels.push(data.time);
                    chart.data.datasets[0].data.push(data.rx);
                    chart.data.datasets[1].data.push(data.tx);

                    if (chart.data.labels.length > 10800) {
                        chart.data.labels.shift();
                        chart.data.datasets[0].data.shift();
                        chart.data.datasets[1].data.shift();
                    }

                    chart.update();
                }
            } catch (err) {
                console.error(`Fetch failed for ${router}:`, err);
            }
        }

        // Update both charts every second
        setInterval(() => {
            fetchTraffic(chart1, routerId1, interface);
            fetchTraffic(chart2, routerId2, interface);
        }, 1000);

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
</x-app-layout>
