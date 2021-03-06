--TEST--
mysqli_set_local_infile_handler() - negative return value/buflen to indicate an error
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifemb.inc');
require_once('skipifconnectfailure.inc');

if (!function_exists('mysqli_set_local_infile_handler'))
	die("skip - function not available.");

require_once('connect.inc');
if (!$TEST_EXPERIMENTAL)
	die("skip - experimental (= unsupported) feature");

if (!$link = my_mysqli_connect($host, $user, $passwd, $db, $port, $socket))
	die("skip Cannot connect to MySQL");

if (!$res = mysqli_query($link, 'SHOW VARIABLES LIKE "local_infile"')) {
	mysqli_close($link);
	die("skip Cannot check if Server variable 'local_infile' is set to 'ON'");
}

$row = mysqli_fetch_assoc($res);
mysqli_free_result($res);
mysqli_close($link);

if ('ON' != $row['Value'])
	die(sprintf("skip Server variable 'local_infile' seems not set to 'ON', found '%s'",
		$row['Value']));
?>
--INI--
mysqli.allow_local_infile=1
--FILE--
<?php
	require_once('connect.inc');
	require_once('local_infile_tools.inc');
	require_once('table.inc');

	function callback_negative_len($fp, &$buffer, $buflen, &$error) {
		static $invocation = 0;

		printf("Callback: %d\n", $invocation);
		$buffer = fread($fp, $buflen);

		$error = "negative length means error";
		return -1;
	}

	$file = create_standard_csv(1);
	$expected = array();
	try_handler(20, $link, $file, 'callback_negative_len', $expected);

	mysqli_close($link);
	print "done!";
?>
--CLEAN--
<?php
	require_once("clean_table.inc");
?>
--EXPECTF--
Callback set to 'callback_negative_len'
Callback: 0
[022] LOAD DATA failed, [2000] negative length means error
done!