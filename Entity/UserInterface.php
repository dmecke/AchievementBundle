<?php

namespace Cunningsoft\AchievementBundle\Entity;

interface UserInterface
{
    /**
     * @return int
     */
    public function getId();

    /**
     * @return Achievement[]
     */
    public function getAchievements();
}
