<?php

namespace Sts\Bundle\AppBundle\Entity;

use Sts\Bundle\AppBundle\Entity\Player;
use Sts\Bundle\AppBundle\Entity\Ranking;
use Sts\Bundle\AppBundle\Entity\PlayerRanking;
use Sts\Bundle\AppBundle\Entity\Round;

/**
 * Tournament class
 */
class Tournament
{
    protected $id;
    protected $name;
    protected $initialRanking;
    protected $currentRound;
    protected $rounds;
    protected $finalRanking;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->rounds = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set currentRound
     *
     * @param Sts\Bundle\AppBundle\Entity\Round $currentRound
     * @return Tournament
     */
    public function setCurrentRound(\Sts\Bundle\AppBundle\Entity\Round $currentRound)
    {
        $this->currentRound = $currentRound;

        return $this;
    }

    /**
     * Get currentRound
     *
     * @return Sts\Bundle\AppBundle\Entity\Round
     */
    public function getCurrentRound()
    {
        return $this->currentRound;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Tournament
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
     * Set initialRanking
     *
     * @param \Sts\Bundle\AppBundle\Entity\Ranking $initialRanking
     * @return Tournament
     */
    public function setInitialRanking(\Sts\Bundle\AppBundle\Entity\Ranking $initialRanking = null)
    {
        $this->initialRanking = $initialRanking;

        return $this;
    }

    /**
     * Get initialRanking
     *
     * @return \Sts\Bundle\AppBundle\Entity\Ranking
     */
    public function getInitialRanking()
    {
        return $this->initialRanking;
    }

    /**
     * Set finalRanking
     *
     * @param \Sts\Bundle\AppBundle\Entity\Ranking $finalRanking
     * @return Tournament
     */
    public function setFinalRanking(\Sts\Bundle\AppBundle\Entity\Ranking $finalRanking = null)
    {
        $this->finalRanking = $finalRanking;

        return $this;
    }

    /**
     * Get finalRanking
     *
     * @return \Sts\Bundle\AppBundle\Entity\Ranking
     */
    public function getFinalRanking()
    {
        return $this->finalRanking;
    }

    /**
     * Get rounds
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRounds()
    {
        return $this->rounds;
    }

    /**
     * Add rounds
     *
     * @param \Sts\Bundle\AppBundle\Entity\Round $rounds
     * @return Tournament
     */
    public function addRound(\Sts\Bundle\AppBundle\Entity\Round $rounds)
    {
        $this->rounds[] = $rounds;

        return $this;
    }

    /**
     * Remove rounds
     *
     * @param \Sts\Bundle\AppBundle\Entity\Round $rounds
     */
    public function removeRound(\Sts\Bundle\AppBundle\Entity\Round $rounds)
    {
        $this->rounds->removeElement($rounds);
    }

    /**
     * prepare this tournament
     *
     * @param  int    $nbRounds
     * @param  array  $players
     */
    public function prepare($nbRounds, array $players)
    {
        shuffle($players); // first ranking : random, no elo ranking

        // adds *** BYE ***
        if (count($players)%2) {
            array_push($players, (new Player)->setName(Player::BYE_NAME));
        }

        // initial ranking
        $initialRanking = new Ranking();
        foreach ($players as $index => $player) {
            $playerRanking = new PlayerRanking();
            $playerRanking->setPlayer($player);
            $playerRanking->setRank($index);
            $playerRanking->setScore(0);
            $playerRanking->setAverage(0);
            $playerRanking->setRanking($initialRanking);

            $initialRanking->addPlayerRanking($playerRanking);
        }

        $this->setInitialRanking($initialRanking);

        // create rounds
        for ($i = 0; $i < $nbRounds; $i++) {
            $round = new Round();
            $round->setNumber($i+1);
            $round->setTournament($this);

            if (!$i) {
                $round->setRanking($initialRanking);
                $initialRanking->setRound($round);
            }

            $this->rounds->set($i+1, $round);
        }
    }
}
