<?php

use Web3\Web3;
use Web3\Providers\HttpProvider;
use Web3\RequestManagers\HttpRequestManager;
use Web3p\EthereumTx\Transaction;

isset($_CONFIG) or die;
require './lib/autoload.php';

$waitingPayments = $db->prepare('SELECT * from payments WHERE completed_at < created_at AND NOW() < expired_at LIMIT 100');
$waitingPayments->execute();
$waitingPayments = $waitingPayments->fetchAll(PDO::FETCH_ASSOC);

$web3 = new Web3(new HttpProvider(new HttpRequestManager($_CONFIG['web3Url'], 5)));

$eth = $web3->eth;

foreach ($waitingPayments as $payment) {
    $wallet_balance = 0;
    $eth->getBalance($payment['payment_wallet'], function ($err, $balance) use (&$wallet_balance) {
        if ($err !== null) {
            echo 'Error: ' . $err->getMessage();
            return;
        }
        $wallet_balance = floatval(wei_to_eth($balance));
    });
    echo sprintf('[Payment #%s | %s | Balance: %s | Payment Amount: %s]', $payment['id'], $payment['payment_wallet'], bc_number_format(floatval($wallet_balance), $_CONFIG['precision']), $payment['amount']) . PHP_EOL;
    if ($wallet_balance > 0 && $wallet_balance > floatval($payment['paid_amount'])) {
        if (floatval($payment['amount']) > $wallet_balance) {
            $update = $db->prepare('UPDATE payments SET paid_amount = ? WHERE id = ?');
            $update->execute([bc_number_format($wallet_balance, $_CONFIG['precision']), $payment['id']]);
        } else {
            $user = $db->prepare('SELECT * from users WHERE id = ?');
            $user->execute([$payment['user']]);
            $user = $user->fetch(PDO::FETCH_ASSOC);
            if (!empty($user)) {
                $nonce = 0;
                $eth->getTransactionCount($payment['payment_wallet'], function ($err, $result) use (&$nonce) {
                    $nonce = gmp_intval($result->value);
                });
                $wallet_balance_wei = eth_to_wei($wallet_balance);
                $gas_wei = gwei_to_wei($_CONFIG['web3GasPrice']);
                $gas_limit = $_CONFIG['web3Gas'];
                $transaction_fee = bcmul($gas_wei, $gas_limit);
                $value_wei = bcsub($wallet_balance_wei, $transaction_fee);
                $transaction = [
                    'nonce' => '0x' . dechex($nonce),
                    'from' => strtolower($payment['payment_wallet']),
                    'to' => strtolower($user['wallet_address']),
                    'gasLimit' => '0x' . bcdechex($gas_limit),
                    'gasPrice' => '0x' . bcdechex($gas_wei),
                    'value' => '0x' . bcdechex($value_wei),
                    'chainId' => strval($_CONFIG['web3ChainId'])
                ];
                $transaction = new Transaction($transaction);
                $signedTx = $transaction->sign($payment['private_key']);
                $payout_tx = '';
                echo sprintf('Transfering funds from %s to %s. ', $payment['payment_wallet'], $user['wallet_address']);
                $eth->sendRawTransaction('0x' . $signedTx, function ($err, $tx) use (&$payout_tx) {
                    if ($err !== null) {
                        echo '(Error: ' . $err->getMessage() . ')' . PHP_EOL;
                        $payout_tx = 'failed';
                    } else {
                        echo '(Transaction Hash: ' . $tx . ')' . PHP_EOL;
                        $payout_tx = $tx;
                    }
                });
                $update = $db->prepare('UPDATE payments SET paid_amount = ?, payout_tx = ?, completed_at = ? WHERE id = ?');
                $update->execute([bc_number_format($wallet_balance, $_CONFIG['precision']), $payout_tx, date('Y-m-d H:i:s'), $payment['id']]);
            }
        }
    }
}
