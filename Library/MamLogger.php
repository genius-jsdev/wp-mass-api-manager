<?php

class MamLogger
{
    private static $mamlogger = null;

    public function __construct()
    { 

    }

    public static function get_instance(){
        if(self::$mamlogger == null){
            self::$mamlogger = new MamLogger();
        }
        return self::$mamlogger;
    }

    public function send_log($log)
    {
        global $wpdb;
        $wpdb->insert($wpdb->prefix . 'mam_log', array(
            'user_id' => get_current_user_id(),
            'log' => $log,
        ));
    }
}
