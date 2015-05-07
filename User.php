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

    private $login_functions = [];
    private $logout_functions = [];

    function __construct($session, $cookie, $check_user_id, $auto_login){
        $this->session = $session;
        $this->cookie = $cookie;
        $tmp = $this->session->get(self::$LOGIN, false) ? $this->session->get(self::$USER_ID, false) : false;
        if($tmp !== false && $check_user_id($tmp)){
            $this->is_login = true;
            $this->user_id = $tmp;
        }else{
            $this->is_login = false;
            $key = $this->cookie->get(self::$AUTO_LOGIN);
            if($key !== null){
                $id = $auto_login($key);
                if($id !== null){
                    $this->login($id);
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
        $this->session->set(self::$LOGIN, true);
        $this->session->set(self::$USER_ID, $user_id);
        $this->is_login = true;
        $this->user_id = $user_id;
        $this->session->regenerate();
        foreach($this->login_functions as $function){
            $function();
        }
    }

    function logout(){
        $this->session->set(self::$LOGIN, false);
        $this->session->regenerate();
        $this->is_login = false;
        $this->user_id = null;
        $this->cookie->remove(self::$AUTO_LOGIN);
        foreach($this->logout_functions as $function){
            $function();
        }
    }

    function enable_auto_login(){
        if($this->is_login){
            $this->cookie->set(self::$AUTO_LOGIN, $this->create_auto_login_key());
        }
    }

    function is_login(){
        return $this->is_login;
    }

}
