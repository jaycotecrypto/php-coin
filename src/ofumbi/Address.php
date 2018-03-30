<?php
namespace ofumbi;
use ofumbi\Api;

class Address
{
	public $api = NULL;
	public $privateKeys =[];
	public $publicKeys = [];
	public $address = NULL;
	public $address = NULL;
	public $add = NULL;
	public $balance = NULL;
	public $type = NULL;
	public $multisig = NULL; //Address $HDMultisig object
	public function __construct($address, \ofumbi\Multisig $multisig,$update = false){
		$this->api = $multisig->api;
		$this->multisig = $multisig;
		$this->address = $address instanceof \BitWasp\Bitcoin\Address\AddressInterface ? $address : \BitWasp\Bitcoin\Address\AddressFactory::fromString($address);
		if($update)
		$this->add = $this->address->getAddress();
		$this->getUpdate();
	}
	
	public function getUpdate(){ // update the address balance
		$utxos = $this->api->listunspent($this->add);
		foreach( $utxos as $utxo){
			$this->utxos[] = new UTXO($this,$utxo);
		}
		$all = collect($this->utxos);
		$this->balance = $all->count()?$all->sum('value'):0;
	}
	
	
	
	
}

