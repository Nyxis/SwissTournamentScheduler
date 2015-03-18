<?php

namespace Sts\Bundle\AppBundle\Entity;

/**
 * Player
 */
class Player
{
    const BYE_NAME = '*** BYE ***';

    protected $id;
    protected $name;
    protected $playerMatches;
    protected $playerRankings;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->playerMatches = new \Doctrine\Common\Collections\ArrayCollection();
        $this->playerRankings = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Player
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Add playerMatches
     *
     * @param \Sts\Bundle\AppBundle\Entity\PlayerMatch $playerMatches
     * @return Player
     */
    public function addPlayerMatch(\Sts\Bundle\AppBundle\Entity\PlayerMatch $playerMatches)
    {
        $this->playerMatches[] = $playerMatches;

        return $this;
    }

    /**
     * Remove playerMatches
     *
     * @param \Sts\Bundle\AppBundle\Entity\PlayerMatch $playerMatches
     */
    public function removePlayerMatch(\Sts\Bundle\AppBundle\Entity\PlayerMatch $playerMatches)
    {
        $this->playerMatches->removeElement($playerMatches);
    }

    /**
     * Get playerMatches
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPlayerMatches()
    {
        return $this->playerMatches;
    }

    /**
     * Add playerRankings
     *
     * @param \Sts\Bundle\AppBundle\Entity\PlayerRanking $playerRankings
     * @return Player
     */
    public function addPlayerRanking(\Sts\Bundle\AppBundle\Entity\PlayerRanking $playerRankings)
    {
        $this->playerRankings[] = $playerRankings;

        return $this;
    }

    /**
     * Remove playerRankings
     *
     * @param \Sts\Bundle\AppBundle\Entity\PlayerRanking $playerRankings
     */
    public function removePlayerRanking(\Sts\Bundle\AppBundle\Entity\PlayerRanking $playerRankings)
    {
        $this->playerRankings->removeElement($playerRankings);
    }

    /**
     * Get playerRankings
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPlayerRankings()
    {
        return $this->playerRankings;
    }
}
