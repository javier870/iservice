<?php

namespace App\OpenApi\Annotations;

use OpenApi\Annotations\Parameter as OAParameter;

/**
 * Adds formData to OpenApi\Annotations\Parameter.
 *
 * @Annotation
 */

class Parameter extends OAParameter
{
    /**
     * @inheritdoc
     */
    public static $_types = [
        'name' => 'string',
        'in' => ['query', 'header', 'path', 'cookie', 'formData', 'body', 'requestBody'],
        'description' => 'string',
        'style' => ['matrix', 'label', 'form', 'simple', 'spaceDelimited', 'pipeDelimited', 'deepObject'],
        'required' => 'boolean',
    ];

}