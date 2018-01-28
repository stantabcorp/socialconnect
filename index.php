<?php 

    session_start();

    require 'vendor/autoload.php';
    require 'config.php';

    $configuration = [
        'settings' => [
            'displayErrorDetails' => true,
        ],
    ];
    $c = new \Slim\Container($configuration);
    $app = new \Slim\App($c);

    $c = $app->getContainer();
    // $c['phpErrorHandler'] = function ($c) {
    //     return function ($request, $response, $error) use ($c) {
    //         return $c['response']
    //             ->withStatus(500)
    //             ->withJson(["success" => false, "code" => 500, "message" => "Something went wrong!"]);
    //     };
    // };
    $c['notAllowedHandler'] = function ($c) {
        return function ($request, $response, $methods) use ($c) {
            return $c['response']
                ->withStatus(405)
                ->withHeader('Allow', implode(', ', $methods))
                ->withJson(["success" => false, "code" => 405, "message" => "Method must be one of ".implode(', ', $methods)]);
        };
    };
    $c['notFoundHandler'] = function ($c) {
        return function ($request, $response) use ($c) {
            return $c['response']
                ->withStatus(404)
                ->withJson(["success" => false, "code" => 404, "message" => "Not found"]);
        };
    };

    if($cfg['auth']['enabled']){
        $app->add(new \Slim\Middleware\HttpBasicAuthentication([
            "users" => [
                $cfg['auth']['username'] => $cfg['auth']['password'],
            ],
            "path" => "/user",
            "secure" => true,
            "error" => function ($request, $response, $arguments) {
                return $response->withStatus(403)->withJson(["success" => false, "code" => 403, "message" => $arguments['message']]);
            }
        ]));
    }

    $app->get('/', \SocialConnect\Controllers\Controller::class . ':getHome')->setName('main');
    $app->post('/convert[/]', \SocialConnect\Controllers\Controller::class . ':convertToken')->setName('convert');

    $app->group('/auth', function(){
        $this->get('/facebook[/]', \SocialConnect\Controllers\Service\FacebookController::class.':getAuthUrl')->setName('facebook.auth');
        $this->get('/twitter[/]', \SocialConnect\Controllers\Service\TwitterController::class.':getAuthUrl')->setName('twitter.auth');
        $this->get('/google[/]', \SocialConnect\Controllers\Service\GoogleController::class.':getAuthUrl')->setName('google.auth');
        $this->get('/github[/]', \SocialConnect\Controllers\Service\GithubController::class.':getAuthUrl')->setName('github.auth');
    });

    $app->group('/auth/callback', function(){
        $this->get('/facebook[/]', \SocialConnect\Controllers\Service\FacebookController::class.':getAuthCallback')->setName('facebook.auth.callback');
        $this->get('/twitter[/]', \SocialConnect\Controllers\Service\TwitterController::class.':getAuthCallback')->setName('twitter.auth.callback');
        $this->get('/google[/]', \SocialConnect\Controllers\Service\GoogleController::class.':getAuthCallback')->setName('google.auth.callback');
        $this->get('/github[/]', \SocialConnect\Controllers\Service\GithubController::class.':getAuthCallback')->setName('github.auth.callback');
    });

    $app->group('/user', function(){
        $this->get('[/]', \SocialConnect\Controllers\User\UserController::class.':getUsers')->setName('users');
        $this->get('/{token}[/]', \SocialConnect\Controllers\User\UserController::class.':getUser')->setName('user');
        $this->delete('/{token}[/]', \SocialConnect\Controllers\User\UserController::class.':deleteUser')->setName('user.delete');
    });
    

    $app->run();