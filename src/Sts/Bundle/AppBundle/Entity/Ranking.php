<?php

namespace Sts\Bundle\AppBundle\Entity;

/**
 * Ranking class
 */
class Ranking
{
    protected $id;
    protected $playerRankings;
    protected $round;

    /**
     * Constructor
     */
    public function __construct()
    {
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
     * Set round
     *
     * @param \Sts\Bundle\AppBundle\Entity\Round $round
     * @return Ranking
     */
    public function setRound(\Sts\Bundle\AppBundle\Entity\Round $round = null)
    {
        $this->round = $round;

        return $this;
    }

    /**
     * Get round
     *
     * @return \Sts\Bundle\AppBundle\Entity\Round
     */
    public function getRound()
    {
        return $this->round;
    }

    /**
     * Add playerRankings
     *
     * @param \Sts\Bundle\AppBundle\Entity\PlayerRanking $playerRankings
     * @return Ranking
     */
    public function addPlayerRanking(\Sts\Bundle\AppBundle\Entity\PlayerRanking $playerRankings)
    {
        $this->playerRankings[] = $playerRankings;

        return $this;
    }

    /*
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
