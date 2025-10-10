<?php

namespace App\Libraries;


use Illuminate\Support\Facades\Cache;

class CommonFunction
{
    public static function showErrorPublic($param, $msg = 'Sorry! Something went wrong! ')
    {
        $j = strpos($param, '(SQL:');
        if ($j > 15) {
            $param = substr($param, 8, $j - 9);
        } else {
            //
        }
        return $msg . $param;
    }

    public static function getUrslList()
    {
        $data = Cache::get('urlsTable');
        $urlsList = [];
        if($data){
            foreach ($data as $value) {
                array_push($urlsList, $value->uri);
            }
        }else{
            $data = \Route::getRoutes();
            foreach ($data as $value) {
                $uriArr = explode('/',$value->uri);
                if (strpos($uriArr[count($uriArr)-1], '{') !== false) {
                    array_pop($uriArr);
                }
                $uri = implode('/', $uriArr);
                array_push($urlsList, $uri);
            }
        }
        sort($urlsList);
        return $urlsList;
    }

    public static function checkAccess($url){
        $data = Cache::get('orderTable');
    }

}
