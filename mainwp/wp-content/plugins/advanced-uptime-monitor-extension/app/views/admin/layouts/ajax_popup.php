<html>    <head>		<?php		$site_url = home_url();		$path = dirname( dirname( dirname( dirname( __FILE__ ) ) ) );		?>        <script type='text/javascript' src='<?php echo $site_url; ?>/wp-admin/load-scripts.php?c=1&amp;load%5B%5D=jquery,utils&amp;ver=3.5.1'></script>        <link rel='stylesheet' id='urm-css'  href='<?php echo plugins_url( 'css/style.css?ver=3.5.2', $path ); ?>' type='text/css' media='all' />        <link rel='stylesheet' id='urm-admin-css'  href='<?php echo plugins_url( 'css/admin.css?ver=3.5.2', $path ); ?>' type='text/css' media='all' />    </head>    <body>		<?php $this->render_main_view(); ?>    </body></html>