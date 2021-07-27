<?php
isset($_CONFIG) or die;
return [
    'success' => true,
    'version' => number_format($_CONFIG['apiVersion'], 2, '.', '')
];