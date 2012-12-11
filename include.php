<?php

include '/var/www/scripts/httpClient/http.php';
include '/var/www/scripts/phpQuery/phpQuery.php';

set_time_limit(0);
ini_set('memory_limit', '512M');

class node {

    public $name, $children, $meta, $hash;

    function __construct($name = '', $meta = null) {
        $this->name = $name;
        $this->meta = $meta;
        $this->children = array();
    }
    public static function __set_state($array) {
        $node = new node;
        foreach($array as $k=>$v) $node->$k = $v;
        return $node;
    }

}
function download_file($url, $filename) {
    $fp = fopen($filename, 'w+');
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 0);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.2.3) Gecko/20100401 Firefox/3.6.3');
    curl_setopt($ch, CURLOPT_FILE, $fp);
    $res = curl_exec($ch);
    curl_close($ch);
    fclose($fp);
}
$http = new http;

function rrmdir($dir)
{
    if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach($objects as $object) {
            if ($object != "." && $object != "..") {
                if (filetype($dir."/".$object) == "dir") {
                    rrmdir($dir."/".$object);
                } else {
                    unlink($dir."/".$object);
                }
            }
        }
        reset($objects);
        rmdir($dir);
    }
}

function MergeArrays($Arr1, $Arr2)
{
  foreach($Arr2 as $key => $Value)
  {
    if(array_key_exists($key, $Arr1) && is_array($Value))
      $Arr1[$key] = MergeArrays($Arr1[$key], $Arr2[$key]);

    else
      $Arr1[$key] = $Value;

  }

  return $Arr1;

}
$settings = MergeArrays(array(
    "url" => null, "creator" => array(),
    "book"=>array("title" => null, 'desc'=> 'Книга сделана с помощью парсера, написанного Георгием Агаповым для парсинга вики-конспектов с neerc.ifmo.ru/wiki.', "lang" => "ru"),
    "levels" => array(0,1,1,1,1,1,1,1,1,1),
), parse_ini_file('settings.ini'));