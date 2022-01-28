<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use App\Entity\Post;
use Symfony\Component\Security\Core\Security;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Serializer\SerializerInterface;
use OpenApi\Annotations as OA;

class PostController extends AbstractController
{
    /**
     * @var Security
     */
    private $security;

    public function __construct(Security $security)
    {
       $this->security = $security;
    }

    /**
     * @Route("api/v1/post", name="api_v1_post", methods={"GET"})
     */
    public function index(ManagerRegistry $doctrine, SerializerInterface $serializer): Response
    {
        $user = $this->security->getUser(); // null or UserInterface, if logged in
        if (!$user) {
            return $this->json([
                'message'   => 'Could\'n locate the user'
            ]);
        }

        // $posts = $doctrine->getRepository(Post::class)->findAll();
        $posts = $user->getPosts();
        $posts = $serializer->serialize($posts, 'json', ['groups' => ['normal']]);

        return $this->json([
            'message'   => 'Success!',
            'data'      => json_decode($posts),
        ]);
    }

    /**
     * @Route("api/v1/post/store", name="api_v1_post_store", methods={"POST"})
     * 
     * @OA\Parameter(
     *     name="message",
     *     in="header",
     *     description="The post message",
     *     @OA\Schema(type="text")
     * )
     */
    public function store(ManagerRegistry $doctrine, SerializerInterface $serializer, Request $request): Response
    {
        $user = $this->security->getUser(); // null or UserInterface, if logged in
        if (!$user) {
            return $this->json([
                'message'   => 'Could\'n locate the user'
            ]);
        }

        if(!$request->get('message')){
            return $this->json([
                'error' => 'Message field is required',
            ], 400);
        };

        $post = new Post();
        $post->setMessage($request->get('message'));

        // relates this post to the user
        $post->setUser($user);

        $entityManager = $doctrine->getManager();
        $entityManager->persist($post);
        $entityManager->flush();

        return $this->json([
            'message' => 'Success!',
        ]);
    }

    /**
     * @Route("api/v1/post/show/{id}", name="api_v1_post_show", methods={"GET"})
     */
    public function show(ManagerRegistry $doctrine, int $id, SerializerInterface $serializer): Response
    {
        $post = $doctrine->getRepository(Post::class)->find($id);

        if (!$post) {
            throw $this->createNotFoundException(
                'No post found for id '.$id
            );
        }

        // check for "view" access: calls all voters
        $this->denyAccessUnlessGranted('view', $post);

        $post = $serializer->serialize($post, 'json', ['groups' => ['normal']]);

        return $this->json([
            'message'   => 'Success!',
            'data'      => json_decode($post),
        ]);
    }
}
