<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Event\CheckPassportEvent;

#[AsEventListener(event: CheckPassportEvent::class)]
class LoginEventListener
{
    public function __construct(private RequestStack $requestStack)
    {
    }

    public function __invoke(CheckPassportEvent $event): void
    {
        $passport = $event->getPassport();
        $user = $passport->getUser();
        
        // Skip role validation for admins
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return;
        }
        
        $request = $this->requestStack->getCurrentRequest();
        if (!$request) {
            return;
        }

        $selectedRole = $request->request->get('_role');
        
        if ($selectedRole) {
            $userRoles = $user->getRoles();
            if (!in_array($selectedRole, $userRoles)) {
                throw new AuthenticationException('Votre rôle ne correspond pas à la sélection ('.$selectedRole.').');
            }
        }
    }
}
