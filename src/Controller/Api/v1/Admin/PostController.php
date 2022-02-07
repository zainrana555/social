<?php

namespace App\Controller\Api\v1\Admin;

use App\Controller\Api\AbstractApiController;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Post;
use App\Entity\Image;
use Symfony\Component\Security\Core\Security;
use OpenApi\Annotations as OA;
use Nelmio\ApiDocBundle\Annotation\Security as OASecurity;
use App\Dto\Response\Transformer\PostResponseDtoTransformer;
use Symfony\Component\String\Slugger\SluggerInterface;
use App\Service\FileUploader;

/**
 * @Route("/api/v1/admin/post", name="v1_admin_post_")
 * @OASecurity(name="Bearer")
 */
class PostController extends AbstractApiController
{
    /**
     * @Route("/", name="index", methods={"GET"})
     * @OA\Tag(name="admin-post")
     *
     * @param ManagerRegistry $doctrine
     * @param SerializerInterface $serializer
     * @return Response
     */
    public function index(ManagerRegistry $doctrine, SerializerInterface $serializer): Response
    {
        // check for "view" access: calls all voters
        $this->denyAccessUnlessGranted('ROLE_MODERATOR');

        $posts = $doctrine->getRepository(Post::class)->findAll();
        $posts = $serializer->serialize($posts, 'json', ['groups' => ['admin']]);

        return $this->respond('success', json_decode($posts));
    }

    /**
     * @Route("/show/{id}", name="show", methods={"GET"})
     * @OA\Tag(name="admin-post")
     *
     * @param Post $post
     * @param SerializerInterface $serializer
     * @return Response
     */
    public function show(Post $post, SerializerInterface $serializer): Response
    {
        // check for "view" access: calls all voters
        $this->denyAccessUnlessGranted('view', $post);

        $post = $serializer->serialize($post, 'json', ['groups' => ['admin']]);

        return $this->respond('success', json_decode($post));
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
     * @OA\Tag(name="admin-post")
     *
     * @param Post $post
     * @param ManagerRegistry $doctrine
     * @param SerializerInterface $serializer
     * @param Request $request
     * @param FileUploader $fileUploader
     * @return Response
     */
    public function update(Post $post, ManagerRegistry $doctrine, SerializerInterface $serializer, Request $request, FileUploader $fileUploader): Response
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

        $post = $serializer->serialize($post, 'json', ['groups' => ['admin']]);

        return $this->respond('success', json_decode($post));
    }

    /**
     * @Route("/delete/{id}", name="delete", methods={"DELETE"})
     * @OA\Tag(name="admin-post")
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