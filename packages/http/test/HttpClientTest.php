<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Http\Test;

use PHPUnit\Framework\TestCase;
use Windwalker\Http\FormData;
use Windwalker\Http\HttpClient;
use Windwalker\Http\Request\Request;
use Windwalker\Http\Test\Mock\MockTransport;
use Windwalker\Test\Traits\BaseAssertionTrait;
use Windwalker\Uri\Uri;
use Windwalker\Uri\UriHelper;

/**
 * Test class of HttpClient
 *
 * @since 2.1
 */
class HttpClientTest extends TestCase
{
    use BaseAssertionTrait;

    /**
     * Test instance.
     *
     * @var HttpClient
     */
    protected $instance;

    /**
     * Property mock.
     *
     * @var  MockTransport
     */
    protected $transport;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->instance = $this->createClient();
    }

    /**
     * createClient
     *
     * @param  array  $options
     * @param  null   $transport
     *
     * @return  HttpClient
     */
    protected function createClient($options = [], $transport = null): HttpClient
    {
        $this->transport = $transport = $transport ?: new MockTransport();

        return new HttpClient($options, $transport);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @return void
     */
    protected function tearDown(): void
    {
    }

    /**
     * testDownload
     *
     * @return  void
     */
    public function testDownload()
    {
        $url = 'http://example.com';
        $dest = '/path/to/file';

        $this->instance->download($url, $dest);

        self::assertEquals('GET', $this->transport->request->getMethod());
        self::assertEquals('http://example.com', $this->transport->request->getRequestTarget());
        self::assertEquals('/path/to/file', $this->transport->getOption('target_file'));
    }

    /**
     * Method to test request().
     *
     * @return void
     *
     * @covers \Windwalker\Http\HttpClient::request
     */
    public function testRequest()
    {
        $url = new Uri('http://example.com/?foo=bar');

        $this->instance->request(
            'GET',
            $url,
            null,
            [
                'params' => ['flower' => 'sakura'],
                'headers' => ['X-Foo' => 'Bar'],
            ]
        );

        self::assertEquals('GET', $this->transport->request->getMethod());
        self::assertEquals('http://example.com/?foo=bar&flower=sakura', $this->transport->request->getRequestTarget());
        self::assertEquals('', $this->transport->request->getBody()->__toString());
        self::assertEquals(['X-Foo' => ['Bar']], $this->transport->request->getHeaders());

        $url = new Uri('http://example.com/?foo=bar');

        $this->instance->request('POST', $url, ['flower' => 'sakura'], ['headers' => ['X-Foo' => 'Bar']]);

        self::assertEquals('POST', $this->transport->request->getMethod());
        self::assertEquals('http://example.com/?foo=bar', $this->transport->request->getRequestTarget());
        self::assertEquals('{"flower":"sakura"}', $this->transport->request->getBody()->__toString());
        self::assertEquals(
            [
                'X-Foo' => ['Bar'],
                'Content-Type' => ['application/json; charset=utf-8']
            ],
            $this->transport->request->getHeaders()
        );

        $this->instance->request('PUT', $url, 'flower=sakura', ['headers' => ['X-Foo' => 'Bar']]);

        self::assertEquals('PUT', $this->transport->request->getMethod());
        self::assertEquals('http://example.com/?foo=bar', $this->transport->request->getRequestTarget());
        self::assertEquals('flower=sakura', $this->transport->request->getBody()->__toString());
    }

    /**
     * Method to test send().
     *
     * @return void
     */
    public function testSendRequest()
    {
        $request = new Request();

        $this->instance->sendRequest($request);

        self::assertSame($request, $this->transport->request);
    }

    /**
     * Method to test options().
     *
     * @return void
     */
    public function testOptions()
    {
        $url = 'http://example.com/?foo=bar';
        $headers = ['X-Foo' => 'Bar'];

        $this->instance->options($url, compact('headers'));

        self::assertEquals('OPTIONS', $this->transport->request->getMethod());
        self::assertEquals($url, $this->transport->request->getRequestTarget());
        self::assertEquals('Bar', $this->transport->request->getHeaderLine('X-Foo'));
    }

    /**
     * Method to test head().
     *
     * @return void
     */
    public function testHead()
    {
        $url = 'http://example.com/?foo=bar';
        $headers = ['X-Foo' => 'Bar'];

        $this->instance->head($url, compact('headers'));

        self::assertEquals('HEAD', $this->transport->request->getMethod());
        self::assertEquals($url, $this->transport->request->getRequestTarget());
        self::assertEquals('Bar', $this->transport->request->getHeaderLine('X-Foo'));
    }

    /**
     * Method to test get().
     *
     * @return void
     *
     * @covers \Windwalker\Http\HttpClient::get
     */
    public function testGet()
    {
        $url = new Uri('http://example.com/?foo=bar');

        $this->instance->get(
            $url,
            [
                'params' => ['flower' => 'sakura'],
                'headers' => ['X-Foo' => 'Bar'],
            ]
        );

        self::assertEquals('GET', $this->transport->request->getMethod());
        self::assertEquals('http://example.com/?foo=bar&flower=sakura', $this->transport->request->getRequestTarget());
        self::assertEquals('', $this->transport->request->getBody()->__toString());
        self::assertEquals(['X-Foo' => ['Bar']], $this->transport->request->getHeaders());
    }

    /**
     * Method to test post().
     *
     * @return void
     *
     * @covers \Windwalker\Http\HttpClient::post
     */
    public function testPost()
    {
        $url = 'http://example.com/?foo=bar';
        $data = ['flower' => 'sakura'];
        $headers = ['X-Foo' => 'Bar'];

        $this->instance->post($url, FormData::create($data), compact('headers'));

        self::assertEquals('POST', $this->transport->request->getMethod());
        self::assertEquals($url, $this->transport->request->getRequestTarget());
        self::assertEquals('Bar', $this->transport->request->getHeaderLine('X-Foo'));
        self::assertEquals(http_build_query($data), $this->transport->request->getBody()->__toString());
    }

    /**
     * Method to test put().
     *
     * @return void
     *
     * @covers \Windwalker\Http\HttpClient::put
     */
    public function testPut()
    {
        $url = 'http://example.com/?foo=bar';
        $data = ['flower' => 'sakura'];
        $headers = ['X-Foo' => 'Bar'];

        $this->instance->put($url, FormData::create($data), compact('headers'));

        self::assertEquals('PUT', $this->transport->request->getMethod());
        self::assertEquals($url, $this->transport->request->getRequestTarget());
        self::assertEquals('Bar', $this->transport->request->getHeaderLine('X-Foo'));
        self::assertEquals(UriHelper::buildQuery($data), $this->transport->request->getBody()->__toString());
    }

    /**
     * Method to test delete().
     *
     * @return void
     *
     * @covers \Windwalker\Http\HttpClient::delete
     */
    public function testDelete()
    {
        $url = 'http://example.com/?foo=bar';
        $data = ['flower' => 'sakura'];
        $headers = ['X-Foo' => 'Bar'];

        $this->instance->delete($url, $data, compact('headers'));

        self::assertEquals('DELETE', $this->transport->request->getMethod());
        self::assertEquals($url, $this->transport->request->getRequestTarget());
        self::assertEquals('Bar', $this->transport->request->getHeaderLine('X-Foo'));
        self::assertEquals(json_encode($data), $this->transport->request->getBody()->__toString());
    }

    /**
     * Method to test trace().
     *
     * @return void
     *
     * @covers \Windwalker\Http\HttpClient::trace
     */
    public function testTrace()
    {
        $url = 'http://example.com/?foo=bar';
        $headers = ['X-Foo' => 'Bar'];

        $this->instance->trace($url, compact('headers'));

        self::assertEquals('TRACE', $this->transport->request->getMethod());
        self::assertEquals($url, $this->transport->request->getRequestTarget());
        self::assertEquals('Bar', $this->transport->request->getHeaderLine('X-Foo'));
    }

    /**
     * Method to test patch().
     *
     * @return void
     *
     * @covers \Windwalker\Http\HttpClient::patch
     */
    public function testPatch()
    {
        $url = 'http://example.com/?foo=bar';
        $data = ['flower' => 'sakura'];
        $headers = ['X-Foo' => 'Bar'];

        $this->instance->patch($url, $data, compact('headers'));

        self::assertEquals('PATCH', $this->transport->request->getMethod());
        self::assertEquals($url, $this->transport->request->getRequestTarget());
        self::assertEquals('Bar', $this->transport->request->getHeaderLine('X-Foo'));
        self::assertEquals(json_encode($data), $this->transport->request->getBody()->__toString());
    }

    public function testCurlCmd(): void
    {
        $request = new Request();

        $request = $request->withRequestTarget('https://example.com?foo=123&bar=yoo')
            ->withMethod('POST')
            ->withAddedHeader('Content-Type', 'multipart/form-data');

        $request->getBody()->write(
            UriHelper::buildQuery(
                [
                    'foo' => 'bar',
                    'yoo' => 'GOO'
                ]
            )
        );

        $curl = $this->instance->toCurlCmd($request);

        self::assertStringSafeEquals(
            <<<CMD
            curl --location --request POST 'https://example.com?foo=123&bar=yoo' \
            --form 'foo=bar' \
            --form 'yoo=GOO'
            CMD,
            $curl
        );

        $curl = $this->instance->toCurlCmd(
            'POST',
            'https://example.com?foo=123&bar=yoo',
            HttpClient::formData(
                [
                    'foo' => 'bar',
                    'yoo' => 'GOO'
                ]
            ),
            [
                'headers' => [
                    'X-CSRF-Token' => 'qETt34lmfd'
                ]
            ]
        );

        self::assertStringSafeEquals(
            <<<CMD
            curl --location --request POST 'https://example.com?foo=123&bar=yoo' \
            --header 'X-CSRF-Token: qETt34lmfd' \
            --header 'Content-Type: application/x-www-form-urlencoded; charset=utf-8' \
            --data-urlencode 'foo=bar' \
            --data-urlencode 'yoo=GOO'
            CMD,
            $curl
        );
    }

    /**
     * Method to test getTransport().
     *
     * @return void
     *
     * @covers \Windwalker\Http\HttpClient::getTransport
     * @TODO   Implement testGetTransport().
     */
    public function testGetTransport()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test setTransport().
     *
     * @return void
     *
     * @covers \Windwalker\Http\HttpClient::setTransport
     * @TODO   Implement testSetTransport().
     */
    public function testSetTransport()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }
}
