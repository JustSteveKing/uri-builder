<?php declare(strict_types=1);

namespace JustSteveKing\UriBuilder;

use JustSteveKing\ParameterBag\ParameterBag;

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
    private ?string $path;

    /**
     * @var ParameterBag
     */
    private ParameterBag $query;

    /**
     * @return self
     */
    public static function build(): self
    {
        return new self();
    }

    /**
     * @param string $uri
     * @return self
     */
    public static function fromString(string $uri): self
    {
        $uri = parse_url($uri);

        if (! is_array($uri)) {
            throw new \RuntimeException("URI failed to parse using parse_url, please ensure is valid URL.");
        }

        return (new self())
            ->addScheme($uri['scheme'])
            ->addHost($uri['host'])
            ->addPath($uri['path'])
            ->addQuery($uri['query']);
    }

    /**
     * @param string $scheme
     * @return self
     */
    public function addScheme(string $scheme): self
    {
        $this->scheme = $scheme;

        return $this;
    }

    /**
     * @param string $host
     * @return self
     */
    public function addHost(string $host): self
    {
        $this->host = $host;

        return $this;
    }

    /**
     * @param string|null $path
     * @return self
     */
    public function addPath(?string $path): self
    {
        if (is_null($path)) {
            return $this;
        }

        $this->path = (substr($path, 0, 1) === '/') ? $path : "/{$path}";

        return $this;
    }

    /**
     * @param string $path
     * @return self
     */
    public function addQuery(string $path): self
    {
        $this->query = ParameterBag::fromString($path);

        return $this;
    }
}
