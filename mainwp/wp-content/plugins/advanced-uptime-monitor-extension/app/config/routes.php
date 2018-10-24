<?php

MvcRouter::public_connect( '{:controller}', array( 'action' => 'index' ) );
MvcRouter::public_connect( '{:controller}/{:id:[\d]+}', array( 'action' => 'show' ) );
MvcRouter::public_connect( '{:controller}/{:action}/{:id:[\d]+}' );
MvcRouter::admin_ajax_connect( array( 'controller' => 'admin_uptime_monitors', 'action' => 'monitor_form' ) );
MvcRouter::admin_ajax_connect( array( 'controller' => 'admin_uptime_monitors', 'action' => 'delete_monitor' ) );
MvcRouter::admin_ajax_connect( array( 'controller' => 'admin_uptime_monitors', 'action' => 'update_monitor' ) );
MvcRouter::admin_ajax_connect( array( 'controller' => 'admin_uptime_monitors', 'action' => 'get_urls' ) );
// MvcRouter::admin_ajax_connect(array('controller' => 'admin_uptime_monitors', 'action' => 'add_url'));
MvcRouter::admin_ajax_connect( array( 'controller' => 'admin_uptime_monitors', 'action' => 'delete_url' ) );
MvcRouter::admin_ajax_connect( array( 'controller' => 'admin_uptime_monitors', 'action' => 'display_dashboard' ) );
MvcRouter::admin_ajax_connect( array( 'controller' => 'admin_uptime_monitors', 'action' => 'update_url' ) );
MvcRouter::admin_ajax_connect( array( 'controller' => 'admin_uptime_monitors', 'action' => 'url_form' ) );
MvcRouter::admin_ajax_connect( array( 'controller' => 'admin_uptime_monitors', 'action' => 'url_start' ) );
MvcRouter::admin_ajax_connect( array( 'controller' => 'admin_uptime_monitors', 'action' => 'url_pause' ) );
MvcRouter::admin_ajax_connect( array( 'controller' => 'admin_uptime_monitors', 'action' => 'statistics_table' ) );
MvcRouter::admin_ajax_connect( array( 'controller' => 'admin_uptime_monitors', 'action' => 'meta_box' ) );
MvcRouter::admin_ajax_connect( array( 'controller' => 'admin_uptime_monitors', 'action' => 'option_page' ) );
