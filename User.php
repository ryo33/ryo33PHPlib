<?php

class User{

    //session key
    private static $LOGIN = '_login_';
    private static $USER_ID = '_user_id';
    //cookie key
    private static $AUTO_LOGIN = '_auto_login';

    private $session;
    private $cookie;
    public $is_login = false;
    public $user_id = null;
    public $AUTO_LOGIN_KEY_LENGTH = 16;

    private $create_auto_login_key;

    private $login_functions = [];
    private $logout_functions = [];

    function __construct($session, $cookie, $destination, $check_user_id, $auto_login, $create_auto_login_key){
        $this->session = $session;
        $this->cookie = $cookie;
        $this->destination = $destination;
        $this->create_auto_login_key = $create_auto_login_key;
        $tmp = $this->session->get($this->destination . self::$LOGIN, false) ? $this->session->get($this->destination . self::$USER_ID, false) : false;
        if($tmp !== false && $check_user_id($tmp)){
            $this->is_login = true;
            $this->user_id = $tmp;
        }else{
            $this->is_login = false;
            $user_id = $this->cookie->get($this->destination . self::$USER_ID);
            $key = $this->cookie->get($this->destination . self::$AUTO_LOGIN);
            if($user_id !== null && $key !== null){
                $result = $auto_login($user_id, $key);
                if($result !== null){
                    $this->login($user_id);
                    $this->set_auto_login($this->user_id, $result);
                }
            }
        }
    }

    function add_function_login($function){
        $this->login_functions[] = $function;
    }

    function add_function_logout($function){
        $this->logout_functions[] = $function;
    }

    function login($user_id){
        $this->session->set($this->destination . self::$LOGIN, true);
        $this->session->set($this->destination . self::$USER_ID, $user_id);
        $this->is_login = true;
        $this->user_id = $user_id;
        $this->session->regenerate();
        foreach($this->login_functions as $function){
            $function();
        }
    }

    function logout(){
        $this->session->set($this->destination . self::$LOGIN, false);
        $this->session->regenerate();
        $this->is_login = false;
        $this->user_id = null;
        $this->cookie->remove($this->destination . self::$AUTO_LOGIN);
        foreach($this->logout_functions as $function){
            $function();
        }
    }

    function set_auto_login($user_id, $key){
        $this->cookie->set($this->destination . self::$AUTO_LOGIN, $key);
        $this->cookie->set($this->destination . self::$USER_ID, $user_id);
    }

    function enable_auto_login(){
        if($this->is_login){
            $func = $this->create_auto_login_key;
            $this->set_auto_login($this->user_id, $func($this->user_id));
        }
    }

    function is_login(){
        return $this->is_login;
    }

}
