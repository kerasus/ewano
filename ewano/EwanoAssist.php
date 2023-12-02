<?php

class EwanoAssist {
    public function __construct(){
        $this->development = false;
        $this->sessionKey = 'is_from_ewano';
        $this->homePath = '/';
        $this->userIdInQueryParamsKye = 'id';
    }

    public function isFromEwano () {
        return ($this->isHomePage() && $this->hasEwanoUserIdInQuery());
    }

    public function hasEwanoUserIdInQuery () {
        return isset($_GET[$this->userIdInQueryParamsKye]);
    }

    public function getEwanoUserIdInQuery () {
        if ($this->hasEwanoUserIdInQuery()) {
            return $_GET[$this->userIdInQueryParamsKye];
        }

        return null;
    }

    public function getCurrentPath () {
        $url = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        return str_replace(site_url(), '', $url);
//        return parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    }

    public function isHomePage () {
        return $this->homePath === $this->getCurrentPath();
    }

    public function hasEwanoFlag () {
        if(!session_id()) {
            session_start();
        }
        if(isset($_SESSION[$this->sessionKey])) {
            return $_SESSION[$this->sessionKey];
        } else {
            return false;
        }
    }

    public function setEwanoFlagInSession () {
        if(!session_id()) {
            session_start();
        }
        $_SESSION[$this->sessionKey] = true;
    }
}
