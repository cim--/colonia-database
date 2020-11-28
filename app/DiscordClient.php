<?php

namespace App;

class DiscordClient extends \Discord\DiscordCommandClient {

    protected $commandoptions = [];
    
    public function registerCommand(string $command, $callable, array $options = []): \Discord\CommandClient\Command
    {
        if (array_key_exists($command, $this->commands)) {
            throw new \Exception("A command with the name {$command} already exists.");
        }
        $this->commandoptions[$command] = $options;
        list($commandInstance, $options) = $this->buildCommand($command, $callable, $options);
        $this->commands[$command]        = $commandInstance;
        foreach ($options['aliases'] as $alias) {
            // fix
            $this->registerAlias($alias, $command);
        }
        return $commandInstance;
    }

    public function getCommands() {
        return $this->commands;
    }

    public function getCommandOptions() {
        return $this->commandoptions;
    }

};
