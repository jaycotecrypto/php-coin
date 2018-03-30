<?php
namespace ofumbi\Api;
use ofumbi\Api\Providers\Insight;
use ofumbi\Api\Providers\Chainso;
use Graze\GuzzleHttp\JsonRpc\Client;

class DASH implements ApiInterface
{
	private  $masternode ,  // api providers
			 $dash ,  
			 $trezor1, 
			 $trezor2, 
			 $trezor3,
			 $chainso, 
			 $siampm;

    public function __construct( ) // well use varoius api to handle rate limmiting
    {
		$this->chainso = new  Chainso('DASH');
		$this->dash = new Insight('https://insight.dash.org/api');
		$this->siampm = new Insight('https://insight.dash.siampm.com/api'); 
		$this->masternode = new Insight('http://insight.masternode.io:3000/api'); 
		$this->trezor1 = new Insight('https://dash-bitcore1.trezor.io/api');   
		$this->trezor2 = new Insight('https://dash-bitcore2.trezor.io/api');	
		$this->trezor3 = new Insight('https://dash-bitcore3.trezor.io/api'); 
		
	}
	
	 /**
     * @return NetworkInterface
     * @throws \Exception
     */
    public static function network()
    {
        return \BitWasp\Bitcoin\Network\NetworkFactory::dash();
    }

    /**
     * @return NetworkInterface
     * @throws \Exception
     */
    public static function testnet()
    {
        return \BitWasp\Bitcoin\Network\NetworkFactory::dashTestnet();
    }
	//chainso
	public function addressTx(array $addresses=[], $blocks = []){
		return $this->masternode->addressTx($addresses, $blocks);
	}
	
	// dash
	public function listunspent($minconf, array $addresses=[], $max = null){
		return $this->dash->listunspent($minconf, $addresses, $max);
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
		return $this->siampm->getTx($hash);
	}
	
	public function currentBlock(){
		return $this->chainso->currentBlock();
	}
	
	public function feePerKB(){
		return $this->blockexplorer->feePerKB();;
	}
	//
	public function importaddress($address,$wallet_name =null,$rescan =null){
		return false;
	}
	
	
}

