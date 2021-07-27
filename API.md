## API Documentation
You must to send a POST request to base url to use the API. The request body must be in **url encoded** format. Every request must contain **api_key** parameter.

### Get API Version
#### Required Parameters
* **api_key**: Your API Key
* **action**: version
#### Example Response
```json
{
    "success": true,
    "version": "1.00"
}
```
#### Example PHP Code
```php
$url = "https://yourwebsite.com/bsc-folder/";

$curl = curl_init($url);
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

$headers = [
  "Content-Type: application/x-www-form-urlencoded"
];

curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

$data = http_build_query([
  "api_key" => "Your API Key",
  "action" => "version"
]);

curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

$result = json_decode(curl_exec($curl), true);
curl_close($curl);
var_dump($result);
```

### Create Payment
#### Required Parameters
* **api_key**: Your API Key
* **action**: create_payment
* **payment_amount**: Payment Amount (BNB)
#### Example Response
```json
{
    "success": true,
    "paymentId": "1000",
    "paymentAmount": "0.1000000000",
    "paymentWallet": "0x000000000000000000000000000000000000dead",
    "expiryDate": "2021-07-27 20:51:41"
}
```
#### Example PHP Code
```php
$url = "https://yourwebsite.com/bsc-folder/";

$curl = curl_init($url);
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

$headers = [
  "Content-Type: application/x-www-form-urlencoded"
];

curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

$data = http_build_query([
  "api_key" => "Your API Key",
  "action" => "create_payment",
  "payment_amount" => "0.1"
]);

curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

$result = json_decode(curl_exec($curl), true);
curl_close($curl);
var_dump($result);
```

### Get Payment Status
#### Required Parameters
* **api_key**: Your API Key
* **action**: get_payment
* **payment_id**: ID of the payment
#### Example Response
```json
{
    "success": true,
    "status": "waiting_payment",
    "paymentId": "3",
    "paymentAmount": "0.1000000000",
    "paidAmount": "0.0000000000",
    "paymentWallet": "0x000000000000000000000000000000000000dead",
    "creationDate": "2021-07-27 15:51:41",
    "expiryDate": "2021-07-27 20:51:41"
}
```
#### Example PHP Code
```php
$url = "https://yourwebsite.com/bsc-folder/";

$curl = curl_init($url);
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

$headers = [
  "Content-Type: application/x-www-form-urlencoded"
];

curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

$data = http_build_query([
  "api_key" => "Your API Key",
  "action" => "get_payment",
  "payment_id" => "1000"
]);

curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

$result = json_decode(curl_exec($curl), true);
curl_close($curl);
var_dump($result);
```
