<?php
/**
 * Created by PhpStorm.
 * User: vezir
 * Date: 4/10/16
 * Time: 9:02 PM
 */

namespace AppBundle\GameOfThree\Request;


class PlayerRequest
{
    public $game    = null;
    public $name    = null;
    public $control = null;

    public function __construct($game, $name, $control)
    {
        $this->game    = $game;
        $this->name    = $name;
        $this->control = $control;
    }

}