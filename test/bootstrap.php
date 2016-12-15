<?php

call_user_func(function () {
    // start serving suite API stub
    exec('php -S localhost:7984 '.__DIR__.'/helper/api-stub.php >/tmp/php-suite-api.log 2>&1 & echo $!', $output);
    $pid = (int) $output[0];
    echo "Starting API stub" . PHP_EOL;

    // Kill api stub
    register_shutdown_function(function() use ($pid) {
        echo "Stopping API stub" . PHP_EOL;
        exec('kill ' . $pid);
        echo file_get_contents('/tmp/php-suite-api.log');
    });
});
