<?php

namespace Knp\JsonSchemaBundle\HttpFoundation;

use Symfony\Component\HttpFoundation\JsonResponse;

class JsonSchemaResponse extends JsonResponse
{
    public function __construct($data, $route = null)
    {
        $headers = array(
            'Content-Type' => 'application/schema+json'
        );
        if ($route) {
            $headers['Link'] = sprintf('<%s>; rel="describedBy"', $route);
        }

        parent::__construct('', 200, $headers);

        // Add pretty printing to the default encoding options supplied by
        // symfony's JsonResponse
        if (isset($this->encodingOptions) && defined('JSON_PRETTY_PRINT')) {
            $this->encodingOptions = $this->encodingOptions | JSON_PRETTY_PRINT;
        }

        $this->setData($data);
    }
}
