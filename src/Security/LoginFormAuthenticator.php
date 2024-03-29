<?php

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\Security\Guard\PasswordAuthenticatedInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class LoginFormAuthenticator extends AbstractFormLoginAuthenticator implements PasswordAuthenticatedInterface
{
    use TargetPathTrait;

    private $entityManager;
    private $urlGenerator;
    private $csrfTokenManager;
    private $passwordEncoder;
    private $session;
    public function __construct(EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator, CsrfTokenManagerInterface $csrfTokenManager, UserPasswordEncoderInterface $passwordEncoder, SessionInterface $session)
    {
        $this->entityManager = $entityManager;
        $this->urlGenerator = $urlGenerator;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->passwordEncoder = $passwordEncoder;
        $this->session = $session;
    }

    public function supports(Request $request)
    {
        return 'app_login_user' === $request->attributes->get('_route')
            && $request->isMethod('POST');
    }

    public function getCredentials(Request $request)
    {
        $credentials = [
            'name' => $request->request->get('name'),
            'password' => $request->request->get('password'),
            'csrf_token' => $request->request->get('_csrf_token'),
        ];
        $request->getSession()->set(
            Security::LAST_USERNAME,
            $credentials['name']
        );

        return $credentials;
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $token = new CsrfToken('authenticate', $credentials['csrf_token']);
        if (!$this->csrfTokenManager->isTokenValid($token)) {
            throw new InvalidCsrfTokenException();
        }

        $user = $this->entityManager->getRepository(User::class)->findOneBy(['name' => $credentials['name']]);
 
        if (!$user) {
            // fail authentication with a custom error
            throw new CustomUserMessageAuthenticationException('Username could not be found.');
        }
        
        return $user;
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        return $this->passwordEncoder->isPasswordValid($user, $credentials['password']);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function getPassword($credentials): ?string
    {
        return $credentials['password'];
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        if ($targetPath = $this->getTargetPath($request->getSession(), $providerKey)) {
            return new RedirectResponse($targetPath);
        }
        $user = $token->getUser();
        if($user->getEstado()=='SUSPENDIDO'){
            return new RedirectResponse($this->urlGenerator->generate('app_logout'));
        }
        if ($user->getUltimaConexion() != null){
            $ultimaConexion = $user->getUltimaConexion(); 
            //$ultimaConexion->modify('+1 hour');
            $colaboraciones = $user->getColaboracions();

            $colaboracionesDespuésDeConexion = 0;
            foreach ($colaboraciones as $colaboracion) {
                $fechaColaboracion = $colaboracion->getFecha();

                if ($fechaColaboracion > $ultimaConexion) {
                   if($colaboracion->getTipo() == 'premio' or $colaboracion->getTipo() == 'mala' ){
                     $colaboracionesDespuésDeConexion++;
                    } 
                }
            }
            // Verifica si hay colaboraciones después de la última conexión
            if ($colaboracionesDespuésDeConexion > 0) {
                // Agrega un mensaje flash informando la cantidad de colaboraciones
                $userId = $user->getId();
                $url = $this->urlGenerator->generate('app_user_perfil', ['id' => $userId]);
                 $request->getSession()->getFlashBag()->add('aviso', 'Tienes ' . $colaboracionesDespuésDeConexion . ' novedades que debes revisar desde tu última conexión. Puedes verlas desde <a href='.$url.'>tu perfil</a> en Mis Colaboraciones');
            }  
        }
        $ya= new \DateTime() ;
        $user->setUltimaConexion($ya);
        $this->entityManager->flush();
        // For example : return new RedirectResponse($this->urlGenerator->generate('some_route'));
        // throw new \Exception('TODO: provide a valid redirect inside '.__FILE__);
        return new RedirectResponse($this->urlGenerator->generate('app_inicio'));
    }

    protected function getLoginUrl()
    {
        return $this->urlGenerator->generate('app_login_user');
    }
}
