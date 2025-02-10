
<x-app-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">
        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-8">
            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="text-2xl md:text-3xl text-slate-800 font-bold">
                    Devices for {{ $router->name }}
                </h1>
            </div>
        </div>

        <!-- label -->
        <div class="flex flex-row text-xs mb-3">
        </div>

        <!-- Table -->
        {{-- <h1>Interfaces for {{ $router->name }}</h1> --}}

        @if ($devices)
            <table>
                <thead>
                    <tr>
                        <th>IP Address</th>
                        <th>MAC Address</th>
                        <th>Interface</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($devices as $device)
                    <tr>
                        <td>{{ $device['address'] ?? 'N/A' }}</td>
                        <td>{{ $device['mac-address'] ?? 'N/A' }}</td>
                        <td>{{ $device['host-name'] ?? 'N/A' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p>No interfaces found.</p>
        @endif
    </div>

    @section('js-page')
    <script>
    </script>
    @endsection
</x-app-layout>

