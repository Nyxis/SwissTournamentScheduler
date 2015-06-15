<?php

namespace Sts\Bundle\AppBundle\Command;

use Sts\Bundle\AppBundle\Entity\Match;
use Sts\Bundle\AppBundle\Entity\Player;
use Sts\Bundle\AppBundle\Entity\Ranking;
use Sts\Bundle\AppBundle\Entity\Round;
use Sts\Bundle\AppBundle\Entity\Tournament;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Simulator command used to simulate a whole tournament
 */
class TournamentSimulatorCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('sts:tournament:simulate')
            ->setDescription('Sts tournament simulation command')
            ->addArgument('name', InputArgument::REQUIRED, 'Tournament name')
            ->addArgument('nb_rounds', InputArgument::REQUIRED, 'Number of rounds')
            ->addArgument('player_names', InputArgument::REQUIRED, 'Player name separe by commas')
         ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $em        = $container->get('doctrine')->getManager();
        $dialog    = $this->getHelperSet()->get('dialog');
        $players   = explode(',', $input->getArgument('player_names'));

        $tournament = new Tournament();
        $tournament->setName(
            $input->getArgument('name')
        );
        $tournament->prepare(
            $input->getArgument('nb_rounds'),
            array_map(function($player) {
                return (new Player)->setName($player);
            }, $players)
        );

        $em->persist($tournament);
        $em->flush();

        $rounds = $tournament->getRounds();

        foreach ($rounds as $round) {
            $this->logRanking(
                $round->getRanking(),
                $tournament,
                $output
            );

            // initialize
            $round->prepare();

            $em->persist($round);
            $em->flush();

            // get unfinished matchs
            $matchs = $round->getMatches()->filter(function(Match $match) {
                return !$match->getFinished();
            });
            $unfinishedMatchs = $matchs;

            while(true) {
                $this->logRound($round, $output);

                $output->writeln('');
                list($playerName, $score) = $dialog->askAndValidate(
                    $output,
                    'Please enter a <info>player score</info> (ex : <comment>name score</comment>) : ',
                    function ($answer) {
                        if (!($answer == 'end_round'
                            || preg_match('/^ *[a-zA-Z0-9]+ *[0-9]? *$/', $answer))
                        ) {
                            throw new \RunTimeException(
                                'Please follow pattern or enter "end_round" to end current round.'
                            );
                        }

                        return array_replace(
                            array(null, null),
                            explode(' ', trim($answer))
                        );
                    }
                );

                if ($playerName == 'end_round' && $unfinishedMatchs->isEmpty()) {
                    break;
                }

                foreach ($matchs as $index => $match) {
                    $scored = 0;
                    foreach ($match->getPlayerMatches() as $playerMatch) {
                        if ($playerMatch->getPlayer()->getName() == $playerName) {
                            $playerMatch->setScore($score);
                        }
                        if (null !==$playerMatch->getScore()) {
                            $scored++;
                        }
                    }
                    if ($scored == $match->getPlayerMatches()->count()) {
                        $match->setFinished(true);
                        unset($unfinishedMatchs[$index]);
                    }
                }
            }

            // close
            $round->close();

            $em->persist($round);
            $em->flush();
        }

        $this->logRanking(
            $tournament->getFinalRanking(),
            $tournament,
            $output
        );

        $em->persist($tournament);
        $em->flush();
    }

    private function fakeRound(Round $round)
    {
        foreach ($round->getMatches() as $match) {
            $playerMatchs = $match->getPlayerMatches();
            foreach ($playerMatchs as $playerResultIndex => $playerMatch) {
                $opponentResultIndex = $playerResultIndex ? 0 : 1;
                $playerMatch->setScore(rand(
                    0,
                    $playerMatchs[$opponentResultIndex]->getScore() == 2 ? 1 : 2
                ));
            }

            $match->setFinished(true);
        }
    }

    private function logRanking(Ranking $ranking, Tournament $tournament, OutputInterface $output)
    {
        $output->writeln('');
        switch (true) {
            case $ranking === $tournament->getInitialRanking() :
                $output->writeln('<info>Initial ranking</info> :');
                break;

            case $ranking === $tournament->getFinalRanking() :
                $output->writeln('<info>Final ranking</info> :');
                break;

            default:
                $output->writeln(sprintf('<info>Ranking</info> after %s rounds :',
                    $ranking->getRound()->getNumber() - 1
                ));
        }

        $log          = array();
        $columnLenght = 0;
        $nbPlayers    = 0;
        $nbRounds     = $tournament->getRounds()->count();

        foreach ($ranking->getPlayerRankings() as $playerRanking) {
            $nbPlayers++;
            $log[] = array(
                $playerRanking->getRank() + 1,
                $playerRanking->getPlayer()->getName(),
                $playerRanking->getScore(),
                $playerRanking->getAverage()
            );
            if (strlen($playerRanking->getPlayer()->getName()) > $columnLenght) {
                $columnLenght = strlen($playerRanking->getPlayer()->getName());
            }
        }
        foreach ($log as $line) {
            list($rank, $name, $score, $average) = $line;
            $rankOff    = strlen($nbPlayers) - strlen($rank);
            $nameOff    = $columnLenght - strlen($name);
            $scoreOff   = strlen($nbRounds*3) - strlen($score);
            $averageOff = 3 - strlen($average);

            $output->writeln(sprintf('| %s%s | %s%s (%s%s%%) | <comment>%s</comment>%s |',
                $rank, $rankOff ? str_repeat(' ', $rankOff) : '',
                $score, $scoreOff ? str_repeat(' ', $scoreOff) : '',
                $average, $averageOff ? str_repeat(' ', $averageOff) : '',
                $name, $nameOff ? str_repeat(' ', $nameOff) : ''
            ));
        }
    }

    private function logRound(Round $round, OutputInterface $output)
    {
        $output->writeln('');
        $output->writeln(sprintf('<info>Round %s</info> :', $round->getNumber()));

        $log          = array();
        $columnLenght = 0;
        $nbRounds     = $round->getTournament()->getRounds()->count();
        foreach ($round->getMatches() as $match) {
            $line = array();
            foreach ($match->getPlayerMatches() as $playerMatch) {
                $line[] = $playerMatch->getPlayer()->getName();
                $line[] = $playerMatch->getScore();
                $line[] = $playerMatch->getPlayerRanking()->getScore();
                $line[] = $playerMatch->getPlayerRanking()->getAverage();
                if (strlen($playerMatch->getPlayer()->getName()) > $columnLenght) {
                    $columnLenght = strlen($playerMatch->getPlayer()->getName());
                }
            }

            $line[] = $match->getFinished();
            $log[]  = $line;
        }

        foreach ($log as $line) {
            list(
                $player, $playerScore, $playerRankScore, $playerRankAvg,
                $opponent, $opponentScore, $opponentRankScore, $opponentRankAvg,
                $finished
            )
                = $line
            ;
            $playerOff          = $columnLenght - strlen($player);
            $playerScoreOff     = strlen($nbRounds*3) - strlen($playerRankScore);
            $playerAverageOff   = 3 - strlen($playerRankAvg);
            $opponentOff        = $columnLenght - strlen($opponent);
            $opponentScoreOff   = strlen($nbRounds*3) - strlen($opponentRankScore);
            $opponentAverageOff = 3 - strlen($opponentRankAvg);

            $output->writeln(sprintf("| %s%s (%s%s%%)  %s<comment>%s</comment> %s - %s <comment>%s</comment>%s  %s%s (%s%s%%) | <fg=%s>%s</fg=%s> |",
                $playerScoreOff ? str_repeat(' ', $playerScoreOff) : '', $playerRankScore,
                $playerRankAvg, $playerAverageOff ? str_repeat(' ', $playerAverageOff) : '',
                $playerOff ? str_repeat(' ', $playerOff) : '', $player,
                $playerScore !== null ? $playerScore : '*', $opponentScore !== null ? $opponentScore : '*',
                $opponent, $opponentOff ? str_repeat(' ', $opponentOff) : '',
                $opponentScoreOff ? str_repeat(' ', $opponentScoreOff) : '', $opponentRankScore,
                $opponentRankAvg, $opponentAverageOff ? str_repeat(' ', $opponentAverageOff) : '',
                $finished ? 'green' : 'red',
                $finished ? '✔' : '✘',
                $finished ? 'green' : 'red'
            ));
        }
    }
}
