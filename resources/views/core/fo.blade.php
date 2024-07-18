<?php
$subMenus = [
		array('title' => 'SAFETY', 'link' => 'safety'),
		array('title' => 'COMMENTS', 'link' => 'comment'),
		array('title' => 'EQUIPMENT', 'link' => 'equipment'),
		array('title' => 'CHEMICAL', 'link' => 'chemical'),
		array('title' => 'PERSONNEL', 'link' => 'personnel'),
		
];
?>
@extends('core.bscontent',['subMenus' => $subMenus])
