<?php
namespace ofumbi;
Tightenco\Collect\Support\Collection;

class Transaction
{
    public $FeePerKB;
    public $to;
	public $from;
	public $amount;
	public $change;
	public $txHash;
	public $coin;
	public $api;
	public $fees;
	public $changeAddress;
	
    public function __construct( Collection $to , Collection $form , \ofumbi\Api $api, $changeAddress, int $fees = 0)
    {
		$this->to = $to ;
		$this->api = $api ;
		$this->from = $from ;
		$this->FeePerKB = $fees;
		$this->changeAddress = $changeAddress;
	}
	
	/*Will return the tx hash*/
	public function __toString(){
		return '0x'.$this->hash->getHex();
	}
	
		  /**
     * create, sign and send a transaction
     *
    
     */
    public function send() {
		$rawtx = $this->selectUTXOS()->getRaw();
		try{
			$finished = $this->api->sendrawtransaction($signed->getHex());
		}catch(Exception $e ){
			throw $e;
		}
       $this->txHash =$finished ;
	   return $this;
	}
	
	
	 protected function selectUTXOS () {
		$from = $this->api->fillUTXOS($this->from);
		$sorted = $from->pluck('utxos')
					   ->collapse()
					   ->sortByDesc(function($utxo,$key){
					   		return $utxo->amount*$utxo->confirmations;
					     });
        $total = 0;
		$target = $this->to->sum('amount');
		$selected =[];
		$OutSize = 16; // base tx size;
		$OutSize += 34*$this->to->count();; //outputs
		$fee = (int)ceil($OutSize * $this->FeePerKB/1000);
		$changeFee = (int)ceil(34 * $this->FeePerKB/1000);
        foreach ($sorted as $utxo ){
			$fees +=  (int)ceil($utxo->size * $this->FeePerKB/1000);
            $selected[] = $utxo;
            $total += ($address->balance - $address->fees);
			if ($total >=$target ){ 
				$change = $total - $target;
				if($change <= $changeFee ||$change < $this->minDust||$change - $changeFee < $this->minDust ){
					$change = 0;// its more expensive to add a chnage output
				}else{
					$fee += $changeFee;
					$this->fees = $fee;
					$this->to->concat(['value'=>$change, 'address'=> $this->changeAddress]);
				}
				$this->change = $change;
				$this->utxos = Collect($selected);
				return $this;
				break;
			}
        }
		$msg = 'Insufficient Balance. Total Bal:'.$this->form->sum('balance')
			 . ' Required:'.$target
			 . ' Plus Fee:'.$fee;
		throw new Exception($msg);
		
	 }
	 
	 
	
	public function getRaw(){
		$this->network = $api->network;
		\BitWasp\Bitcoin\Bitcoin::setNetwork($api->network);
		$this->selectUTXOS();
        $TX = new \BitWasp\Bitcoin\Transaction\Factory\TxBuilder();
	        foreach ($this->to as $out) {
				$TX->payToAddress($out['value'], \BitWasp\Bitcoin\Address\AddressFactory::fromString($out['address']));
			}

        foreach ($this->utxos as $utxo) {
			$signInfo[] = new SignInfo($utxo->address->multisig->privateKeys, new \BitWasp\Bitcoin\Transaction\TransactionOutput($utxo->value, $utxo->scriptPubKey),$utxo->address->multisig->redeemScript);
			//insure blockchain and our calculations are together
			assert($utxo->address->multisig->redeemScript->getOutputScript() == $utxo->scriptPubKey );
			$TX->spendOutPoint(new \BitWasp\Bitcoin\Transaction\OutPoint(\BitWasp\Buffertools\Buffer::hex($utxo->txId), $utxo->index), $utxo->scriptPubKey);
			
        }
		$rawtx = $TX->get();
		$signer = new \BitWasp\Bitcoin\Transaction\Factory\Signer($rawtx, \BitWasp\Bitcoin\Bitcoin::getEcAdapter());
        assert(self::all(function ($signInfo) {
            return $signInfo instanceof SignInfo;
        }, $signInfo), '$signInfo should be SignInfo[]');
       $sigHash = \BitWasp\Bitcoin\Transaction\SignatureHash\SigHash::ALL;
	   foreach ($signInfo as $idx => $info) {
			$redeemScript = $info->redeemScript;
			$txOut = $info->output;
			$keys = $info->keys;
			$signData = (new \BitWasp\Bitcoin\Transaction\Factory\SignData())->p2sh($redeemScript);
			$input = $signer->input($idx, $txOut, $signData );
			foreach($keys as $key){
				$input->sign($key, $sigHash);
			}
			
        }
        return $signer->get();
    }
	
	

}

