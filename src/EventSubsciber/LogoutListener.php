<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\LogoutEvent;
use Symfony\Component\Security\Core\Security;

class LogoutListener implements EventSubscriberInterface
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function onLogoutEvent(LogoutEvent $event)
    {
        $user = $this->security->getUser();

        if ($user && $user->getEstado() === 'SUSPENDIDO') {
            $event->getRequest()->getSession()->getFlashBag()->add('fracaso', '¡Tu cuenta está actualmente suspendida!');
            $suspensiones = $user->getSuspensions();
             foreach ($suspensiones as $suspension) {
                    // Verificar si la suspensión está activa
                    if ($suspension->getEstado() === 'ACTIVA') {
                        // Obtener la fecha de creación
                        $fechaCreacion = $suspension->getFechaVto();

                        // Formatear la fecha (si es necesario)
                        if ($fechaCreacion instanceof \DateTimeInterface) {
                            $fechaFormateada = $fechaCreacion->format('Y-m-d'); // Cambia el formato según tus necesidades
                        } else {
                            $fechaFormateada = 'desconocida';
                        }

                        // Crear el mensaje flash
                        $mensajeFlash = sprintf('Tienes una suspensión activa hasta la fecha %s', $fechaFormateada);

                        // Agregar el mensaje flash
                        $event->getRequest()->getSession()->getFlashBag()->add('fracaso', $mensajeFlash.'. Luego podrás volver a ingresar a keprecios. Recuerda que a las 3 suspensiones, tu cuenta se dará de baja automáticamente.');
                    }
                }
        } else {
            
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            LogoutEvent::class => 'onLogoutEvent',
        ];
    }
}