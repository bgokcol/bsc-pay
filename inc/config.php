<?php
$config = [
    'baseUrl' => '{baseUrl}',
    'dbHost' => '{dbHost}',
    'dbName' => '{dbName}',
    'dbUser' => '{dbUser}',
    'dbPass' => '{dbPass}',
    'web3Url' => '{web3Url}',
    'web3Gas' => '{web3Gas}',
    'web3GasPrice' => '{web3GasPrice}',
    'web3ChainId' => '{web3ChainId}',
    'cronKey' => '{cronKey}',
    'precision' => '{precision}',
    'apiVersion' => 1
];
$config['web3Gas'] = intval($config['web3Gas']);
$config['web3GasPrice'] = intval($config['web3GasPrice']);
$config['web3ChainId'] = intval($config['web3ChainId']);
$config['precision'] = intval($config['precision']);
return $config;