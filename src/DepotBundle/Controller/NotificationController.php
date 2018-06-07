<?php

namespace DepotBundle\Controller;

use Mgilet\NotificationBundle\Entity\Notification;
use Mgilet\NotificationBundle\NotifiableInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class NotificationController
 * the base controller for notifications
 */
class NotificationController extends Controller
{

    /**
     * List of all notifications
     *
     * @Route("/{notifiable}", name="notification_list")
     * @Method("GET")
     * @param NotifiableInterface $notifiable
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction($notifiable)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $notifiableRepo = $entityManager->getRepository('MgiletNotificationBundle:NotifiableNotification');
        return $this->render('MgiletNotificationBundle::notifications.html.twig', array(
            'notifiableNotifications' => $notifiableRepo->findAllForNotifiableId($notifiable)
        ));
    }

    /**
     * Set a Notification as seen
     *
     * @Route("/{notifiable}/mark_as_seen/{notification}", name="notification_mark_as_seen")
     * @Method("POST")
     * @param int $notifiable
     * @param Notification $notification
     *
     * @return JsonResponse
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\EntityNotFoundException
     * @throws \LogicException
     */
    public function markAsSeenAction(Request $request, $notifiable, $notification)
    {
        $manager = $this->get('mgilet.notification');
        $manager->markAsSeen(
            $manager->getNotifiableInterface($manager->getNotifiableEntityById($notifiable)),
            $manager->getNotification($notification),
            true
        );
        $referer = $request->headers->get('referer');

        return $this->redirect($referer);
    }

    /**
     * Set a Notification as unseen
     *
     * @Route("/{notifiable}/mark_as_unseen/{notification}", name="notification_mark_as_unseen")
     * @Method("POST")
     * @param $notifiable
     * @param $notification
     *
     * @return JsonResponse
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\EntityNotFoundException
     * @throws \LogicException
     */
    public function markAsUnSeenAction(Request $request, $notifiable, $notification)
    {
        $manager = $this->get('mgilet.notification');
        $manager->markAsUnseen(
            $manager->getNotifiableInterface($manager->getNotifiableEntityById($notifiable)),
            $manager->getNotification($notification),
            true
        );

        $referer = $request->headers->get('referer');

        return $this->redirect($referer);
    }

    /**
     * Set all Notifications for a User as seen
     *
     * @Route("/{notifiable}/markAllAsSeen", name="notification_mark_all_as_seen")
     * @Method("POST")
     * @param $notifiable
     *
     * @return JsonResponse
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function markAllAsSeenAction(Request $request, $notifiable)
    {
        $manager = $this->get('mgilet.notification');
        $manager->markAllAsSeen(
            $manager->getNotifiableInterface($manager->getNotifiableEntityById($notifiable)),
            true
        );

        $referer = $request->headers->get('referer');

        return $this->redirect($referer);
    }

    /**
     * erase all Notifications for a User as seen
     *
     * @Route("/{notifiable}/eraseNotifications", name="notification_erase_all")
     * @Method("POST")
     * @param $notifiable
     *
     * @return JsonResponse
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function eraseNoticationsActions(Request $request, $notifiable)
    {
        $manager = $this->get('mgilet.notification');
        $notifiable = $manager->getNotifiableInterface($manager->getNotifiableEntityById($notifiable));
        $notifications = $manager->getNotifications($notifiable);
        foreach ($notifications as $notification) {
            $manager->removeNotification([$notifiable], $notification[0], true);
        }

        $referer = $request->headers->get('referer');

        return $this->redirect($referer);
    }
}
