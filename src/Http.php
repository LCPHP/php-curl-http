<?php
namespace niklaslu;

class Http {
    

    /**
     * http curl get
     * @param string $url
     * @param string $data_type
     * @return mixed|boolean
     */
    static public function http_get($url, $data_type='json') {
    
        $cl = curl_init();
        if(stripos($url, 'https://') !== FALSE) {
            curl_setopt($cl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($cl, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($cl, CURLOPT_SSLVERSION, 1);
        }
        curl_setopt($cl, CURLOPT_URL, $url);
        curl_setopt($cl, CURLOPT_RETURNTRANSFER, 1 );
        $content = curl_exec($cl);
        $status = curl_getinfo($cl);
        curl_close($cl);
        if (isset($status['http_code']) && $status['http_code'] == 200) {
            if ($data_type == 'json') {
                $content = json_decode($content , true);
            }
            return $content;
        } else {
            return FALSE;
        }
    }
    
    /**
     * http curl post
     * @param string $url
     * @param unknown $fields
     * @param string $data_type
     * @return mixed|boolean
     */
    static public function http_post($url, $fields, $data_type='json') {
    
        $cl = curl_init();
        if(stripos($url, 'https://') !== FALSE) {
            curl_setopt($cl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($cl, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($cl, CURLOPT_SSLVERSION, 1);
        }
        curl_setopt($cl, CURLOPT_URL, $url);
        curl_setopt($cl, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt($cl, CURLOPT_POST, true);
        // convert @ prefixed file names to CurlFile class
        // since @ prefix is deprecated as of PHP 5.6
        if (class_exists('\CURLFile')) {
            foreach ($fields as $k => $v) {
                if (strpos($v, '@') === 0) {
                    $v = ltrim($v, '@');
                    $fields[$k] = new \CURLFile($v);
                }
            }
        }
        curl_setopt($cl, CURLOPT_POSTFIELDS, $fields);
        $content = curl_exec($cl);
        $status = curl_getinfo($cl);
        curl_close($cl);
        if (isset($status['http_code']) && $status['http_code'] == 200) {
            if ($data_type == 'json') {
                $content = json_decode($content ,true);
            }
            return $content;
        } else {
            return FALSE;
        }
    }
    
    /**
     * 微信api不支持中文转义的json结构
     * @param array $arr
     */
    static function json_encode($arr) {
        if (count($arr) == 0) return "[]";
        $parts = array ();
        $is_list = false;
        //Find out if the given array is a numerical array
        $keys = array_keys ( $arr );
        $max_length = count ( $arr ) - 1;
        if (($keys [0] === 0) && ($keys [$max_length] === $max_length )) { //See if the first key is 0 and last key is length - 1
            $is_list = true;
            for($i = 0; $i < count ( $keys ); $i ++) { //See if each key correspondes to its position
                if ($i != $keys [$i]) { //A key fails at position check.
                    $is_list = false; //It is an associative array.
                    break;
                }
            }
        }
        foreach ( $arr as $key => $value ) {
            if (is_array ( $value )) { //Custom handling for arrays
                if ($is_list)
                    $parts [] = self::json_encode ( $value ); /* :RECURSION: */
                    else
                        $parts [] = '"' . $key . '":' . self::json_encode ( $value ); /* :RECURSION: */
            } else {
                $str = '';
                if (! $is_list)
                    $str = '"' . $key . '":';
                    //Custom handling for multiple data types
                    if (!is_string ( $value ) && is_numeric ( $value ) && $value<2000000000)
                        $str .= $value; //Numbers
                        elseif ($value === false)
                        $str .= 'false'; //The booleans
                        elseif ($value === true)
                        $str .= 'true';
                        else
                            $str .= '"' . addslashes ( $value ) . '"'; //All other things
                            // :TODO: Is there any more datatype we should be in the lookout for? (Object?)
                            $parts [] = $str;
            }
        }
        $json = implode ( ',', $parts );
        if ($is_list)
            return '[' . $json . ']'; //Return numerical JSON
            return '{' . $json . '}'; //Return associative JSON
    }
}