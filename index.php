<?php

require 'vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;

// Create a Guzzle client
$client = new Client();

// Specify the URL of the website you want to scrape
$url = 'https://www.officesupply.com/office-supplies/paper-pads/copy-multi-paper/c200213.html';

// Set a user-agent header to mimic a real web browser
$userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/97.0.4692.71 Safari/537.36';

// Set up a cookie jar to handle cookies
$cookieJar = new CookieJar();

// Make a GET request to the website with headers and cookies
try {
    $response = $client->request('GET', $url, [
        'headers' => [
            'User-Agent' => $userAgent,
        ],
        'cookies' => $cookieJar,
    ]);

    // Check if the request was successful
    if ($response->getStatusCode() === 200) {
        // Get the HTML content of the page
        $html = $response->getBody()->getContents();
        
        // Load the HTML content into a DOMDocument
        $dom = new DOMDocument();
        @$dom->loadHTML($html); // Use @ to suppress warnings about invalid HTML

        // Use XPath to locate elements containing product names
        $xpath = new DOMXPath($dom);
        $productNodes = $xpath->query('//div[contains(@class, "jx-product-title")]//a');

        // Open a CSV file for writing
        $file = fopen('products.csv', 'w');

        // Write the header row
        fputcsv($file, ['Product Name']);

        // Iterate over the product nodes and extract the product names
        foreach ($productNodes as $node) {
            $productName = $node->textContent;
            // Write each product name to the CSV file
            fputcsv($file, [$productName]);
        }

        // Close the CSV file
        fclose($file);

        // Success message
        echo 'Data successfully scraped and saved to products.csv';
    } else {
        echo 'Failed to fetch the page. Status code: ' . $response->getStatusCode();
    }
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
