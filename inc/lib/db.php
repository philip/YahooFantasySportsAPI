<?php
/* DB Helper class. Stolen, for the most part, from Rasmus' twitter OAuth example. */
abstract class db {

	protected static $dbh = FALSE;

	public function connect() {
		self::$dbh = new PDO(
			'sqlite:' . DB_PATH,
			'',
			'', 
			array(
				PDO::ATTR_PERSISTENT       => TRUE,
				PDO::ATTR_EMULATE_PREPARES => TRUE,
			)
		);
		self::$dbh->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
	}

	protected function fatal_error( $msg ) {
		echo '<pre>Error!: ', $msg, PHP_EOL;
		$bt = debug_backtrace();
		foreach( $bt as $line ) {
			$args = var_export( $line['args'], TRUE );
			echo "{$line['function']}($args) at {$line['file']}:{$line['line']}\n";
		}
		echo '</pre>', PHP_EOL;
		exit;
	}
}
