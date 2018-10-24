<?php 
/*
Plugin-Name: Wordfence Security
Plugin-URI: http://www.wordfence.com/
Description: Wordfence Security - Anti-virus, Firewall and High Speed Cache
Author: Wordfence
Version: 5.2.1
Author-URI: http://www.wordfence.com/
*/
?>
<select class="wfConfigElem" id="<?php echo $throtName; ?>" name="<?php echo $throtName; ?>">
	<option value="throttle"<?php $w->sel($throtName, 'throttle'); ?>>throttle it</option>
	<option value="block"<?php $w->sel($throtName, 'block'); ?>>block it</option>
</select>
