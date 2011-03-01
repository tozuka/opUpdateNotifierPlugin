<?php

include(dirname(__FILE__).'/../../bootstrap/unit.php');
# include(dirname(__FILE__).'/../../bootstrap/database.php');

// contextを用意
$configuration = ProjectConfiguration::getApplicationConfiguration('pc_backend', 'test', isset($debug) ? $debug : true);
sfContext::createInstance($configuration);

#if (isset($load_fixtures))
#{
#  new sfDatabaseManager($configuration);
#  Doctrine_Core::loadData(dirname(__FILE__).'/../../fixtures');
#}

$t = new lime_test(null, new lime_output_color());

$targetRoute = '@communityTopic_show?id='.rand(10000,99999);

$t->diag('更新通知リクエストの登録と解除');

UpdateNotifierUtil::unregisterNotificationRequestsForRoute($targetRoute);
$t->is(array(), UpdateNotifierUtil::getRecipientMemberIds($targetRoute), '指定したルーティングに対する更新通知リクエストを全て解除');

UpdateNotifierUtil::registerNotificationRequest($targetRoute, 2);
UpdateNotifierUtil::registerNotificationRequest($targetRoute, 3);
UpdateNotifierUtil::registerNotificationRequest($targetRoute, 5);
UpdateNotifierUtil::registerNotificationRequest($targetRoute, 7);
$t->is(array(2,3,5,7), UpdateNotifierUtil::getRecipientMemberIds($targetRoute), 'リクエスト登録');

UpdateNotifierUtil::registerNotificationRequest($targetRoute, 5);
$t->is(array(2,3,5,7), UpdateNotifierUtil::getRecipientMemberIds($targetRoute), 'リクエスト重複登録');

UpdateNotifierUtil::unregisterNotificationRequest($targetRoute, 5);
$t->is(array(2,3,7), UpdateNotifierUtil::getRecipientMemberIds($targetRoute), 'リクエスト解除');

UpdateNotifierUtil::unregisterNotificationRequestsForMember(2);
$t->is(array(3,7), UpdateNotifierUtil::getRecipientMemberIds($targetRoute), '指定したメンバーの更新通知リクエストを全て解除');

$caption = '見出し文';
$text = <<<EOT
テキスト
です
EOT;

UpdateNotifierUtil::notifyUpdateForRoute($targetRoute, $caption, $text);
