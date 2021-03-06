<?php


namespace App\SwiatPrzesylek;


use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Middleware;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Http\Message\ResponseInterface;

/**
 * Class ApiClient
 * @package App\SwiatPrzesylek
 */
class ApiClient
{
    const LOG_FILE_PATH = __DIR__ . '/../../logs/swiat_przesylek.log';
    const LOG_REQUEST_FORMAT = 'REQUEST: {method} {uri} HTTP/{version} {req_body}';
    /*
     * I did not include response body in logs because it contains base64 encoded label.
     * Including it can cause very fast increasing of log file size.
     */
    const LOG_RESPONSE_FORMAT = 'RESPONSE: {code}';
    const BASE_URI = 'https://api.swiatprzesylek.pl';
    const CREATE_PRE_ROUTE = '/V1/courier/create-pre-routing';
    const TRACK_TEST = '/V1/track/test';

    /**
     * @var Client
     */
    private $guzzleHttpClient;

    /**
     * ApiClient constructor.
     */
    public function __construct()
    {
        $this->guzzleHttpClient = new Client([
            'base_uri' => self::BASE_URI,
            'handler' => $this->createLoggingHandlerStack()
        ]);
    }

    /**
     * @param array $packageObjArray
     * @param array $senderAddressObjArray
     * @param array $receiverAddressObjArray
     * @param array|null $optionsPreRoutingObjArray
     * @param array|null $options2ObjArray
     * @param array|null $customsDutyObjArray
     * @return ResponseInterface
     * @throws Exception
     * @throws GuzzleException
     */
    public function courierCreatePreRouteRequest(
        array $packageObjArray,
        array $senderAddressObjArray,
        array $receiverAddressObjArray,
        array $optionsPreRoutingObjArray = null,
        array $options2ObjArray = null,
        array $customsDutyObjArray = null
    ): ResponseInterface
    {
        $bodyArray = [
            'package' => $packageObjArray,
            'sender' => $senderAddressObjArray,
            'receiver' => $receiverAddressObjArray,
        ];

        if (is_array($optionsPreRoutingObjArray)) {
            $bodyArray['options'] = $optionsPreRoutingObjArray;
        }
        if (is_array($options2ObjArray)) {
            $bodyArray['options2'] = $options2ObjArray;
        }
        if (is_array($customsDutyObjArray)) {
            $bodyArray['customs_duty'] = $customsDutyObjArray;
        }

        return $this->guzzleHttpClient->post(self::CREATE_PRE_ROUTE, [
            'auth' => [
                $_ENV['API_LOGIN'],
                $_ENV['API_KEY'],
            ],
            'json' => $bodyArray,
        ]);
    }

    /**
     * @return ResponseInterface
     * @throws Exception
     * @throws GuzzleException
     */
    public function trackTestRequest(): ResponseInterface
    {
        return $this->guzzleHttpClient->post(self::TRACK_TEST, [
            'auth' => [
                $_ENV['API_LOGIN'],
                $_ENV['API_KEY'],
            ],
        ]);
    }

    /**
     * @return HandlerStack
     */
    private function createLoggingHandlerStack(): HandlerStack
    {
        $logger = new Logger('swiat-przesylek-logger');
        $streamHandler = new StreamHandler(self::LOG_FILE_PATH, Logger::DEBUG);
        /*
         * This formatter is set to remove empty square brackets for extra data at the end of each line.
         */
        $formatter = new LineFormatter(null, null, false, true);
        $streamHandler->setFormatter($formatter);
        $logger->pushHandler($streamHandler);

        $stack = HandlerStack::create();
        $stack->push(
            Middleware::log(
                $logger,
                new MessageFormatter(self::LOG_REQUEST_FORMAT)
            )
        );
        $stack->unshift(
            Middleware::log(
                $logger,
                new MessageFormatter(self::LOG_RESPONSE_FORMAT)
            )
        );

        return $stack;
    }
}
