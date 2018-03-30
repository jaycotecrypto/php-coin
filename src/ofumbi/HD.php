<?php
namespace ofumbi;

class HD{
	
	
	
	/* Wallet  bip44 format*/
	public $bip44;
	/* Wallet  Private Key*/
	private $xpriv;
	/* Wallet  PrvteKey*/
	private $xprivKey;
	/* Wallet  Public Key*/
	private $xpubKey;
	/* Wallet  Public Key*/
	private $xpub; 
	/* Bitcoin network*/
	private $network;
	/* Wallet Password*/
	private $password; 
	/* Wallet  Master Private Key*/
	private $master_xpriv;
	/* Wallet  Master Private Key*/
	private $mnemonic;
	/* Wallet  Master Private Key*/
	private $master_xpub;
	//etherscan.io
	public $apiKey;
	
	function __construct( $network, string $bip44index = 0){
		$this->network = $network;
		$this->bip44 = "m/44'/".$bip44index."'/0'";
	}
	
	function getXpub(){
		if(is_null($this->xpub))throw new Exception('xpub not set');
		return $this->xpub;
	}
	function getXpub(){
		if(is_null($this->xpub))throw new Exception('xpub not set');
		return $this->xpub;
	}
		
 	function getMnemonic(){
		if(is_null($this->mnemonic))throw new Exception('mnemonic not set');
		return $this->mnemonic;
	}
	
	function getXpriv(){
		if(is_null($this->xpriv))throw new Exception('xpriv not set');
		return $this->xpriv;
	}
	function getMasterXpriv(){
		if(is_null($this->master_xpriv))throw new Exception('master_xpriv not set');
		return $this->master_xpriv;
	}
	
	function getPassword(){
		if(is_null($this->password))throw new Exception('password not set');
		return $this->password;
	}

	public function randomSeed( ){
		if(!is_null($password)){
			$this->password = $password;
		}
		$ecAdapter =  \BitWasp\Bitcoin\Bitcoin::getEcAdapter();
		$math = new \BitWasp\Bitcoin\Math\Math();
		$random = new \BitWasp\Bitcoin\Crypto\Random\Random();
		$entropy = $random->bytes(64);
		$bip39 = \BitWasp\Bitcoin\Mnemonic\MnemonicFactory::bip39();
		$seedGenerator = new \BitWasp\Bitcoin\Mnemonic\Bip39\Bip39SeedGenerator($bip39);
		// Get the mnemonic
		$mnemonic = $bip39->entropyToMnemonic($entropy);
		$this->mnemonic = $mnemonic;
		// Derive a seed from mnemonic/password
		if(is_null($this->password)){
			$pass = $random->bytes(8);
			$this->password = $pass->getHex();
		}
		$seed = $seedGenerator->getSeed($this->mnemonic, $this->password);
		$master = \BitWasp\Bitcoin\Key\Deterministic\HierarchicalKeyFactory::fromEntropy($seed);
		return $this->masterSeed($master->toExtendedPrivateKey());
	}
	
	public function recover($mnemonic, $password){
		$this->mnemonic = $mnemonic;
		$this->password = $pasword;
		$bip39 = \BitWasp\Bitcoin\Mnemonic\MnemonicFactory::bip39();
		$seedGenerator = new \BitWasp\Bitcoin\Mnemonic\Bip39\Bip39SeedGenerator($bip39);
		$seed = $seedGenerator->getSeed($this->mnemonic, $this->password);
		$master = \BitWasp\Bitcoin\Key\Deterministic\HierarchicalKeyFactory::fromEntropy($seed);
		return $this->masterSeed($master->toExtendedPrivateKey);
	}
	
	public function masterSeed($master){
		//Master xpriv
		$master = \BitWasp\Bitcoin\Key\Deterministic\HierarchicalKeyFactory::fromExtended($master);
		$master_xpriv = $master->toExtendedPrivateKey($this->network);
		$this->master_xpriv = $master_xpriv;
		$master_xpub = $master->toExtendedPublicKey($this->network); // path is master''
		$this->master_xpub  = $master_xpub;
		$hardened = $master->derivePath($this->bip44);
		$this->xpub = $hardened->toExtendedPublicKey($this->network);
		$this->xpriv = $hardened->toExtendedPrivateKey($this->network);
		$this->xpubKey = $hardened->getPublicKey();
		$this->xprivKey = $hardened->getPrivateKey();;
		return $this;
	}
	
	public function privateSeed($xpriv){
		//Master xpriv
		$this->xpriv = $xpriv;
		$xpriv = \BitWasp\Bitcoin\Key\Deterministic\HierarchicalKeyFactory::fromExtended($xpriv);
		$this->xpub = $xpriv->toExtendedPublicKey($this->network);
		$this->xpubKey = $xpriv->getPublicKey();
		$this->xprivKey = $xpriv->getPrivateKey();;
		return $this;
	}
	
	public function publicSeed($xpub){
		//Master xpriv
		$this->xpub = $xpub;
		$key = \BitWasp\Bitcoin\Key\Deterministic\HierarchicalKeyFactory::fromExtended($this->xpub);
		$this->xpubKey = $key->getPublicKey();
		return $this;
	}
	
	public function getAddress($index){
		if(empty($this->xpub))throw new \Exception('Public Key is missing');
		$key = \BitWasp\Bitcoin\Key\Deterministic\HierarchicalKeyFactory::fromExtended($this->xpub);
		if(strpos($index,'/')!==false)
		$xpub = $key->derivePath($index);
		else
		$xpub = $key->deriveChild($index);
		$publicKey = $xpub->getPublicKey($this->network);
		return $publicKey->getAddress()->getAddress($this->network);
	}
	
	public function derive($index){
		if(empty($this->xpriv))throw new \Exception('HD->derive($index) // Private Key is missing');
		$key = \BitWasp\Bitcoin\Key\Deterministic\HierarchicalKeyFactory::fromExtended($this->xpriv);
		if(strpos($index,'/')!==false)
		$xriv = $key->derivePath($index);
		else
		$xpriv = $key->deriveChild($index);
		$this->xprivKey = $xpriv->getPrivateKey($this->network);
		$this->xpubKey = $xpub->getPublicKey($this->network);
		return $this;
	}
	
	
	
	
	
}
