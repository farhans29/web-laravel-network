
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
                    <div class="relative w-[1000px] h-[350px] bg-gray-50 rounded-lg shadow-md flex items-center justify-center">
                        <canvas id="usageStatsChart" class="w-full h-full"></canvas>
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

        let ctx = document.getElementById("usageStatsChart").getContext("2d");
        let usageChart;
        
        function fetchUsageStats(month = null) {
    let requestUrl = "{{ route('mikrotik.usage-stats.data', ':routerId') }}".replace(':routerId', routerId);

    $.ajax({
        url: requestUrl,
        type: "GET",
        data: { monthInput: month },    
        success: function (response) {
            if (!response || !response.labels || !response.upload || !response.download) {
                console.error("Invalid response format:", response);
                return;
            }

            let selectedDate = month ? new Date(month + "-01") : new Date();
            let year = selectedDate.getFullYear();
            let monthIndex = selectedDate.getMonth() + 1; // Ensure 1-based month (Jan = 1)
            let lastDay = new Date(year, monthIndex, 0).getDate(); // Get last day of the month

            // Generate array of dates (1 - 30/31)
            let dateLabels = Array.from({ length: lastDay }, (_, i) => (i + 1).toString());

            // Initialize dataset arrays with 0 values
            let uploadData = new Array(lastDay).fill(0);
            let downloadData = new Array(lastDay).fill(0);

            // Map the response data into the correct day slots
            response.labels.forEach((dateString, index) => {
                let day = parseInt(dateString.split('-')[2]); // Extract day from "YYYY-MM-DD"
                if (!isNaN(day) && day >= 1 && day <= lastDay) {
                    uploadData[day - 1] = response.upload[index] || 0;
                    downloadData[day - 1] = response.download[index] || 0;
                }
            });

            let canvas = document.getElementById("usageStatsChart");
            if (!canvas) {
                console.error("Canvas element not found");
                return;
            }

            let ctx = canvas.getContext("2d");

            if (usageChart) {
                usageChart.destroy();
            }

            usageChart = new Chart(ctx, {
                type: "bar",
                data: {
                    labels: dateLabels, // Set date labels (1 - 30/31)
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
                    maintainAspectRatio: false, // Allow chart to fit container
                    layout: {
                        padding: 5 // Reduce padding
                    },
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
                                font: { size: 10 }, // Smaller font
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
                        },
                        x: {
                            ticks: {
                                font: { size: 10 }
                            }
                        }
                    }
                }
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

