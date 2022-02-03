<?php

declare(strict_types=1);

namespace App\Dto\Response;

use JMS\Serializer\Annotation as Serialization;

class PostResponseDto
{
    /**
     * @Serialization\Type("int")
     */
    public int $id;

    /**
     * @Serialization\Type("text")
     */
    public string $message;

    /**
     * @Serialization\Type("array<App\Dto\Response\ImageResponseDto>")
     */
    public iterable $images;

    /**
     * @Serialization\Type("App\Dto\Response\UserResponseDto")
     */
    public UserResponseDto $user;
}
