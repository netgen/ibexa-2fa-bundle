<?php

declare(strict_types=1);

namespace Netgen\Bundle\Ibexa2FABundle\Listener;

use Ibexa\AdminUi\Menu\Event\ConfigureMenuEvent;
use Knp\Menu\Util\MenuManipulator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

use function count;

use const PHP_VERSION_ID;

final class MenuEventSubscriber implements EventSubscriberInterface
{
    private MenuManipulator $menuManipulator;

    private TranslatorInterface $translator;

    public function __construct(MenuManipulator $menuManipulator, TranslatorInterface $translator)
    {
        $this->menuManipulator = $menuManipulator;
        $this->translator = $translator;
    }

    public static function getSubscribedEvents(): array
    {
        if (PHP_VERSION_ID < 70400) {
            return [];
        }

        return [
            ConfigureMenuEvent::USER_MENU => ['onConfigureUserMenu', -200],
        ];
    }

    public function onConfigureUserMenu(ConfigureMenuEvent $event): void
    {
        $menu = $event->getMenu();
        $newItem = $menu->addChild(
            'user__setup_2fa',
            ['label' => $this->translator->trans('menu_label', [], 'netgen_ibexa2fa'), 'route' => '2fa_setup'],
        );

        $this->menuManipulator->moveToPosition($newItem, count($menu->getChildren()) - 2);
    }
}
