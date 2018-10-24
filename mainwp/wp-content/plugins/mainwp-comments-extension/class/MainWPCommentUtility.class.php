<?php
class MainWPCommentUtility
{
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
        return (object)$outputSite;
    }

    public static function getTimestamp($timestamp)
    {
        $gmtOffset = get_option('gmt_offset');

        return ($gmtOffset ? ($gmtOffset * HOUR_IN_SECONDS) + $timestamp : $timestamp);
    }

    public static function formatTimestamp($timestamp)
    {
        return date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $timestamp);
    }

    public static function sortmulti($array, $index, $order, $natsort = FALSE, $case_sensitive = FALSE)
    {
        $sorted = array();
        if (is_array($array) && count($array) > 0) {
            foreach (array_keys($array) as $key)
                $temp[$key] = $array[$key][$index];
            if (!$natsort) {
                if ($order == 'asc')
                    asort($temp);
                else
                    arsort($temp);
            }
            else
            {
                if ($case_sensitive === true)
                    natsort($temp);
                else
                    natcasesort($temp);
                if ($order != 'asc')
                    $temp = array_reverse($temp, TRUE);
            }
            foreach (array_keys($temp) as $key)
                if (is_numeric($key))
                    $sorted[] = $array[$key];
                else
                    $sorted[$key] = $array[$key];
            return $sorted;
        }
        return $sorted;
    }

    public static function getSubArrayHaving($array, $index, $value)
    {
        $output = array();
        if (is_array($array) && count($array) > 0) {
            foreach ($array as $arrvalue)
            {
                if ($arrvalue[$index] == $value) $output[] = $arrvalue;
            }
        }
        return $output;
    }

    public static function startsWith($haystack, $needle)
    {
        return !strncmp($haystack, $needle, strlen($needle));
    }

    public static function endsWith($haystack, $needle)
    {
        $length = strlen($needle);
        if ($length == 0) {
            return true;
        }

        return (substr($haystack, -$length) === $needle);
    }

    public static function getNiceURL($pUrl, $showHttp = false)
    {
        $url = $pUrl;

        if (self::startsWith($url, 'http://'))
        {
            if (!$showHttp) $url = substr($url, 7);
        }
        else if (self::startsWith($pUrl, 'https://'))
        {
            if (!$showHttp) $url = substr($url, 8);
        }
        else
        {
            if ($showHttp) $url = 'http://'.$url;
        }

        if (self::endsWith($url, '/'))
        {
            if (!$showHttp) $url = substr($url, 0, strlen($url) - 1);
        }
        else
        {
            $url = $url . '/';
        }
        return $url;
    }
}