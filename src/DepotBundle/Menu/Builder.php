<?php

namespace DepotBundle\Menu;

use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class Builder implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function mainMenu(FactoryInterface $factory, array $options)
    {
        $menu = $factory->createItem('root');
        $menu->addChild('Accueil', array('route' => 'depot_homepage'));
        $menu->addChild('Déposer un devoir', array('route' => 'show_devoir'));
        $menu->addChild('Mes options', array('route' => 'user_profil'));
        $menu->addChild('Nouveau devoir', array('route' => 'new_devoir'));
        return $menu;
    }
}

?>