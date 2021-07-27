<?php

declare(strict_types = 1);

namespace Sop\CryptoTypes\Asymmetric\EC;

use ASN1\Type\Primitive\Integer;
use ASN1\Type\Primitive\OctetString;
use Sop\CryptoEncoding\PEM;
use Sop\CryptoTypes\AlgorithmIdentifier\AlgorithmIdentifier;
use Sop\CryptoTypes\AlgorithmIdentifier\Asymmetric\ECPublicKeyAlgorithmIdentifier;
use Sop\CryptoTypes\AlgorithmIdentifier\Feature\AlgorithmIdentifierType;
use Sop\CryptoTypes\Asymmetric\PublicKey;
use Sop\CryptoTypes\Asymmetric\PublicKeyInfo;

/**
 * Implements elliptic curve public key type as specified by RFC 5480.
 *
 * @link https://tools.ietf.org/html/rfc5480#section-2.2
 */
class ECPublicKey extends PublicKey
{
    /**
     * Elliptic curve public key.
     *
     * @var string
     */
    protected $_ecPoint;
    
    /**
     * Named curve OID.
     *
     * Named curve is not a part of ECPublicKey, but it's stored as a hint
     * for the purpose of PublicKeyInfo creation.
     *
     * @var string|null $_namedCurve
     */
    protected $_namedCurve;
    
    /**
     * Constructor.
     *
     * @param string $ec_point ECPoint
     * @param string|null $named_curve Named curve OID
     * @throws \InvalidArgumentException If ECPoint is invalid
     */
    public function __construct(string $ec_point, $named_curve = null)
    {
        // first octet must be 0x04 for uncompressed form, and 0x02 or 0x03
        // for compressed form.
        if (!strlen($ec_point) || !in_array(ord($ec_point[0]), [2, 3, 4])) {
            throw new \InvalidArgumentException("Invalid ECPoint.");
        }
        $this->_ecPoint = $ec_point;
        $this->_namedCurve = $named_curve;
    }
    
    /**
     * Initialize from curve point coordinates.
     *
     * @param int|string $x X coordinate as a base10 number
     * @param int|string $y Y coordinate as a base10 number
     * @param string|null $named_curve Named curve OID
     * @param int|null $bits Size of <i>p</i> in bits
     * @return self
     */
    public static function fromCoordinates($x, $y, $named_curve = null, $bits = null): ECPublicKey
    {
        // if bitsize is not explicitly set, check from supported curves
        if (!isset($bits) && isset($named_curve)) {
            $bits = self::_curveSize($named_curve);
        }
        $mlen = null;
        if (isset($bits)) {
            $mlen = (int) ceil($bits / 8);
        }
        $x_os = ECConversion::integerToOctetString(new Integer($x), $mlen)->string();
        $y_os = ECConversion::integerToOctetString(new Integer($y), $mlen)->string();
        $ec_point = "\x4$x_os$y_os";
        return new self($ec_point, $named_curve);
    }
    
    /**
     * Get the curve size <i>p</i> in bits.
     *
     * @param string $oid Curve OID
     * @return int|null
     */
    private static function _curveSize(string $oid)
    {
        if (!array_key_exists($oid,
            ECPublicKeyAlgorithmIdentifier::MAP_CURVE_TO_SIZE)) {
            return null;
        }
        return ECPublicKeyAlgorithmIdentifier::MAP_CURVE_TO_SIZE[$oid];
    }
    
    /**
     *
     * @see PublicKey::fromPEM()
     * @param PEM $pem
     * @throws \UnexpectedValueException
     * @return self
     */
    public static function fromPEM(PEM $pem): ECPublicKey
    {
        if (PEM::TYPE_PUBLIC_KEY != $pem->type()) {
            throw new \UnexpectedValueException("Not a public key.");
        }
        $pki = PublicKeyInfo::fromDER($pem->data());
        $algo = $pki->algorithmIdentifier();
        if (AlgorithmIdentifier::OID_EC_PUBLIC_KEY != $algo->oid()) {
            throw new \UnexpectedValueException("Not an elliptic curve key.");
        }
        // ECPoint is directly mapped into public key data
        return new self($pki->publicKeyData(), $algo->namedCurve());
    }
    
    /**
     * Get ECPoint value.
     *
     * @return string
     */
    public function ECPoint(): string
    {
        return $this->_ecPoint;
    }
    
    /**
     * Get curve point coordinates.
     *
     * @return string[] Tuple of X and Y coordinates as base-10 numbers
     */
    public function curvePoint(): array
    {
        return array_map(
            function ($str) {
                return ECConversion::octetsToNumber($str);
            }, $this->curvePointOctets());
    }
    
    /**
     * Get curve point coordinates in octet string representation.
     *
     * @return string[] Tuple of X and Y field elements as a string.
     */
    public function curvePointOctets(): array
    {
        if ($this->isCompressed()) {
            throw new \RuntimeException("EC point compression not supported.");
        }
        $str = substr($this->_ecPoint, 1);
        list($x, $y) = str_split($str, (int) floor(strlen($str) / 2));
        return [$x, $y];
    }
    
    /**
     * Whether ECPoint is in compressed form.
     *
     * @return bool
     */
    public function isCompressed(): bool
    {
        $c = ord($this->_ecPoint[0]);
        return $c != 4;
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
     *
     * {@inheritdoc}
     *
     */
    public function algorithmIdentifier(): AlgorithmIdentifierType
    {
        return new ECPublicKeyAlgorithmIdentifier($this->namedCurve());
    }
    
    /**
     * Generate ASN.1 element.
     *
     * @return OctetString
     */
    public function toASN1(): OctetString
    {
        return new OctetString($this->_ecPoint);
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
     * @link https://tools.ietf.org/html/rfc5480#section-2.2
     */
    public function subjectPublicKeyData(): string
    {
        // ECPoint is directly mapped to subjectPublicKey
        return $this->_ecPoint;
    }
}
