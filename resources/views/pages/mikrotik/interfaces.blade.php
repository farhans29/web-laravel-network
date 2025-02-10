@extends('layouts.app')

@section('content')
    <h1>Interfaces for {{ $router->name }}</h1>

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
@endsection
