<?php
class MainWPCommentDB
{
    //Support old & new versions of wordpress (3.9+)
    public static function use_mysqli()
    {
        /** @var $wpdb wpdb */
        if (!function_exists('mysqli_connect')) return false;

        global $wpdb;
        return ($wpdb->dbh instanceof mysqli);
    }

    public static function fetch_object($result)
    {		
        if (self::use_mysqli())
        {			
            return mysqli_fetch_object($result);
        }
        else
        {			
            return mysql_fetch_object($result);
        }
    }

    public static function free_result($result)
    {
        if (self::use_mysqli())
        {
            return mysqli_free_result($result);
        }
        else
        {
            return mysql_free_result($result);
        }
    }
}