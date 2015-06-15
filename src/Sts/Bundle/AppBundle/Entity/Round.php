<?php

namespace Sts\Bundle\AppBundle\Entity;

use Sts\Bundle\AppBundle\Entity\Match;
use Sts\Bundle\AppBundle\Entity\Player;
use Sts\Bundle\AppBundle\Entity\PlayerMatch;
use Sts\Bundle\AppBundle\Entity\PlayerRanking;

/**
 * Round class
 */
class Round
{
    protected $id;
    protected $number;
    protected $tournament;
    protected $matches;
    protected $ranking;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->matches = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set number
     *
     * @param integer $number
     * @return Round
     */
    public function setNumber($number)
    {
        $this->number = $number;

        return $this;
    }

    /**
     * Get number
     *
     * @return integer
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * Set ranking
     *
     * @param \Sts\Bundle\AppBundle\Entity\Ranking $ranking
     * @return Round
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
     * Add matches
     *
     * @param \Sts\Bundle\AppBundle\Entity\Match $matches
     * @return Round
     */
    public function addMatch(\Sts\Bundle\AppBundle\Entity\Match $matches)
    {
        $this->matches[] = $matches;

        return $this;
    }

    /**
     * Remove matches
     *
     * @param \Sts\Bundle\AppBundle\Entity\Match $matches
     */
    public function removeMatch(\Sts\Bundle\AppBundle\Entity\Match $matches)
    {
        $this->matches->removeElement($matches);
    }

    /**
     * Get matches
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMatches()
    {
        return $this->matches;
    }

    /**
     * Set tournament
     *
     * @param \Sts\Bundle\AppBundle\Entity\Tournament $tournament
     * @return Round
     */
    public function setTournament(\Sts\Bundle\AppBundle\Entity\Tournament $tournament = null)
    {
        $this->tournament = $tournament;

        return $this;
    }

    /**
     * Get tournament
     *
     * @return \Sts\Bundle\AppBundle\Entity\Tournament
     */
    public function getTournament()
    {
        return $this->tournament;
    }

    /**
     * prepare round
     */
    public function prepare()
    {
        $ranking = $this->getRanking();
        $ranking->setRound($this);
        $this->getTournament()->setCurrentRound($this);

        $players = array();
        foreach ($ranking->getPlayerRankings() as $playerRanking) {
            $players[$playerRanking->getPlayer()->getName()] = $playerRanking;
        }

        // each possible player / opponent combination is mesured
        $nbPlayers          = count($players);
        $playerRelevanceMap = array();
        foreach ($players as $playerName => $playerRanking) {
            $playerRelevanceMap[$playerName] = array();

            // player previous opponents list
            $opponents = array();
            foreach ($playerRanking->getPlayer()->getPlayerMatches() as $playerMatch) {
                foreach ($playerMatch->getMatch()->getPlayerMatches() as $vsMastchPlayer) {
                    if ($playerMatch == $vsMastchPlayer) {
                        continue;
                    }

                    $opponents[] = $vsMastchPlayer->getPlayer()->getName();
                }
            }

            $relevanceMap = array();
            foreach ($players as $vsPlayerName => $vsPlayerRanking) {
                if ($vsPlayerName == $playerName) {
                    continue;
                }
                if (in_array($vsPlayerName, $opponents)) {
                    continue; // already played against ? no way !
                }

                // distance from ranking
                $relevanceMap[$vsPlayerName] = $nbPlayers - abs($vsPlayerRanking->getRank() - $playerRanking->getRank());
            }

            $playerRelevanceMap[$playerName] = $relevanceMap;
        }

        // run appairement function of relevance

        // sort relevance map
        foreach ($playerRelevanceMap as $playerName => $relevanceData) {
            uksort($relevanceData, function($playerName1, $playerName2) use ($relevanceData) {
                return $relevanceData[$playerName1] > $relevanceData[$playerName2] ? -1 : 1;
            });
            $playerRelevanceMap[$playerName] = $relevanceData;
        }

        $pairs            = array();
        $availablePlayers = array_keys($playerRelevanceMap);
        $availablePlayers = array_combine($availablePlayers, $availablePlayers);

        do {
            $playerName = array_shift($availablePlayers);

            foreach ($playerRelevanceMap[$playerName] as $possibleOpponent => $relevance) {
                if (isset($availablePlayers[$possibleOpponent])) {
                    $opponentName       = $possibleOpponent;
                    $pairs[$playerName] = $opponentName;
                    unset($availablePlayers[$opponentName]);

                    break;
                }
            }

            if (empty($opponentName)) {
                throw new \RuntimeException('Calculation limit reached !');
            }
            unset($opponentName);

        } while (!empty($availablePlayers));

        foreach ($pairs as $playerName => $opponentName) {

            $match = new Match();

            $playerIsBye   = $playerName == Player::BYE_NAME;
            $opponentIsBye = $opponentName == Player::BYE_NAME;
            $isBye         = $playerIsBye || $opponentIsBye;

            $playerMatch = new PlayerMatch();
            $playerMatch->setMatch($match);
            $playerMatch->setPlayerRanking($players[$playerName]);
            $playerMatch->setPlayer($players[$playerName]->getPlayer());
            $match->addPlayerMatch($playerMatch);
            $players[$playerName]->getPlayer()->addPlayerMatch($playerMatch);
            if ($isBye) {
                $playerMatch->setScore($playerIsBye ? 0 : 2);
                $playerMatch->setWins(!$playerIsBye);
            }

            $playerMatch = new PlayerMatch();
            $playerMatch->setMatch($match);
            $playerMatch->setPlayerRanking($players[$opponentName]);
            $playerMatch->setPlayer($players[$opponentName]->getPlayer());
            $match->addPlayerMatch($playerMatch);
            $players[$opponentName]->getPlayer()->addPlayerMatch($playerMatch);
            if ($isBye) {
                $playerMatch->setScore($opponentIsBye ? 0 : 2);
                $playerMatch->setWins(!$opponentIsBye);
            }

            $match->setFinished($isBye);

            $match->setRound($this);
            $this->addMatch($match);
        }
    }

    /**
     * close this round, generate ranking, prepare next one
     */
    public function close()
    {
        $endRoundRanking  = new Ranking();
        $playerRankings   = array();

        foreach ($this->getMatches() as $match) {
            $isDraw   = true;
            $maxScore = 0;
            $winner   = null;

            // calculate winner
            foreach ($match->getPlayerMatches() as $playerMatch) {
                if ($playerMatch->getScore() > $maxScore) {
                    $winner   = $playerMatch->getPlayer()->getName();
                    $maxScore = $playerMatch->getScore();
                    $isDraw   = false;
                    continue;
                }
                if ($playerMatch->getScore() == $maxScore) {
                    $winner = null;
                    $isDraw = true;
                }
            }

            // create rankings
            foreach ($match->getPlayerMatches() as $playerMatch) {
                $playerMatch->setWins($winner == $playerMatch->getPlayer()->getName());
                $playerMatch->setDraw($isDraw);

                $playerRanking = new PlayerRanking();
                $playerRanking->setPlayer($playerMatch->getPlayer());
                $playerRanking->setRanking($endRoundRanking);
                $playerRanking->setScore(
                    $playerMatch->getPlayerRanking()->getScore() // previous score
                    + ($playerMatch->getWins() ? 3 : ($playerMatch->getDraw() ? 1 : 0))
                );

                $playerRankings[] = $playerRanking;
            }
        }

        // calculate averages
        $players = array_map(
            function(PlayerRanking $playerRanking) { return $playerRanking->getPlayer(); },
            $playerRankings
        );

        $roundWonByPlayer = array();
        foreach ($players as $player) {
            $roundWonByPlayer[$player->getName()] = 0;
            foreach ($player->getPlayerMatches() as $playerMatch) {
                if ($playerMatch->getWins()) {
                    $roundWonByPlayer[$player->getName()]++;
                }
            }
        }

        foreach ($playerRankings as $playerRanking) {
            $opponentsWonRounds = 0;
            $possibleWins       = 0;

            // every opponents from every played matches
            foreach ($playerRanking->getPlayer()->getPlayerMatches() as $playerMatch) {
                foreach ($playerMatch->getMatch()->getPlayerMatches() as $vsPlayerMatch) {
                    if ($vsPlayerMatch == $playerMatch) {
                        continue;
                    }
                    $opponentsWonRounds += $roundWonByPlayer[$vsPlayerMatch->getPlayer()->getName()];
                    $possibleWins       += $this->getNumber();
                }
            }
            $playerRanking->setAverage(
                (int) round($opponentsWonRounds / $possibleWins * 100)
            );
        }

        // sort rankings
        usort($playerRankings, function(PlayerRanking $playerRanking1, PlayerRanking $playerRanking2) {

            // BYE ?
            if ($playerRanking1->getPlayer()->getName() == Player::BYE_NAME)  {
                return 1;
            }
            if ($playerRanking2->getPlayer()->getName() == Player::BYE_NAME)  {
                return -1;
            }

            // Average
            if ($playerRanking1->getScore() == $playerRanking2->getScore()) {
                if ($playerRanking1->getAverage() == $playerRanking2->getAverage()) {
                    return 0;
                }

                return $playerRanking1->getAverage() > $playerRanking2->getAverage() ?
                    -1 : 1
                ;
            }

            return $playerRanking1->getScore() > $playerRanking2->getScore() ?
                -1 : 1
            ;
        });

        foreach ($playerRankings as $index => $playerRanking) {
            $playerRanking->setRank($index);
            $endRoundRanking->getPlayerRankings()->set($index, $playerRanking);
        }

        $nextRound = $this->getTournament()->getRounds()
            ->get($this->getNumber() + 1)
        ;
        if ($nextRound) {
            $endRoundRanking->setRound($nextRound);
            $nextRound->setRanking($endRoundRanking);
        }
        else {
            $this->getTournament()->setFinalRanking($endRoundRanking);
        }
    }
}
