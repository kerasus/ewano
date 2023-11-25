<?php

class EwanoAssist {
    public function __construct(){
        $this->homePath = '/';
    }

    public function isFromEwano () {
        return (isset($_GET['ewano']) && $_GET['ewano'] == '1');
    }

    public function getCurrentPath () {
        return parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    }

    public function isHomePage () {
        return $this->homePath === $this->getCurrentPath();
    }
}
