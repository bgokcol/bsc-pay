<?php

declare(strict_types = 1);

namespace Sop\CryptoTypes\Signature;

use ASN1\Type\Primitive\BitString;

/**
 * Implements RSA signature value.
 *
 * @todo Implement signature parsing
 * @link https://tools.ietf.org/html/rfc2313#section-10
 */
class RSASignature extends Signature
{
    /**
     * Signature value <i>S</i>.
     *
     * @var string
     */
    private $_signature;
    
    /**
     * Constructor.
     */
    protected function __construct()
    {
    }
    
    /**
     * Initialize from RSA signature <i>S</i>.
     *
     * Signature value <i>S</i> is the result of last step in RSA signature
     * process defined in PKCS #1.
     *
     * @link https://tools.ietf.org/html/rfc2313#section-10.1.4
     * @param string $signature Signature bits
     * @return self
     */
    public static function fromSignatureString(string $signature): Signature
    {
        $obj = new self();
        $obj->_signature = strval($signature);
        return $obj;
    }
    
    /**
     *
     * {@inheritdoc}
     */
    public function bitString(): BitString
    {
        return new BitString($this->_signature);
    }
}
