<?php
/**
 * Created by PhpStorm.
 * User: vezir
 * Date: 4/10/16
 * Time: 8:51 PM
 */

namespace AppBundle\GameOfThree\Response;


class GameResponse
{
    public $id            = null;
    public $type          = null;
    public $available     = null;
    public $currentNumber = null;
    public $over          = null;
    public $players       = null;

    public function __construct(array $responseArray)
    {
        if (array_key_exists('@id', $responseArray)) {
            $this->id = $responseArray['@id'];
        }
        if (array_key_exists('@type', $responseArray)) {
            $this->type = $responseArray['@type'];
        }
        if (array_key_exists('available', $responseArray)) {
            $this->available = $responseArray['available'];
        }
        if (array_key_exists('currentNumber', $responseArray)) {
            $this->currentNumber = $responseArray['currentNumber'];
        }
        if (array_key_exists('over', $responseArray)) {
            $this->over = $responseArray['over'];
        }
        if (array_key_exists('players', $responseArray)) {
            $this->players = $responseArray['players'];
        }
    }
}