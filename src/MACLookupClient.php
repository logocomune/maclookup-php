<?php
declare (strict_types=1);

namespace MACLookup;

use DateTimeImmutable;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use MACLookup\Exceptions\APIKeyException;
use MACLookup\Exceptions\ClientException;
use MACLookup\Exceptions\HTTPRequestException;
use MACLookup\Exceptions\ServerException;
use MACLookup\Exceptions\TooManyRequestException;
use MACLookup\Model\MACInfoModel;
use MACLookup\Model\RateLimitModel;
use MACLookup\Model\ResponseMACInfo;
use MACLookup\Model\ResponseVendorInfo;
use MACLookup\Model\VendorInfoModel;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer;

class MACLookupClient
{
    const MACLOOKUP_API_URL = "https://api.maclookup.app/v2/macs";

    const USER_AGENT = "MACLookupClient-PHP/1.0.0 (https://api.maclookup.app)";
    const NO_COMPANY_NAME = "*NO COMPANY*";
    const COMPANY_NAME_PRIVATE = "*PRIVATE*";

    private $timeout = 5.0;

    private $apiKey;

    private $guzzleClient;

    private $deserializer;

    /**
     * @param string $apiKey API KEY. It's optional.
     * @param float $timeout Timeout is seconds
     * @return static
     */
    public static function getInstance(string $apiKey = "", float $timeout = 5.0): self
    {

        $instance = new self($apiKey, new Client(), new Serializer(
            [new GetSetMethodNormalizer(), new ArrayDenormalizer()],
            [new JsonEncoder()]
        ));

        $instance->timeout = $timeout;

        return $instance;
    }

    /**
     * MACLookupClient constructor.
     * @param string $apiKey
     * @param Client $guzzleClient
     * @param Serializer $serializer
     */
    public function __construct(string $apiKey, Client $guzzleClient, Serializer $serializer)
    {
        $this->apiKey = $apiKey;

        $this->guzzleClient = $guzzleClient;

        $this->deserializer = $serializer;
    }

    /**
     * Get full MAC information
     *
     * @param string $mac MAC string. Must be a valid mac or at least first 6 chars of mac. Valid formats: 00:00:00, 00.00.00, 00-00-00, 000000
     * @return ResponseMACInfo
     * @throws APIKeyException Bad API KEY (API response with HTTP status code 401)
     * @throws ClientException API Response with HTTP status code 4xx (except 401 and 429)
     * @throws HTTPRequestException HTTP request error (network, dns...)
     * @throws ServerException API Response with HTTP status code 5xx
     * @throws TooManyRequestException API Response with HTTP status code 429
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getMacInfo(string $mac): ResponseMACInfo
    {
        $cleanMac = $this->cleanMac($mac);
        if (strlen($cleanMac) < 6) {
            throw ClientException::getInstance("MAC is less than 6 chars");
        }

        $options = $this->requestOptions();

        $start = microtime(true);
        try {
            $response = $this->guzzleClient->request('GET', self::MACLOOKUP_API_URL . "/" . $mac, $options);
        } catch (RequestException  $e) {
            throw HTTPRequestException::getInstance($e->getMessage());
        } catch (Throwable $e) {
            throw HTTPRequestException::getInstance($e->getMessage());
        }
        $reqDuration = (microtime(true) - $start) * 1000;


        $exc = $this->checkRespForException($response);
        if ($exc !== null) {
            throw $exc;
        }

        $rateLimit = $this->getRateLimit($response);

        $json = $response->getBody();

        $macInfo = $this->deserializer->deserialize($json, MACInfoModel::class, 'json');

        if ($macInfo === null) {
            throw ServerException::getInstance();
        }

        return new ResponseMACInfo($macInfo, $rateLimit, $reqDuration);
    }

    /**
     * Get Company Name by MAC
     *
     * @param string $mac MAC string. Must be a valid mac or at least first 6 chars of mac. Valid formats: 00:00:00, 00.00.00, 00-00-00, 000000
     * @return ResponseVendorInfo
     * @throws APIKeyException Bad API KEY (API response with HTTP status code 401)
     * @throws ClientException API Response with HTTP status code 4xx (except 401 and 429)
     * @throws HTTPRequestException HTTP request error (network, dns...)
     * @throws ServerException API Response with HTTP status code 5xx
     * @throws TooManyRequestException API Response with HTTP status code 429
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getCompanyName(string $mac): ResponseVendorInfo
    {
        $cleanMac = $this->cleanMac($mac);
        if (strlen($cleanMac) < 6) {
            throw ClientException::getInstance("MAC is less than 6 chars");
        }

        $options = $this->requestOptions();

        $start = microtime(true);
        try {
            $response = $this->guzzleClient->request(
                'GET',
                self::MACLOOKUP_API_URL . "/" . $mac . "/company/name",
                $options
            );
        } catch (RequestException  $e) {
            throw HTTPRequestException::getInstance($e->getMessage());
        } catch (Throwable $e) {
            throw HTTPRequestException::getInstance($e->getMessage());
        }
        $reqDuration = (microtime(true) - $start) * 1000;


        $exc = $this->checkRespForException($response);
        if ($exc !== null) {
            throw $exc;
        }

        $rateLimit = $this->getRateLimit($response);

        $body = $response->getBody()->getContents();
        $vendorInfo = new VendorInfoModel();
        $vendorInfo->setFound(!($body === "" . self::NO_COMPANY_NAME . ""));
        $vendorInfo->setPrivate(($body === "" . self::COMPANY_NAME_PRIVATE . ""));
        $vendorInfo->setCompany("");
        if ($vendorInfo->isFound() && !$vendorInfo->isPrivate()) {
            $vendorInfo->setCompany($body);
        }

        return new ResponseVendorInfo($vendorInfo, $rateLimit, $reqDuration);
    }

    private function getRateLimit(ResponseInterface $res): RateLimitModel
    {
        $limit = (int)$res->getHeaderLine('X-RateLimit-Limit');
        $remaining = (int)$res->getHeaderLine('X-RateLimit-Remaining');
        $resetEpoch = (int)$res->getHeaderLine('X-RateLimit-Reset');

        return new RateLimitModel($limit, $remaining, new DateTimeImmutable("@$resetEpoch"));
    }

    private function cleanMac(string $mac): string
    {

        if ($mac === null) {
            return "";
        }

        $toReplace = [':', '.', '-', " "];
        $cleanMac = trim($mac);

        $cleanMac = str_replace($toReplace, "", $cleanMac);

        $cleanMac = strtoupper($cleanMac);


        if (strlen($cleanMac) >= 9) {
            return substr($cleanMac, 0, 9);
        }

        if (strlen($cleanMac) >= 7) {
            return substr($cleanMac, 0, 7);
        }

        if (strlen($cleanMac) >= 6) {
            return substr($cleanMac, 0, 6);
        }

        return $cleanMac;
    }

    private function checkRespForException(ResponseInterface $response)
    {


        if ($response === null) {
            //Error
            return ServerException::getInstance();
        }

        $statusCode = $response->getStatusCode();
        switch ($statusCode) {
            case 200:
                return null;
            case 400:
                $body = $response->getBody()->getContents();
                $jsonDecoded = json_decode($body);
                $msg = "";
                if ($jsonDecoded) {
                    $msg = $jsonDecoded->error . " (more info: " . $jsonDecoded->moreInfo . ")";
                }
                return ClientException::getInstance($msg);

            case 401:
                return APIKeyException::getInstance();

            case 429:
                $rateLimit = $this->getRateLimit($response);
                return TooManyRequestException::getInstance($rateLimit);
        }

        if ($statusCode >= 400 && $statusCode < 500) {
            $body = $response->getBody()->getContents();
            $jsonDecoded = json_decode($body);
            $msg = "";
            if ($jsonDecoded) {
                $msg = $jsonDecoded->error . "(more info: " . $jsonDecoded->moreInfo . ")";
            }
            return ClientException::getInstance($msg);
        }

        if ($statusCode >= 500) {
            return ServerException::getInstance();
        }
        return ServerException::getInstance();
    }

    /**
     * @return array
     */
    public function requestOptions(): array
    {
        $query = [];

        if (!empty($this->apiKey)) {
            $query['apiKey'] = $this->apiKey;
        }

        $options = [
            'connect_timeout' => $this->timeout,
            'headers' => [
                'User-Agent' => self::USER_AGENT,
            ],
            'http_errors' => false,
        ];
        if (!empty($query)) {
            $options['query'] = $query;
        }
        return $options;
    }
}
