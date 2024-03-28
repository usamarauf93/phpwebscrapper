<?php

require 'vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use League\Csv\Writer;

// Create a Guzzle client
$client = new Client();

// Specify the URL of the website you want to scrape
$category = 'writing-accessories';
$url = 'https://www.officesupply.com/office-supplies/writing-correction/'.$category.'/c200237.html';

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
        $productNodes = $xpath->query('//div[contains(@class, "product-details")]');

        // Create a new CSV writer
        $csvWriter = Writer::createFromPath($category.'.csv', 'w+');
        // Write the header row including additional fields
        $csvWriter->insertOne(['Name',  'Price', 'Images','Categories']);
        $i = 0;
        // Iterate over the product nodes and extract the desired data
        foreach ($productNodes as $node) {
            // Extract product name

            $productNameNode = $xpath->query('//div[contains(@class,"jx-product-title")]//a')->item($i);
            $productName = $productNameNode ? $productNameNode->textContent : '';

            // Extract price (if available)
            $priceNode = $xpath->query('//div[contains(@class,"cart-btn-container")]//span')->item($i);
            $price = $priceNode ? $priceNode->textContent : '';
            $categoryLoop =  $priceNode ? $category : '';
           
            $imageNode = $xpath->query('//div[contains(@class, "product-img")]//a//img')->item($i);
            $imageUrl = $imageNode ? $imageNode->getAttribute('src') : 'https://placehold.co/400';
      
            // Write all extracted data to the CSV file
            if(!empty($categoryLoop) &&  !empty($productName)  )
                $csvWriter->insertOne([$productName, $price,$imageUrl, $categoryLoop]);
            $i++;
        }
        // Success message
        echo 'Data successfully scraped and saved to '.$category.'.csv';
    } else {
        echo 'Failed to fetch the page. Status code: ' . $response->getStatusCode();
    }
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
