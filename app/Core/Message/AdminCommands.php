<?php

namespace App\Core\Message;

use Illuminate\Support\Facades\Log;

class AdminCommands
{
   protected array $commandList = ['/addmoder', '/addadmin', '/warn', '/mute', '/kick', '/akick', '/addInfo', '/newm', '/links', '/words', '/questions'];

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

   public function getCommand(string $input): array
   {
      $pattern = '/\/(\w+)(?:\s+([^\/]+))?/';

      if (preg_match($pattern, $input, $matches)) {
         $command = $matches[1] ?? null;
         $parameters = isset($matches[2]) ? trim($matches[2]) : null;

         return [
            'command' => $command,
            'parameters' => explode(' ', $parameters),
         ];
      }

      return [
         'command' => null,
         'parameters' => null,
      ];
   }
}
