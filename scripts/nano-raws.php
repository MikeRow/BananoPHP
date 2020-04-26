<?php

	/*
		Nano uses huge integers to represent even a tiny value, for example:
		
			1 NANO = 1 Mnano = 1000000000000000000000000000000000000 raw
			
		Since PHP doesn't support mathematical operations with such huge integers, you need a dedicated library
		
		GNU Multiple Precision (GMP) fits this work: https://www.php.net/manual/en/book.gmp.php
	*/

	$raw1 = '2000000000000000000000000000000000000'; // 2 NANO
	$raw2 = '1000000000000000000000000000000000000'; // 1 NANO
	$raw3 = '2000000000000000000000000000000000000'; // 2 NANO
	
	// Sum of raws
	
	echo gmp_strval( gmp_add( $raw1, $raw2 ) ) . PHP_EOL; // Prints '3000000000000000000000000000000000000' (3 NANO)
	
	// Subtraction of raws
	
	echo gmp_strval( gmp_sub( $raw1, $raw2 ) ) . PHP_EOL; // Prints '1000000000000000000000000000000000000' (1 NANO)
	
	// Multiplication of raws
	
	echo gmp_strval( gmp_sub( $raw1, '5' ) ) . PHP_EOL; // Prints '10000000000000000000000000000000000000' (10 NANO)
	
	// Division of raws
	
	echo gmp_strval( gmp_sub( $raw1, '2' ) ) . PHP_EOL; // Prints '1000000000000000000000000000000000000' (1 NANO)
	
	// Comparison of raws
	
	echo gmp_cmp( $raw1, $raw2 ) . PHP_EOL; // Prints 1
	echo gmp_cmp( $raw2, $raw1 ) . PHP_EOL; // Prints -1
	echo gmp_cmp( $raw1, $raw3 ) . PHP_EOL; // Prints 0

?>