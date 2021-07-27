<?php
isset($_CONFIG) or die;

$payment_id = intval(get_post('payment_id'));

$payment = null;

if($payment_id > 0) {
    $payment = $db->prepare('SELECT * from payments WHERE id = ? AND user = ?');
    $payment->execute([$payment_id, $user['id']]);
    $payment = $payment->fetch(PDO::FETCH_ASSOC);
}

if(!empty($payment)) {
    $status = 'waiting_payment';
    if(floatval($payment['paid_amount']) >= floatval($payment['amount'])) {
        $status = 'paid';
    }
    else if(strtotime($payment['expired_at']) <= time()) {
        $status = 'expired';
    }
    $data = [
        'success' => true,
        'status' => $status,
        'paymentId' => $payment['id'],
        'paymentAmount' => $payment['amount'],
        'paidAmount' => empty($payment['paid_amount']) ? bc_number_format(0, $_CONFIG['precision']) : $payment['paid_amount'],
        'paymentWallet' => $payment['payment_wallet'],
        'creationDate' => $payment['created_at']
    ];
    if($status == 'paid') {
        $data['completionDate'] = $payment['completed_at'];
        $data['payoutTransactionHash'] = $payment['payout_tx'];
    }
    else {
        $data['expiryDate'] = $payment['expired_at'];
    }
    json_response($data);
}
else {
    json_response([
        'success' => false,
        'message' => 'Payment id is invalid!'
    ]);
}