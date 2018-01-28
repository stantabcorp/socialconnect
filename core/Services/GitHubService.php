<?php 

    namespace SocialConnect\Services;

    class GitHubService extends Service{

        public function getAuthUrl(){
            return "https://github.com/login/oauth/authorize?scope=user:email&client_id=".$this->config['services']['github']['client_id'];
        }

        public function getAuthCallback(){
            $res = $this->guzzle->request('POST', 'https://github.com/login/oauth/access_token',[
                "form_params" => [
                    "client_id" => $this->config['services']['github']['client_id'],
                    "client_secret" => $this->config['services']['github']['client_secret'],
                    "code" => $_GET['code'],
                    "accept" => "json",
                ],
            ]);
            $data = explode("&", $res->getBody());
            $rep = [];
            foreach($data as $k => $v){
                $d = explode("=", $v);
                $rep[$d[0]] = $d[1]; 
            }
            if(isset($rep['access_token'])){
                if(!$this->config['installed']){
                    $this->createTable();
                }
                $res = $this->guzzle->request('GET', 'https://api.github.com/user?access_token='.$rep['access_token']);
                $json = json_decode($res->getBody(), true);
                if(!isset($json['id'])){
                    return $response->withRedirect($this->config['error_url']);
                }else{
                    $result = $this->dbConn->fetchColumn("SELECT user_uuid FROM `{$this->config['bdd']['prefix']}github` WHERE github_id = :token", [
                        "token" => $json['id'],
                    ]);
                    if($result == null){
                        $uid = uniqid("SC-")."-G";
                        $this->dbConn->insert("`{$this->config['bdd']['prefix']}github`", [
                            "user_uuid" => $uid,
                            "github_id" => $json['id'],
                            "scope" => $rep['scope'],
                            "token_type" => $rep['token_type'],
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
            }else{
                return $this->config['error_url'];
            }
        }

        public function createTable(){
            $this->dbConn->executeSql("CREATE TABLE IF NOT EXISTS `{$this->config['bdd']['prefix']}github` ( `id` INT NOT NULL AUTO_INCREMENT , `user_uuid` VARCHAR(255) NOT NULL , `github_id` VARCHAR(255) NOT NULL , `scope` VARCHAR(255) NOT NULL , `token_type` VARCHAR(255) NOT NULL , `user_ip` VARCHAR(255) NOT NULL , `registration_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP , PRIMARY KEY (`id`)) ENGINE = MyISAM;");
        }

        public function getUsers(){
            return $this->dbConn->fetchRowMany("SELECT user_uuid FROM `{$this->config['bdd']['prefix']}github`", []);
        }

        public function getUser($uuid){
            return $this->dbConn->fetchRowMany("SELECT * FROM `{$this->config['bdd']['prefix']}github` WHERE user_uuid = :uuid", [
                "uuid" => $uuid,
            ]);
        }

        public function deleteUser($uuid){
            $this->dbConn->delete("`{$this->config['bdd']['prefix']}github`", [
                "user_uuid" => $uuid,
            ]);
        }


    }