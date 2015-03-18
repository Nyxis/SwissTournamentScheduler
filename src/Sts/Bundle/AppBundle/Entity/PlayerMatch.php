<?php

namespace Sts\Bundle\AppBundle\Entity;

/**
 * Relation between match an players
 */
class PlayerMatch
{
    protected $id;
    protected $player;
    protected $match;
    protected $playerRanking;
    protected $score;
    protected $wins = false;
    protected $draw = false;

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
     * Set score
     *
     * @param integer $score
     * @return PlayerMatch
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
     * Set wins
     *
     * @param boolean $wins
     * @return PlayerMatch
     */
    public function setWins($wins)
    {
        $this->wins = !empty($wins);

        return $this;
    }

    /**
     * Get wins
     *
     * @return boolean
     */
    public function getWins()
    {
        return $this->wins;
    }

    /**
     * Set draw
     *
     * @param boolean $draw
     * @return PlayerMatch
     */
    public function setDraw($draw)
    {
        $this->draw = !empty($draw);

        return $this;
    }

    /**
     * Get draw
     *
     * @return boolean
     */
    public function getDraw()
    {
        return $this->draw;
    }

    /**
     * Set match
     *
     * @param \Sts\Bundle\AppBundle\Entity\Match $match
     * @return PlayerMatch
     */
    public function setMatch(\Sts\Bundle\AppBundle\Entity\Match $match = null)
    {
        $this->match = $match;

        return $this;
    }

    /**
     * Get match
     *
     * @return \Sts\Bundle\AppBundle\Entity\Match
     */
    public function getMatch()
    {
        return $this->match;
    }

    /**
     * Set player
     *
     * @param \Sts\Bundle\AppBundle\Entity\Player $player
     * @return PlayerMatch
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

    /**
     * Set playerRanking
     *
     * @param \Sts\Bundle\AppBundle\Entity\PlayerRanking $playerRanking
     * @return PlayerMatch
     */
    public function setPlayerRanking(\Sts\Bundle\AppBundle\Entity\PlayerRanking $playerRanking = null)
    {
        $this->playerRanking = $playerRanking;

        return $this;
    }

    /**
     * Get playerRanking
     *
     * @return \Sts\Bundle\AppBundle\Entity\PlayerRanking
     */
    public function getPlayerRanking()
    {
        return $this->playerRanking;
    }
}
