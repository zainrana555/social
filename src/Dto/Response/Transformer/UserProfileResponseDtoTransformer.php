<?php

declare(strict_types=1);

namespace App\Dto\Response\Transformer;

use App\Dto\Exception\UnexpectedTypeException;
use App\Dto\Response\UserProfileResponseDto;
use App\Entity\User;
use App\Repository\UserRepository;

class UserProfileResponseDtoTransformer extends AbstractResponseDtoTransformer
{
    private UserResponseDtoTransformer $userResponseDtoTransformer;
    private PostResponseDtoTransformer $postResponseDtoTransformer;
    private UserRepository $userRepository;

    public function __construct(UserResponseDtoTransformer $userResponseDtoTransformer, PostResponseDtoTransformer $postResponseDtoTransformer, UserRepository $userRepository) {
        $this->userResponseDtoTransformer = $userResponseDtoTransformer;
        $this->postResponseDtoTransformer = $postResponseDtoTransformer;
        $this->userRepository = $userRepository;
    }

    /**
     * @param User $user
     *
     * @return UserProfileResponseDto
     */
    public function transformFromObject($user): UserProfileResponseDto
    {
        if (!$user instanceof User) {
            throw new UnexpectedTypeException('Expected type of User but got ' . \get_class($user));
        }

        $dto = new UserProfileResponseDto();
        $dto->first_name = $user->getFirstName();
        $dto->last_name = $user->getLastName();
        $dto->email = $user->getEmail();
        $dto->friendsTotalCount = count($this->userRepository->getUserFriends($user));
        $dto->friends = $this->userResponseDtoTransformer->transformFromObjects($this->userRepository->getUserFriends($user));
        $dto->followingTotalCount = $user->getFollowing()->count();
        $dto->following = $this->userResponseDtoTransformer->transformFromObjects($user->getFollowing());
        $dto->followersTotalCount = $user->getFollowers()->count();
        $dto->followers = $this->userResponseDtoTransformer->transformFromObjects($user->getFollowers());
        $dto->posts = $this->postResponseDtoTransformer->transformFromObjects($user->getPosts());

        return $dto;
    }
}
