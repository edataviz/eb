<?php
$subMenus = [
		array('title' => 'FIELDS CONFIG', 'link' => 'fieldconfig'),
		array('title' => 'TABLES DATA', 'link' => 'tabledata'),
		array('title' => 'PD TABLES', 'link' => 'configpd'),
		array('title' => 'TAGS MAPPING', 'link' => 'tagsMapping'),
		array('title' => 'FORMULA EDITOR', 'link' => 'formula'),
		array('title' => 'VIEW CONFIG', 'link' => 'viewconfig'),
];
?>
@extends('core.bscontent',['subMenus' => $subMenus])
