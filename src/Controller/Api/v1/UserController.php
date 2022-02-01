<?php

namespace App\Controller\Api\v1;

use App\Entity\User;
use App\Entity\Post;
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

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * @Route("/timeline", name="timeline", methods={"GET"})
     */
    public function timeline(ManagerRegistry $doctrine, SerializerInterface $serializer, PostRepository $postRepository): Response
    {
        $user = $this->security->getUser(); // null or UserInterface, if logged in
        if (!$user) {
            return $this->respond('Couldn\' locate the user', [], 400);
        }

        $posts = $postRepository->findByFollowingUsers($user->getFollowing());
        $posts = $serializer->serialize($posts, 'json', ['groups' => ['normal']]);

        return $this->respond('success', json_decode($posts));
    }

    /**
     * @Route("/follow/{id}", name="follow", methods={"POST"})
     */
    public function follow(User $follow_user, ManagerRegistry $doctrine): Response
    {
        $user = $this->security->getUser(); // null or UserInterface, if logged in
        if (!$user) {
            return $this->respond('Couldn\' locate the user', [], 400);
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
}
