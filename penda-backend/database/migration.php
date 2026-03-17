<?php

namespace App\Database;

use PDO;

abstract class Migration
{
    protected PDO $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    abstract public function up();
    abstract public function down();
}
