
<x-app-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">
        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-8">
            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="text-2xl md:text-3xl text-slate-800 font-bold">
                    Interfaces for {{ $router->name }}
                </h1>
            </div>
        </div>

        <!-- label -->
        <div class="flex flex-row text-xs mb-3">
        </div>

        <!-- Table -->
        {{-- <h1>Interfaces for {{ $router->name }}</h1> --}}

        @if ($interfaces)
            <table border="1">
                <tr>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Status</th>
                </tr>
                @foreach ($interfaces as $interface)
                    <tr>
                        <td>{{ $interface['name'] ?? 'Unknown' }}</td>
                        <td>{{ $interface['type'] ?? 'Unknown' }}</td>
                        <td>{{ isset($interface['running']) && $interface['running'] == 'true' ? 'Running' : 'Stopped' }}</td>
                    </tr>
                @endforeach
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

