<?php require_once( 'header.inc.php' ); ?>
<form id="wpcdnrewrite-config-form" method="post" action="options.php">
    <p>
        <input type="checkbox" id="<?php echo WP_CDN_Purge::RECURSIVE_KEY;?>" name="<?php echo WP_CDN_Purge::RECURSIVE_KEY;?>" value="1" <?php if(get_option( WP_CDN_Purge::RECURSIVE_KEY)=="1") { ?>checked="checked"<?php } ?>>
        <label for="<?php echo WP_CDN_Purge::RECURSIVE_KEY;?>">Hacer flush recursivo.</label>
    </p>
    <p>
        <input type="checkbox" id="<?php echo WP_CDN_Purge::OVERWTITE_IS_MOBILE;?>" name="<?php echo WP_CDN_Purge::OVERWTITE_IS_MOBILE;?>" value="1" <?php if(get_option( WP_CDN_Purge::OVERWTITE_IS_MOBILE)=="1") { ?>checked="checked"<?php } ?>>
        <label for="<?php echo WP_CDN_Purge::OVERWTITE_IS_MOBILE;?>">Sobre escribir la funcion is_mobile usando los headers de cloudfront.</label>
    </p>
    <p>
        <input type="checkbox" id="<?php echo WP_CDN_Purge::FLUSHDEBUG;?>" name="<?php echo WP_CDN_Purge::FLUSHDEBUG;?>" value="1" <?php if(get_option( WP_CDN_Purge::FLUSHDEBUG)=="1") { ?>checked="checked"<?php } ?>>
        <label for="<?php echo WP_CDN_Purge::FLUSHDEBUG;?>">Habiliar mensajes en el log.</label>
    </p>
    <div class="wpcdnrewrite-cloudfront">
        <h3>Cuenta AWS</h3>
        <table>
            <tr>
                <th>Key Id</th>
                <td><input name="<?php echo WP_CDN_Purge::COLDFRONTKEYID_KEY;?>" type="text" value="<?php echo get_option( WP_CDN_Purge::COLDFRONTKEYID_KEY ); ?>"/></td>
            </tr>
            <tr>
                <th>Secret Key</th>
                <td><input name="<?php echo WP_CDN_Purge::COLDFRONTDECRETKEY_KEY;?>" type="text" value="<?php echo get_option( WP_CDN_Purge::COLDFRONTDECRETKEY_KEY ); ?>"/></td>
            </tr>
            <tr>
                <th>Distribution ID</th>
                <td><input name="<?php echo WP_CDN_Purge::COLDFRONTDISTRIBUTIONID_KEY;?>" type="text" value="<?php echo get_option( WP_CDN_Purge::COLDFRONTDISTRIBUTIONID_KEY ); ?>"/></td>
            </tr>
        </table>
    </div>
    <br />
	<table>
		<tr>
			<td>
				<a class="button-secondary" href="options-general.php?page=<?php echo WP_CDN_Purge::SLUG; ?>">Cancel</a>
			</td>
			<td>
				<input class="button-primary" type="submit" Name="save" value="<?php _e( 'Save Options' ); ?>" id="submitbutton" />
				<?php settings_fields( 'wpcdnpurge' ); ?>
			</td>
		</tr>
	</table>
</form>


<form id="wpcdnrewrite-config-form" method="post">
    <div class="wpcdnrewrite-cloudfront">
        <h3>Flush</h3>
        <table>
            <tr>
                <th>Path</th>
                <td><input name="<?php echo WP_CDN_Purge::SINGLE_PATH;?>" type="text" value=""/></td>
            </tr>
            <tr>
                <td>
                    <input class="button-primary" type="submit" Name="save" value="<?php _e( 'Borrar' ); ?>" id="submitbutton" />
                    <?php settings_fields( 'wpcdnpurge' ); ?>
                </td>
            </tr>
        </table>
    </div>
</form>
<?php
    if (isset($_POST[WP_CDN_Purge::SINGLE_PATH])){
        global $wpCdnPurge;
        $path = $wpCdnPurge->clearPath($_POST[WP_CDN_Purge::SINGLE_PATH]);
        $wpCdnPurge->flushCloudFront($path);
        echo "Flush realizado";
    }
?>

<?php require_once( 'footer.inc.php' ); ?>