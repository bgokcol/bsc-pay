## BSC Pay (Simple BNB Payment Gateway)
I like to use Binance Smart Chain, do you? You can use my simple payment gateway if you need to get paid with BSC. (Of course, it also supports other ETH networks.)

### How Does It Work?
1. Our system generates a new wallet address for each payment.
2. Your customer sends BNB to this wallet address.
3. Cron checks the balance of the wallet and approves the payment. Our system transfers funds from generated wallet address to your payout wallet address after approval.


### Prerequisites
* PHP 7.0+
* PHP GMP Extension

### How to Install?
* Create a MySQL database.
* Download this repository and upload files to your web server.
* Go to your website with a web browser. You will see the installation wizard. Enter the database details and your BSC wallet address. (If you want to use Binance Smart Chain, you don't need to change anything in **Network Settings**.)

![image](https://user-images.githubusercontent.com/47295517/127149684-2c1b508d-7694-4acb-8531-0b34447803e3.png)
* After the installation, *your API key* and *cron URL* will appear on the screen. Set up a cron command that runs every minute and makes a request to the cron URL. Example Cron Command: `* * * * * curl {cronUrlAddress}`

[View API Documentation 🔍](https://github.com/bgokcol/bsc-pay/blob/main/API.md)

### Resources
* https://github.com/web3p/web3.php
* https://github.com/web3p/ethereum-tx
* https://www.quicknode.com/guides/web3-sdks/how-to-generate-a-new-ethereum-address-in-php
