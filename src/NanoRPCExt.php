<?php

namespace php4nano;

use \Exception as Exception;

class NanoRPCExt extends NanoRPC
{
    // #
    // ## Wallet sweep
    // #
    
    public function wallet_sweep(array $args)
    {
        // Check args
        if (!isset($args['wallet']) || !isset($args['destination'])) {
            $this->error = 'Unable to parse Array';
            return false;
        }
        
        $wallet      = $args['wallet'];
        $destination = $args['destination'];
        
        // Wallet ok?
        $wallet_info = $this->wallet_info(['wallet' => $wallet]);
        
        if ($this->error != null) {
            $this->error = 'Bad wallet number';
            return false;
        }
        
        // Balance ok?
        if (gmp_cmp($wallet_info['balance'], 1) < 0) {
            $this->error = 'Insufficient balance';
            return false;
        }
        
        // Destination ok?
        if (!NanoTool::account2public($destination, false)) {
            $this->error = 'Bad destination';
            return false;
        }

        // Any sort?
        $sort = isset($args['sort']) ? $args['sort'] : 'list';
        
        //
        
        $return = ['balances' => []];
        
        // Get wallet balances
        $args = [
            'wallet'    => $wallet,
            'threshold' => 1
        ];
        
        $wallet_balances = $this->wallet_balances($args);
        
        // Sort balances
        if ($sort == 'asc') {
            uasort($wallet_balances['balances'], function ($a, $b) {
                return gmp_cmp($a['balance'], $b['balance']);
            });
        } elseif ($sort == 'desc') {
            uasort($wallet_balances['balances'], function ($a, $b) {
                return gmp_cmp($b['balance'], $a['balance']);
            });
        } else {
            // Do nothing
        }
        
        // Sweep wallet
        foreach ($wallet_balances['balances'] as $account => $balances) {
            if ($account == $destination) {
                $return['balances'][$account] = [
                    'notice' => 'Skipped self send',
                    'amount' => $balances['balance']
                ];
                continue;
            }
            
            $args = [
                'wallet'      => $wallet,
                'source'      => $account,
                'destination' => $destination,
                'amount'      => $balances['balance'],
                'id'          => uniqid()
            ];
            
            $send = $this->send($args);
            
            // Send
            $return['balances'][$account] = [
                'block'  => $send['block'],
                'amount' => $balances['balance']
            ];
            
            if ($send['block'] == self::EMPTY32) {
                $return['balances'][$account] = [
                    'error'  => 'Bad send',
                    'amount' => $balances['balance']
                ];
            }
        }
        
        $this->responseRaw = json_encode($return);
        $this->response    = $return;
        
        return $this->response;
    }
    
    public function wallet_wipe(array $args)
    {
        return $this->wallet_sweep($args);
    }
    
    
    // #
    // ## Wallet send
    // #
    
    public function wallet_send(array $args)
    {
        // Check args
        if (!isset($args['wallet']) || !isset($args['destination']) || !isset($args['amount'])) {
            $this->error = 'Unable to parse Array';
            return false;
        }
        
        $wallet      = $args['wallet'];
        $destination = $args['destination'];
        $amount      = $args['amount'];
        
        // Wallet ok?
        $wallet_info = $this->wallet_info(['wallet' => $wallet]);
        
        if ($this->error != null) {
            $this->error = 'Bad wallet number';
            return false;
        }
    
        // Destination ok?
        if (!NanoTool::account2public($destination, false)) {
            $this->error = 'Bad destination';
            return false;
        }
        
        // Amount ok?
        if (!ctype_digit($amount)) {
            $this->error = 'Bad amount';
            return false;
        }
        
        if (gmp_cmp($amount, 1) < 0) {
            $this->error = 'Bad amount';
            return false;
        }
        
        if (gmp_cmp($wallet_info['balance'], $amount) < 0) {
            $this->error = 'Insufficient balance';
            return false;
        }
        
        // Any sort?
        $sort = isset($args['sort']) ? $args['sort'] : 'list';
        
        //
        
        $return            = ['balances' => []];
        $selected_accounts = [];
        $amount_left       = $amount;
        
        // Get wallet balances
        $args = [
            'wallet'    => $wallet,
            'threshold' => 1
        ];
        
        $wallet_balances = $this->wallet_balances($args);
            
        // Sort balances
        if ($sort == 'asc') {
            uasort($wallet_balances['balances'], function ($a, $b) {
                return gmp_cmp($a['balance'], $b['balance']);
            });
        } elseif ($sort == 'desc') {
            uasort($wallet_balances['balances'], function ($a, $b) {
                return gmp_cmp($b['balance'], $a['balance']);
            });
        } else {
            // Do nothing
        }
        
        // Select accounts
        foreach ($wallet_balances['balances'] as $account => $balances) {
            if (gmp_cmp($balances['balance'], $amount_left) >= 0) {
                $selected_accounts[$account] = $amount_left;
                $amount_left                 = '0';
            } else {
                $selected_accounts[$account] = $balances['balance'];
                $amount_left                 = gmp_strval(gmp_sub($amount_left, $balances['balance']));
            }
            
            if (gmp_cmp($amount_left, '0') <= 0) {
                break; // Amount reached
            }
        }

        // Send from selected accounts
        foreach ($selected_accounts as $account => $balance) {
            if ($account == $destination) {
                $return['balances'][$account] = [
                    'notice' => 'Skipped self send',
                    'amount' => $balances['balance']
                ];
                continue;
            }
            
            $args = [
                'wallet'      => $wallet,
                'source'      => $account,
                'destination' => $destination,
                'amount'      => $balance,
                'id'          => uniqid()
            ];
            
            $send = $this->send($args);

            // Send
            $return['balances'][$account] = [
                'block'  => $send['block'],
                'amount' => $balances['balance']
            ];
            
            if ($send['block'] == self::EMPTY32) {
                $return['balances'][$account] = [
                    'error'  => 'Bad send',
                    'amount' => $balances['balance']
                ];
            }
        }
        
        $this->responseRaw = json_encode($return);
        $this->response    = $return;
        
        return $this->response;
    }
    
     
    // #
    // ## Wallet weight
    // #
    
    public function wallet_weight(array $args)
    {
        // Check args
        if (!isset($args['wallet'])) {
            $this->error = 'Unable to parse Array';
            return false;
        }
        
        $wallet = $args['wallet'];
        
        // Wallet ok?
        $wallet_info = $this->wallet_info(['wallet' => $wallet]);
        
        if ($this->error != null) {
            $this->error = 'Bad wallet number';
            return false;
        }
        
        // Any sort?
        
        $sort = isset($args['sort']) ? $args['sort'] : 'list';
        
        //
        
        $return = ['weight' => '', 'weights' => []];
        $wallet_weight = '0';
        
        // Get wallet balances
        $args = [
            'wallet' => $wallet
        ];
        
        $wallet_accounts = $this->account_list($args);
        
        // Check every weight and sum them
        foreach ($wallet_accounts['accounts'] as $account) {
            $account_weight              = $this->account_weight(['account'=>$account]);
            $wallet_weight               = gmp_add($wallet_weight, $account_weight['weight']);
            $return['weights'][$account] = gmp_strval($account_weight['weight']);
        }
        
        $return['weight'] = gmp_strval($wallet_weight);
        
        // Sort weights
        if ($sort == 'asc') {
            uasort($return['weights'], function ($a, $b) {
                return gmp_cmp($a, $b);
            });
        } elseif ($sort == 'desc') {
            uasort($return['weights'], function ($a, $b) {
                return gmp_cmp($b, $a);
            });
        } else {
            // Do nothing
        }
        
        $this->responseRaw = json_encode($return);
        $this->response    = $return;
        
        return $this->response;
    }
}
