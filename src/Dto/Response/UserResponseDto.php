<?php

declare(strict_types=1);

namespace App\Dto\Response;

use App\Entity\Image;
use JMS\Serializer\Annotation as Serialization;

class UserResponseDto
{
    /**
     * @Serialization\Type("string")
     */
    public string $first_name;

    /**
     * @Serialization\Type("string")
     */
    public string $last_name;

    /**
     * @Serialization\Type("string")
     */
    public string $email;

    /**
     * @Serialization\Type("App\Entity\Image")
     */
    public $dp;
}
