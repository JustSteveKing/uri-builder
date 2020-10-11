<?php declare(strict_types=1);

namespace JustSteveKing\UriBuilder;

use JustSteveKing\ParameterBag\ParameterBag;

class Uri
{
    private ?string $path;

    private string $host;

    private string $scheme;

    private ParameterBag $query;

    public static function build(): self
    {
        return new self();
    }

    public function addScheme(string $scheme): self
    {
        $this->scheme = $scheme;

        return $this;
    }

    public function addHost(string $host): self
    {
        $this->host = $host;

        return $this;
    }

    public function addPath(string $path): self
    {
        $this->path = (substr($path, 0, 1) === '/') ? $path : "/{$path}";

        return $this;
    }

    public function addQuery(string $path): self
    {
        $this->query = ParameterBag::fromString($path);

        return $this;
    }
}
