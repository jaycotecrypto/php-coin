<?php
namespace ofumbi;
use ofumbi\Address;
use  BitWasp\Bitcoin\Crypto\EcAdapter\Impl\Secp256k1\Key\PrivateKey;
class Multisig{
	
	private $index = NULL;
	public $api = NULL;
	/* Bitcoin network*/
	private $network;
	/* Wallet  bip44 format*/
	private $bip44;
	/* hdkey  */
	private $HD1; 
	/* hdkey  */
	private $HD2; 
	/* hdkey  */
	private $HD3; 
	/* redeemscript*/
	public $redeemscript ,$xpub ,$address,$privateKey1,$privateKey2,$privateKeys,$path,$HDpath;
	
	/* Wallet  Master Private Key*/
	
	
	function __construct( \ofumbi\HD $hd1 = NULL,\ofumbi\HD $hd2 = NULL, \ofumbi\HD $hd3 = NULL){
		$this->network = $hd1->network;
		$this->bip44 = "m/44'/".$hd1->bip44."'/0'";
		$this->HD1 = $hd1;
		$this->HD2 = $hd2;
		$this->HD3 = $hd3;
		$this->api = $hd1->api;
	}
	
	public function setHD1( \ofumbi\HD $hd){
		$this->HD1 = $hd;
		return $this;
	}
	public function setHD2( \ofumbi\HD $hd){
		$this->HD2 = $hd;
		return $this;
	}
	public function setHD3( \ofumbi\HD $hd){
		$this->HD3 = $hd;
		return $this;
	}
	public function getAddress($update = false){
		return new \ofumbi\Address(clone $this , $update);	
	}
	
	public function deriveAddress($index){
		return  $this->at($index)->address;
	}
	
	public function deriveChangeAddress($index){
		return $this->deriveAddress('1/'.$index);
	}
	
	public function at($index){
		$multisig = [ $this->HD1->getXprivKey(),$this->HD2->getXprivKey(),$this->HD3->getXpubKey()];
		$sequences = new \BitWasp\Bitcoin\Key\Deterministic\HierarchicalKeySequence();
		$hd = new \BitWasp\Bitcoin\Key\Deterministic\MultisigHD(2, $this->bip44, self::sortHDKeys($multisig), $sequences, true);
		if(strpos($index,'/')===false){
			$index = '0/'.$index;
		}
		$this->xpub = $hd->derivePath($index);
		$this->path = $index;
		$this->index = $index;
		$this->HDpath = $this->HD1->bip44; // standard
		$this->redeemscript = $this->xpub->getRedeemScript();
		$this->address = $this->xpub->getAddress($this->network)->getAddress($this->network);
		return  $this;
	}
	
	public function get($index){
		$multisig = [ $this->HD1->getXpubKey(),$this->HD2->getXpubKey(),$this->HD3->getXpubKey()];
		$sequences = new \BitWasp\Bitcoin\Key\Deterministic\HierarchicalKeySequence();
		$hd = new \BitWasp\Bitcoin\Key\Deterministic\MultisigHD(2, $this->bip44, self::sortHDKeys($multisig), $sequences, true);
		if(strpos($index,'/')===false){
			$index = '0/'.$index;
		}
		$this->xpub = $hd->derivePath($index);
		$this->path = $index;
		$this->index = $index;
		$this->HDpath = $this->HD1->bip44; // standard
		$this->redeemscript = $this->xpub->getRedeemScript();
		$this->address = $this->xpub->getAddress($this->network)->getAddress($this->network);
		return  $this;
	}
	
	private static  function sortHDKeys(array $keys) {
		return \BitWasp\Buffertools\Buffertools::sort($keys, function (\BitWasp\Bitcoin\Key\Deterministic\HierarchicalKey $key) {
				return $key->getPublicKey()->getBuffer();
			});
	 }
	
	
	
	
	
}
