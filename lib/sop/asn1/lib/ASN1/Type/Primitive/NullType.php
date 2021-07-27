<?php
declare(strict_types = 1);

namespace ASN1\Type\Primitive;

use ASN1\Element;
use ASN1\Component\Identifier;
use ASN1\Component\Length;
use ASN1\Exception\DecodeException;
use ASN1\Feature\ElementBase;
use ASN1\Type\PrimitiveType;
use ASN1\Type\UniversalClass;

/**
 * Implements <i>NULL</i> type.
 */
class NullType extends Element
{
    use UniversalClass;
    use PrimitiveType;
    
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->_typeTag = self::TYPE_NULL;
    }
    
    /**
     *
     * {@inheritdoc}
     */
    protected function _encodedContentDER(): string
    {
        return "";
    }
    
    /**
     *
     * {@inheritdoc}
     * @return self
     */
    protected static function _decodeFromDER(Identifier $identifier,
        string $data, int &$offset): ElementBase
    {
        $idx = $offset;
        if (!$identifier->isPrimitive()) {
            throw new DecodeException("Null value must be primitive.");
        }
        // null type has always zero length
        Length::expectFromDER($data, $idx, 0);
        $offset = $idx;
        return new self();
    }
}
