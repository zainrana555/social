<?php

namespace App\Controller\Api\v1;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Post;
use Symfony\Component\Security\Core\Security;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Serializer\SerializerInterface;
use OpenApi\Annotations as OA;
use Nelmio\ApiDocBundle\Annotation\Security as OASecurity;
use App\Controller\Api\AbstractApiController;

/**
 * @Route("/api/v1/post", name="v1_post_")
 * @OASecurity(name="Bearer")
 */
class PostController extends AbstractApiController
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
     * @Route("/", name="index", methods={"GET"})
     */
    public function index(ManagerRegistry $doctrine, SerializerInterface $serializer): Response
    {
        $user = $this->security->getUser(); // null or UserInterface, if logged in
        if (!$user) {
            return $this->respond('Couldn\' locate the user', [], 400);
        }

        // $posts = $doctrine->getRepository(Post::class)->findAll();
        $posts = $user->getPosts();
        $posts = $serializer->serialize($posts, 'json', ['groups' => ['normal']]);

        return $this->respond('success', json_decode($posts));
    }

    /**
     * @Route("/timeline", name="timeline", methods={"GET"})
     */
    public function timeline(ManagerRegistry $doctrine, SerializerInterface $serializer): Response
    {
        $user = $this->security->getUser(); // null or UserInterface, if logged in
        if (!$user) {
            return $this->respond('Couldn\' locate the user', [], 400);
        }

        // $posts = $doctrine->getRepository(Post::class)->findAll();
        $posts = $user->getPosts();
        $posts = $serializer->serialize($posts, 'json', ['groups' => ['normal']]);

        return $this->respond('success', json_decode($posts));
    }

    /**
     * @Route("/store", name="store", methods={"POST"})
     * 
     * @OA\Parameter(
     *     name="message",
     *     in="header",
     *     description="The post message",
     *     required=true,
     *     @OA\Schema(type="text")
     * )
     */
    public function store(ManagerRegistry $doctrine, SerializerInterface $serializer, Request $request): Response
    {
        $user = $this->security->getUser(); // null or UserInterface, if logged in
        if (!$user) {
            return $this->respond('Couldn\' locate the user', [], 400);
        }

        if(!$request->get('message')){
            return $this->respond('Message field is required', [], 400);
        };

        $post = new Post();
        $post->setMessage($request->get('message'));

        // relates this post to the user
        $post->setUser($user);

        $entityManager = $doctrine->getManager();
        $entityManager->persist($post);
        $entityManager->flush();

        return $this->respond('success');
    }

    /**
     * @Route("/show/{id}", name="show", methods={"GET"})
     */
    public function show(Post $post,ManagerRegistry $doctrine, SerializerInterface $serializer): Response
    {
        // check for "view" access: calls all voters
        $this->denyAccessUnlessGranted('view', $post);

        $post = $serializer->serialize($post, 'json', ['groups' => ['normal']]);

        return $this->respond('success', json_decode($post));
    }
}
