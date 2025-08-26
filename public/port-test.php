<?php
$ip = 103.162.146.179;
$port = 8333;

$connection = @fsockopen($ip, $port, $errno, $errstr, 5);

if (is_resource($connection)) {
    echo "✅ Port $port is OPEN on $ip";
    fclose($connection);
} else {
    echo "❌ Port $port is CLOSED on $ip. Error: $errstr ($errno)";
}
