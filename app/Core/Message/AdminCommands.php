<?php

namespace App\Core\Message;


class AdminCommands
{
   public array $commandList = ['/addmoder', '/addadmin', '/warn', '/mute', '/kick', '/akick', '/addInfo', '/newm', '/links', '/words', '/questions', '/staff', '/unwarn', '/unban', '/unmute', '/delstaff'];
   public array $commandNotArg = ['addInfo', 'newm', 'links', 'words', 'questions', 'staff'];
   public array $adminsList = ['614242745', '217199523', '582127671'];

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


      if (preg_match('/^\/(\w+)(?:\s+\[id(\d+)\|@?([\w.]+)\])?\s*(.+)?/us', $input, $matches)) {
         $result = [
            'command' => $matches[1],
            'id' => $matches[2] ?? null,
            'nickname' => $matches[3] ?? null,
            'other' => $matches[4] ?? null,
         ];
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
