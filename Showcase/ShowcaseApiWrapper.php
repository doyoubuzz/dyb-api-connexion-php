<?php

/**
 * ShowcaseApiWrapper
 *
 * @author Name t.belin@doyoubuzz.com
 */
class ShowcaseApiWrapper {

    const API_URL = "http://showcase.doyoubuzz.com/api";

    const API_VERSION = "v1";

    protected $apikey;

    protected $apiSecret;

    public function __construct($apikey, $apiSecret)
    {
        $this->apikey = $apikey;
        $this->apiSecret = $apiSecret;
    }

    /**
     * doRequest make a request against the Showcase API
     *
     * @param mixed $action the url to call
     * @param array $userParams the url parameters
     * @param mixed $body the body of the request
     * @param string $method the method of the request (AUTO, GET, POST, PUT, DELETE)
     * @return void
     */
    public function doRequest($action, $userParams = array(), $body = null, $method = 'AUTO')
    {
        $params = $this->generateParams($userParams);
        $params = $this->computeHash($params);
        $url = $this->generateUrl($action, $params);
        $computedMethod = $body === null ? 'GET' : 'POST';
        $curlMethod = $method == 'AUTO' ? $computedMethod : $method;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $curlMethod);

        if ($body) {
            if(is_array($body)) {
                $body = http_build_query($body);
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Length: ' . strlen($body), 'Content-Type:text/xml'));
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }

        $content    = curl_exec($ch);
        $result     = array();
        $info       = curl_getinfo($ch);
        $code       = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($code >= 200 && $code < 300) {
            $result = json_decode($content);
        } else {
            throw new Exception('Error while getting data from API on URL ' . $url . ' with return code (' . $code . ') : Request: ' . $body . ' - Response: '. $content);
        }

        curl_close($ch);

        return $result;
    }


    /*******************
    * INTERNALS
    *******************/

    /**
     * generateUrl
     *
     * @param mixed $action
     * @param mixed $parameters
     * @return void
     */
    protected function generateUrl($action, $parameters)
    {
        return self::API_URL . '/' . self::API_VERSION . '/' . $action . '?' . http_build_query($parameters);
    }

    /**
     * generateParams generate mandatory parameters for an API request
     * mandatory parameters include timestamp and apikey
     *
     * @return void
     */
    protected function generateParams($userParams = array())
    {
        $timestamp = time();
        $mandatoryParams = array(
            'timestamp' => $timestamp,
            'apikey' => $this->apikey,
            'format' => 'json'
        );
        return array_merge($userParams, $mandatoryParams);
    }

    /**
     * computeHash compute the hash of the request depending on all the parameters
     *
     * @param mixed $params
     * @return void
     */
    protected function computeHash($params)
    {
        $paramsStr = "";
        ksort($params);
        foreach ($params as $paramKey => $paramVal) {
            $paramsStr .= $paramVal;
        }
        $hash = md5($paramsStr . $this->apiSecret);
        return array_merge($params, array('hash' => $hash));
    }
}
