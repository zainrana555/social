<?php

declare(strict_types=1);

namespace App\Dto\Response;

use JMS\Serializer\Annotation as Serialization;

class UserProfileResponseDto
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
     * @Serialization\Type("int")
     */
    public int $friendsTotalCount;

    /**
     * @Serialization\Type("array<App\Dto\Response\UserResponseDto>")
     */
    public iterable $friends;

    /**
     * @Serialization\Type("int")
     */
    public int $followingTotalCount;

    /**
     * @Serialization\Type("array<App\Dto\Response\UserResponseDto>")
     */
    public iterable $following;

    /**
     * @Serialization\Type("int")
     */
    public int $followersTotalCount;

    /**
     * @Serialization\Type("array<App\Dto\Response\UserResponseDto>")
     */
    public iterable $followers;

    /**
     * @Serialization\Type("int")
     */
    public int $postsTotalCount;

    /**
     * @Serialization\Type("array<App\Dto\Response\PostResponseDto>")
     */
    public iterable $posts;
}
