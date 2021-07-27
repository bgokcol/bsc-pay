<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
  <title>Installation Wizard</title>
</head>

<body>
  <div class="container my-5">
    <h4>Welcome to <b>Installation Wizard</b>!</h4>
    <div>Please enter the required information to complete the setup. If you encounter any problems, you can <a href="https://github.com/bgokcol/bsc-pay/issues/new/" target="_blank">create an issue</a> on Github.</div>
    <?php
    function post_or_default($key, $default = '')
    {
      if (isset($_POST[$key])) {
        return $_POST[$key];
      } else {
        return $default;
      }
    }
    if (version_compare(phpversion(), '7', '>=')) {
      $currentSSL = isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https';
      $currentUrl = 'http' . ($currentSSL ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

      $required_params = ['baseUrl', 'dbHost', 'dbName', 'dbUser', 'dbPass', 'web3Url', 'web3ChainId', 'web3Gas', 'web3GasPrice', 'precision', 'userWallet'];

      $install_action = true;

      foreach ($required_params as $param) {
        if (!isset($_POST[$param]) || empty($_POST[$param])) {
          $install_action = false;
          break;
        }
      }

      if ($install_action) {
        $_POST['baseUrl'] = rtrim($_POST['baseUrl'], '/') . '/';
        $_POST['dbPass'] = rtrim($_POST['dbPass']);

        $error = null;
        $success = false;

        if (is_writable('../inc/config.php')) {
          try {
            $db = new PDO('mysql:host=' . $_POST['dbHost'] . ';dbname=' . $_POST['dbName'] . ';charset=utf8', $_POST['dbUser'], $_POST['dbPass']);
          } catch (Exception $e) {
            $error = 'Database connection failed!';
          }
          if (empty($error)) {
            $apiKey = bin2hex(random_bytes(20));
            $cronKey = bin2hex(random_bytes(16));
            $db->exec(file_get_contents('database.sql'));
            $insert = $db->prepare('INSERT into users SET wallet_address = ?, api_key = ?, status = 1');
            $insert->execute([
              $_POST['userWallet'], $apiKey
            ]);
            $data = file_get_contents('../inc/config.php');
            $data = str_replace('{cronKey}', addslashes($cronKey), $data);
            foreach ($required_params as $param) {
              $data = str_replace('{' . $param . '}', addslashes($_POST[$param]), $data);
            }
            file_put_contents('../inc/config.php', $data);
            $success = true;
          }
        } else {
          $error = 'Config.php is not writable!';
        }

        if (!empty($error)) {
    ?>
          <div class="alert alert-danger mt-3">
            <?php echo $error ?>
          </div>
      <?php
        } elseif ($success) {
        ?>
        <div class="alert alert-success mt-3">
          Installation successfully completed! <b>install</b> folder will be automatically deleted, if not please manually delete it.
        </div>
        <div class="mt-3">
          <div><b>Your Api Key:</b> <?php echo $apiKey ?></div>
          <div><b>Cron URL Address:</b> <?php echo $_POST['baseUrl'].'?cron='.$cronKey ?></div>
        </div>
        <?php
        }
      }
      ?>
      <?php if (!isset($success) || !$success) {
      ?>
        <form action="" method="post">
          <div class="mt-3"><label for="baseUrl">Base URL:</label></div>
          <input type="text" class="form-control mt-2" id="baseUrl" name="baseUrl" placeholder="Enter the base url." value="<?php echo htmlspecialchars(post_or_default('baseUrl', rtrim(rtrim(rtrim($currentUrl, '/'), 'install'), '/') . '/')) ?>" required>
          <h5 class="mt-3 mb-0">Database Settings</h5>
          <div class="row">
            <div class="col-lg-6">
              <div class="mt-3"><label for="dbHost">Database Hostname:</label></div>
              <input type="text" class="form-control mt-2" id="dbHost" name="dbHost" placeholder="Enter the database hostname." value="<?php echo htmlspecialchars(post_or_default('dbHost', 'localhost')) ?>" required>
            </div>
            <div class="col-lg-6">
              <div class="mt-3"><label for="dbName">Database Name:</label></div>
              <input type="text" class="form-control mt-2" id="dbName" name="dbName" placeholder="Enter the database name." value="<?php echo htmlspecialchars(post_or_default('dbName')) ?>" required>
            </div>
            <div class="col-lg-6">
              <div class="mt-3"><label for="dbUser">Database Username:</label></div>
              <input type="text" class="form-control mt-2" id="dbUser" name="dbUser" placeholder="Enter the database username." value="<?php echo htmlspecialchars(post_or_default('dbUser')) ?>" required>
            </div>
            <div class="col-lg-6">
              <div class="mt-3"><label for="dbPass">Database Password:</label></div>
              <input type="password" class="form-control mt-2" id="dbPass" name="dbPass" placeholder="Enter the database password." value="<?php echo htmlspecialchars(post_or_default('dbPass')) ?>" required>
            </div>
          </div>
          <h5 class="mt-3 mb-0">Network Settings</h5>
          <div class="row">
            <div class="col-lg-6">
              <div class="mt-3"><label for="web3Url">RPC Url:</label></div>
              <input type="text" class="form-control mt-2" id="web3Url" name="web3Url" placeholder="Enter the RPC url." value="<?php echo htmlspecialchars(post_or_default('web3Url', 'https://bsc-dataseed1.binance.org:443')) ?>" required>
            </div>
            <div class="col-lg-6">
              <div class="mt-3"><label for="web3ChainId">Chain Id:</label></div>
              <input type="number" class="form-control mt-2" id="web3ChainId" name="web3ChainId" placeholder="Enter the chain id." value="<?php echo htmlspecialchars(post_or_default('web3ChainId', '56')) ?>" required>
            </div>
            <div class="col-lg-6">
              <div class="mt-3"><label for="web3Gas">Gas Limit:</label></div>
              <input type="number" class="form-control mt-2" id="web3Gas" name="web3Gas" placeholder="Enter the gas limit." value="<?php echo htmlspecialchars(post_or_default('web3Gas', '21000')) ?>" required>
            </div>
            <div class="col-lg-6">
              <div class="mt-3"><label for="web3GasPrice">Gas Price:</label></div>
              <input type="number" class="form-control mt-2" id="web3GasPrice" name="web3GasPrice" placeholder="Enter the gas price." value="<?php echo htmlspecialchars(post_or_default('web3Gas', '5')) ?>" required>
            </div>
            <div class="col-lg-12">
              <div class="mt-3"><label for="precision">Float Precision:</label></div>
              <input type="number" class="form-control mt-2" id="precision" name="precision" placeholder="Enter the float precision." value="<?php echo htmlspecialchars(post_or_default('precision', '10')) ?>" required>
            </div>
          </div>
          <h5 class="mt-3 mb-0">User Settings</h5>
          <div class="mt-3"><label for="userWallet">Payout Wallet Address:</label></div>
          <input type="text" class="form-control mt-2" id="userWallet" name="userWallet" placeholder="Enter the payout wallet address." value="<?php echo htmlspecialchars(post_or_default('userWallet')) ?>" required>
          <button class="mt-3 btn btn-primary" type="submit">Install</button>
        </form>
      <?php
      }
    } else {
      ?>
      <div class="alert alert-danger mt-3">Minimum PHP 7.0 is required!</div>
    <?php
    }
    ?>
  </div>
</body>

</html>