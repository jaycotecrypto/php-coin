<?php
namespace ofumbi;
class UTXO
{
	public $address,$txId,$index,$scriptPubKey,$value,$confirmations;
	public $size = 297;
	public function __construct(\ofumbi\Address $address, object $utxo )
	{
		$this->txId = $utxo->txid;
		$this->index = $utxo->vout;
		$this->scriptPubKey=\BitWasp\Bitcoin\Script\ScriptFactory::fromHex($utxo->scriptPubKey);
		$this->value = $address->api->toSatoshi($utxo->amount);
		$this->confirmations = $utxo->confirmations;
        $this->address = $address;
  	}

	
	
}

