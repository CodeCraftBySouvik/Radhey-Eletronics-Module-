<?php

namespace App\Interfaces;

interface CartInterface
{
    public function listAll();
    public function listById($userId);    
    public function delete($id);
}
