<?php

namespace App\Commands;

use Illuminate\Database\Eloquent\Model;

class CommandHandler
{
    public $message;
    public $args;
    public $discord;

    public function __toString()
    {
        return $this->name;
    }

    public function help()
    {
        $helpMessage = $this->name."\n".$this->description."\n".$this->usage;
        return $helpMessage;
    }
}
