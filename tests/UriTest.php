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
    }
}
