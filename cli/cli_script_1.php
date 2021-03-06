<?php

/**
 * "courier/create-pre-routing is limited to 1 request per 5 seconds. You should find a way how to deal with it."
 * I set time limit for 0 (it means unlimited). Then I used sleep(5) after each request.
 */
set_time_limit (0);

require __DIR__ . '/../vendor/autoload.php';

use App\Helper\CsvLoader;
use App\Helper\ImageManipulator;
use App\SwiatPrzesylek\ApiClient;
use App\SwiatPrzesylek\Objects\AddressObjCreator;
use App\SwiatPrzesylek\Objects\PackageObjCreator;
use Symfony\Component\Dotenv\Dotenv;

$dotenv = new Dotenv();
$dotenv->load(__DIR__.'/../.env');

$addressCsvPath = __DIR__ . '/../source/address.csv';
$dimensionsCsvPath = __DIR__ . '/../source/dimensions.csv';
$addressCsvArray = CsvLoader::getCsvAsArray($addressCsvPath);
$dimensionsCsvArray = CsvLoader::getCsvAsArray($dimensionsCsvPath);
echo 'INFO: LOADING SOURCES FINISHED' . PHP_EOL;

/**
 * $packageIdsTxtPreLines is an array of line numbers which package_ids.txt file contains (if exists).
 * I added line number as the third value after ': ' at the end of each line.
 * It matches line numbers in source files address.csv and dimensions.csv
 * This array is used to check whether some line number is already present in file and should not be processed again.
 * This can be useful in case when something failed and we need to run script again.
 */
if (file_exists(__DIR__ . '/../package_ids.txt')) {
    $packageIdsTxtPre = file(__DIR__ . '/../package_ids.txt', FILE_SKIP_EMPTY_LINES);
    $packageIdsTxtPreLines = array_map(function ($line) {
        return explode(': ', trim($line))[2];
    }, $packageIdsTxtPre);
} else {
    $packageIdsTxtPreLines = [];
}

$apiClient = new ApiClient();

$results = [];
foreach ($addressCsvArray as $lineNumber => $value) {
    $packageObjCreator = new PackageObjCreator($dimensionsCsvArray[$lineNumber]);
    $packageObjArray = $packageObjCreator->getArray();
    $senderAddressObjCreator = new AddressObjCreator('sender', $addressCsvArray[$lineNumber]);
    $senderAddressObjArray = $senderAddressObjCreator->getArray();
    $receiverAddressObjCreator = new AddressObjCreator('receiver', $addressCsvArray[$lineNumber]);
    $receiverAddressObjArray = $receiverAddressObjCreator->getArray();

    if (array_search((string)$lineNumber, $packageIdsTxtPreLines, true) === false) {
        $response = $apiClient->courierCreatePreRouteRequest($packageObjArray, $senderAddressObjArray, $receiverAddressObjArray);
        if ($response->getStatusCode() === 200) {
            $responseBody = $response->getBody();
            $responseBodyArray = json_decode($responseBody, true);
            if (key_exists('result', $responseBodyArray) && $responseBodyArray['result'] === 'OK') {
                $results[$lineNumber] = $responseBodyArray['response'];
            }
        }
        sleep(5);
    }
}
echo 'INFO: REQUESTING API FINISHED' . PHP_EOL;

/**
 * I decided to change date format to Y-m-d (according to ISO 8601).
 * In 'DmY' format 'D' means for example 'Thu', but we have a couple of thursdays every month, so it is not precise.
 * ISO 8601 is more readable and better to keep directories in correct order by date.
 */
$labelsDirName = date('Y-m-d');
$labelsDir = __DIR__ . '/../labels/' . $labelsDirName;
if (!is_dir($labelsDir)) {
    mkdir($labelsDir);
}
echo 'INFO: LABELS DIRECTORY CREATED' . PHP_EOL;

/**
 * I do not check whether any key exists or not.
 * I make an assumption that if response body contains parameter "result" with value "OK",
 * it contains also all parameters described in API documentation.
 *
 * Before running this script you must ensure that you set user's permissions properly.
 * In other case may not be able to save package_ids.txt
 */
$packageIdsTxt = fopen(__DIR__ . '/../package_ids.txt', 'a');
foreach ($results as $lineNumber => $responseContent) {
    foreach ($responseContent['packages'] as $package) {
        if ($package['labels_file_ext'] === 'png') {
            foreach ($package['labels'] as $labelBase64) {
                $randFilename = uniqid('label_', true) . '.' . $package['labels_file_ext'];
                $fullFilename = __DIR__ . '/../labels/' . $labelsDirName . '/' . $randFilename;
                $savingResult = ImageManipulator::saveBase64Image($labelBase64, $fullFilename, -90);
                $filename = $savingResult ? $labelsDirName . '/' . $randFilename : 'FAILED';
                $packageIdsTxtLine = $package['package_id'] . ': ' . $filename . ': ' . $lineNumber . PHP_EOL;
                fwrite($packageIdsTxt, $packageIdsTxtLine);
            }
        } elseif ($package['labels_file_ext'] === 'pdf') {
            /**
             * There is only information about "png" format in task description.
             * In API documentation is written also about 'pdf' format.
             * Here we should place additional code if we want to do something in this case.
             */
        }
    }
}
fclose($packageIdsTxt);
echo 'INFO: SAVING IMAGES FINISHED' . PHP_EOL;

$packageIds = file(__DIR__ . '/../package_ids.txt', FILE_SKIP_EMPTY_LINES);
$labelsFilenames = array_map(function ($line) {
    return __DIR__ . '/../labels/' . trim(explode(': ', $line)[1]);
}, $packageIds);
$labelsFilenames = array_filter($labelsFilenames, function ($filename) {
    return $filename !== 'FAILED';
});
$pdfFilename = __DIR__ . '/../merged_labels.pdf';
$mergingResult = ImageManipulator::mergeImagesToPdf($labelsFilenames, $pdfFilename);
echo 'INFO: MERGING IMAGES TO PDF FINISHED' . PHP_EOL;
echo 'INFO: ALL TASKS FINISHED' . PHP_EOL;
