<?php

include_once("MoodleUtil.inc.php");

class MoodleController extends Returner {

    public function coreUserGetUsers($data) {
        header('Content-Type: text/plain');

        $ret = new Returner();
        $curl = new Curl;
        $serverUrl = URLMOODLE . '/webservice/rest/server.php' . '?wstoken=' . TOKEN . '&wsfunction=core_user_get_users&moodlewsrestformat=json';

        $username = $ret->Decrypting($data["username"]);
        // $password = $ret->Decrypting($data["password"]);
        // $name = utf8_encode($ret->Decrypting($data["name"]));
        // $email = utf8_encode($ret->Decrypting($data["email"]));
        // $course = $ret->Decrypting($data["course"]);
        // $institution = $ret->Decrypting($data["institution"]);
        // $group = $ret->Decrypting($data["group"]);    

        try {
            $user = new stdClass();

            $user->key = "username";
            $user->value = $username;
            $users = array($user);
            $params = array('criteria' => $users);

            $resp = json_decode($curl->post($serverUrl, $params));

            if (is_object($resp) && !empty($resp->users)) {
                $ret->addMsg("Usuário já existe");
                $ret->addDados("id", $resp->users[0]->id);
                $ret->addDados("email", $resp->users[0]->email);
                $ret->setReturn(true);
            } else {
                $ret->addMsg("Usuário não existe");
                $ret->setReturn(false);
            }

            return $ret;
        } catch (Exception $exc) {
            return $exc;
        }
    }

    public function coreUserCreateUsers($data) {
        header('Content-Type: text/plain; charset=UTF-8');

        $ret = new Returner();
        $curl = new Curl;
        $serverUrl = URLMOODLE . '/webservice/rest/server.php' . '?wstoken=' . TOKEN . '&wsfunction=core_user_create_users&moodlewsrestformat=json';

        try {
            $username = $ret->Decrypting($data["username"]);
            $password = $ret->Decrypting($data["password"]);
            $name = $ret->Decrypting($data["name"]);
            $email = $ret->Decrypting($data["email"]);

            if (!empty($data["course"])) {
                $course = $ret->Decrypting($data["course"]);
            }
            
            $institution = $ret->Decrypting($data["institution"]);
            $group = $ret->Decrypting($data["group"]);

            $arName = explode(" ", $name);
            $firstname = "";

            for ($i = 0; $i < (count($arName) - 1); $i++) {
                $firstname .= $arName[$i] . " ";
            }

            $lastname = explode(" ", $name);
            $lastname = ltrim(rtrim($lastname[count($lastname) - 1]));

            $user1 = new stdClass();
            $user1->username = $username;
            $user1->password = $password;
            $user1->firstname = $firstname;
            $user1->lastname = $lastname;
            $user1->email = $email;
            $user1->auth = "manual";
            $user1->idnumber = "";
            $user1->lang = "pt_br";
            $user1->mailformat = 0;
            $user1->country = "BR";
            // $user1->preferences = array(
            //     array("type" => "institution", "value" => $institution)
            // );
            $user1->customfields = array(
                array("type" => "institution", "value" => $institution)
            );

            $users = array($user1);
            $params = array('users' => $users);

            $resp = json_decode($curl->post($serverUrl, $params));

            if (is_array($resp)) {
                if (!empty($resp[0]->id)) {
                    if (!empty($data["course"])) {
                        $enrolCourse = $this->enrolManualEnrolUsersInternal($resp[0]->id, $username, $email, $course);
                    }

                    $ret->addMsg("Usuário cadastrado com sucesso");
                    $ret->setReturn(true);
                } else {
       
                }
            } else {
                $ret->logMsg($resp->debuginfo . " ({$username} | {$email})", "warning");
                $ret->addMsg($resp->debuginfo);      
                $ret->setError(2);
                $ret->setReturn(false);                         
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

    public function enrolManualEnrolUsersInternal($id, $username, $email, $course) {
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
                    $ret->logMsg("Curso de id: ({$value}) não existe" . " ({$username} | {$email})", "warning");
                    $ret->addMsg("Curso de id: ({$value}) não existe");
                }
            }

            return $ret;
        } catch (Exception $exc) {
            return $exc;
        }
    }

    public function coreUserDeleteUsers($data) {
        header('Content-Type: text/plain');

        $ret = new Returner();
        $curl = new Curl;
        $serverUrl = URLMOODLE . '/webservice/rest/server.php' . '?wstoken=' . TOKEN . '&wsfunction=core_user_delete_users&moodlewsrestformat=json';

        try {
            $data = $this->coreUserGetUsers($data);

            if (!empty($data->dados["id"])) {

                $user = new stdClass();

                $users = array($data->dados["id"]);
                $params = array('userids' => $users);

                $resp = json_decode($curl->post($serverUrl, $params));
            } else {
                $ret->addMsg("Usuário não existe");
            }

            return $ret;
        } catch (Exception $exc) {
            return $exc;
        }
    }

}
