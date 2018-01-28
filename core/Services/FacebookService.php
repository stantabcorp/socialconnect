<?php 

    namespace SocialConnect\Services;

    class FacebookService extends Service{

        public function getAuthUrl(){
            return "https://www.facebook.com/v2.11/dialog/oauth?client_id=".$this->config['services']['facebook']['app_id']."&redirect_uri=".$this->config['callback']."/auth/callback/facebook";
        }

        public function getAuthCallback(){
            $res = $this->guzzle->request('GET', 'https://graph.facebook.com/v2.11/oauth/access_token?client_id='.$this->config['services']['facebook']['app_id'].'&redirect_uri='.$this->config['callback'].'/auth/callback/facebook&client_secret='.$this->config['services']['facebook']['app_secret'].'&code='.$_GET['code']);
            $json = json_decode($res->getBody(), true);
            if(isset($json['access_token'])){
                if(!$this->config['installed']){
                    $this->createTable();
                }
                //
                $rep = null;
                $rep = $this->guzzle->request('GET', 'https://graph.facebook.com/me?fields=picture,name,id', ['http_errors' => false, 'headers' => [
                    "Authorization" => "Bearer ".$json['access_token'],
                ]]);
                $js = json_decode($rep->getBody(), true);
                $userID = $js['id'];
                //
                $result = $this->dbConn->fetchColumn("SELECT user_uuid FROM `{$this->config['bdd']['prefix']}facebook` WHERE user_id = :id", [
                    "id" => $userID,
                ]);
                if($result == null){
                    $uid = uniqid("SC-")."-F";
                    $this->dbConn->insert("`{$this->config['bdd']['prefix']}facebook`", [
                        "user_uuid" => $uid,
                        "access_token" => $json['access_token'],
                        "expires" => $json['expires_in'],
                        "user_id" => $userID,
                        "user_ip" => (isset($_SERVER['HTTP_FORWARDED_FOR'])) ? $_SERVER['HTTP_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'],
                    ]);
                }else{
                    $this->dbConn->update("`{$this->config['bdd']['prefix']}facebook`", [
                        "user_id" => $userID,
                    ],[
                        "access_token" => $json['access_token'],
                         "expires" => $json['expires_in'],
                    ]);
                    $uid = $result;
                }
                $redir_url = $this->config['success_url'];
                if($this->session->get('redirect_after_complete') != null){
                    $redir_url = $this->session->get('redirect_after_complete');
                    $this->session->destroy('redirect_after_complete');
                }
                return $redir_url."?sc=".$this->createToken($uid);
            }else{
                return $this->config['error_url'];
            }
        }

        public function createTable(){
            $this->dbConn->executeSql("CREATE TABLE IF NOT EXISTS `{$this->config['bdd']['prefix']}facebook` ( `id` INT NOT NULL AUTO_INCREMENT , `user_uuid` VARCHAR(255) NOT NULL , `access_token` VARCHAR(255) NOT NULL , `expires` INT(11) NOT NULL , `user_id` VARCHAR(255) NOT NULL , `user_ip` VARCHAR(255) NOT NULL , `registration_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP , PRIMARY KEY (`id`)) ENGINE = MyISAM;");
        }

        public function getUsers(){
            return $this->dbConn->fetchRowMany("SELECT user_uuid FROM `{$this->config['bdd']['prefix']}facebook`", []);
        }

        public function getUser($uuid){
            return $this->dbConn->fetchRowMany("SELECT * FROM `{$this->config['bdd']['prefix']}facebook` WHERE user_uuid = :uuid", [
                "uuid" => $uuid,
            ]);
        }

        public function deleteUser($uuid){
            $this->dbConn->delete("`{$this->config['bdd']['prefix']}facebook`", [
                "user_uuid" => $uuid,
            ]);
        }

    }