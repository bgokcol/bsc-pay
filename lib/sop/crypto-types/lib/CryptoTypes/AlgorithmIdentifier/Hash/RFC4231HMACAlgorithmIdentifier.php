<?php

declare(strict_types = 1);

namespace Sop\CryptoTypes\AlgorithmIdentifier\Hash;

use ASN1\Type\UnspecifiedType;
use Sop\CryptoTypes\AlgorithmIdentifier\SpecificAlgorithmIdentifier;
use Sop\CryptoTypes\AlgorithmIdentifier\Feature\HashAlgorithmIdentifier;
use Sop\CryptoTypes\AlgorithmIdentifier\Feature\PRFAlgorithmIdentifier;

/**
 * Base class for HMAC algorithm identifiers specified in RFC 4231.
 *
 * @link https://tools.ietf.org/html/rfc4231#section-3.1
 */
abstract class RFC4231HMACAlgorithmIdentifier extends SpecificAlgorithmIdentifier implements 
    HashAlgorithmIdentifier,
    PRFAlgorithmIdentifier
{
    /**
     * Parameters stored for re-encoding.
     *
     * @var \ASN1\Type\Primitive\NullType|null $_params
     */
    protected $_params;
    
    /**
     *
     * @param UnspecifiedType $params
     * @return self
     */
    public static function fromASN1Params(UnspecifiedType $params = null)
    {
        /*
         * RFC 4231 states that the "parameter" component SHOULD be present
         * but have type NULL.
         */
        $obj = new static();
        if (isset($params)) {
            $obj->_params = $params->asNull();
        }
        return $obj;
    }
    
    /**
     *
     * {@inheritdoc}
     *
     * @return \ASN1\Type\Primitive\NullType|null
     */
    protected function _paramsASN1()
    {
        return $this->_params;
    }
}
