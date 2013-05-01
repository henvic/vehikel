<?php

class Ml_HttpTest extends PHPUnit_Framework_TestCase {
    public function headersProvider()
    {
        return [
            [
                "HTTP/1.1 201 Created\n" .
                    "Content-Length: 0\n" .
                    "Content-Type: text/html; charset=UTF-8\n" .
                    "Location: /image/7ecc99c06b4e4a6580aa51f4a259b25c/php8VcFUY.jpg\n" .
                    "Server: TornadoServer/2.1\n\n",
                [
                    "Status" => "HTTP/1.1 201 Created",
                    "Content-Length" => "0",
                    "Content-Type" => "text/html; charset=UTF-8",
                    "Location" => "/image/7ecc99c06b4e4a6580aa51f4a259b25c/php8VcFUY.jpg",
                    "Server" => "TornadoServer/2.1"
                ]
            ]
        ];
    }

    public function responseProvider()
    {
        return [
            [
                "HTTP/1.1 200 Success\n" .
                    "Content-Type: text/plain; charset=UTF-8" .
                    "\r\n\r\n" .
                    "Hello World!\n",
                [
                    "headers" => [
                        "Status" => "HTTP/1.1 200 Success",
                        "Content-Type" => "text/plain; charset=UTF-8"
                    ],
                    "body" => "Hello World!\n"
                ]
            ],
            [
                "HTTP/1.1 200 Success\n",
                [
                    "headers" => [
                        "Status" => "HTTP/1.1 200 Success"
                    ],
                    "body" => ""
                ]
            ]
        ];
    }


    /**
     * @dataProvider headersProvider
     * @param $headersText
     * @param $expected
     */
    public function testParseHeaders($headersText, $expected)
    {
        $http = new Ml_Http();

        $result = $http->parseHeaders($headersText);

        $this->assertSame($expected, $result, "Different expected header value");
    }

    /**
     * @dataProvider responseProvider
     * @param $responseText
     * @param $expected
     */
    public function testParseResponse($responseText, $expected)
    {
        $http = new Ml_Http();

        $parsedResponse = $http->parseResponse($responseText);

        $this->assertSame($expected, $parsedResponse, "Different expected response value");
    }
}
