<?php

namespace App\Services;

use RouterOS\Client;
use RouterOS\Query;
use Illuminate\Support\Facades\Log;
use PhpParser\Node\Expr\Cast;

class MikrotikApiService
{
    public function connect($host, $username, $password, $port)
    {
        try {
            $client = new Client([
                'host' => $host,
                'user' => $username,
                'pass' => $password,
                'port' => (int) $port, // Change to 8729 for SSL, 8728 for Default
            ]);
            return $client;
        } catch (\Exception $e) {
            // dd($host, $username, $password, $port, $e->getMessage());
            // dd($host,$e->getMessage());
            // return false;
        }
    }

    public function getInterfaces(Client $client)
    {
        if (!$client) {
            return null;
        }

        try {
            $query = new Query('/interface/print');
            $responses = $client->query($query)->read();

            return $responses;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getConnectedDevices($client)
    {
        return $client->query('/ip/arp/print')->read();
    }

    public function getDhcpLeases($client)
    {
        return $client->query('/ip/dhcp-server/lease/print')->read();
        // $leases = $client->query('/ip/dhcp-server/lease/print')->read();
        // return array_slice($leases, 0, 10); // Return only the first 10
    }

    public function getLeaseHistory($client)
    {
        return $client->query('/ip/dhcp-server/lease/print where !active')->read();
    }

    
    public function getFirewallList($client)
    {
        // Send query to RouterOS and parse response
        $response = $client->query('/ip/firewall/address-list/print')->read();
        return $response;
    }

    public function getPPP($client)
    {
        // Send query to RouterOS and parse response
        $response = $client->query('/ppp/active/print')->read();
        return $response;
    }

    public function getPPPSecrets($client)
    {
        // Send query to RouterOS and parse response
        $response = $client->query('/ppp/secret/print')->read();
        return $response;
    }

    public function makeStatic($client, $leaseId)
    {
        try {
            // Set the lease to static
            // $leases = $client->query('/ip/dhcp-server/lease/print')->read();

            // $filteredLeases = array_map(fn($lease) => [
            //     'id' => $lease['.id'] ?? null,
            //     'dynamic' => $lease['dynamic'] ?? null
            // ], $leases);
            // dd($filteredLeases);

            // if (empty($lease)) {
            //     return response()->json(['error' => 'Lease ID not found'], 404);
            // }

            $query = new Query([
                '/ip/dhcp-server/lease/make-static',
                '=.id=' . $leaseId,  // Dynamically set the lease ID
            ]);            

            $response = $client->query($query)
                                ->read();

            return "1";
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function removeStatic($client, $leaseId)
    {
        try {
            // Set the lease to static
            $query = new Query([
                '/ip/dhcp-server/lease/remove',
                '=.id=' . $leaseId,  // Dynamically set the lease ID
            ]);            

            $response = $client->query($query)
                                ->read();
    
            return "1";
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

}
