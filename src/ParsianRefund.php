<?php

namespace HopeOfIran\ParsianRefund;

use Exception;
use GuzzleHttp\Exception\RequestException;
use HopeOfIran\ParsianRefund\Utils\RSAProcessor;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class ParsianRefund
{
    /**
     *  Refund card deposit number, if not filled, will be taken from the original transaction.
     *
     * @var string
     */
    protected $targetCardNumber;
    /**
     * Unique code that is generated by the user of the system and increases numerically by one step (1 to 900000000000000000000)
     *
     * @var int
     */
    protected $refundId;
    /**
     * Bank reference number with which the transaction was successful
     *
     * @var int
     */
    protected $rrn;
    /**
     * refund amount
     *
     * @var int
     */
    protected $amount = 0;
    /**
     * Refund settings
     *
     * @var array
     */
    protected $settings = [];
    /**
     * @var \phpseclib3\Crypt\RSA
     */
    private $CRYPT_RSA;
    /**
     * @var string
     */
    protected $token = '';

    /**
     * @param $settings
     *
     * @throws \Exception
     */
    public function __construct($settings)
    {
        $this->settings  = empty($settings) ? $this->loadDefaultConfig() : $settings;
        $this->refundId  = time();
        $this->CRYPT_RSA = new RSAProcessor($this->settings['certificate'], $this->settings['certificateType']);
    }

    /**
     * Retrieve default config.
     *
     * @return array
     */
    protected function loadDefaultConfig() : array
    {
        return require(static::getDefaultConfigPath());
    }

    /**
     * Retrieve Default config's path.
     *
     * @return string
     */
    public static function getDefaultConfigPath() : string
    {
        return __DIR__.'/config/parsianRefund.php';
    }

    /**
     * @param  string  $targetCardNumber
     *
     * @return $this
     */
    public function targetCardNumber(string $targetCardNumber)
    {
        $this->targetCardNumber = $targetCardNumber;
        return $this;
    }

    /**
     * @param  int  $amount
     *
     * @return $this
     */
    public function amount(int $amount)
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * @param  int  $refundId
     *
     * @return $this
     */
    public function refundId(int $refundId)
    {
        $this->refundId = $refundId;
        return $this;
    }

    /**
     * @param  int  $rrn
     *
     * @return $this
     */
    public function RRN(int $rrn)
    {
        $this->rrn = $rrn;
        return $this;
    }

    /**
     * Set custom configs
     * we can use this method when we want to use dynamic configs
     *
     * @param $key
     * @param $value  |null
     *
     * @return $this
     */
    public function config($key, $value = null)
    {
        $configs = [];
        $key     = is_array($key) ? $key : [$key => $value];
        foreach ($key as $k => $v) {
            $configs[$k] = $v;
        }
        $this->settings = array_merge((array) $this->settings, $configs);
        return $this;
    }

    /**
     * @return \Illuminate\Http\Client\PendingRequest
     */
    protected function httpRequest() : PendingRequest
    {
        $response = Http::baseUrl($this->settings['apiRefundUrl'])
            ->withBasicAuth($this->settings['username'], $this->settings['password'])
            ->timeout($this->settings['http_request']['time_out'])
            ->retry($this->settings['http_request']['retry_times'], $this->settings['http_request']['retry_sleep'], function ($exeption) {
                return !($exeption instanceof RequestException);
            })
            ->asForm();
        if ($this->settings['withoutVerifying'] === 'true') {
            $response->withoutVerifying();
        }
        return $response;
    }

    /**
     * @param  array  $data
     *
     * @return array
     */
    protected function getRequest(array $data)
    {
        $plaintext   = json_encode($data);
        $request     = base64_encode($this->CRYPT_RSA->encrypt($plaintext));
        $requestSign = base64_encode($this->CRYPT_RSA->sign($request));
        return [
            "Request"     => $request,
            "RequestSign" => $requestSign,
        ];
    }

    /**
     * @param  callable  $finalizeCallback
     *
     * @return string
     * @throws \Exception
     */
    public function refund(callable $finalizeCallback = null)
    {
        $response = $this->doRefund();
        if ($finalizeCallback) {
            return call_user_func($finalizeCallback, $this, $response->json());
        }
        return $response;
    }

    /**
     * @param  string  $token
     *
     * @return \Illuminate\Http\Client\Response
     * @throws \Exception
     */
    public function approve(string $token = null)
    {
        if ($token == null) {
            $token = $this->getToken();
        }
        $response = $this->httpRequest()->post("approve", $this->getRequest(["Token" => $token]));
        if ($response->json('Data') == null) {
            throw new Exception($response->json('Message'), $response->json('Status'));
        }
        return $response;
    }

    /**
     * @param  string  $token
     *
     * @return \Illuminate\Http\Client\Response
     * @throws \Exception
     */
    public function cancel(string $token = null)
    {
        if ($token == null) {
            $token = $this->getToken();
        }
        return $this->httpRequest()->post("cancel", $this->getRequest(["Token" => $token]));
    }

    /**
     * @param  string  $token
     *
     * @return \Illuminate\Http\Client\Response
     * @throws \Exception
     */
    public function inquiry(string $token = null)
    {
        if ($token == null) {
            $token = $this->getToken();
        }
        return $this->httpRequest()->post("Inquiry", $this->getRequest(["Token" => $token]));
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getToken()
    {
        if ($this->token == null) {
            $this->doRefund();
        }
        return $this->token;
    }

    /**
     * @return \Illuminate\Http\Client\Response
     * @throws \Exception
     */
    private function doRefund() : Response
    {
        $fields   = $this->getFields();
        $response = $this->httpRequest()->post("doRefund", $this->getRequest($fields));
        if ($response->json('Data') == null) {
            throw new Exception($response->json('Message'), $response->json('Status'));
        }
        $this->token = $response->json('Data')['Token'];

        return $response;
    }

    /**
     * @return array
     * @throws \Exception
     */
    private function getFields() : array
    {
        if ($this->rrn == null) {
            throw new Exception('RRN is required');
        }
        if ($this->refundId == null) {
            throw new Exception('refundId is required');
        }
        $fields = [
            "RefundId" => $this->refundId,
            "RRN"      => $this->rrn,
        ];
        if ($this->amount) {
            $fields['Amount'] = $this->amount;
        }
        if ($this->targetCardNumber) {
            $fields['TargetCardNumber'] = $this->targetCardNumber;
        }
        return $fields;
    }

    /**
     * @return int
     */
    public function getRefundId() : int
    {
        return $this->refundId;
    }
}