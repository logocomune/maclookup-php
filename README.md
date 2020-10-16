# MACLookup REST API v2 CLIENT
[![Build Status](https://travis-ci.org/logocomune/maclookup-php.svg?branch=master)](https://travis-ci.org/logocomune/maclookup-php)
[![Go Report Card](https://goreportcard.com/badge/github.com/logocomune/maclookup-php)](https://goreportcard.com/report/github.com/logocomune/maclookup-php)
[![codecov](https://codecov.io/gh/logocomune/maclookup-php/branch/master/graph/badge.svg)](https://codecov.io/gh/logocomune/maclookup-php)


A PHP library for interacting with [MACLookup's API v2](https://maclookup.app/api-v2/documentation). This library allows you to:

- Get full info (MAC prefix, company name, address and country) of a MAC address
- Get Company name by MAC


## Installation

The recommended way to install MACLookup REST API Client is through [Composer](http://getcomposer.org).
```bash
# Install Composer
curl -sS https://getcomposer.org/installer | php
```

Next, run the Composer command to install the latest stable version of My Apps:

```bash
php composer.phar require logocomune/maclookup-php
```

After installing, you need to require Composer's autoloader:

```php
require 'vendor/autoload.php';
```



### Basic Usage

```injectablephp
<?php
use MACLookup\MACLookupClient;

require (__DIR__.'/vendor/autoload.php');
$client = MACLookupClient::getInstance();

try {
    $responseMACInfo = $macLookupClient->getMacInfo("000000");
    var_export($responseMACInfo);
    //   MACLookup\Model\ResponseMACInfo::__set_state(array(
    //       'responseTime' => 204.819917678833,
    //       'rateLimit' =>
    //           MACLookup\Model\RateLimitModel::__set_state(array(
    //               'limit' => 2,
    //               'remainig' => 1,
    //               'reset' =>
    //                   DateTimeImmutable::__set_state(array(
    //                       'date' => '2020-10-16 14:36:38.000000',
    //                       'timezone_type' => 1,
    //                       'timezone' => '+00:00',
    //                   )),
    //           )),
    //       'macInfo' =>
    //           MACLookup\Model\MACInfoModel::__set_state(array(
    //               'success' => true,
    //               'found' => true,
    //               'macPrefix' => '000000',
    //               'company' => 'XEROX CORPORATION',
    //               'address' => 'M/S 105-50C, WEBSTER NY 14580, US',
    //               'country' => 'US',
    //               'blockStart' => '000000000000',
    //               'blockEnd' => '000000FFFFFF',
    //               'blockSize' => 16777215,
    //               'blockType' => 'MA-L',
    //               'updated' => '2015-11-17',
    //               'isRand' => false,
    //               'isPrivate' => false,
    //           )),
    //   ))
} catch (APIKeyException $e) {
    //Bad API Key HTTP status code 401
} catch (ClientException $e) {
    //Client error  HTTP status code 4xx (no 401 and 429)
} catch (HTTPRequestException $e) {
    //Network error
} catch (ServerException $e) {
    //Server error HTTP status code 5xx
} catch (TooManyRequestException $e) {
    //Too Many Request HTTP status code 429
}

try {
    $companyName = $macLookupClient->getCompanyName("000000");
    var_export($companyName);
    //  MACLookup\Model\ResponseVendorInfo::__set_state(array(
    //     'responseTime' => 292.59610176086426,
    //     'rateLimit' => 
    //    MACLookup\Model\RateLimitModel::__set_state(array(
    //       'limit' => 2,
    //       'remainig' => 1,
    //       'reset' => 
    //      DateTimeImmutable::__set_state(array(
    //         'date' => '2020-10-16 14:41:08.000000',
    //         'timezone_type' => 1,
    //         'timezone' => '+00:00',
    //      )),
    //    )),
    //     'vendorInfo' => 
    //    MACLookup\Model\VendorInfoModel::__set_state(array(
    //       'found' => true,
    //       'private' => false,
    //       'company' => 'XEROX CORPORATION',
    //    )),
    //  ))
} catch (APIKeyException $e) {
    //Bad API Key HTTP status code 401
} catch (ClientException $e) {
    //Client error  HTTP status code 4xx (no 401 and 429)
} catch (HTTPRequestException $e) {
    //Network error
} catch (ServerException $e) {
    //Server error HTTP status code 5xx
} catch (TooManyRequestException $e) {
    //Too Many Request HTTP status code 429
}

```
