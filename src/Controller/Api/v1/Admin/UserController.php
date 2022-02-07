<?php

namespace App\Controller\Api\v1\Admin;

use App\Dto\Response\Transformer\PostResponseDtoTransformer;
use App\Dto\Response\Transformer\UserProfileResponseDtoTransformer;
use App\Dto\Response\Transformer\UserResponseDtoTransformer;
use App\Entity\Image;
use App\Entity\User;
use App\Entity\Friend;
use App\Entity\Post;
use App\Repository\FriendRepository;
use App\Repository\UserRepository;
use App\Service\FileUploader;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;
use Nelmio\ApiDocBundle\Annotation\Security as OASecurity;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Security;
use App\Controller\Api\AbstractApiController;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @Route("/api/v1/admin/user", name="v1_admin_user_")
 * @OASecurity(name="Bearer")
 */
class UserController extends AbstractApiController
{
    /**
     * @var Security
     */
    private Security $security;

    /**
     * @param Security $security
     */
    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * @Route("/", name="index", methods={"GET"})
     * @OA\Tag(name="admin-user")
     *
     * @param ManagerRegistry $doctrine
     * @param SerializerInterface $serializer
     * @return Response
     */
    public function index(ManagerRegistry $doctrine, SerializerInterface $serializer): Response
    {
        // check for "view" access: calls all voters
        $this->denyAccessUnlessGranted('ROLE_MODERATOR');

        $users = $doctrine->getRepository(User::class)->findAll();
        $users = $serializer->serialize($users, 'json', ['groups' => ['admin']]);

        return $this->respond('success', json_decode($users));
    }

    /**
     * @Route("/store", name="store", methods={"POST"})
     * @OA\Tag(name="admin-user")
     *
     * @OA\Parameter(
     *     name="email",
     *     in="query",
     *     description="User email",
     *     required=false,
     *     @OA\Schema(type="email")
     * )
     * @OA\Parameter(
     *     name="password",
     *     in="query",
     *     description="User password",
     *     required=false,
     *     @OA\Schema(type="string")
     * )
     * @OA\Parameter(
     *     name="first_name",
     *     in="query",
     *     description="User first name",
     *     required=false,
     *     @OA\Schema(type="string")
     * )
     * @OA\Parameter(
     *     name="last_name",
     *     in="query",
     *     description="User last name",
     *     required=false,
     *     @OA\Schema(type="string")
     * )
     * @OA\Parameter(
     *     name="roles[]",
     *     in="query",
     *     description="User roles",
     *     @OA\Schema(type="array",items="string")
     * )
     *
     * @param ManagerRegistry $doctrine
     * @param SerializerInterface $serializer
     * @return Response
     */
    public function store(ManagerRegistry $doctrine, SerializerInterface $serializer, Request $request, UserPasswordEncoderInterface $encoder): Response
    {
        // check for "view" access: calls all voters
        $this->denyAccessUnlessGranted('ROLE_MODERATOR');

        $password = $request->get('password');
        $email = $request->get('email');
        $first_name = $request->get('first_name');
        $last_name = $request->get('last_name');
        $roles = $request->get('roles');

        $user = new User();
        $user->setPassword($encoder->encodePassword($user, $password));
        $user->setEmail($email);
        $user->setFirstName($first_name);
        $user->setLastName($last_name);
        $user->setRoles($roles);

        $entityManager = $doctrine->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        $user = $serializer->serialize($user, 'json', ['groups' => ['admin']]);

        return $this->respond('success', json_decode($user));
    }

    /**
     * @Route("/show/{id}", name="show", methods={"GET"})
     * @OA\Tag(name="admin-user")
     *
     * @param SerializerInterface $serializer
     * @param User $user
     * @return Response
     */
    public function show(User $user, SerializerInterface $serializer): Response
    {
        // check for "view" access: calls all voters
        $this->denyAccessUnlessGranted('ROLE_MODERATOR');

        $user = $serializer->serialize($user, 'json', ['groups' => ['admin']]);

        return $this->respond('success', json_decode($user));
    }

    /**
     * @Route("/update/{id}", name="update", methods={"POST"})
     *
     * @OA\Parameter(
     *     name="first_name",
     *     in="query",
     *     description="User first name",
     *     required=false,
     *     @OA\Schema(type="string")
     * )
     * @OA\Parameter(
     *     name="last_name",
     *     in="query",
     *     description="User last name",
     *     required=false,
     *     @OA\Schema(type="string")
     * )
     * @OA\Parameter(
     *     name="image",
     *     in="header",
     *     description="The post images",
     *     required=false,
     *     @OA\Schema(type="file")
     * )
     *
     * @OA\Tag(name="admin-user")
     *
     * @param User $user
     * @param ManagerRegistry $doctrine
     * @param Request $request
     * @param FileUploader $fileUploader
     * @param SerializerInterface $serializer
     * @return Response
     */
    public function update(User $user, ManagerRegistry $doctrine, Request $request, FileUploader $fileUploader, SerializerInterface $serializer): Response
    {
        // check for "view" access: calls all voters
        $this->denyAccessUnlessGranted('ROLE_EDITOR');

        if ($request->get('first_name')){
            $user->setFirstName($request->get('first_name'));
        }
        if ($request->get('last_name')){
            $user->setLastName($request->get('last_name'));
        }
        if ($request->get('roles')){
            $user->setRoles($request->get('roles'));
        }

        $entityManager = $doctrine->getManager();
        if ($request->files->get('image')){

            $img = $request->files->get('image');
            $newFilePath = $fileUploader->upload($img);

            $image = new Image();
            $image->setPath($newFilePath);
            $entityManager->persist($image);

            $user->setDp($image);
        }

        $entityManager->persist($user);
        $entityManager->flush();

        $user = $serializer->serialize($user, 'json', ['groups' => ['admin']]);

        return $this->respond('success', json_decode($user));
    }

    /**
     * @Route("/delete/{id}", name="delete", methods={"DELETE"})
     * @OA\Tag(name="admin-user")
     *
     * @param User $user
     * @param ManagerRegistry $doctrine
     * @return Response
     */
    public function delete(User $user, ManagerRegistry $doctrine): Response
    {
        // check for "view" access: calls all voters
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $entityManager = $doctrine->getManager();
        $entityManager->remove($user);
        $entityManager->flush();

        return $this->respond('success');
    }
}