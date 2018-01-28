<?php 

    namespace SocialConnect\Controllers\User;

    use \Psr\Http\Message\ServerRequestInterface as Request;
    use \Psr\Http\Message\ResponseInterface as Response;

    class UserController{

        private $service;

        public function __construct(){
            $this->service['facebook'] = new \SocialConnect\Services\FacebookService;
            $this->service['github']   = new \SocialConnect\Services\GitHubService;
            $this->service['google']   = new \SocialConnect\Services\GoogleService;
            $this->service['twitter']  = new \SocialConnect\Services\TwitterService;
        }

        public function getUsers(Request $request, Response $response){
            $json = [];
            foreach($this->service as $k => $v){
                $this->service[$k]->createTable();
                $json[$k] = $this->service[$k]->getUsers();
            }
            return $response->withJson($json)->withHeader('Access-Control-Allow-Origin', '*')->withStatus(200);
        }

        public function getUser(Request $request, Response $response, $uuid){
            foreach($this->service as $k => $v){
                $this->service[$k]->createTable();
                $infos = $this->service[$k]->getUser($uuid);
                if($infos != null){
                    $infos['service'] = $k;
                    return $response->withJson($infos)->withHeader('Access-Control-Allow-Origin', '*')->withStatus(200);
                }
            }
            return $response->withJson([
                "success" => false,
                "error" => "Not found"
            ])->withHeader('Access-Control-Allow-Origin', '*')->withStatus(404);
        }

        public function deleteUser(Request $request, Response $response, $uuid){
            foreach($this->service as $k => $v){
                $this->service[$k]->createTable();
                $infos = $this->service[$k]->getUser($uuid);
                if($infos != null){
                    $this->service[$k]->deleteUser($uuid);
                    return $response->withJson([
                        "success" => true,
                    ])->withHeader('Access-Control-Allow-Origin', '*')->withStatus(200);
                }
            }
            return $response->withJson([
                "success" => false,
                "error" => "Not found"
            ])->withHeader('Access-Control-Allow-Origin', '*')->withStatus(404);
        }

    }