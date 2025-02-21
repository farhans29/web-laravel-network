
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

                        // Get dates for last 30 days
                        let dates = [];
                        let currentDate = new Date();
                        for (let i = 29; i >= 0; i--) {
                            let date = new Date();
                            date.setDate(currentDate.getDate() - i);
                            dates.push(date.toISOString().split('T')[0]);
                        }

                        // Get unique interface types
                        let uniqueIntTypes = [...new Set(response.int_type)];

                        // Initialize grouped data with all dates for each interface
                        uniqueIntTypes.forEach(intType => {
                            groupedData[intType] = {
                                labels: [...dates],
                                upload: new Array(30).fill(0),
                                download: new Array(30).fill(0)
                            };
                        });

                        // Fill in actual data where it exists
                        response.labels.forEach((dateString, index) => {
                            let intType = response.int_type[index];
                            let dateIndex = dates.indexOf(dateString.split('T')[0]);
                            if (dateIndex !== -1) {
                                groupedData[intType].upload[dateIndex] = response.upload[index] || 0;
                                groupedData[intType].download[dateIndex] = response.download[index] || 0;
                            }
                        });

                        // Create charts for each interface type
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

                            // Format dates for display
                            const formattedLabels = groupedData[intType].labels.map(date => {
                                return new Date(date).toLocaleDateString('en-GB', {
                                    day: '2-digit',
                                    month: 'short'
                                });
                            });

                            // Create chart
                            let ctx = document.getElementById(uniqueId).getContext("2d");
                            new Chart(ctx, {
                                type: "bar",
                                data: {
                                    labels: formattedLabels,
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
                        $("#chartsContainer").html(`
                            <div class="p-4 bg-red-100 text-red-700 rounded-md">
                                Error loading usage statistics: ${error}
                            </div>
                        `);
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

