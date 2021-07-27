<?php
use Sop\CryptoTypes\Asymmetric\EC\ECPublicKey;
use Sop\CryptoTypes\Asymmetric\EC\ECPrivateKey;
use Sop\CryptoEncoding\PEM;
use kornrunner\Keccak;

isset($_CONFIG) or die;

$payment_amount = floatval(get_post('payment_amount'));

$payment_amount_str = bc_number_format($payment_amount, $_CONFIG['precision']);

$expires_in_hour = 5;

if($payment_amount > 0) {
    require './lib/autoload.php';

    $res = openssl_pkey_new([
        'private_key_type' => OPENSSL_KEYTYPE_EC,
        'curve_name' => 'secp256k1'
    ]);
    
    if (!$res) {
        json_response([
            'success' => false,
            'message' => 'Private key could not be generated!'
        ]);
    }
    
    openssl_pkey_export($res, $priv_key);
    
    $key_detail = openssl_pkey_get_details($res);
    $pub_key = $key_detail['key'];
    
    $priv_pem = PEM::fromString($priv_key);
    
    $ec_priv_key = ECPrivateKey::fromPEM($priv_pem);
    
    $ec_priv_seq = $ec_priv_key->toASN1();
    
    $priv_key_hex = bin2hex($ec_priv_seq->at(1)->asOctetString()->string());
    $priv_key_len = strlen($priv_key_hex) / 2;
    $pub_key_hex = bin2hex($ec_priv_seq->at(3)->asTagged()->asExplicit()->asBitString()->string());
    $pub_key_len = strlen($pub_key_hex) / 2;
    
    $pub_key_hex_2 = substr($pub_key_hex, 2);
    $pub_key_len_2 = strlen($pub_key_hex_2) / 2;
    
    $hash = Keccak::hash(hex2bin($pub_key_hex_2), 256);
    
    $wallet_address = '0x' . substr($hash, -40);
    $wallet_private_key = '0x' . $priv_key_hex;

    $expired_at = date('Y-m-d H:i:s', time() + $expires_in_hour * 3600);

    $payment = $db->prepare('INSERT into payments SET user = ?, payment_wallet = ?, private_key = ?, amount = ?, expired_at = ?');
    $payment->execute([
        $user['id'],
        $wallet_address,
        $wallet_private_key,
        $payment_amount_str,
        $expired_at
    ]);

    $paymentId = $db->lastInsertId();

    json_response([
        'success' => true,
        'paymentId' => $paymentId,
        'paymentAmount' => $payment_amount_str,
        'paymentWallet' => $wallet_address,
        'expiryDate' => $expired_at
    ]);
}
else {
    json_response([
        'success' => false,
        'message' => 'Payment amount must be greater than 0!'
    ]);
}