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

    public function adminMainMenu(FactoryInterface $factory, array $options)
    {
        $menu = $factory->createItem('root');
        $menu->addChild('Accueil', array('route' => 'index_admin'));
        $menu->addChild('Admin - Utilisateurs', array('route' => 'users_admin'))->setExtra('routes', ['users_admin', 'users_admin_new', 'users_admin_edit']);;
        return $menu;
    }
}

?>