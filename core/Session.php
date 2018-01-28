<?php 

    namespace SocialConnect;

    class Session{

        public function __construct(){
            if(session_id() == ""){
                session_start();
            }
        }

        public function get($key){
            return isset($_SESSION[$key]) ? $_SESSION[$key] : null;
        }

        public function set($key, $value){
            $_SESSION[$key] = $value;
        }

        public function destroy($key){
            unset($_SESSION[$key]);
        }

    }