<?php
    // serverNotes.html.php
    /**
     * Misc notes for you to fill as required, available via frontend
     * (or remark out his page option in configuration.php)
     *
     * @author John Day jdayworkplace@gmail.com
     */
?>
<div id="notes">
    Written and only tested on a Debian based distro.<br>
    <i>Replace the X.X with the php version(s) as required.</i><br>
    <br>
    Make sure the FPM status option is enabled, e.g. in > /etc/php/X.X/fpm/pool.d/www.conf<br>
     <br>
    pm.status_path = /status<br>
    <br>
    <i>I change the status path to something more unique, for example /your-phpXX-fpm-status</i><br>
    <br>
    On a limited resource system with low usage I changed these settings from the more hungry defaults:<br>
    <br>
    pm = ondemand<br>
    pm.max_children = 4<br>
    pm.process_idle_timeout = 10s<br>
    <br>
    and in > /etc/php/X.X/php.ini<br>
    <br>
    memory_limit = 32M<br>
    <br>
    If using Apache, in the virtual host where the status should return, I've used the following for each php version.<br>
    e.g. in > /etc/apache2/sites-available/default.conf<br>
    <br>
    &ltLocation "/your-phpXX-fpm-status&gt<br>
    SetHandler proxy:unix:/var/run/php/phpX.X-fpm.sock|fcgi://localhost<br>
    RewriteEngine Off<br>
    Order Allow,Deny<br>
    Allow from 127.0.0.1<br>
    &lt/Location&gt<br>
    <br>
    This is locked to localhost, but you could also place this behind some authentication.<br>
    NOTES:<br>
    1: many sites use the ProxyPass option, but this only worked for the first listed fpm in the vhost for me.<br>
    2: Location is processed AFTER Directory and File sections<br>
    3: SetHandler is how you can define which php a vhost should use.<br>
    4: need to restart both fpm service and apache after any changes<br>
    <br>
    End.
</div>