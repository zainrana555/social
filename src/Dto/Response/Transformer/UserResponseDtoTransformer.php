<?php

declare(strict_types=1);

namespace App\Dto\Response\Transformer;

use App\Dto\Exception\UnexpectedTypeException;
use App\Dto\Response\UserResponseDto;
use App\Entity\User;

class UserResponseDtoTransformer extends AbstractResponseDtoTransformer
{
    /**
     * @param User $user
     *
     * @return UserResponseDto
     */
    public function transformFromObject($user): UserResponseDto
    {
        if (!$user instanceof User) {
            throw new UnexpectedTypeException('Expected type of User but got ' . \get_class($user));
        }

        $dto = new UserResponseDto();
        $dto->first_name = $user->getFirstName();
        $dto->last_name = $user->getLastName();
        $dto->email = $user->getEmail();
        $dto->dp = $user->getDp();

        return $dto;
    }
}
