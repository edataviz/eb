<?php
$subMenus = [	array('title' => 'WELL FORECAST', 'link' => 'forecast'),
				array('title' => 'PREoS', 'link' => 'preos'),
				array('title' => 'MANUAL ALLOCATE PLAN', 'link' => 'allocateplan'),
				array('title' => 'LOAD PLAN/FORECAST', 'link' => 'loadplanforecast'),
];
?>
@extends('core.bscontent',['subMenus' => $subMenus])

