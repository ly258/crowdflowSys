<?php
$path = dirname(__FILE__);
//$jsontxt = "--json '{\"ccd_w\": 3264.0, \"ccd_h\": 2448.0, \"f\": 2056.063, \"tilt\": 0.52359877559829882, \"pan\":1.3917349934819336, \"location\":[680249.10911166156, 3555004.8915008344, 15.250000000000005],\"maxLength\":-1}'";

if (!array_key_exists("json",$_POST))
{
	echo '{"error":0}';
}
//echo "python ".$path."/inverseParamter.py --json '" .$_POST["json"]."'";
system("python ".$path."/inverseParamter.py --json '" .$_POST["json"]."'");
?>
