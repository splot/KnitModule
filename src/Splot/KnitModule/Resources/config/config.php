<?php

use Psr\Log\LogLevel;

return array(
    'stores' => array(
        'default' => array(
            'class' => 'Knit\\Store\\MySQLStore',
            'hostname' => 'localhost',
            'port' => null,
            'username' => '',
            'password' => '',
            'database' => '',
        )
    ),

    'entities' => array(),

    'slow_query_logger' => array(
        'enabled' => true,
        'threshold' => 0.5,
        'raise_to_level' => LogLevel::NOTICE
    )

);