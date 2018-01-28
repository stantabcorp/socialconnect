<?php

    namespace SocialConnect\Controllers\Service;

    use \Psr\Http\Message\ServerRequestInterface as Request;
    use \Psr\Http\Message\ResponseInterface as Response;

    class TwitterController{

        private $service;

        public function __construct(){
            $this->service = new \SocialConnect\Services\TwitterService();
        }

        public function getAuthUrl(Request $request, Response $response){
            return $response->withRedirect($this->service->getAuthUrl());
        }

        public function getAuthCallBack(Request $request, Response $response){
            return $response->withRedirect($this->service->getAuthCallBack());
        }

    }