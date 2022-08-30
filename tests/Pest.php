<?php

declare(strict_types=1);

use JustSteveKing\UriBuilder\Uri;

function url(null|string $url = null): string
{
    return $url ?? "https://www.api.com/resource?include=relationship&fields['relationship']=id,name";
}

function build(): Uri
{
    return Uri::build();
}

function random_string(): string
{
    return sha1(random_bytes(11));
}
