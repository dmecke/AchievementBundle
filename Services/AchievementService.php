<?php

namespace Cunningsoft\AchievementBundle\Services;

use Cunningsoft\AchievementBundle\Entity\Achievement;
use Cunningsoft\AchievementBundle\Entity\UserInterface;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Yaml\Parser;

class AchievementService
{
    /**
     * @var string
     */
    private $rootDir;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @param string $rootDir
     * @param EntityManager $entityManager
     * @param TranslatorInterface $translator
     */
    public function __construct($rootDir, EntityManager $entityManager, TranslatorInterface $translator)
    {
        $this->rootDir = $rootDir;
        $this->entityManager = $entityManager;
        $this->translator = $translator;
    }

    /**
     * @return array
     */
    public function getCategories()
    {
        return array_keys($this->readAchievementsArray());
    }

    public function getNumberOfAvailableAchievements($category)
    {
        $value = $this->readAchievementsArray();

        if ($category != 'Overall') {
            $availableAchievements = count($value[$category]);
        } else {
            $availableAchievements = 0;
            foreach ($value as $v) {
                $availableAchievements += count($v);
            }
        }

        return $availableAchievements;
    }

    /**
     * @param string $category
     *
     * @return array
     */
    public function getAvailableAchievements($category)
    {
        return array_keys($this->readAchievementsArray()[$category]);
    }

    /**
     * @param string $category
     * @param UserInterface $user
     *
     * @return array
     */
    public function getLockedAchievementsByCategoryAndPlayer($category, UserInterface $user)
    {
        $availableAchievements = $this->getAvailableAchievements($category);

        $achievements = array();
        foreach ($availableAchievements as $achievementId) {
            if (!$this->isUnlocked($achievementId, $user)) {
                $achievements[] = $achievementId;
            }
        }

        return $achievements;
    }

    /**
     * @param $category
     * @param UserInterface $user
     *
     * @return float
     */
    public function getAchievementProcessByCategory($category, UserInterface $user)
    {
        if ($this->getNumberOfAvailableAchievements($category) > 0) {
            return $this->getNumberOfAchievementsByCategory($category, $user) / $this->getNumberOfAvailableAchievements($category);
        } else {
            return 0;
        }
    }

    /**
     * @param string $category
     * @param UserInterface $user
     *
     * @return int
     */
    public function getNumberOfAchievementsByCategory($category, UserInterface $user)
    {
        return count($this->getAchievementsByCategory($category, $user));
    }

    /**
     * @param string $category
     * @param UserInterface $user
     *
     * @return Achievement[]
     */
    public function getAchievementsByCategory($category, UserInterface $user)
    {
        $allAchievements = $user->getAchievements();
        if ($category == 'Overall') {
            return $allAchievements;
        }
        $achievements = array();
        foreach ($allAchievements as $v) {
            if ($v->getCategory() == $category) {
                $achievements[] = $v;
            }
        }
        return $achievements;
    }

    /**
     * @param int $limit
     * @param UserInterface $user
     *
     * @return Achievement[]
     */
    public function getLatestAchievements($limit, UserInterface $user)
    {
        return $user->getAchievements()->slice(0, $limit);
    }

    /**
     * @param string $category
     * @param string $name
     * @param UserInterface $user
     */
    public function trigger($category, $name, UserInterface $user)
    {
        $achievements = $this->entityManager->getRepository('CunningsoftAchievementBundle:Achievement')->findBy(array('user' => $user, 'category' => $category, 'id' => $name));
        if (empty($achievements)) {
            $achievement = new Achievement();
            $achievement->setUser($user);
            $achievement->setCategory($category);
            $achievement->setId($name);
            $achievement->setUnlockedMessageShown(false);
            $achievement->setInsertDate(new \DateTime());
            $this->entityManager->persist($achievement);
            $this->entityManager->flush();
        }
    }

    public function getName($category, $achievementId)
    {
        return $this->readAchievementsArray()[$category][$achievementId]['name'];
    }

    public function getDescription($category, $achievementId)
    {
        return $this->translator->trans('description_' . $category . '_' . $achievementId, array(), 'achievements');
    }

    public function getCategoryName($category)
    {
        return $this->translator->trans('category_' . $category, array(), 'achievements');
    }

    /**
     * @param string $achievementId
     * @param UserInterface $user
     *
     * @return bool
     */
    public function isUnlocked($achievementId, UserInterface $user)
    {
        /** @var Achievement[] $playerAchievements */
        $playerAchievements = $user->getAchievements();
        foreach ($playerAchievements as $playerAchievement) {
            if ($playerAchievement->getId() == $achievementId) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array
     */
    private function readAchievementsArray()
    {
        $yaml = new Parser();
        $locator = new FileLocator($this->rootDir.'/config');

        return $yaml->parse(file_get_contents($locator->locate('achievements.yml')));
    }
}
