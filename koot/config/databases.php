<?php
/**
 * KumbiaPHP Web Framework
 * Database connection parameters
 */
/*return [
    'default' => [
        'dsn' => 'mysql:host=127.0.0.1;dbname=koot;charset=utf8',
        'username' => 'koot',
        'password' => '',
        'params' => [
            \PDO::ATTR_PERSISTENT => \true, //conexión persistente
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        ],
    ],
];*/

/**
 * SQLite connection
 */
return [
    'default' => [
        'dsn' => 'sqlite:'.APP_PATH.'temp/sqlite/koot.db',
        'pdo' => 'On',
    ]
];
