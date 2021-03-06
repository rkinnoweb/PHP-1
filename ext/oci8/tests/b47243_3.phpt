--TEST--
Bug #47243 (Crash on exit with ZTS mode)
--SKIPIF--
<?php if (!extension_loaded('oci8')) die ("skip no oci8 extension"); ?>
--FILE--
<?php

require(dirname(__FILE__).'/connect.inc');

// Run Test

$s = oci_parse($c, "select cursor(select dummy from dual) from dual");
oci_execute($s);
oci_fetch_all($s, $r);

// With explicit free and close
oci_free_statement($s);
oci_close($c);

?>
===DONE===
<?php exit(0); ?>
--EXPECTF--
===DONE===
