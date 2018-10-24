<?php
if ( ! is_array( $this->params['data']['UptimeUrl'] ) ) {  // is it object retrieved for monitor editing?
	$url = $this->params['data']['UptimeUrl'];
	$this->params['data']['UptimeUrl'] = array();
	$this->params['data']['UptimeUrl']['url_friendly_name'] = $url->url_friendly_name;
	$this->params['data']['UptimeUrl']['url_api_key'] = $url->url_api_key;
	$this->params['data']['UptimeUrl']['url_address'] = $url->url_address;
	$this->params['data']['UptimeUrl']['url_not_email'] = $url->url_not_email;
	$this->params['data']['UptimeUrl']['url_monitor_type'] = $url->url_monitor_type;
	$this->params['data']['UptimeUrl']['url_monitor_subtype'] = $url->url_monitor_subtype;
	$this->params['data']['UptimeUrl']['url_monitor_keywordtype'] = $url->url_monitor_keywordtype;
	$this->params['data']['UptimeUrl']['url_monitor_keywordvalue'] = $url->url_monitor_keywordvalue;
	$this->params['data']['UptimeUrl']['url_id'] = $url->url_id;
}

//For test on local host  +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
global $wpdb;
// process to get url not exist monitor
global $current_user;
$orderby = 'wp.url';
global $wpdb;
$sql = 'SELECT url_address FROM ' . $wpdb->prefix . 'aum_urls';
$result = $wpdb->get_results( $sql );
$site_names_exits = array();
for ( $i = 0; $i < count( $result ); $i++ ) {
	$site_names_exits[ $i ] = $result[ $i ]->url_address;
}
global $mainwpAdvancedUptimeMonitorExtensionActivator;
$pWebsites = apply_filters( 'mainwp-getsites', $mainwpAdvancedUptimeMonitorExtensionActivator->get_child_file(), $mainwpAdvancedUptimeMonitorExtensionActivator->get_child_key(), null );
if ( count( $site_names_exits ) > 0 ) {
	foreach ( $pWebsites as $website ) {
		$url = rtrim( $website['url'], '/' );
		if ( ! in_array( $url, $site_names_exits ) ) {
			$site_names[ $url ] = stripslashes( $website['name'] );
		}
	}
} else {
	foreach ( $pWebsites as $website ) {
		$url = rtrim( $website['url'], '/' );
		$site_names[ $url ] = stripslashes( $website['name'] );
	}
}

if ( count( $site_names ) > 0 ) {
	//            $tring_result = array_shift(array_keys($site_names));
	$_arr_keys = array_keys( $site_names );
	$tring_result = array_shift( $_arr_keys );
} else {
	$tring_result = 'http://';
}

$list_notification_contact = Advanced_Uptime_Monitor_Extension::get_install()->get_option( 'list_notification_contact' );
?>
<fieldset class="url_form form_fieldset uptime_monitor_popup">
    <h3><?php echo esc_html( $title ); ?></h3>
    <?php $this->display_flash(); ?>
    <?php echo $this->form->create( $model->name ); ?>
    <?php
	if ( isset( $this->params['url_id'] ) ) {
		echo $this->form->hidden_input( 'url_id', array( 'value' => $this->params['url_id'] ) );
	} elseif (isset( $this->params['data']['UptimeUrl']['url_id'] ))
		echo $this->form->hidden_input( 'url_id', array( 'value' => $this->params['data']['UptimeUrl']['url_id'] ) );
	if ( isset( $monitor ) ) {
		echo $this->form->hidden_input( 'monitor_id', array( 'value' => $monitor->monitor_id ) );
	} elseif ($this->params['monitor_id'])
		echo $this->form->hidden_input( 'monitor_id', array( 'value' => $this->params['monitor_id'] ) );

    if (isset($this->params['api_key']))
        echo $this->form->hidden_input( 'add_monitor_api_key', array( 'value' => $this->params['api_key'] ) );

	?>
    <input type="hidden" name="title" value="<?php echo $title ?>" />

    <?php //echo $this->form->input('url_friendly_name',array('label' => 'URL Friendly Name:', 'value' => (isset($this->params['data']['UptimeUrl'])?$this->params['data']['UptimeUrl']['url_friendly_name']:''))); ?>
    <?php
	if ( ! isset( $this->params['url_id'] ) && ! isset( $this->params['data']['UptimeUrl']['url_id'] ) && count( $site_names ) > 0 ) {
		?>
        <div class = "cell choose_checkbox">                    
            <input type="checkbox" name="checkbox_show_select" class = "checkbox_show_select" id="checkbox_show_select" <?php echo (isset( $this->params['checkbox_show_select'] ) ? 'checked="checked"' : ''); ?>>                   
            <label> Load Child Sites</label> 
        </div>        
        <?php
	}

	if ( isset( $this->params['data']['UptimeUrl'] ) && ! empty( $this->params['data']['UptimeUrl']['url_api_key'] ) ) {
		echo $this->form->input( 'url_api_key', array( 'label' => 'Monitor API key:', 'value' => (isset( $this->params['data']['UptimeUrl'] ) ? $this->params['data']['UptimeUrl']['url_api_key'] : '') ) );
	}
	$style_select = 'style="display:none"';
	$style_text = 'style="display:block"';
	if ( isset( $this->params['checkbox_show_select'] ) && count( $site_names ) >= 1 ) {
		$style_select = 'style="display:block"';
		$style_text = 'style="display:none"';
	} else {
		$friendly_n = '';
		if ( isset( $this->params['data']['UptimeUrl']['url_friendly_name'] ) ) {
			$friendly_n = $this->params['data']['UptimeUrl']['url_friendly_name'];
		}
	}
	?>          
    <div class="monitor_url_friendly_name_text" <?php echo $style_text; ?>>
        <label>Site Name:</label>                              
        <input type="text" name="url_friendly_name_textbox" value="<?php echo esc_attr( $friendly_n ); ?>">
    </div>          

    <div class="monitor_url_friendly_name" <?php echo $style_select; ?>>
        <label>Site Name:</label>  
        <?php if ( $url_saved ) { ?>
            <input type="text" name="data[UptimeUrl][url_friendly_name]" value="<?php echo esc_attr( $this->params['data']['UptimeUrl']['url_friendly_name'] ); ?>">
            <?php
} else {
	$args = array( 'options' => $site_names, 'value' => $this->params['data']['UptimeUrl']['url_friendly_name'] );
	echo $this->form->select( 'data[UptimeUrl][url_friendly_name]', $args );
}
		?>
    </div>              

    <?php
	if ( isset( $this->params['data']['UptimeUrl']['url_address'] ) ) {
		echo $this->form->input( 'url_address', array( 'label' => 'Site URL:', 'value' => $this->params['data']['UptimeUrl']['url_address'] ) );
	} else {
		echo $this->form->input( 'url_address', array( 'label' => 'Site URL:', 'value' => 'http://' ) );
	}
	?>
</div> 

<?php
if ( is_array( $list_notification_contact ) && count( $list_notification_contact ) > 0 ) {
	?>
    <table name="list_contact" >     

        <tr>
        <label>Alert Contacts</label>
        </tr>

    <?php
	$alert_contacts = array();
	if ( ! empty( $this->params['monitor_contacts_notification'] ) ) {
		$alert_contacts = explode( '-', $this->params['monitor_contacts_notification'] );
	} else if ( isset( $this->params['data']['UptimeUrl']['url_not_email'] ) && count( $this->params['data']['UptimeUrl']['url_not_email'] ) > 0 ) {
		$alert_contacts = $this->params['data']['UptimeUrl']['url_not_email'];
	}

	$default_contact_id = Advanced_Uptime_Monitor_Extension::get_install()->get_option( 'uptime_default_notification_contact_id' );

	if ( count( $alert_contacts ) > 0 ) {
		foreach ( $list_notification_contact as $key => $val ) {
			$checked_flag = '';
			if ( in_array( $key, $alert_contacts ) ) {
				$checked_flag = 'checked="checked"';
			}

			echo '<tr>
                                        <td>   
                                                     <input type="checkbox" name="checkbox_contact" value="' . esc_attr( $key ) . '"' . $checked_flag . '>
                                                 </td>
                                                  <td scope="row" class="">
                                                     ' . esc_html( $val ) . '
                                                 </td> 
                                          </tr>';
		}
	} else {

		foreach ( $list_notification_contact as $key => $val ) {
			if ( $default_contact_id == $key ) {
				$checked = isset( $this->params['data']['UptimeUrl']['url_id'] ) ? '' : ' checked="checked"';
				/* $checked = ' checked="checked" ';  */
			} else {
				$checked = '';
			}
			echo '<tr>
                                           <td>   
                                                <input type="checkbox" name="checkbox_contact" value="' . esc_attr( $key ) . '" ' . $checked . '>
                                            </td>
                                             <td scope="row" class="">
                                                ' . esc_html( $val ) . '
                                            </td> 
                                     </tr>';
		}
	}
	?>            

    </table>     
    <?php
}
?>

<input type="hidden" name="monitor_contacts_notification" value="<?php echo (isset( $this->params['monitor_contacts_notification'] ) ? esc_attr( $this->params['monitor_contacts_notification'] ) : esc_attr( $default_contact_id )); ?>" />

<?php //echo $this->form->input('url_not_email',array('label' => 'Monitor Notification Email:', 'value' => (isset($this->params['data']['UptimeUrl'])?$this->params['data']['UptimeUrl']['url_not_email']:'')));  ?>

<div class="monitor_type">
    <label>Monitor Type:</label>
    <?php
	$monitor_types = array( '1' => 'HTTP(s)', '2' => 'Keyword Checking', '3' => 'Ping', '4' => 'TCP Ports' );
	$args = array( 'options' => $monitor_types );
	if ( isset( $this->params['data']['UptimeUrl'] ) ) {
		$args['value'] = $this->params['data']['UptimeUrl']['url_monitor_type'];
	}
	echo $this->form->select( 'data[UptimeUrl][url_monitor_type]', $args );
	?>
</div>
<div class="monitor_subtype">
    <label>Monitor Subtype:</label>     
    <?php
	$monitors_subtypes = array(
		'1' => 'HTTP',
		'2' => 'HTTPS',
		'3' => 'FTP',
		'4' => 'SMTP',
		'5' => 'POP3',
		'6' => 'IMAP',
	);

	$args = array( 'options' => $monitors_subtypes );
	if ( isset( $this->params['data']['UptimeUrl'] ) ) {
		$args['value'] = $this->params['data']['UptimeUrl']['url_monitor_subtype'];
	}
	echo $this->form->select( 'data[UptimeUrl][url_monitor_subtype]', $args );
	?>  
</div>
<div class="url_monitor_keywordtype">
    <label>Alert when:</label>      
    <input type="radio" name="data[UptimeUrl][url_monitor_keywordtype]" value=1 id="keywordtype1" <?php echo (isset( $this->params['data']['UptimeUrl'] ) && $this->params['data']['UptimeUrl']['url_monitor_keywordtype'] == '1' ? 'checked=checked' : '') ?> /><label for="keywordtype1" class="label1">exists</label>&nbsp;
    <input type="radio" name="data[UptimeUrl][url_monitor_keywordtype]" value=2 id="keywordtype2" <?php echo (isset( $this->params['data']['UptimeUrl'] ) && $this->params['data']['UptimeUrl']['url_monitor_keywordtype'] == '2' ? 'checked=checked' : '') ?> /><label for="keywordtype2" class="label1">not exists</label>
</div>  
<div class="url_monitor_keywordvalue">
    <label>Keyword:</label>     
    <textarea name="data[UptimeUrl][url_monitor_keywordvalue]"><?php echo (isset( $this->params['data']['UptimeUrl'] ) ? esc_textarea( $this->params['data']['UptimeUrl']['url_monitor_keywordvalue'] ) : '') ?></textarea>
</div>
<div class="clearfix"></div>
<?php
$submit_text = isset( $this->params['data']['UptimeUrl']['url_id'] ) ? 'Save' : 'Create';
echo $this->form->end( $submit_text );
?>
</fieldset>
<a href="javascript:void(0)" class="close_link"><img src="<?php echo plugins_url( 'images/close.png', __FILE__ ) ?>" /></a>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
<script type="text/javascript">
    jQuery(document).ready(function () {
        var admin_url = '<?php echo get_admin_url(); ?>';
        jQuery('.url_form form input[type="submit"]').addClass('aum_button');

        jQuery('.url_form form').attr('action', admin_url + 'admin-ajax.php?action=admin_uptime_monitors_url_form&wp_nonce=<?php echo wp_create_nonce( AdminUptimeMonitorsController::$nonce_token . 'url_form' ); ?>');

        jQuery('a.close_link').click(function () {
            var el = window.frameElement;
            el.parentNode.removeChild(el);
        });

        jQuery('input[name=checkbox_show_select]').change(function () {
            var item = '<?php echo $tring_result; ?>';
            if (jQuery(this).is(':checked')) {
                jQuery('div.monitor_url_friendly_name_text').css('display', 'none');
                jQuery('div.monitor_url_friendly_name').css('display', 'block');
                jQuery('div.monitor_url_friendly_name div select').prop('selectedIndex', 0);

                jQuery('input#UptimeUrlUrlAddress').val(item);
                jQuery('input#UptimeUrlUrlAddress').attr('readonly', 'readonly');
            } else {
                jQuery('div.monitor_url_friendly_name').css('display', 'none');
                jQuery('div.monitor_url_friendly_name_text').css('display', 'block');
                jQuery('div.monitor_url_friendly_name_text input').val('');
                jQuery('input#UptimeUrlUrlAddress').val('http://');
                jQuery('input#UptimeUrlUrlAddress').removeAttr('readonly');
            }
        });

        jQuery('.monitor_type select').change(function () {
            if (jQuery(this).val() == '4')
                jQuery('.monitor_subtype').show();
            else
                jQuery('.monitor_subtype').hide();

            if (jQuery(this).val() == '2') {
                jQuery('.url_monitor_keywordtype').show();
                jQuery('.url_monitor_keywordvalue').show();
            } else {
                jQuery('.url_monitor_keywordtype').hide();
                jQuery('.url_monitor_keywordvalue').hide();
            }
        })

        jQuery('div.monitor_url_address select').attr('disabled', 'disabled');

        jQuery('.monitor_url_friendly_name').change(function ()
        {
            var select = jQuery('.monitor_url_friendly_name select option:selected').val();
            jQuery('input#UptimeUrlUrlAddress').val(select);
        })
        jQuery('.url_form form').submit(function ()
        {
            if (jQuery('input[name=monitor_contacts_notification]').val() == '')
            {
                jQuery('fieldset div#message1').remove();
                jQuery('fieldset.uptime_monitor_popup h3').after(' <div class="notice updated" id="message1" style="display:block;"><p>Please select atleast one Alert Contact</p></div>');
                setTimeout(function () {
                    jQuery('fieldset div#message1').css('display', 'none');
                }, 2000);
                return false;
            }

            jQuery('div.monitor_url_friendly_name select option').each(function () {
                var html_name = jQuery(this).html();
                jQuery(this).val(html_name);
            })
        })
        jQuery('table[name=list_contact]').change(function () {
            var contacts_chossen = '';
            jQuery('input:checkbox[name=checkbox_contact]').each(function () {
                if (this.checked)
                {                  // jQuery.trim(contacts_chossen);  
                    contacts_chossen += jQuery.trim(jQuery(this).val()) + '-';
                }
            });
            contacts_chossen = contacts_chossen.slice(0, -1);
            jQuery('input[name=monitor_contacts_notification]').val(contacts_chossen);
        });
<?php
if ( isset( $this->params['data']['UptimeUrl'] ) && $this->params['data']['UptimeUrl']['url_monitor_type'] == '4' ) {
	echo "jQuery('.monitor_subtype').show();";
}

if ( isset( $this->params['data']['UptimeUrl'] ) && $this->params['data']['UptimeUrl']['url_monitor_type'] == '2' ) {
	echo "jQuery('.url_monitor_keywordtype').show();jQuery('.url_monitor_keywordvalue').show();";
}
if ( $url_saved ) {
	?>
            jQuery('.url_form').find('input').attr('disabled', 'disabled');
            jQuery('.url_form').find('select').attr('disabled', 'disabled');
            jQuery('.url_form').find('textarea').attr('disabled', 'disabled');
            setTimeout(function () {
                parent.location.reload(true);
            }, 2000);
    <?php
}
?>
    });
</script>

