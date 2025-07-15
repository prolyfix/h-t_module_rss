<?php
namespace Prolyfix\RssBundle\EventListener;

use App\Event\ModifiableArrayEvent;
use Doctrine\ORM\EntityManagerInterface;
use Prolyfix\RssBundle\Widget\RssWidget;
use Symfony\Bundle\SecurityBundle\Security;
use Twig\Environment as Twig;

class AddWidgetPositionsListener
{
    public function __construct(private EntityManagerInterface $em, private Security $security, private Twig $twig)
    {
    }

    public function onAppConfigureWidgetPositions(ModifiableArrayEvent $event)
    {
        $availableWidgets = $event->getData();
        $availableWidgets[] = new RssWidget($this->em, $this->security, $this->twig);
        $event->setData($availableWidgets);
    }
}