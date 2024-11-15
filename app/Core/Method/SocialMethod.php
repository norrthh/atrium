<?php

namespace App\Core\Method;

interface SocialMethod
{
   public function sendMessage(int $userId, string $message);
   public function sendWallMessage($filePath, $message);
   public function uploadPhoto(string $imagePath);
   public function closeWallComments(int $postId);
   public function replyWallComment(int $postId, string $message, int $commentId, $image = null);
   public function checkSubscriptionGroup(int $userId);
   public function checkSubscriptionMailing(int $userId);
}
