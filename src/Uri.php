<?php declare(strict_types=1);

namespace JustSteveKing\UriBuilder;

use InvalidArgumentException;
use JustSteveKing\ParameterBag\ParameterBag;

final class Uri
{
    /**
     * Uri constructor.
     *
     * @param ParameterBag $query
     * @param string $scheme
     * @param string $host
     * @param int|null $port
     * @param string|null $path
     * @param string|null $fragment
     *
     * @return void
     */
    private function __construct(
        private ParameterBag $query,
        private string $scheme = '',
        private string $host = '',
        private null|int $port = null,
        private null|string $path = null,
        private null|string $fragment = null,
    ){}

    /**
     * Build a new Uri Builder.
     *
     * @return Uri
     */
    public static function build(): Uri
    {
        return new Uri(
            query: new ParameterBag(),
        );
    }

    /**
     * Build a new Uri Builder from a string.
     *
     * @param  string $uri
     *
     * @throws InvalidArgumentException
     *
     * @return self
     */
    public static function fromString(string $uri): self
    {
        $original = $uri;

        $uri = parse_url($uri);

        if (! is_array($uri)) {
            throw new InvalidArgumentException(
                message: "URI failed to parse using parse_url, please ensure is valid URL. Passed in [$uri]",
            );
        }

        $url = static::build()
            ->addScheme(
                scheme: $uri['scheme'],
            )->addHost(
                host: $uri['host'],
            );

        if (isset($uri['port'])) {
            $url->addPort(
                port: $uri['port'],
            );
        }

        if (isset($uri['path'])) {
            $url->addPath(
                path: $uri['path'],
            );
        }

        if (isset($uri['query'])) {
            $url->addQuery(
                query: $uri['query'],
            );
        }

        $fragment = parse_url($original, PHP_URL_FRAGMENT);

        if (! is_null($fragment) && $fragment !== false) {
            $url->addFragment(
                fragment: $fragment,
            );
        }

        return $url;
    }

    /**
     * Add Scheme.
     *
     * @param  string $scheme
     *
     *
     * @return self
     */
    public function addScheme(string $scheme): self
    {
        $this->scheme = $scheme;

        return $this;
    }

    /**
     * Get the Uri Scheme.
     *
     * @return string
     */
    public function scheme(): string
    {
        return $this->scheme;
    }

    /**
     * Set the Uri Host.
     *
     * @param  string $host
     *
     *
     * @return self
     */
    public function addHost(string $host): self
    {
        $this->host = $host;

        return $this;
    }

    /**
     * Get the Uri Host.
     *
     * @return string
     */
    public function host(): string
    {
        return $this->host;
    }

    /**
     * Set the Uri Port.
     *
     * @param null|int $port
     *
     * @throws InvalidArgumentException
     *
     * @return $this
     */
    public function addPort(null|int $port = null): self
    {
        if (is_null($port)) {
            throw new InvalidArgumentException(
                message: 'Cannot set port to a null value.',
            );
        }

        $this->port = $port;

        return $this;
    }

    /**
     * Get the Uri Port.
     *
     * @return null|int
     */
    public function port(): null|int
    {
        return $this->port;
    }

    /**
     * Set the Uri Path.
     *
     * @param  null|string $path
     * @return self
     */
    public function addPath(null|string $path = null): self
    {
        if (is_null($path)) {
            return $this;
        }

        $this->path = str_starts_with(
            haystack: $path,
            needle: '/',
        ) ? $path : "/$path";

        return $this;
    }

    /**
     * Get the Uri Path.
     *
     * @return null|string
     */
    public function path(): null|string
    {
        return $this->path;
    }

    /**
     * Set the Uri Query.
     *
     * @param  null|string $query
     *
     * @throws InvalidArgumentException
     *
     * @return self
     */
    public function addQuery(null|string $query = null): self
    {
        if (is_null($query)) {
            throw new InvalidArgumentException(
                message: 'Cannot set query to a null value.',
            );
        }

        $this->query = ParameterBag::fromString(
            attributes: $query,
        );

        return $this;
    }

    /**
     * Get the Uri Query.
     *
     * @return ParameterBag
     */
    public function query(): ParameterBag
    {
        return $this->query;
    }

    /**
     * Set a Query Param.
     *
     * @param  string $key
     * @param  mixed  $value
     * @param  bool   $covertBoolToString
     *
     * @throws InvalidArgumentException
     *
     * @return self
     */
    public function addQueryParam(string $key, mixed $value, bool $covertBoolToString = false): self
    {
        if (! in_array(gettype($value), ['string', 'array', 'int', 'float', 'boolean'])) {
            throw new InvalidArgumentException(
                message:'Cannot set Query Parameter to: ' . gettype($value),
            );
        }

        if ($covertBoolToString && is_bool($value)) {
            $value = ($value) ? 'true' : 'false';
        }

        $this->query->set(
            key: $key,
            value: $value,
        );

        return $this;
    }

    /**
     * Get the Uri Query Parameters.
     *
     * @return array
     */
    public function queryParams(): array
    {
        return $this->query->all();
    }

    /**
     * Set the Uri Fragment.
     *
     * @param  string $fragment
     * @return self
     */
    public function addFragment(string $fragment): self
    {
        if ($fragment === '') {
            return $this;
        }

        $this->fragment = str_starts_with(
            haystack: $fragment,
            needle:'#',
        ) ? $fragment : "#{$fragment}";

        return $this;
    }

    /**
     * Set the Uri Fragment.
     *
     * @return null|string
     */
    public function fragment(): null|string
    {
        return $this->fragment;
    }

    /**
     * Turn Uri to String - proxies to toString().
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * Turn Uri to String.
     *
     * @param bool $encode
     *
     * @return string
     */
    public function toString(bool $encode = false): string
    {
        $url = "$this->scheme://$this->host";

        if (! is_null($this->port)) {
            $url .= ":$this->port";
        }

        if (! is_null($this->path)) {
            $url .= "$this->path";
        }

        if (! empty($this->query->all())) {
            $collection = [];
            foreach ($this->query->all() as $key => $value) {
                $collection[] = "$key=$value";
            }

            $queryParamString = implode('&', $collection);

            if ($encode) {
                $queryParamString = urlencode($queryParamString);
            }

            $url .= "?$queryParamString";
        }

        if (! is_null($this->fragment)) {
            $url .= "$this->fragment";
        }

        return $url;
    }
}
