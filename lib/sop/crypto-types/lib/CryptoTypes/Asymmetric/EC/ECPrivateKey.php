<?php

declare(strict_types = 1);

namespace Sop\CryptoTypes\Asymmetric\EC;

use ASN1\Type\UnspecifiedType;
use ASN1\Type\Constructed\Sequence;
use ASN1\Type\Primitive\BitString;
use ASN1\Type\Primitive\Integer;
use ASN1\Type\Primitive\ObjectIdentifier;
use ASN1\Type\Primitive\OctetString;
use ASN1\Type\Tagged\ExplicitlyTaggedType;
use Sop\CryptoEncoding\PEM;
use Sop\CryptoTypes\AlgorithmIdentifier\Asymmetric\ECPublicKeyAlgorithmIdentifier;
use Sop\CryptoTypes\AlgorithmIdentifier\Feature\AlgorithmIdentifierType;
use Sop\CryptoTypes\Asymmetric\PrivateKey;
use Sop\CryptoTypes\Asymmetric\PublicKey;

/**
 * Implements elliptic curve private key type as specified by RFC 5915.
 *
 * @link https://tools.ietf.org/html/rfc5915#section-3
 */
class ECPrivateKey extends PrivateKey
{
    /**
     * Private key.
     *
     * @var string $_privateKey
     */
    protected $_privateKey;
    
    /**
     * Named curve OID.
     *
     * @var string|null $_namedCurve
     */
    protected $_namedCurve;
    
    /**
     * ECPoint value.
     *
     * @var string|null $_publicKey
     */
    protected $_publicKey;
    
    /**
     * Constructor.
     *
     * @param string $private_key Private key
     * @param string|null $named_curve OID of the named curve
     * @param string|null $public_key ECPoint value
     */
    public function __construct(string $private_key, $named_curve = null,
        $public_key = null)
    {
        $this->_privateKey = $private_key;
        $this->_namedCurve = $named_curve;
        $this->_publicKey = $public_key;
    }
    
    /**
     * Initialize from ASN.1.
     *
     * @param Sequence $seq
     * @throws \UnexpectedValueException
     * @return self
     */
    public static function fromASN1(Sequence $seq): ECPrivateKey
    {
        $version = $seq->at(0)
            ->asInteger()
            ->intNumber();
        if (1 != $version) {
            throw new \UnexpectedValueException("Version must be 1.");
        }
        $private_key = $seq->at(1)
            ->asOctetString()
            ->string();
        $named_curve = null;
        if ($seq->hasTagged(0)) {
            $params = $seq->getTagged(0)->asExplicit();
            $named_curve = $params->asObjectIdentifier()->oid();
        }
        $public_key = null;
        if ($seq->hasTagged(1)) {
            $public_key = $seq->getTagged(1)
                ->asExplicit()
                ->asBitString()
                ->string();
        }
        return new self($private_key, $named_curve, $public_key);
    }
    
    /**
     * Initialize from DER data.
     *
     * @param string $data
     * @return self
     */
    public static function fromDER(string $data): ECPrivateKey
    {
        return self::fromASN1(UnspecifiedType::fromDER($data)->asSequence());
    }
    
    /**
     *
     * @see PrivateKey::fromPEM()
     * @param PEM $pem
     * @throws \UnexpectedValueException
     * @return self
     */
    public static function fromPEM(PEM $pem): ECPrivateKey
    {
        $pk = parent::fromPEM($pem);
        if (!($pk instanceof self)) {
            throw new \UnexpectedValueException("Not an EC private key.");
        }
        return $pk;
    }
    
    /**
     * Get the EC private key value.
     *
     * @return string Octets of the private key
     */
    public function privateKeyOctets(): string
    {
        return $this->_privateKey;
    }
    
    /**
     * Whether named curve is present.
     *
     * @return bool
     */
    public function hasNamedCurve(): bool
    {
        return isset($this->_namedCurve);
    }
    
    /**
     * Get named curve OID.
     *
     * @throws \LogicException
     * @return string
     */
    public function namedCurve(): string
    {
        if (!$this->hasNamedCurve()) {
            throw new \LogicException("namedCurve not set.");
        }
        return $this->_namedCurve;
    }
    
    /**
     * Get self with named curve.
     *
     * @param string|null $named_curve Named curve OID
     * @return self
     */
    public function withNamedCurve($named_curve): ECPrivateKey
    {
        $obj = clone $this;
        $obj->_namedCurve = $named_curve;
        return $obj;
    }
    
    /**
     *
     * {@inheritdoc}
     *
     */
    public function algorithmIdentifier(): AlgorithmIdentifierType
    {
        return new ECPublicKeyAlgorithmIdentifier($this->namedCurve());
    }
    
    /**
     * Whether public key is present.
     *
     * @return bool
     */
    public function hasPublicKey(): bool
    {
        return isset($this->_publicKey);
    }
    
    /**
     *
     * {@inheritdoc}
     *
     * @return ECPublicKey
     */
    public function publicKey(): PublicKey
    {
        if (!$this->hasPublicKey()) {
            throw new \LogicException("publicKey not set.");
        }
        return new ECPublicKey($this->_publicKey, $this->namedCurve());
    }
    
    /**
     * Generate ASN.1 structure.
     *
     * @return Sequence
     */
    public function toASN1(): Sequence
    {
        $elements = array(new Integer(1), new OctetString($this->_privateKey));
        if (isset($this->_namedCurve)) {
            $elements[] = new ExplicitlyTaggedType(0,
                new ObjectIdentifier($this->_namedCurve));
        }
        if (isset($this->_publicKey)) {
            $elements[] = new ExplicitlyTaggedType(1,
                new BitString($this->_publicKey));
        }
        return new Sequence(...$elements);
    }
    
    /**
     *
     * {@inheritdoc}
     *
     */
    public function toDER(): string
    {
        return $this->toASN1()->toDER();
    }
    
    /**
     *
     * {@inheritdoc}
     *
     */
    public function toPEM(): PEM
    {
        return new PEM(PEM::TYPE_EC_PRIVATE_KEY, $this->toDER());
    }
}
