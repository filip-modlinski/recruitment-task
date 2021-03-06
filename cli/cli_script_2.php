<?php

set_time_limit (0);

require __DIR__ . '/../vendor/autoload.php';

use App\SwiatPrzesylek\ApiClient;
use App\SwiatPrzesylek\ApiHelper;
use Symfony\Component\Dotenv\Dotenv;

$dotenv = new Dotenv();
$dotenv->load(__DIR__.'/../.env');

$apiClient = new ApiClient();
$response = $apiClient->trackTestRequest();
if ($response->getStatusCode() === 200) {
    $responseBody = $response->getBody();
    $responseBodyArray = json_decode($responseBody, true);
    if (key_exists('result', $responseBodyArray) && $responseBodyArray['result'] === 'OK') {
        $response = $responseBodyArray['response'];
    }
}
echo 'INFO: REQUESTING API FINISHED' . PHP_EOL;

/**
 * $emailsTxtPreIds is an array of package ids which email.txt file contains (if exists).
 * This array is used to check whether some id is already present in file and should not be processed again.
 * This can be useful in case when something failed and we need to run script again.
 */
if (file_exists(__DIR__ . '/../emails.txt')) {
    $emailsTxtPre = file(__DIR__ . '/../emails.txt', FILE_SKIP_EMPTY_LINES);
    $emailsTxtPreIds = array_map(function ($line) {
        return explode(';', trim($line))[0];
    }, $emailsTxtPre);
} else {
    $emailsTxtPreIds = [];
}

/**
 * $smsTxtPreIds is an array of package ids which sms.txt file contains (if exists).
 * This array is used to check whether some id is already present in file and should not be processed again.
 * This can be useful in case when something failed and we need to run script again.
 */
if (file_exists(__DIR__ . '/../sms.txt')) {
    $smsTxtPre = file(__DIR__ . '/../sms.txt', FILE_SKIP_EMPTY_LINES);
    $smsTxtPreIds = array_map(function ($line) {
        return explode(';', trim($line))[0];
    }, $smsTxtPre);
} else {
    $smsTxtPreIds = [];
}

$emailsTxt = fopen(__DIR__ . '/../emails.txt', 'a');
$smsTxt = fopen(__DIR__ . '/../sms.txt', 'a');
$nowMinus12Hours = date("Y-m-d H:i:s", strtotime("-12 hours", time()));
foreach ($response['tts'] as $tts) {
    $lastStatus = array_pop($tts['stat_map_history']);
    if ($lastStatus['date'] > $nowMinus12Hours && array_search($tts['id'], $emailsTxtPreIds, true) === false) {
        $statusName = key_exists((int)$lastStatus['id'], ApiHelper::$statusIdNameMap)
            ? ApiHelper::$statusIdNameMap[(int)$lastStatus['id']]
            : 'UNRECOGNIZED';
        $emailsTxtLine = $tts['id'] . ';' . $statusName . ';' . $lastStatus['date'] . PHP_EOL;
        fwrite($emailsTxt, $emailsTxtLine);
        if ($statusName === 'DELIVERED' && key_exists($tts['country_to'], ApiHelper::$countryTimeZoneMap)
            && array_search($tts['id'], $smsTxtPreIds, true) === false
        ) {
            $timeZone = ApiHelper::$countryTimeZoneMap[$tts['country_to']];
            $dateTime = new DateTime($lastStatus['date']);
            $dateTime->setTimezone(new DateTimeZone($timeZone));
            $localTime = $dateTime->format('H:i:s');
            if ($localTime >= ApiHelper::$dayTime['start'] && $localTime <= ApiHelper::$dayTime['end']) {
                $smsTxtLine = $tts['id'] . ';' . $statusName . ';' . $lastStatus['date'] . PHP_EOL;
                fwrite($smsTxt, $smsTxtLine);
            }
        }
    }
}
fclose($emailsTxt);
fclose($smsTxt);
echo 'INFO: emails.txt FILE SAVED' . PHP_EOL;
echo 'INFO: sms.txt FILE SAVED' . PHP_EOL;
echo 'INFO: ALL TASKS FINISHED' . PHP_EOL;
