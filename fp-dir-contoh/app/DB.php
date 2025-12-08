<?php

declare(strict_types=1);
namespace App;

use PDO;

/**
 * @mixin PDO 
 */
class DB
{
    private PDO $pdo;
    
    public function __construct(array $config)
    {
        $option = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
        
        
        try {
            
            $this->pdo = new PDO($config['driver'] . ':host=' . $config['host'] . ';dbname=' . $config['database'],
                            $config['user'],
                            $config['pass'],
                            $config['options'] ?? $option);
            
        } catch(\PDOException $e){
            echo "Database Connection Failed - Code : {$e->getCode()}";
            exit;
        }
    }
    
    public function __call(string $name, array $arguments)
    {
        return call_user_func_array([$this->pdo, $name], $arguments);
    }
}