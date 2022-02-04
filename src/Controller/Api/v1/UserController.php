<?php

namespace App\Controller\Api\v1;

use App\Entity\User;
use App\Entity\Friend;
use App\Entity\Post;
use App\Repository\FriendRepository;
use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;
use Nelmio\ApiDocBundle\Annotation\Security as OASecurity;
use Symfony\Component\Security\Core\Security;
use App\Controller\Api\AbstractApiController;
use Symfony\Component\Serializer\SerializerInterface;
use App\Repository\PostRepository;
use App\Dto\Response\Transformer\UserResponseDtoTransformer;
use App\Dto\Response\Transformer\UserProfileResponseDtoTransformer;
use App\Dto\Response\Transformer\PostResponseDtoTransformer;

/**
 * @Route("/api/v1/user", name="v1_user_")
 * @OASecurity(name="Bearer")
 */
class UserController extends AbstractApiController
{
    /**
     * @var Security
     */
    private Security $security;

    /**
     * @var UserResponseDtoTransformer
     */
    private UserResponseDtoTransformer $userResponseDtoTransformer;

    /**
     * @var UserProfileResponseDtoTransformer
     */
    private UserProfileResponseDtoTransformer $userProfileResponseDtoTransformer;

    /**
     * @var PostResponseDtoTransformer
     */
    private PostResponseDtoTransformer $postResponseDtoTransformer;

    public function __construct(Security $security, UserResponseDtoTransformer $userResponseDtoTransformer, PostResponseDtoTransformer $postResponseDtoTransformer, UserProfileResponseDtoTransformer $userProfileResponseDtoTransformer)
    {
        $this->security = $security;
        $this->userResponseDtoTransformer = $userResponseDtoTransformer;
        $this->postResponseDtoTransformer = $postResponseDtoTransformer;
        $this->userProfileResponseDtoTransformer = $userProfileResponseDtoTransformer;
    }

    /**
     * @Route("/profile", name="profile", methods={"GET"})
     * @OA\Tag(name="user")
     */
    public function profile(SerializerInterface $serializer): Response
    {
        $user = $this->security->getUser(); // null or UserInterface, if logged in
        if (!$user) {
            return $this->respond('Couldn\'t locate the user', [], 400);
        }

//        $user = $serializer->serialize($user, 'json', ['groups' => ['normal']]);
        $dto = $this->userProfileResponseDtoTransformer->transformFromObject($user);

        return $this->respond('success', $dto);
    }

    /**
     * @Route("/timeline", name="timeline", methods={"GET"})
     * @OA\Tag(name="user")
     */
    public function timeline(ManagerRegistry $doctrine, SerializerInterface $serializer, PostRepository $postRepository): Response
    {
        $user = $this->security->getUser(); // null or UserInterface, if logged in
        if (!$user) {
            return $this->respond('Couldn\'t locate the user', [], 400);
        }

        $posts = $postRepository->findByFollowingUsers($user->getFollowing());
//        $posts = $serializer->serialize($posts, 'json', ['groups' => ['normal']]);
        $dto = $this->postResponseDtoTransformer->transformFromObjects($posts);

        return $this->respond('success', $dto);
    }

    /**
     * @Route("/follow/{id}", name="follow", methods={"POST"})
     * @OA\Tag(name="user")
     */
    public function follow(User $follow_user, ManagerRegistry $doctrine): Response
    {
        $user = $this->security->getUser(); // null or UserInterface, if logged in
        if (!$user) {
            return $this->respond('Couldn\'t locate the user', [], 400);
        }
        if ($user === $follow_user){
            return $this->respond('Invalid user id given', [], 400);
        }

        $user->addFollowing($follow_user);

        $entityManager = $doctrine->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        return $this->respond('success');
    }

    /**
     * @Route("/add-friend/{id}", name="add-friend", methods={"POST"})
     * @OA\Tag(name="user")
     */
    public function addFriend(User $add_friend, ManagerRegistry $doctrine): Response
    {
        $user = $this->security->getUser(); // null or UserInterface, if logged in
        if (!$user) {
            return $this->respond('Couldn\'t locate the user', [], 400);
        }
        if ($user === $add_friend){
            return $this->respond('Invalid user id given', [], 400);
        }

        $friend = new Friend();
        $friend->setUser($user);
        $friend->setFriendUser($add_friend);

        $entityManager = $doctrine->getManager();
        $entityManager->persist($friend);
        $entityManager->flush();

        return $this->respond('success');
    }

    /**
     * @Route("/friend-requests", name="friend-requests", methods={"GET"})
     * @OA\Tag(name="user")
     */
    public function friendRequests(FriendRepository $friendRepository, SerializerInterface $serializer): Response
    {
        $user = $this->security->getUser(); // null or UserInterface, if logged in
        if (!$user) {
            return $this->respond('Couldn\'t locate the user', [], 400);
        }

        $friend_requests = $friendRepository->getFriendRequests($user);
        $friend_requests = $serializer->serialize($friend_requests, 'json', ['groups' => ['normal', 'friend-requests']]);

        return $this->respond('success', json_decode($friend_requests));
    }

    /**
     * @Route("/accept-friend-request/{id}", name="accept-friend-request", methods={"POST"})
     * @OA\Tag(name="user")
     */
    public function acceptFriendRequest(Friend $friend_request, ManagerRegistry $doctrine): Response
    {
        $user = $this->security->getUser(); // null or UserInterface, if logged in
        if (!$user) {
            return $this->respond('Couldn\'t locate the user', [], 400);
        }
        if ($user !== $friend_request->getFriendUser()){
            return $this->respond('Invalid request id given', [], 400);
        }

        $friend_request->setAccepted(true);

        $entityManager = $doctrine->getManager();
        $entityManager->persist($friend_request);
        $entityManager->flush();

        return $this->respond('success');
    }

    /**
     * @Route("/reject-friend-request/{id}", name="reject-friend-request", methods={"POST"})
     * @OA\Tag(name="user")
     */
    public function rejectFriendRequest(Friend $friend_request, ManagerRegistry $doctrine): Response
    {
        $user = $this->security->getUser(); // null or UserInterface, if logged in
        if (!$user) {
            return $this->respond('Couldn\'t locate the user', [], 400);
        }
        if ($user !== $friend_request->getFriendUser() || $friend_request->isAccepted()){
            return $this->respond('Invalid request id given', [], 400);
        }

        $entityManager = $doctrine->getManager();
        $entityManager->remove($friend_request);
        $entityManager->flush();

        return $this->respond('success');
    }
}
