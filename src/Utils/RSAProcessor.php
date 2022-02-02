<?php

namespace HopeOfIran\ParsianRefund\Utils;

use phpseclib3\Crypt\RSA;

class RSAProcessor
{
    public const KEY_TYPE_XML_FILE   = 'xml_file';
    public const KEY_TYPE_XML_STRING = 'xml_string';

    /**
     * @var \phpseclib3\Crypt\RSA\PublicKey $publicKey
     */
    private $publicKey;
    /**
     * @var \phpseclib3\Crypt\RSA\PrivateKey $privateKey
     */
    private $privateKey;

    /**
     * @var false|string
     */
    private $key;

    public function __construct($key, $keyType = null)
    {
        $xmlObject = null;
        $keyType = is_null($keyType) ? null : strtolower($keyType);
        switch ($keyType) {
            case self::KEY_TYPE_XML_FILE:
                $key = file_get_contents($key);
                break;
            case self::KEY_TYPE_XML_STRING:
                $key = strval($key);
                break;
            default:
                throw new \Exception("Undefined key type");
            break;
        }
        $this->key = $key;

        $rsa = RSA::load($key)
            ->withPadding(RSA::ENCRYPTION_PKCS1)
            ->withHash('md5')
            ->withMGFHash('md5');

        $this->publicKey = $rsa->getPublicKey();

        $this->privateKey = $rsa->withPadding(RSA::SIGNATURE_RELAXED_PKCS1);
    }

    /**
     * Encrypt given data
     *
     * @param string $data
     *
     * @return string
     */
    public function encrypt($data)
    {
        return $this->publicKey->encrypt($data);
    }

    /**
     * Sign given data
     *
     * @param  string  $data
     *
     * @return string
     */
    public function sign($data)
    {
        return $this->privateKey->sign($data);
    }
}
