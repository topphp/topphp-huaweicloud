<?php
namespace hwcvod\obs\s3;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Exception\RequestException;
use hwcvod\obs\common\ObsException;
use hwcvod\obs\common\Model;
use hwcvod\obs\log\S3Log;
use GuzzleHttp\Psr7\Stream;
use hwcvod\obs\common\Constants;

trait GetResponseTrait
{

    protected $exceptionResponseMode = true;
    
    protected $chunkSize = 65536;
    
    protected function isClientError(Response $response)
    {
        return $response -> getStatusCode() >= 400 && $response -> getStatusCode() < 500;
    }
    
    protected function parseXmlByType($searchPath, $key, &$value, $xml, $prefix)
    {
        $type = 'string';
        
        if (isset($value['sentAs'])) {
            $key = $value['sentAs'];
        }
        
        if ($searchPath === null) {
            $searchPath = '//'.$prefix.$key;
        }
        
        if (isset($value['type'])) {
            $type = $value['type'];
            if ($type === 'array') {
                $items = $value['items'];
                if (isset($items['wrapper'])) {
                    $searchPath = $searchPath .'/' .$prefix . $items['wrapper'];
                }
                
                $array = [];
                if (!isset($value['data']) || !isset($value['data']['xmlFlattened']) || !$value['data']['xmlFlattened']) {
                    $pkey = isset($items['sentAs']) ? $items['sentAs'] : $items['name'];
                    $_searchPath = $searchPath .'/' . $prefix .$pkey;
                } else {
                    $pkey = $key;
                    $_searchPath = $searchPath;
                }
                if ($result = $xml -> xpath($_searchPath)) {
                    if (is_array($result)) {
                        foreach ($result as $subXml) {
                            $subXml = simplexml_load_string($subXml -> asXML());
                            $subPrefix = $this->getXpathPrefix($subXml);
                            $array[] = $this->parseXmlByType('//'.$subPrefix. $pkey, $pkey, $items, $subXml, $subPrefix);
                        }
                    }
                }
                return $array;
            } elseif ($type === 'object') {
                $properties = $value['properties'];
                $array = [];
                foreach ($properties as $pkey => $pvalue) {
                    $name = isset($pvalue['sentAs']) ? $pvalue['sentAs'] : $pkey;
                    $array[$pkey] = $this->parseXmlByType($searchPath.'/' . $prefix .$name, $name, $pvalue, $xml, $prefix);
                }
                return $array;
            }
        }
        
        if ($result = $xml -> xpath($searchPath)) {
            if ($type === 'boolean') {
                return strval($result[0]) !== 'false';
            } elseif ($type === 'numeric' || $type === 'float') {
                return floatval($result[0]);
            } elseif ($type === 'int' || $type === 'integer') {
                return intval($result[0]);
            } else {
                return strval($result[0]);
            }
        } else {
            if ($type === 'boolean') {
                return false;
            } elseif ($type === 'numeric' || $type === 'float' || $type === 'int' || $type === 'integer') {
                return null;
            } else {
                return '';
            }
        }
    }
    
    private function parseCommonHeaders($model, $response)
    {
        foreach (Constants::COMMON_HEADERS as $key => $value) {
            $model[$value] = $response -> getHeaderLine($key);
        }
    }
    
    protected function parseItems($responseParameters, $model, $response, $body)
    {
        $prefix = '';
        
        $this->parseCommonHeaders($model, $response);
        
        $closeBody = false;
        try {
            foreach ($responseParameters as $key => $value) {
                if (isset($value['location'])) {
                    $location = $value['location'];
                    if ($location === 'header') {
                        $name = isset($value['sentAs']) ? $value['sentAs'] : $key;
                        $isSet = false;
                        if (isset($value['type'])) {
                            $type = $value['type'];
                            if ($type === 'object') {
                                $headers = $response -> getHeaders();
                                $temp = [];
                                foreach ($headers as $headerName => $headerValue) {
                                    if (stripos($headerName, $name) === 0) {
                                        $temp[substr($headerName, strlen($name))] = $response -> getHeaderLine($headerName);
                                    }
                                }
                                $model[$key] = $temp;
                                $isSet = true;
                            } else {
                                if ($response -> hasHeader($name)) {
                                    if ($type === 'boolean') {
                                        $model[$key] = ($response -> getHeaderLine($name)) !== 'false';
                                        $isSet = true;
                                    } elseif ($type === 'numeric' || $type === 'float') {
                                        $model[$key] = floatval($response -> getHeaderLine($name));
                                        $isSet = true;
                                    } elseif ($type === 'int' || $type === 'integer') {
                                        $model[$key] = intval($response -> getHeaderLine($name));
                                        $isSet = true;
                                    }
                                }
                            }
                        }
                        if (!$isSet) {
                            $model[$key] = $response -> getHeaderLine($name);
                        }
                    } elseif ($location === 'xml' && $body !== null) {
                        if (!isset($xml) && ($xml = simplexml_load_string($body -> getContents()))) {
                            $prefix = $this ->getXpathPrefix($xml);
                        }
                        $closeBody = true;
                        $model[$key] = $this -> parseXmlByType(null, $key, $value, $xml, $prefix);
                    } elseif ($location === 'body' && $body !== null) {
                        if (isset($value['type']) && $value['type'] === 'stream') {
                            $model[$key] = $body;
                        } else {
                            $model[$key] = $body -> getContents();
                            $closeBody = true;
                        }
                    }
                }
            }
        } finally {
            if ($closeBody && $body !== null) {
                $body -> close();
            }
        }
    }
    
    private function writeFile($filePath, Stream &$body)
    {
        $filePath = iconv('UTF-8', 'GBK', $filePath);
        if (is_string($filePath) && $filePath !== '') {
            $fp = null;
            $dir = dirname($filePath);
            try {
                if (!is_dir($dir)) {
                    mkdir($dir, 0755, true);
                }
                
                if (($fp = fopen($filePath, 'w'))) {
                    while (!$body->eof()) {
                        $str = $body->read($this->chunkSize);
                        fwrite($fp, $str);
                    }
                    fflush($fp);
                    S3Log::commonLog(DEBUG, "write file %s ok", $filePath);
                } else {
                    S3Log::commonLog(ERROR, "open file error,file path:%s", $filePath);
                }
            } finally {
                if ($fp) {
                    fclose($fp);
                }
                $body->close();
                $body = null;
            }
        }
    }
    
    private function parseXmlToException($body, $obsException)
    {
        try {
            $xmlErrorBody = trim($body -> getContents());
            if ($xmlErrorBody && ($xml = simplexml_load_string($xmlErrorBody))) {
                $prefix = $this->getXpathPrefix($xml);
                if ($tempXml = $xml->xpath('//'.$prefix . 'Code')) {
                    $obsException-> setExceptionCode(strval($tempXml[0]));
                }
                if ($tempXml = $xml->xpath('//'.$prefix . 'RequestId')) {
                    $obsException-> setRequestId(strval($tempXml[0]));
                }
                if ($tempXml = $xml->xpath('//'.$prefix . 'Message')) {
                    $obsException-> setExceptionMessage(strval($tempXml[0]));
                }
                if ($tempXml = $xml->xpath('//'.$prefix . 'HostId')) {
                    $obsException -> setHostId(strval($tempXml[0]));
                }
            }
        } finally {
            $body -> close();
        }
    }
    
    private function parseXmlToModel($body, $model)
    {
        try {
            $xmlErrorBody = trim($body -> getContents());
            if ($xmlErrorBody && ($xml = simplexml_load_string($xmlErrorBody))) {
                $prefix = $this->getXpathPrefix($xml);
                if ($tempXml = $xml->xpath('//'.$prefix . 'Code')) {
                    $model['Code'] = strval($tempXml[0]);
                }
                if ($tempXml = $xml->xpath('//'.$prefix . 'RequestId')) {
                    $model['RequestId'] = strval($tempXml[0]);
                }
                
                if ($tempXml = $xml->xpath('//'.$prefix . 'HostId')) {
                    $model['HostId'] = strval($tempXml[0]);
                }
                if ($tempXml = $xml->xpath('//'.$prefix . 'Resource')) {
                    $model['Resource'] = strval($tempXml[0]);
                }
                
                if ($tempXml = $xml->xpath('//'.$prefix . 'Message')) {
                    $model['Message']  = strval($tempXml[0]);
                }
            }
        } finally {
            $body -> close();
        }
    }
    
    protected function parseResponse(Model $model, Request $request, Response $response, array $requestConfig)
    {
        $statusCode = $response -> getStatusCode();
        $body = $response -> getBody();
        if ($statusCode >= 300) {
            if ($this-> exceptionResponseMode) {
                $obsException= new ObsException();
                $obsException-> setRequest($request);
                $obsException-> setResponse($response);
                $obsException-> setExceptionType($this->isClientError($response) ? 'client' : 'server');
                $this->parseXmlToException($body, $obsException);
                throw $obsException;
            } else {
                $this->parseCommonHeaders($model, $response);
                $this->parseXmlToModel($body, $model);
            }
        } else {
            if (!empty($model)) {
                foreach ($model as $key => $value) {
                    if ($key === 'method') {
                        continue;
                    }
                    if (isset($value['type']) && $value['type'] === 'file') {
                        $this->writeFile($value['value'], $body);
                    }
                    $model[$key] = $value['value'];
                }
            }
            
            if (isset($requestConfig['responseParameters'])) {
                $responseParameters = $requestConfig['responseParameters'];
                if (isset($responseParameters['type']) && $responseParameters['type'] === 'object') {
                    $responseParameters = $responseParameters['properties'];
                }
                $this->parseItems($responseParameters, $model, $response, $body);
            }
        }
        
        $model['HttpStatusCode'] = $statusCode;
        $model['Reason'] = $response -> getReasonPhrase();
    }
    
    protected function getXpathPrefix($xml)
    {
        $namespaces = $xml -> getDocNamespaces();
        if (isset($namespaces[''])) {
            $xml->registerXPathNamespace('ns', $namespaces['']);
            $prefix = 'ns:';
        } else {
            $prefix = '';
        }
        return $prefix;
    }
    
    protected function buildException(Request $request, RequestException $exception, $message)
    {
        $response = $exception-> hasResponse() ? $exception-> getResponse() : null;
        $obsException= new ObsException($message ? $message : $exception-> getMessage());
        $obsException-> setExceptionType('client');
        $obsException-> setRequest($request);
        if ($response) {
            $obsException-> setResponse($response);
            $obsException-> setExceptionType($this->isClientError($response) ? 'client' : 'server');
            $this->parseXmlToException($response -> getBody(), $obsException);
        }
        return $obsException;
    }
    
    protected function parseExceptionAsync(Request $request, RequestException $exception, $message = null)
    {
        return $this->buildException($request, $exception, $message);
    }
    
    protected function parseException(Model $model, Request $request, RequestException $exception, $message = null)
    {
        $response = $exception-> hasResponse() ? $exception-> getResponse() : null;
        
        if ($this-> exceptionResponseMode) {
            throw $this->buildException($request, $exception, $message);
        } else {
            if ($response) {
                $model['HttpStatusCode'] = $response -> getStatusCode();
                $model['Reason'] = $response -> getReasonPhrase();
                $this->parseXmlToModel($response -> getBody(), $model);
            } else {
                $model['HttpStatusCode'] = -1;
                $model['Message'] = $exception -> getMessage();
            }
        }
    }
}