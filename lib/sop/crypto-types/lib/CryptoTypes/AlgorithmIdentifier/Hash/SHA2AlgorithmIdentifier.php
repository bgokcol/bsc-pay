<?php

declare(strict_types = 1);

namespace Sop\CryptoTypes\AlgorithmIdentifier\Hash;

use ASN1\Type\UnspecifiedType;
use Sop\CryptoTypes\AlgorithmIdentifier\SpecificAlgorithmIdentifier;
use Sop\CryptoTypes\AlgorithmIdentifier\Feature\HashAlgorithmIdentifier;

/* @formatter:off *//*

From RFC 5754 - 2. Message Digest Algorithms

    The AlgorithmIdentifier parameters field is OPTIONAL.
    Implementations MUST accept SHA2 AlgorithmIdentifiers with absent
    parameters.  Implementations MUST accept SHA2 AlgorithmIdentifiers
    with NULL parameters.  Implementations MUST generate SHA2
    AlgorithmIdentifiers with absent parameters.

*//* @formatter:on */

/**
 * Base class for SHA2 algorithm identifiers.
 *
 * @link https://tools.ietf.org/html/rfc4055#section-2.1
 * @link https://tools.ietf.org/html/rfc5754#section-2
 */
abstract class SHA2AlgorithmIdentifier extends SpecificAlgorithmIdentifier implements 
    HashAlgorithmIdentifier
{
    /**
     * Parameters.
     *
     * @var \ASN1\Type\Primitive\NullType|null $_params
     */
    protected $_params;
    
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->_params = null;
    }
    
    /**
     *
     * @param UnspecifiedType $params
     * @return self
     */
    public static function fromASN1Params(UnspecifiedType $params = null)
    {
        $obj = new static();
        // if parameters field is present, it must be null type
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
