[![Build Status](https://travis-ci.org/opencafe/validation.svg?branch=master)](https://travis-ci.org/opencafe/validation)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/hopeofiran/parsianrefund/badges/quality-score.png?b=1.0)](https://scrutinizer-ci.com/g/hopeofiran/parsianrefund/?branch=1.0)
[![Latest Stable Version](https://poser.pugx.org/hopeofiran/parsianrefund/v/stable)](https://packagist.org/packages/hopeofiran/parsianrefund)
[![Total Downloads](https://poser.pugx.org/hopeofiran/parsianrefund/downloads)](https://packagist.org/packages/hopeofiran/parsianrefund)
[![License](https://poser.pugx.org/hopeofiran/parsianrefund/license)](https://github.com/hopeofiran/parsianrefund/blob/master/LICENSE.md)

# Laravel Parsian Refund 
Laravel Parsian Refund provides amunt refundtion.

## License
Laravel Persian Validation is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)

## Requirement
* Laravel 6.*
* PHP 5.6-7.3 
## Install

Via Composer

``` bash
$ composer require hopeofiran/parsianrefund
```

## Config

Add the following provider to providers part of config/app.php
``` php
HopeOfIran\ParsianRefund\Providers\ParsianRefundProvider::class
```

## vendor:publish
You can run vendor:publish command to have custom config file of package on this path ( config/parsianRefund.php )
```
php artisan vendor:publish --provider=HopeOfIran\NicardPayment\Providers\NicardPaymentProvider
```

```
return \HopeOfIran\ParsianRefund\Facades\ParsianRefundFacade::amount(1000)
        ->RRN(730157156588)
        ->refundId(187173594849597)->refund(function (HopeOfIran\ParsianRefund\ParsianRefund $parsianRefund) {
            try {
                return $parsianRefund->approve();
            } catch (\Exception $exception) {
                return $exception->getMessage();
            }
        });
```

## Sampel code (cancel)
```
return \HopeOfIran\ParsianRefund\Facades\ParsianRefundFacade::amount(1000)
        ->RRN(730157156588)
        ->refundId(187173594849597)->refund(function (HopeOfIran\ParsianRefund\ParsianRefund $parsianRefund) {
            try {
                return $parsianRefund->cancel();
            } catch (\Exception $exception) {
                return $exception->getMessage();
            }
        });
```