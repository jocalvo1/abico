<?php
// Handle logout request
if (isset($_GET['destroy']) && $_GET['destroy'] == 1) {
    require_once 'session.php';
    session::init();
    session::destroy();
}

class session {
    public static function init() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    public static function set($key, $value) {
        $_SESSION[$key] = $value;
    }
    
    public static function get($key) {
        return isset($_SESSION[$key]) ? $_SESSION[$key] : null;
    }
    
    public static function destroy() {
        session_destroy();
        session_unset();
        header("Location: /abico/login.php");
        exit();
    }
    
    public static function checkLogin() {
        self::init();
        if (self::get("login") == false) {
            self::destroy();
        }
    }
}


// Checks if may naka log nga user or not. Initializes new session whenever may bag o nga user nga nakalog in