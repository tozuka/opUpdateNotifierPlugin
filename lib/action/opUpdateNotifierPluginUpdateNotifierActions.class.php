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


 /**
  * Executes index action
  *
  * @param sfWebRequest $request A request object
  */
  public function executeMylist(sfWebRequest $request)
  {
    $this->member = sfContext::getInstance()->getUser()->getMember();

    $rs = Doctrine::getTable('UpdateNotificationRequest')->createQuery()
      ->select('target_route, created_at')
      ->where('recipient_member_id = ?', $this->member->id)
      ->orderBy('id desc')
      ->fetchArray();

    $this->requests = array();
    foreach ($rs as $r)
    {
      $targetRoute = $r['target_route'];
      $this->requests[] = array('route' => $targetRoute, 
                                'title' => UpdateNotifierUtil::getTitleForRoute($targetRoute),
                                'at' => $r['created_at']);
    }
  }

}
