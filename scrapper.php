<?php
declare(strict_types=1);

require 'vendor/autoload.php';

use Broadcast\BroadcastHandler;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use Message\Task;
use Message\TaskCollection;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;


$log = new Logger('scrapper-flow');
$log->pushHandler(new StreamHandler('app.log', Logger::INFO));

try {
    //Step 1: Initialize the GuzzleHttp Client with Cookie support
    $client = new Client([
        'base_uri' => 'https://app.dataannotation.tech',  // Replace with the target website
        'cookies' => true, // Enables session handling
        'timeout' => 10,    // Timeout for requests
    ]);

    // Step 2: Create a CookieJar to maintain cookies across requests
    $jar = new CookieJar();

    // Step 4: Scrape csrf_token from the form
    $loginUrl = '/users/sign_in';
    $response = $client->get($loginUrl, ['cookies' => $jar]);

    if ($response->getStatusCode() !== 200) {
        throw new Exception('The url ' . $loginUrl . ' returned error: ' . $response->getStatusCode());
    }

    $loginPageHtml = (string)$response->getBody();

    libxml_use_internal_errors(true);
    $doc = new DOMDocument();
    $doc->loadHTML($loginPageHtml);
    $xpath = new DOMXPath($doc);

    $metaTag = $xpath->query('//meta[@name="csrf-token"]');

    if (!$metaTag->length) {
        throw new Exception('CSRF Token unable to parse');
    }

    $csrfToken = $metaTag->item(0)->getAttribute('content');

    print_r("Get CSRF Token: $csrfToken");

    $log->info("Get CSRF Token: $csrfToken");

    // Step 4: Send a POST request to the login form with credentials

    $response = $client->post($loginUrl, [
        'form_params' => [
            'authenticity_token' => $csrfToken,
            'user[email]' => '',  // Replace with the actual form field names and values
            'user[password]' => '',
        ],
        'headers' => [
            'Content-Type' => 'application/x-www-form-urlencoded',  // Standard content type for form submissions
            'Referer' => 'https://app.dataannotation.tech/users/sign_in',  // Optional, helps simulate a real browser
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',  // Optional, simulates browser request
        ],
        'cookies' => $jar,  // Pass the CookieJar to maintain the session
    ]);

    if ($response->getStatusCode() !== 200) {
        throw new Exception('Login Failed with status: ' . $response->getStatusCode());
    }

    // Check if login was successful by analyzing the response
    print_r("Login successful!\n");

    $log->info("Sign in successful!");

    // Step 4: Scrape the protected page (after login)
    $protectedPageResponse = $client->get('/workers/projects', [
        'cookies' => $jar,  // Maintain the session using the same CookieJar
    ]);

    // Get the content of the protected page
    $protectedContent = $protectedPageResponse->getBody()->getContents();

    print_r("Given content: $protectedContent");
    $log->info("Given content: $protectedContent");

    // Step 5: Optionally, parse the content (e.g., using DOMDocument or regex)
    // Example: Load into DOMDocument and parse the HTML
    $dom = new DOMDocument();
    @$dom->loadHTML($protectedContent);
    $xpath = new DOMXPath($dom);

    // Query the div for the data-props attribute
    $dataProps = $xpath->query('//div[@id="workers/WorkerProjectsTable-hybrid-root"]/@data-props')->item(0)->nodeValue;

    // Decode the JSON string (converting HTML entities)
    $dataProps = html_entity_decode($dataProps);
    $jsonData = json_decode($dataProps, true);

    // Extract project information
    $projects = $jsonData['dashboardMerchTargeting']['projects'];

    $availableTasks = new TaskCollection();

    $cacheStorage = new \Cache\FileCache();

    // Loop through projects to extract names, rates, and IDs
    foreach ($projects as $project) {
        $task = ['name' => $project['name'], 'id' => $project['id'], 'pay' => $project['pay']];

        // Extract only payment projects excluding qualifications + notify only about new tasks
        if (strpos(strtoupper($project['name']), "[QUALIFICATION]") === false
            && !in_array($task, $cacheStorage->get('previous_tasks'))) {
            $availableTasks->addTask(new Task($task));
        }
    }

    if(!$availableTasks->isEmpty()){
        BroadcastHandler::createChannel()->sendTasksNotification($availableTasks);

        print_r($availableTasks->getTasks());

        $cacheStorage->save('previous_tasks', $availableTasks->toArray());

        $log->info("Projects info: " . json_encode($availableTasks->getTasks()));
    }else{
        $log->info("No available tasks right now");
    }
} catch (\GuzzleHttp\Exception\GuzzleException|\Exception $e) {
    echo "Error: " . $e->getMessage();

    $log->error("Error: " . $e->getMessage());
}
