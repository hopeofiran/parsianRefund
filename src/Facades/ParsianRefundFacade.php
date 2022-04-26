<?php

namespace HopeOfIran\ParsianRefund\Facades;

use HopeOfIran\ParsianRefund\ParsianRefund;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Facade;

/**
 * Class Payment
 *
 * @package Shetabit\Payment\Facade
 *
 * @method static ParsianRefund targetCardNumber(string $targetCardNumber = null)
 * @method static ParsianRefund refundId(int $refundId)
 * @method static ParsianRefund amount(int $amount)
 * @method static ParsianRefund RRN(int $rrn)
 * @method static ParsianRefund callbackUrl(string $url = null)
 * @method static string getToken(string $token = null)
 * @method static Response refund(callable $finalizeCallback = null)
 * @method static Response approve(string $token = null)
 * @method static Response cancel(string $token = null)
 * @method static Response inquiry(string $token = null)
 *
 */
class ParsianRefundFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    public static function getFacadeAccessor()
    {
        return 'parsian-refund';
    }
}
