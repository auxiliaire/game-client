<?php

namespace AppBundle\Command;

use AppBundle\GameOfThree\Client;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

class PlayCommand extends ContainerAwareCommand
{
    /**
     * @var Client
     */
    private $apiClient;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('app:play_command')
            ->addOption('auto', 'a', InputOption::VALUE_NONE, "Auto playing (no interaction)")
            ->setDescription('Game of Three client (a server should run to play)');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Defining styles:
        $io = new SymfonyStyle($input, $output);
        $style = new OutputFormatterStyle('black', 'green');
        $output->getFormatter()->setStyle('g', $style);
        $cellStyle = new OutputFormatterStyle('black', 'white');
        $output->getFormatter()->setStyle('c', $cellStyle);
        $errorStyle = new OutputFormatterStyle('white', 'red');
        $output->getFormatter()->setStyle('e', $errorStyle);
        $questionStyle = new OutputFormatterStyle(NULL, NULL, array('bold'));
        $output->getFormatter()->setStyle('q', $questionStyle);

        $output->writeln("<q>Welcome to Viktor Daróczi's Game of Three client!</q>");

        $this->apiClient = $this->getContainer()->get('app.game_of_three.client');
        $games = $this->apiClient->getGames();

        if ($games !== null) {
            $output->writeln("Games present on the server:");

            $availableGame = null;
            $gameArray = array();
            foreach ($games as $game) {
                $gameArray[] = array($game->id, (int)$game->available, (int)$game->over);
                if ($game->available) {
                    $availableGame = $game;
                }
            }
            $io->table(array('Id', 'available', 'over'), $gameArray);

            $auto = $input->getOption('auto');
            if (!$auto) {
                $helper = $this->getHelper('question');
                $question = new ChoiceQuestion('<q>Choose action:</q> ', array('j' => 'join', 'c' => 'create'));
                $action = $helper->ask($input, $output, $question);
                switch ($action) {
                    /** @noinspection PhpMissingBreakStatementInspection */
                    case 'c':
                        $availableGame = $this->createGame($output);
                    case 'j':
                        if (!$availableGame) {
                            $availableGame = $this->createGame($output, "<e>No game available!</e> Creating one...");
                        }
                        $name = $io->ask("What's your name?", null, function ($name) {
                            if (!ctype_alpha($name)) {
                                throw new \RuntimeException("Only alpha input allowed.");
                            }
                            return $name;
                        });
                        $question = new ChoiceQuestion('<q>Choose control (auto):</q>', array('auto' => 'auto', 'manual' => 'manual'), 'auto');
                        $control = $helper->ask($input, $output, $question);
                        $player = $this->createPlayer($availableGame, $name, $control, $output);

                        $this->main($availableGame, $player, $output, $io);

                        break;
                }
            } else {
                if (!$availableGame) {
                    $availableGame = $this->createGame($output);
                }
                $playerNum = (count($availableGame->players)? 'Two': 'One');
                $player = $this->createPlayer($availableGame, "Player$playerNum", 'auto', $output);

                $this->main($availableGame, $player, $output, $io);
            }

        } else {
            // Error
        }
    }

    private function main($availableGame, $player, OutputInterface $output, SymfonyStyle $io)
    {
        if (count($availableGame->players) < 2) {
            $output->write("Waiting for other players to join (Ctrl-C to terminate)...");
            $availableGame = $this->apiClient->getGame($availableGame->id);
            while (count($availableGame->players) < 2) {
                sleep(1);
                $availableGame = $this->apiClient->getGame($availableGame->id);
            }
        }
        $output->writeln("Done!");
        $player = $this->apiClient->getPlayer($player->id);
        $opponent = $this->apiClient->getPlayer($player->opponent);
        $opponentMoveCount = count($opponent->moves);
        if ($player->canStart) {
            $number = rand(2, 100);
            $this->apiClient->createMove($player->id, $number);
            $output->writeln("Initial move with number " . $number);
            $opponentStarts = false;
        } else {
            $opponentStarts = true;
        }
        while (!$availableGame->over) {
            $number = $availableGame->currentNumber;
            if ($player->hasTurn) {
                $opponentCurrentMoveCount = count($opponent->moves);
                if ($opponentCurrentMoveCount > $opponentMoveCount) {
                    $opponentMoveCount = $opponentCurrentMoveCount;
                    $moveId = $opponent->moves[$opponentMoveCount - 1];
                    $om = $this->apiClient->getMove($moveId);
                    if ($opponentStarts) {
                        $output->writeln("<q>Opponent's initial move: \t{$om->number}</q>");
                        $opponentStarts = false;
                    } else {
                        $output->writeln("<q>Opponent's move: \t{$om->number} + {$om->step} = {$om->calculatedNumber}\t=>\t{$om->nextNumber}</q>");
                    }
                }
                $step = null;
                if ($player->control == 'manual') {
                    while (is_null($step) || ($step === 'e')) {
                        $step = $io->ask("Where do you move?", null, function ($step) use ($number, $output) {
                            if (!in_array($step, array(-1, 0, 1)) || (($number + $step) % 3 != 0)) {
                                $output->writeln("<e>Invalid step! Choose a number between -1 and 1 that adding to the number will result a sum that is dividable by three without modulus.</e>");
                                $step = 'e';
                            }
                            return $step;
                        });
                    }
                }
                $move = $this->apiClient->createMove($player->id, $number, (int)$step);
                $this->assertMoveExists($move, $output);
                $output->writeln("Player's move: \t\t{$move->number} + {$move->step} = {$move->calculatedNumber}\t=>\t{$move->nextNumber}");
            }
            // Updating entities:
            $player = $this->apiClient->getPlayer($player->id);
            $opponent = $this->apiClient->getPlayer($opponent->id);
            $availableGame = $this->apiClient->getGame($availableGame->id);
            sleep(1);
        }
        if ($player->isWinner) {
            $output->writeln("<g>YOU WON!</g>");
        } else {
            $output->writeln("<q>You lost.</q>");
        }
        $output->writeln("Game over!");
    }

    private function createGame(OutputInterface $output, $message = "Creating game...")
    {
        $output->write($message);
        $availableGame = $this->apiClient->createGame();
        $this->assertGameExists($availableGame, $output);
        $output->writeln("Done!");
        return $availableGame;
    }

    /**
     * @param $availableGame
     * @param $name
     * @param $control
     * @param OutputInterface $output
     * @return \AppBundle\GameOfThree\Response\PlayerResponse|null
     */
    private function createPlayer($availableGame, $name, $control, OutputInterface $output)
    {
        $output->write("Creating player...");
        $player = $this->apiClient->createPlayer($availableGame->id, $name, $control);
        $this->assertPlayerExists($player, $output);
        $output->writeln("Done!");
        return $player;
    }

    private function assertGameExists($availableGame, OutputInterface $output)
    {
        if (!$availableGame) {
            $output->writeln(PHP_EOL . "<e>FATAL! Cannot create game.</e>");
            $output->writeln(var_export($availableGame, true));
            $this->dumpError($output);
            exit;
        }
    }

    private function assertPlayerExists($player, OutputInterface $output)
    {
        if (!$player) {
            $output->writeln(PHP_EOL . "<e>FATAL! Cannot create player.</e>");
            $output->writeln(var_export($player, true));
            $this->dumpError($output);
            exit;
        }
    }

    private function assertMoveExists($move, OutputInterface $output)
    {
        if (!$move) {
            $output->writeln(PHP_EOL . "<e>FATAL! Cannot create move.</e>");
            $output->writeln(var_export($move, true));
            $this->dumpError($output);
            exit;
        }
    }

    private function dumpError(OutputInterface $output)
    {
        $output->writeln('<e>' . $this->apiClient->getError() . ': ' . $this->apiClient->getErrorMessage() . '</e>');
    }
}
