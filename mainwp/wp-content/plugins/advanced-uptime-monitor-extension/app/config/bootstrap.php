<?php

MvcConfiguration::set(array(
	'Debug' => false,
));
MvcConfiguration::append(array(
	'AdminPages' => array(
		'uptime_monitors_urls' => array(
			'hide_menu' => true,
		),
		'uptime_monitors' => array(
			'capability' => 'Subscriber',
			'update_monitor' => array(
				'label' => 'Update Monitor',
				'in_menu' => false,
			),
			'hide_menu' => true,
		),
	),
));
