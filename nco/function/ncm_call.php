<?php

// # Call ncm

function ncm_call(
    &$ssh,
    string $ncm_path,
    string $command,
    array $arguments,
    string $flags = '',
    string $callerID = 'remote-script'
)
{
    if ($flags != '') {
        $flags .= ',';
    }
    
    $flags .= 'json_in,json_out,no_confirm';
    
    $return = $ssh->exec(
        "php $ncm_path $command '" .
        json_encode($arguments) .
        "' flags=$flags callerID=$callerID" .
        PHP_EOL
    );
    
    return json_decode($return, true);
}
