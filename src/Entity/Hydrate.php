<?php
namespace App\Entity;

trait Hydrate
{
    public function hydrate($data)
    {
        foreach($data as $key => $value) {
            $method = 'get' . ucfirst($value);
            if(method_exists($this, $method)) {
                call_user_func($this->$method, $value);
            }
        }
    }
}