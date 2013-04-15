<?php

namespace Cunningsoft\AchievementBundle\Controller;

use Cunningsoft\AchievementBundle\Entity\Achievement;
use Doctrine\ORM\EntityManager;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     * @return array
     *
     * @Template
     */
    public function achievementsAction()
    {
        return array(
            'achievementService' => $this->get('cunningsoft.achievement.service'),
        );
    }

    /**
     * @return Response
     */
    public function achievementMessagesAction()
    {
        $response = new Response();
        $response->setStatusCode(200);
        $content = '';

        if ($this->get('security.context')->isGranted('ROLE_USER')) {

            /** @var Achievement[] $achievements */
            $achievements = $this->getEntityManager()->getRepository('CunningsoftAchievementBundle:Achievement')->findBy(array('user' => $this->getUser(), 'unlockedMessageShown' => false));

            foreach ($achievements as $a) {
                $content .= '<script type="text/javascript">achievementUnlocked("' . $a->getCategory() . '", "' . $a->getId() . '", "' . $this->getAchievementService()->getName($a->getCategory(), $a->getId()) . '", "' . $this->getAchievementService()->getDescription($a->getCategory(), $a->getId()) . '")</script>';
            }
        }
        $response->setContent($content);

        return $response;
    }

    /**
     * @param string $category
     * @param string $id
     *
     * @return Response
     *
     * @Route("/{category}/{id}/markAchievementMessageShown", name="markAchievementMessageShown")
     * @Secure(roles="ROLE_USER")
     */
    public function markAchievementMessageShownAction($category, $id)
    {
        $achievement = $this->getEntityManager()->getRepository('CunningsoftAchievementBundle:Achievement')->find(array('user' => $this->getUser(), 'category' => $category, 'id' => $id));
        $achievement->setUnlockedMessageShown(true);
        $this->getEntityManager()->flush();

        return new Response();
    }

    /**
     * @return EntityManager
     */
    private function getEntityManager()
    {
        return $this->get('doctrine.orm.entity_manager');
    }

    /**
     * @return AchievementService
     */
    private function getAchievementService()
    {
        return $this->get('cunningsoft.achievement.service');
    }
}
