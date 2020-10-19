<?php declare(strict_types=1);

namespace JustSteveKing\UriBuilder\Tests;

use JustSteveKing\ParameterBag\ParameterBag;
use JustSteveKing\UriBuilder\Uri;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class UriTest extends TestCase
{
    private string $url;

    protected function setUp(): void
    {
        $this->url = "https://www.api.com/resource?include=relationship&fields['relationship']=id,name";
    }

    /**
     * @test
     */
    public function it_will_create_class_on_static_build()
    {
        $this->assertInstanceOf(
            Uri::class,
            Uri::build()
        );
    }

    /**
     * @test
     */
    public function it_will_create_from_a_string()
    {
        $class = Uri::fromString($this->url);

        $this->assertInstanceOf(
            Uri::class,
            $class
        );
    }

    /**
     * @test
     */
    public function it_will_set_the_scheme()
    {
        $url = Uri::build()->addScheme('https');

        $this->assertEquals(
            'https',
            $url->scheme()
        );
    }

    /**
     * @test
     */
    public function it_will_set_the_host()
    {
        $url = Uri::build()->addHost('domain.com');

        $this->assertEquals(
            'domain.com',
            $url->host()
        );
    }

    /**
     * @test
     */
    public function it_will_set_the_path()
    {
        $url = Uri::build()->addPath(null);

        $this->assertNull($url->path());

        $url = Uri::build()->addPath('resource');

        $this->assertEquals(
            '/resource',
            $url->path()
        );
    }

    /**
     * @test
     */
    public function it_will_set_the_query()
    {
        $url = Uri::build()->addQuery('include=test');

        $bag = $url->query();

        $this->assertInstanceOf(
            ParameterBag::class,
            $bag
        );

        $this->assertTrue(
            $bag->has('include')
        );

        $this->assertEquals(
            'test',
            $bag->get('include')
        );

        $this->assertEquals(
            [
                'include' => 'test'
            ],
            $bag->all()
        );
    }

    /**
     * @test
     */
    public function it_will_set_the_fragment()
    {
        $url = Uri::build();

        $this->assertNull($url->fragment());

        $url->addFragment('test');

        $this->assertEquals(
            '#test',
            $url->fragment()
        );

        $url->addFragment('#with-hash');

        $this->assertEquals(
            '#with-hash',
            $url->fragment()
        );
    }

    /**
     * @test
     * @throws RuntimeException
     */
    public function it_will_fail_to_build_from_a_string()
    {
        $this->expectException(RuntimeException::class);
        $url = Uri::fromString('http:///example.com');

        $url = Uri::fromString(':80');
    }

    /**
     * @test
     */
    public function it_will_convert_to_a_string()
    {
        $url = Uri::fromString($this->url);

        $this->assertEquals(
            $this->url,
            $url->toString()
        );

        $this->assertEquals(
            $this->url,
            (string) $url
        );

        $newUrl = "{$this->url}#add-fragment";

        $url = Uri::fromString($newUrl);

        $this->assertEquals(
            $newUrl,
            $url->toString()
        );

        $this->assertEquals(
            $newUrl,
            (string) $url
        );
    }

    /**
     * @test
     */
    public function it_will_let_me_build_query_params_gradually()
    {
        $url = Uri::fromString('https://www.domain.com/api/v1/resource?test=another');

        $this->assertEquals(
            ['test' => 'another'],
            $url->queryParams()
        );

        $url->addQueryParam('boolean', true);

        $this->assertEquals(
            [
                'test' => 'another',
                'boolean' => 1
            ],
            $url->queryParams()
        );
    }

    /**
     * @test
     */
    public function it_will_allow_me_to_convert_booleans_to_strings()
    {
        $url = Uri::fromString('https://www.domain.com/api/v1/resource?test=1');

        $this->assertEquals(
            ['test' => true],
            $url->queryParams()
        );

        $url->addQueryParam('another', false, true);

        $this->assertEquals(
            [
                'test' => true,
                'another' => 'false'
            ],
            $url->queryParams()
        );

        $url->addQueryParam('last', false, true);

        $this->assertEquals(
            [
                'test' => true,
                'another' => 'false',
                'last' => 'false'
            ],
            $url->queryParams()
        );

        $this->assertEquals(
            "https://www.domain.com/api/v1/resource?test=1&another=false&last=false",
            $url->toString()
        );
    }

    /**
     * @test
     */
    public function it_throws_an_exception_when_sending_unsupported_query_params()
    {
        $this->expectException(RuntimeException::class);
        $url = Uri::fromString('https://www.domain.com/api/v1/resource');

        $url->addQueryParam('array', ['test' => 'value']);
        $url->addQueryParam('object', new class{});
    }

    /**
     * @test
     */
    public function it_will_url_encode_if_requested()
    {
        $queryParam = "query=url encode me";
        $url = Uri::fromString("https://www.domain.com/api/v1/resource?{$queryParam}");

        $this->assertEquals(
            "https://www.domain.com/api/v1/resource?{$queryParam}",
            $url->toString()
        );

        $encoded = urlencode($queryParam);
        $this->assertEquals(
            "https://www.domain.com/api/v1/resource?{$encoded}",
            $url->toString(true)
        );
    }

    /**
     * @test
     */
    public function it_will_set_the_port_if_passed_and_wont_if_not()
    {
        $urlString = 'https://www.domain.com/api/v1/resource';
        $url = Uri::fromString($urlString);

        $this->assertNull($url->port());

        $url = Uri::fromString('https://www.domain.com:9000');
        $this->assertIsInt($url->port());
        $this->assertEquals(
            9000,
            $url->port()
        );

        $this->assertEquals(
            'https://www.domain.com:9000',
            $url->toString()
        );
    }
}
