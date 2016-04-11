<?php
/**
 * Created by PhpStorm.
 * User: vezir
 * Date: 4/11/16
 * Time: 8:32 AM
 */

namespace AppBundle\GameOfThree\Request;


class MoveRequest
{
    public $number = null;
    public $step   = null;
    public $player = null;

    public function __construct($player, $step, $number)
    {
        $this->player = $player;
        $this->step   = $step;
        $this->number = $number;
    }
}