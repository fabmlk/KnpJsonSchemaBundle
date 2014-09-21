<?php

namespace Knp\JsonSchemaBundle\HttpFoundation;

use Symfony\Component\HttpFoundation\JsonResponse;

class JsonSchemaResponse extends JsonResponse
{
    public function __construct($data, $alias, $route)
    {
        parent::__construct('', 200, [
            'Content-Type' => sprintf('application/%s+json', $alias),
            'Link'         => sprintf('<%s>; rel="describedBy"', $route),
        ]);

        // Add pretty printing to the default encoding options supplied by
        // symfony's JsonResponse
        $this->encodingOptions = $this->encodingOptions | JSON_PRETTY_PRINT;

        $this->setData($data);
    }
}
