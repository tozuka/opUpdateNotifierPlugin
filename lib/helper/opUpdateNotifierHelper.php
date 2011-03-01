<?php

function op_update_notifier_register_link($targetRoute)
{
  $recipientMemberId = sfContext::getInstance()->getUser()->getMemberId();

  $encodedRoute = urlencode(str_replace('/', '\\', $targetRoute));
  $isRegistered = UpdateNotifierUtil::isNotificationRequestRegistered($targetRoute, $recipientMemberId);
  $count = UpdateNotifierUtil::countNotificationRequestRegistrants($targetRoute);

  $html = '<p align="right"><font size="-1" color="green">[更新通知('.$count.'):'.($isRegistered ? 'ON' : 'OFF');

  if ($isRegistered)
  {
    $html .= ' <a href="/updateNotifier/unregister/'.$encodedRoute.'">→OFF</a>';
  }
  else
  {
    $html .= ' <a href="/updateNotifier/register/'.$encodedRoute.'">→ON</a>';
  }
  $html .= ']</font></p>';

  return $html;
}
