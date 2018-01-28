<?php 

    namespace SocialConnect\Services;

    class GoogleService extends Service{

        public function getAuthUrl(){
            $client = new \Google_Client();
            $client->setClientId($this->config['services']['google']['client_id']);
            $client->setClientSecret($this->config['services']['google']['client_secret']);
            $client->setRedirectUri($this->config['callback']."/auth/callback/google");
            $client->setAccessType('offline');
            $client->addScope('profile');
            $client->setIncludeGrantedScopes(true);
            $auth_url = $client->createAuthUrl();
            return $auth_url;
        }

        public function getAuthCallback(){
            $client = new \Google_Client();
            $client->setClientId($this->config['services']['google']['client_id']);
            $client->setClientSecret($this->config['services']['google']['client_secret']);
            $client->setRedirectUri($this->config['callback']."/auth/callback/google");
            $client->setAccessType('offline');
            $client->addScope('profile');
            $client->setIncludeGrantedScopes(true);
            $client->authenticate($_GET['code']);
            $access_token = $client->getAccessToken();
            if(!$this->config['installed']){
                $this->createTable();
            }
            if(isset($access_token['access_token'])){
                $payload = $client->verifyIdToken($access_token['id_token']);
                if($payload){
                    $userid = $payload['sub'];
                    $result = $this->dbConn->fetchColumn("SELECT user_uuid FROM `{$this->config['bdd']['prefix']}google` WHERE user_id = :user_id", [
                        "user_id" => $userid,
                    ]);
                    if($result == null){
                        $uid = uniqid("SC-")."-GE";
                        $this->dbConn->insert("`{$this->config['bdd']['prefix']}google`", [
                            "user_uuid" => $uid,
                            "access_token" => $access_token['access_token'],
                            "token_type" => $access_token['token_type'],
                            "expires_in" => $access_token['expires_in'],
                            "created" => $access_token['created'],
                            "user_id" => $userid,
                            "user_infos" => json_encode([
                                "email" => $payload['email'],
                                "email_verified" => $payload['email_verified'],
                                "name" => $payload['name'],
                                "picture" => $payload['picture'],
                                "given_name" => $payload['given_name'],
                                "family_name" => $payload['family_name'],
                                "locale" => $payload['locale'],
                            ]),
                            "user_ip" => (isset($_SERVER['HTTP_FORWARDED_FOR'])) ? $_SERVER['HTTP_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'],
                        ]);
                    }else{
                        $this->dbConn->update("`{$this->config['bdd']['prefix']}google`", [
                            "user_id" => $userid,
                        ],[
                            "access_token" => $access_token['access_token'],
                            "token_type" => $access_token['token_type'],
                            "expires_in" => $access_token['expires_in'],
                            "created" => $access_token['created'],
                            "user_infos" => json_encode([
                                "email" => $payload['email'],
                                "email_verified" => $payload['email_verified'],
                                "name" => $payload['name'],
                                "picture" => $payload['picture'],
                                "given_name" => $payload['given_name'],
                                "family_name" => $payload['family_name'],
                                "locale" => $payload['locale'],
                            ]),
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
            }else{
                return $this->config['error_url'];
            }
        }

        public function createTable(){
            $this->dbConn->executeSql("CREATE TABLE IF NOT EXISTS `{$this->config['bdd']['prefix']}google` ( `id` INT NOT NULL AUTO_INCREMENT , `user_uuid` VARCHAR(255) NOT NULL , `access_token` VARCHAR(255) NOT NULL , `user_id` VARCHAR(255) NOT NULL , `token_type` VARCHAR(255) NOT NULL , `expires_in` INT NOT NULL , `user_infos` LONGTEXT NOT NULL , `user_ip` VARCHAR(255) NOT NULL , `registration_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP , `created` BIGINT NOT NULL , PRIMARY KEY (`id`)) ENGINE = MyISAM;");
        }

        public function getUsers(){
            return $this->dbConn->fetchRowMany("SELECT user_uuid FROM `{$this->config['bdd']['prefix']}google`", []);
        }

        public function getUser($uuid){
            return $this->dbConn->fetchRowMany("SELECT * FROM `{$this->config['bdd']['prefix']}google` WHERE user_uuid = :uuid", [
                "uuid" => $uuid,
            ]);
        }

        public function deleteUser($uuid){
            $this->dbConn->delete("`{$this->config['bdd']['prefix']}google`", [
                "user_uuid" => $uuid,
            ]);
        }


    }