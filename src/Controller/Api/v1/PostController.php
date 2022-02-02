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
use App\Dto\Response\Transformer\PostResponseDtoTransformer;

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

    /**
     * @var PostResponseDtoTransformer
     */
    private PostResponseDtoTransformer $postResponseDtoTransformer;

    public function __construct(Security $security, PostResponseDtoTransformer $postResponseDtoTransformer)
    {
       $this->security = $security;
       $this->postResponseDtoTransformer = $postResponseDtoTransformer;
    }

    /**
     * @Route("/", name="index", methods={"GET"})
     * @OA\Tag(name="post")
     */
    public function index(ManagerRegistry $doctrine, SerializerInterface $serializer): Response
    {
        $user = $this->security->getUser(); // null or UserInterface, if logged in
        if (!$user) {
            return $this->respond('Couldn\' locate the user', [], 400);
        }

        // $posts = $doctrine->getRepository(Post::class)->findAll();
        $posts = $user->getPosts();
//        $posts = $serializer->serialize($posts, 'json', ['groups' => ['normal']]);
        $dto = $this->postResponseDtoTransformer->transformFromObjects($posts);

        return $this->respond('success', $dto);
    }

    /**
     * @Route("/store", name="store", methods={"POST"})
     * 
     * @OA\Parameter(
     *     name="message",
     *     in="query",
     *     description="The post message",
     *     required=false,
     *     @OA\Schema(type="text")
     * )
     * @OA\Tag(name="post")
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

        $dto = $this->postResponseDtoTransformer->transformFromObject($post);

        return $this->respond('success', $dto);
    }

    /**
     * @Route("/show/{id}", name="show", methods={"GET"})
     * @OA\Tag(name="post")
     */
    public function show(Post $post,ManagerRegistry $doctrine, SerializerInterface $serializer): Response
    {
        // check for "view" access: calls all voters
        $this->denyAccessUnlessGranted('view', $post);

//        $post = $serializer->serialize($post, 'json', ['groups' => ['normal']]);
        $dto = $this->postResponseDtoTransformer->transformFromObject($post);

        return $this->respond('success', $dto);
    }

    /**
     * @Route("/update/{id}", name="update", methods={"PATCH"})
     *
     * @OA\Parameter(
     *     name="message",
     *     in="query",
     *     description="The post message",
     *     required=false,
     *     @OA\Schema(type="text")
     * )
     * @OA\Tag(name="post")
     */
    public function update(Post $post, ManagerRegistry $doctrine, Request $request): Response
    {
        if(!$request->get('message')){
            return $this->respond('Message field is required', [], 400);
        };

        // check for "view" access: calls all voters
        $this->denyAccessUnlessGranted('edit', $post);

        $post->setMessage($request->get('message'));

        $entityManager = $doctrine->getManager();
        $entityManager->persist($post);
        $entityManager->flush();

        $dto = $this->postResponseDtoTransformer->transformFromObject($post);

        return $this->respond('success', $dto);
    }

    /**
     * @Route("/delete/{id}", name="delete", methods={"DELETE"})
     * @OA\Tag(name="post")
     */
    public function delete(Post $post, ManagerRegistry $doctrine): Response
    {
        // check for "view" access: calls all voters
        $this->denyAccessUnlessGranted('edit', $post);

        $entityManager = $doctrine->getManager();
        $entityManager->remove($post);
        $entityManager->flush();

        return $this->respond('success');
    }
}
