<?php
function pingAddress1($address, $timeout = 1) {
    $result = [
        'status' => false,
        'error' => '',
        'timeout' => false,
        'dns_resolved' => false,
    ];

    // Sanitize and prepare the address
    $address = trim($address); // Trim whitespace
    $address = parse_url($address, PHP_URL_HOST) ?: $address; // Extract hostname if URL is provided

    // Check if the address is an IP address
    if (filter_var($address, FILTER_VALIDATE_IP)) {
        $ip = $address; // Use the address directly if it's a valid IP
        $result['dns_resolved'] = true; // Consider direct IP as "resolved"
    } else {
        // Attempt DNS resolution using dns_get_record for domain names
        $dnsRecords = dns_get_record($address, DNS_A);
        if ($dnsRecords) {
            $result['dns_resolved'] = true;
            $ip = $dnsRecords[0]['ip']; // Use the first IP address found
        } else {
            $result['error'] = "DNS resolution failed for $address.";
            return $result;
        }
    }

    // Attempt to open a socket connection
    $socket = @fsockopen($ip, 80, $errno, $errstr, $timeout);
    if ($socket) {
        fclose($socket);
        $result['status'] = true;
    } else {
        $result['error'] = "Unable to connect to $address: $errstr ($errno)";
        if ($errno == 110 || strpos($errstr, 'timed out') !== false) {
            $result['timeout'] = true;
        }
    }

    return $result;
}

// An alternative version that uses a different method for DNS lookups.
function pingAddress2($address, $timeout = 1) {
    // Initialize the result array with default values
    $result = [
        'status' => false,
        'error' => '',
        'timeout' => false,
    ];

    // Convert the address to IP
    $ip = gethostbyname($address);

    // Attempt to open a socket with the specified timeout
    $socket = @fsockopen($ip, 80, $errno, $errstr, $timeout);

    if ($socket) {
        // Connection successful, close socket
        fclose($socket);
        $result['status'] = true;
    } else {
        // Connection failed
        if ($errno == 110 || strpos($errstr, 'timed out') !== false) {
            // Specific handling for timeout errors
            $result['timeout'] = true;
            $result['error'] = "Connection to $address timed out.";
        } else {
            // Handle other errors
            $result['error'] = "Unable to connect to $address: $errstr ($errno)";
        }
    }

    return $result;
}

?>