<?php


/**
 * CurlX Lib - v0.0.4
 * @author n9va
 */
class StrangerN9na
{
    const VERSION = '0.0.4';

    private static array $default = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER         => TRUE,
        CURLOPT_ENCODING       => true,
        CURLINFO_HEADER_OUT    => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_AUTOREFERER    => true,
        CURLOPT_CONNECTTIMEOUT => 30,
        CURLOPT_TIMEOUT        => 60,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => 0
    ];

    private static string $current_dir = '';

    private static $ch;

    private static array $info;
    private static $headersCallBack;

    private static string $cookie_file = '';
    private static string $ua = '';
    private static int $error_code;
    private static string $error_string;

    private static $response;

    /**
     * Basic curl_init for request structure
     * 
     * @access private
     * @param string $url
     * 
     * @return void
     */
    private static function PrepareN9va(string $url) : void 
    {
        self::$ch = curl_init($url);
        self::SetOpt(self::$default);
    }

    /**
     * Add options to current curl structure
     * 
     * @access public
     * @param array $args
     * 
     * @return void
     */
    public static function SetOpt(array $option) : void 
    {
        curl_setopt_array(self::$ch, $option);
    }

    /**
     * Add an header to current curl structure
     * 
     * @access private
     * @param header
     * 
     * @return void
     */
    private static function Header(array $header) : void
    {
        self::SetOpt([CURLOPT_HTTPHEADER => $header]);
    }

    /**
     *  Set a proxy tunnel configuration to current curl structure, support: HTTP/S, SOCKS4, SOCKS5
     * 
     * @access private
     * @param array $args
     * 
     * @return void
     */
    private static function Tunnel(array $args) : void 
    {
        self::SetOpt([
            CURLOPT_PROXY => $args['SERVER'],
            CURLOPT_HTTPPROXYTUNNEL => true
        ]);
    }

    /**
     * Proxy Auth
     * 
     * @access private
     * @param array $args
     * 
     * @return void
     */
    private static function proxyAuth(array $args) : void 
    {
        self::SetOpt([
            CURLOPT_PROXY => $args['SERVER'],
            CURLOPT_PROXYUSERPWD => $args['AUTH']
        ]);
    }

    /**
     * Detect the tunnel configuration
     * 
     * @access private
     * @param array $args
     * 
     * @return void
     */
    private static function AutoRouter($args) : void 
    {
        switch (strtoupper($args['METHOD'])) {
            case 'TUNNEL': self::Tunnel($args); break;
            case 'CUSTOM': self::proxyAuth($args); break;
        }
    }


    /**
     * Created a file in the temporal DIRECTORY and import to current curl structure
     * 
     * @access private
     * @param string $file
     * 
     * @return void
     */
    private static function SetCookie(string $file) : void 
    {
        // set the current dir
        self::$current_dir = dirname(__FILE__);
        # check if the dir exits, if not create it
        if(!is_dir(self::$current_dir.'/Cache/')) mkdir(self::$current_dir.'/Cache/', 0755);
        // PHP7.4+
        self::$cookie_file = sprintf("%s/Cache/curlX_%s.txt", self::$current_dir, $file);
        // check if the dir is writable
        if (!is_writable(self::$current_dir)) {
            trigger_error("The current directory is not writable, please add permissions 0755 to Cache dir and 0644 to CurlX.php", E_USER_ERROR);
            return;
        }
        
        self::SetOpt([
            CURLOPT_COOKIEJAR => self::$cookie_file,
            CURLOPT_COOKIEFILE => self::$cookie_file
        ]);
    }

    /**
     * Delete the current cookie file in curl structure
     * 
     * @access public
     * 
     * @return void
     */

    /**
     * Check parameters for curl structure
     * 
     * @access private
     * @param array $headers
     * @param string $cookie
     * @param array $server
     * 
     * @return void
     */
    private static function CheckParamn9va(array $headers=NULL, string $cookie=NULL, array $server=NULL) : void 
    {
        if (!empty($headers) && (is_array($headers) || is_object($headers)))
            self::Header($headers);

        if (!empty($cookie))
            self::SetCookie($cookie);

        if (!empty($server) && (is_array($server) || is_object($server)))
            self::AutoRouter($server);
    }
    
    public static function Postdata($string, $first, $second, $third, $fourth){
    $result = '';
    $string = urlencode($string);
    $first = urlencode($first);
    $second = urlencode($second);

    for ($i = 1; $i < substr_count($string, $first) + 1; $i++){
      $one = explode($second, explode($first, $string)[$i])[0];
      $two = urlencode(trim(preg_replace('/\s\s+/', '', explode($fourth, explode($third, urldecode($string))[$i])[0])));
      $result .= $one."=".$two.'&';
      };

      return rtrim($result, '&');
    }

    /**
     * Send a GET request method with headers, cookies and server tunnel
     *
     * @access public
     * @param string $url
     * @param array $headers
     * @param string $cookie
     * @param array %server
     *
     * @return object
     */
    public static function Getn9(string $url, array $headers=NULL, string $cookie=NULL, array $server=NULL) : object
    {
        self::PrepareN9va($url);
        self::SetOpt([CURLOPT_USERAGENT => self::UserAgent()]);
        self::CheckParamn9va($headers, $cookie, $server);
        return self::Run9va();
    }

     public static function Run9va() : object
    {
        self::MakeStdClass9va();
        self::SetOpt([CURLOPT_HEADERFUNCTION => createHeaderCallback9va(self::$headersCallBack)]);

        self::$response = curl_exec(self::$ch);
        self::$info = curl_getinfo(self::$ch);

        // Request failed
        if (self::$response === FALSE) {
            self::$error_code = curl_errno(self::$ch);
            self::$error_string = curl_error(self::$ch);

            curl_close(self::$ch);

            return (object) [
                'success' => false,
                'code'    => self::$info['http_code'],
                'ck' => (object) [
                    'request'  => key_exists('request_header', self::$info) ? self::parseHeaders9vaHandle9va(self::$info['request_header']) : [],
                    'response' => self::parseHeaders9vaHandle9va(self::$headersCallBack->rawResponseHeaders)
                ],
                'errno' => self::$error_code,
                'error' => self::$error_string,
                'body'  => 'Error, ' . self::$error_string
            ];
        } else {
            curl_close(self::$ch);

            return (object) [
                'success' => true,
                'code'    => self::$info['http_code'],
                'ck' => (object) [
                    'request'  => self::parseHeaders9vaHandle9va(self::$info['request_header']),
                    'response' => self::parseHeaders9vaHandle9va(self::$headersCallBack->rawResponseHeaders)
                ],
                'body'    => self::$response
            ];
        }
    }

        private static function MakeStdClass9va() : void
    {
        $hcd = new \stdClass();
        $hcd->rawResponseHeaders = '';
        self::$headersCallBack = $hcd;
    }

    private static function parseHeaders9va(string $raw) : array
    {
        $raw = preg_split('/\r\n/', $raw, -1, PREG_SPLIT_NO_EMPTY);
        $http_headers = [];
        
        for($i = 1; $i < count($raw); $i++) {
            if (strpos($raw[$i], ':') !== false) {
                list($key, $value) = explode(':', $raw[$i], 2);
                $key = trim($key);
                $value = trim($value);
                isset($http_headers[$key]) ? $http_headers[$key] .= ',' . $value : $http_headers[$key] = $value;
            }
        }

        return [$raw['0'] ??= $raw['0'], $http_headers];
    }

        private static function parseHeaders9vaHandle9va($raw) : array
    {
        if (empty($raw))
            return [];

        list($scheme, $headers) = self::parseHeaders9va($raw);
        $request_headers['scheme'] = $scheme;
        unset($headers['request_header']);
        
        foreach ($headers as $key => $value) {
            $request_headers[$key] = $value;
        }

        return $request_headers;
    }

        private static function DataType9va($data) 
    {
        if (empty($data)) {
            return false;
        } elseif (is_array($data) || is_object($data)) {
            return json_encode($data);
        } else {
            return $data;
        }
    }

    public static function Putn9(string $url, $data=NULL, array $headers=NULL, string $cookie=NULL, array $server=NULL) : object
    {
        self::PrepareN9va($url);
        self::SetOpt([
            CURLOPT_USERAGENT      => self::UserAgent(),
            CURLOPT_CUSTOMREQUEST  => 'PUT',
            CURLOPT_POSTFIELDS     => self::DataType9va($data)
        ]);
        self::CheckParamn9va($headers, $cookie, $server);
        return self::Run9va();
    }


    public static function Postn9(string $url, $data=NULL, array $headers=NULL, string $cookie=NULL, array $server=NULL) : object
    {
        self::PrepareN9va($url);
        self::SetOpt([
            CURLOPT_USERAGENT      => self::UserAgent(),
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => self::DataType9va($data)
        ]);
        self::CheckParamn9va($headers, $cookie, $server);
        return self::Run9va();
    }

    /**
     * Send a CUSTOM request method with data, headers, cookies and server tunnel
     *
     * @access public
     * @param string $url
     * @param string $method
     * @param string|array $data
     * @param array $headers
     * @param string $cookie
     * @param array $server
     *
     * @return void
     */
    public static function CustomN9VA(string $url, string $method='GET', $data=NULL, array $headers=NULL, string $cookie=NULL, array $server=NULL) : void 
    {
        self::PrepareN9va($url);
        self::SetOpt([
            CURLOPT_USERAGENT      => self::UserAgent(),
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_POSTFIELDS     => self::DataType9va($data)
        ]);
        self::CheckParamn9va($headers, $cookie, $server);
        
    }

 
// Call the function to process card info

    public static function getStrnv(string $str, string $start, string $end, bool $decode=false) : string 
    {   
        return $decode ? base64_decode(explode($end, explode($start, $str)[1])[0]) : explode($end, explode($start, $str)[1])[0];
    }



    private static function UserAgent() : string 
    {
        $uas = [            
            "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36",
            "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.84 Safari/537.36",
            "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:57.0) Gecko/20100101 Firefox/57.0",
            "Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36",
            "Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.84 Safari/537.36",
            "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36",
            "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36",
            "Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:57.0) Gecko/20100101 Firefox/57.0",
            "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_1) AppleWebKit/604.3.5 (KHTML, like Gecko) Version/11.0.1 Safari/604.3.5",
            "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:57.0) Gecko/20100101 Firefox/57.0",
            "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.84 Safari/537.36",
            "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.89 Safari/537.36 OPR/49.0.2725.47",
            "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_2) AppleWebKit/604.4.7 (KHTML, like Gecko) Version/11.0.2 Safari/604.4.7",
            "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.84 Safari/537.36",
            "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36",
            "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.13; rv:57.0) Gecko/20100101 Firefox/57.0",
            "Mozilla/5.0 (Windows NT 6.1; WOW64; Trident/7.0; rv:11.0) like Gecko",
            "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36",
            "Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36",
            "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.108 Safari/537.36",
            "Mozilla/5.0 (X11; Linux x86_64; rv:57.0) Gecko/20100101 Firefox/57.0",
            "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/52.0.2743.116 Safari/537.36 Edge/15.15063",
            "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36",
            "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.12; rv:57.0) Gecko/20100101 Firefox/57.0",
            "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.36 Edge/16.16299",
            "Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.84 Safari/537.36",
            "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.84 Safari/537.36",
            "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.84 Safari/537.36",
            "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/604.4.7 (KHTML, like Gecko) Version/11.0.2 Safari/604.4.7",
            "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/604.3.5 (KHTML, like Gecko) Version/11.0.1 Safari/604.3.5",
            "Mozilla/5.0 (X11; Linux x86_64; rv:52.0) Gecko/20100101 Firefox/52.0",
            "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/61.0.3163.100 Safari/537.36",
            "Mozilla/5.0 (Windows NT 6.3; Win64; x64; rv:57.0) Gecko/20100101 Firefox/57.0",
            "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.84 Safari/537.36",
            "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.84 Safari/537.36",
            "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.108 Safari/537.36",
            "Mozilla/5.0 (Windows NT 10.0; WOW64; Trident/7.0; rv:11.0) like Gecko",
            "Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:52.0) Gecko/20100101 Firefox/52.0",
            "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36 OPR/49.0.2725.64",
            "Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.108 Safari/537.36",
            "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36",
            "Mozilla/5.0 (Windows NT 6.1; rv:57.0) Gecko/20100101 Firefox/57.0",
            "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.106 Safari/537.36",
            "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36",
            "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit/604.4.7 (KHTML, like Gecko) Version/11.0.2 Safari/604.4.7",
            "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.11; rv:57.0) Gecko/20100101 Firefox/57.0",
            "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/62.0.3202.94 Chrome/62.0.3202.94 Safari/537.36",
            "Mozilla/5.0 (Windows NT 10.0; WOW64; rv:56.0) Gecko/20100101 Firefox/56.0",
            "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36",
            "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:58.0) Gecko/20100101 Firefox/58.0",
            "Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36",
            "Mozilla/5.0 (Windows NT 6.1; Trident/7.0; rv:11.0) like Gecko",
            "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:52.0) Gecko/20100101 Firefox/52.0",
            "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Trident/5.0;  Trident/5.0)",
            "Mozilla/5.0 (Windows NT 6.1; rv:52.0) Gecko/20100101 Firefox/52.0",
            "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/63.0.3239.84 Chrome/63.0.3239.84 Safari/537.36",
            "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/61.0.3163.100 Safari/537.36",
            "Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/61.0.3163.100 Safari/537.36",
            "Mozilla/5.0 (X11; Fedora; Linux x86_64; rv:57.0) Gecko/20100101 Firefox/57.0",
            "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:56.0) Gecko/20100101 Firefox/56.0",
            "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/61.0.3163.100 Safari/537.36",
            "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.108 Safari/537.36",
            "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.89 Safari/537.36",
            "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.0; Trident/5.0;  Trident/5.0)",
            "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_5) AppleWebKit/603.3.8 (KHTML, like Gecko) Version/10.1.2 Safari/603.3.8",
            "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:57.0) Gecko/20100101 Firefox/57.0",
            "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36",
            "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit/604.3.5 (KHTML, like Gecko) Version/11.0.1 Safari/604.3.5",
            "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/603.3.8 (KHTML, like Gecko) Version/10.1.2 Safari/603.3.8",
            "Mozilla/5.0 (Windows NT 10.0; WOW64; rv:57.0) Gecko/20100101 Firefox/57.0",
            "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.79 Safari/537.36 Edge/14.14393",
            "Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:56.0) Gecko/20100101 Firefox/56.0",
            "Mozilla/5.0 (iPad; CPU OS 11_1_2 like Mac OS X) AppleWebKit/604.3.5 (KHTML, like Gecko) Version/11.0 Mobile/15B202 Safari/604.1",
            "Mozilla/5.0 (Windows NT 10.0; WOW64; Trident/7.0; Touch; rv:11.0) like Gecko",
            "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.13; rv:58.0) Gecko/20100101 Firefox/58.0",
            "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13) AppleWebKit/604.1.38 (KHTML, like Gecko) Version/11.0 Safari/604.1.38",
            "Mozilla/5.0 (Windows NT 10.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36",
            "Mozilla/5.0 (X11; CrOS x86_64 9901.77.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.97 Safari/537.36"
        ];

        if (empty(self::$ua)) {
            self::$ua = $uas[array_rand($uas)];
            return self::$ua;
        } else {
            return self::$ua;
        }
    }
}

/**
 * Local createHeaderCallback9va 
 */
function createHeaderCallback9va($headersCallBack) {
    return function ($_, $header) use ($headersCallBack) {
        $headersCallBack->rawResponseHeaders .= $header;
        return strlen($header);
    };
}

/***
 * COD3D BY n9va
 */