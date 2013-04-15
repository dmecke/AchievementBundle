<?php

namespace Cunningsoft\AchievementBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Achievement
{
    /**
     * @var UserInterface
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="UserInterface", inversedBy="achievements")
     */
    private $user;

    /**
     * @var string
     *
     * @ORM\Id
     * @ORM\Column(type="string", name="achievement")
     */
    private $id;

    /**
     * @var string
     * @ORM\Id
     * @ORM\Column(type="string")
     */
    private $category;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $unlockedMessageShown;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $insertDate;

    /**
     * @return \DateTime
     */
    public function getInsertDate()
    {
        return $this->insertDate;
    }

    /**
     * @param UserInterface $user
     */
    public function setUser(UserInterface $user)
    {
        $this->user = $user;
    }

    /**
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @param \DateTime $insertDate
     */
    public function setInsertDate(\DateTime $insertDate)
    {
        $this->insertDate = $insertDate;
    }

    /**
     * @param boolean $unlockedMessageShown
     */
    public function setUnlockedMessageShown($unlockedMessageShown)
    {
        $this->unlockedMessageShown = $unlockedMessageShown;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param string $category
     */
    public function setCategory($category)
    {
        $this->category = $category;
    }
}
