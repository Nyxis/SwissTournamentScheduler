<?php

namespace Sts\Bundle\AppBundle\Entity;

/**
 * Ranking player relation class
 */
class PlayerRanking
{
    protected $id;
    protected $player;
    protected $ranking;
    protected $rank;
    protected $score;
    protected $average;

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
     * Set rank
     *
     * @param integer $rank
     * @return PlayerRanking
     */
    public function setRank($rank)
    {
        $this->rank = $rank;

        return $this;
    }

    /**
     * Get rank
     *
     * @return integer
     */
    public function getRank()
    {
        return $this->rank;
    }

    /**
     * Set score
     *
     * @param integer $score
     * @return PlayerRanking
     */
    public function setScore($score)
    {
        $this->score = $score;

        return $this;
    }

    /**
     * Get score
     *
     * @return integer
     */
    public function getScore()
    {
        return $this->score;
    }

    /**
     * Set average
     *
     * @param integer $average
     * @return PlayerRanking
     */
    public function setAverage($average)
    {
        $this->average = $average;

        return $this;
    }

    /**
     * Get average
     *
     * @return integer
     */
    public function getAverage()
    {
        return $this->average;
    }

    /**
     * Set ranking
     *
     * @param \Sts\Bundle\AppBundle\Entity\Ranking $ranking
     * @return PlayerRanking
     */
    public function setRanking(\Sts\Bundle\AppBundle\Entity\Ranking $ranking = null)
    {
        $this->ranking = $ranking;

        return $this;
    }

    /**
     * Get ranking
     *
     * @return \Sts\Bundle\AppBundle\Entity\Ranking
     */
    public function getRanking()
    {
        return $this->ranking;
    }

    /**
     * Set player
     *
     * @param \Sts\Bundle\AppBundle\Entity\Player $player
     * @return PlayerRanking
     */
    public function setPlayer(\Sts\Bundle\AppBundle\Entity\Player $player = null)
    {
        $this->player = $player;

        return $this;
    }

    /**
     * Get player
     *
     * @return \Sts\Bundle\AppBundle\Entity\Player
     */
    public function getPlayer()
    {
        return $this->player;
    }
}
