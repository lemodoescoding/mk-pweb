<?php

declare(strict_types=1);
namespace App;

use PDO;
use App\DB;

class App
{
    private static DB $db;
    
    public function __construct(
        protected Router $router,
        protected array $request,
        protected Config $config
    ){
        static::$db = new DB($config->db ?? []);
    }
    
    public static function db(): DB
    {
        return static::$db;
    }
    
    public function run()
    {
        $serverProtocol = $_SERVER['SERVER_PROTOCOL'];
        
        try {
            echo $this->router->resolve($this->request['uri'],$this->request['method']);
         
        } catch(\App\Exception\InvalidRequestName $e){
            //header('HTTP/1.1 404 Not Found');
            http_response_code(404);
            
            echo View::make('error/404');
        } catch(\App\Exception\InvalidRequestMethod $e){
            header($serverProtocol . ' 405 Method Not Allowed');
            
            echo View::make('error/405');
        } catch(\App\Exception\ViewNotFoundException $e){
            header($serverProtocol . ' 404 Not Found');
            
            echo View::make('error/404', ['info' => 'No Content Found']);
        }
    }
}
