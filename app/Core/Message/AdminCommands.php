<?php

namespace App\Core\Message;

use Illuminate\Support\Facades\Log;

class AdminCommands
{
   public array $commandList = ['/addmoder', '/addadmin', '/warn', '/mute', '/kick', '/akick', '/addInfo', '/newm', '/links', '/words', '/questions', '/staff', '/unwarn'];
   public array $commandNotArg = ['addInfo', 'newm', 'links', 'words', 'questions', 'staff'];

   public function checkCommand(string $input): bool
   {
      $pattern = '/\/(\w+)(?:\s+([^\/]+))?/';

      if (preg_match($pattern, $input, $matches)) {
         $command = $matches[1] ?? null;
         if ($command) {
            return in_array('/' . $command, $this->commandList);
         }
      }

      return false;
   }

   public function checkCommandVK(string $input): array
   {
      $result = [];

      if (preg_match('/\/(\w+)(?:\s+\[id(\d+)\|@?([\w.]+)\])?\s*(.+)?/u', $input, $matches)) {
         $result = [
            'command' => $matches[1], // Название команды
            'id' => $matches[2] ?? null, // ID пользователя (если есть)
            'nickname' => $matches[3] ?? null,
            'other' => $matches[4] ?? null,
         ];
      }

      Log::info('checkCommandVK: ' . print_r($result, true));

      return $result;
   }

   public function parseCommandWithArgs(string $input): array
   {
      $result = [];

      if (preg_match('/\/(\w+)\s+(.+)/', $input, $matches)) {
         $result = [
            'command' => $matches[1], // Название команды
            'args' => trim($matches[2]), // Все аргументы после команды
         ];
      } else {
         // Если команда без аргументов
         if (preg_match('/\/(\w+)/', $input, $matches)) {
            $result = [
               'command' => $matches[1],
               'args' => null, // Аргументы отсутствуют
            ];
         }
      }

      return $result;
   }

   public function getCommand(string $input): array
   {
      $pattern = '/\/(\w+)(?:\s+([^\/]+))?/';

       if (preg_match($pattern, $input, $matches)) {
           $command = $matches[1] ?? null;
           $parameters = isset($matches[2]) ? trim($matches[2]) : null;

           return [
               'command' => $command,
               'parameters' => $parameters ? preg_split('/\s+/', $parameters) : [],
               'param' => $parameters
           ];
       }

      return [
         'command' => null,
         'parameters' => null,
      ];
   }
}
