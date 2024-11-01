<?php

namespace App\Helpers;

use Exception;

class CurlRequest
{
    private $curl;
    private $url;
    private $method;
    private $headers = [];
    private $options = [];

    public function __construct($url, $method = 'GET')
    {
        $this->url = $url;
        $this->method = strtoupper($method);
        $this->curl = curl_init();
        $this->setDefaultOptions();
    }

    private function setDefaultOptions()
    {
        $this->options[CURLOPT_RETURNTRANSFER] = true;
        $this->options[CURLOPT_FOLLOWLOCATION] = true;
    }

    public function setHeader($header, $value)
    {
        $this->headers[] = "$header: $value";
    }

    public function setHeaders(array $headers)
    {
        foreach ($headers as $header => $value) {
            $this->setHeader($header, $value);
        }
    }

    public function setBearerToken($token)
    {
        $this->setHeader('Authorization', 'Bearer ' . $token);
    }
    
    public function setOption($option, $value)
    {
        $this->options[$option] = $value;
    }

    public function setOptions(array $options)
    {
        foreach ($options as $option => $value) {
            $this->setOption($option, $value);
        }
    }

    public function setPostFields($fields)
    {
        if (is_array($fields)) {
            $this->options[CURLOPT_POSTFIELDS] = http_build_query($fields);
        } else {
            $this->options[CURLOPT_POSTFIELDS] = $fields;
        }
    }

    public function execute()
    {
        $this->options[CURLOPT_URL] = $this->url;
        $this->options[CURLOPT_CUSTOMREQUEST] = $this->method;

        if (!empty($this->headers)) {
            $this->options[CURLOPT_HTTPHEADER] = $this->headers;
        }

        curl_setopt_array($this->curl, $this->options);

        $response = curl_exec($this->curl);
        $info = curl_getinfo($this->curl);
        $error = curl_error($this->curl);

        if ($error) {
            curl_close($this->curl);
            throw new Exception("cURL Error: $error");
        }

        curl_close($this->curl);

        return [
            'response' => $response,
            'info' => $info,
        ];
    }
}
