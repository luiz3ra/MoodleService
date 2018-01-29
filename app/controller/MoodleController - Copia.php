<?php

include_once("MoodleUtil.inc.php");

class MoodleController extends Returner {

    public function coreUserGetUsers($data, $update = null) {
        header('Content-Type: text/plain');

        $ret = new Returner();
        $curl = new Curl;
        $serverUrl = URLMOODLE . '/webservice/rest/server.php' . '?wstoken=' . TOKEN . '&wsfunction=core_user_get_users&moodlewsrestformat=json';

        try {
            $user = new stdClass();

            $user->key = "username";
            $user->value = $data->dados["username"];
            $users = array($user);
            $params = array('criteria' => $users);

            $resp = json_decode($curl->post($serverUrl, $params));

            if (is_object($resp)) {
                if (!empty($resp->users)) {
                    $ret->addDados("id", $resp->users[0]->id);
                    $ret->addDados("username", $resp->users[0]->username);
                    $ret->addDados("password", $data->dados["password"]);
                    $ret->addDados("email", $resp->users[0]->email);
                    $ret->addMsg("Usuário já existe");
                    $ret->setError(0);
                    $ret->setReturn(true);
                } else {
                    $ret->addMsg("Usuário não existe");
                    $ret->setError(1);
                    $ret->setReturn(false);
                }
            } else {
                print_r("Error: 39");
                die();
            }

            return $ret;
        } catch (Exception $exc) {
            return $exc;
        }
    }

    public function coreUserCreateUsers($data) {
        header('Content-Type: text/plain');

        $ret = new Returner();
        $curl = new Curl;
        $serverUrl = URLMOODLE . '/webservice/rest/server.php' . '?wstoken=' . TOKEN . '&wsfunction=core_user_create_users&moodlewsrestformat=json';

        try {
            $name = explode(" ", $data->dados["name"]);
            $firstname = "";

            for ($i = 0; $i < (count($name) - 1); $i++) {
                $firstname .= $name[$i] . " ";
            }

            $lastname = explode(" ", $data->dados["name"]);
            $lastname = ltrim(rtrim($lastname[count($lastname) - 1]));

            $user1 = new stdClass();
            $user1->username = $data->dados["username"];
            $user1->password = $this->Decrypting($data->dados["password"]);
            $user1->firstname = $firstname;
            $user1->lastname = $lastname;
            $user1->email = ($data->dados["username"] . "@alunosnet.com");
            $user1->auth = "manual";
            $user1->idnumber = "";
            $user1->lang = "pt_br";
            $user1->mailformat = 0;
            $user1->country = "BR";
            $user1->customfields = array(
                array("type" => "institution", "value" => $data->dados["institution"]),
                array("type" => "email", "value" => $data->dados["email"])
            );

            $users = array($user1);
            $params = array('users' => $users);

            $resp = json_decode($curl->post($serverUrl, $params));

            if (is_object($resp)) {
                if (preg_match("/Username already exists/", $resp->debuginfo)) {
                    $ret->addMsg("Usuario ja existe");
                    $ret->setError(0);
                } else {
                    $ret->addMsg($resp->debuginfo);
                }

                $ret->setReturn(false);
            } else {
                if (is_array($resp)) {
                    $enrolCourse = $this->enrolManualEnrolUsersInternal($resp[0]->id, $data->dados["course"]);

                    $ret->addDados("id", $resp[0]->id);
                    $ret->addDados("username", $resp[0]->username);
                    $ret->addDados("email", $data->dados["email"]);
                    $ret->addMsg("Usuário cadastrado com sucesso");
                    $ret->mergeMsg($enrolCourse->msg);
                    $ret->setReturn(true);
                } else {
                    print_r($resp);
                    die();
                }
            }

            return $ret;
        } catch (Exception $exc) {
            return $exc;
        }
    }

    public function coreUserUpdateUsers($data) {
        header('Content-Type: text/plain');

        $ret = new Returner();
        $curl = new Curl;
        $serverUrl = URLMOODLE . '/webservice/rest/server.php' . '?wstoken=' . TOKEN . '&wsfunction=core_user_update_users&moodlewsrestformat=json';

        try {
            $getUser = $this->coreUserGetUsers($data, true);

            if ($getUser->getReturn()) {

                $name = explode(" ", $data->dados["name"]);
                $firstname = "";

                for ($i = 0; $i < (count($name) - 1); $i++) {
                    $firstname .= $name[$i] . " ";
                }

                $lastname = explode(" ", $data->dados["name"]);
                $lastname = ltrim(rtrim($lastname[count($lastname) - 1]));

                $user1 = new stdClass();
                $user1->id = $getUser->dados["id"];
                $user1->password = $this->Decrypting($data->dados["password"]);
                $user1->firstname = ltrim(rtrim($firstname));
                $user1->lastname = $lastname;
                $user1->email = $data->dados["email"];
                $user1->suspended = $data->dados["suspended"];
                $users = array($user1);
                $params = array('users' => $users);

                $resp = json_decode($curl->post($serverUrl, $params));

                if (is_object($resp)) {
                    foreach ($resp as $key => $value) {
                        $ret->addDados($key, $value);
                    }
                    $ret->addMsg("Erro ao atualizar");
                    $ret->setReturn(false);
                } else {
                    $ret->addDados("id", $getUser->dados["id"]);
                    $ret->addDados("name", $firstname . $lastname);
                    $ret->addDados("email", $data->dados["email"]);
                    $ret->addMsg("Usuário atualizado com sucesso");
                    $ret->setReturn(true);
                }
            } else {
                $ret->addDados("username", $data->dados["username"]);
                $ret->addDados("email", $data->dados["email"]);
                $ret->addMsg("Usuário não existe");
                $ret->setError(1);
                $ret->setReturn(false);
            }

            return $ret;
        } catch (Exception $exc) {
            return $exc;
        }
    }

    public function enrolManualEnrolUsersInternal($id, $course) {
        header('Content-Type: text/plain');

        $ret = new Returner();
        $curl = new Curl;
        $serverUrl = URLMOODLE . '/webservice/rest/server.php' . '?wstoken=' . TOKEN . '&wsfunction=enrol_manual_enrol_users&moodlewsrestformat=json';

        try {
            $arrayCourse = explode("/", $course);

            foreach (array_filter($arrayCourse) as $key => $value) {
                $enrolment = new stdClass();

                $enrolment->roleid = 5;
                $enrolment->userid = $id;
                $enrolment->courseid = $value;
                $enrolments = array($enrolment);
                $params = array('enrolments' => $enrolments);

                $resp = json_decode($curl->post($serverUrl, $params));

                if (is_object($resp)) {
                    $ret->addMsg("Curso de id: ({$value}) não existe");
                }
            }

            return $ret;
        } catch (Exception $exc) {
            return $exc;
        }
    }

    public function enrolManualEnrolUsers($data) {
        header('Content-Type: text/plain');

        $ret = new Returner();
        $curl = new Curl;
        $serverUrl = URLMOODLE . '/webservice/rest/server.php' . '?wstoken=' . TOKEN . '&wsfunction=enrol_manual_enrol_users&moodlewsrestformat=json';

        try {
            $getUser = $this->coreUserGetUsers($data);

            if ($getUser->getReturn()) {

                $arrayCourse = explode("/", $data->dados["course"]);
                $erroCount = 0;

                foreach (array_filter($arrayCourse) as $key => $value) {
                    $enrolment = new stdClass();

                    $getCourse = $this->coreCourseGetCourses($value);

                    if (is_object($getCourse)) {
                        foreach ($getCourse as $key => $value) {
                            $ret->addDados($key, $value);
                        }
                        $ret->addMsg("Erro ao atualizar");
                        $ret->setReturn(false);

                        return $ret;
                        die();
                    }

                    if (!empty($getCourse)) {
                        $enrolment->roleid = 5;
                        $enrolment->userid = $getUser->getDados("id");
                        $enrolment->courseid = $value;
                        $enrolments = array($enrolment);
                        $params = array('enrolments' => $enrolments);

                        $resp = json_decode($curl->post($serverUrl, $params));
                        $ret->setReturn(true);
                    } else {
                        $erroCount++;
                        $ret->setError(3);
                    }
                }

                if ($ret->getError() == 3) {
                    if ($erroCount > 1) {
                        $ret->addMsg("{$erroCount} cursos não existem");
                    } else {
                        $ret->addMsg("{$erroCount} curso não existe");
                    }

                    $ret->setError(3);
                    $ret->setReturn(false);
                } else {
                    $ret->addMsg("Curso(s) cadastrados(s)");
                    $ret->setReturn(true);
                }
            }
            return $ret;
        } catch (Exception $exc) {
            return $exc;
        }
    }

    public function coreCourseGetCourses($idCourse) {
        header('Content-Type: text/plain');

        $ret = new Returner();
        $curl = new Curl;
        $serverUrl = URLMOODLE . '/webservice/rest/server.php' . '?wstoken=' . TOKEN . '&wsfunction=core_course_get_courses&moodlewsrestformat=json';

        $params = array(
            "options" => array(
                "ids" => array(
                    $idCourse
                )
            )
        );

        try {
            $resp = json_decode($curl->post($serverUrl, $params));

            return $resp;
        } catch (Exception $e) {
            return $e;
        }
    }

    public function Decrypting($data) {
        $arrayUrl = "";

        try {
            if (is_array($data) || is_object($data)) {
                foreach ($data as $key => $value) {
                    $strConvert = "";

                    $arrayString = (explode("-", $value));

                    $q = 0;

                    for ($Count = (count($arrayString) - 1); $Count >= 0; $Count--) {
                        if (count($arrayString) > 1) {
                            if (substr($arrayString[$Count], 0, 1)) {
                                $pass = substr($arrayString[$Count], 3);

                                $strConvert .= chr($pass - ($q + 7));
                            } else {
                                $pass = substr($arrayString[$Count], 3);

                                $strConvert .= chr($pass + ($q - 7));
                            }

                            $q++;
                        } else {
                            $strConvert = $value;
                        }
                    }

                    $arrayUrl[$key] = utf8_encode($strConvert);
                }
            } else {
                $strConvert = "";

                $arrayString = (explode("-", $data));

                $q = 0;

                for ($Count = (count($arrayString) - 1); $Count >= 0; $Count--) {
                    if (substr($arrayString[$Count], 0, 1)) {
                        $pass = substr($arrayString[$Count], 3);

                        $strConvert .= chr($pass - ($q + 7));
                    } else {
                        $pass = substr($arrayString[$Count], 3);

                        $strConvert .= chr($pass + ($q - 7));
                    }

                    $q++;

                    $arrayUrl = utf8_encode($strConvert);
                }
            }

            return $arrayUrl;
        } catch (Exception $exc) {
            return $exc->getTraceAsString();
        }
    }

    public function validationGet($data) {
        $ret = new Returner();
        $ret->setReturn(true);

        switch ($data["function"]) {
            case "core_user_get_users":
                if (empty($data["username"])) {
                    $ret->addMsg('Username inválido!');
                }

                if (empty($data["password"])) {
                    $ret->addMsg('Password inválido!');
                }

                if (empty($data["email"])) {
                    $ret->addMsg('Email inválido!');
                }

                if ($data["redirect"] = null || $data["redirect"] = "") {
                    $ret->addMsg('Redirect inválido!');
                } else {
                    if ($data["redirect"] == 1) {
                        if (empty($data["returnType"])) {
                            $ret->addMsg('ReturnType inválido!');
                        }
                    }
                }

                if ($ret->hasMsg()) {
                    $ret->setReturn(false);

                    return $ret;
                } else {
                    $ret->addDados('username', $this->Decrypting($data["username"]));
                    $ret->addDados('password', $data["password"]);
                    $ret->addDados('email', $this->Decrypting($data["email"]));
                    $ret->addDados('redirect', $data["redirect"]);

                    if ($data["redirect"] == 1) {
                        $ret->addDados('returnType', $data["returnType"]);
                    }

                    return $ret;
                }

                break;

            case "core_user_create_users":
                if (empty($data["username"])) {
                    $ret->addMsg('Username inválido!');
                }

                if (empty($data["password"])) {
                    $ret->addMsg('Password inválido!');
                }

                if (empty($data["name"])) {
                    $ret->addMsg('Name inválido!');
                }

                if (empty($data["email"])) {
                    $ret->addMsg('Email inválido!');
                }

                if (empty($data["institution"])) {
                    $ret->addMsg('Institution inválido!');
                }

                if (empty($data["returnType"])) {
                    $ret->addMsg('ReturnType inválido!');
                }

                if ($ret->hasMsg()) {
                    $ret->setReturn(false);

                    return $ret;
                } else {
                    $ret->addDados('username', $this->Decrypting($data["username"]));
                    $ret->addDados('password', $data["password"]);
                    $ret->addDados('name', $this->Decrypting($data["name"]));
                    $ret->addDados('email', $this->Decrypting($data["email"]));

                    if (!empty($data["course"])) {
                        $ret->addDados('course', $this->Decrypting($data["course"]));
                    } else {
                        $ret->addDados('course', null);
                    }

                    $ret->addDados('institution', $this->Decrypting($data["institution"]));
                    $ret->addDados('returnType', $data["returnType"]);

                    return $ret;
                }

                break;

            case "core_user_update_users":
                if (empty($data["username"])) {
                    $ret->addMsg('Username inválido!');
                }

                if (empty($data["password"])) {
                    $ret->addMsg('Password inválido!');
                }

                if (empty($data["name"])) {
                    $ret->addMsg('Name inválido!');
                }

                if (empty($data["email"])) {
                    $ret->addMsg('Email inválido!');
                }

                if (empty($data["returnType"])) {
                    $ret->addMsg('ReturnType inválido!');
                }

                if ($ret->hasMsg()) {
                    $ret->setReturn(false);

                    return $ret;
                } else {
                    $ret->addDados('username', $this->Decrypting($data["username"]));
                    $ret->addDados('password', $data["password"]);
                    $ret->addDados('name', $this->Decrypting($data["name"]));
                    $ret->addDados('email', $this->Decrypting($data["email"]));
                    $ret->addDados('suspended', $data["suspended"]);
                    $ret->addDados('returnType', $data["returnType"]);

                    return $ret;
                }

                break;

            case "enrol_manual_enrol_users":
                if (empty($data["username"])) {
                    $ret->addMsg('Username inválido!');
                }

                if (empty($data["password"])) {
                    $ret->addMsg('Password inválido!');
                }

                if (empty($data["email"])) {
                    $ret->addMsg('Email inválido!');
                }

                if (empty($data["course"])) {
                    $ret->addMsg('Course inválido!');
                }

                if (empty($data["returnType"])) {
                    $ret->addMsg('ReturnType inválido!');
                }

                if ($ret->hasMsg()) {
                    $ret->setReturn(false);

                    return $ret;
                } else {
                    $ret->addDados('username', $this->Decrypting($data["username"]));
                    $ret->addDados('password', $data["password"]);
                    $ret->addDados('email', $this->Decrypting($data["email"]));
                    $ret->addDados('course', $this->Decrypting($data["course"]));
                    $ret->addDados('returnType', $data["returnType"]);

                    return $ret;
                }

                break;

            case "get_descryp":
                $ret->addDados('dados', $this->Decrypting($data));

                return $ret;

                break;

            case "teste_user":
                $ret->addDados('dados', $this->Decrypting($data));

                return $ret;

                break;
            default:
                break;
        }
    }

}
