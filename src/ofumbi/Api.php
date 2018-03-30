<?php
namespace ofumbi;
use ofumbi\Api\Provider;
use \BitWasp\Bitcoin\Network\Network;
use phpseclib\Math\BigInteger;
use ofumbi\Api\ApiInterface;
Tightenco\Collect\Support\Collection;

class Api 
{
    public $network;
    public $provider;
	public $minConf = 6;
	public $max = 999999;

    public function __construct(Network $network, ApiInterface $provider)
    {
        $this->provider = $provider;
		$this->network = $network;
        
    }
	public function toBTC($satoshi)
    {
        return bcdiv((int)(string)$satoshi, 100000000, 8);
    }
	
	public  function toSatoshi($btc)
    {
        $out = bcmul(sprintf("%.8f", (float)$btc), 100000000, 0);
		return (int)$out;
    }
	
	public function fillUTXOS(Collection $address){
			$utxos = $this->listunspent($address->pluck('add'));
			$utxos = collect($utxos)->groupBy('address');
			return $address->map(function($add , $id)use($utxos){
				$ok = $utxos->get($add->address);
				if(empty($ok )) return NULL;
				foreach ($ok as $utxo ){
					if( $utxo->confimations > $this->minconf)
					$add->utxos[] = new UTXO($add,$utxo);
				}
				return $add;
			})->reject(function($value , $key){
				 return empty($value)||count($value->utxos) < 1 ;
			});
	}
	
	
	public function listunspent($address){
		$address = is_array($address)?$address:[$address];
		return $this->provider->listunspent($this->minconf, $address, $this->max);
	}
	
	public function getBalance($address){
		$address = is_array($address)?$address:[$address];
		return $this->provider->getBalance($this->minconf, $address);
	}
	
	public function importaddress($address,$wallet_name =null,$rescan =null){
		return $this->provider->importaddress($address,$wallet_name =null,$rescan =null);
	}
	
	public function sendrawtransaction( $hexRawTx ){
		return $this->provider->sendrawtransaction( $hexRawTx );
	}
	
	public function getBlock($blockHeigt){
		return $this->provider->getBlock($blockHeigt);
	}
	
	public function getTx($Hash){
		return $this->provider->getTx($Hash);
	}
	
	public function currentBlock(){
		return $this->provider->currentBlock();
	}
	
	public function feePerKB(){
		return $this->provider->feePerKB();
	}


   
}