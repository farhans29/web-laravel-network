
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
        
        <!-- Chart -->
        {{-- <div class="flex flex-col col-span-full sm:col-span-6 xl:col-span-4 bg-white shadow-lg rounded-sm border border-slate-200">
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
                    <div id="chartsContainer" class="space-y-6">
                        <!-- The chart will be provided here -->
                    </div>

                </div>
            </div>
        </div> --}}
        <!-- Chart Container -->
        <div class="flex flex-col col-span-full sm:col-span-6 xl:col-span-4 bg-white shadow-lg rounded-sm border border-slate-200">
            <div class="p-3">
                {{-- <form id="monthForm" class="mb-3">
                    <label for="monthInput" class="block text-sm font-medium text-gray-700">Month</label>
                    <input disabled type="month" id="monthInput" name="monthInput" class="mt-1 p-2 border border-gray-300 rounded-md">
                </form> --}}
                <!-- Container for charts -->
                <div id="chartsContainer" class="space-y-6">
                    <!-- Dynamic charts will be added here -->
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
            let routerId = urlParts[urlParts.length - 1]; 

            function fetchUsageStats(month = null) {
                let requestUrl = "{{ route('mikrotik.usage-stats.data', ':routerId') }}".replace(':routerId', routerId);

                $.ajax({
                    url: requestUrl,
                    type: "GET",
                    data: { monthInput: month },
                    success: function (response) {
                        let chartsContainer = $("#chartsContainer");
                        chartsContainer.empty(); // Clear previous charts
                        
                        let groupedData = {};
                        if (!response || !response.labels || !response.upload || !response.download || !response.int_type) {
                            console.error("Invalid response format:", response);
                            return;
                        }

                        // Get current date and date 30 days ago
                        let currentDate = new Date();
                        let thirtyDaysAgo = new Date();
                        thirtyDaysAgo.setDate(currentDate.getDate() - 30);

                        // Filter data for last 30 days
                        let filteredIndices = response.labels.map((dateString, index) => {
                            let dataDate = new Date(dateString);
                            return dataDate >= thirtyDaysAgo ? index : null;
                        }).filter(index => index !== null);

                        // If no filtered data available
                        if (filteredIndices.length === 0) {
                            chartsContainer.html(`
                                <div class="p-4 bg-gray-100 text-center text-gray-500 rounded-md">
                                    No usage data available for the last 30 days.
                                </div>
                            `);
                            return;
                        }

                        // Group filtered data by interface type
                        filteredIndices.forEach((index) => {
                            let dateString = response.labels[index];
                            let intType = response.int_type[index];
                            if (!groupedData[intType]) {
                                groupedData[intType] = { labels: [], upload: [], download: [] };
                            }
                            groupedData[intType].labels.push(dateString);
                            groupedData[intType].upload.push(response.upload[index] || 0);
                            groupedData[intType].download.push(response.download[index] || 0);
                        });

                        // Loop through each interface and create a chart
                        Object.keys(groupedData).forEach((intType) => {
                            let uniqueId = "usageStatsChart_" + intType.replace(/\s+/g, "_"); 

                            let cardHtml = `
                                <div class="flex flex-col bg-white shadow-lg rounded-sm border border-slate-200">
                                    <header class="px-5 py-4 border-b border-slate-100">
                                        <h2 class="font-semibold text-slate-800">${intType}</h2>
                                    </header>
                                    <div class="p-3">
                                        <div class="relative w-full h-[350px] bg-gray-50 rounded-lg shadow-md flex items-center justify-center">
                                            <canvas id="${uniqueId}" class="w-full h-full"></canvas>
                                        </div>
                                    </div>
                                </div>
                            `;
                            
                            chartsContainer.append(cardHtml);

                            // Create chart for this interface
                            let ctx = document.getElementById(uniqueId).getContext("2d");
                            new Chart(ctx, {
                                type: "bar",
                                data: {
                                    labels: groupedData[intType].labels,
                                    datasets: [
                                        {
                                            label: "Upload (Bytes)",
                                            data: groupedData[intType].upload,
                                            backgroundColor: "rgba(54, 162, 235, 0.7)",
                                            borderColor: "rgba(54, 162, 235, 1)",
                                            borderWidth: 1
                                        },
                                        {
                                            label: "Download (Bytes)",
                                            data: groupedData[intType].download,
                                            backgroundColor: "rgba(255, 99, 132, 0.7)",
                                            borderColor: "rgba(255, 99, 132, 1)",
                                            borderWidth: 1
                                        }
                                    ]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    plugins: {
                                        legend: {
                                            display: true,
                                            position: "top"
                                        }
                                    },
                                    scales: {
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

