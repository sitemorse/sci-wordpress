<?php

/**
 * Sitemorse SCI Wordpress Plugin
 * Copyright (C) 2017 Sitemorse (UK Sales) Ltd
 *
 * This file is part of Sitemorse SCI.
 *
 * Sitemorse SCI is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.

 * Sitemorse SCI is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with Sitemorse SCI.  If not, see <http://www.gnu.org/licenses/>.
**/

class SCIClient {

  /* PHP implementation of an SCI protocol client.
     v1.0 (c) Sitemorse (UK Sales) Ltd 2011
  */

  const SCI_DEFAULT_SERVER = "sci.sitemorse.com";
  const SCI_DEFAULT_PORT = 5371;
  const SCI_DEFAULT_SSL_PORT = 5372;
  const SCI_CONNECT_TIMEOUT = 4;
  const SCI_READ_TIMEOUT = 240;
  const CRLF = "\r\n";
  const SCI_HASH_ALGORITHM = "sha1";
  const WEB_TIMEOUT = 6;
  const BUFFER_SIZE = 512;

  private $licenceKey;
  private $serverHostname;
  private $serverPort;
  private $serverSecure;
  private $postAllowed;

  function __construct($licenceKey, $args=[]) {
    $this->licenceKey = $licenceKey;
    $this->debug = false;
    if (array_key_exists("debug", $args))
      $this->debug = $args["debug"];
    $this->debugData = "";
    $this->extendedReponse = true;
    if (array_key_exists("extendedReponse", $args))
      $this->extendedReponse = $args["extendedReponse"];
    $this->serverSecure = true;
    if (array_key_exists("serverSecure", $args))
      $this->serverSecure = $args["serverSecure"];
    $this->serverHostname = self::SCI_DEFAULT_SERVER;
    if (array_key_exists("serverHostname", $args))
      $this->serverHostname = $args["serverHostname"];
    $this->serverPort = $this->serverSecure ? self::SCI_DEFAULT_SSL_PORT
      : self::SCI_DEFAULT_PORT;
    if (array_key_exists("serverPort", $args))
      $this->serverPort =  $args["serverPort"];
    $this->postAllowed = true;
    if (array_key_exists("postAllowed", $args))
      $this->postAllowed = $args["postAllowed"];
    $this->extraQuery = null;
    if (array_key_exists("extraQuery", $args))
      $this->extraQuery = $args["extraQuery"];
    $this->extraHeaders = null;
    if (array_key_exists("extraHeaders", $args))
      $this->extraHeaders = $args["extraHeaders"];
    $this->cookies = null;
    if (array_key_exists("cookies", $args))
      $this->cookies = $args["cookies"];
    $this->proxyHostname = null;
    if (array_key_exists("proxyHostname", $args))
      $this->proxyHostname = $args["proxyHostname"];
    $this->proxyPort = null;
    if (array_key_exists("proxyPort", $args))
      $this->proxyPort = $args["proxyPort"];
  }

  function establishConnection() {
    if ($this->proxyHostname) {
      $sock = $this->fsockopen($this->proxyHostname,
        $this->proxyPort, 0, 0, self::SCI_CONNECT_TIMEOUT);
      if (!$sock)
        throw new Exception("Error connecting to proxy " .
          $this->proxyHostname . ":" . $this->proxyPort . " " . $errstr);
      $this->fsendall($sock, "CONNECT " . $this->serverHostname . ":" .
        $this->serverPort . " HTTP/1.0" . self::CRLF . self::CRLF);
      $line = $this->fgetline($sock);
      if ($line == null)
        throw new Exception("HTTP proxy dropped connection "
            . "after request");
      if (substr($line, 0, 7) !== "HTTP/1." || strlen($line) < 12)
        throw new Exception("Unknown status line from HTTP proxy: " . $line);
      if (substr($line, 8, 4) !== " 200")
        throw new Exception("HTTP proxy server returned error: " . $line);
      while ($line = fgets($sock)) {
        if ($line == null)
          throw new Exception("HTTP proxy dropped connection "
              . "during response headers");
        if (strlen($line) == 0 || $line == self::CRLF)
          break;
      }
      if ($this->serverSecure)
        stream_socket_enable_crypto($sock, true,
          STREAM_CRYPTO_METHOD_SSLv3_CLIENT);
    } else {
      $sock = $this->fsockopen(($this->serverSecure ? "ssl://" : "tcp://") .
        $this->serverHostname, $this->serverPort, 0, 0,
          self::SCI_CONNECT_TIMEOUT);
      if (!$sock)
        throw new Exception("Error connecting to " . $this->serverHostname .
          ":" . $this->serverPort . " ");
    }
    stream_set_timeout($sock, self::SCI_READ_TIMEOUT);
    $line = $this->fgetline($sock);
    if (!$line)
      throw new Exception("Error reading SCI greeting line");
    if (strlen($line) < 16 || substr($line, 0, 4) !== "SCI:")
      throw new Exception("Bad greeting line from SCI server");
    if (substr($line, 4, 1) !== "1")
      throw new Exception("SCI server is using incompatible protocol version");
    $this->fsendall($sock,
      $this->GenerateAuthResponse(substr($line, 8)) . self::CRLF);
    $line = $this->fgetline($sock);
    if (!$line)
      throw new Exception(
        "Error reading from SCI server after authentication sent");
    if ($line !== "OK")
      throw new Exception($line);
    return $sock;
  }

  private function sendArgs($url, $hostNames, $view, $sock, $editurl="") {
    if (!in_array(parse_url($url, PHP_URL_HOST), $hostNames))
      array_push($hostNames, parse_url($url, PHP_URL_HOST));
    $this->hostNames = $hostNames;
    $args = array(
      "url" => $url,
      "hostNames" => $this->hostNames,
      "view" => $view,
      "extendedResponse" => $this->extendedReponse,
      "screenshot" => true,
      "testContent" => true,
      "cookies" => $this->cookies
    );
    if ($editurl) $args["editing"] = $editurl;
    $jsonreq = json_encode($args);
    $this->fsendall($sock, strlen($jsonreq) . self::CRLF . $jsonreq .
      self::CRLF);
    $line = $this->fgetline($sock);
    if (!$line)
      throw new Exception(
        "Error reading from SCI server after request data sent");
    if ($line !== "OK")
      throw new Exception($line);
  }

  function performTest($url, $hostNames=[], $view="snapshot-page", $editurl="") {
    try {
      $sock = $this->establishConnection();
      $this->sendArgs($url, $hostNames, $view, $sock, $editurl=$editurl);
      $results = $this->ProxyRequests($sock, $this->hostNames);
    } catch(Exception $e) {
      throw new Exception($e);
    }
    if ($this->debug) {
      $results["debug"] = true;
      $results["debugData"] = $this->debugData;
    }
    return $results;
  }

  private function fsockopen($hostname, $port, $errno, $errstr, $timeout) {
    if ($this->debug) {
      $this->debugData .= sprintf("socket open ('%s', %d, %d, %d, %d)\n",
        $hostname, $port, $errno, $errstr, $timeout);
    }
    return fsockopen($hostname, $port, $errno, $errstr, $timeout);
  }

  private function fgetline($handle, $length=1024, $ending=self::CRLF) {
    $line = stream_get_line($handle, $length, $ending);
    if ($this->debug) {
      $this->debugData .= "server: $line\n";
    }
    return $line;
  }

  private function fsendall($fp, $s, $log_message=false) {
    if ($this->debug) {
      if ($log_message) $this->debugData .= "client: $log_message\n";
      else $this->debugData .= "client: $s\n";
    }
    $written = fwrite($fp, $s, strlen($s));
    if ($written === false)
      throw new Exception("Error writing to socket");
    if (!fflush($fp))
      throw new Exception("Error flushing socket");
  }

  private function GenerateAuthResponse($challenge) {
    return substr($this->licenceKey, 0, 8) .
      hash_hmac(self::SCI_HASH_ALGORITHM, $challenge,
      substr($this->licenceKey, 8));
  }


  private function web_timeout($sock, $timetarget) {
    $now = microtime(true);
    if ($now >= $timetarget) {
      $this->fsendall($sock,
        "XSCI timeout Timeout reading from web server" . self::CRLF);
      return true;
    }
    return false;
  }

  private function ProxyRequests($sock) {
    $lastLine = "";
    $results = [];
    while (true) {
      /* Read a request line and see what it says */
      $line = $this->fgetline($sock);
      if (!$line)
        throw new Exception(
          "Error reading from SCI server during proxy phase");
      $lastLine = $line;
      if ($line === "XSCI-NOOP")
        continue;
      elseif (substr($line, 0, 14) === "XSCI-COMPLETE ") {
        $results["url"] = substr($line, 14);
        $results["results"] = "";
        if ($this->extendedReponse) {
          $this->fgetline($sock); #Content-Type
          $length = substr($this->fgetline($sock), 16); #Content-Length
          $this->fgetline($sock); #Empty line
          $results["results"] = $this->fgetline($sock, $length); #JSON
        }
        return $results;
      }
      elseif (substr($line, 0, 11) === "XSCI-ERROR ")
        throw new Exception(substr($line, 11));
      elseif (!(substr($line, 0, 4) === "GET ") &&
        !(substr($line, 0, 5) === "POST "))
        throw new Exception("Unknown SCI request: " . $line);
      /*
       * It's an HTTP request, parse the request line and the URL, and
       * read the headers.
      */
      $parts = explode(" ", $line);
      $method = $parts[0];
      $url = parse_url($parts[1]);
      $proto = $url["scheme"];
      if (isset($url["port"]))
        $port = $url["port"];
      elseif (strtolower($proto) == "https")
        $port = 443;
      else
        $port = 80;
      $host = $url["host"];
      $path = $url["path"];
      $query = "";
      if (isset($url["query"]))
        $query = "?" . $url["query"];
      $httpVersion = $parts[2];
      if (substr($httpVersion, 0, 7) !== "HTTP/1." || strlen($httpVersion) != 8)
        throw new Exception("Unknown HTTP version: " . $httpVersion);
      $headers = $this->ReadHeaders($sock);
      $i = strrpos($line, " ");
      /* If there was a Content-Length header, read a body too. */
      $data = "";
      for ($i = 0; $i < count($headers); ++$i) {
        if (substr(strtolower($headers[$i]), 0, 15) == "content-length:") {
          $clen = (int)(substr($headers[$i], 16));
          while (strlen($data) < $clen) {
            $i = fread($sock, $clen > self::BUFFER_SIZE ? $clen :
              self::BUFFER_SIZE);
            if (!$i)
              throw new Exception(
                "SCI server disconnected while sending HTTP body");
            $data .= $i;
            $clen -= strlen($i);
            break;
          }
        }
      }
      /* Security checks on the request */
      if ($method =="POST" && !$this->postAllowed) {
        $this->fsendall($sock, "XSCI accessdenied POST actions have been" .
        " disallowed" . self::CRLF);
        continue;
      }
      if ($proto != "http" && $proto != "https") {
        $this->fsendall($sock, "XSCI badscheme URL scheme '" . $proto .
          "' not allowed" . self::CRLF);
        continue;
      }
      for ($i = 0; $i < count($this->hostNames); ++$i) {
        if (strtolower($this->hostNames[$i]) == strtolower($host)) {
          break;
        }
      }
      if ($i >= count($this->hostNames)) {
        $this->fsendall($sock, "XSCI accessdenied CMS proxy access denied to host '"
          . $host . "'" . self::CRLF);
        continue;
      }
      if ($port != -1 && ($port < 1 || $port > 65535
        || $port == 19 || $port == 25)) {
        $this->fsendall($sock, "XSCI badport Access denied to port " . $port
        . self::CRLF);
        continue;
      }
      /*
       * Forward the request to the relevant web server. This entire
       * section must not take longer than WEB_TIMEOUT ms.
       */
      $transport_protocol = "tcp://";
      if ($proto == "https")
        $transport_protocol = "ssl://";
      $web = $this->fsockopen($transport_protocol . $host,
        $port, 0, 0, self::SCI_CONNECT_TIMEOUT);
      if ($web === false) {
        $err_msg = "";
        if ($errno === 0)
          $err_msg = "XSCI noaddr Unknown hostname" . self::CRLF;
        elseif ($errno === 111)
          $err_msg = "XSCI connref Connection refused" . self::CRLF;
        elseif ($errno === 110)
          $err_msg = "XSCI timeout Timeout reading from web server" . self::CRLF;
        elseif ($errno === 5)
          $err_msg = "XSCI exception " . $errstr . self::CRLF;
        if ($err_msg)
          $this->fsendall($sock, $err_msg);
        continue;
      }
      /* Connect to the server */
      $starttime = microtime(true);
      $timetarget = $starttime + self::WEB_TIMEOUT;
      /*
       * Calculate the path to send in the request.
       * We may need to add parameters if this.extraQuery is set.
       */
      if ($this->extraQuery) {
        if ($query)
          $query = "?" . $this->extraQuery;
        else
          $query .= "&" . $this->extraQuery;
      }
      /*
       * Write the request line, the headers, and the body (if
       * any)
       */
        " " . $httpVersion . self::CRLF;
      $this->fsendall($web, $method . " " . $path .
        $query . " " . $httpVersion . self::CRLF);
      for ($i = 0; $i < count($headers); ++$i) {
        $this->fsendall($web, $headers[$i] . self::CRLF);
      }
      /*
      * Insert X-SCI-CONTROL header to display magic comments
      */
      $this->fsendall($web, "X-SCI-CONTROL: " .
        substr($this->licenceKey, 0, 8) . " content-only" .
        self::CRLF);
      if ($this->extraHeaders !== null) {
        for ($i = 0; $i < count($this->extraHeaders); ++$i) {
          $this->fsendall($web, $this->extraHeaders[$i] . self::CRLF);
        }
      }
      $this->fsendall($web, self::CRLF);
      if ($data !== null) {
        $this->fsendall($web, $data, "SEND DATA");
      }
      /*
       * Wait for the status response line, then read the headers
       */
      if ($this->web_timeout($sock, $timetarget))
        continue;
      $status = $this->fgetline($web);
      $resptime = microtime(true);
      if ($status === null) {
        $this->fsendall($sock,
          "XSCI noeoh No end-of-headers found" . self::CRLF);
        continue;
      }
      if (substr($status, 0, 4) != "HTTP") {
        $this->fsendall($sock,
          "XSCI badstatus Bad status line '" . $status . "'" . self::CRLF);
        continue;
      }
      if ($this->web_timeout($sock, $timetarget))
        continue;
      $headers = $this->ReadHeaders($web);
      if ($this->web_timeout($sock, $timetarget))
        continue;
      if ($headers == null) {
        $this->fsendall($sock,
          "XSCI noeoh No end-of-headers found" . self::CRLF);
        continue;
      }
      /*
       * Read the response body, by reading all the data until the
       * socket is closed by the other end.
       */
      $data = "";
      $now = microtime(true);
      while (!feof($web) && $now < $timetarget) {
        $now = microtime(true);
        $data .= fread($web, self::BUFFER_SIZE);
      }
      if ($this->web_timeout($sock, $timetarget))
        continue;
      $endtime = microtime(true);
      /*
       * Forward the response headers and body back to the SCI server.
       * Remove any Content-Length header that may already exist, and
       * add a new one that we can guarantee to be correct.
       */
      $this->fsendall($sock, $status . self::CRLF);
      for ($i = 0; $i < count($headers); ++$i) {
        if (substr(strtolower($headers[$i]), 0, 15) != "content-length:") {
          $this->fsendall($sock, $headers[$i] . self::CRLF);
        }
      }
      $xsciResp = (int)(($resptime - $starttime) * 1000);
      $xscTotalTime = (int)(($endtime - $starttime) * 1000);
      $this->fsendall($sock, "Content-Length: " . strlen($data) . self::CRLF);
      $this->fsendall($sock, "X-SCI-Response: " . $xsciResp . self::CRLF);
      $this->fsendall($sock, "X-SCI-TotalTime: " . $xscTotalTime .
        self::CRLF);
      $this->fsendall($sock, self::CRLF);
      $this->fsendall($sock, $data, "SEND DATA");
    }
  }

  private function ReadHeaders($sock) {
    $headers = [];
    $buf = "";
    while (true) {
      $line = $this->fgetline($sock);
      if ($line === null)
        return null;
      if (strlen($line) == 0) {
        if (count($buf) > 0)
          array_push($headers, $buf);
        return $headers;
      }
      if (substr($line, 0, 1) === " " || (substr($line, 0, 1) == "\t"))
        $buf .= $line;
      elseif (strlen($buf) > 0) {
        array_push($headers, $buf);
        $buf = "";
      }
      $buf .= $line;
    }
  }
}
