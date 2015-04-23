<?php
class EasySql{

    private $pdo = null;
    private $fetch_mode = PDO::FETCH_ASSOC;
    private $debug = false;

    function __construct($dsn, $user, $password, $fetch_mode=PDO::FETCH_ASSOC, $utf=true){
        $this->pdo = new PDO($dsn, $user, $password);
        $this->fetch_mode = $fetch_mode;
        if($utf){
            $this->pdo->exec('SET NAMES utf8');
        }
    }

    function prepare($sql, $arg=null, $exec=false){
        if($this->debug){
            error_log($sql);
        }
        if($arg !== null){
            if(!is_array($arg)){
                $arg = array($arg);
            }
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($arg);
            return $stmt;
        }else{
            if($exec){
                return $this->pdo->exec($sql);
            }else{
                return $this->pdo->query($sql);
            }
        }
    }

    function debug($debug){
        $this->debug = $debug;
    }

    function beginTransaction(){
        $this->pdo->beginTransaction();
    }

    function commit(){
        $this->pdo->commit();
    }

    function fetch($sql, $arg=null){
        try{
            return $this->prepare($sql, $arg)->fetch($this->fetch_mode);
        }catch(Exception $e){
            exit($e->getMessage() . $sql);
        }
    }

    function fetchAll($sql, $arg=null){
        return $this->prepare($sql, $arg)->fetchAll($this->fetch_mode);
    }

    function fetchColumn($sql, $arg=null){
        return $this->prepare($sql, $arg)->fetchColumn();
    }

    function fetchColumnAll($sql, $arg=null){
        return $this->prepare($sql, $arg)->fetchAll(PDO::FETCH_COLUMN);
    }

    function execute($sql, $arg=null){
        $this->prepare($sql, $arg, true);
    }

    function select($table, $columns, $where){
        if($this->check_table_name($table)){
            return null;
        }
        return 'SELECT ' . $columns . ' FROM `' . $table . '` WHERE ' . implode(' AND ', array_map(function($a){return '`' . $a . '` = ?';}, $where));
    }

    function insert($table, $columns, $values, $last_insert_id=false){
        if($this->check_table_name($table)){
            return null;
        }
        if(!is_array($columns)){
            $columns = array($columns);
        }
        if(!is_array($values)){
            $values = array($values);
        }
        $this->execute('INSERT INTO `' . $table . '`(`' . implode('`, `', $columns) . '`)VALUES(' . str_repeat('?,', count($columns) - 1) . '?)', $values);
        if($last_insert_id){
            return $this->pdo->lastInsertId();
        }
    } 

    function check_table_name($name){
        if(strpos($name, '`') !== false or strpos($name, ';') !== false){
            return true;
        }
        return false;
    }

    function update($table, $id, $pairs){
        if($this->check_table_name($table)){
            return null;
        }
        foreach($pairs as $key => $value){
            $columns[] = $key;
            $values[] = $value;
        }
        $values[] = $id;
        $this->execute('UPDATE `' . $table . '` SET ' . implode(', ', array_map(function($a){return '`' . $a . '` = ?';}, $columns)) . ' WHERE `id` = BINARY ?', $values);
    }

    function get_count($table, $where){
        if($this->check_table_name($table)){
            return null;
        }
        $where1 = [];
        $where2 = [];
        foreach($where as $key=>$item){
            $where1[] = $key;
            $where2[] = $item;
        }
        return $this->fetchColumn($this->select($table, 'COUNT(`id`)', $where1), $where2);
    }

}
