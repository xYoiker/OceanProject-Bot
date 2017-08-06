<?php

use OceanProject\Models\CustomCommand;
use OceanProject\Models\Minrank;

$customcommand = function (int $who, array $message, int $type) {

    $bot = OceanProject\API\ActionAPI::getBot();

    if (!$bot->minrank($who, 'customcommand')) {
        return $bot->network->sendMessageAutoDetection($who, $bot->botlang('not.enough.rank'), $type);
    }

    if (!isset($message[1]) || empty($message[1]) || !in_array($message[1], ['add', 'remove', 'rm', 'list', 'ls'])) {
        return $bot->network->sendMessageAutoDetection(
            $who,
            'Usage: !customcommand [add/remove] [newcommand] [rank] [message]',
            $type
        );
    }
    
    switch ($message[1]) {
        case 'add':
            if (isset($message[2]) && !empty($message[2])) {
                if (isset($message[3]) && !empty($message[3])) {
                    if (isset($message[3]) && !empty($message[4])) {
                        $command = ctype_alnum($message[2][0]) ? $message[2] : substr($message[2], 1);
                        $command = strtolower($command);
                        foreach ($bot->customcommands as $cc) {
                            if ($command == $cc['command']) {
                                return $bot->network->sendMessageAutoDetection(
                                    $who,
                                    'This custom command is already added!',
                                    $type
                                );
                            }
                        }

                        if (isset($bot->minranks[$command])) {
                            return $bot->network->sendMessageAutoDetection(
                                $who,
                                'This is already a command! (d)',
                                $type
                            );
                        }
                        
                        if (!in_array(ucfirst($message[3]), Minrank::pluck('name')->toArray())) {
                            return $bot->network->sendMessageAutoDetection(
                                $who,
                                'The minrank is not valid!',
                                $type
                            );
                        }
                        
                        $minrankID = Minrank::where('name', ucfirst($message[3]))->first();
                        
                        $customCmd = new CustomCommand;
                        $customCmd->bot_id = $bot->data->id;
                        $customCmd->command = $command;
                        $customCmd->response = implode(' ', array_slice($message, 4));
                        $customCmd->minrank_id = (int)$minrankID->id;
                        $customCmd->save();
                        
                        $bot->customcommands = $bot->setCustomCommands();
                        return $bot->network->sendMessageAutoDetection(
                            $who,
                            'The custom command "' . $command . '" has been added!',
                            $type
                        );
                    }
                }
            }
            break;
        case 'remove':
        case 'rm':
            if (isset($message[2]) && !empty($message[2])) {
                $command = ctype_alnum($message[2][0]) ? $message[2] : substr($message[2], 1);
                foreach ($bot->customcommands as $cc) {
                    if (strtolower($command) == $cc['command']) {
                        CustomCommand::where([
                          ['command', '=', $command],
                          ['bot_id', '=', $bot->data->id]
                        ])->delete();
                        
                        $bot->customcommands = $bot->setCustomCommands();
                        return $bot->network->sendMessageAutoDetection(
                            $who,
                            'The custom command "' . $command . '" has been removed!',
                            $type
                        );
                    }
                }
                return $bot->network->sendMessageAutoDetection(
                    $who,
                    'I could not find this custom command in the list.',
                    $type
                );
            }
            break;
        case 'list':
        case 'ls':
            $cmdList = [];
            foreach ($bot->customcommands as $cc) {
                $cmdList[] = $cc['command'];
            }
            return $bot->network->sendMessageAutoDetection(
                $who,
                'Current list : ' . implode(', ', $cmdList) . '.',
                $type
            );
            break;
    }
};
