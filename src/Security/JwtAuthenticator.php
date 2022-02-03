<?php
namespace App\Security;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Entity\User;

class JwtAuthenticator extends AbstractGuardAuthenticator
{
    private EntityManagerInterface $em;
    private ContainerBagInterface $params;

    /**
     * @param EntityManagerInterface $em
     * @param ContainerBagInterface $params
     */
    public function __construct(EntityManagerInterface $em, ContainerBagInterface $params)
    {
        $this->em = $em;
        $this->params = $params;
    }

    /**
     * @param Request $request
     * @param AuthenticationException|null $authException
     * @return JsonResponse
     */
    public function start(Request $request, AuthenticationException $authException = null): JsonResponse
	{ 
	    $data = [ 
	        'message' => 'Authentication Required'
	    ];
	    return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
	}

    /**
     * @param Request $request
     * @return bool
     */
	public function supports(Request $request): bool
	{
	    return $request->headers->has('Authorization');
	}

    /**
     * @param Request $request
     * @return string|null
     */
	public function getCredentials(Request $request): string
	{
	        return $request->headers->get('Authorization');
	}

    /**
     * @param $credentials
     * @param UserProviderInterface $userProvider
     * @return User
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
	public function getUser($credentials, UserProviderInterface $userProvider): User
	{
	        try {
	            $credentials = str_replace('Bearer ', '', $credentials);
	            // $jwt = (array) JWT::decode(
	            //                   $credentials, 
	            //                   $this->params->get('jwt_secret'),
	            //                   ['HS256']
	            //                 );
	            $jwt = (array) JWT::decode($credentials, new Key($this->params->get('jwt_secret'), 'HS256'));
	            return $this->em->getRepository(User::class)
	                    ->findOneBy([
	                            'email' => $jwt['user'],
	                    ]);
	        }catch (\Exception $exception) {
	                throw new AuthenticationException($exception->getMessage());
	        }
	}

    /**
     * @param $credentials
     * @param UserInterface $user
     * @return bool
     */
	public function checkCredentials($credentials, UserInterface $user): bool
    {
    	// Check credentials - e.g. make sure the password is valid.
        // In case of an API token, no credential check is needed.

        // Return `true` to cause authentication success
        return true;
    }

    /**
     * @param Request $request
     * @param AuthenticationException $exception
     * @return JsonResponse
     */
	public function onAuthenticationFailure(Request $request, AuthenticationException $exception): JsonResponse
	{
	        return new JsonResponse([
	                'message' => $exception->getMessage()
	        ], Response::HTTP_UNAUTHORIZED);
	}

    /**
     * @param Request $request
     * @param TokenInterface $token
     * @param string $providerKey
     * @return Response|void|null
     */
	public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $providerKey)
	{
	    return;
	}

    /**
     * @return bool
     */
	public function supportsRememberMe(): bool
	{
	    return false;
	}

}