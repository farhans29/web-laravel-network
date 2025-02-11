<?php

namespace App\Services;

use RouterOS\Client;
use RouterOS\Query;

class MikrotikApiService
{
    public function connect($host, $username, $password)
    {
        try {
            $client = new Client([
                'host' => $host,
                'user' => $username,
                'pass' => $password,
                'port' => 8333, // Change to 8729 for SSL, 8728 for Default
            ]);
            return $client;
        } catch (\Exception $e) {
            return false;
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

}
