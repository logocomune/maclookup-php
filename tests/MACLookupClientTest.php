<?php

namespace MACLookup\Test;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use MACLookup\Exceptions\APIKeyException;
use MACLookup\Exceptions\BadMACFormatException;
use MACLookup\Exceptions\ClientException;
use MACLookup\Exceptions\HTTPRequestException;
use MACLookup\Exceptions\ServerException;
use MACLookup\Exceptions\TooManyRequestException;
use MACLookup\MACLookupClient;
use MACLookup\Model\ResponseMACInfo;
use MACLookup\Model\ResponseVendorInfo;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer;

class MACLookupClientTest extends TestCase
{

    public function testGetInstance()
    {
        $macLookupClient = MACLookupClient::getInstance();
        $this->assertNotNull($macLookupClient);
        $this->assertInstanceOf('MACLookup\MACLookupClient', $macLookupClient);
    }

    ///////////////
    /// getMacInfo
    //////////////
    public function testGetMacInfoEmptyMac()
    {
        $this->expectException(ClientException::class);

        $macLookupClient = MACLookupClient::getInstance();
        $macLookupClient->getMacInfo("");
    }

    public function testGetMacInfoTooSmall()
    {
        $this->expectException(ClientException::class);

        $macLookupClient = MACLookupClient::getInstance();

        $macLookupClient->getMacInfo("00000");
    }

    public function testGetMacInfoGuzzleRequestException()
    {
        $this->expectException(HTTPRequestException::class);

        $mock = new MockHandler([

            new RequestException('Error Communicating with Server', new Request('GET', 'test'))
        ]);

        $macClient = $this->getMockClient($mock);
        $macClient->getMacInfo("000000");
    }


    public function testGetMacInfoMalformedMac()
    {
        $this->expectException(ClientException::class);

        $mock = new MockHandler([
            new Response(
                400,
                [
                'X-Ratelimit-Limit' => '2, 2;window=1',
                'X-Ratelimit-Remaining' => '1',
                'X-Ratelimit-Reset' => '1602709444',
                ],
                '{"success":false,"error":"\'000000Z\' has a bad format","errorCode":102,"moreInfo":"https://maclookup.app/api-v2/documentation"}'
            ),
            new RequestException('Error Communicating with Server', new Request('GET', 'test'))
        ]);

        $macClient = $this->getMockClient($mock);
        $macClient->getMacInfo("000000Z");
    }

    public function testGetMacInfoBadApiKey()
    {
        $this->expectException(APIKeyException::class);

        $mock = new MockHandler([
            new Response(
                401,
                [
                'X-Ratelimit-Limit' => '2, 2;window=1',
                'X-Ratelimit-Remaining' => '1',
                'X-Ratelimit-Reset' => '1602709444',
                ],
                '{"success":false,"error":"Unauthorized","errorCode":401,"moreInfo":"https://maclookup.app/api-v2/plans"}'
            ),
            new RequestException('Error Communicating with Server', new Request('GET', 'test'))
        ]);

        $macClient = $this->getMockClient($mock);
        $macClient->getMacInfo("000000");
    }


    public function testGetMacInfoClientException()
    {
        $this->expectException(ClientException::class);

        $mock = new MockHandler([
            new Response(499, [
                'X-Ratelimit-Limit' => '2, 2;window=1',
                'X-Ratelimit-Remaining' => '1',
                'X-Ratelimit-Reset' => '1602709444',
            ], '{"success":false,"error":"Unauthorized","errorCode":409,"moreInfo":"-"}'),
            new RequestException('Error Communicating with Server', new Request('GET', 'test'))
        ]);

        $macClient = $this->getMockClient($mock);
        $macClient->getMacInfo("000000Z");
    }

    public function testGetMacInfoServerException()
    {
        $this->expectException(ServerException::class);

        $mock = new MockHandler([
            new Response(500, [
                'X-Ratelimit-Limit' => '2, 2;window=1',
                'X-Ratelimit-Remaining' => '1',
                'X-Ratelimit-Reset' => '1602709444',
            ], 'OPS'),
            new RequestException('Error Communicating with Server', new Request('GET', 'test'))
        ]);

        $macClient = $this->getMockClient($mock);
        $macClient->getMacInfo("000000Z");
    }


    public function testGetMacInfo()
    {

        // Create a mock and queue two responses.
        $mock = new MockHandler([
            new Response(
                200,
                [
                'X-Ratelimit-Limit' => '2, 2;window=1',
                'X-Ratelimit-Remaining' => '1',
                'X-Ratelimit-Reset' => '1602709444',
                ],
                '{"success":true,"found":true,"macPrefix":"000000","company":"XEROX CORPORATION","address":"M/S 105-50C, WEBSTER NY 14580, US","country":"US","blockStart":"000000000000","blockEnd":"000000FFFFFF","blockSize":16777215,"blockType":"MA-L","updated":"2015-11-17","isRand":false,"isPrivate":false}'
            ),
            new RequestException('Error Communicating with Server', new Request('GET', 'test'))
        ]);

        $macClient = $this->getMockClient($mock);
        $responseMACInfo = $macClient->getMacInfo("000000");

        $this->assertInstanceOf(ResponseMACInfo::class, $responseMACInfo);
        $this->assertNotNull($responseMACInfo->getMacInfo());
        $this->assertTrue($responseMACInfo->getMacInfo()->isSuccess());
        $this->assertTrue($responseMACInfo->getMacInfo()->isFound());
        $this->assertEquals("000000", $responseMACInfo->getMacInfo()->getMacPrefix());
        $this->assertEquals("XEROX CORPORATION", $responseMACInfo->getMacInfo()->getCompany());
        $this->assertEquals("M/S 105-50C, WEBSTER NY 14580, US", $responseMACInfo->getMacInfo()->getAddress());
        $this->assertEquals("000000000000", $responseMACInfo->getMacInfo()->getBlockStart());
        $this->assertEquals("000000FFFFFF", $responseMACInfo->getMacInfo()->getBlockEnd());
        $this->assertEquals(16777215, $responseMACInfo->getMacInfo()->getBlockSize());
        $this->assertEquals("MA-L", $responseMACInfo->getMacInfo()->getBlockType());
        $this->assertEquals("2015-11-17", $responseMACInfo->getMacInfo()->getUpdated());
        $this->assertFalse($responseMACInfo->getMacInfo()->isRand());
        $this->assertFalse($responseMACInfo->getMacInfo()->isPrivate());
    }



    public function testGetMacInfoRateLimitValue()
    {
        // Create a mock and queue two responses.
        $mock = new MockHandler([
            new Response(200, [
                'X-Ratelimit-Limit' => '2, 2;window=1',
                'X-Ratelimit-Remaining' => '1',
                'X-Ratelimit-Reset' => '1602709444',
            ], '{}'),
            new RequestException('Error Communicating with Server', new Request('GET', 'test'))
        ]);

        $macClient = $this->getMockClient($mock);

        $responseMACInfo = $macClient->getMacInfo("000000");
        $rateLimit = $responseMACInfo->getRateLimit();
        $this->assertEquals(2, $rateLimit->getLimit());
        $this->assertEquals(1, $rateLimit->getRemaining());
        $this->assertEquals(new \DateTimeImmutable("@1602709444"), $rateLimit->getReset());
    }

    public function testGetMacInfoRateLimitValueExceeded()
    {
        // Create a mock and queue two responses.
        $mock = new MockHandler([
            new Response(429, [
                'X-Ratelimit-Limit' => '2, 2;window=1',
                'X-Ratelimit-Remaining' => '0',
                'X-Ratelimit-Reset' => '1602709444',
            ], '{}'),
            new RequestException('Error Communicating with Server', new Request('GET', 'test'))
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);
        $macClient = new MACLookupClient("", $client, new Serializer(
            [new GetSetMethodNormalizer(), new ArrayDenormalizer()],
            [new JsonEncoder()]
        ));
        try {
            $responseMACInfo = $macClient->getMacInfo("000000");
        } catch (TooManyRequestException $e) {
            $rateLimit = $e->getRateLimit();
            $this->assertEquals(2, $rateLimit->getLimit());
            $this->assertEquals(0, $rateLimit->getRemaining());
            $this->assertEquals(new \DateTimeImmutable("@1602709444"), $rateLimit->getReset());
            return;
        }

        $this->assertTrue(false);
    }


    ///////////////////
    /// getCompanyName
    ///////////////////
    public function testGetCompanyNameEmptyMac()
    {
        $this->expectException(ClientException::class);

        $macLookupClient = MACLookupClient::getInstance();
        $macLookupClient->getCompanyName("");
    }

    public function testGetCompanyNameTooSmall()
    {
        $this->expectException(ClientException::class);

        $macLookupClient = MACLookupClient::getInstance();

        $macLookupClient->getCompanyName("00000");
    }

    public function testGetCompanyNameGuzzleRequestException()
    {
        $this->expectException(HTTPRequestException::class);

        $mock = new MockHandler([

            new RequestException('Error Communicating with Server', new Request('GET', 'test'))
        ]);

        $macClient = $this->getMockClient($mock);
        $macClient->getCompanyName("000000");
    }


    public function testGetCompanyNameMalformedMac()
    {
        $this->expectException(ClientException::class);

        $mock = new MockHandler([
            new Response(
                400,
                [
                    'X-Ratelimit-Limit' => '2, 2;window=1',
                    'X-Ratelimit-Remaining' => '1',
                    'X-Ratelimit-Reset' => '1602709444',
                ],
                ''
            ),
            new RequestException('Error Communicating with Server', new Request('GET', 'test'))
        ]);

        $macClient = $this->getMockClient($mock);
        $macClient->getCompanyName("000000Z");
    }

    public function testGetCompanyNameBadApiKey()
    {
        $this->expectException(APIKeyException::class);

        $mock = new MockHandler([
            new Response(
                401,
                [
                    'X-Ratelimit-Limit' => '2, 2;window=1',
                    'X-Ratelimit-Remaining' => '1',
                    'X-Ratelimit-Reset' => '1602709444',
                ],
                ''
            ),
            new RequestException('Error Communicating with Server', new Request('GET', 'test'))
        ]);

        $macClient = $this->getMockClient($mock);
        $macClient->getMacInfo("000000");
    }


    public function testGetCompanyNameClientException()
    {
        $this->expectException(ClientException::class);

        $mock = new MockHandler([
            new Response(499, [
                'X-Ratelimit-Limit' => '2, 2;window=1',
                'X-Ratelimit-Remaining' => '1',
                'X-Ratelimit-Reset' => '1602709444',
            ], ''),
            new RequestException('Error Communicating with Server', new Request('GET', 'test'))
        ]);

        $macClient = $this->getMockClient($mock);
        $macClient->getCompanyName("000000");
    }

    public function testGetCompanyNameServerException()
    {
        $this->expectException(ServerException::class);

        $mock = new MockHandler([
            new Response(500, [
                'X-Ratelimit-Limit' => '2, 2;window=1',
                'X-Ratelimit-Remaining' => '1',
                'X-Ratelimit-Reset' => '1602709444',
            ], 'OPS'),
            new RequestException('Error Communicating with Server', new Request('GET', 'test'))
        ]);

        $macClient = $this->getMockClient($mock);
        $macClient->getCompanyName("000000Z");
    }


    public function testGetComapnyName()
    {

        // Create a mock and queue two responses.
        $mock = new MockHandler([
            new Response(
                200,
                [
                    'X-Ratelimit-Limit' => '2, 2;window=1',
                    'X-Ratelimit-Remaining' => '1',
                    'X-Ratelimit-Reset' => '1602709444',
                ],
                'MY COMPANY'
            ),
            new RequestException('Error Communicating with Server', new Request('GET', 'test'))
        ]);

        $macClient = $this->getMockClient($mock);
        $resp = $macClient->getCompanyName("000000");

        $this->assertInstanceOf(ResponseVendorInfo::class, $resp);
        $this->assertNotNull($resp->getVendorInfo());
        $this->assertTrue($resp->getVendorInfo()->isFound());
        $this->assertFalse($resp->getVendorInfo()->isPrivate());
        $this->assertEquals($resp->getVendorInfo()->getCompany(), 'MY COMPANY');
    }

    public function testGetComapnyNameNotFound()
    {

        // Create a mock and queue two responses.
        $mock = new MockHandler([
            new Response(
                200,
                [
                    'X-Ratelimit-Limit' => '2, 2;window=1',
                    'X-Ratelimit-Remaining' => '1',
                    'X-Ratelimit-Reset' => '1602709444',
                ],
                '*NO COMPANY*'
            ),
            new RequestException('Error Communicating with Server', new Request('GET', 'test'))
        ]);

        $macClient = $this->getMockClient($mock);
        $resp = $macClient->getCompanyName("000000");

        $this->assertInstanceOf(ResponseVendorInfo::class, $resp);
        $this->assertNotNull($resp->getVendorInfo());
        $this->assertFalse($resp->getVendorInfo()->isFound());
        $this->assertFalse($resp->getVendorInfo()->isPrivate());
        $this->assertEquals($resp->getVendorInfo()->getCompany(), '');
    }

    public function testGetComapnyNamePrivate()
    {

        // Create a mock and queue two responses.
        $mock = new MockHandler([
            new Response(
                200,
                [
                    'X-Ratelimit-Limit' => '2, 2;window=1',
                    'X-Ratelimit-Remaining' => '1',
                    'X-Ratelimit-Reset' => '1602709444',
                ],
                '*PRIVATE*'
            ),
            new RequestException('Error Communicating with Server', new Request('GET', 'test'))
        ]);

        $macClient = $this->getMockClient($mock);
        $resp = $macClient->getCompanyName("000000");

        $this->assertInstanceOf(ResponseVendorInfo::class, $resp);
        $this->assertNotNull($resp->getVendorInfo());
        $this->assertTrue($resp->getVendorInfo()->isFound());
        $this->assertTrue($resp->getVendorInfo()->isPrivate());
        $this->assertEquals($resp->getVendorInfo()->getCompany(), '');
    }



    public function testGetCompanyNameRateLimitValue()
    {
        // Create a mock and queue two responses.
        $mock = new MockHandler([
            new Response(200, [
                'X-Ratelimit-Limit' => '2, 2;window=1',
                'X-Ratelimit-Remaining' => '1',
                'X-Ratelimit-Reset' => '1602709444',
            ], ''),
            new RequestException('Error Communicating with Server', new Request('GET', 'test'))
        ]);

        $macClient = $this->getMockClient($mock);

        $responseMACInfo = $macClient->getCompanyName("000000");
        $rateLimit = $responseMACInfo->getRateLimit();
        $this->assertEquals(2, $rateLimit->getLimit());
        $this->assertEquals(1, $rateLimit->getRemaining());
        $this->assertEquals(new \DateTimeImmutable("@1602709444"), $rateLimit->getReset());
    }

    public function testGetCompanyNameRateLimitValueExceeded()
    {
        // Create a mock and queue two responses.
        $mock = new MockHandler([
            new Response(429, [
                'X-Ratelimit-Limit' => '2, 2;window=1',
                'X-Ratelimit-Remaining' => '0',
                'X-Ratelimit-Reset' => '1602709444',
            ], ''),
            new RequestException('Error Communicating with Server', new Request('GET', 'test'))
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);
        $macClient = new MACLookupClient("", $client, new Serializer(
            [new GetSetMethodNormalizer(), new ArrayDenormalizer()],
            [new JsonEncoder()]
        ));
        try {
            $macClient->getCompanyName("000000");
        } catch (TooManyRequestException $e) {
            $rateLimit = $e->getRateLimit();
            $this->assertEquals(2, $rateLimit->getLimit());
            $this->assertEquals(0, $rateLimit->getRemaining());
            $this->assertEquals(new \DateTimeImmutable("@1602709444"), $rateLimit->getReset());
            return;
        }

        $this->assertTrue(false);
    }


    private function getMockClient(MockHandler $mock)
    {
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);
        $macClient = new MACLookupClient("", $client, new Serializer(
            [new GetSetMethodNormalizer(), new ArrayDenormalizer()],
            [new JsonEncoder()]
        ));

        return $macClient;
    }
}
