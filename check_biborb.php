<html>
<head>
<title>Test BibORB installation</title>
<style type="text/css">

.error {
    font-weight:bold;
    color:red;
}

.ok {
    font-weight:bold;
    color:green;
}

h1 {
    text-align:center;
}
    
.report {
    margin:auto;
    border-collapse:collapse;
}

.report caption {
    font-weight:bold;
    font-size:large;
}

.report tr {
    border: solid 1px black;
}

.report th {
    color:white;
    background-color:#777;
    text-align:left;
    width:200px;
}

.report td {
    text-align:left;
    width:300px;
    padding-left:1em;
    background-color:#ddd;
}

</style>
</head>
<body>

<?php

$OS_TYPE = strtoupper(substr(PHP_OS, 0, 3));
$XSLT_LOADED = extension_loaded('xslt');
if($OS_TYPE == "Win"){
    $XSLT_MODULE = "php_xslt.dll";
}
else{
    $XSLT_MODULE = "xslt.so";
}

$YES_REPORT = "<span class='ok'>YES</span>";
$NO_REPORT = "<span class='error'>NO</span>";
?>

<h1>Testing biborb installation</h1>

<table class="report">
    <caption>Information</caption>
    <tbody>
        <tr>
            <th>BibORB directory</th>
            <td><?php echo realpath("."); ?></td>
        </tr>
        <tr>
            <th>PHP version</th>
            <td><?php echo phpversion() ?></td>
        </tr>
        <tr>
            <th>OS</th>
            <td><?php echo PHP_OS; ?></td>
        </tr>
    </tbody>
</table>

<table class="report">
    <caption>XSLT support</caption>
    <tbody>
        <tr>
            <th>XSLT module loaded:</th>
            <td><?php echo ($XSLT_LOADED ? $YES_REPORT : $NO_REPORT) ?></td>
        </tr>
            <?php                
            if(!$XSLT_LOADED){
                echo "<tr><th>Loading extensions is allowed?</th><td>";
                if(!(bool)ini_get( "enable_dl" ) || (bool)ini_get( "safe_mode" )){
                    echo $NO_REPORT;
                }
                else{
                    echo $YES_REPORT;
                }
                echo "<tr><th>PHP extendion directory</th><td>".realpath(ini_get("extension_dir"))."</td></tr>";
                echo "<tr><td>XSLT module ($XSLT_MODULE) is present in extension_dir?</td><td>";
                if(!file_exists(ini_get("extension_dir")."/".$XSLT_MODULE)) {
                    echo $NO_REPORT;
                }
                else{
                    echo $YES_REPORT;
                } 
                echo "</td></tr>";
            }
             ?>
    </tbody>
</table>

<table class="report">
    <caption>Testing BibORB</caption>
    <tbody>
        <tr>
            <th>Write access to bibs repository?</th>
            <td><?php echo (is_writable("./bibs") ? $YES_REPORT : $NO_REPORT); ?></td>
        </tr>
        <tr>
            <th>Write access to bibs/.trash repository?</th>
            <td><?php echo (is_writable("./bibs/.trash") ? $YES_REPORT : $NO_REPORT); ?></td>
        </tr>
    </tbody>
</table>

</body>
</html>
