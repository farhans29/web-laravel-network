
<x-app-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">
        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-8">
            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="text-2xl md:text-3xl text-slate-800 font-bold">
                    Usage Stats for {{ $router->name }}
                    {{-- Usage Stats for {{ 'Router' }} --}}
                    @if(!isset($router) || !$router)
                        <p class="text-red-500">Error: Router not found</p>
                    @endif
                </h1>
            </div>
        </div>

        <!-- label -->
        <div class="flex flex-row text-xs mb-3">
        </div>
        
        <div id="statusContainer" class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-2 mb-4">
        <!-- Status boxes will be dynamically added here -->
        </div>
        
        <!-- Chart -->
        <div class="flex flex-col col-span-full sm:col-span-6 xl:col-span-4 bg-white shadow-lg rounded-sm border border-slate-200">
            <header class="px-5 py-4 border-b border-slate-100">
                <h2 class="font-semibold text-slate-800">{{ 'Router 1 ' }}</h2>
            </header>
            <div class="p-3">
                <!-- Card content -->
                <div id="usage-stats-daily">
                    <form id="monthForm" class="mb-3">
                        <label for="monthInput" class="block text-sm font-medium text-gray-700">Month</label>
                        <input type="month" id="monthInput" name="monthInput" class="mt-1 p-2 border border-gray-300 rounded-md">
                    </form>
                    <!-- Chart container -->
                    {{-- <div class="relative w-[1000px] h-[350px] bg-gray-50 rounded-lg shadow-md flex items-center justify-center">
                        <canvas id="usageStatsChart" class="w-full h-full"></canvas>
                    </div> --}}
                    <div id="chartsContainer" class="space-y-6">
                        <!-- The chart will be provided here -->
                    </div>

                </div>
            </div>
        </div>
    </div>

    @include('components.modal-interface-image')
    @section('js-page')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
    $(document).ready(function () {
            let urlParts = window.location.pathname.split("/");
            let routerId = urlParts[urlParts.length - 1]; // Extract router ID from URL

            function fetchUsageStats(month = null) {
                let requestUrl = "{{ route('mikrotik.usage-stats.data', ':routerId') }}".replace(':routerId', routerId);

                $.ajax({
                    url: requestUrl,
                    type: "GET",
                    data: { monthInput: month },
                    success: function (response) {
                        if (!response || !response.labels || !response.upload || !response.download || !response.int_type) {
                            console.error("Invalid response format:", response);
                            return;
                        }

                        // Clear previous charts
                        $("#chartsContainer").empty();

                        // Generate X-axis labels (30 days back to 7 days ahead)
                        let today = new Date();
                        let startDate = new Date(today);
                        startDate.setDate(startDate.getDate() - 30);
                        let endDate = new Date(today);
                        endDate.setDate(endDate.getDate() + 7);

                        let dateLabels = [];
                        let dateMap = {}; // Store for quick lookup

                        for (let d = new Date(startDate); d <= endDate; d.setDate(d.getDate() + 1)) {
                            let formattedDate = d.toISOString().split("T")[0]; // Format: YYYY-MM-DD
                            dateLabels.push(formattedDate);
                            dateMap[formattedDate] = { upload: 0, download: 0 };
                        }

                        // Get unique interfaces
                        let uniqueInterfaces = [...new Set(response.int_type)];

                        uniqueInterfaces.forEach((interfaceName) => {
                            // Create container
                            let chartDiv = $(`
                                <div class="relative w-[1000px] h-[350px] bg-gray-50 rounded-lg shadow-md p-4">
                                    <h3 class="text-lg font-semibold text-center mb-2">${interfaceName}</h3>
                                    <canvas id="chart_${interfaceName.replace(/[^a-zA-Z0-9]/g, '_')}"></canvas>
                                </div>
                            `);
                            $("#chartsContainer").append(chartDiv);

                            // Filter API data for this interface
                            response.labels.forEach((apiDate, index) => {
                                let formattedDate = apiDate.split(" ")[0]; // Extract only YYYY-MM-DD
                                if (response.int_type[index] === interfaceName && dateMap[formattedDate]) {
                                    dateMap[formattedDate].upload += response.upload[index];
                                    dateMap[formattedDate].download += response.download[index];
                                }
                            });

                            // Extract final dataset
                            let uploadData = dateLabels.map(date => dateMap[date].upload);
                            let downloadData = dateLabels.map(date => dateMap[date].download);

                            // Create chart
                            new Chart(document.getElementById(`chart_${interfaceName.replace(/[^a-zA-Z0-9]/g, '_')}`).getContext("2d"), {
                                type: "bar",
                                data: {
                                    labels: dateLabels,
                                    datasets: [
                                        {
                                            label: "Upload (Bytes)",
                                            data: uploadData,
                                            backgroundColor: "rgba(54, 162, 235, 0.7)",
                                            borderColor: "rgba(54, 162, 235, 1)",
                                            borderWidth: 1
                                        },
                                        {
                                            label: "Download (Bytes)",
                                            data: downloadData,
                                            backgroundColor: "rgba(255, 99, 132, 0.7)",
                                            borderColor: "rgba(255, 99, 132, 1)",
                                            borderWidth: 1
                                        }
                                    ]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    scales: {
                                        x: {
                                            ticks: {
                                                autoSkip: true,
                                                maxTicksLimit: 10 // Show fewer labels to avoid clutter
                                            }
                                        },
                                        y: {
                                            beginAtZero: true,
                                            ticks: {
                                                callback: function (value) {
                                                    if (value < 1024) return value + "B";
                                                    let kb = value / 1024;
                                                    if (kb < 1024) return kb.toFixed(1) + " KB";
                                                    let mb = kb / 1024;
                                                    if (mb < 1024) return mb.toFixed(1) + " MB";
                                                    let gb = mb / 1024;
                                                    return gb.toFixed(1) + " GB";
                                                }
                                            }
                                        }
                                    }
                                }
                            });
                        });
                    },
                    error: function (xhr, status, error) {
                        console.error("Error fetching data:", error);
                    }
                });
            }

            // Initial fetch
            let urlParams = new URLSearchParams(window.location.search);
            let initialMonth = urlParams.get("monthInput") || null;
            fetchUsageStats(initialMonth);

            // Fetch new data when month changes
            $("#monthInput").on("change", function () {
                let selectedMonth = $(this).val();
                fetchUsageStats(selectedMonth);
            });
        });

    </script>

    @endsection
</x-app-layout>

