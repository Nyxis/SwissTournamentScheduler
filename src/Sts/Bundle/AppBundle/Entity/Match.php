<?php

namespace Sts\Bundle\AppBundle\Entity;

/**
 * Match between players
 */
class Match
{
    protected $id;
    protected $playerMatches;
    protected $round;
    protected $finished;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->playerMatchs = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set finished
     *
     * @param $finished boolean
     */
    public function setFinished($finished)
    {
        $this->finished = $finished;

        return $this;
    }

    /**
     * Get finished
     *
     * @return boolean
     */
    public function getFinished()
    {
        return $this->finished;
    }

    /**
     * Add playerMatchs
     *
     * @param \Sts\Bundle\AppBundle\Entity\PlayerMatch $playerMatchs
     * @return Match
     */
    public function addPlayerMatch(\Sts\Bundle\AppBundle\Entity\PlayerMatch $playerMatchs)
    {
        $this->playerMatchs[] = $playerMatchs;

        return $this;
    }

    /**
     * Remove playerMatchs
     *
     * @param \Sts\Bundle\AppBundle\Entity\PlayerMatch $playerMatchs
     */
    public function removePlayerMatch(\Sts\Bundle\AppBundle\Entity\PlayerMatch $playerMatchs)
    {
        $this->playerMatchs->removeElement($playerMatchs);
    }

    /**
     * Get playerMatchs
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPlayerMatches()
    {
        return $this->playerMatchs;
    }

    /**
     * Set round
     *
     * @param \Sts\Bundle\AppBundle\Entity\Round $round
     * @return Match
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
}
