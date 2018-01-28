<?php 

    namespace SocialConnect\Services;

    class TwitterService extends Service{

        public function getAuthUrl(){
            $connection = new \Abraham\TwitterOAuth\TwitterOAuth($this->config['services']['twitter']['consumer_key'], $this->config['services']['twitter']['consumer_secret']);
            $request_token = $connection->oauth('oauth/request_token', array('oauth_callback' => $this->config['callback']."/auth/callback/twitter"));
            $this->session->set('twitter_oauth_token', $request_token['oauth_token']);
            $this->session->set('twitter_oauth_token_secret', $request_token['oauth_token_secret']);
            $url = $connection->url('oauth/authorize', array('oauth_token' => $request_token['oauth_token']));
            return $url;
        }

        public function getAuthCallback(){
            $request_token = [];
            $request_token['oauth_token'] = $this->session->get('twitter_oauth_token');
            $request_token['oauth_token_secret'] = $this->session->get('twitter_oauth_token_secret');
            if (!isset($_GET['oauth_token']) || $request_token['oauth_token'] !== $_GET['oauth_token']) {
                return $this->config['error_url'];
            }
            $connection = new \Abraham\TwitterOAuth\TwitterOAuth(
                $this->config['services']['twitter']['consumer_key'],
                $this->config['services']['twitter']['consumer_secret'],
                $request_token['oauth_token'],
                $request_token['oauth_token_secret']
            );
            $access_token = $connection->oauth("oauth/access_token", ["oauth_verifier" => $_GET['oauth_verifier']]);
            if(!$this->config['installed']){
                $this->createTable();
            }
            $result = $this->dbConn->fetchColumn("SELECT `user_uuid` FROM `{$this->config['bdd']['prefix']}twitter` WHERE oauth_token = :token AND oauth_token_secret = :osecret", [
                "token" => $access_token['oauth_token'],
                "osecret" => $access_token['oauth_token_secret'],
            ]);
            if($result == null){
                $uid = uniqid("SC-")."-T";
                $this->dbConn->insert("`{$this->config['bdd']['prefix']}twitter`", [
                    "user_uuid" => $uid,
                    "oauth_token" => $access_token['oauth_token'],
                    "oauth_token_secret" => $access_token['oauth_token_secret'],
                    "twitter_user_id" => $access_token['user_id'],
                    "twitter_screenname" => $access_token['screen_name'],
                    "user_ip" => (isset($_SERVER['HTTP_FORWARDED_FOR'])) ? $_SERVER['HTTP_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'],
                ]);
            }else{
                $uid = $result;
            }
            $redir_url = $this->config['success_url'];
            if($this->session->get('redirect_after_complete') != null){
                $redir_url = $this->session->get('redirect_after_complete');
                $this->session->destroy('redirect_after_complete');
            }
            return $redir_url."?sc=".$this->createToken($uid);
        }

        public function createTable(){
            $this->dbConn->executeSql("CREATE TABLE IF NOT EXISTS `{$this->config['bdd']['prefix']}twitter` ( `id` INT NOT NULL AUTO_INCREMENT , `user_uuid` VARCHAR(255) NOT NULL , `oauth_token` VARCHAR(255) NOT NULL , `oauth_token_secret` VARCHAR(255) NOT NULL , `twitter_user_id` VARCHAR(255) NOT NULL , `twitter_screenname` VARCHAR(255) NOT NULL , `user_ip` VARCHAR(255) NOT NULL , `registration_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP , PRIMARY KEY (`id`)) ENGINE = MyISAM;");
        }

        public function getUsers(){
            return $this->dbConn->fetchRowMany("SELECT user_uuid FROM `{$this->config['bdd']['prefix']}twitter`", []);
        }

        public function getUser($uuid){
            return $this->dbConn->fetchRowMany("SELECT * FROM `{$this->config['bdd']['prefix']}twitter` WHERE user_uuid = :uuid", [
                "uuid" => $uuid,
            ]);
        }

        public function deleteUser($uuid){
            $this->dbConn->delete("`{$this->config['bdd']['prefix']}twitter`", [
                "user_uuid" => $uuid,
            ]);
        }

    }