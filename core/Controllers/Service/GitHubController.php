<?php

    namespace SocialConnect\Controllers\Service;

    use \Psr\Http\Message\ServerRequestInterface as Request;
    use \Psr\Http\Message\ResponseInterface as Response;

    class GitHubController{

        private $service;

        public function __construct(){
            $this->service = new \SocialConnect\Services\GitHubService();
        }

        public function getAuthUrl(Request $request, Response $response){
            return $response->withRedirect($this->service->getAuthUrl());
        }

        public function getAuthCallBack(Request $request, Response $response){
            return $response->withRedirect($this->service->getAuthCallBack());
        }

    }