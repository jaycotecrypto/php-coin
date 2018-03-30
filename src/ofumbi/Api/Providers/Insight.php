<?php
namespace ofumbi\Api\Providers;
use ofumbi\Api\Providers\Provider;
use ofumbi\Api\ApiInterface;
use Graze\GuzzleHttp\JsonRpc\Client;

class Insight extends Provider implements ApiInterface
{
	

    public function __construct(string $insighturl)
    {
        parent::__construct( $insighturl );
    }	

	public function listunspent($minconf, array $addresses=[], $max = null){
		$endpoint = "/addrs/".explode(',',$addresses)."/utxo";
		$result = $this->httpRequest($endpoint);
		return $result;
	}
	
	public function addressTx(array $addresses=[], $blocks = []){
		$adrs = $addresses->pluck('addresss');
		$endpoint = "/addrs/".explode(',',$adrs)."/txs";
		$from = 0;
		$to = 50;
		$result = $this->httpRequest($endpoint."?from={$from}&to={$to}");
		$txs = collect($result->items);
		if($result->pagesItems > 50 ){
			$loops = ceil($result->pagesItems/50); // loop through all the pages
			for($i=2 ;$i == $loops; $i++ ){ 
				$from = $to;
				$to = $to*$i;
				$response = $this->httpRequest($endpoint."?from={$from}&to={$to}");
				$txs->concat($response->items);
			}
		}
		$valid = [];
		foreach ($result as $tx){
			if(!in_array($tx->blockheight, $blocks)) continue;
			$vin = collect($tx->vin);
			$vout = collect($tx->vout);
			$btx->type = 'recieve'; 
			$all_from = $vin->pluck('add');
			$all_to = $vout->pluck('scriptPubKey')->pluck('addresses')->collapse();
			$mine_from = $all_from->intersect($adrs);
			$mine_to = $all_to->intersect($adrs);
			if($mine_from->count()){
				$btx = new \ofumbi\BTX;
				$btx->from = $mine_from;
				$btx->type = 'send'; 
				$btx->to = $all_to;
				$btx->addresses = $all_to;
				$btx->hash = $tx->txid ;
				$btx->fee = $tx->fees;
 				$btx->amount = $vin->whereIn('add', $mine_from)->sum('valueSat');
 				$btx->confirmations = $tx->confirmations;
				$btx->blockHeight = $tx->blockheight;
				$valid[] = $btx;
			}
			
			if($mine_to->count()){
				$btx = new \ofumbi\BTX;
				$btx->from = $all_from;
				$btx->type = 'send'; 
				$btx->to = $mine_to;
				$btx->hash = $tx->txid ;
				$btx->fee = $tx->fees;
 				$btx->amount = $vout->reject(function($val, $key)use($mine_to){
					return count(array_intersect($val->scriptPubKey->addresses,$mine_to)) < 1;
				})->sum('valueSat');
 				$btx->confirmations = $tx->confirmations;
				$btx->blockHeight = $tx->blockheight;
				$valid[] = $btx;
			}
	
		}
		return collect($valid);
	}
	
	public function importaddress($address,$wallet_name =null,$rescan =null){
		return false;
	}
	
	public function sendrawtransaction( $hexRawTx ){
		$endpoint = "/tx/send";
		return $this->httpRequest($endpoint,['rawtx'=>$hexRawTx],'POST')->txid;
	}
	
	public function getBlock($hash){
		$endpoint = "/txs/?block=".$hash;
		$result = $this->httpRequest($endpoint);
		$txs = collect($result->txs);
		if($result->pagesTotal > 1 ){
			for($i=2 ;$i == $result->pagesTotal; $i++ ){ 
				$response = $this->httpRequest($endpoint);
				$txs->concat($response->txs);
			}
		}
		return $result;
	}
	
	public function getBlockByNumber($number){
		$endpoint = "/block-index/".$number;
		$hash = $this->httpRequest($endpoint)->blockHash;
		return $this->getBlock($hash);
	}
	
	public function getTx($hash){
		$endpoint = "/tx/".$hash;
		return $this->httpRequest($endpoint);
	}
	
	public function currentBlock(){
		$endpoint = "/status?q=getInfo";
		return $this->httpRequest($endpoint)->blocks;
	}
	
	public function feePerKB(){
		$endpoint = "/utils/estimatefee?nbBlocks=";
		$fees = new stdClass;
		$fees->high = $this->httpRequest($endpoint.'2')->{'2'};
		$fees->medium = $this->httpRequest($endpoint.'6')->{'6'};
		$fees->low = $this->httpRequest($endpoint.'12')->{'12'};
		return $fees;
	}
	
	public function getBalance($minConf, array $addresses=[]){
		$all = collect ($this->listunspent($minConf, $addresses));
		return $all->reject(function($v,$k){
			$v->confirmations < $minConf; 
		})->sum('amount');
	}
	
	
}

