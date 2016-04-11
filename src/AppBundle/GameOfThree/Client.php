<?php
/**
 * Created by PhpStorm.
 * User: vezir
 * Date: 4/10/16
 * Time: 6:32 PM
 */

namespace AppBundle\GameOfThree;


use AppBundle\GameOfThree\Request\MoveRequest;
use AppBundle\GameOfThree\Request\PlayerRequest;
use AppBundle\GameOfThree\Response\GameResponse;
use AppBundle\GameOfThree\Response\MoveResponse;
use AppBundle\GameOfThree\Response\PlayerResponse;
use Circle\RestClientBundle\Exceptions\CurlException;
use Circle\RestClientBundle\Services\RestClient;
use Symfony\Component\HttpFoundation\Response;

class Client
{
    const API_URL = 'http://localhost:8000/';

    const K_MEMBER      = 'hydra:member';
    const K_TITLE       = 'hydra:title';
    const K_DESCRIPTION = 'hydra:description';

    const P_GAMES   = 'games';
    const P_PLAYERS = 'players';
    const P_MOVES   = 'moves';

    const RANGE_MIN = 2;
    const RANGE_MAX = 100;

    /**
     * @var RestClient
     */
    private $restClient;
    private $statusCode = null;
    private $error = null;
    private $errorMessage = null;

    public function __construct(RestClient $restClient)
    {
        $this->restClient = $restClient;
    }

    private function getUrl($endPoint)
    {
        return self::API_URL . $endPoint;
    }

    private function get($endPoint)
    {
        $apiResponse = null;
        try {
            $apiResponse = $this->restClient->get($this->getUrl($endPoint));
        } catch (CurlException $e) {
            $this->error = $e->getCode();
            $this->errorMessage = $e->getMessage();
        }
        return $apiResponse;
    }

    private function post($endPoint, $data)
    {
        $apiResponse = null;
        try {
            $apiResponse = $this->restClient->post($this->getUrl($endPoint), $data);
        } catch (CurlException $e) {
            $this->error = $e->getCode();
            $this->errorMessage = $e->getMessage();
        }
        return $apiResponse;
    }

    private function getAcceptedStatuses()
    {
        return array(
            Response::HTTP_OK,
            Response::HTTP_CREATED
        );
    }

    private function call(\Closure $closure)
    {
        $result = null;
        /* @var $apiResponse Response */
        if ($apiResponse = call_user_func($closure)) {
            $this->statusCode = $apiResponse->getStatusCode();
            if (in_array($this->statusCode, $this->getAcceptedStatuses())) {
                $jsonObj = json_decode($apiResponse->getContent(), true);
                if (isset($jsonObj[self::K_MEMBER])) {
                    $result = $jsonObj[self::K_MEMBER];
                } else {
                    $result = $jsonObj;
                }
            } else {
                // Trying to get error info:
                $jsonObj = json_decode($apiResponse->getContent(), true);
                if (isset($jsonObj[self::K_TITLE])) {
                    $this->error = $jsonObj[self::K_TITLE];
                }
                if (isset($jsonObj[self::K_DESCRIPTION])) {
                    $this->errorMessage = $jsonObj[self::K_DESCRIPTION];
                }
            }
        }
        return $result;
    }

    public function getGame($id)
    {
        $game = $this->call(function () use ($id) {
            return $this->get(trim($id, '/'));
        });
        if ($game) {
            $game = new GameResponse($game);
        }
        return $game;
    }

    /**
     * @return GameResponse[]
     */
    public function getGames()
    {
        $response = $this->call(function () {
           return $this->get(self::P_GAMES);
        });
        if (!is_array($response)) {
            return $response;
        }
        $games = array();
        foreach ($response as $game) {
            $games[] = new GameResponse($game);
        }
        return $games;
    }

    /**
     * @return GameResponse|null
     */
    public function createGame()
    {
        $game = $this->call(function () {
            return $this->post(self::P_GAMES, '{}');
        });
        if ($game) {
            $game = new GameResponse($game);
        }
        return $game;
    }

    /**
     * @param $id
     * @return PlayerResponse|null
     */
    public function getPlayer($id)
    {
        $player = $this->call(function () use ($id) {
            return $this->get(trim($id, '/'));
        });
        if ($player) {
            $player = new PlayerResponse($player);
        }
        return $player;
    }

    /**
     * @param string $game
     * @param string $name
     * @param string $control
     * @return PlayerResponse|null
     */
    public function createPlayer($game, $name, $control)
    {
        $dataString = json_encode(new PlayerRequest($game, $name, $control));
        $player = $this->call(function () use ($dataString) {
            return $this->post(self::P_PLAYERS, $dataString);
        });
        if ($player) {
            $player = new PlayerResponse($player);
        }
        return $player;
    }

    /**
     * @param $id
     * @return MoveResponse|null
     */
    public function getMove($id)
    {
        $move = $this->call(function () use ($id) {
            return $this->get(trim($id, '/'));
        });
        if ($move) {
            $move = new MoveResponse($move);
        }
        return $move;
    }

    /**
     * @param $player
     * @param $number
     * @param $step
     * @return MoveResponse|null
     */
    public function createMove($player, $number, $step = null)
    {
        if ($step === null) {
            switch ($number % 3) {
                case 1:
                    $step = -1;
                    break;
                case 2:
                    $step = 1;
                    break;
                case 0;
                    $step = 0;
                    break;
            }
        }
        $dataString = json_encode(new MoveRequest($player, $step, $number));
        $move = $this->call(function () use ($dataString) {
            return $this->post(self::P_MOVES, $dataString);
        });
        if ($move) {
            $move = new MoveResponse($move);
        }
        return $move;
    }

    /**
     * @return null
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @return null
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @return null
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

}