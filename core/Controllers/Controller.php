<?php 

    namespace SocialConnect\Controllers;

    use \Psr\Http\Message\ServerRequestInterface as Request;
    use \Psr\Http\Message\ResponseInterface as Response;

    class Controller{

        public function getHome(Request $request, Response $response){
            return $response->withJson([
                "Application-Name" => "SocialConnect", 
                "Application-Author" => "STAN-TAb Corp.",
                "Application-Description" => "User managment with social media",
                "Application-Owner" => "STAN-TAb Corp.",
                "Application-Url" => "https://github.com/stantabcorp/socialconnect",
            ])->withHeader('Access-Control-Allow-Origin', '*')->withStatus(200);
        }

        public function convertToken(Request $request, Response $response){
            $service = new \SocialConnect\Service\Service;
            return $service->convertToken($request, $response);
        }

    }