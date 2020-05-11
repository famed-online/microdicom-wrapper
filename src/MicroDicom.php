<?php

namespace MicroDicom;

use http\Url;

class MicroDicom {

    private $dicomServer;

    public function __construct($dicomServer)
    {
        $this->dicomServer = $dicomServer;
    }

    public function ping() {

        $url =$this->dicomServer . '/ping';

        if ($result = $this->curl_send($url)) {
            return $result;
        }

        return null;
    }

    public function set_values($file_path, $tags_values) {

        $url =$this->dicomServer . '/set_values';
        $cFile = curl_file_create($file_path);
        $params_tags = $this->tags_values_to_string($tags_values);

        $params = array( 'params' =>'{ "items": [ '.$params_tags.'] }',
                         'file'=> $cFile);
        if ($result = $this->curl_send($url, $params)) {
            return $result;
        }
        return null;

    }

    public function get_values($file_path, $tags) {

        $url =$this->dicomServer . '/get_values';
        $cFile = curl_file_create($file_path);
        $params_tags = $this->tags_to_string($tags);

        $params = array( 'params' =>'{ "filter": [ '.$params_tags.' ] }',
                         'file'=> $cFile);

        if ($result = $this->curl_send($url, $params)) {
            return $result;
        }
        return null;

    }

    public function get_binary($file_path, $tags) {

        $url =$this->dicomServer . '/get_binary';
        $cFile = curl_file_create($file_path);
        $params_tags = $this->tags_to_string($tags);

        $params = array( 'params' =>'{ "tag": '.$params_tags.' }',
                         'file'=> $cFile);

        if ($result = $this->curl_send($url, $params)) {
            return $result;
        }
        return null;

    }

    public function get_image($file_path, $format='jpeg', $resize=1024, $convert_factor=0.5){

        $url =$this->dicomServer . '/get_image';
        $cFile = curl_file_create($file_path);

        $params = array( 'params' =>'{ "format": "'.$format.'", "resize": '.$resize.', "convert_factor": '.$convert_factor.' }',
                         'file'=> $cFile);

        if ($result = $this->curl_send($url, $params)) {
            return $result;
        }
        return null;
    }



    private function curl_send($url, $params=null) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);        //return the transfer as a string
        curl_setopt($ch, CURLOPT_URL, $url);
        if ($params) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        }
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }

    private function tags_to_string($tags) {
        $params_tags = '';
        if (is_array($tags[0])) {                           //Если в tags получили двумерный массив тэгов (несколько элементов)
            foreach ($tags as $tag) {
                $params_tags = $params_tags . '[ "'.$tag[0].'", "'.$tag[1].'" ],';
            }
            $params_tags = substr($params_tags, 0, -1);
        } elseif (is_array($tags)) {                    //Если в tags получили одномерный массив тэгов (один элемент)
            $params_tags = '[ "'.$tags[0].'", "'.$tags[1].'" ]';
        }
        return$params_tags;
    }

    private function tags_values_to_string($tags_values) {
        $params_tags = '';
        if (is_array($tags_values[0])) {                           //Если в tags получили двумерный массив тэгов (несколько элементов)
            foreach ($tags_values as $tag) {
                $params_tags = $params_tags . '{ "tag": [ "'.$tag[0].'", "'.$tag[1].'" ], "value": "'.$tag[2].'" },';
            }
            $params_tags = substr($params_tags, 0, -1);
        } elseif (is_array($tags_values)) {                    //Если в tags получили одномерный массив тэгов (один элемент)
            $params_tags = '{ "tag": [ "'.$tags_values[0].'", "'.$tags_values[1].'" ], "value": "'.$tags_values[2].'" }';
        }
        return$params_tags;
    }

}
