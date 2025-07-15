<?php
namespace Prolyfix\RssBundle\EventListener;

use App\Event\ModifiableArrayEvent;
use Prolyfix\RssBundle\ProlyfixRssBundle;
use Doctrine\ORM\EntityManagerInterface;
use Prolyfix\RssBundle\Widget\RssWidget;
use Symfony\Bundle\SecurityBundle\Security;
use Twig\Environment as Twig;

class AddConfigurationListener
{
    public function __construct(private EntityManagerInterface $em, private Security $security, private Twig $twig)
    {
    }

    public function onAppConfigureWidgetPositions(ModifiableArrayEvent $event)
    {
        $availableConfigurations = $event->getData();
        $availableConfigurations[] = ProlyfixRssBundle::class;
        $event->setData($availableConfigurations);
    }
}