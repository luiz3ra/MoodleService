<?php

ini_set('display_errors', 1);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL);

require_once('config/config.php');
require_once('app/controller/MoodleController.php');

$moodleCtrl = new MoodleController();
$moodleUtil = new Returner();

if (!empty($_GET["function"])) {
    switch ($_GET["function"]) {
        case "core_user_get_users":
            $getUser = $moodleCtrl->coreUserGetUsers($_GET);

            if(isset($_GET["redirect"])) {
                if($_GET["redirect"]) {
                    header("Location: /FormLogin.php?username={$_GET["username"]}&password={$_GET["password"]}");
                }
            }

            if ($_GET["returnType"] == "xml") {
                $moodleUtil->toXml($getUser);
            } else {
                $moodleUtil->toJson($getUser);
            }

        break;

        case "core_user_create_users":
            $getUser = $moodleCtrl->coreUserCreateUsers($_GET);

            if ($_GET["returnType"] == "xml") {
                $moodleUtil->toXml($getUser);
            } else {
                $moodleUtil->toJson($getUser);
            }      

        break;            

        case "core_user_delete_users":
            $getUser = $moodleCtrl->coreUserDeleteUsers($_GET);

            if ($_GET["returnType"] == "xml") {
                $moodleUtil->toXml($getUser);
            } else {
                $moodleUtil->toJson($getUser);
            }                      

        break;            

        default:
            print_r("Função inválida!");
        break;
    }    

    die();
} else {

    print_r("Função inválida!");

    die();
}