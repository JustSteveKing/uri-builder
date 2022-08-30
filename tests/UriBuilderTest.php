<?php

declare(strict_types=1);

use JustSteveKing\UriBuilder\Uri;
use Safe\Exceptions\UrlException;

it('will create a new URI Builder from a valid uri string', function () {
    $object = Uri::fromString(
        uri: url(),
    );

    expect(
        value: $object,
    )->toBeInstanceOf(
        class: Uri::class,
    );
});

it('will fail to build from a string', function (string $invalidUri) {
    Uri::fromString(
        uri: $invalidUri,
    );
})->throws(UrlException::class)->with(
    [
        'http:///example.com',
        'https:///example.com',
        'ftp:///example.com',
        ':80',
    ]
);

it('can set the uri scheme', function () {
    $testScheme = random_string();
    $object = Uri::build();

    expect(
        value: $object->scheme(),
    )->toBeEmpty();

    $object->addScheme(
        scheme: $testScheme,
    );

    expect(
        value: $object->scheme(),
    )->toEqual(
        expected: $testScheme,
    );
});

it('can set the uri host', function () {
    $testHost = random_string();
    $object = Uri::build();

    expect(
        value: $object->host(),
    )->toBeEmpty();

    $object->addHost(
        host: $testHost,
    );

    expect(
        value: $object->host(),
    )->toEqual(
        expected: $testHost,
    );
});

it('can set the uri path (with / prefix)', function () {
    $testPath = '/' . random_string();
    $object = Uri::build();

    expect(
        value: $object->path(),
    )->toBeNull();

    $object->addPath(
        path: $testPath,
    );

    expect(
        value: $object->path(),
    )->toEqual(
        expected: $testPath,
    );
});

it('adds / to the start of path where not present', function () {
    $testPath = ltrim(string: random_string(), characters: '/');
    $object = Uri::build();

    $object->addPath(
        path: $testPath,
    );
    expect(
        value: $object->path(),
    )->toStartWith(
        expected: '/'
    );
});

it('can set the uri fragment (with # prefix)', function () {
    $testFragment = '#' . random_string();
    $object = Uri::build();

    expect(
        value: $object->fragment(),
    )->toBeNull();

    $object->addFragment(
        fragment: $testFragment,
    );

    expect(
        value: $object->fragment(),
    )->toEqual(
        expected: $testFragment,
    );
});

it('adds # to start of fragment where not present', function () {
    $testFragment = ltrim(string: random_string(), characters: '#');
    $object = Uri::build();

    $object->addFragment(
        fragment: $testFragment,
    );

    expect(
        value: $object->fragment()
    )->toStartWith(
        expected: '#'
    );
});

it('will set the query', function () {
    $object = Uri::build()->addQuery(
        query: 'test=test',
    );

    expect(
        value: $object->query()->all(),
    )->toHaveCount(
        count: 1,
    )->toEqual(
        expected: ['test' => 'test'],
    );
});

it('will convert a uri builder object to a string', function (string $testUrl) {
    $url = Uri::fromString(
        uri: $testUrl,
    );

    expect(
        value: $url->toString(),
    )->toEqual(
        expected: $testUrl
    );

    expect(
        value: (string)$url,
    )->toEqual(
        expected: $testUrl
    );
})->with(
    [
        'no path'                           => 'https://www.api.com',
        'with path'                         => 'https://www.api.com/resource',
        'with fragment'                     => 'https://www.api.com#add-fragment',
        'with port'                         => 'https://www.api.com:9000',
        'with path & fragment'              => 'https://www.api.com/resource#add-fragment',
        'with path & port'                  => 'https://www.api.com:9856/resource',
        'with path, port & fragment'        => 'https://www.api.com:9856/resource#add-fragment',
        'with port & fragment'              => 'https://www.api.com:9856#add-fragment',
        'with query'                        => 'https://www.api.com?queryTest=result',
        'with path & query'                 => 'https://www.api.com/resource?queryTest=result',
        'with port & query'                 => 'https://www.api.com:9000?queryTest=result',
        'with fragment & query'             => 'https://www.api.com?queryTest=result#fragment',
        'with port, fragment & query'       => 'https://www.api.com:9000?queryTest=result#fragment',
        'with path, fragment & query'       => 'https://www.api.com/testResourcePath?queryTest=result#fragment',
        'with port, path & query'           => 'https://www.api.com:9000/portPathQuery?queryTest=result',
        'with port, path, fragment & query' => 'https://www.api.com:9000/portPathQuery?queryTest=result#fragment',
    ]
);

it('will build query parameters gradually', function () {
    $object = Uri::fromString(
        uri: 'https://www.domain.com/api/v1/resource?test=another',
    );

    expect(
        value: $object->queryParams(),
    )->toHaveCount(
        count: 1,
    )->toEqual(
        expected: ['test' => 'another'],
    );

    $object->addQueryParam(
        key: 'foo',
        value: 'bar',
    );

    expect(
        value: $object->queryParams(),
    )->toHaveCount(
        count: 2,
    )->toEqual(
        expected: ['test' => 'another', 'foo' => 'bar'],
    );
});

it('will convert booleans to string', function () {
    $object = Uri::fromString(
        uri: 'https://www.domain.com/api/v1/resource?test=1',
    );

    expect(
        value: $object->queryParams(),
    )->toHaveCount(
        count: 1,
    )->toEqual(
        expected: ['test' => true],
    );

    $object->addQueryParam(
        key: 'another',
        value: false,
        covertBoolToString: true,
    );

    expect(
        value: $object->queryParams(),
    )->toHaveCount(
        count: 2,
    )->toEqual(
        expected: [
            'test' => true,
            'another' => 'false'
        ],
    );

    expect(
        value: $object->toString(),
    )->toEqual(
        expected: 'https://www.domain.com/api/v1/resource?test=1&another=false',
    );
});

it('will encode the uri if requested', function () {
    $queryParam = "query=url encode me";

    $object = Uri::fromString(
        uri: "https://www.domain.com/api/v1/resource?$queryParam",
    );

    expect(
        value: $object->toString(),
    )->toEqual(
        expected: "https://www.domain.com/api/v1/resource?{$queryParam}",
    );

    $encoded = urlencode($queryParam);
    expect(
        value: $object->toString(
            encode: true,
        ),
    )->toEqual(
        expected: "https://www.domain.com/api/v1/resource?$encoded",
    );
});

it('will set the port if passed in', function (string $testUri, $expectedPort) {
    $object = Uri::fromString(
        uri: $testUri,
    );

    expect(
        value: $object->port(),
    )->toBe(
        expected: $expectedPort,
    );
})->with(
    [
        ['uri' => url(), 'expectedPort' => null],
        ['uri' => 'https://www.domain.com:9000', 'expectedPort' => 9000],
        ['uri' => 'http://www.domain.com:9100', 'expectedPort' => 9100],
        ['uri' => 'ftp://site.com', 'expectedPort' => null],
    ]
);

it('cannot set the port with no value', function () {
    Uri::build()->addPort();
})->throws(
    InvalidArgumentException::class,
);

it('cannot set the port explicitly to null', function () {
    Uri::build()->addPort(port: null);
})->throws(
    InvalidArgumentException::class,
);

it('cannot set the query with no value', function () {
    Uri::build()->addQuery();
})->throws(
    InvalidArgumentException::class,
);

it('cannot set the query explicitly to null', function () {
    Uri::build()->addQuery(query: null);
})->throws(
    InvalidArgumentException::class,
);

it('will not add a path if nothing is passed to addPath', function () {
    $object = Uri::fromString(
        uri: 'https://www.api.com',
    );

    expect(
        value: $object->path()
    )->toBeNull();

    $object->addPath(path: null);

    expect(
        value: $object->path()
    )->toBeNull();

    expect(
        value: $object->toString(),
    )->toEqual(
        expected: 'https://www.api.com',
    );
});

it('will not add a fragment if nothing is passed to addFragment', function () {
    $object = Uri::fromString(
        uri: 'https://www.api.com',
    );

    expect(
        value: $object->fragment(),
    )->toBeNull();

    $object->addFragment(
        fragment: '',
    );

    expect(
        value: $object->fragment(),
    )->toBeNull();

    expect(
        value: $object->toString(),
    )->toEqual(
        expected: 'https://www.api.com',
    );
});

it('will append path without adding slash if it contains it in given path', function () {
    expect(
        value: Uri::fromString(uri: 'https://www.api.com')->addPath('/test')->toString(),
    )->toEqual(
        expected: 'https://www.api.com/test',
    );
});

it('will append path without adding slash if main path has it', function () {
    expect(
        value: Uri::fromString(uri: 'https://www.api.com/')->addPath('test')->toString(),
    )->toEqual(
        expected: 'https://www.api.com/test',
    );
});

it('will append path without adding slash if main path and given path has it', function () {
    expect(
        value: Uri::fromString(uri: 'https://www.api.com/')->addPath('/test')->toString(),
    )->toEqual(
        expected: 'https://www.api.com/test',
    );
});

it('will append path without adding slash if the path is missing it', function () {
    expect(
        value: Uri::fromString(uri: 'https://www.api.com')->addPath('test/test')->toString(),
    )->toEqual(
        expected: 'https://www.api.com/test/test',
    );
});
