<?php

namespace App\Models;

use PDO;

abstract class BaseModel
{
    protected PDO $pdo;

    public function __construct()
    {
        // db() function is defined in private/db.php
        $this->pdo = db();
    }

    public function __destruct()
    {
        // Explicitly close the SQL connection
        $this->pdo = null;
    }
}
