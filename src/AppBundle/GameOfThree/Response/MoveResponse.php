<?php
/**
 * Created by PhpStorm.
 * User: vezir
 * Date: 4/11/16
 * Time: 8:54 AM
 */

namespace AppBundle\GameOfThree\Response;


class MoveResponse
{
    public $context          = null;
    public $id               = null;
    public $type             = null;
    public $number           = null;
    public $step             = null;
    public $player           = null;
    public $calculatedNumber = null;
    public $nextNumber       = null;

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
        if (array_key_exists('number', $responseArray)) {
            $this->number = $responseArray['number'];
        }
        if (array_key_exists('step', $responseArray)) {
            $this->step = $responseArray['step'];
        }
        if (array_key_exists('player', $responseArray)) {
            $this->player = $responseArray['player'];
        }
        if (array_key_exists('calculatedNumber', $responseArray)) {
            $this->calculatedNumber = $responseArray['calculatedNumber'];
        }
        if (array_key_exists('nextNumber', $responseArray)) {
            $this->nextNumber = $responseArray['nextNumber'];
        }
    }
}