<?php
class MainWPWordfenceUtility
{
    public static function formatTimestamp($timestamp)
    {
        return date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $timestamp);
    }
    
    static function ctype_digit($str)
    {
        return (is_string($str) || is_int($str) || is_float($str)) && preg_match('/^\d+\z/', $str);
    }
    
    public static function mapSite(&$website, $keys)
    {
        $outputSite = array();
        foreach ($keys as $key)
        {
            $outputSite[$key] = $website->$key;
        }
        return $outputSite;
    }  
    
        
    public static function isAdmin($user = false){
        if($user){
            if(is_multisite()){
                if(user_can($user, 'manage_network')){
                        return true;
                }
            } else {
                if(user_can($user, 'manage_options')){
                        return true;
                }
            }
        } else {
            if(is_multisite()){
                if(current_user_can('manage_network')){
                        return true;
                }
            } else {
                if(current_user_can('manage_options')){
                        return true;
                }
            }
        }
        return false;
    }
    
    public static function getSiteBaseURL(){
        return rtrim(site_url(), '/') . '/';
    }
    
    public static function bigRandomHex(){
            return dechex(rand(0, 2147483647)) . dechex(rand(0, 2147483647)) . dechex(rand(0, 2147483647));
    }
    
    static function getGetDataAuthed($website, $paramValue, $paramName = 'where', $open_location = "")
    {
        $params = array();
        if ($website && $paramValue != '')
        {
            $nonce = rand(0,9999);
            if (($website->nossl == 0) && function_exists('openssl_verify')) {
                $nossl = 0;
                openssl_sign($paramValue . $nonce, $signature, base64_decode($website->privkey));
            }
            else
            {
                $nossl = 1;
                $signature = md5($paramValue . $nonce . $website->nosslkey);
            }
            $signature = base64_encode($signature);
           
            $params = array(
                'login_required' => 1,
                'user' => $website->adminname,
                'mainwpsignature' => rawurlencode($signature),
                'nonce' => $nonce,
                'nossl' => $nossl,
                'open_location' => $open_location,
                $paramName => rawurlencode($paramValue)
            );
        }

        $url = (isset($website->siteurl) && $website->siteurl != '' ? $website->siteurl : $website->url);
        $url .= (substr($url, -1) != '/' ? '/' : '');
        $url .= '?';

        foreach ($params as $key => $value)
        {
            $url .= $key . '=' . $value . '&';
        }

        return rtrim($url, '&');
    }
    
    public static function isValidIP($IP){
        return filter_var($IP, FILTER_VALIDATE_IP) !== false;
    }       
    
}