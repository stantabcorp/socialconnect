<?php 

    $cfg = [
        "bdd" => [ 
            "user" => "root",
            "pass" => "",
            "host" => "localhost",
            "base" => "socialconnect",
            "prefix" => "sc_",
        ],
        "services" => [
            "facebook" => [
                "app_id" => "",
                "app_secret" => "",
            ],
            "twitter" => [
                "consumer_key" => "",
                "consumer_secret" => "",
            ],
            "google" => [
                "client_id" => "",
                "client_secret" => "",
            ],
            "github" => [
                "client_id" => "",
                "client_secret" => "",
            ],
        ],
        "auth" => [
            "enabled" => true,
            "username" => "test",
            "password" => "test",
        ],
        "callback" => "http://localhost/socialconnect",
        "error_url" => "http://localhost/accounts?err",
        "success_url" => "http://localhost/accounts",
        "installed" => false,
    ];