<?php

class Cookie{

    function get($name, $default=null){
        return isset($_COOKIE[$name]) ? $_COOKIE[$name] : $default;
    }

    function set($name, $value){
        setcookie($name, $value);
    }

    function remove($name){
        setcookie($name, null, time() - 3600);
    }

}
