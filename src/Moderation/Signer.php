<?php

namespace Topphp\TopphpHuawei\Moderation;

define("BasicDateFormat", "Ymd\THis\Z");
define("Algorithm", "SDK-HMAC-SHA256");
define("HeaderXDate", "X-Sdk-Date");
define("HeaderHost", "host");
define("HeaderAuthorization", "Authorization");
define("HeaderContentSha256", "X-Sdk-Content-Sha256");

class Request
{
    public $method  = "";
    public $scheme  = "";
    public $host    = "";
    public $uri     = "";
    public $query   = array();
    public $headers = array();
    public $body    = "";
}

class Signer
{
    public $AppKey    = "";
    public $AppSecret = "";

    private function escape($string)
    {
        $entities     = array("+", "%7E");
        $replacements = array("%20", "~");
        return str_replace($entities, $replacements, urlencode($string));
    }
// Build a CanonicalRequest from a regular request string
//
// CanonicalRequest =
//  HTTPRequestMethod + "\n" +
//  CanonicalURI + "\n" +
//  CanonicalQueryString + "\n" +
//  CanonicalHeaders + "\n" +
//  SignedHeaders + "\n" +
//  HexEncode(Hash(RequestPayload))
    private function canonicalRequest($r)
    {
        $CanonicalURI         = $this->canonicalURI($r);
        $CanonicalQueryString = $this->canonicalQueryString($r);
        $canonicalHeaders     = $this->canonicalHeaders($r);
        $SignedHeaders        = $this->signedHeaders($r);
        if (@$r->headers[HeaderContentSha256]) {
            $hash = $r->headers[HeaderContentSha256];
        } else {
            $hash = hash("sha256", $r->body);
        }
        return "$r->method\n$CanonicalURI\n$CanonicalQueryString\n$canonicalHeaders\n$SignedHeaders\n$hash";
    }

// CanonicalURI returns request uri
    private function canonicalURI($r)
    {
        $pattens = explode("/", $r->uri);
        $uri     = array();
        foreach ($pattens as $v) {
            array_push($uri, $this->escape($v));
        }
        $urlpath = join("/", $uri);
        if (substr($urlpath, -1) != "/") {
            $urlpath = $urlpath . "/";
        }
        return $urlpath;
    }

// CanonicalQueryString
    private function canonicalQueryString($r)
    {
        $a = array();
        foreach ($r->query as $key => $value) {
            $k = $this->escape($key);
            if ($value == "") {
                $kv = $k;
            } else {
                $kv = "$k=" . $this->escape($value);
            }
            array_push($a, $kv);
        }
        sort($a);
        return join("&", $a);
    }

// CanonicalHeaders
    private function canonicalHeaders($r)
    {
        $a = array();
        foreach ($r->headers as $key => $value) {
            array_push($a, strtolower($key) . ":" . trim($value));
        }
        sort($a);
        return join("\n", $a) . "\n";
    }

    private function curlHeaders($r)
    {
        $header = array();
        foreach ($r->headers as $key => $value) {
            array_push($header, strtolower($key) . ":" . trim($value));
        }
        return $header;
    }

// SignedHeaders
    private function signedHeaders($r)
    {
        $a = array();
        foreach ($r->headers as $key => $value) {
            array_push($a, strtolower($key));
        }
        sort($a);
        return join(";", $a);
    }

// Create a "String to Sign".
    private function stringToSign($canonicalRequest, $t)
    {
        date_default_timezone_set("UTC");
        $date = date(BasicDateFormat, $t);
        $hash = hash("sha256", $canonicalRequest);
        return "SDK-HMAC-SHA256\n$date\n$hash";
    }

// Create the HWS Signature.
    private function signStringToSign($stringToSign, $signingKey)
    {
        return hash_hmac("sha256", $stringToSign, $signingKey);
    }

    private function authHeaderValue($signature, $accessKey, $signedHeaders)
    {
        return "SDK-HMAC-SHA256 Access=$accessKey, SignedHeaders=$signedHeaders, Signature=$signature";
    }

    public function sign($r)
    {
        date_default_timezone_set("UTC");
        if (@$r->headers[HeaderXDate]) {
            $t = date_create_from_format(BasicDateFormat, $r->headers[HeaderXDate]);
        }
        if (!@$t) {
            $t                       = time();
            $r->headers[HeaderXDate] = date(BasicDateFormat, $t);
        }
        $queryString = $this->canonicalQueryString($r);
        if ($queryString != "") {
            $queryString = "?" . $queryString;
        }
        $canonicalRequest                = $this->canonicalRequest($r);
        $stringToSign                    = $this->stringToSign($canonicalRequest, $t);
        $signature                       = $this->signStringToSign($stringToSign, $this->AppSecret);
        $signedHeaders                   = $this->signedHeaders($r);
        $authValue                       = $this->authHeaderValue($signature, $this->AppKey, $signedHeaders);
        $r->headers[HeaderAuthorization] = $authValue;

        $curl    = curl_init();
        $url     = $r->scheme . "://" . $r->host . $r->uri . $queryString;
        $headers = $this->curlHeaders($r);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $r->method);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $r->body);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_NOBODY, false);
        return $curl;
    }
}
