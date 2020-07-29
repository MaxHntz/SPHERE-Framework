<?php
namespace SPHERE\Application\Setting\Univention;

use SPHERE\Application\Setting\Univention\Service\Entity\TblUnivention;
use SPHERE\System\Extension\Repository\Debugger;

class UniventionUser
{
    private $curlhandle;
    private $server;
    private $token;

    public function __construct() {

        if(($tblUnivention = Univention::useService()->getUnivention(TblUnivention::TYPE_VALUE_SERVER))){
            $this->server = $tblUnivention->getValue();
        }
        if(($tblUnivention = Univention::useService()->getUnivention(TblUnivention::TYPE_VALUE_TOKEN))){
            $this->token = $tblUnivention->getValue();
        }
        $this->curlhandle = curl_init();
    }

    /**
     * @param string $property // name, firstname, lastname, birthday, record_uid (alle Properties "Resource Users")
     * @param string $value // Suche nach Mandanten Beispiel: "ref-"
     * @param bool   $fromFirstChar
     *
     * @return array|bool
     */
    public function getUserListByProperty($property = 'name', $value = '', $fromFirstChar = true)
    {

        curl_reset($this->curlhandle);

        if($fromFirstChar){
            $Url = 'https://'.$this->server.'/v1/users/?'.$property.'='.$value.'%2A';
        } else {
            $Url = 'https://'.$this->server.'/v1/users/?'.$property.'=%2A'.$value.'%2A';
        }

        curl_setopt_array($this->curlhandle, array(
            CURLOPT_URL => $Url,
            CURLOPT_HTTPGET => TRUE,
            CURLOPT_SSL_VERIFYHOST => FALSE,
            CURLOPT_HTTPHEADER => array('accept: application/json',
                'Authorization: bearer '.$this->token),
            //return the transfer as a string
            CURLOPT_RETURNTRANSFER => TRUE
//            CURLOPT_POSTFIELDS => $Content
        ));

        $Json = curl_exec($this->curlhandle);
        // Object to Array
        $StdClassAsArray = json_decode($Json, true);

        $UserList = array();
        if(is_array($StdClassAsArray) && !empty($StdClassAsArray)){
            foreach($StdClassAsArray as $User){
                $UserList[] = $User;
            }
        }
        return (is_array($UserList) && !empty($UserList) ? $UserList : false);
    }

    /**
     * @param string $name
     * @param string $email
     * @param string $firstname
     * @param string $lastname
     * @param string $record_uid
     * @param array  $roles
     * @param array  $schools
     * @param array  $school_classes
     * @param string $source_uid
     *
     * @return string|null
     */
    public function createUser($name = '', $email = '', $firstname = '', $lastname = '', $record_uid = '', $roles = array(),
        $schools = array(), $school_classes = array(), $source_uid = '')
    {
        curl_reset($this->curlhandle);

        $PersonContent = array(
            'name' => $name,
//            'mailPrimaryAddress' => $email,
//            'email' => $email,
            'firstname' => $firstname,
            'lastname' => $lastname,
            // AccountId
            'record_uid' => $record_uid,
            'roles' => $roles,
            'schools' => $schools,
//            'school_classes' => $school_classes, // ToDO Spalte funktioniert nicht
            // Mandant + AccountId
            'source_uid' => $source_uid
        );

        $PersonContent = json_encode($PersonContent);

//        $PersonContent = http_build_query($PersonContent);

        curl_setopt_array($this->curlhandle, array(
            CURLOPT_URL => 'https://'.$this->server.'/v1/users/',
            CURLOPT_POST => TRUE,
            CURLOPT_SSL_VERIFYHOST => FALSE,
            CURLOPT_HTTPHEADER => array('accept: application/json',
                'Content-Type: application/json',
                'Authorization: bearer '.$this->token),
            //return the transfer as a string
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_POSTFIELDS => $PersonContent
        ));

        /**
         * possible field's
          - dn
          - url
          - ucsschool_roles
          - name
          - school
          - firstname
          - lastname
          - birthday
          - disabled
          - email
          - record_uid
          - roles
          - schools
          - school_classes
          - source_uid
          - udm_properties { description, gidNumber, employeeType, organisation, phone, title, uidNumber }
         **/
        $Json = $this->execute($this->curlhandle);
        // Object to Array
        $StdClassArray = json_decode($Json, true);
        $Error = null;
        if(isset($StdClassArray['detail'])){
            if(is_string($StdClassArray['detail'])){
                $Error = $name.' - '.$StdClassArray['detail'];
            }elseif(is_array($StdClassArray['detail'])){
                $Error = '';
                foreach($StdClassArray['detail'] as $Detail){
                    if($Detail['msg']){
                        $Error .= $name.' - '.$Detail['msg'];
                    }
                }
            }
        }

        return $Error;
    }

    /**
     * @param string $name
     * @param string $email
     * @param string $firstname
     * @param string $lastname
     * @param string $record_uid
     * @param array  $roles
     * @param array  $schools
     * @param string $source_uid
     *
     * @return string|null
     */
    public function updateUser($name = '', $email = '', $firstname = '', $lastname = '', $record_uid = '', $roles = array(),
        $schools = array(), $source_uid = '')
    {
        curl_reset($this->curlhandle);

        $PersonContent = array(
            'name' => $name,
//            'mailPrimaryAddress' => $email,
//            'email' => $email,
            'firstname' => $firstname,
            'lastname' => $lastname,
            // Try AccountId to find Account again?
            'record_uid' => $record_uid,
            'roles' => $roles,
//Local Test without schools
            'schools' => $schools, // test with two array elements
            // Mandant + AccountId to human resolve problems?
            'source_uid' => $source_uid
        );

        $PersonContent = json_encode($PersonContent);

//        $PersonContent = http_build_query($PersonContent);

        curl_setopt_array($this->curlhandle, array(
            CURLOPT_URL => 'https://'.$this->server.'/v1/users/'.$name,
            CURLOPT_CUSTOMREQUEST => 'PUT',
            CURLOPT_SSL_VERIFYHOST => FALSE,
            CURLOPT_HTTPHEADER => array('accept: application/json',
                'Content-Type: application/json',
                'Authorization: bearer '.$this->token),
            //return the transfer as a string
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_POSTFIELDS => $PersonContent
        ));

        /**
         * possible field's
        - dn
        - url
        - ucsschool_roles
        - name
        - school
        - firstname
        - lastname
        - birthday
        - disabled
        - email
        - record_uid
        - roles
        - schools
        - school_classes
        - source_uid
        - udm_properties { description, gidNumber, employeeType, organisation, phone, title, uidNumber }
         **/
        $Json = $this->execute($this->curlhandle);
        // Object to Array
        $StdClassArray = json_decode($Json, true);
        $Error = null;
        if(isset($StdClassArray['detail'])){
            if(is_string($StdClassArray['detail'])){
                $Error = $name.' - '.$StdClassArray['detail'];
            }elseif(is_array($StdClassArray['detail'])){
                $Error = '';
                foreach($StdClassArray['detail'] as $Detail){
                    if($Detail['msg']){
                        $Error .= $name.' - '.$Detail['msg'];
                    }
                }
            }
        }

        return $Error;
    }

    /**
     * @param $name
     *
     * @return string|null
     */
    public function deleteUser($name)
    {

        curl_reset($this->curlhandle);

        $name = urlencode($name);

        Debugger::screenDump($name);

        curl_setopt_array($this->curlhandle, array(
            CURLOPT_URL => 'https://'.$this->server.'/v1/users/'.$name,
            CURLOPT_CUSTOMREQUEST => 'DELETE',
            CURLOPT_SSL_VERIFYHOST => FALSE,
            CURLOPT_HTTPHEADER => array('Authorization: bearer '.$this->token),
            //return the transfer as a string
            CURLOPT_RETURNTRANSFER => TRUE,
        ));

        $Json = $this->execute($this->curlhandle);
        // Object to Array
        $StdClassArray = json_decode($Json, true);
        $Error = null;
        if(isset($StdClassArray['detail'])){
            if(is_string($StdClassArray['detail'])){
                $Error = $name.' - '.$StdClassArray['detail'];
            }elseif(is_array($StdClassArray['detail'])){
                $Error = '';
                foreach($StdClassArray['detail'] as $Detail){
                    if($Detail['msg']){
                        $Error .= $name.' - '.$Detail['msg'];
                    }
                }
            }
        }

        return $Error;
    }

    private $retriableErrorCodes = [
        CURLE_COULDNT_RESOLVE_HOST,
        CURLE_COULDNT_CONNECT,
        CURLE_HTTP_NOT_FOUND,
        CURLE_READ_ERROR,
        CURLE_OPERATION_TIMEOUTED,
        CURLE_HTTP_POST_ERROR,
        CURLE_SSL_CONNECT_ERROR,
    ];

    /**
     * Executes a CURL request with optional retries and exception on failure
     *
     * @param  resource    $ch             curl handler
     * @param  int         $retries
     * @param  bool        $closeAfterDone
     * @return bool|string @see curl_exec
     */
    public function execute($ch, $retries = 5, $closeAfterDone = true)
    {
        while ($retries--) {
            $curlResponse = curl_exec($ch);
            if ($curlResponse === false) {
                $curlErrno = curl_errno($ch);
                if (false === in_array($curlErrno, $this->retriableErrorCodes, true) || !$retries) {
                    echo curl_error($ch);
                    if ($closeAfterDone) {
                        curl_close($ch);
                    }
                    return null; //throw new \RuntimeException(sprintf('Curl error (code %d): %s', $curlErrno, $curlError));
                }
                continue;
            }
            if ($closeAfterDone) {
                curl_close($ch);
            }
            return $curlResponse;
        }
        return false;
    }
}