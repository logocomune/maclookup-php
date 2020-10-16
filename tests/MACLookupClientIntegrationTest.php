<?php


namespace MacLookup\Tests;

use MACLookup\Exceptions\APIKeyException;
use MACLookup\Exceptions\ClientException;
use MACLookup\Exceptions\HTTPRequestException;
use MACLookup\Exceptions\ServerException;
use MACLookup\Exceptions\TooManyRequestException;
use MACLookup\MACLookupClient;
use PHPUnit\Framework\TestCase;

class MACLookupClientIntegrationTest extends TestCase
{
    /**
     * @group integration
     */
    public function testGetMacInfoEmptyMac()
    {

        $macLookupClient = MACLookupClient::getInstance("");
        try {
            $companyName = $macLookupClient->getCompanyName("000000");
            var_export($companyName);
        } catch (APIKeyException $e) {
            //Bad API Key HTTP status code 401
            echo $e->getMessage();
        } catch (ClientException $e) {
            //Client error  HTTP status code 4xx (no 401 and 429)
            echo $e->getMessage();
        } catch (HTTPRequestException $e) {
            //Network error
            echo $e->getMessage();
        } catch (ServerException $e) {
            //Server error HTTP status code 5xx
            echo $e->getMessage();
        } catch (TooManyRequestException $e) {
            //Too Many Request HTTP status code 429
            echo $e->getMessage();
        }
    }
}
