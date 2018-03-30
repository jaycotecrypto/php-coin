<?php
namespace ofumbi;
use ofumbi\Address;
class Multisig{
	
	
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
	
	
	function __construct( \Ofumbi\Api $Api, string $bip44index = 0 ,\ofumbi\HD $hd1 = NULL,\ofumbi\HD $hd3 = NULL, \ofumbi\HD $hd3 = NULL){
		$this->network = $Api->network;
		$this->bip44 = "m/44'/".$bip44index."'/0'";
		$this->HD1 = $hd1;
		$this->HD2 = $hd2;
		$this->HD3 = $hd3;
		$this->Api = $Api;
	}
	
	public function setHD( \ofumbi\HD $hd , $index = 1){
		if($index == 1)
		$this->HD1 = $hd;
		if($index == 2)
		$this->HD2 = $hd;
		if($index == 3)
		$this->HD3 = $hd;
		return $this;
	}
	
	public function getAddress($index, $update = false){
		return new \ofumbi\Address($this->derive($index)->xpub->getAddress(), $this , $update);	
	}
	
	public function deriveAddress($index){
		return  $this->derive($index)->address;
	}
	
	public function deriveChangeAddress($index){
		return $this->getAddress('1/'.$index);
	}
	
	protected function derive($index){
		$multisig = [ $this->HD1->xpub,$this->HD2->xpub,$this->HD3->xpub];
		$sequences = new \BitWasp\Bitcoin\Key\Deterministic\HierarchicalKeySequence();
		$hd = new \BitWasp\Bitcoin\Key\Deterministic\MultisigHD(2, $this->bip44, self::sortHDKeys($multisig), $sequences, true);
		if(strpos($index,'/')===false){
			$index = '0/'.$index;
		}
		$this->xpub = $hd->derivePath($index);
		$this->path = $index;
		$this->HDpath = $this->HD1->bip44; // standard
		$this->redeemscript = $xpub->getRedeemScript();
		$this->address = $xpub->getAddress()->getAddress();
		$this->privateKey1 = $this->HD1->derive($index)->xprivKey;
		$this->privateKey2 = $this->HD2->derive($index)->xprivKey;
		$this->privateKeys = self::sortHDKeys([$this->privateKey1 ,$this->privateKey2 ]);
		return  $this;
			
	}
	
	private static  function sortHDKeys(array $keys) {
		return \BitWasp\Buffertools\Buffertools::sort($keys, function (\BitWasp\Bitcoin\Key\Deterministic\HierarchicalKey $key) {
				return $key->getPublicKey()->getBuffer();
			});
	 }
	
	
	
	
	
}
