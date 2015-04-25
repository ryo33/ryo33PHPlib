<?php

class Request{

    const POST = 0;
    const GET = 1;

    private $params;

    function __construct($URL = false){
        global $_SERVER, $_GET, $_POST;
        $uri = $_SERVER['REQUEST_URI'];
        if(($pos = strpos($uri, '?')) !== false){
            $uri = substr($uri, 0, $pos);
        }
        $this->uris = explode('/', trim($uri, '/'));
        $this->uris = array_map('urldecode', $this->uris);
        if($URL !== false){
            $this->uris = array_slice($this->uris, count(explode('/', rtrim(preg_replace('/^https?:\/\//', '', $URL), '/'))) - 1);
        }
        $this->uri_position = 0;
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            $this->request_method = self::POST;
        }else{
            $this->request_method = self::GET;
        }
    }

    function get_uri($get=0, $jump=1){
        $jump -= $get;
        for(;$get != 0;($get < 0) ? $get ++ : $get --){
            $this->uri_position += ($get < 0) ? -1 : 1;
        }
        $result = isset($this->uris[$this->uri_position]) ? $this->uris[$this->uri_position] : false;
        for(;$jump != 0;($jump < 0) ? $jump ++ : $jump --){
            $this->uri_position += ($jump < 0) ? -1 : 1;
        }
        return $result;
    }

    private function get_option($option, $name, $default=null){
        return isset($option[$name]) ? $option[$name] : $default;
    }

    function get_int($name, $option=[]){
        $min = $this->get_option($option, 'min');
        $max = $this->get_option($option, 'max');
        $default = $this->get_option($option, 'default');
        $unsigned = (bool) $this->get_option($option, 'unsigned', false);
        $exception = (bool) $this->get_option($option, 'exception', false);
        $param = $this->get_param($name, $default);
        if(
            $param !== null &&
            is_numeric($param) &&
            (ctype_digit($param) || ($unsigned && substr($param, 0, 1) === '-' && ctype_digit(substr($param, 1, -1)))) &&
            (($result = (int) $param) || true) &&
            ($min === null || $result >= $min) &&
            ($max === null || $result <= $max)
        ){
            return $result;
        }else if($exception){
            throw new WrongParameterException();
        }else{
            return $default === null ? null : (int) $default;
        }
    }

    function get_float($name, $option=[]){
        $min = $this->get_option($option, 'min');
        $max = $this->get_option($option, 'max');
        $default = $this->get_option($option, 'default');
        $unsigned = (bool) $this->get_option($option, 'unsigned', false);
        $exception = (bool) $this->get_option($option, 'exception', false);
        $param = $this->get_param($name, $default);
        if(
            $param !== null &&
            is_numeric($param) &&
            (($result = (double) $param) || true) &&
            (! $unsigned || $param >= 0) &&
            ($min === null || $result >= $min) &&
            ($max === null || $result <= $max)
        ){
            return $result;
        }else if($exception){
            throw new WrongParameterException();
        }else{
            return $default === null ? null : (float) $default;
        }
    }

    function get_bool($name, $option=[]){
        $default = $this->get_option($option, 'default');
        $exception = (bool) $this->get_option($option, 'exception', false);
        $param = $this->get_param($name, $default);
        if($param !== null){
            switch(strtolower($param)){
            case 'false':
            case 'f':
                return false;
            case 'true':
            case 't':
                return true;
            }
        }else if($exception){
            throw new WrongParameterException();
        }else{
            return $default === null ? null : (boolean) $default;
        }
    }

    function get_string($name, $option=[]){
        $min = $this->get_option($option, 'min');
        $max = $this->get_option($option, 'max');
        $default = $this->get_option($option, 'default');
        $exception = (bool) $this->get_option($option, 'exception', false);
        $function = $this->get_option($option, 'function', function($a){return true;});
        $param = $this->get_param($name, $default);
        if(
            $param !== null &&
            (($length = strlen($param)) || true) &&
            ($min === null || $length >= $min) &&
            ($max === null || $length <= $max) &&
            $function($param) === true
        ){
            return $param;
        }else if($exception){
            throw new WrongParameterException();
        }else{
            return $default === null ? null : (string) $default;
        }
    }

    function get_datetime($name, $option=[]){
        $min = $this->get_option($option, 'min');
        $max = $this->get_option($option, 'max');
        $default = $this->get_option($option, 'default');
        $exception = (bool) $this->get_option($option, 'exception', false);
        $param = $this->get_param($name, $default);
        if(
            $param !== null &&
            (($result = str_to_datetime($param)) !== null) &&
            ($min === null || $result >= str_to_datetime($min)) &&
            ($max === null || $result <= str_to_datetime($max))
        ){
            return $result;
        }else if($exception){
            throw new WrongParameterException();
        }else{
            return $default === null ? null : str_to_datetime($default);
        }
    }

    function get_email($name, $option=[]){
        $default = $this->get_option($option, 'default');
        $exception = (bool) $this->get_option($option, 'exception', false);
        $param = $this->get_param($name, $default);
        if(
            $param !== null &&
            filter_var($param, FILTER_VALIDATE_EMAIL)
        ){
            return $param;
        }else if($exception){
            throw new WrongParameterException();
        }else{
            return $default;
        }
    }

    private function get_param($name, $default=null){
        if($this->request_method === self::POST){
            if(isset($_POST[$name])){
                $default = $_POST[$name];
                unset($this->params[$name]);
            }
            return $default;
        }else{
            if(isset($_GET[$name])){
                $default = $_GET[$name];
                unset($this->params[$name]);
            }
            return $default;
        }
    }

    function check_param(){
        if(count($this->params) !== 0){
            $this->params = [];
            return true;
        }
        return false;
    }

}
