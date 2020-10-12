<?php declare(strict_types=1);

namespace JustSteveKing\UriBuilder;

use JustSteveKing\ParameterBag\ParameterBag;
use RuntimeException;

class Uri
{
    /**
     * @var string
     */
    private string $scheme;

    /**
     * @var string
     */
    private string $host;

    /**
     * @var string|null
     */
    private ?string $path = null;

    /**
     * @var ParameterBag
     */
    private ParameterBag $query;

    /**
     * @var string|null
     */
    private ?string $fragment = null;

    /**
     * @return self
     */
    public static function build(): self
    {
        return new self();
    }

    /**
     * @param  string $uri
     * @return self
     */
    public static function fromString(string $uri): self
    {
        $original = $uri;

        $uri = parse_url($uri);

        if (! is_array($uri)) {
            throw new RuntimeException("URI failed to parse using parse_url, please ensure is valid URL.");
        }

        $url = self::build()
            ->addScheme($uri['scheme'])
            ->addHost($uri['host']);

        if (isset($uri['path']) || ! is_null($uri['path'])) {
            $url->addPath($uri['path']);
        }

        if (isset($uri['query']) || ! is_null($uri['query'])) {
            $url->addQuery($uri['query']);
        }

        $fragment = parse_url($original, PHP_URL_FRAGMENT);

        if (! is_null($fragment)) {
            $url->addFragment($fragment);
        }

        return $url;
    }

    /**
     * @param  string $scheme
     * @return self
     */
    public function addScheme(string $scheme): self
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
     * @return self
     */
    public function addHost(string $host): self
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
     * @param  string|null $path
     * @return self
     */
    public function addPath(?string $path = null): self
    {
        if (is_null($path)) {
            return $this;
        }

        $this->path = (substr($path, 0, 1) === '/') ? $path : "/{$path}";

        return $this;
    }

    /**
     * @return string|null
     */
    public function path():? string
    {
        return $this->path;
    }

    /**
     * @param  string $path
     * @return self
     */
    public function addQuery(string $path): self
    {
        $this->query = ParameterBag::fromString($path);

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
     * @param  string $fragment
     * @return self
     */
    public function addFragment(string $fragment): self
    {
        if (is_null($fragment)) {
            return $this;
        }

        $this->fragment = (substr($fragment, 0, 1) === '#') ? $fragment : "#{$fragment}";

        return $this;
    }

    /**
     * @return string|null
     */
    public function fragment():? string
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
     * @return string
     */
    public function toString(): string
    {
        $url = "{$this->scheme}://{$this->host}";

        if (! is_null($this->path)) {
            $url .= "{$this->path}";
        }

        if (! empty($this->query->all())) {
            $collection = [];
            foreach ($this->query->all() as $key => $value) {
                $collection[] = "{$key}={$value}";
            }

            $url .= '?' . implode('&', $collection);
        }

        if (! is_null($this->fragment)) {
            $url .= "{$this->fragment}";
        }

        return $url;
    }
}
