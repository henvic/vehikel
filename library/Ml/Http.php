<?php

class Ml_Http
{
    public function parseHeaders($headersText)
    {
        $headers = [];

        foreach (explode("\n", $headersText) as $i => $line) {
            if ($i === 0) {
                $headers['Status'] = $line;
            } else {
                $lineContentArray = explode(': ', $line, 2);

                if (sizeof($lineContentArray) == 2) {
                    list ($key, $value) = explode(': ', $line);
                    $headers[$key] = $value;
                }
            }
        }

        return $headers;
    }

    public function parseResponse($response)
    {
        $content = explode("\r\n\r\n", $response, 2);

        $contentSize = sizeof($content);

        $data = [];

        $data["headers"] = $this->parseHeaders($content[0]);

        if ($contentSize == 2) {
            $data["body"] = $content[1];
        } else {
            $data["body"] = "";
        }

        return $data;
    }
}