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
        $ts = $this->container->get('security.token_storage');

        $user = $ts->getToken()->getUser();

        $menu = $factory->createItem('root');

        if($user->hasRole('ROLE_ADMIN')) {
            $menu->addChild('Admin - Utilisateurs', array('route' => 'users_admin'))->setExtra('routes', ['users_admin', 'users_admin_new', 'users_admin_edit']);
            $menu->addChild('Admin - UE', array('route' => 'ue_list'));
            $menu->addChild('Admin - Import', array('route' => 'import_admin'));
        }
        
        if($user->hasRole('ROLE_ENSEIGNANT')) {
            $menu->addChild('Nouveau devoir', array('route' => 'new_devoir'));
        }

        if($user->hasRole('ROLE_ENSEIGNANT') || $user->hasRole('ROLE_ETUDIANT')) {
            $menu->addChild('Mes Devoirs', array('route' => 'depot_homepage'));

            $menu->addChild('Mon Profil', array('route' => 'fos_user_profile_show'));
        }

        return $menu;
    }

    public function adminMainMenu(FactoryInterface $factory, array $options)
    {
        $menu = $factory->createItem('root');
        $menu->addChild('Admin - Utilisateurs', array('route' => 'users_admin'))->setExtra('routes', ['users_admin', 'users_admin_new', 'users_admin_edit']);;
        $menu->addChild('Admin - UE', array('route' => 'ue_list'));
        $menu->addChild('Admin - Import', array('route' => 'import_admin'));

        return $menu;
    }
}

?>