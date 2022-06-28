<?php //declare(strict_types=1);
/* 
 *
 * Http Client
 */

class Kttp
{
    const USER_AGENT = 'KumbiaPHP ';//.KUMBIA_VERSION;

    /**
     * @var string
     */
    private string $method;
    /**
     * @var string
     */
    private string $url;
    /**
     * @var string
     */
    private string $query = '';
    /**
     * @var array
     */
    private array $headers = [];
    /**
     * @var string|null
     */
    private string $body;
    /**
     * @var array
     */
    private array $curlopts = [];

    protected function __construct(string $method, string $url, array $headers = [])
    {
        $this->method = $method;
        $this->url = $url;
        $this->headers = $headers;
    }

    public static function get(string $url): self
    {
        return self::request('GET', $url);
    }

    public static function post(string $url): self
    {
        return self::request('POST', $url);
    }

    public static function request(string $method, string $url, array $headers = []): self
    {
        return new self(strtoupper($method) , $url, $headers);
    }

    public function query(array $params): self
    {
        $this->query = '?' . http_build_query($params);
        return $this;
    }

    public function header(string $header, string $value): self
    {
        $this->headers[$header] = $value;
        return $this;
    }

    public function headerArray(array $headers): self
    {
        $this->headers = array_merge($this->headers, $headers); // mmm review
        return $this;
    }

    public function body(string $data, string $type = 'application/json')
    {
        $this->body = $data;
        $this->headers['Content-Type'] = $type;
        return $this;
    }

    public function jsonBody(array $data): self
    {
        //añadir exception si falla
        return $this->body(json_encode($data));
    }

    /**
     * Create a form urlencoded from an array
     *
     * @param array $data
     * @return self
     */
    public function formBody(array $data): self
    {
        return $this->body(http_build_query($data), 'application/x-www-form-urlencoded');
    }

    /**
     * https://www.php.net/manual/en/function.curl-setopt.php
     * https://curl.se/libcurl/c/curl_easy_setopt.html
     * 
     * @param int   $key    A constant CURLOPT_xxxx
     * @param mixed $value
     */
    public function setopt(int $key, $value): self
    {
        $this->curlopts[$key] = $value;
        return $this;
    }

    /**
     * https://www.php.net/manual/en/function.curl-setopt.php
     * https://curl.se/libcurl/c/curl_easy_setopt.html
     * 
     * @param array $options A list of constants CURLOPT_XXXXXS and values
     */
    public function setoptArray(array $options): self
    {
        $this->curlopts = $options + $this->curlopts;
        return $this;
    }
    
    protected function joinHeaders(): array
    {
        $headers = [];
        foreach ($this->headers as $key => $value) {
            $headers[] = "$key: $value";
        }
        return $headers;
    }


    protected function getOptions(): array
    {
        $options = [
            CURLOPT_USERAGENT      => self::USER_AGENT,
            CURLOPT_URL            => $this->url . $this->query,
            CURLOPT_CUSTOMREQUEST  => $this->method,
            CURLOPT_HTTPHEADER     => $this->joinHeaders(),
            CURLOPT_ENCODING       => '',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT        => 5
        ];

        if (isset($this->body)) {
            $options[CURLOPT_POSTFIELDS] = $this->body;
        }

        if ($this->curlopts) {
            $options = $this->curlopts + $options;
        }

        return $options;
    }

    public function send(): string
    {
        $ch = curl_init();
        curl_setopt_array($ch, $this->getOptions());

        $result = curl_exec($ch);
        if ($result === false) {
            $error = curl_error($ch);
            $errno = curl_errno($ch);
            curl_close($ch);
            throw new RuntimeException($error, $errno);
        }
        curl_close($ch);
        return $result;
    }

    /**
     * @return mixed
     */
    public function getJson(bool $assoc = true, int $depth = 512, int $flags = 0)
    {
        $this->headers['Accept'] = 'application/json';
        return json_decode($this->send(), $assoc, $depth, JSON_THROW_ON_ERROR|$flags);
    }
    // Use Kumbia parsers
}
