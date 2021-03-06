<?php

namespace App\Controller\Api\v1;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Post;
use App\Entity\Image;
use Symfony\Component\Security\Core\Security;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Serializer\SerializerInterface;
use OpenApi\Annotations as OA;
use Nelmio\ApiDocBundle\Annotation\Security as OASecurity;
use App\Controller\Api\AbstractApiController;
use App\Dto\Response\Transformer\PostResponseDtoTransformer;
use Symfony\Component\String\Slugger\SluggerInterface;
use App\Service\FileUploader;

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

    /**
     * @param Security $security
     * @param PostResponseDtoTransformer $postResponseDtoTransformer
     */
    public function __construct(Security $security, PostResponseDtoTransformer $postResponseDtoTransformer)
    {
       $this->security = $security;
       $this->postResponseDtoTransformer = $postResponseDtoTransformer;
    }

    /**
     * @Route("/", name="index", methods={"GET"})
     * @OA\Tag(name="post")
     *
     * @return Response
     */
    public function index(): Response
    {
        $user = $this->security->getUser(); // null or UserInterface, if logged in
        if (!$user) {
            return $this->respond('Couldn\' locate the user', [], 400);
        }

        $posts = $user->getPosts();
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
     *
     * @param ManagerRegistry $doctrine
     * @param Request $request
     * @return Response
     */
    public function store(ManagerRegistry $doctrine, Request $request): Response
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
     *
     * @param Post $post
     * @return Response
     */
    public function show(Post $post): Response
    {
        // check for "view" access: calls all voters
        $this->denyAccessUnlessGranted('view', $post);

        $dto = $this->postResponseDtoTransformer->transformFromObject($post);

        return $this->respond('success', $dto);
    }

    /**
     * @Route("/update/{id}", name="update", methods={"POST"})
     *
     * @OA\Parameter(
     *     name="message",
     *     in="query",
     *     description="The post message",
     *     required=false,
     *     @OA\Schema(type="text")
     * )
     * @OA\Parameter(
     *     name="images[]",
     *     in="header",
     *     description="The post images",
     *     required=false,
     *     @OA\Schema(type="file")
     * )
     * @OA\Tag(name="post")
     *
     * @param Post $post
     * @param ManagerRegistry $doctrine
     * @param Request $request
     * @param FileUploader $fileUploader
     * @return Response
     */
    public function update(Post $post, ManagerRegistry $doctrine, Request $request, FileUploader $fileUploader): Response
    {
        // check for "view" access: calls all voters
        $this->denyAccessUnlessGranted('edit', $post);

        $entityManager = $doctrine->getManager();

        if(!$request->get('message')){
            return $this->respond('Message field is required', [], 400);
        };
        $post->setMessage($request->get('message'));

        if ($request->files->get('images')){
            foreach ($request->files->get('images') as $img){

                $newFilePath = $fileUploader->upload($img);

                $image = new Image();
                $image->setPath($newFilePath);
                $image->setPost($post);

                $entityManager->persist($image);
            }
        }

        $entityManager->persist($post);
        $entityManager->flush();

        $dto = $this->postResponseDtoTransformer->transformFromObject($post);

        return $this->respond('success', $dto);
    }

    /**
     * @Route("/delete/{id}", name="delete", methods={"DELETE"})
     * @OA\Tag(name="post")
     *
     * @param Post $post
     * @param ManagerRegistry $doctrine
     * @return Response
     */
    public function delete(Post $post, ManagerRegistry $doctrine): Response
    {
        // check for "view" access: calls all voters
        $this->denyAccessUnlessGranted('delete', $post);

        $entityManager = $doctrine->getManager();
        $entityManager->remove($post);
        $entityManager->flush();

        return $this->respond('success');
    }
}
