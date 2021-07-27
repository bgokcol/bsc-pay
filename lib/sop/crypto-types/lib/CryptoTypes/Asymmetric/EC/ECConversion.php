<?php

declare(strict_types = 1);

namespace Sop\CryptoTypes\Asymmetric\EC;

use ASN1\Type\Primitive\BitString;
use ASN1\Type\Primitive\Integer;
use ASN1\Type\Primitive\OctetString;

/**
 * Implements data type conversions from SEC 1: Elliptic Curve Cryptography.
 *
 * @link http://www.secg.org/sec1-v2.pdf
 */
class ECConversion
{
    /**
     * Perform Bit-String-to-Octet-String Conversion.
     *
     * Defined in SEC 1 section 2.3.1.
     *
     * @param BitString $bs
     * @throws \RuntimeException
     * @return OctetString
     */
    public static function bitStringToOctetString(BitString $bs): OctetString
    {
        $str = $bs->string();
        if ($bs->unusedBits()) {
            // @todo pad string
            throw new \RuntimeException("Unaligned bitstrings to supported");
        }
        return new OctetString($str);
    }
    
    /**
     * Perform Octet-String-to-Bit-String Conversion.
     *
     * Defined in SEC 1 section 2.3.2.
     *
     * @param OctetString $os
     * @return BitString
     */
    public static function octetStringToBitString(OctetString $os): BitString
    {
        return new BitString($os->string());
    }
    
    /**
     * Perform Integer-to-Octet-String Conversion.
     *
     * Defined in SEC 1 section 2.3.7.
     *
     * @param Integer $num
     * @param int|null $mlen Optional desired output length
     * @throws \UnexpectedValueException
     * @return OctetString
     */
    public static function integerToOctetString(Integer $num, $mlen = null): OctetString
    {
        $gmp = gmp_init($num->number(), 10);
        $str = gmp_export($gmp, 1, GMP_MSW_FIRST | GMP_BIG_ENDIAN);
        if (null !== $mlen) {
            $len = strlen($str);
            if ($len > $mlen) {
                throw new \RangeException("Number is too large.");
            }
            // pad with zeroes
            if ($len < $mlen) {
                $str = str_repeat("\0", $mlen - $len) . $str;
            }
        }
        return new OctetString($str);
    }
    
    /**
     * Perform Octet-String-to-Integer Conversion.
     *
     * Defined in SEC 1 section 2.3.8.
     *
     * @param OctetString $os
     * @return Integer
     */
    public static function octetStringToInteger(OctetString $os): Integer
    {
        $num = gmp_import($os->string(), 1, GMP_MSW_FIRST | GMP_BIG_ENDIAN);
        return new Integer(gmp_strval($num, 10));
    }
    
    /**
     * Convert a base-10 number to octets.
     *
     * This is a convenicence method for integer <-> octet string conversion
     * without the need for external ASN.1 dependencies.
     *
     * @param int|string $num Number in base-10
     * @param int|null $mlen Optional desired output length
     * @return string
     */
    public static function numberToOctets($num, $mlen = null): string
    {
        return self::integerToOctetString(new Integer($num), $mlen)->string();
    }
    
    /**
     * Convert octets to a base-10 number.
     *
     * This is a convenicence method for integer <-> octet string conversion
     * without the need for external ASN.1 dependencies.
     *
     * @param string $str
     * @return string Number in base-10
     */
    public static function octetsToNumber(string $str): string
    {
        return self::octetStringToInteger(new OctetString($str))->number();
    }
}
