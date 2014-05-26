<?php

class Config implements \ArrayAccess {

    /**
     * @var string
     */
    protected $delimiter = '.';

    /**
     * @var array
     */
    protected $data = array();

    /**
     * @var array
     */
    protected $cache = array();

    /**
     * @param string $path
     * @param string|null $environment
     * @throws \Exception if the format is not supported or the environment is not found
     */
    public function __construct($path, $environment = null) {
        if (file_exists($path) == false || is_file($path) == false) {
            throw new \Exception("Cannot find path: {$path}");
        }
        $file = pathinfo($path);
        $format = strtoupper($file['extension']);
        $method = 'load' . $format;
        if (method_exists($this, $method)) {
            $data = $this->$method($path);
            if ($environment !== null) {
                if (isset($data[$environment])) {
                    $this->data = $data[$environment];
                } else {
                    throw new \Exception("Environment '{$environment}' not found");
                }
            } else {
                $this->data = $data;
            }
        } else {
            throw new \Exception("Unsupported format: {$format}");
        }
    }

    /**
     * @param $path
     * @param string|null $environment
     * @return Config
     */
    public static function load($path, $environment = null) {
        return new Config($path, $environment);
    }

    /**
     * @param string $key
     * @param null $default
     * @return mixed
     */
    public function get($key, $default = null) {
        if (isset($cache[$key])) {
            return $cache[$key];
        }
        $words = explode($this->delimiter, $key);
        $base = $this->data;
        $object = end($words);
        foreach ($words as $word) {
            if (isset($base[$word])) {
                if ($word === $object) {
                    $cache[$key] = $base[$word];
                    return $base[$word];
                } else {
                    $base = $base[$word];
                }
            } else {
                return $default;
            }
        }
    }

    /**
     * @param string $delimiter
     * @throws Exception
     */
    public function setDelimiter($delimiter) {
        if (is_string($delimiter) && strlen($delimiter) === 1) {
            $this->delimiter = $delimiter;
            $this->cache = array();
        } else {
            throw new \Exception('Delimiter must be a non-empty string.');
        }
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset) {
        return !is_null($this->get($offset));
    }

    /**
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset) {
        return $this->get($offset);
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value) {

    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset) {

    }

    /**
     * @param string $path
     * @return array
     * @throws Exception
     */
    protected function loadINI($path) {
        $data = @parse_ini_file($path, true);
        if ($data) {
            return $data;
        } else {
            throw new \Exception('INI parse error');
        }
    }

    /**
     * @param string $path
     * @throws Exception
     * @return array
     */
    protected function loadJSON($path) {
        $data = file_get_contents($path);
        $json = json_decode($data, true);
        if ($json === null) {
            throw new \Exception('JSON parse error');
        } else {
            return (array) $json;
        }
    }
}
