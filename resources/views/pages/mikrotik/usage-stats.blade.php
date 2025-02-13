
<x-app-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">
        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-8">
            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="text-2xl md:text-3xl text-slate-800 font-bold">
                    Interfaces for {{ '$router->name' }}
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

        <!-- Table -->
        {{-- <div class="table-responsive">
            <table id="interfaceTable" class="relative table table-bordered text-xs" style="width:100%">
                <thead>
                    <tr>
                        <th class="text-center">*</th>
                        <th class="text-center">Name</th>
                        <th class="text-center">Mac Address</th>
                        <th class="text-center">RX</th>
                        <th class="text-center">TX</th>
                        <th class="text-center">Running</th>
                        <th class="text-center">Enabled</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    Data wil be inserted here
                </tbody>
            </table>
        </div> --}}

        <div class="flex flex-col col-span-full sm:col-span-6 xl:col-span-4 bg-white shadow-lg rounded-sm border border-slate-200" >
                <header class="px-5 py-4 border-b border-slate-100">
                    <h2 class="font-semibold text-slate-800"> {{ 'Router 1 '}} </h2>
                </header>
                <div class="p-3">
                    <!-- Card content -->
                    <div id="usage-stats-daily">
                        <form id="monthForm">
                            <label for="monthInput">Month</label>
                            <input type="month" id="monthInput" name="monthInput">
                            {{-- <button type="submit"></button> --}}
                        </form>
                        <div class="grow w-[300px] h-[400px]">
                            {{-- <header class="text-xs uppercase text-slate-400 bg-slate-50 rounded-sm font-semibold p-2">{{ 'Router 1' }} </header> --}}
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
                    if (usageChart) {
                        usageChart.destroy();
                    }

                    usageChart = new Chart(ctx, {
                        type: "bar",
                        data: {
                            labels: response.labels,
                            datasets: [
                                {
                                    label: "Upload (Bytes)",
                                    data: response.upload,
                                    backgroundColor: "rgba(54, 162, 235, 0.7)",
                                    borderColor: "rgba(54, 162, 235, 1)",
                                    borderWidth: 1
                                },
                                {
                                    label: "Download (Bytes)",
                                    data: response.download,
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
                                        font: { size: 10 } // Smaller font
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

