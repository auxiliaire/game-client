<?php
/**
 * Created by PhpStorm.
 * User: vezir
 * Date: 4/10/16
 * Time: 9:21 PM
 */

namespace AppBundle\GameOfThree\Response;


class PlayerResponse
{
    public $context  = null;
    public $id       = null;
    public $type     = null;
    public $name     = null;
    public $control  = null;
    public $canJoin  = null;
    public $canStart = null;
    public $hasTurn  = null;
    public $isWinner = null;
    public $webhook  = null;
    public $game     = null;
    public $moves    = null;
    public $opponent = null;

    public function __construct(array $responseArray)
    {
        if (array_key_exists('@context', $responseArray)) {
            $this->context = $responseArray['@context'];
        }
        if (array_key_exists('@id', $responseArray)) {
            $this->id = $responseArray['@id'];
        }
        if (array_key_exists('@type', $responseArray)) {
            $this->type = $responseArray['@type'];
        }
        if (array_key_exists('name', $responseArray)) {
            $this->name = $responseArray['name'];
        }
        if (array_key_exists('control', $responseArray)) {
            $this->control = $responseArray['control'];
        }
        if (array_key_exists('canJoin', $responseArray)) {
            $this->canJoin = $responseArray['canJoin'];
        }
        if (array_key_exists('canStart', $responseArray)) {
            $this->canStart = $responseArray['canStart'];
        }
        if (array_key_exists('hasTurn', $responseArray)) {
            $this->hasTurn = $responseArray['hasTurn'];
        }
        if (array_key_exists('isWinner', $responseArray)) {
            $this->isWinner = $responseArray['isWinner'];
        }
        if (array_key_exists('webhook', $responseArray)) {
            $this->webhook = $responseArray['webhook'];
        }
        if (array_key_exists('game', $responseArray)) {
            $this->game = $responseArray['game'];
        }
        if (array_key_exists('moves', $responseArray)) {
            $this->moves = $responseArray['moves'];
        }
        if (array_key_exists('opponent', $responseArray)) {
            $this->opponent = $responseArray['opponent']['@id'];
        }
    }
}