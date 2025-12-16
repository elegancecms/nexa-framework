<?php

namespace Core;

class Middleware
{
    protected $middleware = [];

    public function web(array $append = [])
    {
        // Web middleware grubuna ekleme mantığı
    }

    public function encryptCookies(array $except = [])
    {
        // Cookie şifreleme ayarları
    }
}
