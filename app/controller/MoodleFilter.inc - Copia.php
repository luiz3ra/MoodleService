<?php

class MoodleFilter extends MoodleController {

    private $username;
    private $password;
    private $email;
    private $name;
    private $institution;
    private $course;
    private $returnType;
    private $redirect;
    private $function;

    function getUsername() {
        return $this->username;
    }

    function setUsername($username) {
        $this->username = $username;
    }

    function getPassword() {
        return $this->password;
    }

    function setPassword($password) {
        $this->password = $password;
    }

    function getName() {
        return $this->name;
    }

    function setName($name) {
        $this->name = $name;
    }

    function getEmail() {
        return $this->email;
    }

    function setEmail($email) {
        $this->email = $email;
    }

    function getInstitution() {
        return $this->institution;
    }

    function setInstitution($institution) {
        $this->institution = $institution;
    }

    function getCourse() {
        return $this->course;
    }

    function setCourse($course) {
        $this->course = $course;
    }

    function getReturnType() {
        return $this->returnType;
    }

    function setReturnType($returnType) {
        $this->returnType = $returnType;
    }

    function getRedirect() {
        return $this->redirect;
    }

    function setRedirect($redirect) {
        $this->redirect = $redirect;
    }

    function getFunction() {
        return $this->function;
    }

    function setFunction($function) {
        $this->function = $function;
    }

    function populationBean($request) {

        while (list($key, $value) = @each($request)) {
            $method = "set" . ucwords($key);

            $this->$method($value);
        }

        return $this;
    }

    public function filterFunction($request) {
        $this->populationBean($request);

        $ret = new Returner();
        $ret->setReturn(true);

        switch ($request["function"]) {
            /**
             * Verifica se o usuário existe
             */
            case "core_user_get_users":
                if (empty($this->getUsername())) {
                    $ret->addMsg('Username inválido!');
                } else {
                    $ret->addDados('username', $this->Decrypting($this->getUsername()));
                }

                if (empty($this->getPassword())) {
                    $ret->addMsg('Password inválido!');
                } else {
                    $ret->addDados('password', $this->Decrypting($this->getPassword()));
                }

                if (empty($this->getEmail())) {
                    $ret->addMsg('Email inválido!');
                } else {
                    $ret->addDados('email', $this->Decrypting($this->getEmail()));
                }

                if ($ret->hasMsg()) {
                    $ret->setReturn(false);

                    return $ret;
                } else {
                    if (!empty($this->getRedirect())) {
                        $ret->addDados('redirect', $this->getRedirect());
                    }

                    if (!empty($this->getReturnType())) {
                        $ret->addDados('returnType', $this->getReturnType());
                    }

                    if (!empty($this->getFunction())) {
                        $ret->addDados('function', $this->getFunction());
                    }
//                    $retFunction = $this->coreUserGetUsers($this->Decrypting($ret->getDados()));

                    return $ret;
                }

                break;

            case "core_user_create_users":
                if (empty($this->getUsername())) {
                    $ret->addMsg('Username inválido!');
                    $ret->setReturn(false);
                } else {
                    $ret->addDados('username', $this->getUsername());
                }

                if (empty($this->getPassword())) {
                    $ret->addMsg('Password inválido!');
                    $ret->setReturn(false);
                } else {
                    $ret->addDados('password', $this->getPassword());
                }

                if (empty($this->getName())) {
                    $ret->addMsg('Name inválido!');
                    $ret->setReturn(false);
                } else {
                    $ret->addDados('name', $this->getName());
                }

                if (empty($this->getEmail())) {
                    $ret->addMsg('Email inválido!');
                    $ret->setReturn(false);
                } else {
                    $ret->addDados('email', $this->getEmail());
                }

                if (empty($this->getInstitution())) {
                    $ret->addMsg('Institution inválido!');
                    $ret->setReturn(false);
                } else {
                    $ret->addDados('institution', $this->getInstitution());
                }

                if ($ret->hasMsg()) {
                    return $ret;
                } else {
                    print_r($this->coreUserCreateUsers($this->Decrypting($ret->getDados())));
                }

//                if ($ret->getReturn()) {
//                    $dataGet = $this->Decrypting($ret->getDados());
//
//                    $getUser = $this->coreUserGetUsers($dataGet);
//
//                    if (!empty($getUser->users)) {
//                        $ret->addMsg('Aluno já existe!');
//                        $ret->setReturn(false);
//                    } else {
//                        $insertUser = $this->coreUserCreateUsers($dataGet);
//
//                        print_r($insertUser);
//                    }
//                }

                break;

            case "core_user_update_users":
                if (empty($this->getUsername())) {
                    $ret->addMsg('Username inválido!');
                    $ret->setReturn(false);
                } else {
                    $ret->addDados('username', $this->getUsername());
                }

                if (empty($this->getPassword())) {
                    $ret->addMsg('Password inválido!');
                    $ret->setReturn(false);
                } else {
                    $ret->addDados('password', $this->getPassword());
                }

                if (empty($this->getName())) {
                    $ret->addMsg('Name inválido!');
                    $ret->setReturn(false);
                } else {
                    $ret->addDados('name', $this->getName());
                }

                if (empty($this->getEmail())) {
                    $ret->addMsg('Email inválido!');
                    $ret->setReturn(false);
                } else {
                    $ret->addDados('email', $this->getEmail());
                }

                if ($ret->hasMsg()) {
                    return $ret;
                } else {
                    return $this->coreUserUpdateUsers($this->Decrypting($ret->getDados()));
                }

//                if ($ret->getReturn()) {
//                    $dataGet = $this->Decrypting($ret->getDados());
//
//                    $getUser = $this->coreUserGetUsers($dataGet);
//
//                    if (!empty($getUser->users)) {
//                        $getUser = $this->coreUserUpdateUsers($getUser, $dataGet);
//
//                        if (empty($getUser)) {
//                            $ret->addMsg('Aluno atualizado!');
//                            $ret->setReturn(true);
//                        } else {
//                            $ret->addMsg('Erro ao atualizar!');
//                            $ret->setReturn(false);
//                        }
//                    } else {
//                        $ret->addMsg('Aluno não encontrado!');
//                        $ret->setReturn(false);
//                    }
//                }

                break;

            case "enrol_manual_enrol_users":
                if (empty($this->getUsername())) {
                    $ret->addMsg('Username inválido!');
                    $ret->setReturn(false);
                } else {
                    $ret->addDados('username', $this->getUsername());
                }

                if (empty($this->getEmail())) {
                    $ret->addMsg('Email inválido!');
                    $ret->setReturn(false);
                } else {
                    $ret->addDados('email', $this->getEmail());
                }

                if (empty($this->getCourse())) {
                    $ret->addMsg('Curso inválido!');
                    $ret->setReturn(false);
                } else {
                    $ret->addDados('course', $this->getCourse());
                }

                if ($ret->hasMsg()) {
                    return $ret;
                } else {
                    print_r($this->enrolManualEnrolUsers($this->Decrypting($ret->getDados())));
                }

//                if ($ret->getReturn()) {
//                    $dataGet = $this->Decrypting($ret->getDados());
//
//                    $getUser = $this->coreUserGetUsers($dataGet);
//
//                    if (!empty($getUser->users)) {
//                        $ret = $this->enrolManualEnrolUsers($getUser, $dataGet, $ret);
//                    } else {
//                        $ret->addMsg('Aluno não encontrado!');
//                        $ret->setReturn(false);
//                    }
//                }

                break;

            case "enrol_manual_unenrol_users":
                if (empty($this->getUsername())) {
                    $ret->addMsg('Username inválido!');
                    $ret->setReturn(false);
                } else {
                    $ret->addDados('username', $this->getUsername());
                }

                if (empty($this->getEmail())) {
                    $ret->addMsg('Email inválido!');
                    $ret->setReturn(false);
                } else {
                    $ret->addDados('email', $this->getEmail());
                }

                if (empty($this->getCourse())) {
                    $ret->addMsg('Curso inválido!');
                    $ret->setReturn(false);
                } else {
                    $ret->addDados('course', $this->getCourse());
                }

                if ($ret->hasMsg()) {
                    return $ret;
                } else {
                    print_r($this->enrolManualUnenrolUsers($this->Decrypting($ret->getDados())));
                }

//                if ($ret->getReturn()) {
//                    $dataGet = $this->Decrypting($ret->getDados());
//
//                    $getUser = $this->coreUserGetUsers($dataGet);
//
//                    if (!empty($getUser->users)) {
//                        $this->enrolManualUnenrolUsers($getUser, $dataGet, $ret);
//                    } else {
//                        $ret->addMsg('Aluno não encontrado!');
//                        $ret->setReturn(false);
//                    }
//                }

                break;

            case "core_webservice_get_site_info":

                $this->coreWebserviceGetSiteInfo();
                break;
            default:
                break;
        }

        return $ret;
    }

}
