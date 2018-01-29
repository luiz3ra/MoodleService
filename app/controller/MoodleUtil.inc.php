<?php

/**
 * Classe de retorno padrão de funções
 *
 * @package Fivecom
 * @subpackage Base
 * @category Returner
 */
class Returner {

    /**
     * Lista com os dados gerados
     *
     * @var array
     */
    public $dados;

    /**
     * Identifica se o retorno foi falha ou sucesso
     *
     * @var bool
     */
    public $return;

    /**
     * Lista de mensagens geradas
     * @var array
     */
    public $msg;
    public $error;

    /**
     * Construtor
     */
    public function __construct() {
        $this->dados = array();
        $this->msg = array();
        $this->return = false;
        $this->error = null;
    }

    /**
     * Pega a lista de dados do returner, caso
     * uma chave seja passada, ele busca o valor
     * relacionado a essa chave e retorna-o
     *
     * @param string $chave
     * @return array
     */
    public function getDados($chave = '') {
        $dados = '';
        if (!empty($chave)) {
            $dados = $this->dados[$chave];
        } else {
            $dados = $this->dados;
        }
        return $dados;
    }

    /**
     * Justa um array de mensagens passado com o array
     * local
     *
     * @param array $msgArray
     */
    public function mergeMsg(array $msgArray) {
        $this->msg = array_merge($this->msg, $msgArray);
    }

    /**
     * Identifica se o objeto possui mensagem
     *
     * @return bool
     */
    public function hasMsg() {
        if (count($this->msg) > 0) {
            return true;
        }
        return false;
    }

    /**
     * Identifica se o objeto possui dados
     * @return bool
     */
    public function hasDados() {
        if (count($this->dados) > 0) {
            return true;
        }

        return false;
    }

    /**
     * Adiciona um dado ao returner
     *
     * @param string|int $chave
     * @param mided $dado
     */
    public function addDados($chave, $dado, $array = null) {
        if (!empty($array)) {
            foreach ($array as $key => $value) {
                $this->dados[$key] = $value;
            }
        } else {
            $this->dados[$chave] = $dado;
        }
    }

    /**
     * Substritui a lista de dados pela passada.
     * Caso queira adiciona apenas mais um dado use
     * addDados().
     *
     * @param array $listaDados
     */
    public function setDados(array $listaDados) {
        $this->dados = $listaDados;
    }

    /**
     * Destrói os dados do returner
     */
    public function deleteDados() {
        unset($this->dados);
    }

    /**
     * Retorna o estado do returner
     *
     * @return bool
     */
    public function getReturn() {
        return $this->return;
    }

    /**
     * Define o estado do returner, sucesso ou erro
     *
     * @param bool $bool
     */
    public function setReturn($bool) {
        $this->return = $bool;
    }

    /**
     * Retorna o estado do returner
     *
     * @return bool
     */
    public function getError() {
        return $this->error;
    }

    /**
     * Define o estado do returner, sucesso ou erro
     *
     * @param int $int
     */
    public function setError($int) {
        $this->error = $int;
    }

    /**
     * Retorna a lista de mensagens
     *
     * @return array
     */
    public function getMsg() {
        return $this->msg;
    }

    /**
     * Substritui a lista de mensagens pela passada.
     * Caso queira adiciona apenas mais uma mensagem use
     * addMsg() e se caso possuir um array de mensagem, pode
     * usar o mergeMsg() que junta as duas listas
     *
     * @param array $listaMensagens
     */
    public function setMsg(array $listaMensagens) {
        $this->msg = $listaMensagens;
    }

    /**
     * Adiciona uma mensagem ao sistema
     *
     * @param string $mensagem
     */
    public function addMsg($mensagem = null) {
        if (is_string($mensagem) && strlen($mensagem) > 0) {
            $this->msg[] = $mensagem;
        }
    }

    /**
     * Transforma as mensagens em uma string com quebra de linha
     * do tipo \n
     *
     * @return string
     */
    public function toString() {
        return implode("\n", $this->msg);
    }

    /**
     * Retorna uma string das mensagens com a quebra de
     * linha <br /> e \n
     *
     * @return string
     */
    public function toHtml() {
        return implode("<br />\n", $this->msg);
    }

    /**
     * Retorna uma string formatada com uma lista <ul>
     *
     * @return string
     */
    public function toList() {
        $msg = null;
        if ($this->hasMsg()) {
            $li = '';
            foreach ($this->msg as $v) {
                $li .= "<li>{$v}</li>\n";
            }
            if ($this->return) {
                $msg = "<ul class='aviso sucess'>\n{$li}</ul>\n";
            } else {
                $msg = "<ul class='aviso error'>\n{$li}</ul>\n";
            }
        }
        return $msg;
    }

    /**
     * Retorna uma string no formato de um XML
     * Esse método não modifica o header do arquivo, ficando
     * ao seu cargo fazer isso.
     *
     * @return string
     */
    public function toXml($ret) {
        $return = ($ret->return) ? 1 : 0;
        $dados = $ret->getDados();

        try {
            $dom = new DOMDocument("1.0", "UTF-8");
            $dom->preserveWhiteSpace = false;
            $dom->formatOutput = true;

            $Moodle = $dom->createElement("Moodle");

            $return = $dom->createElement("Return", $return);
            $Moodle->appendChild($return);

            if ($ret->getError()>= 0) {
                $error = $dom->createElement("Error", $ret->getError());
                $Moodle->appendChild($error);
            }

            if ($ret->hasMsg()) {
                $msgs = $dom->createElement("Msgs");

                $i = 0;

                foreach ($ret->msg as $v) {
                    ${"msg$i"} = $dom->createElement("Msg", $v);

                    $msgs->appendChild(${"msg$i"});

                    $i++;
                }

                $Moodle->appendChild($msgs);
            }

            if ($ret->getDados()) {
                $dados = $dom->createElement("Dados");

                $i = 0;

                foreach ($ret->getDados() as $keyDados => $valueDados) {
                    $dados->appendChild($dom->createElement($keyDados, $valueDados));

                    $i++;
                }

                $Moodle->appendChild($dados);
            }

            $dom->appendChild($Moodle);

//            $dom->save("contatos.xml");
            header("Content-Type: text/xml");

            print $dom->saveXML();
        } catch (Exception $e) {
            return $e;
        }
    }

    public function toJson($dados = false) {
        $ret = $this->toArray($dados);

        print json_encode($ret);
    }

    public function toArray($dados = false) {
        if (array_key_exists("return", $dados)) {
            $ret["return"] = $dados->getReturn();
            $ret["msg"] = array_map("htmlentities", $dados->getMsg());

            if ($dados == true) {

                $map = $dados->getDados();

                foreach ($map as $k => $v) {
                    if (is_array($v)) {
                        foreach ($v as $sk => $sv)
                            $map[$k][$sk] = array_map("utf8_encode", $sv);
                    } else {
                        $map[$k] = utf8_encode($v);
                    }
                }

                $ret["dados"] = $map;
            }

            return $ret;
        } else {
            $ret = array();
            $map = array();

            foreach ($dados as $k => $v) {
                $map[$k] = utf8_encode($v);
            }

            return $map;
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

                    $arrayUrl[$key] = $strConvert;
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

    function logMsg( $msg, $level = 'info', $file = 'log.log' ) {
    // variável que vai armazenar o nível do log (INFO, WARNING ou ERROR)
        $levelStr = '';

    // verifica o nível do log
        switch ( $level )
        {
            case 'info':
            // nível de informação
            $levelStr = 'INFO';
            break;

            case 'warning':
            // nível de aviso
            $levelStr = 'WARNING';
            break;

            case 'error':
            // nível de erro
            $levelStr = 'ERROR';
            break;
        }

    // data atual
        $date = date( 'Y-m-d H:i:s' );

    // formata a mensagem do log
    // 1o: data atual
    // 2o: nível da mensagem (INFO, WARNING ou ERROR)
    // 3o: a mensagem propriamente dita
    // 4o: uma quebra de linha
        $msg = sprintf( "[%s] [%s]: %s%s", $date, $levelStr, $msg, PHP_EOL );

    // escreve o log no arquivo
    // é necessário usar FILE_APPEND para que a mensagem seja escrita no final do arquivo, preservando o conteúdo antigo do arquivo
        file_put_contents( $file, $msg, FILE_APPEND );
    }

}

/**
 * cURL class
 *
 * This is a wrapper class for curl, it is quite easy to use:
 * <code>
 * $c = new curl;
 * // enable cache
 * $c = new curl(array('cache'=>true));
 * // enable cookie
 * $c = new curl(array('cookie'=>true));
 * // enable proxy
 * $c = new curl(array('proxy'=>true));
 *
 * // HTTP GET Method
 * $html = $c->get('http://example.com');
 * // HTTP POST Method
 * $html = $c->post('http://example.com/', array('q'=>'words', 'name'=>'moodle'));
 * // HTTP PUT Method
 * $html = $c->put('http://example.com/', array('file'=>'/var/www/test.txt');
 * </code>
 *
 * @author     Dongsheng Cai <dongsheng@moodle.com> - https://github.com/dongsheng/cURL
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 */
class Curl {

    /** @var bool */
    public $cache = false;
    public $proxy = false;

    /** @var array */
    public $response = array();
    public $header = array();

    /** @var string */
    public $info;
    public $error;

    /** @var array */
    private $options;

    /** @var string */
    private $proxy_host = '';
    private $proxy_auth = '';
    private $proxy_type = '';

    /** @var bool */
    private $debug = false;
    private $cookie = false;
    private $count = 0;

    /**
     * @param array $options
     */
    public function __construct($options = array()) {
        if (!function_exists('curl_init')) {
            $this->error = 'cURL module must be enabled!';
            trigger_error($this->error, E_USER_ERROR);
            return false;
        }
        // the options of curl should be init here.
        $this->resetopt();
        if (!empty($options['debug'])) {
            $this->debug = true;
        }
        if (!empty($options['cookie'])) {
            if ($options['cookie'] === true) {
                $this->cookie = 'curl_cookie.txt';
            } else {
                $this->cookie = $options['cookie'];
            }
        }
        if (!empty($options['cache'])) {
            if (class_exists('curl_cache')) {
                $this->cache = new curl_cache();
            }
        }
    }

    /**
     * Resets the CURL options that have already been set
     */
    public function resetopt() {
        $this->options = array();
        $this->options['CURLOPT_USERAGENT'] = 'MoodleBot/1.0';
        // True to include the header in the output
        $this->options['CURLOPT_HEADER'] = 0;
        // True to Exclude the body from the output
        $this->options['CURLOPT_NOBODY'] = 0;
        // TRUE to follow any "Location: " header that the server
        // sends as part of the HTTP header (note this is recursive,
        // PHP will follow as many "Location: " headers that it is sent,
        // unless CURLOPT_MAXREDIRS is set).
        //$this->options['CURLOPT_FOLLOWLOCATION']    = 1;
        $this->options['CURLOPT_MAXREDIRS'] = 10;
        $this->options['CURLOPT_ENCODING'] = '';
        // TRUE to return the transfer as a string of the return
        // value of curl_exec() instead of outputting it out directly.
        $this->options['CURLOPT_RETURNTRANSFER'] = 1;
        $this->options['CURLOPT_BINARYTRANSFER'] = 0;
        $this->options['CURLOPT_SSL_VERIFYPEER'] = 0;
        $this->options['CURLOPT_SSL_VERIFYHOST'] = 2;
        $this->options['CURLOPT_CONNECTTIMEOUT'] = 30;
    }

    /**
     * Reset Cookie
     */
    public function resetcookie() {
        if (!empty($this->cookie)) {
            if (is_file($this->cookie)) {
                $fp = fopen($this->cookie, 'w');
                if (!empty($fp)) {
                    fwrite($fp, '');
                    fclose($fp);
                }
            }
        }
    }

    /**
     * Set curl options
     *
     * @param array $options If array is null, this function will
     * reset the options to default value.
     *
     */
    public function setopt($options = array()) {
        if (is_array($options)) {
            foreach ($options as $name => $val) {
                if (stripos($name, 'CURLOPT_') === false) {
                    $name = strtoupper('CURLOPT_' . $name);
                }
                $this->options[$name] = $val;
            }
        }
    }

    /**
     * Reset http method
     *
     */
    public function cleanopt() {
        unset($this->options['CURLOPT_HTTPGET']);
        unset($this->options['CURLOPT_POST']);
        unset($this->options['CURLOPT_POSTFIELDS']);
        unset($this->options['CURLOPT_PUT']);
        unset($this->options['CURLOPT_INFILE']);
        unset($this->options['CURLOPT_INFILESIZE']);
        unset($this->options['CURLOPT_CUSTOMREQUEST']);
    }

    /**
     * Set HTTP Request Header
     *
     * @param array $headers
     *
     */
    public function setHeader($header) {
        if (is_array($header)) {
            foreach ($header as $v) {
                $this->setHeader($v);
            }
        } else {
            $this->header[] = $header;
        }
    }

    /**
     * Set HTTP Response Header
     *
     */
    public function getResponse() {
        return $this->response;
    }

    /**
     * private callback function
     * Formatting HTTP Response Header
     *
     * @param mixed $ch Apparently not used
     * @param string $header
     * @return int The strlen of the header
     */
    private function formatHeader($ch, $header) {
        $this->count++;
        if (strlen($header) > 2) {
            list($key, $value) = explode(" ", rtrim($header, "\r\n"), 2);
            $key = rtrim($key, ':');
            if (!empty($this->response[$key])) {
                if (is_array($this->response[$key])) {
                    $this->response[$key][] = $value;
                } else {
                    $tmp = $this->response[$key];
                    $this->response[$key] = array();
                    $this->response[$key][] = $tmp;
                    $this->response[$key][] = $value;
                }
            } else {
                $this->response[$key] = $value;
            }
        }
        return strlen($header);
    }

    /**
     * Set options for individual curl instance
     *
     * @param object $curl A curl handle
     * @param array $options
     * @return object The curl handle
     */
    private function apply_opt($curl, $options) {
        // Clean up
        $this->cleanopt();
        // set cookie
        if (!empty($this->cookie) || !empty($options['cookie'])) {
            $this->setopt(array('cookiejar' => $this->cookie,
                'cookiefile' => $this->cookie
            ));
        }

        // set proxy
        if (!empty($this->proxy) || !empty($options['proxy'])) {
            $this->setopt($this->proxy);
        }
        $this->setopt($options);
        // reset before set options
        curl_setopt($curl, CURLOPT_HEADERFUNCTION, array(&$this, 'formatHeader'));
        // set headers
        if (empty($this->header)) {
            $this->setHeader(array(
                'User-Agent: MoodleBot/1.0',
                'Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7',
                'Connection: keep-alive'
            ));
        }
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->header);

        if ($this->debug) {
            echo '<h1>Options</h1>';
            var_dump($this->options);
            echo '<h1>Header</h1>';
            var_dump($this->header);
        }

        // set options
        foreach ($this->options as $name => $val) {
            if (is_string($name)) {
                $name = constant(strtoupper($name));
            }
            curl_setopt($curl, $name, $val);
        }
        return $curl;
    }

    /**
     * Download multiple files in parallel
     *
     * Calls {@link multi()} with specific download headers
     *
     * <code>
     * $c = new curl;
     * $c->download(array(
     *              array('url'=>'http://localhost/', 'file'=>fopen('a', 'wb')),
     *              array('url'=>'http://localhost/20/', 'file'=>fopen('b', 'wb'))
     *              ));
     * </code>
     *
     * @param array $requests An array of files to request
     * @param array $options An array of options to set
     * @return array An array of results
     */
    public function download($requests, $options = array()) {
        $options['CURLOPT_BINARYTRANSFER'] = 1;
        $options['RETURNTRANSFER'] = false;
        return $this->multi($requests, $options);
    }

    /*
     * Mulit HTTP Requests
     * This function could run multi-requests in parallel.
     *
     * @param array $requests An array of files to request
     * @param array $options An array of options to set
     * @return array An array of results
     */

    protected function multi($requests, $options = array()) {
        $count = count($requests);
        $handles = array();
        $results = array();
        $main = curl_multi_init();
        for ($i = 0; $i < $count; $i++) {
            $url = $requests[$i];
            foreach ($url as $n => $v) {
                $options[$n] = $url[$n];
            }
            $handles[$i] = curl_init($url['url']);
            $this->apply_opt($handles[$i], $options);
            curl_multi_add_handle($main, $handles[$i]);
        }
        $running = 0;
        do {
            curl_multi_exec($main, $running);
        } while ($running > 0);
        for ($i = 0; $i < $count; $i++) {
            if (!empty($options['CURLOPT_RETURNTRANSFER'])) {
                $results[] = true;
            } else {
                $results[] = curl_multi_getcontent($handles[$i]);
            }
            curl_multi_remove_handle($main, $handles[$i]);
        }
        curl_multi_close($main);
        return $results;
    }

    /**
     * Single HTTP Request
     *
     * @param string $url The URL to request
     * @param array $options
     * @return bool
     */
    protected function request($url, $options = array()) {
        // create curl instance
        $curl = curl_init($url);

        $options['url'] = $url;
        $this->apply_opt($curl, $options);
        if ($this->cache && $ret = $this->cache->get($this->options)) {
            return $ret;
        } else {
            $ret = curl_exec($curl);
            if ($this->cache) {
                $this->cache->set($this->options, $ret);
            }
        }

        $this->info = curl_getinfo($curl);
        $this->error = curl_error($curl);

        if ($this->debug) {
            echo '<h1>Return Data</h1>';
            var_dump($ret);
            echo '<h1>Info</h1>';
            var_dump($this->info);
            echo '<h1>Error</h1>';
            var_dump($this->error);
        }

        curl_close($curl);

        if (empty($this->error)) {
            return $ret;
        } else {
            return $this->error;
            // exception is not ajax friendly
            //throw new moodle_exception($this->error, 'curl');
        }
    }

    /**
     * HTTP HEAD method
     *
     * @see request()
     *
     * @param string $url
     * @param array $options
     * @return bool
     */
    public function head($url, $options = array()) {
        $options['CURLOPT_HTTPGET'] = 0;
        $options['CURLOPT_HEADER'] = 1;
        $options['CURLOPT_NOBODY'] = 1;
        return $this->request($url, $options);
    }

    /**
     * Recursive function formating an array in POST parameter
     * @param array $arraydata - the array that we are going to format and add into &$data array
     * @param string $currentdata - a row of the final postdata array at instant T
     *                when finish, it's assign to $data under this format: name[keyname][][]...[]='value'
     * @param array $data - the final data array containing all POST parameters : 1 row = 1 parameter
     */
    function format_array_postdata_for_curlcall($arraydata, $currentdata, &$data) {
        foreach ($arraydata as $k => $v) {
            $newcurrentdata = $currentdata;
            if (is_object($v)) {
                $v = (array) $v;
            }
            if (is_array($v)) { //the value is an array, call the function recursively
                $newcurrentdata = $newcurrentdata . '[' . urlencode($k) . ']';
                $this->format_array_postdata_for_curlcall($v, $newcurrentdata, $data);
            } else { //add the POST parameter to the $data array
                $data[] = $newcurrentdata . '[' . urlencode($k) . ']=' . urlencode($v);
            }
        }
    }

    /**
     * Transform a PHP array into POST parameter
     * (see the recursive function format_array_postdata_for_curlcall)
     * @param array $postdata
     * @return array containing all POST parameters  (1 row = 1 POST parameter)
     */
    function format_postdata_for_curlcall($postdata) {
        if (is_object($postdata)) {
            $postdata = (array) $postdata;
        }
        $data = array();
        foreach ($postdata as $k => $v) {
            if (is_object($v)) {
                $v = (array) $v;
            }
            if (is_array($v)) {
                $currentdata = urlencode($k);
                $this->format_array_postdata_for_curlcall($v, $currentdata, $data);
            } else {
                $data[] = urlencode($k) . '=' . urlencode($v);
            }
        }
        $convertedpostdata = implode('&', $data);
        return $convertedpostdata;
    }

    /**
     * HTTP POST method
     *
     * @param string $url
     * @param array|string $params
     * @param array $options
     * @return bool
     */
    public function post($url, $params = '', $options = array()) {
        $options['CURLOPT_POST'] = 1;
        if (is_array($params)) {
            $params = $this->format_postdata_for_curlcall($params);
        }
        $options['CURLOPT_POSTFIELDS'] = $params;
        return $this->request($url, $options);
    }

    /**
     * HTTP GET method
     *
     * @param string $url
     * @param array $params
     * @param array $options
     * @return bool
     */
    public function get($url, $params = array(), $options = array()) {
        $options['CURLOPT_HTTPGET'] = 1;

        if (!empty($params)) {
            $url .= (stripos($url, '?') !== false) ? '&' : '?';
            $url .= http_build_query($params, '', '&');
        }
        return $this->request($url, $options);
    }

    /**
     * HTTP PUT method
     *
     * @param string $url
     * @param array $params
     * @param array $options
     * @return bool
     */
    public function put($url, $params = array(), $options = array()) {
        $file = $params['file'];
        if (!is_file($file)) {
            return null;
        }
        $fp = fopen($file, 'r');
        $size = filesize($file);
        $options['CURLOPT_PUT'] = 1;
        $options['CURLOPT_INFILESIZE'] = $size;
        $options['CURLOPT_INFILE'] = $fp;
        if (!isset($this->options['CURLOPT_USERPWD'])) {
            $this->setopt(array('CURLOPT_USERPWD' => 'anonymous: noreply@moodle.org'));
        }
        $ret = $this->request($url, $options);
        fclose($fp);
        return $ret;
    }

    /**
     * HTTP DELETE method
     *
     * @param string $url
     * @param array $params
     * @param array $options
     * @return bool
     */
    public function delete($url, $param = array(), $options = array()) {
        $options['CURLOPT_CUSTOMREQUEST'] = 'DELETE';
        if (!isset($options['CURLOPT_USERPWD'])) {
            $options['CURLOPT_USERPWD'] = 'anonymous: noreply@moodle.org';
        }
        $ret = $this->request($url, $options);
        return $ret;
    }

    /**
     * HTTP TRACE method
     *
     * @param string $url
     * @param array $options
     * @return bool
     */
    public function trace($url, $options = array()) {
        $options['CURLOPT_CUSTOMREQUEST'] = 'TRACE';
        $ret = $this->request($url, $options);
        return $ret;
    }

    /**
     * HTTP OPTIONS method
     *
     * @param string $url
     * @param array $options
     * @return bool
     */
    public function options($url, $options = array()) {
        $options['CURLOPT_CUSTOMREQUEST'] = 'OPTIONS';
        $ret = $this->request($url, $options);
        return $ret;
    }

    public function get_info() {
        return $this->info;
    }

}

/**
 * This class is used by cURL class, use case:
 *
 * <code>
 *
 * $c = new curl(array('cache'=>true), 'module_cache'=>'repository');
 * $ret = $c->get('http://www.google.com');
 * </code>
 *
 * @package    core
 * @subpackage file
 * @copyright  1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class curl_cache {

    /** @var string */
    public $dir = '';

    /**
     *
     * @param string @module which module is using curl_cache
     *
     */
    function __construct() {
        $this->dir = '/tmp/';
        if (!file_exists($this->dir)) {
            mkdir($this->dir, 0700, true);
        }
        $this->ttl = 1200;
    }

    /**
     * Get cached value
     *
     * @param mixed $param
     * @return bool|string
     */
    public function get($param) {
        $this->cleanup($this->ttl);
        $filename = 'u_' . md5(serialize($param));
        if (file_exists($this->dir . $filename)) {
            $lasttime = filemtime($this->dir . $filename);
            if (time() - $lasttime > $this->ttl) {
                return false;
            } else {
                $fp = fopen($this->dir . $filename, 'r');
                $size = filesize($this->dir . $filename);
                $content = fread($fp, $size);
                return unserialize($content);
            }
        }
        return false;
    }

    /**
     * Set cache value
     *
     * @param mixed $param
     * @param mixed $val
     */
    public function set($param, $val) {
        $filename = 'u_' . md5(serialize($param));
        $fp = fopen($this->dir . $filename, 'w');
        fwrite($fp, serialize($val));
        fclose($fp);
    }

    /**
     * Remove cache files
     *
     * @param int $expire The number os seconds before expiry
     */
    public function cleanup($expire) {
        if ($dir = opendir($this->dir)) {
            while (false !== ($file = readdir($dir))) {
                if (!is_dir($file) && $file != '.' && $file != '..') {
                    $lasttime = @filemtime($this->dir . $file);
                    if (time() - $lasttime > $expire) {
                        @unlink($this->dir . $file);
                    }
                }
            }
        }
    }

    /**
     * delete current user's cache file
     *
     */
    public function refresh() {
        if ($dir = opendir($this->dir)) {
            while (false !== ($file = readdir($dir))) {
                if (!is_dir($file) && $file != '.' && $file != '..') {
                    if (strpos($file, 'u_') !== false) {
                        @unlink($this->dir . $file);
                    }
                }
            }
        }
    }

}
