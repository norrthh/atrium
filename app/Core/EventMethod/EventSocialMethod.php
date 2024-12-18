<?php

namespace App\Core\EventMethod;

interface EventSocialMethod
{
   public function sendMessage(int $userId, string $message);
   public function sendWallMessage($filePath, $message);
   public function uploadPhoto(string $imagePath);
   public function closeWallComments(int $postId, int $user_id = null);
   public function replyWallComment(int $postId, string $message, int $commentId, $image = null);
   public function checkSubscriptionGroup(int $userId);
   public function checkSubscriptionMailing(int $userId);
   public function checkVkDonutSubscription(int $userId);
}
