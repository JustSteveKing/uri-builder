<?php

declare(strict_types=1);

namespace JustSteveKing\UriBuilder;

use InvalidArgumentException;
use JustSteveKing\ParameterBag\ParameterBag;

use Safe\Exceptions\UrlException;

use function Safe\parse_url;

final class Uri
{
    /**
     * @param ParameterBag $query
     * @param string $scheme
     * @param string $host
     * @param int|null $port
     * @param string|null $path
     * @param string|null $fragment
     * @return void
     */
    private function __construct(
        private ParameterBag $query,
        private string $scheme = '',
        private string $host = '',
        private null|int $port = null,
        private null|string $path = null,
        private null|string $fragment = null,
    ) {
    }

    /**
     * @return Uri
     */
    public static function build(): Uri
    {
        return new Uri(
            query: new ParameterBag(),
        );
    }

    /**
     * @param string $uri
     * @return Uri
     * @throws UrlException
     * @throws InvalidArgumentException
     */
    public static function fromString(string $uri): Uri
    {
        $original = $uri;

        $uri = parse_url($uri);

        if (
            ! is_array($uri)
            || ! isset($uri['scheme'], $uri['host'])
        ) {
            throw new InvalidArgumentException(
                message: "URI failed to parse using parse_url, please ensure is valid URL. Passed in [$uri]",
            );
        }

        $url = Uri::build()
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

        if (! is_null($fragment)) {
            $url->addFragment(
                fragment: $fragment,
            );
        }

        return $url;
    }

    /**
     * @param  string $scheme
     * @return Uri
     */
    public function addScheme(string $scheme): Uri
    {
        $this->scheme = $scheme;

        return $this;
    }

    /**
     * @return string
     */
    public function scheme(): string
    {
        return $this->scheme;
    }

    /**
     * @param  string $host
     * @return Uri
     */
    public function addHost(string $host): Uri
    {
        $this->host = $host;

        return $this;
    }

    /**
     * @return string
     */
    public function host(): string
    {
        return $this->host;
    }

    /**
     * @param null|int $port
     * @throws InvalidArgumentException
     * @return Uri
     */
    public function addPort(null|int $port = null): Uri
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
     * @return null|int
     */
    public function port(): null|int
    {
        return $this->port;
    }

    /**
     * @param  null|string $path
     * @return Uri
     */
    public function addPath(null|string $path = null): Uri
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
     * Appends given path to current path value. Auto adds a slash if it needs to be added.
     *
     * @param  string $path
     * @return self
     */
    public function 
    (string $path): self
    {
        $shouldPrefixSlash = str_ends_with(
                haystack: $this->path,
                needle: '/',
            ) === false && str_starts_with(
                haystack: $path,
                needle: '/',
            ) === false;

        $this->path .= $shouldPrefixSlash ? "/$path" : "$path";

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
     * @param  null|string $query
     * @throws InvalidArgumentException
     * @return Uri
     */
    public function addQuery(null|string $query = null): Uri
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
     * @return ParameterBag
     */
    public function query(): ParameterBag
    {
        return $this->query;
    }

    /**
     * @param  string $key
     * @param  int|string|bool|null|array  $value
     * @param  bool   $covertBoolToString
     * @throws InvalidArgumentException
     * @return Uri
     */
    public function addQueryParam(string $key, int|string|bool|null|array $value, bool $covertBoolToString = false): Uri
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
     * @return array<string,mixed>
     */
    public function queryParams(): array
    {
        return $this->query->all();
    }

    /**
     * @param  string $fragment
     * @return Uri
     */
    public function addFragment(string $fragment): Uri
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
     * @return null|string
     */
    public function fragment(): null|string
    {
        return $this->fragment;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * @param bool $encode
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
