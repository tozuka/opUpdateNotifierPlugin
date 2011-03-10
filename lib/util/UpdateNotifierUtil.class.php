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


  public static function notify_update($text, $place, $route, $author = null)
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
      'text' => $text_,
      'place' => $place,
      'route' => $route,
      'author' => $author,
    );

    foreach (self::getRecipientMemberIds($route) as $recipientMemberId)
    {
      self::sendNotificationMail($recipientMemberId, $params);
    }
  }

  // 旧API（なにもしない）
  public static function notifyUpdateForRoute($route, $caption, $text, $author = null)
  {
  }

  public function processFormPostSave($event)
  {
    $form = $event->getSubject();
    $author = sfContext::getInstance()->getUser()->getMember()->getName();
    $i18n = sfContext::getInstance()->getI18N();

    switch (get_class($form))
    {
      case 'DiaryForm':
        $diary = $form->getObject();

        $text = $diary->body;
        $place = $author.'さんの'.$i18n->__('Diary');
        $route = '@diary_show?id='.$diary->id;
        break;

      case 'DiaryCommentForm':
        $diaryComment = $form->getObject();
        $diary = $diaryComment->Diary;

        $text = $diaryComment->body;
        $place = $author.'さんの'.$i18n->__('Diary');
        $route = '@diary_show?id='.$diary->id.'&comment_count='.$diary->countDiaryComments(true);
        break;

      case 'CommunityEventForm':
        $communityEvent = $form->getObject();

        $text = $communityEvent->body;
        $place = $i18n->__('Community Events').' '.$communityEvent->name;
        $route = '@communityEvent_show?id='.$communityEvent->getId();
        break;

      case 'CommunityEventCommentForm':
        $communityEventComment = $form->getObject();
        $communityEvent = $communityEventComment->CommunityEvent;

        $text = $communityEventComment->body;
        $place = $i18n->__('Community Events').' '.$communityEvent->name;
        $route = '@communityEvent_show?id='.$communityEvent->getId();
        break;

      case 'CommunityTopicForm':
        $communityTopic = $form->getObject();

        $text = $communityTopic->body;
        $place = $i18n->__('Community Topics').' '.$communityTopics->name;
        $route = '@communityTopic_show?id='.$communityTopic->getId();
        break;

      case 'CommunityTopicCommentForm':
        $communityTopicComment = $form->getObject();
        $communityTopic = $communityTopicComment->CommunityTopic;

        $text = $communityTopicComment->body;
        $place = $i18n->__('Community Topics').' '.$communityTopic->name;
        $route = '@communityTopic_show?id='.$communityTopic->getId();
        break;

      default:
        //error_log('form.post_save event from '.get_class($form).' is not supported.');
        return;
    } 

    UpdateNotiferUtil::notify_update($text, $place, $route, $author);
  }
}

