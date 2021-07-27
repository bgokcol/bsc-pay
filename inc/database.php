<?php
isset($_CONFIG) or die;

try {
    $db = new PDO('mysql:host='.$_CONFIG['dbHost'].';dbname='.$_CONFIG['dbName'].';charset=utf8', $_CONFIG['dbUser'], $_CONFIG['dbPass']);
}
catch(Exception $e) {
    json_response([
        'success' => false,
        'message' => 'Database connection failed.'
    ]);
}