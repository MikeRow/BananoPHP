<?php 

	require_once __DIR__ . '/../../src/NanoTools.php';
	require_once __DIR__ . '/../../src/NanoBlocks.php';
	require_once __DIR__ . '/../../src/NanoRPCExt.php';
	
	use php4nano\lib\Nano\Tools as NanoTools;
	
	$nanorpc = new php4nano\Nano\RPCExt();
	
	$private_key    = ''; // Owner account secret key
	$public_key     = ''; // Owner account public key
	$account        = ''; // Owner account
	
	$difficulty   = 'ffffffc000000000'; // Current receive difficulty
	$account_info = $nanorpc->account_info( ['account'=>$account] );
	$block_info   = $nanorpc->block_info( ['json_block'=>true,'hash'=>$account_info['frontier']] );
	
	$work = NanoTools::getWork( $public_key, $difficulty );
	
	$me = new php4nano\Nano\Blocks( $private_key );
	
	//$me->prev_set( $account_info['frontier'], $block_info['contents'] );
	$me->setWork( $work );
	$me->open( '', '', '' );
	
	$open = $nanorpc->process(['json_block'=>'true','block'=>$me->block]);
	
	if( $nanorpc->error ) echo $nanorpc->error . PHP_EOL;
	
	print_r( $open );
