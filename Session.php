<?php

class Session{

    function __construct(){
        session_start();
    }

    function get($name, $default=null){
        if(isset($_SESSION[$name])){
            return $_SESSION[$name];
        }
        return $default;
    }

    function set($name, $value){
        $_SESSION[$name] = $value;
    }

    function remove($name=false){
        if($name === false){
            $_SESSION = array();
        }else{
            unset($_SESSION[$name]);
        }
    }

    function regenerate(){
        session_regenerate_id(true);
    }

}
