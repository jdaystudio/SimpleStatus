<?php
    // serverDetails.html.php
    /**
     * available variables are defined in Status::ServerDetails() method
     * which then parses and captures this template file for output
     *
     * @author John Day jdayworkplace@gmail.com
     */
?>
<div id="servertable">
<table>
    <tr>
        <th>Server</th>
        <th>CPU</th>
        <th>RAM</th>
        <th>Hard Disk</th>
    </tr>
    <tr>
        <td>
            Name: <?php echo $_SERVER['SERVER_NAME']; ?><br><br>
            Addr: <?php echo $_SERVER['SERVER_ADDR']; ?></td>
        <td>
            Usage: <?php echo $cpuload; ?>%<br><br>
            Threads: <?php echo $cpu_count; ?></td>
        <td>
            Usage: <?php echo $memusage; ?>%<br><br>
            Used: <?php echo $memused; ?> / Free <?php echo $memavailable; ?> / Total <?php echo $memtotal; ?><br><br>
            Shared: <?php echo $memshared; ?> / Cached: <?php echo $memcached; ?>
        </td>
        <td>
            Usage: <?php echo $diskusage; ?>%<br><br>
            Used: <?php echo $diskused; ?>GB / Free: <?php echo $diskfree; ?>GB / Total: <?php echo $disktotal; ?>GB<br><br>
            Swap Total:<?php echo $swaptotal; ?> / Used:<?php echo $swapused; ?> / Free:<?php echo $swapfree; ?>
        </td>
    </tr>

    <tr>
        <td>Default PHP Version<br>
            <?php echo phpversion(); ?>
        </td>
        <td>Connections<br>
            Established: <?php echo $connections; ?> / Total: <?php echo $totalconnections; ?>
        </td>
        <td>PHP Load: <?php echo $phpload; ?>GB</td>
        <td></td>
    </tr>
</table>
</div>