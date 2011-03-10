<?php

/**
 * This file is part of the OpenPNE package.
 * (c) OpenPNE Project (http://www.openpne.jp/)
 *
 * For the full copyright and license information, please view the LICENSE
 * file and the NOTICE file that were distributed with this source code.
 */

class opUpdateNotifierPlugin001_createUpdateNotificationRequest extends opMigration
{
  public function up()
  {
    // create table
    $conn = Doctrine_Manager::connection();

    // ashiato create table
    $conn->export->createTable(
      'update_notification_request',
      array(
        'id' => array('type' => 'integer', 'primary' => true, 'autoincrement' => true, 'length' => 4),
        'target_route' => array('type' => 'string', 'notnull' => true, 'length' => 255),
        'recipient_member_id' => array('type' => 'integer', 'notnull' => true, 'length' => 4),
        'created_at' => array('type' => 'timestamp'),
        'updated_at' => array('type' => 'timestamp'),
      ),
      array(
        'type' => 'MyISAM',
        'collate' => 'utf8_unicode_ci',
        'charset' => 'utf8',
      )
    );

    $conn->export->createIndex(
      'update_notification_request',
      'target_and_recipient_UNIQUE',
      array(
        'fields' => array('target_route', 'recipient_member_id'),
        'type'   => 'unique',
      )
    );

  }

  public function down()
  {
    $this->dropTable('update_notification_request');
  }
}
