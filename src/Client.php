<?php

namespace Firebase;

use Exception;
use Firebase\Token\TokenException;
use Firebase\Token\TokenGenerator;

/**
 * Firebase PHP Client
 *
 * @author Ron Masas <ronmasas@gmail.com>
 * @link   https://www.firebase.com/docs/rest-api.html
 *
 */
class Client
{

    private $_host;
    private $_token;
    private $_timeout;
    private $_orderBy;
    private $_equalTo;
    private $_startAt;
    private $_endAt;
    private $_limitToFirst;
    private $_limitToLast;

    /**
     * Constructor
     *
     * @param string $baseURI
     * @param string $token
     */
    public function __construct($host, $token = false, $timeout = 10)
    {
        $this->_host = $host;
        $this->_token = $token;
        $this->_timeout = $timeout;
    }

    /**
     * Make a new instace of Firebase\Client
     *
     * @param string $host
     * @param mixed $token
     * @param integer $timeout
     * @return Firebase\Client
     */
    public static function make($host, $token = false, $timeout = 10)
    {
        return new static($host, $token, $timeout);
    }

    /**
     * Generate access token
     *
     * @param string $secret
     * @param array $object
     * @return TokenGenerator
     */
    public static function generateToken($secret, $object)
    {
        try
        {
            return (new TokenGenerator($secret))
                            ->setData($object)
                            ->create();
        } catch (TokenException $e)
        {
            $e->getMessage();
            return false;
        }
    }

    /**
     * Create a new Firebase instace for a child path
     *
     * @param string $path
     * @return Firebase
     */
    public function child($path)
    {
        return new static($this->_host . $path, $this->_token);
    }

    /**
     * Writing data into Firebase with a PUT request
     *
     * @param mixed $data
     * @return array
     */
    public function set($data)
    {
        return $this->write($data);
    }

    /**
     * Writing data into Firebase with a POST request
     *
     * @param mixed $data
     * @return array
     */
    public function push($data)
    {
        return $this->write($data, 'POST');
    }

    /**
     * Writing data into Firebase with a PATCH request
     *
     * @param mixed $data
     * @return array
     */
    public function update($data)
    {
        return $this->write($data, 'PATCH');
    }

    /**
     * Delete data from Firebase with a DELETE request
     *
     * @return void
     */
    public function delete()
    {
        return $this->write('', 'DELETE');
    }

    /**
     * Get data from Firebase with a GET request
     *
     * @return array
     */
    public function get()
    {
        try
        {
            $ch = $this->curl('GET');
            $return = curl_exec($ch);
            curl_close($ch);
            return json_decode($return, true);
        } catch (Exception $e)
        {
            //...
        }
        return null;
    }

    /**
     * Filter for search using get()
     *
     * @return Client
     */
    public function orderBy($orderBy)
    {
        $this->_orderBy = $orderBy;
        return $this;
    }

    /**
     * Filter for search using get()
     *
     * @return Client
     */
    public function equalTo($equalTo)
    {
        $this->_equalTo = $equalTo;
        return $this;
    }

    /**
     * Filter for search using get()
     *
     * @return Client
     */
    public function startAt($startAt)
    {
        $this->_startAt = $startAt;
        return $this;
    }

    /**
     * Filter for search using get()
     *
     * @return Client
     */
    public function endAt($endAt)
    {
        $this->_endAt = $endAt;
        return $this;
    }
    
    /**
     * Filter for search using get()
     *
     * @return Client
     */
    public function limitToFirst($limitToFirst)
    {
        $this->_limitToFirst = $limitToFirst;
        return $this;
    }

    /**
     * Filter for search using get()
     *
     * @return Client
     */
    public function limitToLast($limitToLast)
    {
        $this->_limitToLast = $limitToLast;
        return $this;
    }

    /**
     * Generate curl object
     *
     * @param string $mode
     * @return curl
     */
    private function curl($mode)
    {
        $url = sprintf('%s.json', $this->_host);

        if ($this->_token)
        {
            $url = sprintf('%s?auth=%s', $url, $this->_token);
        }

        if ($this->_orderBy)
        {
            $pre = "";

            if ($this->_token)
            {
                $pre = "&";
            }
            else
            {
                $pre = "?";
            }

            $url = sprintf('%s'. $pre .'orderBy="%s"', $url, $this->_orderBy);

            if ($this->_equalTo)
            {
                $url = sprintf('%s&equalTo=%s', $url, $this->_equalTo);
            }

            if ($this->_startAt)
            {
                $url = sprintf('%s&startAt=%s', $url, $this->_startAt);
            }

            if ($this->_endAt)
            {
                $url = sprintf('%s&endAt=%s', $url, $this->_endAt);
            }

            if ($this->_limitToFirst)
            {
                $url = sprintf('%s&limitToFirst=%s', $url, $this->_limitToFirst);
            }

            if ($this->_limitToLast)
            {
                $url = sprintf('%s&limitToLast=%s', $url, $this->_limitToLast);
            }

        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->_timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->_timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $mode);
        return $ch;
    }

    /**
     * Write data into firebase
     *
     * @param mixed $data
     * @param string $method
     * @return array
     */
    private function write($data, $method = 'PUT')
    {
        $jsonData = json_encode($data);
        $header = array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($jsonData)
        );
        try
        {
            $ch = $this->curl($method);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
            $return = curl_exec($ch);
            curl_close($ch);
            return json_decode($return, true);
        } catch (Exception $e)
        {
            //...
        }
        return null;
    }

}
