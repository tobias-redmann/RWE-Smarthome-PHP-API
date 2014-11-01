<?php

if (!file_exists(dirname(__FILE__).'/config.php')) {

    die('Please create a config.php - use the config-sample.php');

}

include('config.php');

include('vendor/autoload.php');

include('smarthome.php');


function getGUID(){
    if (function_exists('com_create_guid')){
        return com_create_guid();
    }else{
        mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
        $charid = strtoupper(md5(uniqid(rand(), true)));
        $hyphen = chr(45);// "-"
        $uuid = ''// "{"
            .substr($charid, 0, 8).$hyphen
            .substr($charid, 8, 4).$hyphen
            .substr($charid,12, 4).$hyphen
            .substr($charid,16, 4).$hyphen
            .substr($charid,20,12)
            .'';// "}"
        return $uuid;
    }
}

