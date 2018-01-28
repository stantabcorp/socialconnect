<?php

    namespace SocialConnect\Services;

    class Service{

        protected $config;
        protected $guzzle;
        protected $dbConn;
        protected $session;

        public function __construct(){
            require './config.php';
            $this->config = $cfg;
            $this->guzzle = new \GuzzleHttp\Client();
            $pdo = new \Simplon\Mysql\PDOConnector(
                $this->config['bdd']['host'],
                $this->config['bdd']['user'],
                $this->config['bdd']['pass'],
                $this->config['bdd']['base']
            );
            $pdoConn = $pdo->connect('utf8', []);
            $this->dbConn = new \Simplon\Mysql\Mysql($pdoConn);
            $this->session = new \SocialConnect\Session();
        }

        public function getConfig(){
            return $this->config;
        }

        public function createToken($uuid){
            if(!$this->config['installed']){
                $this->dbConn->executeSql("CREATE TABLE IF NOT EXISTS `{$this->config['bdd']['prefix']}tokens` ( `id` INT NOT NULL AUTO_INCREMENT , `user_uuid` VARCHAR(255) NOT NULL , `token` VARCHAR(255) NOT NULL , `expire_time` BIGINT NOT NULL , PRIMARY KEY (`id`)) ENGINE = MyISAM;");
            }
            $tok = uniqid("tkn-", true);
            $this->dbConn->insert("`{$this->config['bdd']['prefix']}tokens`", [
                "user_uuid" => $uuid,
                "token" => $tok,
                "expire_time" => time()+30,
            ]);
            return $tok;
        }

        public function convertToken(Request $request, Response $response){
            if(!isset($_POST['token'])){
                return $response->withStatus(404)->withJson(["success" => false, "code" => 404, "message" => "Not found"])->withHeader('Access-Control-Allow-Origin', '*');
            }
            $result = $this->dbConn->fetchRow("SELECT user_uuid,expire_time FROM `{$this->config['bdd']['prefix']}tokens` WHERE token = :token", [
                "token" => $_POST['token'],
            ]);
            if($result != null){
                if(time() >= intval($result['expire_time'])){
                    return $response->withStatus(404)->withJson(["success" => false, "code" => 404, "message" => "Not found"])->withHeader('Access-Control-Allow-Origin', '*');
                }
                return $response->withJson(["success" => true, "uuid" => $result['user_uuid']])->withHeader('Access-Control-Allow-Origin', '*');
            }
            return $response->withStatus(404)->withJson(["success" => false, "code" => 404, "message" => "Not found"])->withHeader('Access-Control-Allow-Origin', '*');
        }

    }