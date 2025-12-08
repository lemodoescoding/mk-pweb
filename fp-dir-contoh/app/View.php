<?php

declare(strict_types=1);
namespace App;

use App\Exception\ViewNotFoundException;

final class View
{
    public function __construct(
        protected string $view,
        protected array $params = []
    ) {
    }
    
    public static function make(
        string $view,
        array $params = []
    ): static {
        return new static($view, $params);
    }
    
    public function render(): string
    {
        $header = VIEW_PATH . '/' . 'layout/header' . '.php';
        $path = VIEW_PATH . '/' . $this->view . '.php';
        $footer = VIEW_PATH . '/' . 'layout/footer' . '.php';
        
        if(!file_exists($path))
        {
            throw new ViewNotFoundException();
            exit;
        }
        
        foreach ($this->params as $key => $value){
            $$key = $value;
        }
        
        ob_start();
        
        include $header;
        include $path;
        include $footer;
        
        $temp = ob_get_clean();
        
        return $temp;
    }
    
    // automatically executed when the class is called as string
    
    public function __toString(): string
    {
        return $this->render();
    }
}