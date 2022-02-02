<?php

declare(strict_types=1);

namespace App\Dto\Response\Transformer;

use App\Dto\Exception\UnexpectedTypeException;
use App\Dto\Response\PostResponseDto;
use App\Entity\Post;

class PostResponseDtoTransformer extends AbstractResponseDtoTransformer
{
    private UserResponseDtoTransformer $userResponseDtoTransformer;

    public function __construct(UserResponseDtoTransformer $userResponseDtoTransformer) {
        $this->userResponseDtoTransformer = $userResponseDtoTransformer;
    }

    /**
     * @param Post $post
     *
     * @return PostResponseDto
     */
    public function transformFromObject($post): PostResponseDto
    {
        if (!$post instanceof Post) {
            throw new UnexpectedTypeException('Expected type of Post but got ' . \get_class($post));
        }

        $dto = new PostResponseDto();
        $dto->id = $post->getId();
        $dto->message = $post->getMessage();
        $dto->user = $this->userResponseDtoTransformer->transformFromObject($post->getUser());

        return $dto;
    }
}
