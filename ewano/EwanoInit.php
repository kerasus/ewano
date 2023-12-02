<?php

include_once('EwanoApi.php');
include_once('EwanoAuth.php');
include_once('EwanoAssist.php');
include_once('EwanoReport.php');
include_once('EwanoJavaScripts.php');

class EwanoInit {
    public function __construct(){
        $this->api = new EwanoApi();
        $this->usernameKey = 'mobile';
        $this->assist = new EwanoAssist();
        $this->ewanoReport = new EwanoReport();
        $this->javaScripts = new EwanoJavaScripts();
        $this->development = $this->assist->development;
        if ($this->development) {
            $this->assist->setEwanoFlagInSession();
        }
    }

    public function prepareForIncomingUser () {
        if (!$this->assist->isFromEwano()) {
            return $this;
        }
        $this->assist->setEwanoFlagInSession();
        $this->ewanoUserId = $this->assist->getEwanoUserIdInQuery();
        $this->user = $this->api->getUserFromService($this->ewanoUserId);
        if (!isset($this->user)) {
            return $this;
        }
        $this->auth = new EwanoAuth($this->user);
        if (!$this->auth->isUserLoggedIn()) {
            $this->auth->login();
        }
        return $this;
    }

    public function prepareEwanoGateway () {
        if ($this->assist->hasEwanoFlag()) {
            include_once('EwanoGateway.php');
        }
        return $this;
    }

    public function onWebAppReady () {
        $this->javaScripts->onWebAppReady();
        return $this;
    }
}
