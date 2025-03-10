<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class TrafficCollectorTest extends TestCase
{
       use DatabaseTransactions; // Rolls back DB changes after each test

    /** @test */
    public function it_stores_traffic_data_successfully()
    {
        // Simulate the request data
        $data = [
            'idr' => 25,
            'intp' => 'eth01',
            'tx' => 989898,
            'rx' => 101010,
            'dt' => now()->format('Y-m-d H:i:s'),
            'key' => 'MrqIl2o*x$?b8CT',
        ];

        // Make a GET request to the endpoint
        $response = $this->getJson('/traffic-collector?' . http_build_query($data));

        // Assert that the response is successful
        $response->assertStatus(200)
                 ->assertJson(['message' => 'Data received successfully']);

        // Verify data was inserted into the database
        $this->assertDatabaseHas('t_traffic_logs', [
            'idrouter' => $data['idr'],
            'int_type' => $data['intp'],
            'tx_bytes' => $data['tx'],
            'rx_bytes' => $data['rx'],
            'datetime' => $data['dt'],
        ]);
    }

    /** @test */
    public function it_returns_validation_errors_for_missing_data()
    {
        // Make a GET request with missing parameters
        $response = $this->getJson('/traffic-collector');

        // Assert that validation fails (status code 422)
        $response->assertStatus(422)
                 ->assertJsonStructure(['error']);
    }
}
