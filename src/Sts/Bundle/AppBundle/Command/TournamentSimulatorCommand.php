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
         ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $em        = $container->get('doctrine')->getManager();
        $dialog    = $this->getHelperSet()->get('dialog');

        $tournament = new Tournament();
        $tournament->setName(
            $input->getArgument('name')
        );
        $tournament->prepare(
            $input->getArgument('nb_rounds'),
            array(
                (new Player)->setName('elspeth'),
                (new Player)->setName('ajani'),
                (new Player)->setName('kiora'),
                (new Player)->setName('ugin'),
                (new Player)->setName('chandra'),
                (new Player)->setName('jace'),
                (new Player)->setName('liliana'),
            )
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
            $this->logRound($round, $output);

            $em->persist($round);
            $em->flush();

            // $output->writeln('');
            // $dialog->askConfirmation(
            //     $output,
            //     sprintf('<question>Play round %s ?</question>', $round->getNumber()),
            //     false
            // );
            // $output->writeln('');


            // fake matches
            $this->fakeRound($round);
            $this->logRound($round, $output);

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
                if ($playerMatch->getPlayer()->getName() == Player::BYE_NAME) {
                    $playerMatch->setScore(0);
                    $playerMatchs[$opponentResultIndex]->setScore(2);
                    break;
                }

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

        $output->writeln('');
    }

    private function logRound(Round $round, OutputInterface $output)
    {
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
                $playerScore ?: '0', $opponentScore ?: '0',
                $opponent, $opponentOff ? str_repeat(' ', $opponentOff) : '',
                $opponentScoreOff ? str_repeat(' ', $opponentScoreOff) : '', $opponentRankScore,
                $opponentRankAvg, $opponentAverageOff ? str_repeat(' ', $opponentAverageOff) : '',
                $finished ? 'green' : 'red',
                $finished ? '✔' : '✘',
                $finished ? 'green' : 'red'
            ));
        }

        $output->writeln('');
    }
}
