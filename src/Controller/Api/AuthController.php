<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Firebase\JWT\JWT;
use OpenApi\Annotations as OA;

/**
 * @Route("/api/auth", name="auth_")
 */
class AuthController extends AbstractController
{
    /**
     * @Route("/register", name="register", methods={"POST"})
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
     * @OA\Tag(name="auth")
     */
    public function register(Request $request, UserPasswordEncoderInterface $encoder): Response
    {
        $password = $request->get('password');
        $email = $request->get('email');
        $user = new User();
        $user->setPassword($encoder->encodePassword($user, $password));
        $user->setEmail($email);
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();
        return $this->json([
            'user' => $user->getEmail()
        ]);
    }

    /**
     * @Route("/login", name="login", methods={"POST"})
     *
     * @OA\Parameter(
     *     name="email",
     *     in="query",
     *     description="User email",
     *     @OA\Schema(type="email")
     * )
     * @OA\Parameter(
     *     name="password",
     *     in="query",
     *     description="User password",
     *     @OA\Schema(type="string")
     * )
     * @OA\Tag(name="auth")
     */
    public function login(Request $request, UserRepository $userRepository, UserPasswordEncoderInterface $encoder): Response
    {
        $user = $userRepository->findOneBy([
            'email'=>$request->get('email'),
        ]);
        if (!$user || !$encoder->isPasswordValid($user, $request->get('password'))) {
            return $this->json([
                'message' => 'email or password is wrong.',
            ]);
        }
       $payload = [
           "user" => $user->getUsername(),
           "exp"  => (new \DateTime())->modify("+100 minutes")->getTimestamp(),
       ];


        $jwt = JWT::encode($payload, $this->getParameter('jwt_secret'), 'HS256');
        return $this->json([
            'message' => 'success!',
            'token' => sprintf('Bearer %s', $jwt),
        ]);
    }
}
