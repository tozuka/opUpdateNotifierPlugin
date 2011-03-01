<?php

class UpdateNotifierUtil {

  public static function getRecipientMemberIds($targetRoute)
  {
    $conn = opDoctrineQuery::chooseConnection(true);

    return $conn->fetchColumn('SELECT recipient_member_id FROM update_notification_request WHERE target_route= ?', array($targetRoute));
  }

  public static function registerNotificationRequest($targetRoute, $recipientMemberId = null)
  {
    if (is_null($recipientMemberId))
    {
      $recipientMemberId = sfContext::getInstance()->getUser()->getMemberId();
    }

    try
    {
      $req = new UpdateNotificationRequest();
      $req->setTargetRoute($targetRoute);
      $req->setRecipientMemberId($recipientMemberId);
      $req->save();
    }
    catch (Doctrine_Connection_Mysql_Exception $e)
    {
      // already registered. ignored.
    }
    catch (Exception $e)
    {
      // error
    }
  }

  public static function isNotificationRequestRegistered($targetRoute, $recipientMemberId = null)
  {
    if (is_null($recipientMemberId))
    {
      $recipientMemberId = sfContext::getInstance()->getUser()->getMemberId();
    }

    $conn = opDoctrineQuery::chooseConnection(true);
    $result = $conn->fetchOne('SELECT count(*) FROM update_notification_request WHERE target_route = ? AND recipient_member_id = ?', array($targetRoute, $recipientMemberId));

    return $result ? true : false;
  }

  public static function countNotificationRequestRegistrants($targetRoute)
  {
    $conn = opDoctrineQuery::chooseConnection(true);
    $count = $conn->fetchOne('SELECT count(*) FROM update_notification_request WHERE target_route = ?', array($targetRoute));

    return $count;
  }

  public static function unregisterNotificationRequest($targetRoute, $recipientMemberId = null)
  {
    if (is_null($recipientMemberId))
    {
      $recipientMemberId = sfContext::getInstance()->getUser()->getMemberId();
    }

    $conn = opDoctrineQuery::chooseConnection(true);
    $conn->execute('DELETE FROM update_notification_request WHERE target_route = ? AND recipient_member_id = ?', array($targetRoute, $recipientMemberId));
  }

  public static function unregisterNotificationRequestsForRoute($targetRoute)
  {
    $conn = opDoctrineQuery::chooseConnection(true);
    $conn->execute('DELETE FROM update_notification_request WHERE target_route = ?', array($targetRoute));
  }

  public static function unregisterNotificationRequestsForMember($recipientMemberId = null)
  {
    if (is_null($recipientMemberId))
    {
      $recipientMemberId = sfContext::getInstance()->getUser()->getMemberId();
    }

    $conn = opDoctrineQuery::chooseConnection(true);
    $conn->execute('DELETE FROM update_notification_request WHERE recipient_member_id = ?', array($recipientMemberId));
  }

  private static function sendNotificationMail($recipientMemberId, $params)
  {
    $member = Doctrine::getTable('Member')->find($recipientMemberId);
    $mailAddress = $member->getConfig('pc_address');

    opMailSend::sendTemplateMail(
      'updateNotification', $mailAddress,
      opConfig::get('admin_mail_address'), $params);
  }

  public static function notifyUpdateForRoute($route, $caption, $text, $author = null)
  {
    if (is_null($author))
    {
      $author = sfContext::getInstance()->getUser()->getMember()->getName();
    }

    $text_ = $author . '≫' . PHP_EOL;

    foreach (split("\n", $text) as $line)
    {
      $text_ .= '＞ ' . $line . PHP_EOL;
    }

    $params = array(
      'route' => $route,
      'caption' => $caption,
      'author' => $author,
      'text' => $text_,
    );

    foreach (self::getRecipientMemberIds($route) as $recipientMemberId)
    {
      self::sendNotificationMail($recipientMemberId, $params);
    }
  }

}

