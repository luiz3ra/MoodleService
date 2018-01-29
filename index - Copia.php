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
            $getData = $moodleCtrl->validationGet($_GET);

            if ($getData->return) {
                $getUser = $moodleCtrl->coreUserGetUsers($getData);

                if ($getUser->return && !empty($_GET["redirect"])) {
                    header("Location: https://www.facon.edu.br/webservice/FormLogin.php?username={$getUser->dados["username"]}&password={$getUser->dados["password"]}");
                } else {
                    if ($_GET["returnType"] == "xml") {
                        $moodleUtil->toXml($getUser);
                    } else {
                        $moodleUtil->toJson($getUser);
                    }
                }
            } else {
                $moodleUtil->toXml($getData);
            }

            break;
        case "core_user_create_users":
            $getData = $moodleCtrl->validationGet($_GET);

            if ($getData->return) {
                $createUser = $moodleCtrl->coreUserCreateUsers($getData);

                if ($_GET["returnType"] == "xml") {
                    $moodleUtil->toXml($createUser);
                } else {
                    $moodleUtil->toJson($createUser);
                }
            } else {
                $moodleUtil->toXml($getData);
            }

            break;
        case "core_user_update_users":
            $getData = $moodleCtrl->validationGet($_GET);

            if ($getData->return) {
                $updateUser = $moodleCtrl->coreUserUpdateUsers($getData);

                if ($_GET["returnType"] == "xml") {
                    $moodleUtil->toXml($updateUser);
                } else {
                    $moodleUtil->toJson($updateUser);
                }
            } else {
                $moodleUtil->toXml($getData);
            }

            break;

        case "enrol_manual_enrol_users":
            $getData = $moodleCtrl->validationGet($_GET);

            if ($getData->return) {
                $updateUser = $moodleCtrl->enrolManualEnrolUsers($getData);

                if ($_GET["returnType"] == "xml") {
                    $moodleUtil->toXml($updateUser);
                } else {
                    $moodleUtil->toJson($updateUser);
                }
            } else {
                $moodleUtil->toXml($getData);
            }

            break;

        case "get_descryp":
            $getData = $moodleCtrl->validationGet($_GET);

            print_r($getData);

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