<?php

class opUpdateNotifierPluginUpdateNotifierActions extends sfActions
{
  public function executeRegister($request)
  {
    $recipientMemberId = sfContext::getInstance()->getUser()->getMemberId();
    $targetRoute = str_replace('\\', '/', $request->getParameter('route'));

    UpdateNotifierUtil::registerNotificationRequest($targetRoute, $recipientMemberId);

    error_log(sprintf("+route=(%s)", $targetRoute));
    $this->redirect($targetRoute);
  }

  public function executeUnregister($request)
  {
    $recipientMemberId = sfContext::getInstance()->getUser()->getMemberId();
    $targetRoute = str_replace('\\', '/', $request->getParameter('route'));

    UpdateNotifierUtil::unregisterNotificationRequest($targetRoute, $recipientMemberId);

    error_log(sprintf("-route=(%s)", $targetRoute));
    $this->redirect($targetRoute);
  }

}
