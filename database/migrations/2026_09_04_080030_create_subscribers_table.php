<?php

use PDO;

return [
    'up' => function (PDO $db): void {
        $db->exec("CREATE TABLE subscribers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(255) NOT NULL UNIQUE,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        )");
    },
    'down' => function (PDO $db): void {
        $db->exec("DROP TABLE IF EXISTS subscribers");
    },
];
