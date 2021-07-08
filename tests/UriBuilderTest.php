<?php declare(strict_types=1);

use JustSteveKing\UriBuilder\Uri;

use function PHPUnit\Framework\assertEquals;

it('will create a new URI Builder from a string', function () {
    $object = Uri::fromString(
        uri: url(),
    );

    expect(
        value: $object,
    )->toBeInstanceOf(
        class: Uri::class,
    );
});

it('can set the uri scheme', function () {
    $object = build();

    expect(
        value: $object->scheme(),
    )->toEqual(
        expected: '',
    );

    $object->addScheme(
        scheme: 'http',
    );

    expect(
        value: $object->scheme(),
    )->toEqual(
        expected: 'http',
    );
});

it('can set the uri host', function () {
    $object = build();

    expect(
        value: $object->host(),
    )->toEqual(
        expected: '',
    );

    $object->addHost(
        host: 'api.domain.com',
    );

    expect(
        value: $object->host(),
    )->toEqual(
        expected: 'api.domain.com',
    );
});

it('can set the uri path', function () {
    $object = build();

    expect(
        value: $object->path(),
    )->toEqual(
        expected: '',
    );

    $object->addPath(
        path: 'test',
    );

    expect(
        value: $object->path(),
    )->toEqual(
        expected: '/test',
    );
});

it('will set the query', function () {
    $object = build()->addQuery(
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

it('can set the uri fragment', function () {
    $object = build();

    expect(
        value: $object->fragment(),
    )->toEqual(
        expected: '',
    );

    $object->addFragment(
        fragment: 'test',
    );

    expect(
        value: $object->fragment(),
    )->toEqual(
        expected: '#test',
    );
});

it('will fail to build from a string', function () {
    $object = Uri::fromString(
        uri: 'http:///example.com',
    );

    $object2 = Uri::fromString(
        uri: ':80',
    );
})->throws(
    exceptionClass: InvalidArgumentException::class,
);

it('will convert a uri builder object to a string', function () {
    $url = url();

    $url = Uri::fromString(
        uri: $url,
    );

    assertEquals(
        $url,
        $url->toString()
    );

    assertEquals(
        $url,
        (string) $url
    );

    $newUrl = "{$url}#add-fragment";

    $url = Uri::fromString(
        uri: $newUrl,
    );

    assertEquals(
        $newUrl,
        $url->toString()
    );

    assertEquals(
        $newUrl,
        (string) $url
    );
});

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

it('throws an exception when an unsupported query parameter is passed', function () {
    $object = Uri::fromString(
        uri: 'https://www.domain.com/api/v1/resource',
    );

    $object->addQueryParam(
        key: 'object',
        value: new class{},
    );
})->throws(
    exceptionClass: InvalidArgumentException::class,
);

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

it('will set the port if passed in', function () {
    $urlString = 'https://www.domain.com/api/v1/resource';
    $object = Uri::fromString(
        uri: $urlString,
    );

    expect(
        value: $object->port(),
    )->toBeNull();

    $object2 = Uri::fromString(
        uri: 'https://www.domain.com:9000',
    );

    expect(
        value: $object2->port(),
    )->toBeInt()->toEqual(
        expected: 9000,
    );

    expect(
        value: $object2->toString(),
    )->toEqual(
        expected: 'https://www.domain.com:9000',
    );
});

it('cannot set the port to null', function () {
    $object = build();

    $object->addPort();
})->throws(
    exceptionClass: InvalidArgumentException::class,
);

it('cannot set the query to null', function () {
    $object = build();

    $object->addQuery();
})->throws(
    exceptionClass: InvalidArgumentException::class,
);

it('will not add a path if nothing is passed to addPath', function () {
    $object = Uri::fromString(
        uri: 'https://www.api.com',
    );

    expect(
        value: $object->toString(),
    )->toEqual(
        expected: 'https://www.api.com',
    );

    $object->addPath();

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
        value: $object->toString(),
    )->toEqual(
        expected: 'https://www.api.com',
    );

    $object->addFragment(
        fragment: '',
    );

    expect(
        value: $object->toString(),
    )->toEqual(
        expected: 'https://www.api.com',
    );
});
