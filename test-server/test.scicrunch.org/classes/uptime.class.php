<?php

class Uptime {
    private $timestamp = '';
    private $contentHeader = '';
    private $contentBody = '';
    private $httpCode = 0;
    private $contentType = '';
    private $totalTime = 0;

    public function getTimestamp () {
        $t = microtime(true);
        $micro = sprintf("%06d",($t - floor($t)) * 1000000);
        $d = new DateTime( date('Y-m-d H:i:s.'.$micro, $t) );
        $ts = $d->format("YmdHis.u");

        return $ts;
    }

    public function getDateTime () {
        date_default_timezone_set('America/Los_Angeles');
        $date = new DateTime();
        $date = $date->format('Y-m-d H:i:s');
        $timestamp = explode(" ", $date);

        return $timestamp;
    }

    public function callService ($url, $timeout) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_HTTPHEADER, Array("User-Agent: curl/7.47.0"));

        $response = curl_exec($ch);

        // Then, after curl_exec call:
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($response, 0, $header_size);
        $body = substr($response, $header_size);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        $total_time = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
        curl_close($ch);

        $this->setContentHeader($header);
        $this->setContentBody($body);
        $this->setHttpCode($code);
        $this->setContentType($type);
        $this->setTotalTime($total_time);

//         $content = json_decode($this->getContentBody());
//         echo "size: " . $header_size . "<br>";
//         echo "url: " . $url . "<br>";
//         echo "code: " . $this->getHttpCode() . "<br>";
//         echo "type: " . $this->getContentType() . "<br>";
//         echo "header: " . $this->getContentHeader() . "<br>";
//         echo 'total_time: ' . $this->getTotalTime() . "<br>";
//         echo 'body: ' . $this->getContentBody() . "<br>";
//         echo "content lenght: " . sizeof($content). "<br>";
//         echo "<pre>";
//         print_r($content);
//         echo "</pre>";
//         exit;
    }

    /**
     * @return the $contentHeader
     */
    public function getContentHeader()
    {
        return $this->contentHeader;
    }

 /**
     * @return the $contentBody
     */
    public function getContentBody()
    {
        return $this->contentBody;
    }

 /**
     * @return the $httpCode
     */
    public function getHttpCode()
    {
        return $this->httpCode;
    }

 /**
     * @return the $contentType
     */
    public function getContentType()
    {
        return $this->contentType;
    }

 /**
     * @return the $totalTime
     */
    public function getTotalTime()
    {
        return $this->totalTime;
    }

 /**
     * @param string $contentHeader
     */
    public function setContentHeader($contentHeader)
    {
        $this->contentHeader = $contentHeader;
    }

 /**
     * @param string $contentBody
     */
    public function setContentBody($contentBody)
    {
        $this->contentBody = $contentBody;
    }

 /**
     * @param number $httpCode
     */
    public function setHttpCode($httpCode)
    {
        $this->httpCode = $httpCode;
    }

 /**
     * @param string $contentType
     */
    public function setContentType($contentType)
    {
        $this->contentType = $contentType;
    }

 /**
     * @param number $totalTime
     */
    public function setTotalTime($totalTime)
    {
        $this->totalTime = $totalTime;
    }

}

?>
