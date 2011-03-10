<?php
$this->dispatcher->connect('form.post_save', array('UpdateNotifierUtil', 'processFormPostSave'));
