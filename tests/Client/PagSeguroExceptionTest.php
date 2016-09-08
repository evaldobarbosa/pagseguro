<?php
namespace PHPSC\PagSeguro\Client;

//use GuzzleHttp\Stream\Stream;
use GuzzleHttp\Psr7\Stream;
//use GuzzleHttp\Message\Response;
use GuzzleHttp\Psr7\Response;

class PagSeguroExceptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function createShouldCreateAGenericMessageWhenStatusCodeIsNot400()
    {
        $tmpfile = tmpfile();
        fwrite($tmpfile,'Server gone mad');
        $stream = new Stream($tmpfile);
        $response = new Response(500, [], $stream);
        $exception = PagSeguroException::create($response);

        $this->assertInstanceOf(PagSeguroException::class, $exception);
        $this->assertEquals('[500] A HTTP error has occurred: Server gone mad', $exception->getMessage());
    }

    /**
     * @test
     */
    public function createShouldParseAsXmlWhenStatusCodeIs400()
    {
        $xml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<errors>
    <error>
        <code>11004</code>
        <message>Currency is required.</message>
    </error>
    <error>
        <code>11005</code>
        <message>Currency invalid value: 100</message>
    </error>
</errors>
XML;

        $message = <<<'MESSAGE'
Some errors occurred:
[11004] Currency is required.
[11005] Currency invalid value: 100
MESSAGE;
        $tmp = fopen("php://stdout", "wb");

        $stream = new Stream($tmp);
        $response = new Response(400, [], $xml);
        $exception = PagSeguroException::create($response);

        $this->assertInstanceOf(PagSeguroException::class, $exception);
        $this->assertEquals($message, $exception->getMessage());
    }
}
