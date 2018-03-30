<?php
namespace ofumbi\Api;
use ofumbi\Api\Providers\Insight;
use ofumbi\Api\Providers\Chainso;
use Graze\GuzzleHttp\JsonRpc\Client;

class LTC implements ApiInterface
{
	private  $litecoin ,  
			 $trezor1, 
			 $trezor2, 
			 $trezor3,
			 $chainso, 
			 $coinspace;

    public function __construct( ) // well use varoius api to handle rate limmiting
    {
		
		$this->litecoin = new Insight('https://insight.litecore.io/api');
		$this->trezor1 = new Insight('https://ltc-bitcore1.trezor.io/api');  //listunspent
		$this->trezor2 = new Insight('https://ltc-bitcore2.trezor.io/api');	// balance 
		$this->trezor3 = new Insight('https://ltc-bitcore3.trezor.io/api');  // pushTx
		$this->chainso = new  Chainso('LTC');
		$this->coinspace = new Insight('https://ltc.coin.space/api');  //get Block
	}
	 /**
     * @return NetworkInterface
     * @throws \Exception
     */
    public static function network()
    {
        return \BitWasp\Bitcoin\Network\NetworkFactory::litecoin();
    }

    /**
     * @return NetworkInterface
     * @throws \Exception
     */
    public static function testnet()
    {
        return \BitWasp\Bitcoin\Network\NetworkFactory::litecoinTestnet();
    }
	//chainso
	public function addressTx(array $addresses=[], $blocks = []){
		return $this->chainso->addressTx($addresses, $blocks);
	}
	
	// litecoin
	public function listunspent($minconf, array $addresses=[], $max = null){
		return $this->litecoin->listunspent($minconf, $addresses, $max);
	}
	
	//trezor
	public function getBalance($minConf, array $addresses=[]){
		$this->trezor2->getBalance($minConf, $addresses );
	}
	
	public function sendrawtransaction( $hexRawTx ){
		return $this->trezor3->sendrawtransaction( $hexRawTx );
	}
	
	public function getBlock($hash){
		return $this->trezor1->getBlock($hash);
	}
	
	public function getBlockByNumber($number){
		return $this->getBlock($number);
	}
	
	public function getTx($hash){
		return $this->coinspace->getTx($hash);
	}
	
	public function currentBlock(){
		return $this->coinspace->currentBlock();
	}
	
	public function feePerKB(){
		return $this->blockexplorer->feePerKB();;
	}
	
	//
	public function importaddress($address,$wallet_name =null,$rescan =null){
		return false;
	}
	
	
}

